# E-WARGA Modul Jadwal Keamanan & Siskamling Ronda

Dokumen ini merupakan panduan komprehensif implementasi **Modul Jadwal Keamanan Bulan Ini**, mencakup sistem penugasan Satpam (Mode Cluster) dan Ronda Bergilir Warga (Mode Perkampungan).

---

## 1. Fitur Utama

1. **Dual Schedule Persona**:
   - **Mode Cluster**: Jadwal Shift Satpam profesional (Shift Pagi: 07:00-15:00, Siang: 15:00-23:00, Malam: 23:00-07:00).
   - **Mode Perkampungan**: Jadwal Ronda Malam Warga RT bergilir (21:00-04:00) yang dibagi per regu blok/kelompok warga.
2. **Duty Tracking Bulan Ini**:
   - Menampilkan kalender/daftar bertugas untuk bulan aktif dengan status kehadiran: `scheduled` (Terjadwal), `checked_in` (Hadir/Aktif Bertugas), `swapped` (Tukar Shift), `missed` (Tidak Hadir).
3. **1-Click Self Check-in Presensi**:
   - Petugas satpam atau warga yang sedang berjaga di pos kamling dapat melakukan check-in instan dengan timestamp presensi tersimpan otomatis.
4. **Fitur Tukar Shift (Swap Request)**:
   - Apabila seorang warga berhalangan ronda karena urusan dinas/keluarga, warga dapat mengajukan permohonan tukar jadwal dengan warga lain.
5. **Quick Add / Roster Generator**:
   - Pengurus RT dapat menambahkan jadwal baru dengan memilih tanggal, shift, pos jaga, dan personil yang bertugas.

---

## 2. Struktur Data (`security_schedules` Table)

```sql
CREATE TABLE security_schedules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    schedule_type VARCHAR(20) DEFAULT 'satpam', -- 'satpam' atau 'ronda_warga'
    date DATE NOT NULL,
    shift_name VARCHAR(50) NOT NULL,            -- 'Pagi', 'Siang', 'Malam', 'Ronda Malam'
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    post_location VARCHAR(100) DEFAULT 'Pos Utama Gate A',
    status VARCHAR(20) DEFAULT 'scheduled',     -- 'scheduled', 'checked_in', 'swapped', 'missed'
    checked_in_at TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## 3. Alur Penggunaan (User Journey)

### 3.1 Melihat Siapa yang Bertugas Bulan Ini
1. Masuk ke menu **"Jadwal & Lingkungan"** di navigasi utama atau URL `/jadwal-keamanan`.
2. Di bagian atas terdapat metrik ringkasan:
   - Total Petugas/Warga Aktif
   - Jadwal yang Sedang Berjalan Hari Ini
   - Status Check-in Hari Ini
3. Tabel jadwal menampilkan foto, nama, no HP/WhatsApp, tanggal tugas, shift, pos jaga, dan tombol aksi.

### 3.2 Melakukan Check-in di Pos
1. Saat jam bertugas tiba, personil membuka kartu tugasnya.
2. Klik tombol **"Presensi Sekarang"** (`POST /jadwal-keamanan/{id}/checkin`).
3. Sistem mencatat waktu presensi aktual (`checked_in_at`) dan mengubah badge status menjadi **"Hadir / On Duty"**.

### 3.3 Mengajukan Tukar Shift
1. Pada baris jadwal yang ingin ditukar, klik tombol **"Tukar Jadwal"** (`POST /jadwal-keamanan/{id}/tukar`).
2. Masukkan alasan penukaran jadwal.
3. Status berubah menjadi **"Tukar Shift Diajukan"** dan pengurus RT mendapat notifikasi ringkasan.

---

## 4. Screenshot / Tampilan UI
Tampilan jadwal terintegrasi dengan Tailwind CSS responsif:
- Tampilan Desktop: Tabel matriks jadwal lengkap dengan badge status warna-warni dan filter bulan.
- Tampilan Mobile: Kartu vertikal ramah sentuhan, tombol WhatsApp langsung terhubung ke rekan satu shift.
