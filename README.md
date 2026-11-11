# 🏙️ E-WARGA - Smart Community & IoT Living Ecosystem
> **Platform Manajemen RT/RW & Smart Cluster Terintegrasi Berbasis Web (Laravel 12) & Mobile Apps (Android & iOS).**

---

## 📚 Indeks Dokumentasi Proyek (Master Documentation)

Dokumentasi teknis proyek **E-WARGA** telah dipisahkan secara mendalam agar rapi dan fokus:

| Dokumen | Deskripsi & Isi Utama |
| :--- | :--- |
| 📘 **[README.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/README.md)** *(File Ini)* | **Master Blueprint**: Visi bisnis, struktur role (RBAC), roadmap pengembangan, dan arsitektur ekosistem. |
| 📡 **[docs/API_DOCUMENTATION.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/API_DOCUMENTATION.md)** | **Dokumentasi REST API (Pemisahan FE & BE)**: Spesifikasi endpoint JSON lengkap untuk integrasi Mobile App & SPA. |
| 🚀 **[docs/DEPLOYMENT_VPS.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/DEPLOYMENT_VPS.md)** | **Deploy VPS & Auto-Update GitHub CI/CD**: Setup domain, Nginx, SSL Let's Encrypt, dan otomatisasi update saat push. |
| ⚡ **[firmware/esp32_smart_gate/README.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/firmware/esp32_smart_gate/README.md)** | **Firmware IoT ESP32 Smart Gate**: Skema wiring hardware, library Arduino, dan pengujian RFID RC522 & Servo. |
| 🏘️ **[docs/DUAL_MODE.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/DUAL_MODE.md)** | **Dual-Mode Lingkungan**: Mode Perumahan / Cluster modern vs Mode Perkampungan / Desa / RT tradisional. |
| 🛡️ **[docs/JADWAL_KEAMANAN.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/JADWAL_KEAMANAN.md)** | **Modul Jadwal Keamanan**: Penugasan Satpam bulanan, presensi check-in, tukar shift, dan Siskamling Ronda Malam Warga. |
| 🩺 **[docs/POSYANDU.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/POSYANDU.md)** | **Modul Posyandu Digital**: Tracking demografi 5 kategori (balita, anak, ibu hamil, ibu/kader, lansia) & deteksi dini stunting. |
| 📜 **[docs/SURAT_PENGANTAR.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/SURAT_PENGANTAR.md)** | **Surat Pengantar RT Online**: Pengajuan digital, persetujuan RT 1-klik, penomoran resmi, QR Code verifikasi anti-pemalsuan & cetak. |
| 🖼️ **[docs/BRANDING_PICTURE.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/BRANDING_PICTURE.md)** | **Brand & Visual Blueprint**: Logo resmi, App Icon mobile (1:1), filosofi visual, dan palet warna (Clean PropTech). |
| 🧠 **[docs/BACKEND.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/BACKEND.md)** | **Backend Blueprint**: Arsitektur Laravel 12, skema database lengkap, spesifikasi REST API, logika multi-tenancy, dan IoT gate protocol. |
| 🎨 **[docs/FRONTEND.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/FRONTEND.md)** | **Frontend & UX Blueprint**: Wireframe Web Desktop, antarmuka Mobile, visualisasi Chart.js keuangan, dan sistem tutorial (*Onboarding Tour*). |
| 🌓 **[docs/DARK_MODE.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/DARK_MODE.md)** | **Theme Blueprint (Dark & Light Mode)**: Arsitektur Tailwind CSS v4, pencegahan FOUC, tombol toggle reaktif Alpine.js, dan sinkronisasi Chart.js. |
| 🛠️ **[docs/TROUBLESHOOTING.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/TROUBLESHOOTING.md)** | **Problem Solving & Troubleshooting**: Katalog analisis bug, solusi pencegahan error, dan panduan teknis operasional. |

---

## 📌 1. Visi Produk & Filosofi Sistem

**E-WARGA** adalah platform *Smart Community* komprehensif yang dirancang untuk modernisasi tata kelola lingkungan perumahan/RT/RW di seluruh Indonesia. Sistem ini menggabungkan:
1. **Administrasi & Keuangan Transparan:** Memudahkan warga dan pengurus mencatat serta memantau kas lingkungan tanpa curiga.
2. **Keamanan Fisik & Digital Terintegrasi:** Menghubungkan pos satpam dengan palang pintu otomatis (*Smart Gate*) dan sistem izin tamu (*Visitor Management*).
3. **Ramah Pengguna Awam (Gaptek-Friendly):** Tampilan dengan ukuran tulisan besar, alur seperti WhatsApp, dan bantuan tutorial interaktif di setiap langkah.
4. **Model Bisnis SaaS + Managed IoT:** E-WARGA menyediakan software sekaligus dukungan teknisi hardware terkelola untuk perumahan di seluruh Indonesia.

---

## 👥 2. Struktur Organisasi & Role-Based Access Control (RBAC)

```text
                  SUPER ADMIN (Penyedia Platform & Teknisi Pusat)
                                      │
                         PENGURUS RW / PAGUYUBAN (Opsional)
                                      │
                            KETUA RT (Approver Utama)
                                      │
            ┌─────────────────────────┼─────────────────────────┐
            │                         │                         │
      SEKRETARIS RT             BENDAHARA RT             OPERASIONAL POS
    - Data Warga              - Iuran Kas & Tagihan    - SATPAM (Gerbang,
    - Surat Pengantar         - Pembukuan & Rekap       Log Tamu, Pantau CCTV)
    - Pengumuman Warga        - Bukti Transfer
──────────────────────────────────────────────────────────────────────────
                                 WARGA
               ┌───────────────────────┴───────────────────────┐
       KEPALA KELUARGA (KK)                    ANGGOTA KELUARGA / PENYEWA
    - Pembayaran Iuran Rumah               - Buka Gerbang via HP
    - Voting & Hak Suara                   - Buat Izin Tamu / Kurir
    - Kelola Anggota Rumah                 - Tombol Darurat (Panic Button)
──────────────────────────────────────────────────────────────────────────
                                  TAMU
    - Menerima Pass Akses (QR Code Sementara dari Warga / Pos Satpam)
```

---

## 🗺️ 3. Roadmap Pengembangan (Development Phases)

### 📍 Fase 1: Fondasi Sistem & Web Desktop (Target Saat Ini)
* **Lingkungan:** Lokal Windows (Localhost via Laravel 12 & MySQL/SQLite).
* **Fokus Pengerjaan:**
  * Scaffold project Laravel 12 + Tailwind CSS + Alpine.js/Blade.
  * Autentikasi multi-role (Super Admin, Ketua RT, Bendahara, Sekretaris, Satpam, Warga).
  * Panel Pendaftaran Komplek/Perumahan Baru (Landing Page).
  * Modul Manajemen Warga & Rumah (Blok/Nomor Rumah).
  * Modul Pembukuan & Iuran Kas RT (Upload Bukti Bayar & Transparansi Saldo).
  * **Virtual Smart Gate Simulator:** Layar simulasi di browser untuk menguji respon buka/tutup palang pintu secara lokal.

### 📍 Fase 2: Integrasi Hardware Smart Gate & REST API
* Integrasi mikrokontroler (ESP32 / Relay Module / RFID Reader) dengan backend Laravel via protokol HTTP Webhook / MQTT.
* Log buka pintu otomatis (Warga Tap RFID / Klik Buka di HP / Scan QR).
* Pembuatan endpoint REST API standar (Laravel Sanctum) untuk konsumsi mobile apps.

### 📍 Fase 3: Aplikasi Mobile (Android & iOS)
* Pengembangan aplikasi mobile native-cross platform (Flutter).
* Fitur Warga: Buka gerbang 1-klik, buat tiket tamu via WhatsApp, tombol panik, bayar iuran via QRIS.
* Fitur Satpam: Mode Pos Jaga (Scanner QR tamu, monitoring lalu lintas kendaraan masuk/keluar).

### 📍 Fase 4: Cloud Deployment & Ekspansi Nasional (SaaS Production)
* Deployment ke Cloud VPS / Server Production.
* Integrasi Payment Gateway resmi (Midtrans/Xendit) untuk auto-reconciliation iuran kas RT.
* Ekspansi katalog sensor & perangkat pintar lanjutan (*Coming Soon Sensors*).

---

## 🚨 4. Katalog Fitur IoT: Rilis Utama (MVP) vs Coming Soon

```
[ AKTIF / FASE MVP ]
  └── 🟢 Smart Barrier Gate (Palang Pintu Otomatis)
      - Buka palang via tombol aplikasi HP warga
      - Akses kartu RFID (offline fallback saat internet mati)
      - Dynamic QR Code untuk akses Kurir Paket & Tamu Warga
      - Virtual Gate Simulator untuk pengujian di komputer lokal

[ COMING SOON / ROADMAP PERANGKAT PINTAR ]
  ├── 🟡 1. Smart CCTV AI & ANPR (Automatic Number Plate Recognition)
  ├── 🟡 2. Public Panic Button Pole (Tiang Tombol Darurat Fisik Lingkungan)
  ├── 🟡 3. Smart Flood & Drainage Sensor (Sensor Banjir / Saluran Air)
  ├── 🟡 4. Smart Street Lighting (PJU Pintar Hemat Energi)
  ├── 🟡 5. Smart Waste Bin Monitor (Sensor Volume Bak Sampah TPS)
  └── 🟡 6. Smart Sub-Meter Listrik & Air Fasilitas Umum
```

### 📋 Rincian Fitur Sensor & Hardware "Coming Soon":

#### 1. Smart CCTV AI & ANPR (Deteksi Plat Nomor Otomatis)
* **Konsep:** Kamera IP di gerbang masuk membaca plat nomor kendaraan warga secara instan.
* **Fungsi:** Jika plat terdaftar di database warga aktif, palang terbuka otomatis tanpa warga perlu mengeluarkan kartu atau HP.

#### 2. Public Panic Button Pole (Tiang Tombol Darurat Fisik)
* **Konsep:** Tiang fisik berwarna merah mencolok yang diletakkan di area taman, sudut rawan, atau balai RT.
* **Fungsi:** Jika ditekan saat darurat (pencuri, kebakaran), sirine strobo di tiang berbunyi kencang, sirine di pos satpam berdering, dan seluruh HP pengurus mendapat notifikasi darurat.

#### 3. Smart Flood & Drainage Sensor (Sensor Ketinggian Air Got/Sungai)
* **Konsep:** Sensor ultrasonik tahan air dipasang di bibir saluran pembuangan utama komplek.
* **Fungsi:** Mengukur elevasi air saat hujan deras. Jika mencapai batas bahaya, sistem memicu peringatan dini (*Flood Alert*) ke seluruh ponsel warga.

#### 4. Smart Street Lighting / PJU Pintar
* **Konsep:** Otomatisasi lampu penerangan jalan berbasis sensor intensitas cahaya (LDR) dan jam operasional.
* **Fungsi:** Menyalakan lampu saat senja dan meredupkannya saat larut malam untuk memangkas tagihan listrik bersama.

#### 5. Smart Waste Bin Monitor (Sensor Bak Sampah TPS)
* **Konsep:** Sensor jarak inframerah pada penampungan sampah sementara lingkungan.
* **Fungsi:** Alert otomatis ke pengurus/petugas kebersihan saat kapasitas sampah mencapai 90% sebelum meluber.

#### 6. Smart Sub-Meter Listrik & Air Fasum
* **Konsep:** Pembacaan digital beban listrik balai RT dan pompa air bersama.
* **Fungsi:** Memantau konsumsi energi dan mendeteksi anomali (misal: pipa air bocor atau indikasi korsleting).

---

## 🛠️ 5. Ringkasan Spesifikasi Teknologi (Tech Stack)

* **Server / Web Framework:** Laravel 12 (PHP 8.2+)
* **Database:** MySQL / SQLite (Multi-Tenancy Ready)
* **Frontend Web:** Tailwind CSS + Alpine.js / Blade Components
* **Real-time & IoT Bridge:** Laravel Events, WebSockets / HTTP Webhooks
* **Simulator:** Web-based Virtual Barrier Gate Canvas/CSS Animation
* **Target Mobile (Fase 3):** Flutter (Dart) untuk Android & iOS

---

## 📚 6. Daftar Dokumentasi Teknis & Panduan Modul (.md)

Setiap fitur dan subsistem pada E-WARGA didokumentasikan dalam file markdown khusus:

| Dokumen | Topik / Modul | Deskripsi |
| :--- | :--- | :--- |
| [docs/API_DOCUMENTATION.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/API_DOCUMENTATION.md) | **Dokumentasi REST API (Pemisahan FE & BE)** | Spesifikasi endpoint JSON lengkap untuk integrasi Mobile App (Flutter/RN) & SPA. |
| [docs/DEPLOYMENT_VPS.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/DEPLOYMENT_VPS.md) | **Deploy VPS & Auto-Update CI/CD** | Setup domain, Nginx, SSL Let's Encrypt, dan otomatisasi GitHub Actions saat push. |
| [firmware/esp32_smart_gate/README.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/firmware/esp32_smart_gate/README.md) | **Firmware IoT ESP32 Smart Gate** | Panduan hardware mikrokontroler ESP32, RFID RC522, Servo SG90, LCD I2C & REST API. |
| [docs/SIDEBAR_UI.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/SIDEBAR_UI.md) | **Desktop Sidebar & Landing Page** | Arsitektur UI/UX desktop-first (`w-72`), navigasi terkelompok, dan landing page konversi B2B/klien. |
| [docs/FRONTEND.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/FRONTEND.md) | **Frontend & UI/UX Dasar** | Filosofi gaptek-friendly, palet warna, tipografi, dan wireframe awal. |
| [docs/BACKEND.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/BACKEND.md) | **Backend Architecture & Database** | ERD skema relasi, arsitektur multi-tenancy, dan integrasi hardware smart gate. |
| [docs/DARK_MODE.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/DARK_MODE.md) | **Dark & Light Mode Engine** | Spesifikasi tema kontras tinggi, anti-FOUC, dan integrasi reaktif Chart.js. |
| [docs/DUAL_MODE.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/DUAL_MODE.md) | **Dual Environment (Cluster vs Guyub)** | Toggle 1-klik komplek perumahan modern vs rukun warga perkampungan. |
| [docs/POSYANDU.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/POSYANDU.md) | **Posyandu & Stunting Tracking** | Pencatatan balita, ibu hamil, lansia, imunisasi, dan deteksi dini stunting. |
| [docs/SURAT_PENGANTAR.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/SURAT_PENGANTAR.md) | **Surat Pengantar RT Online** | Alur permohonan digital, verifikasi TTD Ketua RT, dan verifikasi QR publik. |
| [docs/JADWAL_KEAMANAN.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/JADWAL_KEAMANAN.md) | **Jadwal Keamanan & Siskamling** | Shift satpam komplek vs regu ronda malam warga perkampungan. |
| [docs/TROUBLESHOOTING.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/TROUBLESHOOTING.md) | **Problem Solving & Troubleshooting** | 12 log audit bug/masalah, solusi perbaikan kode, dan panduan teknis operasional. |
| [docs/BRANDING_PICTURE.md](file:///c:/Users/fabia/OneDrive/Documents/E-WARGA/docs/BRANDING_PICTURE.md) | **Brand & Visual Assets** | Spesifikasi visual, logo aplikasi, favicon, dan maskot "Bang Warga". |

---
*Silakan baca dokumen di atas sesuai modul yang sedang dipelajari atau dikembangkan.*
