<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'E-WARGA - Smart Community & IoT Living Ecosystem' }}</title>
    
    <!-- Favicon Resmi SVG -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Compiled Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Dark / Light Mode Early Bootstrapper (Prevent FOUC) -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body 
    x-data="{
        sidebarOpen: localStorage.getItem('ewarga_drawer_open') !== 'false',
        toggleDrawer() {
            this.sidebarOpen = !this.sidebarOpen;
            localStorage.setItem('ewarga_drawer_open', this.sidebarOpen);
        },
        closeDrawer() {
            this.sidebarOpen = false;
            localStorage.setItem('ewarga_drawer_open', 'false');
        },
        openDrawer() {
            this.sidebarOpen = true;
            localStorage.setItem('ewarga_drawer_open', 'true');
        }
    }"
    class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased min-h-screen transition-colors duration-200"
>

@php
    $isLanding = request()->routeIs('home');
    $activeTenant = \App\Models\Tenant::first();
    $pendingLettersCount = \App\Models\LetterRequest::where('status', 'pending')->count();
@endphp

    <!-- ======================================================== -->
    <!-- BACKDROP GELAP (UNTUK MOBILE / OVERLAY DRAWER)            -->
    <!-- ======================================================== -->
    <div 
        x-show="sidebarOpen" 
        @click="closeDrawer()" 
        x-cloak 
        class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-xs lg:hidden transition-opacity"
    ></div>

    <!-- ======================================================== -->
    <!-- TOMBOL TARIK LACI MENGAPUNG (SAAT LACI DITUTUP)          -->
    <!-- ======================================================== -->
    <button 
        x-show="!sidebarOpen" 
        @click="openDrawer()" 
        x-cloak
        type="button"
        class="fixed top-4 left-4 z-40 flex items-center gap-2.5 px-4 py-2.5 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-slate-200/90 dark:border-slate-700/90 text-slate-800 dark:text-slate-100 shadow-xl shadow-slate-900/10 hover:border-emerald-500 hover:text-emerald-600 dark:hover:text-emerald-400 hover:scale-105 active:scale-95 transition-all text-xs font-black cursor-pointer group"
        title="Buka Laci Menu Navigasi E-WARGA"
    >
        <span class="p-1 rounded-lg bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 group-hover:bg-emerald-600 group-hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </span>
        <span class="tracking-tight">Laci Menu</span>
        <span class="text-slate-400 dark:text-slate-500 text-[10px] group-hover:translate-x-0.5 transition-transform">▶</span>
    </button>

    <!-- ======================================================== -->
    <!-- LACI NAVIGASI SAMPING KIRI (DRAWER SIDEBAR NGELACI)      -->
    <!-- ======================================================== -->
    <aside 
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed top-0 left-0 z-50 h-screen w-80 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-2xl transition-transform duration-300 ease-in-out shrink-0 overflow-y-auto"
    >
        <div class="p-5 space-y-5">
            <!-- Header Laci: Brand & Tombol Dorong Tutup Laci -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Logo E-WARGA" class="h-10 w-auto object-contain">
                    <div>
                        <span class="font-extrabold text-base tracking-tight text-slate-900 dark:text-white block leading-tight">E-WARGA</span>
                        <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block">Smart Community OS</span>
                    </div>
                </a>
                
                <!-- Tombol Dorong Masuk Laci (Collapse Drawer) -->
                <button 
                    @click="closeDrawer()" 
                    type="button"
                    class="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition flex items-center justify-center gap-1 group"
                    title="Dorong / Tutup Laci Menu"
                >
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                </button>
            </div>

            <!-- Kartu Status Lingkungan Aktif (Cluster vs Kampung) -->
            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Mode Lingkungan:</span>
                    <form action="{{ route('environment.toggle') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-[10px] font-bold px-2 py-0.5 rounded-md transition {{ ($activeTenant?->environment_type ?? 'cluster') === 'cluster' ? 'bg-blue-100 dark:bg-blue-950/80 text-blue-800 dark:text-blue-300 hover:bg-blue-200' : 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 hover:bg-emerald-200' }}" title="Klik untuk beralih mode Cluster / Kampung">
                            {{ ($activeTenant?->environment_type ?? 'cluster') === 'cluster' ? '🏢 Cluster Perumahan' : '🌾 Perkampungan' }}
                        </button>
                    </form>
                </div>
                <div class="font-extrabold text-xs text-slate-800 dark:text-slate-100 truncate">
                    {{ $activeTenant?->name ?? 'Graha Asri Residence' }}
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                    {{ $activeTenant?->rt_rw ?? 'RT 04 / RW 02' }} &bull; {{ $activeTenant?->city ?? 'Bekasi' }}
                </div>
            </div>

            <!-- Menu Navigasi di Dalam Laci Samping -->
            <nav class="space-y-6 text-xs font-semibold">
                
                @if($isLanding)
                    <!-- KELOMPOK KHUSUS LANDING PAGE (SEKSI HALAMAN PROMOSI) -->
                    <div>
                        <div class="px-3 pb-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 flex items-center justify-between">
                            <span>Seksi Landing Page</span>
                            <span class="text-[9px] text-emerald-600 dark:text-emerald-400 font-bold">KLIEN</span>
                        </div>
                        <div class="space-y-1">
                            <a href="#hero" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-slate-800 dark:hover:text-emerald-400 transition">
                                <span class="text-base">🏠</span>
                                <span>Beranda & Hero</span>
                            </a>
                            <a href="#fitur" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-slate-800 dark:hover:text-emerald-400 transition">
                                <span class="text-base">⭐</span>
                                <span>Fitur Unggulan</span>
                            </a>
                            <a href="#keamanan" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-slate-800 dark:hover:text-emerald-400 transition">
                                <span class="text-base">🚪</span>
                                <span>IoT Smart Barrier Gate</span>
                            </a>
                            <a href="#posyandu" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-slate-800 dark:hover:text-emerald-400 transition">
                                <span class="text-base">🩺</span>
                                <span>Posyandu & Stunting</span>
                            </a>
                            <a href="#komparasi" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-slate-800 dark:hover:text-emerald-400 transition">
                                <span class="text-base">⚖️</span>
                                <span>Sebelum vs Sesudah</span>
                            </a>
                            <a href="#testimoni" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-slate-800 dark:hover:text-emerald-400 transition">
                                <span class="text-base">💬</span>
                                <span>Testimoni Pengurus & Warga</span>
                            </a>
                            <a href="#daftar-komplek" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-slate-800 dark:hover:text-emerald-400 transition">
                                <span class="text-base">📝</span>
                                <span>Konsultasi & Survei Pasang</span>
                            </a>
                        </div>
                    </div>

                    <!-- PINTASAN LANGSUNG KE APLIKASI OPERASIONAL -->
                    <div>
                        <div class="px-3 pb-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
                            Akses Cepat Modul Sistem
                        </div>
                        <div class="space-y-1">
                            <a href="{{ route('dashboard') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold shadow-md shadow-emerald-600/20 hover:from-emerald-500 hover:to-teal-500 transition">
                                <div class="flex items-center gap-3">
                                    <span class="text-base">📊</span>
                                    <span>Buka Dashboard RT</span>
                                </div>
                                <span>➔</span>
                            </a>
                            <a href="{{ route('simulator') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                <div class="flex items-center gap-3">
                                    <span class="text-base">🧪</span>
                                    <span>Simulator Palang Pintu</span>
                                </div>
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            </a>
                            <a href="{{ route('schedules.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                <span class="text-base">🔦</span>
                                <span>Jadwal Satpam & Ronda</span>
                            </a>
                            <a href="{{ route('posyandu.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                <span class="text-base">🩺</span>
                                <span>Posyandu Digital KMS</span>
                            </a>
                        </div>
                    </div>

                @else
                    <!-- KELOMPOK UNTUK HALAMAN INTERNAL OPERASIONAL -->
                    <!-- Kelompok 1: Manajemen Utama -->
                    <div>
                        <div class="px-3 pb-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
                            Manajemen Utama
                        </div>
                        <div class="space-y-1">
                            <a href="{{ route('dashboard') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <div class="flex items-center gap-3">
                                    <span class="text-base">📊</span>
                                    <span>Dashboard RT & Kas</span>
                                </div>
                                @if(request()->routeIs('dashboard'))
                                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                @endif
                            </a>

                            <a href="{{ route('warga.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('warga.index') ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <div class="flex items-center gap-3">
                                    <span class="text-base">📱</span>
                                    <span>Akses Warga (Mobile View)</span>
                                </div>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 font-bold">HP</span>
                            </a>
                        </div>
                    </div>

                    <!-- Kelompok 2: Keamanan & Hardware IoT -->
                    <div>
                        <div class="px-3 pb-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
                            Keamanan & Hardware
                        </div>
                        <div class="space-y-1">
                            <a href="{{ route('satpam.console') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('satpam.console') ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <div class="flex items-center gap-3">
                                    <span class="text-base">🛡️</span>
                                    <span>Pos Pantau Satpam</span>
                                </div>
                                <span class="text-[10px] font-bold font-mono text-cyan-600 dark:text-cyan-400">QR</span>
                            </a>

                            <a href="{{ route('simulator') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('simulator') ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <div class="flex items-center gap-3">
                                    <span class="text-base">🧪</span>
                                    <span>Simulator Smart Gate</span>
                                </div>
                                <span class="flex h-2 w-2 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                            </a>

                            <a href="{{ route('schedules.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('schedules.*') ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <div class="flex items-center gap-3">
                                    <span class="text-base">🔦</span>
                                    <span>Jadwal & Ronda Bulan Ini</span>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Kelompok 3: Pelayanan Warga Digital -->
                    <div>
                        <div class="px-3 pb-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
                            Pelayanan Warga
                        </div>
                        <div class="space-y-1">
                            <a href="{{ route('posyandu.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('posyandu.*') ? 'bg-pink-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <div class="flex items-center gap-3">
                                    <span class="text-base">🩺</span>
                                    <span>Posyandu Digital RT/RW</span>
                                </div>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-pink-100 text-pink-700 dark:bg-pink-950/60 dark:text-pink-300 font-bold">KMS</span>
                            </a>

                            <a href="{{ route('letters.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('letters.*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <div class="flex items-center gap-3">
                                    <span class="text-base">📜</span>
                                    <span>Surat Pengantar RT</span>
                                </div>
                                @if($pendingLettersCount > 0)
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-500 text-white font-black animate-pulse">
                                        {{ $pendingLettersCount }}
                                    </span>
                                @endif
                            </a>
                        </div>
                    </div>

                    <!-- Kelompok 4: Portal & Informasi -->
                    <div>
                        <div class="px-3 pb-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
                            Portal Informasi
                        </div>
                        <div class="space-y-1">
                            <a href="{{ route('home') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                <span class="text-base">🌐</span>
                                <span>Lihat Landing Page Klien</span>
                            </a>
                        </div>
                    </div>
                @endif

            </nav>
        </div>

        <!-- Bagian Bawah Laci: Mode Gelap, Switcher Role, & User Profile -->
        <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/70 space-y-3">
            <!-- Dark / Light Mode Toggle & Role Quick Switcher -->
            <div class="flex items-center justify-between gap-2">
                <!-- Dark / Light Mode Toggle -->
                <div x-data="{
                    isDark: document.documentElement.classList.contains('dark'),
                    toggle() {
                        this.isDark = !this.isDark;
                        if (this.isDark) {
                            document.documentElement.classList.add('dark');
                            localStorage.setItem('theme', 'dark');
                            window.dispatchEvent(new CustomEvent('theme-changed', { detail: 'dark' }));
                        } else {
                            document.documentElement.classList.remove('dark');
                            localStorage.setItem('theme', 'light');
                            window.dispatchEvent(new CustomEvent('theme-changed', { detail: 'light' }));
                        }
                    }
                }" class="flex-1">
                    <button 
                        @click="toggle()" 
                        type="button" 
                        class="w-full py-2 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center justify-center gap-2 shadow-xs cursor-pointer" 
                        :title="isDark ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap'"
                        aria-label="Toggle Dark / Light Theme"
                    >
                        <svg x-show="isDark" x-cloak class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <svg x-show="!isDark" x-cloak class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <span x-text="isDark ? 'Mode Terang' : 'Mode Gelap'"></span>
                    </button>
                </div>

                <!-- Role Switcher Trigger -->
                <div x-data="{ openRole: false }" class="relative">
                    <button @click="openRole = !openRole" type="button" class="py-2 px-3 bg-[#1B365D] hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1.5 cursor-pointer" title="Ganti Peran Pengujian">
                        <span>Role</span>
                        <span class="text-[10px]">▼</span>
                    </button>
                    <div x-show="openRole" @click.away="openRole = false" x-cloak class="absolute bottom-full left-0 mb-2 w-56 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 py-2 z-50 text-xs">
                        <div class="px-3 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Switch Peran Demo:</div>
                        <a href="{{ route('quick.login', 'ketua_rt') }}" class="block px-3 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold">👔 Ketua RT (Approver)</a>
                        <a href="{{ route('quick.login', 'bendahara') }}" class="block px-3 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold">💰 Bendahara (Kas)</a>
                        <a href="{{ route('quick.login', 'satpam') }}" class="block px-3 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold">🛡️ Satpam (Pos Jaga)</a>
                        <a href="{{ route('quick.login', 'warga') }}" class="block px-3 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold">🏡 Warga (Buka Pintu)</a>
                        <a href="{{ route('quick.login', 'super_admin') }}" class="block px-3 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold">⚡ Super Admin</a>
                    </div>
                </div>
            </div>

            <!-- Info Profil Akun yang Sedang Aktif -->
            @auth
                <div class="flex items-center justify-between pt-2 border-t border-slate-200/80 dark:border-slate-800/80">
                    <div class="flex items-center gap-2.5 truncate">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 flex items-center justify-center font-black text-xs shrink-0">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="truncate">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ Auth::user()->name }}</div>
                            <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">{{ Auth::user()->role_label }}</div>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline shrink-0">
                        @csrf
                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 transition cursor-pointer" title="Keluar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </aside>

    <!-- ======================================================== -->
    <!-- KONTEN UTAMA (BERGESER ERGONOMIS MENYESUAIKAN LACI)       -->
    <!-- ======================================================== -->
    <div 
        :class="sidebarOpen ? 'lg:pl-80' : 'pl-0'" 
        class="flex-1 flex flex-col min-w-0 min-h-screen transition-all duration-300 ease-in-out"
    >
        
        <!-- PADA HALAMAN INTERNAL: TOPBAR TOOLBAR TIPIS (TANPA NAVBAR BESAR DI ATAS) -->
        @if(!$isLanding)
            <header class="h-16 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 sticky top-0 z-30 px-6 flex items-center justify-between gap-4 transition-colors duration-200">
                <!-- Kiri: Tombol Laci & Breadcrumb -->
                <div class="flex items-center gap-3">
                    <button 
                        @click="toggleDrawer()" 
                        type="button"
                        class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition cursor-pointer flex items-center gap-1.5 text-xs font-bold"
                        :title="sidebarOpen ? 'Sembunyikan Laci' : 'Buka Laci'"
                    >
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <span class="hidden sm:inline" x-text="sidebarOpen ? 'Tutup Laci' : 'Buka Laci'"></span>
                    </button>
                    
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                        <span class="text-slate-900 dark:text-white font-bold">E-WARGA</span>
                        <span>/</span>
                        <span class="text-emerald-600 dark:text-emerald-400 capitalize">
                            {{ str_replace('-', ' ', request()->segment(1) ?: 'Dashboard') }}
                        </span>
                    </div>
                </div>

                <!-- Kanan: Tanggal & Badge Lingkungan -->
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 text-xs font-semibold">
                        <span>🗓️</span>
                        <span>{{ now()->translatedFormat('l, d M Y') }}</span>
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold {{ ($activeTenant?->environment_type ?? 'cluster') === 'cluster' ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' }}">
                        <span class="w-2 h-2 rounded-full {{ ($activeTenant?->environment_type ?? 'cluster') === 'cluster' ? 'bg-blue-500' : 'bg-emerald-500' }} animate-pulse"></span>
                        <span class="hidden md:inline">{{ ($activeTenant?->environment_type ?? 'cluster') === 'cluster' ? 'Cluster Perumahan' : 'Perkampungan (RT)' }}</span>
                    </div>
                </div>
            </header>
        @endif

        <!-- Area Notifikasi Flash Message -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 {{ $isLanding ? 'pt-6' : 'mt-4' }} w-full">
            @if(session('success'))
                <div class="bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 px-4 py-3 rounded-xl flex items-center gap-3 text-sm font-medium shadow-xs mb-4">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 dark:bg-rose-950/60 border border-red-200 dark:border-rose-800 text-red-800 dark:text-rose-300 px-4 py-3 rounded-xl flex items-center gap-3 text-sm font-medium shadow-xs mb-4">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('panic_sent'))
                <div class="bg-red-600 text-white px-5 py-4 rounded-xl flex items-center gap-4 text-base font-bold shadow-lg animate-pulse mb-4">
                    <span class="text-2xl">🚨</span>
                    <div>
                        <div>PERINGATAN DARURAT TERKIRIM KE POS SATPAM</div>
                        <div class="text-xs font-normal text-red-100">{{ session('panic_sent') }}</div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Area Konten Halaman Aktif -->
        <main class="flex-1 {{ $isLanding ? '' : 'p-4 sm:p-6 lg:p-8' }}">
            @yield('content')
        </main>

        <!-- Footer Halaman -->
        @if($isLanding)
            <!-- Footer Khusus Landing Page -->
            <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-12 text-xs text-slate-500 dark:text-slate-400">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <img src="{{ asset('images/logo.jpg') }}" alt="Logo E-WARGA" class="h-8 w-auto object-contain">
                                <span class="font-bold text-base text-slate-800 dark:text-white">E-WARGA</span>
                            </div>
                            <p class="text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                Ekosistem Smart Community & Managed IoT Living terpadu untuk RT, RW, Paguyuban, dan Pengembang Cluster di Indonesia.
                            </p>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-[11px] mb-3">Fitur Solusi</h4>
                            <ul class="space-y-2">
                                <li><a href="{{ route('dashboard') }}" class="hover:text-emerald-600 transition">Transparansi Kas RT</a></li>
                                <li><a href="{{ route('simulator') }}" class="hover:text-emerald-600 transition">Palang Pintu Otomatis</a></li>
                                <li><a href="{{ route('schedules.index') }}" class="hover:text-emerald-600 transition">Jadwal Keamanan & Ronda</a></li>
                                <li><a href="{{ route('posyandu.index') }}" class="hover:text-emerald-600 transition">Posyandu Digital & Stunting</a></li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-[11px] mb-3">Pelayanan Warga</h4>
                            <ul class="space-y-2">
                                <li><a href="{{ route('letters.index') }}" class="hover:text-emerald-600 transition">Surat Pengantar RT Online</a></li>
                                <li><a href="{{ route('warga.index') }}" class="hover:text-emerald-600 transition">Aplikasi HP Buka Gerbang</a></li>
                                <li><a href="{{ route('satpam.console') }}" class="hover:text-emerald-600 transition">Console Pos Satpam</a></li>
                                <li><a href="#daftar-komplek" class="hover:text-emerald-600 transition">Pemasangan Komplek Baru</a></li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-[11px] mb-3">Pusat Bantuan</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Hubungi konsultan E-WARGA untuk survei perangkat keras & palang gerbang:</p>
                            <div class="font-mono text-emerald-600 dark:text-emerald-400 font-bold">WhatsApp: 0812-3456-7890</div>
                            <div class="text-[11px] text-slate-400 mt-1">Jakarta • Bekasi • Bandung • Surabaya</div>
                        </div>
                    </div>
                    <div class="pt-8 border-t border-slate-100 dark:border-slate-800 flex flex-col md:flex-row justify-between items-center gap-4 text-slate-400">
                        <div>&copy; {{ date('Y') }} E-WARGA Indonesia. All rights reserved. Platform Resmi Manajemen Lingkungan.</div>
                        <div class="flex items-center gap-4 text-[11px]">
                            <span>Ketentuan Layanan</span>
                            <span>•</span>
                            <span>Kebijakan Privasi</span>
                            <span>•</span>
                            <span>Keamanan Data ISO/IEC</span>
                        </div>
                    </div>
                </div>
            </footer>
        @else
            <!-- Footer Halaman Internal Aplikasi -->
            <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-6 px-6 text-center text-xs text-slate-400 transition-colors duration-200">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-2">
                    <div>
                        <span class="font-bold text-slate-700 dark:text-slate-300">E-WARGA Desktop Console</span> &bull; Lingkungan Pintar Masa Depan
                    </div>
                    <div>
                        &copy; {{ date('Y') }} E-WARGA Indonesia. All rights reserved.
                    </div>
                </div>
            </footer>
        @endif

    </div>

</body>
</html>
