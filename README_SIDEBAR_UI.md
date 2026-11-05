# 🖥️ E-WARGA - UI/UX Architecture: Desktop-First Sidebar & Marketing Landing Page

> **Standar Desain Antarmuka Desktop, Hierarki Navigasi Sidebar Ergonomis, & Landing Page Konversi Klien**
> *Status: Implemented & Production Ready (Tailwind CSS v4 + Alpine.js)*

---

## 🎯 1. Latar Belakang & Filosofi UI/UX

Dalam pengelolaan perumahan (Cluster Modern) maupun perkampungan warga (Guyub RT/RW), pengguna desktop biasanya adalah **Ketua RT/RW, Bendahara, Sekretaris, dan Komandan Satpam** yang menghabiskan waktu berjam-jam di depan layar monitor laptop atau PC pos jaga.

Sebelumnya, navigasi menggunakan *top navbar* tradisional yang sering kali:
1. Terpotong jika jumlah menu bertambah (Kas, Posyandu, Jadwal Satpam, Surat RT, Simulator).
2. Mempersempit area vertikal untuk tabel data keuangan dan log keluar-masuk kendaraan.
3. Mencampurkan halaman publik promosi (*Landing Page*) dengan tampilan internal aplikasi.

### Solusi yang Diimplementasikan:
1. **Pemisahan Layout Tegas (*Dual-Layout Architecture*):**
   * **Route Landing Page (`/`):** Tampilan *Full-width Marketing Showcase* modern tanpa sidebar yang dirancang khusus untuk memikat calon pembeli/klien (Developer properti, pengurus paguyuban, Ketua RT/RW se-Indonesia).
   * **Route Aplikasi Internal (`/dashboard`, `/kas`, dll.):** Tata letak *Enterprise Desktop Sidebar* (`w-72` / 288px) yang kokoh, terorganisir per kategori kerja, dan ramah monitor besar.
2. **Navigasi Berkelompok (*Categorized Mental Model*):** Memudahkan pengurus menemukan fitur berdasarkan tugas harian tanpa perlu mencari-cari.
3. **Ergonomi & Kontras Tinggi:** Mendukung transisi instan Dark Mode dan Light Mode di sidebar maupun topbar.

---

## 📐 2. Struktur Navigasi Sidebar Desktop (`w-72`)

Sidebar ditempatkan di sisi kiri dengan posisi `sticky top-0 h-screen`, memungkinkan area konten di kanan melakukan *scroll* secara independen tanpa kehilangan navigasi.

```text
┌─────────────────────────────────────────────────────────┐
│ 🏢 E-WARGA Cloud           [🟢 Online Cloud]            │
│ Sistem Cerdas Pengelolaan Warga & Keamanan              │
├─────────────────────────────────────────────────────────┤
│ 🔄 MODE LINGKUNGAN:                                     │
│ [ 🏘️ Cluster Perumahan ]   (1-klik beralih ke Guyub)    │
├─────────────────────────────────────────────────────────┤
│ 📌 MANAJEMEN UTAMA                                      │
│  ├─ 📊 Dashboard RT                                     │
│  ├─ 💳 Kas & Keuangan (Pie Chart & Buku Kas)            │
│  └─ 👥 Data Warga & Rumah                               │
│                                                         │
│ 🛡️ KEAMANAN & HARDWARE                                 │
│  ├─ 🚧 Simulator Palang Pintu (IoT Gate)                │
│  └─ 👮 Jadwal Ronda & Satpam (2 Versi Komplek/Kampung)   │
│                                                         │
│ 📋 PELAYANAN WARGA                                      │
│  ├─ ✉️ Surat Pengantar RT  [Badge: Pending Counter]     │
│  └─ 🩺 Posyandu Warga (Tracking Balita & Lansia)        │
│                                                         │
│ 📢 PORTAL INFORMASI                                     │
│  └─ 📰 Pengumuman Warga                                 │
├─────────────────────────────────────────────────────────┤
│ 🎭 GANTI PERAN CEPAT (Testing Mode):                    │
│ [ Ketua RT ] [ Bendahara ] [ Satpam ] [ Warga ]         │
├─────────────────────────────────────────────────────────┤
│ 🌓 Ganti Tema: [ ☀️ Terang ] / [ 🌙 Gelap ]            │
├─────────────────────────────────────────────────────────┤
│ 👤 Doni Pratama (Ketua RT)        [ 🚪 Keluar ]         │
│ Blok A-04 • Administrator                               │
└─────────────────────────────────────────────────────────┘
```

### Detail Komponen Sidebar:
1. **Brand Header & Status Pulse:**
   * Logo E-WARGA dengan gradien Emerald Community.
   * Indikator real-time dot berkedip (`animate-pulse`) warna hijau menandakan koneksi websocket/cloud aktif.
2. **Environment Switcher Interactive Banner:**
   * Tombol *quick switch* antara **Cluster / Perumahan Modern** (fokus palang pintu RFID, CCTV, satpam shift) dan **Perkampungan Guyub** (fokus siskamling ronda, jimpitan beras, pos kamling).
3. **Kelompok Navigasi (Accordion-Style Visuals):**
   * Setiap menu memiliki ikon SVG tegas dan indikator aktif berupa latar belakang `bg-emerald-50 text-emerald-700` (Light) atau `bg-emerald-950/60 text-emerald-400` (Dark) serta border tepi vertikal tebal.
   * Menu **Surat Pengantar** dilengkapi badge dinamis jumlah surat yang menunggu persetujuan (*Pending Approval*).
4. **Interactive Role Switcher Pill:**
   * Memudahkan pengujian 4 hak akses peran tanpa repot logout: **Ketua RT**, **Bendahara**, **Satpam Pos Jaga**, dan **Warga Biasa**.
5. **Footer Profile & Quick Settings:**
   * Tombol ganti tema (*Dark / Light Mode Toggle*).
   * Avatar pengguna, nama lengkap, nomor rumah/blok, peran, dan tombol logout aman berproteksi CSRF.

---

## 🖥️ 3. Topbar Desktop Pendukung

Pada sisi kanan sidebar, topbar desktop menyediakan konteks kontekstual bagi pengguna:
* **Breadcrumb Navigation:** Menampilkan lokasi halaman aktif secara hierarkis (misal: `Dashboard > Kas & Keuangan > Laporan Arus Kas`).
* **Live Clock & Date:** Waktu dan tanggal Indonesia terformat rapi (`Kamis, 06 November 2026`).
* **Active Environment Pill:** Lencana status lingkungan yang sedang aktif beserta tombol pintas switch cepat.
* **Mobile Hamburger Drawer:** Untuk layar tablet atau smartphone, tombol hamburger muncul otomatis untuk membuka sidebar sebagai modal drawer samping.

---

## 🚀 4. Strategi Landing Page Konversi Klien (`/`)

Halaman beranda publik dirancang dengan standar **SaaS B2B PropTech** tingkat tinggi untuk meyakinkan calon klien:

### Elemen Kunci Landing Page:
1. **Hero Section dengan Value Proposition Berani:**
   * *"Transformasi Digital Lingkungan RT/RW & Cluster Perumahan Anda"*.
   * Mengintegrasikan palang pintu gerbang otomatis, iuran kas transparan, jadwal pos kamling, dan pelayanan posyandu dalam satu genggaman.
   * Call to Action ganda: **[ 🚀 Buka Dashboard Demo ]** dan **[ 📅 Jadwalkan Demo & Survei ]**.
2. **Live Test-Drive Role Badges:**
   * Calon klien dapat langsung menguji akun demo dengan kredensial instan (`admin123`) untuk Ketua RT, Bendahara, Satpam, atau Warga.
3. **Statistik Terpercaya & Social Proof:**
   * Indikator capaian: `120+ Komplek & RW`, `15.000+ Kartu RFID Aktif`, `Rp 1.4 M+ Kas Transparan`, `99.9% Uptime Cloud`.
4. **Tabel Komparasi "Sebelum vs Sesudah":**
   * Membandingkan cara manual (grup WhatsApp berisik, pembukuan kertas hilang, palang manual rentan maling) dengan ekosistem terpadu E-WARGA.
5. **6 Modul Utama Interaktif:**
   * Smart Barrier Gate (IoT Palang Pintu)
   * Kas & Keuangan Transparan (Chart Interaktif)
   * Posyandu Terpadu & Pencegahan Stunting
   * Pelayanan Surat Pengantar RT Online
   * Jadwal Keamanan & Siskamling (Dual Mode)
   * Mobile Warga & Panic Button
6. **Teaser Simulator Palang Pintu:**
   * Cuplikan tampilan console pos jaga satpam yang dapat langsung dicoba oleh calon pembeli.
7. **Testimoni Otentik:**
   * Pengakuan langsung dari Ketua Paguyuban Cluster, Komandan Regu Satpam, dan Koordinator Kader Posyandu.
8. **Form Registrasi & Survei Pemasangan:**
   * Formulir survei terintegrasi untuk pengurus komplek yang ingin meminta proposal penawaran harga instalasi barrier gate dan software.

---

## 🎨 5. Spesifikasi Teknis & Kesesuaian Dark Mode

| Elemen | Light Mode | Dark Mode | Catatan |
| :--- | :--- | :--- | :--- |
| **Sidebar Background** | `#FFFFFF` (`bg-white`) | `#0F172A` (`bg-slate-900`) | Kontras WCAG AAA |
| **Sidebar Border** | `#E2E8F0` (`border-slate-200`) | `#1E293B` (`border-slate-800`) | Garis batas vertikal tipis |
| **Menu Item Hover** | `#F1F5F9` (`hover:bg-slate-100`) | `#1E293B` (`hover:bg-slate-800`) | Feedback kursor instan |
| **Active Item Pill** | `#ECFDF5` (`bg-emerald-50`) | `#064E3B` (`bg-emerald-950/60`) | Border kiri tebal emerald |
| **Main Content Canvas** | `#F8FAFC` (`bg-slate-50`) | `#020617` (`bg-slate-950`) | Mencegah kelelahan mata |

---

## 🧪 6. Verifikasi & Pengujian
* **Unit & Feature Test:** Semua 23 pengujian otomatis Laravel (`php artisan test`) lolos 100%.
* **Build Asset:** `npm run build` sukses mengompilasi CSS dan JS Vite dalam `< 600ms`.
* **Cross-Role Testing:** Navigasi dapat diakses mulus dari peran Ketua RT, Bendahara, Satpam, dan Warga dengan pembatasan hak akses yang sesuai.
