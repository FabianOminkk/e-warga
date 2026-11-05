# E-WARGA Layanan Surat Pengantar RT Online Digital

Dokumen ini merupakan panduan komprehensif implementasi **Layanan Surat Pengantar RT Online Digital** pada platform **E-WARGA**, yang menggantikan proses pembuatan surat pengantar fisik yang memakan waktu menjadi proses digital kilat lengkap dengan verifikasi QR Code resmi.

---

## 1. Latar Belakang & Manfaat

Sebelum digitalisasi:
- Warga harus mendatangi rumah Ketua RT secara langsung, seringkali berbenturan dengan kesibukan pengurus.
- Format surat sering tidak seragam dan nomor surat manual rawan duplikasi.
- Pihak kelurahan atau instansi tujuan (bank, disdukcapil) sulit memverifikasi apakah surat benar-benar diterbitkan oleh RT bersangkutan.

Dengan **E-WARGA Layanan Surat Pengantar RT Online**:
1. **Pengajuan Fleksibel dari Smartphone**: Warga cukup mengisi jenis surat dan keperluan dalam hitungan detik.
2. **Persetujuan 1-Klik oleh Ketua RT**: Ketua RT dapat mereview data dan menyetujui kapan saja.
3. **Penomoran Otomatis & Standar Kelurahan**: Menggunakan format resmi `470/{No_Urut}/RT.04/RW.02/{Bulan_Romawi}/{Tahun}`.
4. **Verifikasi QR Code Publik Anti-Pemalsuan**: Setiap surat memiliki kode sertifikasi unik yang jika dipindai akan langsung menampilkan keabsahan dokumen.
5. **Format Siap Cetak (A4 PDF)**: Dilengkapi kop surat resmi RT/RW dan layout baku tata naskah dinas.

---

## 2. Jenis Layanan Surat yang Didukung

1. **Surat Pengantar Pembuatan / Rekam KTP-el (`pengantar_ktp`)**
2. **Surat Pengantar Pembuatan / Perubahan Kartu Keluarga (`pengantar_kk`)**
3. **Surat Keterangan Domisili Tempat Tinggal (`domisili`)**
4. **Surat Keterangan Tidak Mampu / SKTM Beasiswa (`sktm`)**
5. **Surat Pengantar Keterangan Usaha / SKU UMKM (`izin_usaha`)**
6. **Surat Pengantar Nikah / Model N1-N4 (`pengantar_nikah`)**
7. **Surat Keterangan Kematian (`kematian`)**

---

## 3. Alur Penggunaan (User Journey)

```
[Warga Mengisi Form Pengajuan] 
          │
          ▼ (Status: Menunggu Persetujuan RT)
[Ketua RT Menerima Pengajuan di Dashboard Surat]
     ├───► [Klik: Setujui & TTD]
     │          │
     │          ▼
     │     [Sistem Otomatis Generate:
     │      - Nomor Surat Resmi
     │      - Unique QR Code Token
     │      - Timestamp Tanda Tangan Digital]
     │          │
     │          ▼ (Status: Disetujui)
     │     [Warga Membuka & Cetak Dokumen / Simpan PDF]
     │
     └───► [Klik: Tolak Pengajuan]
                │
                ▼
           [Input Alasan Penolakan -> Warga Dapat Merevisi]
```

---

## 4. Keamanan & Verifikasi QR Code

Setiap dokumen surat yang telah disetujui memiliki QR Code unik, contohnya:
- URL Verifikasi: `http://127.0.0.1:8000/surat-pengantar/verifikasi/{QR_CODE}`
- Pihak Kelurahan, Bank, atau Disdukcapil cukup mengarahkan kamera smartphone ke QR Code pada dokumen fisik.
- Halaman verifikasi akan memvalidasi:
  - Validitas nomor surat
  - Nama dan NIK pemohon
  - Tanggal penandatanganan elektronik
  - Nama Ketua RT yang menandatangani

---

## 5. Skema Basis Data (`letter_requests` Table)

```sql
CREATE TABLE letter_requests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    letter_type VARCHAR(50) NOT NULL,
    letter_number VARCHAR(100) NULL UNIQUE,
    applicant_name VARCHAR(255) NOT NULL,
    nik VARCHAR(20) NOT NULL,
    house_block VARCHAR(50) NOT NULL,
    purpose TEXT NOT NULL,
    supporting_data TEXT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    rejection_reason TEXT NULL,
    qr_verification_code VARCHAR(64) NULL UNIQUE,
    signed_at TIMESTAMP NULL,
    signed_by INTEGER NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (signed_by) REFERENCES users(id) ON DELETE SET NULL
);
```

---

## 6. Rute Endpoint Web

| Method | URL | Controller Action | Keterangan |
| :--- | :--- | :--- | :--- |
| `GET` | `/surat-pengantar` | `LetterController@index` | Daftar & filter pengajuan surat |
| `POST` | `/surat-pengantar` | `LetterController@store` | Form pengajuan surat warga |
| `POST` | `/surat-pengantar/{id}/approve` | `LetterController@approve` | Persetujuan & penomoran otomatis |
| `POST` | `/surat-pengantar/{id}/reject` | `LetterController@reject` | Tolak pengajuan dengan alasan |
| `GET` | `/surat-pengantar/verifikasi/{code}`| `LetterController@verify` | Preview resmi dokumen & validasi QR |
