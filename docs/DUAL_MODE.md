# E-WARGA Dual-Mode Lingkungan Architecture (Cluster vs Kampung)

Dokumen ini menjelaskan arsitektur, perbedaan karakteristik fungsional, dan implementasi teknis sistem **Dual-Mode Lingkungan** pada aplikasi **E-WARGA**.

---

## 1. Latar Belakang & Visi

Struktur sosial dan kebutuhan operasional lingkungan perumahan di Indonesia terbagi ke dalam dua model utama:
1. **Perumahan / Cluster / Townhouse Modern**: Memiliki *single-gate system*, mempekerjakan satpam profesional berbayar, iuran lingkungan (IPL) terstruktur tinggi, dan interaksi gerbang terotomatisasi (IoT/RFID/QR).
2. **Perkampungan / Desa / Rukun Tetangga (RT) Tradisional**: Bersifat terbuka, mengutamakan sistem keamanan swakarsa (**Siskamling / Ronda Malam Bergilir Warga**), gotong royong kerja bakti, dan Pos Pelayanan Terpadu (**Posyandu**).

E-WARGA dirancang fleksibel agar Ketua RT / Pengurus Lingkungan dapat mengaktifkan mode yang sesuai hanya dengan 1-klik melalui tombol *Environment Mode Switcher*.

---

## 2. Matriks Komparasi Fitur

| Dimensi | Mode Perumahan / Cluster (`cluster`) | Mode Perkampungan / Desa (`kampung`) |
| :--- | :--- | :--- |
| **Sistem Keamanan** | Satpam profesional berbayar, shift 8 jam (Pagi/Sore/Malam). | Siskamling ronda malam bergilir warga RT (Shift Ronda). |
| **Akses Gerbang** | Barrier Gate Otomatis, RFID, Guest QR Pass, Scan Satpam. | Portal manual swakarsa / pos kamling terbuka dengan kentongan. |
| **Iuran Warga** | Iuran Pengelolaan Lingkungan (IPL), tagihan bulanan fix. | Iuran kas RT, jimpitan beras/koin, iuran kematian & sampah. |
| **Aktivitas Utama** | Monitoring tamu tak dikenal, paket kurir drop-off. | Posyandu Balita & Lansia, Kerja Bakti RT, Ronda Malam Warga. |
| **Prioritas Menu UI** | Simulator Gate, Console Satpam, Guest Pass. | Jadwal Ronda Warga, Posyandu Digital, Surat Pengantar RT. |

---

## 3. Implementasi Teknis di Database & Kode

### 3.1 Skema Database (`tenants` Table)
```php
Schema::table('tenants', function (Blueprint $table) {
    // Mode lingkungan: 'cluster' atau 'kampung'
    $table->string('environment_type')->default('cluster')->after('status');
});
```

### 3.2 Model Attribute (`Tenant.php`)
```php
protected $fillable = [
    'name',
    'slug',
    'code',
    'environment_type', // 'cluster' | 'kampung'
    'plan',
    'status',
];

public function isCluster(): bool
{
    return $this->environment_type === 'cluster';
}

public function isKampung(): bool
{
    return $this->environment_type === 'kampung';
}
```

### 3.3 Dynamic Controller & View Adaptation
Pada controller dan Blade layout, sistem mendeteksi `$tenant->environment_type`:
```blade
@if($tenant->isKampung())
    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300">
        🌾 Mode Perkampungan / RT Tradisional
    </span>
@else
    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300">
        🏘️ Mode Cluster / Perumahan Modern
    </span>
@endif
```

---

## 4. Cara Beralih Mode (Switching Workflow)

1. Pengurus RT (Ketua RT / Admin) membuka halaman **Jadwal & Lingkungan** (`/jadwal-keamanan`).
2. Terdapat kartu kontrol **"Mode Lingkungan Aktif"**.
3. Klik tombol toggle **"Ganti ke Mode Perkampungan"** atau **"Ganti ke Mode Cluster"**.
4. Controller memanggil route `POST /toggle-mode`:
   ```php
   public function toggleEnvironmentMode(Request $request)
   {
       $tenant = Tenant::first();
       $newMode = $tenant->environment_type === 'cluster' ? 'kampung' : 'cluster';
       $tenant->update(['environment_type' => $newMode]);
       
       return back()->with('success', "Mode lingkungan berhasil diubah ke: " . strtoupper($newMode));
   }
   ```
5. Tampilan langsung menyesuaikan secara reaktif tanpa perlu restart server atau re-migrasi database.

