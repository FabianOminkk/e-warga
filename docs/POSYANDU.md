# E-WARGA Modul Posyandu Digital RT/RW

Dokumen ini merupakan panduan komprehensif implementasi **Modul Posyandu Digital RT/RW** pada aplikasi **E-WARGA**, mencakup pencatatan demografis warga sasaran (Balita, Anak-anak, Ibu Hamil, Ibu/Kader, dan Lansia), pemantauan tumbuh kembang KMS, deteksi dini risiko stunting balita, skrining ibu hamil, serta Posbindu lansia.

---

## 1. Latar Belakang & Urgensi

Di tingkat lingkungan RT/RW, Pos Pelayanan Terpadu (Posyandu) adalah garda terdepan kesehatan masyarakat. Namun, pencatatan di lapangan umumnya masih menggunakan buku posyandu kertas manual yang berisiko:
1. Data timbang berat badan dan tinggi badan balita tercecer sehingga stunting terlambat dideteksi.
2. Tidak adanya data agregat demografi (berapa jumlah balita, anak-anak, ibu hamil, dan lansia) yang siap saji saat diminta Puskesmas / Kelurahan.
3. Rekapitulasi hasil pemeriksaan lansia (tensi, gula darah, kolesterol) tidak terlacak secara berkala.

**E-WARGA Modul Posyandu Digital** mendigitalkan seluruh proses tersebut secara real-time.

---

## 2. Fitur Utama

### 2.1 Dashboard Demografi Sasaran (5 Kategori Lengkap)
E-WARGA mengelompokkan warga sasaran ke dalam 5 kategori dengan kartu metrik interaktif:
1. **👶 Balita (< 5 Tahun / 0 - 59 Bulan)**: Pelacakan usia dalam satuan bulan, berat badan (BB), tinggi badan (TB), lingkar kepala, lingkar lengan atas (LiLA), dan vaksin/imunisasi dasar lengkap (BCG, Polio, DPT booster, Campak).
2. **🧒 Anak-anak (5 - 12 Tahun)**: Pemantauan tumbuh kembang usia sekolah dasar dan asupan gizi.
3. **🤰 Ibu Hamil (Bumil)**: Pemantauan usia kehamilan (minggu), trimester, LiLA untuk skrining Kurang Energi Kronis (KEK), tekanan darah (tensi), dan distribusi Tablet Tambah Darah (TTD).
4. **👩 Ibu / Wanita Usia Subur (WUS) & Kader**: Register kader aktif PKK/Posyandu dan pendataan KB.
5. **👵 Lansia (> 60 Tahun / Posbindu PTM)**: Skrining Penyakit Tidak Menular: Tekanan Darah (Hipertensi), Gula Darah Sewaktu (GDS), Kolesterol, dan Asam Urat.

### 2.2 Algoritma Deteksi Dini Stunting Balita
Sistem menerapkan evaluasi otomatis terhadap kurva baku berat dan tinggi badan:
```php
if ($member->category === 'balita') {
    $ageInMonths = Carbon::now()->diffInMonths($member->birth_date);
    $weight = $validated['weight_kg'] ?? 0;
    $height = $validated['height_cm'] ?? 0;

    // Evaluasi ambang batas kurva standar WHO
    if ($ageInMonths >= 12 && $height > 0 && $height < 70) {
        $validated['nutrition_status'] = 'risiko_stunting';
    } elseif ($weight > 0 && $weight < 7.5 && $ageInMonths >= 12) {
        $validated['nutrition_status'] = 'gizi_kurang';
    } else {
        $validated['nutrition_status'] = 'gizi_baik';
    }
}
```
Ketika balita terdeteksi `risiko_stunting`, sistem memunculkan **Radar Banner Merah/Kuning** untuk merekomendasikan intervensi Pemberian Makanan Tambahan (PMT) protein hewani (telur/susu) dan rujukan ke Puskesmas.

### 2.3 Skrining Risiko Tinggi Ibu Hamil (KEK)
Deteksi otomatis pada batas Lingkar Lengan Atas (LiLA):
- LiLA < 23.5 cm: Kategori `risiko_tinggi` (Indikasi Kurang Energi Kronis yang membahayakan janin).
- LiLA &ge; 23.5 cm: Status gizi optimal / normal.

### 2.4 Buku Register & Filter Instan
Tabel register warga dilengkapi tab filter 1-klik (`Semua`, `Balita`, `Anak-anak`, `Ibu Hamil`, `Ibu`, `Lansia`) tanpa reload halaman lambat.

---

## 3. Skema Basis Data

### 3.1 Tabel `posyandu_members`
Menyimpan profil kependudukan warga sasaran posyandu:
- `tenant_id`: ID lingkungan RT/RW.
- `user_id`: Relasi ke akun warga terdaftar (opsional).
- `family_card_number`: Nomor Kartu Keluarga (KK).
- `nik`: Nomor Induk Kependudukan 16 digit.
- `name`: Nama lengkap.
- `gender`: 'L' atau 'P'.
- `birth_date`: Tanggal lahir (digunakan untuk auto-kalkulasi usia dalam bulan atau tahun).
- `category`: 'balita', 'anak', 'ibu_hamil', 'ibu', 'lansia'.
- `parent_name`: Nama orang tua / pasangan penanggung jawab.
- `address`: Blok/No rumah.
- `phone`: Kontak WhatsApp.
- `notes`: Catatan medis awal / HPL kehamilan.

### 3.2 Tabel `posyandu_records`
Menyimpan riwayat pemeriksaan bulanan saat hari Posyandu:
- `posyandu_member_id`: ID warga sasaran.
- `checkup_date`: Tanggal penimbangan/skrining.
- `weight_kg`: Berat badan dalam kilogram (presisi 1 desimal).
- `height_cm`: Tinggi/panjang badan dalam sentimeter (presisi 1 desimal).
- `head_circumference_cm`: Lingkar kepala (cm).
- `arm_circumference_cm`: Lingkar lengan atas (LiLA).
- `blood_pressure`: Nilai tensi (contoh: '120/80').
- `blood_sugar`: Gula darah sewaktu (mg/dL).
- `cholesterol`: Kadar kolesterol (mg/dL).
- `uric_acid`: Asam urat (mg/dL).
- `gestational_age_weeks`: Usia kehamilan (khusus bumil).
- `nutrition_status`: 'gizi_baik', 'risiko_stunting', 'gizi_kurang', 'gizi_lebih', 'normal', 'hipertensi', 'risiko_tinggi'.
- `vitamins_or_vaccines`: Vitamin A, vaksin polio, tablet Fe/TTD, obat cacing.
- `examiner_name`: Nama bidan pembina / kader pemeriksa.
- `action_plan`: Konseling gizi atau arahan rujukan.

---

## 4. Rute Endpoint Web
| Method | URL | Controller Action | Keterangan |
| :--- | :--- | :--- | :--- |
| `GET` | `/posyandu` | `PosyanduController@index` | Halaman utama modul Posyandu |
| `POST` | `/posyandu/members` | `PosyanduController@storeMember` | Form pendaftaran warga baru |
| `DELETE` | `/posyandu/members/{id}` | `PosyanduController@destroyMember` | Hapus warga dari buku register |
| `POST` | `/posyandu/records` | `PosyanduController@storeRecord` | Simpan hasil penimbangan / pemeriksaan |

