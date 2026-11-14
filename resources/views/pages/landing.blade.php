@extends('layouts.app')

@section('content')
<div class="space-y-24 sm:space-y-32 py-8 sm:py-16 overflow-x-hidden">

    <!-- ======================================================== -->
    <!-- 1. HERO SECTION (HIGH-CONVERSION ENTERPRISE BRANDING)     -->
    <!-- Menjawab Solusi dalam 5 Detik Pertama Sesuai Konsep UI/UX-->
    <!-- ======================================================== -->
    <section id="hero" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        
        <!-- Ambient Background Glow Orb (Apple / Linear Aesthetic) -->
        <div class="absolute -top-16 left-1/2 -translate-x-1/2 w-[650px] h-[320px] bg-gradient-to-tr from-emerald-500/15 via-teal-400/10 to-indigo-500/10 blur-3xl pointer-events-none rounded-full animate-float-ambient -z-10"></div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
            
            <!-- Left Hero Content: Hierarki Visual & Value Proposition -->
            <div class="lg:col-span-7 space-y-6 sm:space-y-8">
                
                <!-- Trust Badge -->
                <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-emerald-50 dark:bg-emerald-950/70 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm font-extrabold tracking-wide shadow-xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                    <span>PLATFORM SMART COMMUNITY & IOT LIVING #1 DI INDONESIA</span>
                </div>
                
                <!-- Main Headline (Hierarki Visual H1 Dominan & Jelas) -->
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 dark:text-white tracking-tight leading-[1.12]">
                    Modernisasi Lingkungan RT/RW, <span class="bg-gradient-to-r from-emerald-600 via-teal-500 to-emerald-500 bg-clip-text text-transparent">Cluster Perumahan</span> & Desa Mandiri.
                </h1>

                <!-- Body Subtitle: Keterbacaan Tinggi (16px-18px) & Anti-Sesak -->
                <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                    Tinggalkan cara lama tagih iuran keliling dan portal bambu manual. <strong>E-WARGA</strong> menyediakan ekosistem terpadu: integrasi <em>Palang Pintu Otomatis (Smart Gate)</em>, pembukuan kas terbuka anti-curiga berbasis grafik, surat pengantar digital berstempel QR resmi, hingga jadwal siskamling & posyandu dalam satu genggaman.
                </p>

                <!-- Action CTA Buttons (Alur Singkat / Friction Reduction) -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 pt-2">
                    <a href="#kerjasama" class="px-8 py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-2xl shadow-xl shadow-emerald-600/30 text-base transition-all transform hover:-translate-y-0.5 text-center flex items-center justify-center gap-3 cursor-pointer touch-press btn-shimmer">
                        <span>Daftarkan Lingkungan Anda</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    
                    @auth
                        @if(Auth::user()->role === 'super_admin')
                            <a href="{{ route('superadmin.index') }}" class="px-7 py-4 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black rounded-2xl shadow-lg shadow-amber-500/20 text-base transition flex items-center justify-center gap-2 cursor-pointer touch-press">
                                <span>⚡ Buka Pusat Super Admin</span>
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="px-7 py-4 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 font-extrabold rounded-2xl border-2 border-slate-200 dark:border-slate-800 text-base transition shadow-xs flex items-center justify-center gap-2 cursor-pointer touch-press">
                                <span>Buka Dashboard Aktif ➔</span>
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="px-7 py-4 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 font-extrabold rounded-2xl border-2 border-slate-200 dark:border-slate-800 text-base transition shadow-xs flex items-center justify-center gap-2 cursor-pointer touch-press">
                            <span>Masuk ke Akun Anda</span>
                            <span>➔</span>
                        </a>
                    @endauth
                </div>

                <!-- Live Metrics Summary: Bukti Sosial Real-Time & Transparan (Count-Up Animation) -->
                <div class="grid grid-cols-3 gap-4 sm:gap-6 pt-6 border-t border-slate-200 dark:border-slate-800">
                    <div class="space-y-1">
                        <div x-data="countUp({{ $totalHouses }}, 1200)" x-text="displayValue + ' Unit'" class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-mono">
                            {{ $totalHouses }} Unit
                        </div>
                        <div class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400">Rumah Terdata</div>
                    </div>
                    <div class="space-y-1">
                        <div x-data="countUp({{ $totalUsers }}, 1500)" x-text="displayValue + ' Jiwa'" class="text-2xl sm:text-3xl font-black text-indigo-600 dark:text-indigo-400 font-mono">
                            {{ $totalUsers }} Jiwa
                        </div>
                        <div class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400">Warga Terdaftar</div>
                    </div>
                    <div class="space-y-1">
                        <div x-data="countUp({{ (int) $totalCash }}, 1800, true)" x-text="displayValue" class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
                            Rp {{ number_format($totalCash, 0, ',', '.') }}
                        </div>
                        <div class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400">Kas Terbuka Live</div>
                    </div>
                </div>
            </div>

            <!-- Right Hero Section: BENTO SHOWCASE (Interactive Motion IoT Simulator) -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- KARTU 1: LIVE INTERACTIVE BENTO PREVIEW DENGAN SIMULASI PALANG PINTU -->
                <div x-data="heroGateDemo" class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 sm:p-7 space-y-5 relative overflow-hidden transition-all duration-300">
                    
                    <!-- Top Bar Kartu: Telemetri Gerbang -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <span class="w-3 h-3 rounded-full" :class="isOpen ? 'bg-amber-400 animate-ping' : 'bg-emerald-500 animate-ping'"></span>
                            <span class="font-mono text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">
                                SMART GATE ESP32 #01
                            </span>
                        </div>
                        <span 
                            class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border transition-colors duration-200"
                            :class="isOpen ? 'bg-amber-50 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800' : 'bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'"
                            x-text="isOpen ? 'PALANG TERBUKA (' + timer + 's)' : 'ONLINE • AKTIF'"
                        >
                            ONLINE • AKTIF
                        </span>
                    </div>

                    <!-- Visual Palang Pintu Fisik 2D Interaktif -->
                    <div class="p-5 rounded-2xl bg-slate-950 text-white space-y-4 relative overflow-hidden border border-slate-800">
                        
                        <!-- Radar & Tiang Barrier Gate -->
                        <div class="flex items-center justify-between pb-2 border-b border-slate-800/80 text-[11px] font-mono">
                            <div class="flex items-center gap-2">
                                <span class="relative flex h-2 w-2">
                                    <span class="radar-pulse-ring absolute inline-flex h-full w-full rounded-full bg-cyan-400"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-cyan-500"></span>
                                </span>
                                <span class="text-slate-300">Radar Sensor Anti-Bentur</span>
                            </div>
                            <span class="text-cyan-400 font-bold">Respon: &lt; 0.3 dtk</span>
                        </div>

                        <!-- Palang Gerbang yang Terangkat saat isOpen = true -->
                        <div class="flex items-end justify-between py-2 px-2">
                            <!-- Tiang Barrier Motor Housing -->
                            <div class="flex items-end gap-2">
                                <div class="w-9 h-14 bg-gradient-to-t from-slate-800 via-amber-500 to-amber-400 rounded-t-lg shadow-md flex flex-col items-center justify-center p-1 text-[8px] font-black text-slate-950">
                                    <span>IOT</span>
                                    <span class="w-1.5 h-1.5 rounded-full mt-1" :class="isOpen ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
                                </div>

                                <!-- Palang Bergaris Merah-Putih dengan CSS Rotate Animation -->
                                <div class="pb-2">
                                    <div 
                                        class="barrier-arm w-32 sm:w-44 h-3 bg-gradient-to-r from-red-600 via-white to-red-600 rounded-r-md shadow-lg border border-slate-700/60"
                                        :class="{ 'is-open': isOpen }"
                                    ></div>
                                </div>
                            </div>

                            <!-- Indikator Status Suara / Lampu Strobo -->
                            <div class="text-right font-mono text-[11px]">
                                <span class="block text-[10px] text-slate-400 uppercase">Status Palang:</span>
                                <span class="font-extrabold" :class="isOpen ? 'text-amber-400' : 'text-emerald-400'" x-text="isOpen ? '▲ TERANGKAT' : '▼ MENUTUP'">
                                    ▼ MENUTUP
                                </span>
                            </div>
                        </div>

                        <!-- Event Log Riwayat yang Berganti Otomatis -->
                        <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between gap-3 text-xs font-mono">
                            <div class="flex items-center gap-3 truncate">
                                <span class="text-xl shrink-0" x-text="events[activeEventIndex].icon">🚗</span>
                                <div class="truncate">
                                    <div class="font-bold text-white text-xs truncate" x-text="events[activeEventIndex].title">B 1984 RFS • Honda CR-V</div>
                                    <div class="text-[10px] text-slate-400 truncate" x-text="events[activeEventIndex].subtitle">Bpk. Bambang Pamungkas (Blok B4/12)</div>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-emerald-500/20 text-emerald-300 block" x-text="events[activeEventIndex].status">✓ DIIZINKAN</span>
                                <span class="text-[9px] text-slate-400 block mt-0.5" x-text="events[activeEventIndex].type">RFID Tap</span>
                            </div>
                        </div>

                        <!-- Tombol Interaktif Uji Buka Palang Langsung di Hero -->
                        <button 
                            type="button" 
                            @click="triggerOpen()" 
                            :disabled="isOpen"
                            class="w-full py-2.5 px-4 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-500 disabled:bg-slate-800 disabled:text-slate-500 text-white shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer touch-press"
                        >
                            <span x-show="!isOpen">👆 Tekan untuk Uji Buka Palang (Live Demo)</span>
                            <span x-show="isOpen" x-cloak>⚡ Palang Terbuka! Menutup otomatis dalam <strong x-text="timer + 's'"></strong></span>
                        </button>
                    </div>

                    <!-- Mini Widget 2 Kolom: Kas Live & Surat Pengantar dengan Animasi Progress -->
                    <div class="grid grid-cols-2 gap-3">
                        <div 
                            x-data="{ progressWidth: '0%' }" 
                            x-init="setTimeout(() => progressWidth = '92%', 300)"
                            class="p-3.5 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200/60 dark:border-emerald-800/60 space-y-2"
                        >
                            <div class="flex items-center justify-between text-[10px] font-black uppercase text-emerald-800 dark:text-emerald-300">
                                <span>Iuran Warga</span>
                                <span>92%</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-emerald-200 dark:bg-emerald-900/60 overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full transition-all duration-1000 ease-out" :style="'width: ' + progressWidth"></div>
                            </div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">Kas transparan anti-curiga</div>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-200/60 dark:border-indigo-800/60 space-y-1">
                            <div class="text-[10px] font-black uppercase text-indigo-800 dark:text-indigo-300">Surat Pengantar RT</div>
                            <div class="text-xl font-black text-indigo-700 dark:text-indigo-400 font-mono">1-Klik TTD</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">Stempel QR resmi digital</div>
                        </div>
                    </div>

                    <!-- Tombol Coba Simulator Penuh -->
                    <a href="{{ route('simulator') }}" class="w-full py-3 px-4 rounded-xl font-bold text-xs bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 transition flex items-center justify-center gap-2 cursor-pointer">
                        <span>🧪 Buka Console Simulator Gerbang Lengkap</span>
                        <span>➔</span>
                    </a>
                </div>

                <!-- KARTU 2: QUICK TEST-DRIVE PERAN (1-KLIK EKSPLORASI) -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">TEST DRIVE LANGSUNG</span>
                            <h3 class="font-black text-slate-900 dark:text-white text-sm">Coba Hak Akses Berbagai Peran:</h3>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300">
                            1-KLIK
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 text-xs">
                        <a href="{{ route('quick.login', 'super_admin') }}" class="p-2.5 rounded-xl border border-amber-300 dark:border-amber-700 bg-amber-50/40 dark:bg-amber-950/20 hover:bg-amber-100/60 transition flex items-center gap-2 group">
                            <span class="text-base">⚡</span>
                            <span class="font-bold text-slate-900 dark:text-white group-hover:text-amber-600 truncate">Super Admin</span>
                        </a>
                        <a href="{{ route('quick.login', 'ketua_rw') }}" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-indigo-500 hover:bg-indigo-50/40 transition flex items-center gap-2 group">
                            <span class="text-base">🏛️</span>
                            <span class="font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 truncate">Ketua RW</span>
                        </a>
                        <a href="{{ route('quick.login', 'ketua_rt') }}" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-emerald-500 hover:bg-emerald-50/40 transition flex items-center gap-2 group">
                            <span class="text-base">👔</span>
                            <span class="font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 truncate">Ketua RT</span>
                        </a>
                        <a href="{{ route('quick.login', 'bendahara') }}" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-emerald-500 hover:bg-emerald-50/40 transition flex items-center gap-2 group">
                            <span class="text-base">💰</span>
                            <span class="font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 truncate">Bendahara</span>
                        </a>
                        <a href="{{ route('quick.login', 'satpam') }}" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-emerald-500 hover:bg-emerald-50/40 transition flex items-center gap-2 group">
                            <span class="text-base">🛡️</span>
                            <span class="font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 truncate">Pos Satpam</span>
                        </a>
                        <a href="{{ route('quick.login', 'warga') }}" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-emerald-500 hover:bg-emerald-50/40 transition flex items-center gap-2 group">
                            <span class="text-base">🏡</span>
                            <span class="font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 truncate">Portal Warga</span>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </section>
                            <span class="text-base">💰</span>
                            <span class="font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 truncate">Bendahara</span>
                        </a>
                        <a href="{{ route('quick.login', 'satpam') }}" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-emerald-500 hover:bg-emerald-50/40 transition flex items-center gap-2 group">
                            <span class="text-base">🛡️</span>
                            <span class="font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 truncate">Pos Satpam</span>
                        </a>
                        <a href="{{ route('quick.login', 'warga') }}" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-emerald-500 hover:bg-emerald-50/40 transition flex items-center gap-2 group">
                            <span class="text-base">🏡</span>
                            <span class="font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 truncate">Portal Warga</span>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- ======================================================== -->
    <!-- 2. SOCIAL PROOF & ADAPTASI DUAL MODE (CLUSTER & KAMPUNG) -->
    <!-- Konsistensi Ruang Kosong (Whitespace) & Keterbacaan        -->
    <!-- ======================================================== -->
    <section class="border-y border-slate-200 dark:border-slate-800 py-12 sm:py-16 bg-slate-100/50 dark:bg-slate-900/40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
            <p class="text-xs sm:text-sm font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">
                Solusi Cerdas Teruji untuk Berbagai Karakter Lingkungan di Indonesia
            </p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 pt-2">
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-2">
                    <div class="text-3xl mb-1">🏢</div>
                    <div class="font-black text-sm sm:text-base text-slate-900 dark:text-white">Cluster Perumahan</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Single Gate System & Satpam Shift 24 Jam</div>
                </div>
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-2">
                    <div class="text-3xl mb-1">🏡</div>
                    <div class="font-black text-sm sm:text-base text-slate-900 dark:text-white">Komplek RT/RW</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Transparansi Kas Iuran & Layanan Surat Digital</div>
                </div>
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-2">
                    <div class="text-3xl mb-1">🌾</div>
                    <div class="font-black text-sm sm:text-base text-slate-900 dark:text-white">Desa / Kampung Guyub</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Jadwal Siskamling Ronda & Posyandu KMS Terpadu</div>
                </div>
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-2">
                    <div class="text-3xl mb-1">🏗️</div>
                    <div class="font-black text-sm sm:text-base text-slate-900 dark:text-white">Pengembang Properti</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">Nilai Tambah Jual Smart Residence Siap Huni</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- 3. SEBELUM VS SESUDAH (PERBANDINGAN NYATA CARA LAMA VS BARU)-->
    <!-- Membangun Kepercayaan (Trust) & Menghilangkan Ragu       -->
    <!-- ======================================================== -->
    <section id="komparasi" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center max-w-3xl mx-auto space-y-3">
            <span class="text-xs sm:text-sm font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">KOMPARASI EFISIENSI LINGKUNGAN</span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 dark:text-white tracking-tight">
                Mengapa Lingkungan Anda Harus Beralih ke E-WARGA?
            </h2>
            <p class="text-base text-slate-600 dark:text-slate-400">
                Bandingkan kerepotan tata kelola manual konvensional dengan kenyamanan sistem digital terpadu.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Kolom Cara Lama (Manual) -->
            <div class="p-8 sm:p-10 rounded-3xl bg-rose-50/70 dark:bg-rose-950/20 border-2 border-rose-200 dark:border-rose-900/50 space-y-6">
                <div class="flex items-center gap-3.5">
                    <span class="w-12 h-12 rounded-2xl bg-rose-200 dark:bg-rose-900/60 text-rose-800 dark:text-rose-200 flex items-center justify-center text-xl font-bold shrink-0">✕</span>
                    <div>
                        <h3 class="font-black text-lg sm:text-xl text-rose-950 dark:text-rose-200">Cara Lama (Manual & Konvensional)</h3>
                        <p class="text-xs sm:text-sm text-rose-700 dark:text-rose-300">Rentan konflik, lambat, dan memicu kecurigaan antar warga</p>
                    </div>
                </div>

                <ul class="space-y-4 text-sm text-slate-700 dark:text-slate-300">
                    <li class="flex items-start gap-3">
                        <span class="text-rose-500 font-bold text-base shrink-0">✕</span>
                        <span><strong>Buku Kas Kertas:</strong> Sering tercecer, bukti transfer hilang, dan warga ragu apakah uang kas iuran benar-benar dipakai untuk lingkungan.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-rose-500 font-bold text-base shrink-0">✕</span>
                        <span><strong>Portal Bambu & Manual:</strong> Satpam repot buka tutup tali. Tamu luar dan kurir tidak terdata riwayat keluar-masuknya.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-rose-500 font-bold text-base shrink-0">✕</span>
                        <span><strong>Urus Surat Mengganggu Waktu:</strong> Warga harus mengetuk rumah Ketua RT malam-malam hanya untuk meminta selembar surat pengantar KTP/KK.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-rose-500 font-bold text-base shrink-0">✕</span>
                        <span><strong>Catatan Posyandu Tercecer:</strong> Buku timbangan manual membuat indikasi stunting pada balita terlambat terdeteksi oleh Puskesmas.</span>
                    </li>
                </ul>
            </div>

            <!-- Kolom Cara Baru (E-WARGA) -->
            <div class="p-8 sm:p-10 rounded-3xl bg-emerald-50/80 dark:bg-emerald-950/30 border-2 border-emerald-300 dark:border-emerald-800/80 space-y-6">
                <div class="flex items-center gap-3.5">
                    <span class="w-12 h-12 rounded-2xl bg-emerald-200 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200 flex items-center justify-center text-xl font-bold shrink-0">✓</span>
                    <div>
                        <h3 class="font-black text-lg sm:text-xl text-emerald-950 dark:text-emerald-200">Dengan Ekosistem E-WARGA</h3>
                        <p class="text-xs sm:text-sm text-emerald-700 dark:text-emerald-300">Transparan, otomatis, terhubung IoT, dan bebas repot</p>
                    </div>
                </div>

                <ul class="space-y-4 text-sm text-slate-700 dark:text-slate-300">
                    <li class="flex items-start gap-3">
                        <span class="text-emerald-600 font-bold text-base shrink-0">✓</span>
                        <span><strong>Buku Kas Terbuka & Grafik Interaktif:</strong> Warga dapat mengecek saldo kas live lewat grafik Pie/Bar Chart. Bukti transfer diunggah langsung.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-emerald-600 font-bold text-base shrink-0">✓</span>
                        <span><strong>Smart Barrier Gate Otomatis:</strong> Palang terangkat 1-klik via HP warga, kartu RFID tap cepat, dan pass tamu QR Code via WhatsApp.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-emerald-600 font-bold text-base shrink-0">✓</span>
                        <span><strong>Surat Pengantar RT Online:</strong> Warga mengajukan dari rumah, Ketua RT menyetujui dalam 1-klik, menghasilkan dokumen resmi berstempel QR.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-emerald-600 font-bold text-base shrink-0">✓</span>
                        <span><strong>Posyandu Digital Terpadu:</strong> Monitoring 5 kategori demografi warga dengan alarm otomatis deteksi risiko stunting balita.</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- 4. ENAM PILAR FITUR UTAMA SISTEM                          -->
    <!-- Keterbacaan & Ukuran Teks Standar 16px                    -->
    <!-- ======================================================== -->
    <section id="fitur" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center max-w-3xl mx-auto space-y-3">
            <span class="text-xs sm:text-sm font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">MODUL UTAMA PLATFORM</span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 dark:text-white tracking-tight">
                6 Pilar Solusi Tata Kelola Digital Lingkungan
            </h2>
            <p class="text-base text-slate-600 dark:text-slate-400">
                Dirancang khusus dengan alur kerja yang intuitif, aman, dan mudah dioperasikan oleh seluruh kalangan warga.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- 1. Smart Gate -->
            <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs hover:border-emerald-500 transition-all group space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-3xl font-bold group-hover:scale-110 transition">
                    🚪
                </div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white">Palang Pintu Otomatis (Smart Gate)</h3>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Buka palang gerbang langsung dari tombol HP warga, tap kartu RFID, atau scan tiket QR kurir ekspedisi. Dilengkapi log riwayat keluar-masuk kendaraan real-time.
                </p>
                <div class="pt-2">
                    <a href="{{ route('simulator') }}" class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400 inline-flex items-center gap-1.5 group-hover:underline">
                        <span>Coba Simulator Gerbang</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- 2. Kas RT Terbuka -->
            <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs hover:border-emerald-500 transition-all group space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-950/70 text-blue-600 dark:text-blue-400 flex items-center justify-center text-3xl font-bold group-hover:scale-110 transition">
                    📊
                </div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white">Buku Kas RT & Analisis Grafik</h3>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Transparansi total keuangan lingkungan dengan visualisasi Pie Chart proporsi pengeluaran dan Bar Chart perbandingan bulanan. Bukti transfer iuran tersimpan rapi.
                </p>
                <div class="pt-2">
                    <a href="{{ route('dashboard') }}" class="text-sm font-extrabold text-blue-600 dark:text-blue-400 inline-flex items-center gap-1.5 group-hover:underline">
                        <span>Lihat Pembukuan Kas</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- 3. Posyandu Digital -->
            <div id="posyandu" class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs hover:border-pink-500 transition-all group space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-pink-50 dark:bg-pink-950/70 text-pink-600 dark:text-pink-400 flex items-center justify-center text-3xl font-bold group-hover:scale-110 transition">
                    🩺
                </div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white">Posyandu Digital & Cegah Stunting</h3>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Tracking komprehensif 5 kategori warga (balita, anak-anak, ibu hamil, kader PKK, dan lansia). Dilengkapi alarm deteksi dini risiko stunting balita dan riwayat tensi posbindu.
                </p>
                <div class="pt-2">
                    <a href="{{ route('posyandu.index') }}" class="text-sm font-extrabold text-pink-600 dark:text-pink-400 inline-flex items-center gap-1.5 group-hover:underline">
                        <span>Buku Posyandu KMS</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- 4. Surat Pengantar RT Online -->
            <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs hover:border-indigo-500 transition-all group space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-3xl font-bold group-hover:scale-110 transition">
                    📜
                </div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white">Surat Pengantar RT Online Ber-QR</h3>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Warga dapat mengajukan surat permohonan KTP, KK, Domisili, SKTM, dan izin usaha secara online. Ketua RT menyetujui dalam 1-klik dengan tanda tangan stempel QR verifikasi instan.
                </p>
                <div class="pt-2">
                    <a href="{{ route('letters.index') }}" class="text-sm font-extrabold text-indigo-600 dark:text-indigo-400 inline-flex items-center gap-1.5 group-hover:underline">
                        <span>Layanan Surat Digital</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- 5. Jadwal Keamanan Satpam & Ronda -->
            <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs hover:border-amber-500 transition-all group space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-950/70 text-amber-600 dark:text-amber-400 flex items-center justify-center text-3xl font-bold group-hover:scale-110 transition">
                    🔦
                </div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white">Jadwal Satpam & Ronda Warga</h3>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Dua mode fleksibel: Shift satpam profesional untuk cluster perumahan atau roster ronda siskamling malam untuk perkampungan, lengkap dengan fitur tukar shift antar warga.
                </p>
                <div class="pt-2">
                    <a href="{{ route('schedules.index') }}" class="text-sm font-extrabold text-amber-600 dark:text-amber-400 inline-flex items-center gap-1.5 group-hover:underline">
                        <span>Jadwal Petugas & Ronda</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- 6. Portal Mandiri Warga & Panic Button -->
            <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs hover:border-rose-500 transition-all group space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-950/70 text-rose-600 dark:text-rose-400 flex items-center justify-center text-3xl font-bold group-hover:scale-110 transition">
                    🚨
                </div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white">Portal Mandiri Warga & Tombol Panik</h3>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Akses mandiri warga untuk memantau status pembayaran iuran, membuat tiket tamu kurir WhatsApp, dan menekan Panic Button darurat yang langsung membunyikan sirene pos satpam.
                </p>
                <div class="pt-2">
                    <a href="{{ route('warga.index') }}" class="text-sm font-extrabold text-rose-600 dark:text-rose-400 inline-flex items-center gap-1.5 group-hover:underline">
                        <span>Akses Portal Warga</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- 5. KATALOG PERANGKAT KERAS & HARDWARE IOT                 -->
    <!-- Bukti Integrasi Hardware Fisik & R&D Masa Depan          -->
    <!-- ======================================================== -->
    <section id="hardware" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center max-w-3xl mx-auto space-y-3">
            <span class="text-xs sm:text-sm font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">KATALOG EKOSISTEM IOT</span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 dark:text-white tracking-tight">
                Perangkat Keras Cerdas: Siap Pasang & Inovasi R&D
            </h2>
            <p class="text-base text-slate-600 dark:text-slate-400">
                E-WARGA bukan hanya software biasa, namun terhubung langsung dengan mesin palang gerbang dan perangkat telemetri pintar.
            </p>
        </div>

        <!-- Tabel 1: Perangkat yang Siap Pasang -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">Perangkat Keras Siap Operasional & Survei</h3>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300">
                    STATUS: READY STOCK
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- 1. Smart Barrier Gate -->
                <div class="bg-white dark:bg-slate-900 p-7 rounded-3xl border-2 border-emerald-500/40 dark:border-emerald-500/30 shadow-lg space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl font-bold">
                        🚪
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">HARDWARE UTAMA</div>
                        <h4 class="text-base font-black text-slate-900 dark:text-white">Smart Barrier Gate Otomatis</h4>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Mesin palang gerbang kecepatan tinggi (1.5 - 3 detik) bertenaga brushless DC. Terhubung ke controller microchip E-WARGA via API aman, respons buka 1-klik dari HP atau kartu RFID.
                    </p>
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-400 font-semibold">Sensor Anti-Bentur:</span>
                        <span class="font-bold text-slate-800 dark:text-white">Loop Detector + Radar</span>
                    </div>
                </div>

                <!-- 2. Kiosk QR Scanner & RFID Post -->
                <div class="bg-white dark:bg-slate-900 p-7 rounded-3xl border-2 border-emerald-500/40 dark:border-emerald-500/30 shadow-lg space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-50 dark:bg-cyan-950 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-2xl font-bold">
                        📷
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-cyan-600 dark:text-cyan-400 uppercase tracking-wider">OUTDOOR POST</div>
                        <h4 class="text-base font-black text-slate-900 dark:text-white">Kiosk QR & RFID Scanner Tiang</h4>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Tiang pedestal outdoor standar industri bersertifikasi IP66 tahan panas dan hujan. Membaca tiket pass tamu QR Code via WhatsApp kurir dan kartu tap RFID penghuni secara instan.
                    </p>
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-400 font-semibold">Kecepatan Baca:</span>
                        <span class="font-bold text-slate-800 dark:text-white font-mono">&lt; 0.3 Detik</span>
                    </div>
                </div>

                <!-- 3. Virtual Hardware Simulator -->
                <div class="bg-white dark:bg-slate-900 p-7 rounded-3xl border-2 border-emerald-500/40 dark:border-emerald-500/30 shadow-lg space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-950 text-teal-600 dark:text-teal-400 flex items-center justify-center text-2xl font-bold">
                        🧪
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-teal-600 dark:text-teal-400 uppercase tracking-wider">VIRTUAL DIGITAL TWIN</div>
                        <h4 class="text-base font-black text-slate-900 dark:text-white">Simulator Palang Pintu Cloud</h4>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Console visual twin di layar browser untuk mensimulasikan respon buka-tutup palang, timer 5 detik, dan traffic log secara langsung tanpa perlu membeli perangkat fisik terlebih dahulu.
                    </p>
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                        <a href="{{ route('simulator') }}" class="font-bold text-emerald-600 hover:underline">Uji Simulator Gerbang ➔</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel 2: Pipeline R&D Masa Depan -->
        <div class="space-y-4 pt-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full bg-cyan-500 animate-ping"></span>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">Inovasi Perangkat Keras Mendatang (Pipeline & Coming Soon)</h3>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-black bg-cyan-100 dark:bg-cyan-950/80 text-cyan-800 dark:text-cyan-300">
                    TAHAP R&D & INTEGRASI
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- CS 1: CCTV ANPR -->
                <div 
                    @click="triggerComingSoon('Inovasi Hardware: CCTV ANPR (Auto Number Plate)', 'Fitur integrasi kamera AI pemindai plat nomor polisi kendaraan warga sedang dalam tahap pengujian integrasi firmware Edge-AI dan sinkronisasi basis data kendaraan (Coming Soon).')" 
                    class="p-6 rounded-3xl bg-slate-100/70 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-3 relative overflow-hidden group cursor-pointer hover:border-blue-500 hover:shadow-lg transition transform hover:-translate-y-1"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-2xl">📹</span>
                        <span class="px-2.5 py-1 rounded-md text-[9px] font-black uppercase bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300">Coming Soon ➔</span>
                    </div>
                    <h4 class="font-black text-sm text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">CCTV ANPR (Auto Number Plate Recognition)</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Kamera AI resolusi tinggi yang otomatis memindai plat nomor polisi kendaraan warga saat mendekati gerbang. Palang terbuka tanpa perlu tap kartu.
                    </p>
                </div>

                <!-- CS 2: Tiang Tombol Panik Publik -->
                <div 
                    @click="triggerComingSoon('Inovasi Hardware: Tiang Tombol Panik Darurat (Public Pole)', 'Perangkat tiang tombol panik fisik dengan sirine piezoelektrik & interkom 2 arah sedang dalam prototipe perakitan modul telemetri LoRa/GSM (Coming Soon).')" 
                    class="p-6 rounded-3xl bg-slate-100/70 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-3 relative overflow-hidden group cursor-pointer hover:border-rose-500 hover:shadow-lg transition transform hover:-translate-y-1"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-2xl">🚨</span>
                        <span class="px-2.5 py-1 rounded-md text-[9px] font-black uppercase bg-rose-100 dark:bg-rose-950 text-rose-800 dark:text-rose-300">Coming Soon ➔</span>
                    </div>
                    <h4 class="font-black text-sm text-slate-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400 transition">Tiang Tombol Panik Darurat (Public Pole)</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Tiang panik fisik yang dipasang di taman/gang lingkungan. Dilengkapi sirine piezoelektrik, strobo merah 360°, dan interkom suara langsung terhubung ke pos satpam.
                    </p>
                </div>

                <!-- CS 3: Sensor Banjir Drainase -->
                <div 
                    @click="triggerComingSoon('Inovasi Hardware: Sensor Banjir & Saluran Air IoT', 'Modul sensor ultrasonik pemantau debit drainase got dan early warning system banjir sedang dalam uji ketahanan cuaca ekstrim & kalibrasi ambang batas alarm (Coming Soon).')" 
                    class="p-6 rounded-3xl bg-slate-100/70 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-3 relative overflow-hidden group cursor-pointer hover:border-teal-500 hover:shadow-lg transition transform hover:-translate-y-1"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-2xl">🌊</span>
                        <span class="px-2.5 py-1 rounded-md text-[9px] font-black uppercase bg-teal-100 dark:bg-teal-950 text-teal-800 dark:text-teal-300">Coming Soon ➔</span>
                    </div>
                    <h4 class="font-black text-sm text-slate-900 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition">Sensor Banjir & Saluran Air IoT</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Sensor ultrasonik pengukur ketinggian debit saluran drainase got lingkungan warga. Otomatis membunyikan alarm dan mengirim broadcast notifikasi saat siaga 1.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- 6. TESTIMONI PENGURUS RT, SATPAM, & POSYANDU              -->
    <!-- Social Proof Autentik yang Membangun Rasa Aman            -->
    <!-- ======================================================== -->
    <section id="testimoni" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center max-w-3xl mx-auto space-y-3">
            <span class="text-xs sm:text-sm font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">TESTIMONI NYATA</span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 dark:text-white tracking-tight">
                Pengalaman Nyata Pengurus & Warga
            </h2>
            <p class="text-base text-slate-600 dark:text-slate-400">
                Simak bagaimana E-WARGA telah mentransformasi tata kelola lingkungan hunian di berbagai kota.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                <div class="text-amber-400 text-lg">★★★★★</div>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed italic">
                    "Dulu warga sering nunggak iuran bulanan karena lupa atau curiga ke mana uang kas dialokasikan. Sejak E-WARGA dipakai, transparansi kas dengan grafik Pie Chart membuat warga percaya. Tingkat bayar iuran melonjak dari 45% ke 98%!"
                </p>
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div class="font-black text-base text-slate-900 dark:text-white">Bpk. H. Sutrisno</div>
                    <div class="text-xs text-slate-400">Ketua RT 04 / RW 02 - Bekasi</div>
                </div>
            </div>

            <div class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                <div class="text-amber-400 text-lg">★★★★★</div>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed italic">
                    "Palang otomatis dan tiket QR tamu kurir sangat meringankan tugas pos jaga. Kendaraan luar yang mencurigakan tidak bisa sembarangan masuk cluster. Satpam kami jauh lebih tertib dan terbantu."
                </p>
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div class="font-black text-base text-slate-900 dark:text-white">Pak Slamet</div>
                    <div class="text-xs text-slate-400">Komandan Regu Keamanan Cluster</div>
                </div>
            </div>

            <div class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-4">
                <div class="text-amber-400 text-lg">★★★★★</div>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed italic">
                    "Fitur Posyandu Digital luar biasa membantu kader. Begitu berat badan dan tinggi balita diinput, ada indikator otomatis jika ada risiko stunting, sehingga intervensi gizi tambahan dari Puskesmas bisa segera diberikan."
                </p>
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div class="font-black text-base text-slate-900 dark:text-white">Bidan Nina Kurnia, S.Tr.Keb</div>
                    <div class="text-xs text-slate-400">Pembina Posyandu Melati</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- 7. SEKSI KEMITRAAN, KONSULTASI & FORM PENDAFTARAN LINGKUNGAN-->
    <!-- Call to Action (CTA) Utama Tanpa Distraksi                -->
    <!-- ======================================================== -->
    <section id="kerjasama" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        
        <!-- Kartu Kontak Resmi & Email Kerjasama -->
        <div class="bg-gradient-to-r from-slate-900 via-[#1B365D] to-slate-950 rounded-3xl p-8 sm:p-12 text-white shadow-2xl border border-slate-800 space-y-8">
            <div class="text-center max-w-2xl mx-auto space-y-3">
                <span class="px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-400/20 text-emerald-300 border border-emerald-400/30">
                    PUSAT KEMITRAAN & PENGADAAN PERANGKAT
                </span>
                <h3 class="text-3xl sm:text-4xl font-black">Konsultasi Teknis & Kerjasama Resmi</h3>
                <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                    Kami melayani pengadaan sistem software, pemasangan palang pintu otomatis, survei lokasi, serta kemitraan strategis dengan pengembang perumahan dan asosiasi RT/RW se-Indonesia.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                <div class="p-5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15 space-y-1">
                    <div class="text-[10px] uppercase font-bold text-emerald-300">Email Kerjasama & Kemitraan:</div>
                    <a href="mailto:partnership@ewarga.id" class="text-sm font-bold text-white hover:text-emerald-400 transition block truncate">
                        partnership@ewarga.id
                    </a>
                    <div class="text-[10px] text-slate-400">Developer, Paguyuban, Pemda</div>
                </div>

                <div class="p-5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15 space-y-1">
                    <div class="text-[10px] uppercase font-bold text-cyan-300">Email Penjualan & Hardware:</div>
                    <a href="mailto:sales@ewarga.id" class="text-sm font-bold text-white hover:text-cyan-400 transition block truncate">
                        sales@ewarga.id
                    </a>
                    <div class="text-[10px] text-slate-400">Pengadaan Barrier Gate & Kiosk</div>
                </div>

                <div class="p-5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15 space-y-1">
                    <div class="text-[10px] uppercase font-bold text-amber-300">Hotline WhatsApp Konsultan:</div>
                    <div class="text-sm font-mono font-bold text-white block">
                        +62 812-3456-7890
                    </div>
                    <div class="text-[10px] text-slate-400">Respon Teknis Cepat (24 Jam)</div>
                </div>
            </div>
        </div>

        @guest
        <!-- Formulir Permohonan Kemitraan & Survei Lokasi -->
        <div id="daftar-komplek" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-8 sm:p-12 shadow-2xl space-y-8">
            <div class="text-center max-w-xl mx-auto space-y-3">
                <span class="text-xs sm:text-sm font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">KONSULTASI GRATIS & SURVEI PERANGKAT</span>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">Daftarkan Lingkungan Anda Hari Ini</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Silakan isi formulir data komplek/perumahan Anda. Tim konsultan teknis lapangan kami akan menghubungi nomor WhatsApp Anda untuk jadwal survei dan estimasi biaya pemasangan.
                </p>
            </div>

            <form action="{{ route('tenant.register') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Nama Komplek / Perumahan / Desa</label>
                        <input type="text" name="complex_name" placeholder="Misal: Cluster Graha Asri Indah" required class="w-full px-4 py-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-base outline-hidden transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Nomor RT / RW</label>
                        <input type="text" name="rt_rw" placeholder="Misal: RT 04 / RW 02" required class="w-full px-4 py-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-base outline-hidden transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Kota / Kabupaten</label>
                        <input type="text" name="city" placeholder="Misal: Bekasi / Bandung / Surabaya" required class="w-full px-4 py-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-base outline-hidden transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Estimasi Jumlah Rumah / KK</label>
                        <input type="number" name="estimated_houses" placeholder="Misal: 150" class="w-full px-4 py-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-base outline-hidden transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Nama Pengurus / Penanggung Jawab</label>
                        <input type="text" name="contact_name" placeholder="Nama Anda (Ketua RT / RW / Pengembang)" required class="w-full px-4 py-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-base outline-hidden transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Nomor WhatsApp Aktif</label>
                        <input type="tel" name="contact_phone" placeholder="08xxxxxxxxxx" required class="w-full px-4 py-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-base outline-hidden transition">
                    </div>
                </div>

                <div class="p-5 bg-emerald-50 dark:bg-emerald-950/40 rounded-2xl border border-emerald-100 dark:border-emerald-900 flex items-center gap-3.5">
                    <input type="checkbox" name="need_barrier_gate" id="need_barrier_gate" value="1" checked class="w-5 h-5 text-emerald-600 rounded-md border-slate-300 dark:border-slate-700 focus:ring-emerald-500">
                    <label for="need_barrier_gate" class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                        Lingkungan kami berminat memasang <strong>Palang Pintu Otomatis (Smart Barrier Gate) & Kunjungan Teknisi Lapangan</strong>
                    </label>
                </div>

                <div class="text-center pt-2">
                    <button type="submit" class="w-full sm:w-auto px-12 py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-2xl shadow-xl shadow-emerald-600/30 text-base sm:text-lg transition-all transform hover:-translate-y-0.5 cursor-pointer touch-press">
                        Kirim Permohonan & Jadwalkan Survei
                    </button>
                </div>
            </form>
        </div>
        @else
        <!-- KETIKA SUDAH LOGIN: TAMPILKAN STATUS SESI & TOMBOL CEPAT KE DASHBOARD -->
        <div class="bg-gradient-to-r from-emerald-600 via-teal-700 to-[#1B365D] rounded-3xl p-8 sm:p-12 text-white shadow-2xl flex flex-col md:flex-row items-center justify-between gap-6 border border-emerald-400/20">
            <div class="space-y-3 text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/20 text-xs font-black uppercase tracking-wider backdrop-blur-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                    <span>Sesi Aktif: {{ Auth::user()->role_label }}</span>
                </div>
                <h3 class="text-2xl sm:text-3xl font-black">
                    Halo, {{ Auth::user()->name }}!
                </h3>
                <p class="text-sm text-emerald-100 max-w-xl leading-relaxed">
                    Lingkungan Anda telah aktif di sistem E-WARGA. Anda sedang login dengan hak akses <strong>{{ Auth::user()->role_label }}</strong>. Silakan kembali ke panel dashboard untuk mengelola kas, palang pintu, dan pelayanan warga.
                </p>
            </div>
            <div class="shrink-0">
                @if(Auth::user()->role === 'super_admin')
                    <a href="{{ route('superadmin.index') }}" class="px-8 py-4 bg-amber-400 hover:bg-amber-300 text-slate-950 font-black rounded-2xl shadow-xl transition transform hover:scale-105 active:scale-95 text-base flex items-center gap-2 cursor-pointer">
                        <span>Buka Dashboard Utama</span>
                        <span>➔</span>
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="px-8 py-4 bg-white text-emerald-950 hover:bg-emerald-50 font-black rounded-2xl shadow-xl transition transform hover:scale-105 active:scale-95 text-base flex items-center gap-2 cursor-pointer">
                        <span>Buka Dashboard Utama</span>
                        <span>➔</span>
                    </a>
                @endif
            </div>
        </div>
        @endguest
    </section>

</div>
@endsection
