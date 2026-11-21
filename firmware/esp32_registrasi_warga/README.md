# 🪪 ESP32 Standalone Scanner: Registrasi Kilat e-KTP

> **Firmware mikrokontroler ESP32 khusus untuk alat scanner pembaca e-KTP di meja registrasi warga baru (Kantor RT/RW, Pos Satpam, atau Balai Warga).**  
> *Versi ini praktis dan tidak membutuhkan motor servo/palang pintu gerbang.*

---

## 📋 Keunggulan & Cara Kerja

1. **Tanpa Rakitan Palang Gerbang:**
   * Hanya membutuhkan ESP32 + RFID Reader RC522 + LCD Display (Opsional Buzzer).
   * Cukup ditaruh di atas meja pendaftaran atau pos jaga.
2. **Terintegrasi Langsung ke Web:**
   * Saat warga menempelkan kartu e-KTP fisik ke reader ini, nomor chip UID (13.56&nbsp;MHz) dikirim secara nirkabel via Wi-Fi ke server web E-WARGA.
   * Di layar laptop/HP pada halaman **[Registrasi Kilat e-KTP](https://e-warga.unique-monsters.my.id/register)**, kolom nomor kartu otomatis terisi dalam sekejap tanpa ketik manual.
3. **Proteksi Anti-Duplikat:**
   * Jika kartu e-KTP sudah pernah didaftarkan pada warga lain, alat memberi nada error (*beep panjang*) dan LCD menampilkan `REGIS GAGAL! / KARTU SUDAH ADA`.

---

## 🛠️ Komponen yang Dibutuhkan

| No | Komponen | Estimasi Harga | Fungsi |
| :---: | :--- | :--- | :--- |
| 1 | **ESP32 DevKit V1 (30 atau 38 Pin)** | ~Rp&nbsp;45.000 | Mikrokontroler utama Wi-Fi |
| 2 | **Modul RFID RC522 (13.56 MHz)** | ~Rp&nbsp;15.000 | Sensor pembaca chip e-KTP fisik |
| 3 | **LCD Display 16x2 + I2C Adapter** | ~Rp&nbsp;25.000 | Layar teks petunjuk pendaftaran |
| 4 | **Buzzer 5V (Aktif/Pasif)** | ~Rp&nbsp;3.000 | Indikator suara *beep* sukses/gagal (Opsional) |
| 5 | **Kabel Jumper Dupont Female-to-Female** | ~Rp&nbsp;10.000 | Penghubung pin modul |
| 6 | **Kabel Micro-USB / Type-C** | Sesuai tipe ESP32 | Sumber daya 5V dari charger HP / Laptop |

---

## 🔌 Skema Wiring Pinout

### 1. RFID RC522 &rarr; ESP32 (Hardware SPI)
> **PENTING:** Modul RC522 WAJIB dihubungkan ke tegangan **3.3V** ESP32, JANGAN ke 5V (dapat merusak modul).

| Pin Modul RC522 | Pin GPIO ESP32 | Keterangan |
| :--- | :--- | :--- |
| **3.3V** | **3V3** | Tegangan 3.3V DC |
| **GND** | **GND** | Ground |
| **RST** | **GPIO 4** | Reset Pin |
| **SDA / SS** | **GPIO 5** | SPI Chip Select |
| **SCK** | **GPIO 18** | SPI Clock |
| **MISO** | **GPIO 19** | SPI Master In |
| **MOSI** | **GPIO 23** | SPI Master Out |

### 2. LCD 16x2 I2C &rarr; ESP32
| Pin Modul I2C LCD | Pin GPIO ESP32 | Keterangan |
| :--- | :--- | :--- |
| **VCC** | **VIN (5V)** | Sumber daya LCD |
| **GND** | **GND** | Ground |
| **SDA** | **GPIO 21** | I2C Data |
| **SCL** | **GPIO 22** | I2C Clock |

### 3. Buzzer (Opsional)
| Pin Buzzer | Pin GPIO ESP32 | Keterangan |
| :--- | :--- | :--- |
| **Kaki (+) / Positif** | **GPIO 12** | Sinyal PWM nada beep |
| **Kaki (-) / Negatif** | **GND** | Ground |

---

## 💻 Langkah Upload via Arduino IDE

1. **Buka Arduino IDE** di laptop Anda.
2. Buka menu **Tools &rarr; Manage Libraries…** (atau `Ctrl + Shift + I`).
3. Cari dan instal 3 library berikut:
   * **`MFRC522`** by *GithubCommunity*
   * **`LiquidCrystal I2C`** by *Frank de Brabander*
   * **`ArduinoJson`** by *Benoit Blanchon* (Versi 6 atau 7)
4. Buka file [esp32_registrasi_warga.ino](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/firmware/esp32_registrasi_warga/esp32_registrasi_warga.ino).
5. Sesuaikan baris konfigurasi Wi-Fi & URL server:
   ```cpp
   const char* WIFI_SSID     = "Nama_WiFi_Anda";
   const char* WIFI_PASSWORD = "Password_WiFi_Anda";
   const char* SERVER_BASE_URL = "https://e-warga.unique-monsters.my.id";
   ```
   *(Jika Anda menjalankan Laravel di localhost laptop, gunakan IP lokal laptop Anda, contoh: `http://192.168.1.10:8000`).*
6. Pilih board **DOIT ESP32 DEVKIT V1**, pilih Port COM yang sesuai, lalu klik **Upload** (tanda panah kanan ➔).

---

## 🎯 Cara Uji Coba Registrasi Kilat

1. Sambungkan alat ESP32 ke adaptor charger HP atau port USB laptop.
2. Layar LCD menampilkan:
   ```text
   E-WARGA REGIS
   TEMPEL e-KTP...
   ```
3. Buka halaman registrasi web di browser:
   🔗 **[https://e-warga.unique-monsters.my.id/register](https://e-warga.unique-monsters.my.id/register)**
4. Pilih tab **"⚡ Registrasi Kilat e-KTP"**, lalu klik tombol **"📡 Hubungkan Alat Sensor ESP32"**.
5. Tempelkan kartu fisik e-KTP ke sensor RFID:
   * Layar LCD berbunyi *beep-beep* gembira dan menampilkan:
     ```text
     KTP TERCATAT!
     REGIS KILAT OK
     ```
   * Nomor UID kartu seketika muncul otomatis di formulir pendaftaran warga pada layar laptop/HP Anda.
   * Lanjutkan dengan mengunggah foto KTP / klik "⚡ Simulasi Scan KTP" untuk melengkapi biodata dalam 1-klik!
