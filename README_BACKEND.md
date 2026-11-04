# 🧠 E-WARGA - Technical Blueprint: Backend (BE)
> **Spesifikasi Teknis & Arsitektur Server Berbasis Laravel 12 (PHP 8.2+)**

Dokumen ini adalah acuan arsitektur backend untuk platform **E-WARGA**, mencakup arsitektur multi-tenant, desain database relasional, spesifikasi REST API, logika bisnis per-modul, dan protokol komunikasi IoT (Palang Pintu Otomatis).

---

## 🏛️ 1. Pola Arsitektur (Architectural Pattern)

Backend dibangun menggunakan pola **Modular Monolith** dengan pendekatan **Multi-Tenancy (Row-Level Isolation)**:
* **Multi-Tenancy:** Setiap data perumahan/RT diisolasi secara ketat menggunakan `tenant_id`. Query database otomatis menyaring data berdasarkan komplek yang sedang aktif (`Global Scope` di Laravel Eloquent).
* **Service-Repository Pattern:** Memisahkan logika bisnis kompleks (seperti kalkulasi kas, approval tagihan, validasi token pintu) dari Controller, sehingga kode mudah di-maintain dan siap untuk unit testing.
* **Event-Driven Architecture:** Setiap aksi kritis (misal: gerbang dibuka, tombol panik ditekan, bukti bayar di-upload) memicu event internal (`Event` & `Listener`) untuk memproses audit log, broadcast notifikasi, dan trigger hardware.

---

## 🗄️ 2. Desain Database & Skema Lengkap (ERD Blueprint)

### A. Tabel Core & Multi-Tenancy
```sql
-- 1. Tabel Komplek / Lingkungan (Tenants)
CREATE TABLE tenants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,              -- Contoh: "Cluster Melati Mas"
    slug VARCHAR(100) UNIQUE NOT NULL,       -- "cluster-melati-mas"
    rt_rw VARCHAR(20) NOT NULL,              -- "RT 05 / RW 12"
    province VARCHAR(100),
    city VARCHAR(100),
    district VARCHAR(100),
    postal_code VARCHAR(10),
    subscription_plan ENUM('starter', 'pro', 'enterprise') DEFAULT 'starter',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- 2. Tabel Unit Rumah
CREATE TABLE houses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    block_name VARCHAR(10) NOT NULL,         -- "Blok A"
    house_number VARCHAR(10) NOT NULL,       -- "12"
    full_address TEXT NULL,
    occupancy_status ENUM('occupied', 'vacant', 'rented') DEFAULT 'occupied',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY tenant_house_idx (tenant_id, block_name, house_number)
);
```

### B. Tabel Pengguna & Autentikasi (RBAC)
```sql
-- 3. Tabel Users
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NULL,          -- NULL untuk Super Admin Platform
    house_id BIGINT UNSIGNED NULL,           -- NULL untuk Satpam / Admin yang bukan warga
    name VARCHAR(150) NOT NULL,
    phone_number VARCHAR(20) UNIQUE NOT NULL,-- Kunci login utama via WhatsApp OTP
    email VARCHAR(150) NULL,
    password VARCHAR(255) NOT NULL,          -- Default 'admin123' untuk staf; Warga tentukan sendiri
    role ENUM(
        'super_admin',                       -- Tim Kamu (Platform Provider)
        'ketua_rt',                          -- Approver Utama Lingkungan
        'sekretaris',                        -- Pengelola Data Warga & Surat
        'bendahara',                         -- Pengelola Keuangan & Verifikasi Iuran
        'satpam',                            -- Pos Jaga & Scanner Tamu
        'warga'                              -- Penduduk (KK / Anggota)
    ) NOT NULL DEFAULT 'warga',
    is_family_head BOOLEAN DEFAULT FALSE,    -- TRUE = Penanggung jawab tagihan rumah
    nik VARCHAR(20) NULL,                    -- Nomor Induk Kependudukan (Terenkripsi)
    status ENUM('active', 'inactive', 'pending_approval') DEFAULT 'pending_approval',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (house_id) REFERENCES houses(id) ON DELETE SET NULL
);

-- Kebijakan Kredensial Default (Seeder Policy):
-- 1. Seluruh Akun Pengurus & Staf (Super Admin, Ketua RT, Sekretaris, Bendahara, Satpam):
--    -> Password default di-set otomatis menjadi: "admin123"
-- 2. Akun Warga (Kepala Keluarga & Anggota):
--    -> Password bebas ditentukan sendiri oleh masing-masing warga saat aktivasi/pendaftaran mandiri.

-- 4. Tabel Kendaraan Warga (Untuk Validasi Satpam & ANPR CCTV)
CREATE TABLE vehicles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    house_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    license_plate VARCHAR(20) NOT NULL,      -- "B 1234 ABC" (disimpan uppercase tanpa spasi)
    type ENUM('car', 'motorcycle', 'bicycle') NOT NULL,
    brand_model VARCHAR(100) NULL,           -- "Toyota Avanza Hitam"
    rfid_card_uid VARCHAR(50) UNIQUE NULL,   -- UID Kartu RFID fisik (jika ada)
    created_at TIMESTAMP NULL,
    FOREIGN KEY (house_id) REFERENCES houses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### C. Tabel IoT & Palang Pintu Otomatis (Smart Gate)
```sql
-- 5. Tabel Gerbang Pintar
CREATE TABLE smart_gates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,              -- "Gerbang Masuk Utama Pos Timur"
    device_uid VARCHAR(100) UNIQUE NOT NULL, -- Serial Number Hardware (ESP32/Gateway)
    secret_token VARCHAR(255) NOT NULL,      -- Token HMAC otentikasi hardware
    ip_address VARCHAR(50) NULL,
    status ENUM('online', 'offline', 'maintenance') DEFAULT 'offline',
    last_ping_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- 6. Tabel Log Akses Palang Pintu
CREATE TABLE gate_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    smart_gate_id BIGINT UNSIGNED NOT NULL,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,            -- Terisi jika warga yang membuka
    visitor_id BIGINT UNSIGNED NULL,         -- Terisi jika tamu/kurir yang lewat
    access_method ENUM('mobile_button', 'rfid_card', 'qr_code', 'satpam_manual', 'anpr_cctv') NOT NULL,
    direction ENUM('entry', 'exit') NOT NULL DEFAULT 'entry',
    status ENUM('granted', 'denied') NOT NULL DEFAULT 'granted',
    reason VARCHAR(255) NULL,                -- Misal: "Tunggakan iuran > 3 bulan"
    snapshot_image_url VARCHAR(255) NULL,    -- Foto kamera gerbang saat terbuka
    triggered_at TIMESTAMP NOT NULL,
    FOREIGN KEY (smart_gate_id) REFERENCES smart_gates(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### D. Tabel Keuangan & Iuran (Billing & Ledger)
```sql
-- 7. Tabel Tagihan Iuran Bulanan Rumah (IPL)
CREATE TABLE billings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    house_id BIGINT UNSIGNED NOT NULL,
    billing_period DATE NOT NULL,            -- "2026-11-01" (Bulan Tagihan)
    amount DECIMAL(12,2) NOT NULL,           -- Misal: 150000.00
    description VARCHAR(255) NULL,           -- "Iuran Kebersihan, Keamanan, & Kas RT"
    status ENUM('unpaid', 'pending_verification', 'paid', 'overdue') DEFAULT 'unpaid',
    due_date DATE NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (house_id) REFERENCES houses(id) ON DELETE CASCADE,
    UNIQUE KEY tenant_house_period (tenant_id, house_id, billing_period)
);

-- 8. Tabel Pembayaran & Bukti Transfer
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    billing_id BIGINT UNSIGNED NOT NULL,
    payer_user_id BIGINT UNSIGNED NOT NULL,
    payment_method ENUM('manual_transfer', 'qris', 'cash') DEFAULT 'manual_transfer',
    proof_image VARCHAR(255) NULL,           -- URL gambar struk transfer bank
    amount_paid DECIMAL(12,2) NOT NULL,
    verified_by_user_id BIGINT UNSIGNED NULL,-- Bendahara yang memverifikasi
    verified_at TIMESTAMP NULL,
    rejection_reason VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (billing_id) REFERENCES billings(id) ON DELETE CASCADE,
    FOREIGN KEY (payer_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 9. Tabel Buku Kas Umum (Transparansi Saldo Lingkungan)
CREATE TABLE cash_books (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    created_by_user_id BIGINT UNSIGNED NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    category VARCHAR(100) NOT NULL,          -- "Iuran Warga", "Gaji Satpam", "Perbaikan Lampu"
    title VARCHAR(200) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    transaction_date DATE NOT NULL,
    receipt_image VARCHAR(255) NULL,         -- Bukti nota pembelian/kuitansi
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### E. Tabel Tamu (Visitor Management System)
```sql
-- 10. Tabel Izin Tamu & Kurir
CREATE TABLE visitors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    host_house_id BIGINT UNSIGNED NOT NULL,
    invited_by_user_id BIGINT UNSIGNED NOT NULL,
    guest_name VARCHAR(150) NOT NULL,
    guest_type ENUM('guest', 'courier', 'ride_hailing', 'contractor') DEFAULT 'guest',
    vehicle_plate VARCHAR(20) NULL,
    qr_token VARCHAR(64) UNIQUE NOT NULL,    -- Token acak untuk QR Code akses masuk
    valid_from TIMESTAMP NOT NULL,
    valid_until TIMESTAMP NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    checked_in_by_satpam_id BIGINT UNSIGNED NULL,
    checked_in_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (host_house_id) REFERENCES houses(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (checked_in_by_satpam_id) REFERENCES users(id) ON DELETE SET NULL
);
```

---

## 📡 3. Spesifikasi REST API (Core Endpoints)

Semua endpoint API dilindungi oleh middleware **Laravel Sanctum**. Respon standar selalu dibungkus dalam format:
```json
{
  "success": true,
  "message": "Deskripsi respon",
  "data": { ... }
}
```

### A. Autentikasi & Profil
| Method | Endpoint | Role | Fungsi |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/auth/request-otp` | Public | Kirim 4-digit OTP ke nomor WhatsApp warga |
| `POST` | `/api/v1/auth/verify-otp` | Public | Verifikasi OTP & terbitkan Bearer Token |
| `GET` | `/api/v1/auth/me` | All Roles | Mendapatkan detail profil & data rumah aktif |

### B. Palang Pintu Otomatis (Smart Gate Engine)
#### `POST /api/v1/gate/trigger-open`
* **Headers:** `Authorization: Bearer <TOKEN>`
* **Request Body:**
  ```json
  {
    "gate_id": 1
  }
  ```
* **Logika Backend:**
  1. Validasi apakah `user` berada di perumahan yang sama dengan `gate_id`.
  2. Periksa apakah status rumah warga aktif.
  3. *(Opsional)* Cek apakah tunggakan iuran rumah melampaui aturan RT.
  4. Simpan record di `gate_logs` dengan status `granted`.
  5. Kirim event websocket/HTTP webhook ke hardware ESP32 / Simulator browser.
* **Response 200 OK:**
  ```json
  {
    "success": true,
    "message": "Palang gerbang berhasil dibuka. Silakan masuk.",
    "data": {
      "log_id": 1042,
      "gate_name": "Gerbang Utama",
      "hold_duration_seconds": 5
    }
  }
  ```

#### `POST /api/v1/gate/scan-visitor-qr` (Akses Satpam)
* **Headers:** `Authorization: Bearer <TOKEN_SATPAM>`
* **Request Body:**
  ```json
  {
    "qr_token": "a8fbc730e42d9910c"
  }
  ```
* **Logika Backend:**
  1. Validasi token QR di tabel `visitors`.
  2. Pastikan waktu saat ini berada di antara `valid_from` dan `valid_until`.
  3. Pastikan `is_used` masih `FALSE`.
  4. Tandai `is_used = TRUE`, catat jam check-in satpam, dan picu palang terbuka.

### C. Pembayaran & Iuran Kas
| Method | Endpoint | Role | Fungsi |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/billings/my-bills` | Warga (KK) | Riwayat tagihan iuran rumahnya |
| `POST` | `/api/v1/billings/{id}/pay` | Warga (KK) | Upload foto struk bukti transfer |
| `GET` | `/api/v1/cashbook/summary` | Semua Warga | Transparansi total kas RT & pengeluaran |
| `POST` | `/api/v1/payments/{id}/verify`| Bendahara | Approve / Reject bukti pembayaran |

---

## ⚡ 4. Protokol Komunikasi IoT (Hardware & Simulator)

Palang pintu otomatis membutuhkan latensi respon di bawah 1 detik dari saat tombol di HP ditekan sampai motor palang bergerak.

```text
[HP Warga / Satpam]
        │
        ▼ (HTTPS REST POST /api/v1/gate/trigger-open)
[Laravel 12 Backend]
        │
        ├── Simpan Log ke MySQL (gate_logs)
        │
        ▼ (Protokol Webhook Lokal / WebSocket Reverb)
[Hardware Gateway / Simulator Browser]
        │
        ▼ (Sinyal Digital Pin GPIO HIGH selama 500ms)
[Relay Module ➔ Motor Palang Terbuka]
```

### Format Payload ke Hardware Mikrokontroler (ESP32):
```json
{
  "event": "TRIGGER_GATE_OPEN",
  "device_uid": "ESP32-GATE-01",
  "pulse_duration_ms": 500,
  "timestamp": 1793836800,
  "signature": "hmac_sha256_hash_here"
}
```

---

## 💻 5. Panduan Menjalankan Backend di Windows Lokal

1. **Prasyarat Sistem:**
   * PHP >= 8.2 (Ekstensi aktif: `pdo_mysql`, `curl`, `mbstring`, `openssl`).
   * Composer (Package Manager PHP).
   * MySQL / MariaDB (via Laragon atau XAMPP).
2. **Inisialisasi Project:**
   ```bash
   composer create-project laravel/laravel:^12.0 .
   composer require laravel/sanctum
   ```
3. **Konfigurasi Database `.env`:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ewarga_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```
4. **Jalankan Migrasi & Server Lokal:**
   ```bash
   php artisan migrate
   php artisan serve --host=0.0.0.0 --port=8000
   ```
   *(Argumen `--host=0.0.0.0` wajib agar server bisa dibuka oleh HP warga di jaringan Wi-Fi lokal yang sama).*

---

## 🔑 6. Kebijakan Seeder & Akun Uji Coba Default

Ketika perintah `php artisan db:seed` dijalankan untuk pertama kali, sistem akan otomatis membuat akun-akun pengurus contoh untuk memudahkan pengujian:

| Role | Username / No. HP | Password Default | Keterangan |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `08110000001` / `superadmin@ewarga.id` | `admin123` | Akses penuh seluruh tenant & teknisi |
| **Ketua RT** | `08110000002` / `ketuart@ewarga.id` | `admin123` | Approver utama lingkungan |
| **Sekretaris** | `08110000003` / `sekretaris@ewarga.id` | `admin123` | Pengelola data warga & surat |
| **Bendahara** | `08110000004` / `bendahara@ewarga.id` | `admin123` | Verifikator bukti bayar kas RT |
| **Satpam Pos**| `08110000005` / `satpam@ewarga.id` | `admin123` | Akses pos jaga & scanner tamu |
| **Warga** | Tergantung pendaftaran | **Ditentukan Warga Sendiri** | Bebas dibuat saat registrasi/aktivasi mandiri |


