# 🖥️ E-WARGA - UI/UX Architecture: Left Drawer Navigation ("Ngelaci") & Landing Page

> **Standar Antarmuka Laci Navigasi Samping Kiri (*Slide-Out Drawer*), Penghapusan Top Navbar, & Arsitektur Konversi Klien**
> *Status: Implemented & Production Ready (Tailwind CSS v4 + Alpine.js)*

---

## 🎯 1. Filosofi & Perubahan: Sistem Laci Samping Kiri ("Ngelaci")

Berdasarkan audit pengalaman pengguna (UX) dan feedback operasional, antarmuka **E-WARGA** menerapkan konsep **Unified Left Drawer ("Sistem Laci")**:

### Mengapa Top Navbar Dihilangkan Sepenuhnya?
1. **Memotong Pandangan Vertikal:** *Top navbar* tradisional yang menempel kaku di bagian atas monitor mengurangi ruang pandang untuk grafik keuangan, tabel data warga, dan log pantau CCTV palang pintu.
2. **Kekakuan Akses:** Ketika pengguna menjelajahi halaman promosi (*Landing Page*) atau dashboard, bar atas yang selalu menempel terasa menutupi konten (*cluttered*).
3. **Ergonomi Laci Samping Kiri ("Ngelaci"):**
   * Seluruh navigasi dipindahkan ke **sisi kiri layar** dalam bentuk **Laci Navigasi (*Drawer Sidebar*)**.
   * Dapat **ditarik keluar** (*slide open*) saat dibutuhkan, atau **didorong masuk** (*collapse/slide shut*) seperti laci meja sungguhan.
   * Saat laci ditutup, halaman menjadi 100% *full-width* bersih tanpa ada bar atas yang mengganggu, dengan sebuah tombol penarik laci melayang (*floating drawer tab*) yang ergonomis di pojok kiri atas.

---

## 📐 2. Desain & Mekanisme Laci Samping Kiri (`w-80`)

Laci navigasi dirancang dengan animasi geser halus (*silky slide-over / push transition*):

```text
┌─────────────────────────────────────────────────────────┐
│ 🏢 E-WARGA Cloud           [ ◀ Dorong Tutup Laci ]      │  <- [ZONA 1: FIXED HEADER]
│ Smart Community OS                                      │     (Selalu menetap di atas)
├─────────────────────────────────────────────────────────┤
│ 🔄 MODE LINGKUNGAN:                                     │
│ [ 🏢 Cluster Perumahan ]   (1-klik beralih ke Kampung)  │
├─────────────────────────────────────────────────────────┤
│ [JIKA DI LANDING PAGE (ZONA PROMOSI)]:                  │  <- [ZONA 2: MENU TENGAH]
│ 🏷️ SEKSI LANDING PAGE                                   │     (Scrollable halus,
│  ├─ 🏠 Beranda & Hero                                   │      scrollbar transparan)
│  ├─ ⭐ Fitur Unggulan                                   │
│  ├─ 🚪 IoT Smart Barrier Gate                           │
│  ├─ 🩺 Posyandu & Stunting                              │
│  ├─ ⚖️ Sebelum vs Sesudah                               │
│  ├─ 💬 Testimoni Pengurus & Warga                       │
│  └─ 📝 Konsultasi & Survei Pasang (Redirect ke Login)   │
│                                                         │
│ 🚀 AKSES CEPAT MODUL SISTEM                             │
│  ├─ 📊 Buka Dashboard RT (Tombol Gradien Emerald)       │
│  └─ 🔐 Halaman Login B2B (Promosi + Form Auth)          │
├─────────────────────────────────────────────────────────┤
│ [JIKA DI DASHBOARD (ZONA OPERASIONAL MURNI)]:           │
│ 📌 MANAJEMEN UTAMA                                      │
│  ├─ 📊 Dashboard RT & Kas                               │
│  └─ 📱 Akses Warga (Mobile View)                        │
│ 🛡️ KEAMANAN & HARDWARE                                 │
│  ├─ 🛡️ Pos Pantau Satpam (QR Scanner)                   │
│  ├─ 🧪 Simulator Smart Gate (IoT)                       │
│  └─ 🔦 Jadwal & Ronda Bulan Ini                         │
│ 📋 PELAYANAN WARGA                                      │
│  ├─ 🩺 Posyandu Digital RT/RW                           │
│  └─ 📜 Surat Pengantar RT [Badge: Pending Counter]      │
│ 📢 PORTAL PUBLIK                                        │
│  └─ 🌐 Lihat Beranda Publik                             │
├─────────────────────────────────────────────────────────┤
│ 🌓 Ganti Tema: [ ☀️ Terang ] / [ 🌙 Gelap ]            │  <- [ZONA 3: FIXED FOOTER]
│ 🎭 Ganti Peran: [ Role ▼ ] (Anti-clipping card)         │     (Selalu menetap di bawah)
├─────────────────────────────────────────────────────────┤
│ ⚡ Fabian (Super Admin)            [ 🚪 Keluar ]         │
│ Super Admin Platform • Akses Penuh                      │
└─────────────────────────────────────────────────────────┘
```

---

## 🎛️ 3. Perilaku Laci Interaktif (*Interactive Drawer Behavior*)

1. **Keadaan Laci Terbuka (*Drawer Open*):**
   * Sisi kiri menampilkan seluruh menu secara rapi dengan lebar `w-80` (320px).
   * Pada monitor desktop (`lg:`), area konten utama secara otomatis bergeser (`lg:pl-80`) dengan animasi `transition-all duration-300`, sehingga tidak ada konten yang tertutup.
   * Di sudut kanan atas laci, terdapat tombol dorong laci: `[ ◀ ]` untuk menutup laci kembali.
2. **Keadaan Laci Tertutup (*Drawer Collapsed / Hidden*):**
   * Laci meluncur mulus masuk ke dinding kiri (`-translate-x-full`).
   * Konten utama langsung melebar penuh 100% (*full viewport immersion*).
   * Muncul tombol melayang anggun di pojok kiri atas:
     `[ ☰ Laci Menu E-WARGA ▶ ]` berkaca transparan (*backdrop-blur-md*).
   * Sekali klik, laci akan meluncur keluar kembali seketika (*silky slide-out*).
3. **Persistensi State (`localStorage`):**
   * Pilihan pengguna (apakah menyukai laci terbuka atau tertutup) disimpan di browser (`ewarga_drawer_open`), sehingga saat berpindah halaman atau refresh, kondisi laci tidak berubah secara acak.
4. **Responsif Mobile & Tablet (`< 1024px`):**
   * Pada layar sentuh HP/tablet, membuka laci memunculkan backdrop redup (`bg-slate-950/60 backdrop-blur-xs`).
   * Menyentuh bagian luar laci otomatis menutup laci dengan instan.

---

## 🚀 4. Integrasi Landing Page Konversi Klien (`/`)

Tanpa adanya *top navbar* yang mengganggu:
* **Tampilan Bersih & Imersif:** Pengunjung disuguhkan visual hero yang megah dengan metrik langsung (*Live Stats* unit rumah, jumlah warga, dan saldo kas).
* **Navigasi Cepat dari Sisi Kiri:** Pengunjung dapat melompat ke seksi mana pun (Fitur, IoT Gate, Posyandu, Komparasi, Testimoni, Formulir Survei) atau langsung meluncur ke Demo Aplikasi melalui laci di sebelah kiri.
* **Anchor Terintegrasi:** Seluruh tautan laci langsung mengarahkan layar ke ID seksi target (`#hero`, `#fitur`, `#keamanan`, `#posyandu`, `#komparasi`, `#testimoni`, `#daftar-komplek`).

---

## 🧪 5. Verifikasi Kualitas
* **Zero Topbar Clutter:** Dipastikan tidak ada lagi `<header>` besar yang menempel di bagian atas layar halaman utama.
* **100% Dark Mode Compatible:** Laci samping mendukung transisi warna mulus antara slate gelap `#0F172A` dan putih bersih `#FFFFFF`.
* **Testing:** Seluruh skenario pengujian otomatis lolos (*23 passed, 56 assertions*).
