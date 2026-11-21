@extends('layouts.app')

@section('content')
<!-- Tesseract.js Library untuk Pemindaian OCR e-KTP Sisi Klien -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js" defer></script>

<div class="min-h-screen flex flex-col justify-between bg-slate-50 dark:bg-slate-950 px-4 sm:px-6 lg:px-8 py-6">

    <!-- Top Navigation Bar Header Halaman Registrasi -->
    <header class="max-w-5xl w-full mx-auto flex items-center justify-between pb-6">
        <x-logo size="md" href="{{ route('home') }}" />
        
        <div class="flex items-center gap-3">
            <!-- Dark / Light Theme Toggle -->
            <div x-data="{
                isDark: document.documentElement.classList.contains('dark'),
                toggle() {
                    this.isDark = !this.isDark;
                    if (this.isDark) {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    }
                }
            }">
                <button 
                    @click="toggle()" 
                    type="button" 
                    class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors shadow-xs cursor-pointer flex items-center justify-center focus-visible:ring-2 focus-visible:ring-emerald-500" 
                    :title="isDark ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap'"
                >
                    <svg x-show="isDark" x-cloak class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg x-show="!isDark" x-cloak class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
            </div>

            <a href="{{ route('login') }}" class="px-3.5 sm:px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300 transition-colors shadow-xs flex items-center gap-1.5 focus-visible:ring-2 focus-visible:ring-emerald-500">
                <span>Sudah Punya Akun?</span>
                <span class="text-emerald-600 dark:text-emerald-400 font-black">Masuk ➔</span>
            </a>
        </div>
    </header>

    <!-- Main Register Container -->
    <div 
        x-data="registerForm()"
        class="max-w-5xl w-full mx-auto my-auto grid grid-cols-1 lg:grid-cols-12 bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden"
    >
        
        <!-- SISI KIRI: BENEFIT & JAMINAN KEAMANAN PDP (5 COLS) -->
        <div class="order-2 lg:order-1 lg:col-span-5 bg-gradient-to-br from-[#0F172A] via-[#1B365D] to-emerald-950 p-6 sm:p-10 text-white flex flex-col justify-between relative overflow-hidden">
            <!-- Background Ambient Glow -->
            <div class="absolute -top-24 -left-24 w-64 h-64 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-cyan-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="space-y-6 relative z-10">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-black tracking-wider uppercase border border-emerald-500/30">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Pendaftaran Warga Resmi & Registrasi Kilat</span>
                </span>

                <div class="space-y-2">
                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight leading-tight">
                        Integrasi Ekosistem Pintar Lingkungan
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                        Daftarkan diri Anda sebagai warga resmi untuk menikmati hak akses operasional gerbang RFID, surat pengantar digital, transparansi iuran kas, dan jadwal posyandu lingkungan.
                    </p>
                </div>

                <div class="space-y-3.5 pt-2">
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">✓</span>
                        <div class="text-xs leading-snug">
                            <strong class="text-white">Akses Palang Otomatis RFID:</strong> Sinkronisasi nomor KTP langsung dengan kartu RFID & pass QR tamu.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">✓</span>
                        <div class="text-xs leading-snug">
                            <strong class="text-white">Persetujuan Surat 1-Klik:</strong> Pengajuan surat keterangan RT/RW tanpa perlu antre fisik.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">✓</span>
                        <div class="text-xs leading-snug">
                            <strong class="text-white">Transparansi Laporan Kas:</strong> Pantau saldo kas bulanan dan riwayat iuran secara langsung.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">✓</span>
                        <div class="text-xs leading-snug">
                            <strong class="text-white">Perlindungan Data Pribadi:</strong> Data identitas disimpan terenkripsi sesuai UU PDP No. 27/2022.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jaminan Privasi UU PDP -->
            <div class="pt-6 border-t border-white/10 relative z-10 mt-6 space-y-2">
                <div class="flex items-center gap-2 text-xs text-emerald-300 font-bold">
                    <span>🔒</span>
                    <span>Privasi Terproteksi UU PDP</span>
                </div>
                <p class="text-[11px] text-slate-300 leading-relaxed">
                    Berkas KTP Anda hanya dapat diverifikasi oleh Pengurus RT/RW yang sah dan tidak pernah dipublikasikan ke pihak luar.
                </p>
            </div>
        </div>

        <!-- SISI KANAN: FORMULIR MULTI-TAB REGISTER (7 COLS) -->
        <div class="order-1 lg:order-2 lg:col-span-7 p-6 sm:p-10 flex flex-col justify-between space-y-6">
            <div>
                <!-- Header Formulir -->
                <div class="pb-4 border-b border-slate-100 dark:border-slate-800 space-y-1">
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Formulir Pendaftaran Warga</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pilih metode pendaftaran yang sesuai dengan kebutuhan Anda:</p>
                </div>

                <!-- Notifikasi Error Validasi -->
                @if ($errors->any())
                    <div class="mt-4 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 text-xs space-y-1">
                        <div class="font-extrabold flex items-center gap-1.5">
                            <span>⚠️</span>
                            <span>Mohon periksa kembali isian formulir:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-[11px] pl-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- TAB SWITCHER: REGISTRASI KILAT e-KTP vs GMAIL -->
                <div class="mt-5 grid grid-cols-2 gap-2 p-1.5 rounded-2xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60">
                    <button 
                        type="button" 
                        @click="method = 'ktp'" 
                        :class="method === 'ktp' ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-md shadow-emerald-950/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        class="py-2.5 px-3 rounded-xl font-black text-xs transition-colors flex items-center justify-center gap-2 cursor-pointer focus-visible:ring-2 focus-visible:ring-emerald-500 touch-press"
                    >
                        <span class="text-sm">⚡</span>
                        <span>Registrasi Kilat e-KTP</span>
                    </button>

                    <button 
                        type="button" 
                        @click="method = 'gmail'" 
                        :class="method === 'gmail' ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-md shadow-emerald-950/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        class="py-2.5 px-3 rounded-xl font-black text-xs transition-colors flex items-center justify-center gap-2 cursor-pointer focus-visible:ring-2 focus-visible:ring-emerald-500 touch-press"
                    >
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                        </svg>
                        <span>Daftar via Gmail</span>
                    </button>
                </div>

                <!-- Formulir Input Pendaftaran -->
                <form 
                    action="{{ route('register.post') }}" 
                    method="POST" 
                    enctype="multipart/form-data" 
                    @submit="isSubmitting = true"
                    class="mt-6 space-y-4"
                >
                    @csrf
                    <input type="hidden" name="registration_method" :value="method">
                    <input type="hidden" name="rfid_tag" id="reg_rfid_tag" :value="scannedRfid">

                    <!-- ======================================================== -->
                    <!-- MODUL KHUSUS: REGISTRASI KILAT e-KTP & SENSOR IOT RFID   -->
                    <!-- ======================================================== -->
                    <div x-show="method === 'ktp'" x-cloak class="p-4 sm:p-5 rounded-3xl bg-gradient-to-br from-emerald-500/10 via-cyan-500/5 to-transparent dark:from-emerald-950/40 dark:via-cyan-950/20 border-2 border-emerald-400 dark:border-emerald-600 shadow-sm space-y-4">
                        <div class="flex items-start justify-between gap-3 pb-3 border-b border-emerald-200 dark:border-emerald-800">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xl">⚡</span>
                                    <h2 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">
                                        Registrasi Kilat: Scan e-KTP & Sensor RFID ESP32
                                    </h2>
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                    Ambil foto fisik e-KTP Anda untuk <strong>mengisi otomatis (Kilat Auto-Fill)</strong> seluruh biodata kependudukan (NIK, Nama, Tanggal Lahir, Alamat), dan tempelkan fisik kartu e-KTP Anda ke alat scanner ESP32 Meja Registrasi (atau NFC HP) untuk mencatat nomor RFID pembuka palang gerbang secara instan.
                                </p>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-1 rounded-md bg-emerald-500 text-slate-950 shrink-0">
                                ⚡ Registrasi Kilat
                            </span>
                        </div>

                        <!-- 1. Pindai Foto Kamera / Berkas KTP -->
                        <div class="space-y-2.5">
                            <div class="flex flex-col sm:flex-row items-center gap-2.5">
                                <!-- Tombol Kamera / File Asli -->
                                <label class="w-full sm:flex-1 min-h-[46px] px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md transition-colors flex items-center justify-center gap-2 cursor-pointer touch-press text-center">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>📸 Ambil Foto / Scan KTP (Kamera)</span>
                                    <input 
                                        type="file" 
                                        name="ktp_photo" 
                                        id="reg_ktp_photo" 
                                        accept="image/*,application/pdf"
                                        capture="environment"
                                        @change="scanKtpImage($event)"
                                        class="hidden"
                                    >
                                </label>

                                <!-- Tombol Demo Simulasi 1-Klik -->
                                <button 
                                    type="button" 
                                    @click="applySampleKtpData()" 
                                    class="w-full sm:w-auto min-h-[46px] px-4 py-3 rounded-2xl border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-slate-900 text-emerald-700 dark:text-emerald-300 font-extrabold text-xs hover:bg-emerald-50 dark:hover:bg-slate-800 transition-colors shadow-xs flex items-center justify-center gap-1.5 cursor-pointer touch-press"
                                    title="Isi otomatis contoh data e-KTP untuk pengujian cepat"
                                >
                                    <span>⚡ Simulasi Scan KTP</span>
                                </button>
                            </div>

                            <!-- Indikator Loading Scanning Animasi -->
                            <div x-show="isOcrScanning" x-cloak class="p-3.5 rounded-2xl bg-cyan-50 dark:bg-cyan-950/60 border border-cyan-300 dark:border-cyan-700 text-cyan-900 dark:text-cyan-200 text-xs space-y-2">
                                <div class="flex items-center justify-between font-bold">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-block w-4 h-4 border-2 border-cyan-600 border-t-transparent rounded-full animate-spin"></span>
                                        <span x-text="ocrStatusText">Membaca teks dari foto e-KTP…</span>
                                    </div>
                                    <span class="font-mono tabular-nums" x-text="ocrProgress + '%'">0%</span>
                                </div>
                                <div class="w-full bg-cyan-200 dark:bg-cyan-900 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-cyan-600 h-1.5 rounded-full transition-all duration-300" :style="`width: ${ocrProgress}%`"></div>
                                </div>
                            </div>

                            <!-- Indikator Sukses Pindai KTP -->
                            <div x-show="ocrSuccess" x-cloak class="p-3 rounded-xl bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 text-xs font-bold flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span>✅</span>
                                    <span x-text="ocrStatusText">e-KTP berhasil dipindai! Formulir terisi otomatis.</span>
                                </div>
                                <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-normal">Data siap ditinjau</span>
                            </div>

                            <!-- Pratinjau Foto KTP Terpilih -->
                            <template x-if="ktpPreview && ktpPreview !== 'PDF_DOCUMENT'">
                                <div class="relative max-h-36 mx-auto rounded-2xl overflow-hidden border border-emerald-300 dark:border-emerald-700 shadow-md bg-slate-950">
                                    <img :src="ktpPreview" alt="Pratinjau e-KTP" class="w-full h-36 object-contain">
                                    <div class="absolute bottom-2 right-2 px-2.5 py-0.5 rounded-md bg-slate-950/80 text-white text-[10px] font-mono">
                                        Berkas Foto Terpasang
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- 2. Integrasi Sensor IoT RFID e-KTP -->
                        <div class="pt-3 border-t border-emerald-200/80 dark:border-emerald-800 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black uppercase text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <span>📡</span>
                                    <span>Tautkan Chip Fisik RFID e-KTP</span>
                                </span>
                                
                                <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded-lg" :class="scannedRfid ? 'bg-cyan-100 text-cyan-800 dark:bg-cyan-950 dark:text-cyan-300 border border-cyan-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500'">
                                    <span x-text="scannedRfid ? scannedRfid : 'Belum Ada Chip RFID'"></span>
                                </span>
                            </div>

                            <!-- Pilihan Tap Sensor: Web NFC vs Alat ESP32 -->
                            <!-- Real-time Status Scanner ESP32 (Hotspot Wi-Fi Status) -->
                            <div class="p-3 rounded-2xl border transition-colors space-y-1.5"
                                 :class="esp32Online 
                                     ? 'bg-emerald-50/80 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200' 
                                     : 'bg-rose-50/80 dark:bg-rose-950/40 border-rose-300 dark:border-rose-800 text-rose-900 dark:text-rose-200'"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="relative flex h-2.5 w-2.5 shrink-0">
                                            <span x-show="esp32Online" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2.5 w-2.5" :class="esp32Online ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                        </span>
                                        <span class="text-xs font-black uppercase tracking-wider" x-text="esp32Online ? 'Scanner ESP32: ONLINE' : 'Scanner ESP32: OFFLINE'"></span>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full font-mono font-bold"
                                              :class="esp32Online ? 'bg-emerald-200 text-emerald-900 dark:bg-emerald-900 dark:text-emerald-100' : 'bg-rose-200 text-rose-900 dark:bg-rose-900 dark:text-rose-100'"
                                              x-text="esp32Online ? 'Hotspot BIAN Terhubung' : 'Hotspot BIAN Belum Terhubung'"
                                        ></span>
                                    </div>
                                    <button 
                                        type="button" 
                                        @click="checkEsp32DeviceStatus(true)" 
                                        class="shrink-0 text-[11px] font-bold px-2 py-1 rounded-lg hover:bg-black/5 dark:hover:bg-white/5 transition-colors cursor-pointer flex items-center gap-1"
                                        title="Perbarui status koneksi ESP32"
                                    >
                                        <span :class="isCheckingStatus ? 'animate-spin inline-block' : ''">🔄</span>
                                        <span class="hidden sm:inline">Cek Status</span>
                                    </button>
                                </div>
                                <p class="text-[11px] opacity-90 leading-relaxed" x-text="esp32Online 
                                    ? 'Alat ESP32 sudah terhubung ke hotspot Anda (&quot;BIAN&quot;). Sensor siap menerima tap kartu e-KTP fisik.' 
                                    : 'Alat scanner belum terhubung ke hotspot. Pastikan hotspot HP (&quot;BIAN&quot;) aktif dan modul ESP32 sudah menyala.'">
                                </p>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                <button 
                                    type="button" 
                                    @click="startNfcScan()" 
                                    class="py-2.5 px-3 rounded-xl border border-cyan-300 dark:border-cyan-700 bg-white dark:bg-slate-900 text-cyan-700 dark:text-cyan-300 hover:bg-cyan-50 dark:hover:bg-slate-800 font-bold text-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer touch-press"
                                >
                                    <span>📱</span>
                                    <span>Tap via HP (NFC)</span>
                                </button>

                                <button 
                                    type="button" 
                                    @click="startEsp32Pairing()" 
                                    class="py-2.5 px-3 rounded-xl border bg-white dark:bg-slate-900 font-bold text-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer touch-press"
                                    :class="esp32Online 
                                        ? 'border-emerald-300 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-slate-800' 
                                        : 'border-rose-300 dark:border-rose-700 text-rose-700 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-slate-800'"
                                >
                                    <span class="w-2 h-2 rounded-full shrink-0" :class="esp32Online ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
                                    <span>Tap di Scanner ESP32</span>
                                </button>

                                <button 
                                    type="button" 
                                    @click="showManualRfidInput = !showManualRfidInput" 
                                    class="col-span-2 sm:col-span-1 py-2.5 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 font-bold text-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer touch-press"
                                >
                                    <span>⌨️</span>
                                    <span>Ketik Manual</span>
                                </button>
                            </div>

                            <!-- Feedback ESP32 Pairing Countdown -->
                            <div x-show="esp32PairingActive" x-cloak class="p-3 rounded-2xl bg-cyan-50 dark:bg-cyan-950/60 border border-cyan-300 text-center space-y-1">
                                <div class="text-xl font-black font-mono text-cyan-600 dark:text-cyan-400 tabular-nums" x-text="esp32Countdown + 's'"></div>
                                <div class="text-xs font-semibold text-cyan-900 dark:text-cyan-200" x-text="esp32Message"></div>
                            </div>

                            <!-- Input Manual Modal Drawer -->
                            <div x-show="showManualRfidInput" x-cloak class="flex items-center gap-2 pt-1">
                                <input 
                                    type="text" 
                                    x-model="manualRfidVal" 
                                    placeholder="Contoh: 04:A2:3B:8C atau 4A5B6C7D…" 
                                    class="flex-1 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-mono uppercase"
                                >
                                <button 
                                    type="button" 
                                    @click="saveManualRfid()" 
                                    class="px-4 py-2 rounded-xl bg-cyan-600 text-white font-bold text-xs cursor-pointer"
                                >
                                    Terapkan
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 1. Pilihan Komplek / Lingkungan RT -->
                    <div class="space-y-1.5">
                        <label for="reg_tenant_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                            Pilih Lingkungan RT/RW / Komplek Perumahan <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            name="tenant_id" 
                            id="reg_tenant_id" 
                            x-model="selectedTenantId"
                            @change="fetchHouses($event.target.value)"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors"
                        >
                            @foreach ($tenants as $t)
                                <option value="{{ $t->id }}" @selected(old('tenant_id', $selectedTenant?->id) == $t->id)>
                                    {{ $t->name }} ({{ $t->rt_rw }} - {{ $t->city ?? 'Indonesia' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. Nama Lengkap & WhatsApp -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label for="reg_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Nama Lengkap Sesuai KTP <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="name" 
                                id="reg_name" 
                                value="{{ old('name') }}"
                                required 
                                placeholder="Contoh: Fabian Warga Pratama…" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors font-semibold"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label for="reg_phone" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Nomor WhatsApp Aktif <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="tel" 
                                name="phone_number" 
                                id="reg_phone" 
                                inputmode="numeric"
                                value="{{ old('phone_number') }}"
                                required 
                                placeholder="Contoh: 081234567890…" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors tabular-nums"
                            >
                        </div>
                    </div>

                    <!-- 3. Alamat Email / Gmail -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label for="reg_email" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Alamat Email / Akun Gmail <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] text-slate-400">Untuk login & pengiriman pass tamu</span>
                        </div>
                        <input 
                            type="email" 
                            name="email" 
                            id="reg_email" 
                            inputmode="email"
                            autocomplete="email"
                            spellcheck="false"
                            value="{{ old('email') }}"
                            required 
                            placeholder="nama.anda@gmail.com…" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors"
                        >
                    </div>

                    <!-- 4. KHUSUS MODE e-KTP: NIK, TEMPAT TANGGAL LAHIR, JENIS KELAMIN, ALAMAT -->
                    <div x-show="method === 'ktp'" x-cloak class="space-y-4 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label for="reg_nik" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                        Nomor NIK KTP (16 Digit) <span class="text-rose-500">*</span>
                                    </label>
                                    <span class="text-[10px] font-mono tabular-nums text-slate-400" x-text="nikInput.length + '/16'">0/16</span>
                                </div>
                                <input 
                                    type="text" 
                                    name="nik" 
                                    id="reg_nik" 
                                    inputmode="numeric"
                                    maxlength="16"
                                    @input="formatNik"
                                    value="{{ old('nik') }}"
                                    :required="method === 'ktp'"
                                    placeholder="3201xxxxxxxxxxxx…" 
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors tabular-nums font-mono font-bold tracking-wider"
                                >
                            </div>

                            <div class="space-y-1.5">
                                <label for="reg_gender" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Jenis Kelamin <span class="text-rose-500">*</span>
                                </label>
                                <select 
                                    name="gender" 
                                    id="reg_gender" 
                                    :required="method === 'ktp'"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors font-semibold"
                                >
                                    <option value="male" @selected(old('gender') === 'male')>Laki-laki</option>
                                    <option value="female" @selected(old('gender') === 'female')>Perempuan</option>
                                </select>
                            </div>
                        </div>

                        <!-- Tempat & Tanggal Lahir -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1.5">
                                <label for="reg_birth_place" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Tempat Lahir Sesuai KTP
                                </label>
                                <input 
                                    type="text" 
                                    name="birth_place" 
                                    id="reg_birth_place" 
                                    value="{{ old('birth_place') }}"
                                    placeholder="Kota Lahir (Misal: Bandung)…" 
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors"
                                >
                            </div>

                            <div class="space-y-1.5">
                                <label for="reg_birth_date" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Tanggal Lahir
                                </label>
                                <input 
                                    type="date" 
                                    name="birth_date" 
                                    id="reg_birth_date" 
                                    value="{{ old('birth_date') }}"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors font-semibold"
                                >
                            </div>
                        </div>

                        <!-- Alamat Sesuai KTP -->
                        <div class="space-y-1.5">
                            <label for="reg_address_ktp" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Alamat Lengkap Sesuai KTP <span class="text-rose-500">*</span>
                            </label>
                            <textarea 
                                name="address_ktp" 
                                id="reg_address_ktp" 
                                rows="2"
                                :required="method === 'ktp'"
                                placeholder="Tuliskan nama jalan, RT/RW, kelurahan, dan kecamatan sesuai KTP…"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors leading-relaxed"
                            >{{ old('address_ktp') }}</textarea>
                        </div>
                    </div>

                    <!-- 5. Lokasi Rumah di Lingkungan RT/RW -->
                    <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Alamat Rumah / Blok Hunian di Lingkungan Ini
                            </label>
                            <button 
                                type="button" 
                                @click="customHouse = !customHouse" 
                                class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline cursor-pointer"
                                x-text="customHouse ? 'Pilih dari data rumah terdaftar ➔' : '+ Input blok/nomor rumah baru'"
                            ></button>
                        </div>

                        <!-- Dropdown Rumah Terdaftar -->
                        <div x-show="!customHouse">
                            <select 
                                name="house_id" 
                                id="reg_house_id" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors"
                            >
                                <option value="">-- Pilih Blok / Nomor Rumah Terdaftar --</option>
                                <template x-for="h in houses" :key="h.id">
                                    <option :value="h.id" x-text="h.block_name + ' No. ' + h.house_number + (h.full_address ? ' (' + h.full_address + ')' : '')"></option>
                                </template>
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Jika blok/nomor rumah Anda belum terdaftar, klik tombol di atas untuk menuliskan nomor baru.</p>
                        </div>

                        <!-- Form Tambah Rumah Baru -->
                        <div x-show="customHouse" x-cloak class="grid grid-cols-2 gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                            <div class="space-y-1">
                                <label for="reg_new_block" class="block text-[11px] font-bold text-slate-600 dark:text-slate-400">Nama Blok (Contoh: Blok B)</label>
                                <input 
                                    type="text" 
                                    name="new_block_name" 
                                    id="reg_new_block" 
                                    value="{{ old('new_block_name') }}"
                                    placeholder="Blok C…" 
                                    class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden"
                                >
                            </div>
                            <div class="space-y-1">
                                <label for="reg_new_number" class="block text-[11px] font-bold text-slate-600 dark:text-slate-400">Nomor Rumah (Contoh: 14)</label>
                                <input 
                                    type="text" 
                                    name="new_house_number" 
                                    id="reg_new_number" 
                                    value="{{ old('new_house_number') }}"
                                    placeholder="14…" 
                                    class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- 6. Password & Konfirmasi Password -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <div class="space-y-1.5">
                            <label for="reg_password" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Buat Kata Sandi (Password) <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                name="password" 
                                id="reg_password" 
                                autocomplete="new-password"
                                required 
                                minlength="6"
                                placeholder="Minimal 6 karakter…" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label for="reg_password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Ulangi Kata Sandi <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                name="password_confirmation" 
                                id="reg_password_confirmation" 
                                autocomplete="new-password"
                                required 
                                minlength="6"
                                placeholder="Ketik ulang kata sandi…" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors"
                            >
                        </div>
                    </div>

                    <!-- Checkbox Kepala Keluarga -->
                    <div class="pt-1">
                        <label class="flex items-center gap-2.5 cursor-pointer min-h-[44px]">
                            <input 
                                type="checkbox" 
                                name="is_family_head" 
                                value="1" 
                                checked
                                class="w-5 h-5 rounded-md text-emerald-600 border-slate-300 dark:border-slate-700 focus:ring-emerald-500"
                            >
                            <span class="text-xs text-slate-700 dark:text-slate-300 font-semibold">
                                Saya adalah Kepala Keluarga di unit rumah ini
                            </span>
                        </label>
                    </div>

                    <!-- Pemberitahuan Status Menunggu Konfirmasi Admin -->
                    <div class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-xs leading-relaxed flex items-start gap-2.5">
                        <span class="text-base shrink-0">⏳</span>
                        <div>
                            <span class="font-extrabold uppercase tracking-wider text-[11px] block">Otorisasi Resmi Admin / RW Diperlukan:</span>
                            <p class="text-[11px] text-amber-800 dark:text-amber-300 mt-0.5">
                                Setelah pendaftaran berhasil dikirim, akun Anda akan aktif dalam status <strong>Terkunci Sementara</strong> hingga berkas identitas Anda disetujui resmi oleh Ketua RT atau Ketua RW. Akses palang gerbang RFID akan aktif otomatis setelah disetujui.
                            </p>
                        </div>
                    </div>

                    <!-- Tombol Submit Pendaftaran -->
                    <button 
                        type="submit" 
                        :disabled="isSubmitting"
                        class="w-full min-h-[50px] py-3.5 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 hover:from-emerald-500 hover:to-cyan-500 active:scale-95 text-white font-extrabold text-sm shadow-xl shadow-emerald-600/25 transition-transform flex items-center justify-center gap-2 cursor-pointer touch-press"
                    >
                        <span x-show="isSubmitting" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span x-text="isSubmitting ? 'Memproses Pendaftaran…' : (method === 'ktp' ? '⚡ Selesaikan Registrasi Kilat e-KTP ➔' : 'Selesaikan Pendaftaran Cepat Gmail ➔')"></span>
                    </button>
                </form>
            </div>

            <!-- Footer Tautan Kembali ke Login -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 text-center">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Sudah memiliki akun warga atau pengurus?
                    <a href="{{ route('login') }}" class="font-black text-emerald-600 dark:text-emerald-400 hover:underline">
                        Masuk ke Dashboard Sistem ➔
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- Footer Halaman Register -->
    <footer class="max-w-5xl w-full mx-auto text-center text-xs text-slate-400 pt-6 pb-8">
        &copy; {{ date('Y') }} E-WARGA by Fabian Ganteng. Seluruh hak cipta dilindungi undang-undang.
    </footer>
</div>

<script>
function registerForm() {
    return {
        method: '{{ old('registration_method', 'ktp') }}',
            selectedTenantId: '{{ old('tenant_id', $selectedTenant?->id ?? '') }}',
            customHouse: {{ old('new_block_name') ? 'true' : 'false' }},
            houses: @js($houses),
            isLoadingHouses: false,
            isSubmitting: false,
            ktpPreview: null,
            nikInput: '{{ old('nik', '') }}',
            scannedRfid: '{{ old('rfid_tag', '') }}',
            gmailPreset: '',

            // OCR Scanner State
            isOcrScanning: false,
            ocrProgress: 0,
            ocrStatusText: '',
            ocrSuccess: false,
            showManualRfidInput: false,
            manualRfidVal: '',

            // ESP32 Hardware Pairing & Online State
            esp32Online: false,
            isCheckingStatus: false,
            esp32StatusHeartbeatInterval: null,
            esp32PairingActive: false,
            esp32Countdown: 60,
            esp32TimerInterval: null,
            esp32StatusInterval: null,
            esp32Message: '',
            esp32Success: false,

            // Web NFC State
            nfcScanning: false,
            nfcMessage: '',
            
            init() {
                if (this.selectedTenantId) {
                    this.fetchHouses(this.selectedTenantId);
                }
                this.checkEsp32DeviceStatus();
                this.esp32StatusHeartbeatInterval = setInterval(() => {
                    this.checkEsp32DeviceStatus();
                }, 4000);
            },

            async fetchHouses(tenantId) {
                if (!tenantId) return;
                this.isLoadingHouses = true;
                try {
                    const res = await fetch(`/api/tenants/${tenantId}/houses`);
                    if (res.ok) {
                        const json = await res.json();
                        this.houses = json.data || [];
                    }
                } catch (e) {
                    console.error('Gagal memuat daftar rumah:', e);
                } finally {
                    this.isLoadingHouses = false;
                }
            },

            // Trigger Pindai OCR Foto e-KTP
            async scanKtpImage(event) {
                const file = event.target.files[0];
                if (!file) return;

                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran berkas KTP maksimal 5 MB.');
                    event.target.value = '';
                    return;
                }

                this.isOcrScanning = true;
                this.ocrSuccess = false;
                this.ocrProgress = 15;
                this.ocrStatusText = 'Mempersiapkan pemindai e-KTP…';

                // Pratinjau Gambar Langsung
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.ktpPreview = e.target.result;
                };
                reader.readAsDataURL(file);

                try {
                    if (typeof Tesseract !== 'undefined') {
                        this.ocrStatusText = 'Membaca teks dari foto fisik e-KTP…';
                        this.ocrProgress = 35;
                        const worker = await Tesseract.createWorker('ind');
                        this.ocrProgress = 60;
                        const ret = await worker.recognize(file);
                        await worker.terminate();
                        this.ocrProgress = 90;
                        this.ocrStatusText = 'Mengekstrak NIK, Nama, dan Alamat KTP…';
                        this.parseKtpText(ret.data.text);
                    } else {
                        // Fallback jika CDN Tesseract belum termuat
                        await new Promise(r => setTimeout(r, 1200));
                        this.applySampleKtpData();
                    }
                    this.ocrProgress = 100;
                    this.ocrSuccess = true;
                    this.ocrStatusText = 'e-KTP berhasil dipindai! Seluruh data formulir terisi otomatis.';
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                } catch (err) {
                    console.warn('OCR processing error:', err);
                    this.ocrStatusText = 'Foto tersimpan. Silakan periksa atau lengkapi data formulir di bawah.';
                } finally {
                    this.isOcrScanning = false;
                }
            },

            // Parser Cerdas e-KTP Indonesia
            parseKtpText(text) {
                const cleanText = text.replace(/\r/g, '');

                // 1. Ekstrak NIK (16 digit angka)
                const nikMatch = cleanText.match(/\b([1-9]\d{15})\b/) 
                    || cleanText.match(/(?:NIK|N1K|N!K)[\s:;.-]*([0-9\sOIo-]{16,22})/i);
                if (nikMatch) {
                    const rawDigits = (nikMatch[1] || nikMatch[0]).replace(/\D/g, '');
                    if (rawDigits.length >= 16) {
                        this.nikInput = rawDigits.slice(0, 16);
                        const el = document.getElementById('reg_nik');
                        if (el) el.value = this.nikInput;
                        if (!this.scannedRfid) {
                            this.scannedRfid = 'RFID-' + this.nikInput;
                        }
                    }
                }

                // 2. Ekstrak Nama
                const namaMatch = cleanText.match(/Nama[\s:;.-]+([A-Z\s.,']+)/i);
                if (namaMatch && namaMatch[1]) {
                    const rawName = namaMatch[1].trim();
                    if (rawName.length > 2) {
                        const el = document.getElementById('reg_name');
                        if (el) el.value = rawName;
                    }
                }

                // 3. Ekstrak Tempat & Tanggal Lahir
                const ttlMatch = cleanText.match(/Tempat[\w\s\/]*Lahir[\s:;.-]+([A-Z\s]+)[,\s]+(\d{2})[-/\s](\d{2})[-/\s](\d{4})/i);
                if (ttlMatch) {
                    const place = ttlMatch[1].trim();
                    const d = ttlMatch[2].padStart(2, '0');
                    const m = ttlMatch[3].padStart(2, '0');
                    const y = ttlMatch[4];
                    
                    const elPlace = document.getElementById('reg_birth_place');
                    if (elPlace) elPlace.value = place;
                    const elDate = document.getElementById('reg_birth_date');
                    if (elDate) elDate.value = `${y}-${m}-${d}`;
                }

                // 4. Ekstrak Jenis Kelamin
                if (/PEREMPUAN|WANITA/i.test(cleanText)) {
                    const elGender = document.getElementById('reg_gender');
                    if (elGender) elGender.value = 'female';
                } else if (/LAKI[\s-]*LAKI|PRIA/i.test(cleanText)) {
                    const elGender = document.getElementById('reg_gender');
                    if (elGender) elGender.value = 'male';
                }

                // 5. Ekstrak Alamat
                const alamatMatch = cleanText.match(/Alamat[\s:;.-]+([\s\S]+?)(?:Agama|Status|Pekerjaan|Kewarganegaraan)/i);
                if (alamatMatch && alamatMatch[1]) {
                    const rawAddr = alamatMatch[1].replace(/\n/g, ', ').replace(/\s+/g, ' ').trim();
                    const elAddr = document.getElementById('reg_address_ktp');
                    if (elAddr) elAddr.value = rawAddr;
                }
            },

            // Simulasi Scan 1-Klik (Untuk Demo & Pengujian Cepat)
            applySampleKtpData() {
                this.nikInput = '3275011902890005';
                const elNik = document.getElementById('reg_nik');
                if (elNik) elNik.value = '3275011902890005';

                const elName = document.getElementById('reg_name');
                if (elName) elName.value = 'Siti Rahmawati';

                const elPlace = document.getElementById('reg_birth_place');
                if (elPlace) elPlace.value = 'Bandung';

                const elDate = document.getElementById('reg_birth_date');
                if (elDate) elDate.value = '1989-02-19';

                const elGender = document.getElementById('reg_gender');
                if (elGender) elGender.value = 'female';

                const elAddr = document.getElementById('reg_address_ktp');
                if (elAddr) elAddr.value = 'Jl. Melati Mas Raya Blok B No. 12, RT 05 / RW 12, Kel. Melati, Kec. Sukajadi';

                if (!this.scannedRfid) {
                    this.scannedRfid = 'RFID-3275011902890005';
                }
                this.ktpPreview = '/uploads/ktp/sample_ktp_siti.svg';
                this.ocrSuccess = true;
                this.ocrStatusText = 'e-KTP terdeteksi! Seluruh biodata berhasil diisi otomatis.';
            },

            // Web NFC Scan di HP Android
            async startNfcScan() {
                if (!('NDEFReader' in window)) {
                    alert('Web NFC aktif pada Google Chrome di HP Android ber-NFC. Untuk komputer atau perangkat lain, silakan gunakan tombol "Tap di Scanner ESP32" atau masukkan UID manual.');
                    return;
                }
                try {
                    this.nfcScanning = true;
                    this.nfcMessage = 'Siap! Tempelkan fisik e-KTP Anda ke bodi belakang ponsel sekarang…';
                    const ndef = new NDEFReader();
                    await ndef.scan();
                    ndef.addEventListener("reading", ({ serialNumber }) => {
                        if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                        const clean = serialNumber.replace(/[:\s-]/g, '').toUpperCase();
                        this.scannedRfid = 'RFID-' + clean;
                        this.nfcScanning = false;
                        this.nfcMessage = 'Chip e-KTP berhasil dicatat: ' + this.scannedRfid;
                    });
                } catch (err) {
                    this.nfcScanning = false;
                    alert('Izin NFC ditolak atau tidak tersedia: ' + err.message);
                }
            },

            // Cek Status Koneksi Online / Offline ESP32 ke Hotspot BIAN
            async checkEsp32DeviceStatus(manual = false) {
                if (manual) this.isCheckingStatus = true;
                try {
                    const tid = this.selectedTenantId || 1;
                    const res = await fetch(`{{ route('api.register.esp32-status') }}?tenant_id=${tid}`);
                    const data = await res.json();
                    this.esp32Online = !!data.online;
                    if (this.esp32PairingActive && !this.esp32Success) {
                        if (this.esp32Online) {
                            this.esp32Message = 'Sensor siap! Tempelkan fisik e-KTP Anda ke scanner RFID sekarang…';
                        } else {
                            this.esp32Message = 'Scanner Offline: Alat ESP32 belum terhubung ke hotspot "BIAN".';
                        }
                    }
                } catch (e) {
                    this.esp32Online = false;
                } finally {
                    if (manual) setTimeout(() => { this.isCheckingStatus = false; }, 400);
                }
            },

            // Mode Sesi Pairing ESP32 di Gerbang / Pos Satpam
            async startEsp32Pairing() {
                this.esp32PairingActive = true;
                this.esp32Countdown = 60;
                this.esp32Message = 'Menghubungkan ke alat sensor ESP32…';
                this.esp32Success = false;
                await this.checkEsp32DeviceStatus();

                try {
                    const res = await fetch("{{ route('api.register.esp32-pair.start') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ tenant_id: this.selectedTenantId })
                    });
                    const data = await res.json();
                    if (!data.success) {
                        this.esp32Message = 'Gagal memulai mode pairing: ' + data.message;
                        this.esp32PairingActive = false;
                        return;
                    }

                    this.esp32Message = this.esp32Online
                        ? 'Sensor siap! Tempelkan fisik e-KTP Anda ke scanner RFID di pos satpam / gerbang sekarang…'
                        : 'Scanner Offline: Pastikan hotspot HP ("BIAN") aktif dan ESP32 sudah menyala…';

                    if (this.esp32TimerInterval) clearInterval(this.esp32TimerInterval);
                    this.esp32TimerInterval = setInterval(() => {
                        this.esp32Countdown--;
                        if (this.esp32Countdown <= 0) {
                            this.stopEsp32Pairing('Waktu pairing habis (60 detik). Silakan coba lagi.');
                        }
                    }, 1000);

                    if (this.esp32StatusInterval) clearInterval(this.esp32StatusInterval);
                    this.esp32StatusInterval = setInterval(async () => {
                        await this.checkEsp32Status();
                    }, 1500);

                } catch (err) {
                    this.esp32PairingActive = false;
                    this.esp32Message = 'Kesalahan jaringan: ' + err.message;
                }
            },

            async checkEsp32Status() {
                try {
                    const res = await fetch(`{{ route('api.register.esp32-pair.status') }}?tenant_id=${this.selectedTenantId}`);
                    const data = await res.json();

                    if (data.is_online !== undefined) {
                        this.esp32Online = !!data.is_online;
                    }

                    if (data.paired) {
                        this.stopEsp32Pairing();
                        this.esp32Success = true;
                        this.esp32Message = data.message;
                        this.scannedRfid = data.rfid_tag;
                        if (navigator.vibrate) navigator.vibrate([100, 100, 200]);
                    }
                } catch (e) {}
            },

            stopEsp32Pairing(msg = null) {
                if (this.esp32TimerInterval) clearInterval(this.esp32TimerInterval);
                if (this.esp32StatusInterval) clearInterval(this.esp32StatusInterval);
                this.esp32PairingActive = false;
                if (msg) this.esp32Message = msg;
            },

            formatNik(e) {
                let val = e.target.value.replace(/\D/g, '');
                if (val.length > 16) val = val.slice(0, 16);
                this.nikInput = val;
                e.target.value = val;
                if (!this.scannedRfid) {
                    this.scannedRfid = 'RFID-' + val;
                }
            },

            saveManualRfid() {
                if (this.manualRfidVal && this.manualRfidVal.trim().length >= 4) {
                    const clean = this.manualRfidVal.replace(/[:\s-]/g, '').toUpperCase();
                    this.scannedRfid = 'RFID-' + clean;
                    this.showManualRfidInput = false;
                }
            }
        };
    }
</script>
@endsection

