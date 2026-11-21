/**
 * ==============================================================================
 * E-WARGA SMART COMMUNITY OS - ESP32 STANDALONE REGISTRASI KILAT e-KTP
 * ==============================================================================
 * Firmware khusus alat scanner e-KTP meja pendaftaran warga (Pos Satpam / Kantor RT/RW).
 * TIDAK MEMERLUKAN SERVO / PALANG PINTU GERBANG (Praktis & Mudah Dirakit).
 * 
 * Fungsi Alat:
 * - Warga / Calon Warga Baru menempelkan kartu fisik e-KTP ke alat scanner ini.
 * - Chip UID e-KTP (13.56 MHz) dibaca secara instan.
 * - UID otomatis dikirimkan ke server web E-WARGA pada menu "Registrasi Kilat e-KTP".
 * - Layar LCD menampilkan feedback status, dan buzzer berbunyi konfirmasi.
 * 
 * Komponen Hardware:
 * 1. ESP32 DevKit V1 (30 / 38 pin)
 * 2. Modul RFID Reader MFRC522 (13.56 MHz)
 * 3. Layar LCD 16x2 I2C (PCF8574 Adapter, Alamat 0x27 / 0x3F)
 * 4. Pasif / Aktif Buzzer 5V (Opsional, untuk indikator nada suara beep)
 * 5. LED Indikator Onboard (GPIO 2)
 * 
 * Library yang dibutuhkan di Arduino IDE (Tools -> Manage Libraries):
 * 1. MFRC522 by GithubCommunity
 * 2. LiquidCrystal I2C by Frank de Brabander
 * 3. ArduinoJson by Benoit Blanchon (Versi 6 atau 7)
 * ==============================================================================
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <ArduinoJson.h>
#include <SPI.h>
#include <MFRC522.h>

// ------------------------------------------------------------------------------
// 1. PENGATURAN WI-FI & URL SERVER E-WARGA
// ------------------------------------------------------------------------------
const char* WIFI_SSID     = "BIAN";         // Ganti dengan SSID Wi-Fi / Hotspot Anda
const char* WIFI_PASSWORD = "00000000";     // Ganti dengan Password Wi-Fi / Hotspot Anda

// Masukkan IP laptop/server lokal (contoh: "http://192.168.1.10:8000")
// atau URL domain produksi (contoh: "https://e-warga.unique-monsters.my.id")
const char* SERVER_BASE_URL = "https://e-warga.unique-monsters.my.id"; 
const char* DEVICE_UID      = "ESP32-REGIS-DESK-01";
const int   TENANT_ID       = 1;                      // ID Lingkungan RT/RW default

// ------------------------------------------------------------------------------
// 2. PIN DEFINITION HARDWARE ESP32
// ------------------------------------------------------------------------------
// Pin RFID RC522 (Hardware SPI)
#define PIN_RC522_SS    5    // SDA / SS
#define PIN_RC522_RST   4    // RST
#define PIN_RC522_SCK   18   // SCK
#define PIN_RC522_MISO  19   // MISO
#define PIN_RC522_MOSI  23   // MOSI

// Pin Indikator & Feedback
#define PIN_BUZZER      12   // Pin Buzzer (Opsional, hubungkan ke kaki + buzzer)
#define PIN_LED_STATUS  2    // Pin LED Status Onboard ESP32

// Pin I2C LCD (SDA=GPIO 21, SCL=GPIO 22)
// Alamat default: 0x27 (atau 0x3F jika menggunakan modul display lain)
LiquidCrystal_I2C lcd(0x27, 16, 2);

// Objek RFID
MFRC522 rfid(PIN_RC522_SS, PIN_RC522_RST);

// Debounce timer agar kartu tidak terbaca berkali-kali dalam 1 detik
unsigned long lastScanMillis = 0;
const unsigned long SCAN_COOLDOWN = 1500; // 1.5 detik cooldown

// Timer Heartbeat Ping ke Server (Setiap 8 detik)
unsigned long lastPingMillis = 0;
const unsigned long PING_INTERVAL = 8000; // 8 detik interval ping status online

// ------------------------------------------------------------------------------
// FEEDBACK SUARA BUZZER & LED
// ------------------------------------------------------------------------------
void beepSuccess() {
    digitalWrite(PIN_LED_STATUS, HIGH);
    tone(PIN_BUZZER, 2000, 100);
    delay(120);
    tone(PIN_BUZZER, 2600, 180);
    digitalWrite(PIN_LED_STATUS, LOW);
}

void beepError() {
    digitalWrite(PIN_LED_STATUS, HIGH);
    tone(PIN_BUZZER, 600, 450);
    delay(500);
    digitalWrite(PIN_LED_STATUS, LOW);
}

void beepClick() {
    tone(PIN_BUZZER, 1800, 50);
}

// ------------------------------------------------------------------------------
// TAMPILAN DISPLAY LCD 16X2
// ------------------------------------------------------------------------------
void updateLcd(const char* line1, const char* line2) {
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print(line1);
    lcd.setCursor(0, 1);
    lcd.print(line2);
}

void setLcdStandby() {
    updateLcd("E-WARGA REGIS", "TEMPEL e-KTP...");
}

// ------------------------------------------------------------------------------
// MENGIRIM UID e-KTP KE SERVER REGISTRASI KILAT
// ------------------------------------------------------------------------------
void sendRfidToRegistrationServer(String rfidUid) {
    if (WiFi.status() != WL_CONNECTED) {
        beepError();
        updateLcd("WIFI TERPUTUS!", "CEK KONEKSI");
        delay(2000);
        setLcdStandby();
        return;
    }

    beepClick();
    updateLcd("MENCATAT e-KTP..", rfidUid.c_str());

    HTTPClient http;
    // Endpoint REST API Registrasi Kilat
    String endpoint = String(SERVER_BASE_URL) + "/api/v1/registration-device/scan";
    http.begin(endpoint);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-Device-UID", DEVICE_UID);

#if defined(ARDUINOJSON_VERSION_MAJOR) && ARDUINOJSON_VERSION_MAJOR >= 7
    JsonDocument reqDoc;
#else
    StaticJsonDocument<256> reqDoc;
#endif
    reqDoc["device_uid"] = DEVICE_UID;
    reqDoc["rfid_uid"]   = rfidUid;
    reqDoc["tenant_id"]  = TENANT_ID;

    String reqBody;
    serializeJson(reqDoc, reqBody);

    Serial.println("[HTTP POST] Mengirim UID: " + rfidUid + " ke " + endpoint);
    int httpResponseCode = http.POST(reqBody);

    if (httpResponseCode > 0) {
        String response = http.getString();
        Serial.println("[HTTP RESPONSE] " + response);

#if defined(ARDUINOJSON_VERSION_MAJOR) && ARDUINOJSON_VERSION_MAJOR >= 7
        JsonDocument resDoc;
#else
        StaticJsonDocument<512> resDoc;
#endif
        DeserializationError error = deserializeJson(resDoc, response);

        if (!error) {
            bool success = resDoc["success"] | false;
            const char* status = resDoc["status"] | "denied";
            const char* line1  = resDoc["lcd_line1"] | "KTP TERCATAT!";
            const char* line2  = resDoc["lcd_line2"] | "REGIS KILAT OK";

            if (success && strcmp(status, "paired") == 0) {
                // Berhasil tercatat untuk registrasi kilat
                beepSuccess();
                updateLcd(line1, line2);
                Serial.printf("[SUKSES] e-KTP %s masuk ke form registrasi kilat!\n", rfidUid.c_str());
                delay(3000);
            } else {
                // Gagal (contoh: kartu sudah terdaftar pada warga lain)
                beepError();
                updateLcd(line1, line2);
                Serial.printf("[DITOLAK] %s\n", line2);
                delay(3000);
            }
        } else {
            beepError();
            updateLcd("PARSING ERROR!", "CEK RESPONSE");
            delay(2000);
        }
    } else {
        Serial.printf("[HTTP ERROR] Kode: %d\n", httpResponseCode);
        beepError();
        updateLcd("SERVER TIMEOUT", "COBA LAGI");
        delay(2000);
    }

    http.end();
    setLcdStandby();
}

// ------------------------------------------------------------------------------
// HEARTBEAT PING (Kirim status ONLINE ke Server secara berkala)
// ------------------------------------------------------------------------------
void sendHeartbeatPing() {
    if (WiFi.status() != WL_CONNECTED) return;

    HTTPClient http;
    String endpoint = String(SERVER_BASE_URL) + "/api/v1/registration-device/ping?device_uid=" + String(DEVICE_UID) + "&tenant_id=" + String(TENANT_ID);
    http.begin(endpoint);
    http.addHeader("X-Device-UID", DEVICE_UID);
    http.setTimeout(3000);

    int httpResponseCode = http.GET();
    if (httpResponseCode == 200) {
        Serial.printf("[HEARTBEAT] Ping server sukses. Status: ONLINE di Hotspot %s\n", WIFI_SSID);
    } else {
        Serial.printf("[HEARTBEAT] Ping respon: %d\n", httpResponseCode);
    }
    http.end();
}

// ------------------------------------------------------------------------------
// SETUP PROGRAM
// ------------------------------------------------------------------------------
void setup() {
    Serial.begin(115200);
    delay(500);
    Serial.println("\n\n==================================================");
    Serial.println("  E-WARGA - ESP32 STANDALONE REGISTRASI e-KTP   ");
    Serial.println("==================================================");

    // Inisialisasi Pin Feedback
    pinMode(PIN_BUZZER, OUTPUT);
    pinMode(PIN_LED_STATUS, OUTPUT);
    digitalWrite(PIN_BUZZER, LOW);
    digitalWrite(PIN_LED_STATUS, LOW);

    // Inisialisasi I2C & LCD
    Wire.begin(21, 22);
    lcd.init();
    lcd.backlight();
    updateLcd("E-WARGA REGIS", "MEMULAI SISTEM..");
    beepClick();
    delay(1000);

    // Inisialisasi Hardware SPI & Sensor MFRC522
    SPI.begin(PIN_RC522_SCK, PIN_RC522_MISO, PIN_RC522_MOSI, PIN_RC522_SS);
    rfid.PCD_Init();
    delay(100);

    // Self-test MFRC522
    byte version = rfid.PCD_ReadRegister(rfid.VersionReg);
    Serial.printf("[RFID] Versi Modul MFRC522: 0x%02X\n", version);
    if (version == 0x00 || version == 0xFF) {
        Serial.println("[RFID WARNING] Modul MFRC522 tidak terdeteksi! Periksa kabel jumper.");
        updateLcd("SENSOR RFID!", "PERIKSA KABEL");
        delay(2500);
    } else {
        Serial.println("[RFID OK] Sensor MFRC522 siap membaca e-KTP.");
    }

    // Sambungkan ke Jaringan Wi-Fi
    updateLcd("KONEKSI WI-FI..", WIFI_SSID);
    Serial.printf("\n[WIFI] Menyambungkan ke Hotspot: %s \n", WIFI_SSID);

    WiFi.disconnect(true);
    delay(200);
    WiFi.mode(WIFI_STA);
    WiFi.persistent(false);
    WiFi.setAutoReconnect(true);
    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 40) {
        delay(500);
        Serial.print(".");
        attempts++;
        digitalWrite(PIN_LED_STATUS, !digitalRead(PIN_LED_STATUS));
    }

    if (WiFi.status() == WL_CONNECTED) {
        digitalWrite(PIN_LED_STATUS, HIGH);
        Serial.printf("\n[WIFI OK] Terhubung! IP ESP32: %s\n", WiFi.localIP().toString().c_str());
        beepSuccess();
        updateLcd("WI-FI ONLINE", WiFi.localIP().toString().c_str());
        delay(2000);
        sendHeartbeatPing();
    } else {
        Serial.println("\n[WIFI TIMEOUT] Wi-Fi gagal terhubung pada boot awal.");
        Serial.println("[TIPS HOTSPOT] 1. Pastikan Hotspot menggunakan frekuensi 2.4 GHz (bukan 5 GHz).");
        Serial.println("[TIPS HOTSPOT] 2. Pastikan Nama SSID dan Password tepat.");
        Serial.println("[TIPS HOTSPOT] 3. Alat akan otomatis terus mencoba menghubungkan di background.");
        beepError();
        updateLcd("WIFI GAGAL!", "CEK 2.4GHz BIAN");
        delay(2500);
    }

    setLcdStandby();
    Serial.println("[STANDBY] Tempelkan fisik e-KTP untuk memulai registrasi kilat...");
}

// ------------------------------------------------------------------------------
// LOOP UTAMA
// ------------------------------------------------------------------------------
void loop() {
    // 0. Auto Reconnect jika hotspot baru aktif atau sinyal sempat putus
    if (WiFi.status() != WL_CONNECTED) {
        static unsigned long lastReconnectAttempt = 0;
        if (millis() - lastReconnectAttempt >= 6000) {
            lastReconnectAttempt = millis();
            Serial.println("[WIFI RETRY] Menghubungkan ulang ke Hotspot " + String(WIFI_SSID) + "...");
            WiFi.reconnect();
        }
    } else {
        // Heartbeat Ping berkala ke Server setiap 8 detik agar terdeteksi ONLINE di Web
        if (millis() - lastPingMillis >= PING_INTERVAL) {
            lastPingMillis = millis();
            sendHeartbeatPing();
        }
    }

    // 1. Cek apakah ada kartu e-KTP / RFID di depan sensor
    if (!rfid.PICC_IsNewCardPresent()) {
        delay(50);
        return;
    }

    // 2. Baca serial UID kartu
    if (!rfid.PICC_ReadCardSerial()) {
        delay(50);
        return;
    }

    // 3. Debounce proteksi cooldown
    if (millis() - lastScanMillis < SCAN_COOLDOWN) {
        rfid.PICC_HaltA();
        rfid.PCD_StopCrypto1();
        return;
    }
    lastScanMillis = millis();

    // 4. Format UID menjadi string heksadesimal berpemisah kolon (Contoh: "5A:4B:3C:2D")
    String rfidUid = "";
    for (byte i = 0; i < rfid.uid.size; i++) {
        if (rfid.uid.uidByte[i] < 0x10) rfidUid += "0";
        rfidUid += String(rfid.uid.uidByte[i], HEX);
        if (i < rfid.uid.size - 1) rfidUid += ":";
    }
    rfidUid.toUpperCase();

    Serial.println("\n--------------------------------------------------");
    Serial.println("[RFID SCAN] e-KTP Terdeteksi! UID: " + rfidUid);

    // 5. Kirim UID ke Server Web E-WARGA untuk Form Registrasi Kilat
    sendRfidToRegistrationServer(rfidUid);

    // Hentikan komunikasi dengan kartu saat ini
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
}
