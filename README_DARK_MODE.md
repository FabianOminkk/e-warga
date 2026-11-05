# E-WARGA Theme Architecture (Dark & Light Mode)

Dokumen ini merupakan panduan teknis implementasi **Sistem Tema Dinamis (Dark / Light Mode)** pada platform **E-WARGA**, yang mencakup arsitektur Tailwind CSS v4, pencegahan *Flash of Unstyled Content* (FOUC), persistensi `localStorage`, sinkronisasi visual Chart.js, serta standar kontras aksesibilitas.

---

## 1. Latar Belakang & Kebutuhan Desain

Penggunaan platform RT/RW dilakukan pada berbagai kondisi pencahayaan:
1. **Pos Satpam Malam Hari**: Petugas satpam bertugas dalam kondisi minim cahaya di pos jaga. Mode gelap (*Dark Mode*) mengurangi silau layar dan kelelahan mata (*eye-strain*).
2. **Warga di Luar Ruangan (Siang Hari)**: Warga yang membuka palang pintu atau memeriksa tagihan iuran di bawah terik matahari membutuhkan kontras tinggi (*Light Mode*).
3. **Pengurus RT / Bendahara**: Membutuhkan kebebasan memilih tema visual yang nyaman saat menginput pembukuan kas bulanan.

---

## 2. Arsitektur & Teknologi

### 2.1 Konfigurasi Tailwind CSS v4 (`resources/css/app.css`)
Tailwind CSS v4 menggunakan strategi `@custom-variant` untuk mengaktifkan pemilih class `.dark`:
```css
@import 'tailwindcss';

@custom-variant dark (&:where(.dark, .dark *));

@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';
@source '../views/**/*.blade.php';
```

### 2.2 Pencegahan FOUC (*Flash of Unstyled Content*)
Agar layar tidak berkedip putih saat pengguna membuka halaman dalam mode gelap, skrip inisialisasi dini diletakkan di dalam `<head>` sebelum konten HTML dirender:
```html
<script>
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
</script>
```

### 2.3 Komponen Tombol Toggle Interaktif (Alpine.js)
Terletak pada *Top Navigation Bar* di `resources/views/layouts/app.blade.php`:
* **Icon Matahari (☀️)**: Muncul saat mode gelap aktif (klik untuk beralih ke mode terang).
* **Icon Bulan (🌙)**: Muncul saat mode terang aktif (klik untuk beralih ke mode gelap).
* Menembakkan custom event `window.dispatchEvent(new CustomEvent('theme-changed', { detail: '...' }))` agar komponen visual lainnya (seperti Chart.js) dapat merender ulang warnanya tanpa me-refresh halaman.

---

## 3. Sinkronisasi Dinamis Visualisasi Chart.js

Grafik keuangan (Pie Chart pengeluaran, Donut Chart iuran, dan Bar Chart arus kas) mendengarkan sinyal perubahan tema:
```javascript
window.addEventListener('theme-changed', function () {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#CBD5E1' : '#475569';
    const borderColor = isDark ? '#1E293B' : '#FFFFFF';
    const gridColor = isDark ? '#334155' : '#F1F5F9';

    if (expenseChartInstance) {
        expenseChartInstance.data.datasets[0].borderColor = borderColor;
        expenseChartInstance.options.plugins.legend.labels.color = textColor;
        expenseChartInstance.update();
    }

    if (cashflowChartInstance) {
        cashflowChartInstance.options.scales.x.ticks.color = textColor;
        cashflowChartInstance.options.scales.y.ticks.color = textColor;
        cashflowChartInstance.options.scales.y.grid.color = gridColor;
        cashflowChartInstance.update();
    }
});
```

---

## 4. Palet Warna Desain (PropTech Contrast Standards)

| Elemen UI | Mode Terang (Light Mode) | Mode Gelap (Dark Mode) |
| :--- | :--- | :--- |
| **Latar Belakang Canvas** | `bg-slate-50` (`#F8FAFC`) | `bg-slate-950` (`#020617`) |
| **Latar Belakang Kartu / Box** | `bg-white` (`#FFFFFF`) | `bg-slate-900` (`#0F172A`) |
| **Garis Pembatas (Borders)** | `border-slate-200` (`#E2E8F0`) | `border-slate-800` (`#1E293B`) |
| **Teks Judul Utama** | `text-slate-900` (`#0F172A`) | `text-white` (`#FFFFFF`) |
| **Teks Paragraf / Deskripsi** | `text-slate-600` (`#475569`) | `text-slate-300` (`#CBD5E1`) |
| **Aksen Hijau / Sukses** | `text-emerald-600` / `bg-emerald-50` | `text-emerald-400` / `bg-emerald-950/70` |
| **Aksen Biru / Formal** | `text-blue-600` / `bg-blue-50` | `text-blue-400` / `bg-blue-950/70` |
| **Aksen Amber / Perhatian** | `text-amber-600` / `bg-amber-50` | `text-amber-400` / `bg-amber-950/70` |
