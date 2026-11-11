/**
 * ==============================================================================
 * E-WARGA SMART COMMUNITY OS - ESP32 SMART BARRIER GATE CONTROLLER
 * ==============================================================================
 * Hardware Specification:
 * - Mikrokontroler : ESP32 DevKit V1 (30 / 38 pin)
 * - Sensor Kartu   : MFRC522 RFID Reader (E-KTP / Kartu RFID 13.56 MHz)
 * - Display        : LCD 16x2 I2C (PCF8574 Adapter, Alamat 0x27 / 0x3F)
 * - Aktuator Palang: Servo SG90 (0 derajat = Tertutup, 90 derajat = Terbuka)
 * 
 * Library yang dibutuhkan di Arduino Library Manager:
 * 1. MFRC522 by GithubCommunity
 * 2. LiquidCrystal I2C by Frank de Brabander
 * 3. ESP32Servo by Kevin Harrington
 * 4. ArduinoJson by Benoit Blanchon (Versi 6 atau 7)
 * ==============================================================================
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ESP32Servo.h>

// ------------------------------------------------------------------------------
// 1. KONFIGURASI JARINGAN WI-FI & SERVER BACKEND LARAVEL
// ------------------------------------------------------------------------------
const char* WIFI_SSID     = "NAMA_WIFI_LINGKUNGAN";       // Ganti dengan SSID Wi-Fi Anda
const char* WIFI_PASSWORD = "PASSWORD_WIFI";              // Ganti dengan Password Wi-Fi Anda

// Masukkan IP Komputer / Server Laravel lokal Anda (Cek via `ipconfig` di Windows, contoh: 192.168.1.10)
// JANGAN gunakan '127.0.0.1' di ESP32, karena 127.0.0.1 merujuk ke chip ESP32 itu sendiri.
const char* SERVER_BASE_URL = "http://192.168.1.10:8000"; 
const char* DEVICE_UID      = "ESP32-GATE-01";
const char* DEVICE_TOKEN    = "SECRET-EW-GATE-2026";

// ------------------------------------------------------------------------------
// 2. PIN DEFINITION HARDWARE
// ------------------------------------------------------------------------------
// Pin RFID RC522 (Menggunakan Hardware SPI ESP32)
#define PIN_RC522_SS    5    // SDA / SS
#define PIN_RC522_RST   4    // RST
#define PIN_RC522_SCK   18   // SCK
#define PIN_RC522_MISO  19   // MISO
#define PIN_RC522_MOSI  23   // MOSI

// Pin Servo SG90
#define PIN_SERVO_GATE  13   // PWM Control Pin SG90

// Pin I2C LCD (Standar ESP32: SDA=21, SCL=22)
// Alamat I2C umum: 0x27 atau 0x3F
LiquidCrystal_I2C lcd(0x27, 16, 2);

// Objek Hardware
MFRC522 rfid(PIN_RC522_SS, PIN_RC522_RST);
Servo gateServo;

// Variabel State Palang
bool isGateOpen = false;
unsigned long gateOpenedMillis = 0;
unsigned long gateOpenDuration = 5000; // 5 detik default

// Timer Heartbeat Ping ke Server
unsigned long lastPingMillis = 0;
const unsigned long PING_INTERVAL = 2500; // Ping setiap 2.5 detik

// ------------------------------------------------------------------------------
// FUNGSI TAMPILAN LCD
// ------------------------------------------------------------------------------
void updateLcd(const char* line1, const char* line2) {
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print(line1);
    lcd.setCursor(0, 1);
    lcd.print(line2);
}

void setLcdStandby() {
    updateLcd("E-WARGA GATE 01", "TEMPEL KARTU KTP");
}

// ------------------------------------------------------------------------------
// KONTROL PALANG PINTU SERVO SG90
// ------------------------------------------------------------------------------
void openBarrierGate(const char* residentName, unsigned long durationMs = 5000) {
    gateServo.write(90); // Angkat palang pintu 90 derajat
    isGateOpen = true;
    gateOpenedMillis = millis();
    gateOpenDuration = durationMs;

    char line2[17];
    snprintf(line2, sizeof(line2), "%.16s", residentName);
    updateLcd("AKSES DIBERIKAN", line2);
    Serial.println("[GATE] Palang pintu TERANGKAT (90 Derajat)");
}

void closeBarrierGate() {
    gateServo.write(0); // Tutup kembali palang ke posisi 0 derajat
    isGateOpen = false;
    Serial.println("[GATE] Palang pintu DITUTUP (0 Derajat)");
    setLcdStandby();
}

// ------------------------------------------------------------------------------
// SETUP AWAL
// ------------------------------------------------------------------------------
void setup() {
    Serial.begin(115200);
    delay(500);
    Serial.println("\n[BOOT] Inisialisasi E-WARGA IoT Smart Gate...");

    // 1. Inisialisasi LCD 16x2 I2C
    Wire.begin(21, 22);
    lcd.init();
    lcd.backlight();
    updateLcd("E-WARGA IOT OS", "MEMULAI SISTEM..");
    delay(1000);

    // 2. Inisialisasi Servo SG90
    gateServo.setPeriodHertz(50);
    gateServo.attach(PIN_SERVO_GATE, 500, 2400); // Standard pulse width SG90
    closeBarrierGate(); // Pastikan posisi awal tertutup

    // 3. Inisialisasi RFID RC522
    SPI.begin(PIN_RC522_SCK, PIN_RC522_MISO, PIN_RC522_MOSI, PIN_RC522_SS);
    rfid.PCD_Init();
    delay(100);
    Serial.println("[BOOT] Modul RFID RC522 Siap.");

    // 4. Koneksi Wi-Fi
    updateLcd("MENGHUBUNGKAN", WIFI_SSID);
    WiFi.mode(WIFI_STA);
    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

    int wifiRetry = 0;
    while (WiFi.status() != WL_CONNECTED && wifiRetry < 20) {
        delay(500);
        Serial.print(".");
        wifiRetry++;
    }

    if (WiFi.status() == WL_CONNECTED) {
        Serial.println("\n[WIFI] Terhubung! IP: " + WiFi.localIP().toString());
        updateLcd("WIFI TERHUBUNG!", WiFi.localIP().toString().c_str());
        delay(1500);
    } else {
        Serial.println("\n[WIFI] Gagal terhubung ke Wi-Fi. Cek SSID & Password.");
        updateLcd("WIFI GAGAL!", "MODE OFFLINE");
        delay(2000);
    }

    setLcdStandby();
}

// ------------------------------------------------------------------------------
// KOMUNIKASI HTTP KE SERVER LARAVEL E-WARGA
// ------------------------------------------------------------------------------

// 1. Kirim UID RFID ke Server saat kartu ditempelkan
void sendRfidToServer(String rfidUid) {
    if (WiFi.status() != WL_CONNECTED) {
        updateLcd("KONEKSI OFFLINE", "HUBUNGI SATPAM");
        delay(2000);
        setLcdStandby();
        return;
    }

    updateLcd("MEMERIKSA KTP..", rfidUid.c_str());

    HTTPClient http;
    String endpoint = String(SERVER_BASE_URL) + "/api/v1/gate/scan-rfid";
    http.begin(endpoint);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-Device-UID", DEVICE_UID);
    http.addHeader("X-Device-Token", DEVICE_TOKEN);

    StaticJsonDocument<200> reqDoc;
    reqDoc["device_uid"] = DEVICE_UID;
    reqDoc["rfid_uid"]   = rfidUid;

    String reqBody;
    serializeJson(reqDoc, reqBody);

    Serial.println("[HTTP POST] Mengirim UID: " + rfidUid);
    int httpResponseCode = http.POST(reqBody);

    if (httpResponseCode > 0) {
        String response = http.getString();
        Serial.println("[HTTP RESPONSE] " + response);

        StaticJsonDocument<512> resDoc;
        DeserializationError error = deserializeJson(resDoc, response);

        if (!error) {
            bool success = resDoc["success"] | false;
            const char* status = resDoc["status"] | "denied";

            if (success && strcmp(status, "granted") == 0) {
                const char* residentName = resDoc["resident_name"] | "Warga";
                int duration = resDoc["open_duration"] | 5;
                openBarrierGate(residentName, duration * 1000);
            } else {
                const char* line1 = resDoc["lcd_line1"] | "AKSES DITOLAK!";
                const char* line2 = resDoc["lcd_line2"] | "BELUM DI-ACC RW";
                updateLcd(line1, line2);
                delay(3000);
                setLcdStandby();
            }
        }
    } else {
        Serial.printf("[HTTP ERROR] Kode: %d\n", httpResponseCode);
        updateLcd("SERVER TIMEOUT", "COBA LAGI");
        delay(2000);
        setLcdStandby();
    }

    http.end();
}

// 2. Heartbeat Ping & Cek apakah Warga menekan tombol "Buka Gerbang" di HP
void pingServer() {
    if (WiFi.status() != WL_CONNECTED) return;

    HTTPClient http;
    String endpoint = String(SERVER_BASE_URL) + "/api/v1/gate/ping";
    http.begin(endpoint);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-Device-UID", DEVICE_UID);
    http.addHeader("X-Device-Token", DEVICE_TOKEN);

    StaticJsonDocument<128> reqDoc;
    reqDoc["device_uid"] = DEVICE_UID;

    String reqBody;
    serializeJson(reqDoc, reqBody);

    int httpCode = http.POST(reqBody);

    if (httpCode == 200) {
        String resBody = http.getString();
        StaticJsonDocument<384> resDoc;
        DeserializationError err = deserializeJson(resDoc, resBody);

        if (!err) {
            const char* action = resDoc["action"] | "idle";
            
            // JIKA ADA PERINTAH BUKA DARI HP WARGA ATAU POS SATPAM
            if (strcmp(action, "open") == 0 && !isGateOpen) {
                const char* triggerBy = resDoc["triggered_by"] | "Aplikasi HP";
                int duration = resDoc["open_duration"] | 5;
                Serial.println("[REMOTE] Perintah Buka Diterima dari HP Warga!");
                openBarrierGate(triggerBy, duration * 1000);
            }
        }
    }

    http.end();
}

// ------------------------------------------------------------------------------
// LOOP UTAMA (REAL-TIME EVENT DRIVEN)
// ------------------------------------------------------------------------------
void loop() {
    unsigned long currentMillis = millis();

    // 1. Logika Auto-Close Palang Pintu Otomatis
    if (isGateOpen && (currentMillis - gateOpenedMillis >= gateOpenDuration)) {
        closeBarrierGate();
    }

    // 2. Heartbeat Ping ke Server secara Berkala
    if (currentMillis - lastPingMillis >= PING_INTERVAL) {
        lastPingMillis = currentMillis;
        pingServer();
    }

    // 3. Deteksi Pembacaan Kartu RFID RC522
    if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) {
        return; // Tidak ada kartu baru, lanjutkan loop
    }

    // Baca UID Kartu
    String scannedUid = "";
    for (byte i = 0; i < rfid.uid.size; i++) {
        if (rfid.uid.uidByte[i] < 0x10) scannedUid += "0";
        scannedUid += String(rfid.uid.uidByte[i], HEX);
    }
    scannedUid.toUpperCase();

    Serial.println("\n[RFID] Kartu Ditempelkan! UID: " + scannedUid);
    sendRfidToServer(scannedUid);

    // Hentikan enkripsi kartu agar siap untuk pembacaan berikutnya
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
    delay(500);
}
