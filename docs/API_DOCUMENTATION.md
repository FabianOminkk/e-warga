# 📡 E-WARGA - Dokumentasi REST API (Pemisahan Backend & Frontend)

> **Arsitektur API-First (Decoupled Layer)**: Backend menyajikan data murni dalam format JSON melalui endpoint REST API standar, siap dikonsumsi oleh aplikasi mobile (Flutter / React Native untuk Android & iOS) atau Frontend terpisah (Vue / React SPA), sementara Frontend Web berbasis Blade + Tailwind CSS tetap berjalan normal tanpa merusak struktur sistem yang ada.

---

## 🏛️ Arsitektur Pemisahan FE dan BE

```text
                                  ┌───────────────────────────────┐
                                  │   CLIENTS / FRONTEND (FE)     │
                                  ├───────────────────────────────┤
                                  │ 1. Web Portal (Blade + Alpine)│
                                  │ 2. Mobile Apps (Flutter/RN)   │
                                  │ 3. Perangkat IoT (ESP32)      │
                                  └───────────────┬───────────────┘
                                                  │
                                   HTTP / REST API │ (JSON Body / Bearer Token)
                                                  ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           BACKEND ENGINE (LARAVEL 12)                           │
├─────────────────────────────────────────────────────────────────────────────────┤
│  Routing Layer         : routes/api.php (v1 REST API)                           │
│  Security Layer        : App\Http\Middleware\ApiTokenAuthenticate (Bearer Auth) │
│  Controllers (API)     : App\Http\Controllers\Api\* (Return Standard JSON)      │
│  Data & Business Logic : App\Models\* (Multi-Tenant Eloquent ORM)               │
│  Database Storage      : SQLite / MySQL / PostgreSQL                            │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🔐 Standar Header & Autentikasi

### Base URL:
- **Lokal:** `http://localhost:8000/api/v1`
- **Produksi (VPS):** `https://namadomain.com/api/v1`

### Request Headers Wajib:
```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer <TOKEN_DARI_LOGIN>
```

### Format Standar Respon JSON:
```json
{
  "success": true,
  "message": "Operasi berhasil",
  "data": { ... }
}
```

---

## 📋 Katalog Endpoint Lengkap

### 1. Autentikasi & Profil Pengguna (`/auth`)

| Method | Endpoint | Akses | Deskripsi |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/auth/login` | Public | Login via email/nomor HP + password, mengembalikan Bearer Token (berlaku 30 hari). |
| `GET` | `/api/v1/auth/me` | Bearer Token | Mengambil detail profil pengguna aktif, rumah, kendaraan, dan tenant. |
| `POST` | `/api/v1/auth/logout` | Bearer Token | Mengakhiri sesi dan mencabut token otentikasi. |

#### Contoh Request Login (`POST /api/v1/auth/login`):
```json
{
  "login": "bambang@ewarga.id",
  "password": "password"
}
```

#### Contoh Respon:
```json
{
  "success": true,
  "message": "Login berhasil.",
  "data": {
    "token": "ew_9f82k3...randomtoken",
    "token_type": "Bearer",
    "expires_in": 2592000,
    "user": {
      "id": 1,
      "name": "Bambang Sudarsono",
      "email": "bambang@ewarga.id",
      "role": "warga",
      "verification_status": "approved"
    }
  }
}
```

---

### 2. Portal Warga Mandiri (`/warga`)

| Method | Endpoint | Akses | Deskripsi |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/warga/portal` | Warga | Data lengkap beranda aplikasi: status iuran, tamu aktif, log gerbang, & surat. |
| `POST` | `/api/v1/warga/open-gate` | Warga | Buka palang gerbang secara nirkabel dari tombol HP (terhubung ke ESP32). |
| `POST` | `/api/v1/warga/visitors` | Warga | Membuat tiket tamu / kurir paket dengan kode QR akses. |
| `POST` | `/api/v1/warga/panic` | Warga | Memancarkan sinyal darurat (Panic Button) ke Pos Satpam & Pengurus RT. |
| `GET` | `/api/v1/warga/bills` | Warga | Riwayat tagihan kas dan iuran kebersihan rumah warga. |
| `POST` | `/api/v1/warga/profile` | Warga | Memperbarui nama, no HP, email, dan pekerjaan. |
| `POST` | `/api/v1/warga/change-password` | Warga | Mengganti kata sandi akun warga. |

#### Contoh Buka Gerbang (`POST /api/v1/warga/open-gate`):
```json
{
  "success": true,
  "message": "Perintah buka palang gerbang berhasil dikirim ke perangkat IoT.",
  "data": {
    "gate_name": "Gerbang Utama",
    "hold_duration_seconds": 5
  }
}
```

---

### 3. Konsol Pos Satpam & Penjagaan (`/satpam`)

| Method | Endpoint | Akses | Deskripsi |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/satpam/console` | Satpam / RT | Memuat status gerbang, tamu yang dinantikan, dan alert panik aktif. |
| `POST` | `/api/v1/satpam/trigger-manual` | Satpam | Buka palang pintu secara manual dengan catatan alasan. |
| `POST` | `/api/v1/satpam/checkin-visitor` | Satpam | Check-in tamu via scan token QR tiket atau ID tamu. |

---

### 4. Perangkat IoT Machine-to-Machine (`/gate`)

*Endpoint ini tanpa proteksi session/CSRF khusus untuk mikrokontroler hardware.*

| Method | Endpoint | Akses | Deskripsi |
| :--- | :--- | :--- | :--- |
| `GET / POST` | `/api/v1/gate/ping` | ESP32 | Heartbeat mikrokontroler & mendengarkan perintah buka jarak jauh. |
| `POST` | `/api/v1/gate/scan-rfid` | RFID RC522 | Validasi kartu E-KTP / kartu RFID warga (ACC Ketua RW). |
| `POST` | `/api/v1/gate/scan-qr` | Barcode Scanner | Validasi kode QR tiket tamu. |

---

### 5. Dashboard Eksekutif & Kas Keuangan (`/dashboard`)

| Method | Endpoint | Akses | Deskripsi |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/dashboard/summary` | Pengurus / RW | Ringkasan statistik rumah, saldo kas, tagihan, & log akses. |
| `GET` | `/api/v1/dashboard/cashbook` | Semua Role | Riwayat transaksi kas masuk & keluar dengan filter dan paginasi. |
| `POST` | `/api/v1/dashboard/cashbook` | Bendahara / RT | Mencatat transaksi pemasukan atau pengeluaran baru. |
| `POST` | `/api/v1/dashboard/payments/{id}/verify` | Bendahara | Menyetujui atau menolak bukti transfer iuran warga. |
| `GET` | `/api/v1/dashboard/verifications` | Ketua RW | Daftar pengajuan verifikasi E-KTP warga. |
| `POST` | `/api/v1/dashboard/verifications/{id}/approve` | Ketua RW | Menyetujui KTP warga & mengaktifkan kartu RFID di gerbang. |
| `POST` | `/api/v1/dashboard/verifications/{id}/reject` | Ketua RW | Menolak pengajuan verifikasi KTP dengan catatan perbaikan. |

---

### 6. Jadwal Keamanan & Siskamling Ronda (`/schedules`)

| Method | Endpoint | Akses | Deskripsi |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/schedules` | Semua Role | Mengambil jadwal dinas bulanan & siapa yang bertugas malam ini. |
| `POST` | `/api/v1/schedules` | Pengurus | Menambahkan jadwal piket satpam / ronda warga. |
| `POST` | `/api/v1/schedules/{id}/checkin` | Petugas | Presensi kehadiran check-in di pos jaga. |
| `POST` | `/api/v1/schedules/{id}/swap` | Petugas | Permohonan tukar shift ronda dengan petugas pengganti. |
| `POST` | `/api/v1/schedules/toggle-mode` | Super Admin / RT | Beralih antara mode Perumahan (Cluster) vs Mode Desa/Kampung (Guyub). |

---

### 7. Posyandu Digital & Deteksi Dini Stunting (`/posyandu`)

| Method | Endpoint | Akses | Deskripsi |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/posyandu` | Semua Role | Statistik 5 kategori (balita, anak, ibu hamil, ibu, lansia) & deteksi stunting. |
| `POST` | `/api/v1/posyandu/members` | Kader / RT | Mendaftarkan warga sasaran posyandu baru. |
| `POST` | `/api/v1/posyandu/records` | Kader / RT | Mencatat data penimbangan, tinggi, gizi, vitamin, & kalkulasi stunting. |
| `DELETE` | `/api/v1/posyandu/members/{id}` | Kader / RT | Menghapus data anggota sasaran posyandu. |

---

### 8. Layanan Surat Pengantar RT Online (`/letters`)

| Method | Endpoint | Akses | Deskripsi |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/letters` | Semua Role | Daftar pengajuan surat pengantar & statistik status (pending, disetujui, ditolak). |
| `POST` | `/api/v1/letters` | Warga | Mengajukan surat pengantar baru (KTP, KK, Domisili, SKTM, dll). |
| `POST` | `/api/v1/letters/{id}/approve` | Ketua RT | Menandatangani surat secara digital & menerbitkan nomor resmi. |
| `POST` | `/api/v1/letters/{id}/reject` | Ketua RT | Menolak permohonan surat dengan alasan spesifik. |
| `GET` | `/api/v1/letters/verify/{code}` | Publik (Bebas) | Verifikasi keaslian surat melalui scan kode QR oleh instansi kelurahan/kecamatan. |

---

## 💡 Panduan Integrasi Mobile Developer (Flutter / React Native)

Untuk menghubungkan aplikasi mobile baru ke E-WARGA Backend:
1. Panggil `POST /api/v1/auth/login` dan simpan token di Secure Storage (misal: `flutter_secure_storage`).
2. Setiap kali memanggil endpoint lain, sertakan header:
   ```dart
   options: Options(
     headers: {
       'Accept': 'application/json',
       'Authorization': 'Bearer $token',
     },
   )
   ```
3. Backend telah dilengkapi penanganan token otomatis, rate limiting, dan format exception standar JSON.

