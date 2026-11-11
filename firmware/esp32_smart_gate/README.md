# Firmware ESP32 Smart Gate - E-WARGA IoT

Firmware resmi untuk mikrokontroler **ESP32**, **LCD 16x2 I2C**, **Servo SG90**, dan **RFID RC522** yang terintegrasi penuh dengan backend **E-WARGA Smart Community OS**.

---

## 1. Skema Pengkabelan (Wiring Pinout Table)

### A. RFID RC522 ke ESP32 (Jalur Hardware SPI)
| Pin RC522 | Pin ESP32 | Keterangan |
| :--- | :--- | :--- |
| **VCC (3.3V)** | **3V3** | ⚠️ Wajib 3.3V (Jangan ke 5V agar chip tidak rusak) |
| **RST** | **GPIO 4** | Reset Pin |
| **GND** | **GND** | Ground |
| **MISO** | **GPIO 19** | SPI Master In Slave Out |
| **MOSI** | **GPIO 23** | SPI Master Out Slave In |
| **SCK** | **GPIO 18** | SPI Clock |
| **SDA / SS** | **GPIO 5** | SPI Chip Select |

---

### B. LCD 16x2 I2C ke ESP32
| Pin Modul I2C LCD | Pin ESP32 | Keterangan |
| :--- | :--- | :--- |
| **VCC** | **VIN / 5V** | Daya 5V untuk backlight LCD |
| **GND** | **GND** | Ground |
| **SDA** | **GPIO 21** | Jalur Data I2C |
| **SCL** | **GPIO 22** | Jalur Clock I2C |

*Catatan: Jika tulisan tidak muncul, putar potensiometer biru (trimpot kontras) di belakang modul I2C LCD dengan obeng kecil.*

---

### C. Servo SG90 (Palang Pintu) ke ESP32
| Kabel Servo SG90 | Pin ESP32 / Sumber Daya | Keterangan |
| :--- | :--- | :--- |
| **Coklat (GND)** | **GND** | Ground bersama |
| **Merah (VCC)** | **VIN (5V) / Adaptor 5V** | Disarankan memakai daya 5V terpisah jika servo bergetar |
| **Oranye / Kuning (Signal)** | **GPIO 13** | Sinyal PWM Kendali Sudut |

---

## 2. Library Arduino IDE yang Dibutuhkan

Buka **Arduino IDE** &rarr; Menu **Sketch** &rarr; **Include Library** &rarr; **Manage Libraries...**, lalu cari dan pasang:
1. **MFRC522** (oleh *GithubCommunity*)
2. **LiquidCrystal I2C** (oleh *Frank de Brabander*)
3. **ESP32Servo** (oleh *Kevin Harrington*)
4. **ArduinoJson** (oleh *Benoit Blanchon* - Versi 6 atau 7)

---

## 3. Konfigurasi dan Cara Upload

1. Buka file `esp32_smart_gate.ino` di Arduino IDE.
2. Ubah konfigurasi Wi-Fi:
   ```cpp
   const char* WIFI_SSID     = "NAMA_WIFI_ANDA";
   const char* WIFI_PASSWORD = "PASSWORD_WIFI_ANDA";
   ```
3. Ubah IP Server Laravel:
   ```cpp
   // Cek IP laptop/server via terminal: ipconfig (Windows)
   // Contoh: http://192.168.1.15:8000
   const char* SERVER_BASE_URL = "http://192.168.X.X:8000";
   ```
4. Pilih Board: **ESP32 Dev Module** (atau sesuai tipe board ESP32 Anda).
5. Pilih Port COM yang sesuai & klik **Upload**.

---

## 4. Alur Integrasi Otomatis dengan E-WARGA

1. **Tap Kartu E-KTP / RFID:**
   - ESP32 membaca UID kartu dan mengirim ke endpoint: `POST /api/v1/gate/scan-rfid`.
   - Jika warga **sudah di-ACC oleh Ketua RW**, LCD menampilkan:
     `AKSES DIBERIKAN` / `[Nama Warga]` & servo SG90 berputar 90° membuka palang selama 5 detik, lalu menutup otomatis.
   - Jika warga **belum di-ACC RW** atau unverified, LCD menampilkan:
     `AKSES DITOLAK!` / `BELUM DI-ACC RW` & servo tetap terkunci di 0°.
2. **Buka Palang via Tombol HP Warga:**
   - Saat warga menekan tombol hijau di portal web HP `http://.../warga`, server mencatat perintah buka.
   - Dalam 1-2 detik saat ESP32 melakukan *ping*, servo otomatis membuka palang secara nirkabel!

