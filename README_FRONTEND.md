# 🎨 E-WARGA - Technical Blueprint: Frontend (FE) & UX
> **Spesifikasi Antarmuka Pengguna, Wireframe, & Panduan Desain Ramah Warga Awam**

Dokumen ini adalah acuan arsitektur frontend untuk platform **E-WARGA**, mencakup tata letak Web Desktop (Fase 1), aplikasi Mobile (Fase 3), modul Simulator Palang Pintu, dan sistem *Onboarding Tour* interaktif.

---

## 🎯 1. Filosofi Desain: "Gaptek-Friendly & Elderly-Ready"

Mayoritas warga perumahan di Indonesia terdiri dari kepala keluarga paruh baya, ibu-ibu pengajian, dan satpam dengan berbagai tingkat keakraban teknologi. Oleh karena itu, prinsip utama frontend adalah:

1. **Ukuran Target Sentuh Besar (Minimum 56x56 px):**
   * Semua tombol aksi utama memiliki bantalan (*padding*) tebal sehingga jari besar atau tangan gemetar tidak salah memencet.
2. **Pola Desain "Seperti WhatsApp":**
   * Alur navigasi dibuat vertikal, kartu-kartu informasi sederhana tanpa animasi berlebihan yang membingungkan.
3. **Kontras Warna Tinggi (Rasio Minimal 4.5:1 - WCAG AAA):**
   * Menjamin keterbacaan tinggi saat warga berada di luar ruangan dengan terik sinar matahari (misal di depan gerbang portal).
4. **Bahasa Tanpa Jargon Komputer:**
   * Tidak ada istilah *Authentication, Session Timeout, Dynamic QR, Payload*.
   * Menggunakan bahasa sehari-hari: *"Buka Pintu"*, *"Undangan Tamu"*, *"Iuran Bulan Ini"*, *"Hubungi Pos Satpam"*.
5. **Umpan Balik Tiga Indera (Visual, Getar, Suara):**
   * Saat tombol gerbang ditekan, antarmuka memberi getaran pendek (*Haptic Feedback*), warna tombol berubah hijau terang, dan muncul suara konfirmasi pendek *"Ting!"*.

---

## 🎨 2. Palet Warna & Tipografi Resmi

| Nama Warna | Kode HEX | Peruntukan |
| :--- | :--- | :--- |
| **Emerald Community (Utama)** | `#059669` (Tailwind `emerald-600`) | Tombol aksi positif, Buka Gerbang, status LUNAS |
| **Deep Tactical Navy** | `#0F172A` (Tailwind `slate-900`) | Background Pos Satpam (Night mode) & Header Utama |
| **Alert Crimson** | `#DC2626` (Tailwind `red-600`) | Tombol Darurat (*Panic Button*), Status Belum Bayar |
| **Warm Warning Amber** | `#D97706` (Tailwind `amber-600`) | Status Menunggu Verifikasi Bendahara |
| **Soft Surface Gray** | `#F8FAFC` (Tailwind `slate-50`) | Latar belakang aplikasi (tidak membuat mata cepat lelah) |

* **Font Utama:** `Plus Jakarta Sans` atau `Inter` (Font sans-serif modern dengan keterbacaan tinggi pada layar kecil).
* **Ukuran Teks Minimum:** `16px` (1rem) untuk teks bodi, `20px - 28px` untuk teks instruksi tombol utama.

---

## 🖥️ 3. Modul & Wireframe Web Desktop (Fase 1)

Web desktop dibangun menggunakan **Tailwind CSS + Alpine.js / Blade Components** di atas Laravel 12.

### A. Landing Page & Form Pasang E-WARGA (Public View)
Halaman depan untuk memasarkan produk ke pengurus RT dan developer perumahan di seluruh Indonesia:
```text
┌────────────────────────────────────────────────────────────────────────┐
│  [E-WARGA Logo]             Fitur    Keamanan    Biaya    [ Masuk RT ] │
├────────────────────────────────────────────────────────────────────────┤
│                                                                        │
│       Transformasi Komplek Anda Menjadi Smart Community Modern         │
│     Kelola Iuran Transparan, Keamanan Satpam, & Palang Pintu Otomatis  │
│                                                                        │
│          [ 🚀 DAFTARKAN KOMPLEK KAMI / JADWALKAN SURVEI ]               │
│                                                                        │
│  ┌──────────────────────┐ ┌──────────────────────┐ ┌────────────────┐  │
│  │ 🚪 Palang Pintu Auto │ │ 💳 Kas RT Transparan │ │ 🎫 Izin Tamu WA│  │
│  │ Buka via HP & Kartu  │ │ Rekap otomatis       │ │ Scan QR Satpam │  │
│  └──────────────────────┘ └──────────────────────┘ └────────────────┘  │
└────────────────────────────────────────────────────────────────────────┘
```

---

### B. Console Pos Satpam (Desktop / Tablet Pos Jaga)
Didesain khusus untuk monitor komputer atau tablet di pos jaga satpam dengan tema **Dark Mode Tactical**:
```text
┌────────────────────────────────────────────────────────────────────────┐
│  POS JAGA: GERBANG UTAMA                       WAKTU: 14:35:10 WIB     │
├─────────────────────────────────────┬──────────────────────────────────┤
│ 🚗 LOG KENDARAAN TERBARU            │ 📷 SCANNER QR TAMU & KURIR       │
│                                     │                                  │
│ [14:34] Mobil - B 1892 KRF          │  ┌────────────────────────────┐  │
│         Bpk. Doni (Blok B-04)       │  │                            │  │
│         Status: Terbuka (Aplikasi)  │  │      [ KAMERA WEB SCAN ]   │  │
│                                     │  │      Arahkan QR HP Tamu    │  │
│ [14:28] Motor - B 4091 TTR          │  │                            │  │
│         Kurir J&T Express           │  └────────────────────────────┘  │
│         Status: Terbuka (QR Tamu)   │                                  │
│                                     │  [ 🟢 BUKA PALANG MANUAL ]       │
│ [14:15] Mobil - B 9981 ZAA          │  (Klik jika kondisi darurat)     │
│         Ibu Rini (Blok A-01)        │                                  │
│         Status: Terbuka (Kartu RFID)│  [ 🚨 BUNYIKAN SIRINE KOMPLEK ]  │
└─────────────────────────────────────┴──────────────────────────────────┘
```

---

### C. Dashboard Ketua RT & Bendahara
Fokus pada transparansi kas dan verifikasi data:
* **Statistik Cepat (Top Cards):**
  * Total Kas Bulan Ini: `Rp 18.450.000` (Pemasukan: Rp 22.000.000 | Pengeluaran: Rp 3.550.000).
  * Persentase Pembayaran Iuran: `85% Lunas` (120 dari 141 Rumah).
  * Kendaraan Masuk Hari Ini: `342 Kendaraan`.
* **Daftar Approval Pembayaran:**
  * Menampilkan antrian verifikasi foto struk transfer dari warga.
  * Tombol 1-klik: `[ ✅ Approve ]` atau `[ ❌ Tolak & Beri Alasan ]`.

---

### D. Virtual Smart Gate Simulator (Developer Tool)
Alat visual di browser untuk menguji respon palang pintu tanpa perlu menyambungkan alat fisik:
```text
┌────────────────────────────────────────────────────────────────────────┐
│  🧪 E-WARGA VIRTUAL GATE SIMULATOR (DEV ENVIRONMENT)                   │
├────────────────────────────────────────────────────────────────────────┤
│                                                                        │
│   STATUS GERBANG: [ 🟢 TERBUKA / OPEN ]                                │
│   DURASI TERBUKA: [ 3 Detik Tersisa... ]                               │
│                                                                        │
│           ┌──────────┐                                                 │
│           │ TIANG    │  \  \  \  (PALANG TERANGKAT KE ATAS 90 DERAJAT) │
│           │ GATE     │   \  \                                          │
│           │ [RFID]   │    \                                            │
│           └──────────┘                                                 │
│                                                                        │
│   LOG AKTIVITAS REAL-TIME:                                             │
│   - [14:40:02] Event Diterima: 'GateOpenedEvent'                       │
│   - [14:40:02] Warga: Bpk. Bambang Pamungkas (Rumah Blok A-12)         │
│   - [14:40:07] Motor Palang Turun Otomatis                             │
│                                                                        │
│   [ Tombol Uji Coba: Simulasikan Tap Kartu RFID Warga ]                │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 📱 4. Antarmuka Mobile Warga (Fase 3 - Flutter)

Tampilan mobile difokuskan pada kesederhanaan ekstrim:

### A. Layar Beranda (Home Screen)
```text
┌──────────────────────────────────────────┐
│  E-WARGA                 [🔔] [👤 Profil]│
│  Cluster Melati Mas - Blok C-08          │
├──────────────────────────────────────────┤
│                                          │
│   ┌──────────────────────────────────┐   │
│   │                                  │   │
│   │      🟢 BUKA GERBANG MASUK       │   │  <-- Tinggi 64px, Teks Bold
│   │     (Pencet saat di depan pos)   │   │
│   │                                  │   │
│   └──────────────────────────────────┘   │
│                                          │
├──────────────────────────────────────────┤
│  MENU UTAMA                              │
│                                          │
│  ┌──────────────────┐ ┌────────────────┐ │
│  │ 🎫 Buat Izin Tamu│ │ 💳 Iuran Kas RT│ │
│  │ (Kirim link WA)  │ │ (Status: LUNAS)│ │
│  └──────────────────┘ └────────────────┘ │
│  ┌──────────────────┐ ┌────────────────┐ │
│  │ 📢 Pengumuman RT │ │ 📊 Laporan Kas │ │
│  │ Kerja bakti besok│ │ Saldo: 18 Juta │ │
│  └──────────────────┘ └────────────────┘ │
│                                          │
│  ┌──────────────────────────────────┐    │
│  │ 🚨 TOMBOL DARURAT (PANIC BUTTON) │    │  <-- Warna Merah Cerah
│  └──────────────────────────────────┘    │
└──────────────────────────────────────────┘
```

### B. Alur Pembuatan Undangan Tamu / Paket via WhatsApp:
1. Warga klik **"Buat Izin Tamu"**.
2. Masukkan nama tamu (misal: *"Kurir Shopee"* atau *"Paman Rudi"*).
3. Pilih masa berlaku: `[ 2 Jam ]`, `[ Hari Ini Saja ]`, atau `[ Besok ]`.
4. Sistem menghasilkan QR Pass dan tombol: **"Bagikan ke WhatsApp"**.
5. Tamu tinggal menunjukkan QR tersebut di pos satpam tanpa perlu turun dari kendaraan.

---

## 🧭 5. Sistem Onboarding Interaktif (Tutorial Pengguna Baru)

Saat warga atau pengurus pertama kali membuka aplikasi, sistem tidak langsung membiarkan mereka menebak-nebak, melainkan menjalankan **Interactive Guided Tour (Coach Marks)**:

```text
Layar Meredup (Overlay Hitam Transparan 70%)
                       │
                       ▼
    [ STEP 1: SOROT TOMBOL BUKA GERBANG ]
    "Ini tombol utama Anda. Cukup pencet tombol hijau ini saat kendaraan 
     Anda tiba di depan portal komplek untuk membuka palang."
    [ Lanjut ➔ ]
                       │
                       ▼
    [ STEP 2: SOROT MENU IZIN TAMU ]
    "Ada paket belanja online atau tamu keluarga? Buatkan tiket masuk 
     instan dan kirimkan langsung ke WhatsApp mereka dari sini."
    [ Lanjut ➔ ]
                       │
                       ▼
    [ STEP 3: SOROT KARTU IURAN ]
    "Cek tagihan iuran bulanan rumah Anda dan lihat transparansi 
     penggunaan kas RT secara terbuka tanpa rahasia."
    [ Siap, Saya Paham! ]
```

* **Video Walkthrough Pendek:** Di sudut kanan atas layar selalu ada tombol `[ ❓ Cara Pakai ]` yang memutar animasi 15 detik tentang cara kerja aplikasi.

---

## ⚡ 6. State Management & Responsivitas (Mobile & Web)

1. **Offline Mode & Indikator Internet:**
   * Jika sinyal HP lemah saat di depan gerbang, aplikasi menampilkan banner kuning ramah: *"Internet lambat, gunakan Kartu RFID cadangan jika palang belum merespon."*
2. **Pencegahan Double-Click:**
   * Tombol Buka Gerbang otomatis dinonaktifkan (*disabled*) selama 5 detik setelah ditekan sekali untuk mencegah warga menekan berulang kali secara panik.

