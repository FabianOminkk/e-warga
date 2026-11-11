<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#10B981">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>{{ $title ?? 'E-WARGA - Smart Community & IoT Living Ecosystem' }}</title>
    
    <!-- Favicon Resmi SVG -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Compiled Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Dark / Light Mode Early Bootstrapper (Mencegah FOUC) -->
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
        
        /* Sleek custom scrollbar for desktop drawer */
        .custom-drawer-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .custom-drawer-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-drawer-scroll::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.4);
            border-radius: 9999px;
        }
        .dark .custom-drawer-scroll::-webkit-scrollbar-thumb {
            background-color: rgba(75, 85, 99, 0.5);
        }
    </style>
</head>
<body 
    x-data="{
        sidebarOpen: window.innerWidth >= 1024 ? (localStorage.getItem('ewarga_drawer_open') !== 'false') : false,
        landingMobileMenu: false,
        openComingSoon: false,
        comingSoonTitle: '',
        comingSoonDesc: '',
        showUserAccountModal: false,
        toggleDrawer() {
            this.sidebarOpen = !this.sidebarOpen;
            if (window.innerWidth >= 1024) {
                localStorage.setItem('ewarga_drawer_open', this.sidebarOpen);
            }
        },
        closeDrawer() {
            this.sidebarOpen = false;
            if (window.innerWidth >= 1024) {
                localStorage.setItem('ewarga_drawer_open', 'false');
            }
        },
        openDrawer() {
            this.sidebarOpen = true;
            if (window.innerWidth >= 1024) {
                localStorage.setItem('ewarga_drawer_open', 'true');
            }
        },
        triggerComingSoon(title, desc) {
            this.comingSoonTitle = title || 'Fitur Sedang Dalam Pengembangan';
            this.comingSoonDesc = desc || 'Maaf, fitur ini belum tersedia saat ini dan sedang dalam tahap pengembangan (Coming Soon). Tim teknis kami sedang mengintegrasikannya agar segera dapat Anda gunakan.';
            this.openComingSoon = true;
        }
    }"
    class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased min-h-screen transition-colors duration-200"
>

@php
    $isLanding = request()->routeIs('home');
    $isLogin = request()->routeIs('login');

    // Jika masuk ke area internal aplikasi ("di dalem"), pastikan otomatis login sebagai Super Admin jika belum ada sesi
    if (!$isLanding && !$isLogin && !\Illuminate\Support\Facades\Auth::check()) {
        $defaultAdmin = \App\Models\User::where('role', 'super_admin')->first();
        if ($defaultAdmin) {
            \Illuminate\Support\Facades\Auth::login($defaultAdmin);
        }
    }

    $activeTenant = \App\Models\Tenant::getActiveTenant();
    $pendingLettersCount = \App\Models\LetterRequest::where('status', 'pending')->count();
    $currentUser = \Illuminate\Support\Facades\Auth::user();
@endphp

@if($isLanding)
    <!-- ================================================================= -->
    <!-- KONDISI 1: LANDING PAGE STANDALONE (MURNI PROMOSI / BRANDING)    -->
    <!-- SAMA SEKALI TIDAK ADA SIDEBAR / LACI MENU DI LANDING PAGE         -->
    <!-- ================================================================= -->

    <!-- TOPBAR NAVBAR KHUSUS LANDING PAGE (DESKTOP-FIRST ENTERPRISE) -->
    <header class="sticky top-0 z-50 bg-white/90 dark:bg-slate-950/90 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800/80 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-6">
            
            <!-- Brand Logo SVG (Match Dark & Light 100%) -->
            <x-logo size="md" href="{{ route('home') }}" />

            <!-- Desktop Anchor Nav Links -->
            <nav class="hidden lg:flex items-center gap-7 text-xs font-bold text-slate-600 dark:text-slate-300">
                <a href="#hero" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Beranda</a>
                <a href="#fitur" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Fitur Solusi</a>
                <a href="#hardware" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition flex items-center gap-1.5">
                    <span>Hardware IoT</span>
                    <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 font-extrabold uppercase">Siap + R&D</span>
                </a>
                <a href="#posyandu" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Posyandu Digital</a>
                <a href="#komparasi" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Mengapa Kami</a>
                <a href="#testimoni" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Testimoni</a>
                <a href="#kerjasama" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Kemitraan & Kontak</a>
            </nav>

            <!-- Topbar Right Controls -->
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
                        class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition shadow-xs cursor-pointer flex items-center justify-center" 
                        :title="isDark ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap'"
                        aria-label="Toggle Dark / Light Theme"
                    >
                        <svg x-show="isDark" x-cloak class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <svg x-show="!isDark" x-cloak class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    </button>
                </div>

                <!-- CTA Masuk Sistem / Dashboard -->
                @auth
                    @if($currentUser?->role === 'super_admin')
                        <a href="{{ route('superadmin.index') }}" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs shadow-md shadow-amber-500/20 transition flex items-center gap-2">
                            <span>⚡ Pusat Super Admin</span>
                            <span>➔</span>
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                            <span>Buka Dashboard</span>
                            <span>➔</span>
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 font-bold text-xs transition">
                        Masuk
                    </a>
                    <a href="#kerjasama" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-lg shadow-emerald-600/20 transition flex items-center gap-2">
                        <span>Konsultasi & Survei</span>
                        <span>➔</span>
                    </a>
                @endauth
                <!-- Mobile Menu Hamburger Button (Landing Page) -->
                <button 
                    @click="landingMobileMenu = !landingMobileMenu" 
                    type="button" 
                    class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 lg:hidden hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer touch-press"
                    aria-label="Buka Menu Navigasi"
                >
                    <svg x-show="!landingMobileMenu" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="landingMobileMenu" x-cloak class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Slide-Down Menu (Landing Page) -->
        <div 
            x-show="landingMobileMenu" 
            x-cloak 
            @click.away="landingMobileMenu = false"
            class="lg:hidden border-t border-slate-200 dark:border-slate-800 bg-white/98 dark:bg-slate-950/98 backdrop-blur-md px-5 py-4 space-y-3 transition-all animate-in fade-in slide-in-from-top-2 duration-200 shadow-xl"
        >
            <div class="grid grid-cols-2 gap-2 text-xs font-bold text-slate-700 dark:text-slate-200">
                <a @click="landingMobileMenu = false" href="#hero" class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 hover:text-emerald-600 transition flex items-center gap-2">
                    <span>🏠</span> Beranda
                </a>
                <a @click="landingMobileMenu = false" href="#fitur" class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 hover:text-emerald-600 transition flex items-center gap-2">
                    <span>⚡</span> Fitur Solusi
                </a>
                <a @click="landingMobileMenu = false" href="#hardware" class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 hover:text-emerald-600 transition flex items-center gap-2">
                    <span>📹</span> Hardware IoT
                </a>
                <a @click="landingMobileMenu = false" href="#posyandu" class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 hover:text-emerald-600 transition flex items-center gap-2">
                    <span>👶</span> Posyandu
                </a>
                <a @click="landingMobileMenu = false" href="#komparasi" class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 hover:text-emerald-600 transition flex items-center gap-2">
                    <span>⚖️</span> Keunggulan
                </a>
                <a @click="landingMobileMenu = false" href="#testimoni" class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 hover:text-emerald-600 transition flex items-center gap-2">
                    <span>💬</span> Testimoni
                </a>
            </div>

            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-2">
                @auth
                    @if($currentUser?->role === 'super_admin')
                        <a href="{{ route('superadmin.index') }}" class="w-full py-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs shadow-md transition flex items-center justify-center gap-2 touch-press">
                            <span>⚡ Buka Pusat Super Admin</span>
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md transition flex items-center justify-center gap-2 touch-press">
                            <span>Buka Dashboard Operasional</span>
                        </a>
                    @endif
                @else
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('login') }}" class="py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-200 hover:bg-slate-100 font-bold text-xs text-center transition touch-press">
                            Masuk Akun
                        </a>
                        <a href="#kerjasama" @click="landingMobileMenu = false" class="py-2.5 rounded-xl bg-emerald-600 text-white font-extrabold text-xs text-center shadow-md transition touch-press">
                            Konsultasi
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- Area Flash Message -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        @if(session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 px-4 py-3 rounded-2xl flex items-center gap-3 text-sm font-medium shadow-xs mb-4">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 px-4 py-3 rounded-2xl flex items-center gap-3 text-sm font-medium shadow-xs mb-4">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif
    </div>

    <!-- KONTEN MURNI LANDING PAGE (FULL WIDTH DESKTOP) -->
    <main class="w-full">
        @yield('content')
    </main>

    <!-- FOOTER RESMI BRANDING E-WARGA -->
    <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-16 text-xs text-slate-500 dark:text-slate-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-12">
                <div class="space-y-4">
                    <x-logo size="md" href="{{ route('home') }}" />
                    <p class="text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                        Platform Enterprise Smart Community & Managed IoT Living #1 di Indonesia. Mengintegrasikan Palang Pintu Otomatis, Transparansi Kas Keuangan, Layanan Surat Digital, Posyandu Terpadu, dan Keamanan Wilayah.
                    </p>
                    <div class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                        PT SMART COMMUNITY INDONESIA
                    </div>
                </div>

                <div>
                    <h4 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-[11px] mb-4">Fitur Solusi</h4>
                    <ul class="space-y-2.5">
                        <li><a href="#fitur" class="hover:text-emerald-600 transition">Transparansi Kas RT & Laporan</a></li>
                        <li><a href="#hardware" class="hover:text-emerald-600 transition">Palang Pintu Otomatis RFID / QR</a></li>
                        <li><a href="#posyandu" class="hover:text-emerald-600 transition">Posyandu Digital & Deteksi Stunting</a></li>
                        <li><a href="#fitur" class="hover:text-emerald-600 transition">Surat Pengantar RT Online Ber-QR</a></li>
                        <li><a href="#fitur" class="hover:text-emerald-600 transition">Jadwal Satpam & Ronda Swakarsa</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-[11px] mb-4">Hardware Pipeline</h4>
                    <ul class="space-y-2.5">
                        <li><a href="#hardware" class="hover:text-emerald-600 transition">Smart Barrier Gate (Tersedia)</a></li>
                        <li><a href="#hardware" class="hover:text-emerald-600 transition">Kiosk QR Scanner Outdoor (Tersedia)</a></li>
                        <li><a href="#hardware" class="hover:text-emerald-600 transition">CCTV ANPR Otomatis Plat (Coming Soon)</a></li>
                        <li><a href="#hardware" class="hover:text-emerald-600 transition">Tiang Tombol Panik Warga (Coming Soon)</a></li>
                        <li><a href="#hardware" class="hover:text-emerald-600 transition">Sensor Banjir Saluran Air (Coming Soon)</a></li>
                        <li><a href="#hardware" class="hover:text-emerald-600 transition">Smart PJU & Smart TPS (Coming Soon)</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-[11px] mb-4">Hubungi & Kerjasama</h4>
                    <div class="space-y-2 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Email Kemitraan:</span>
                            <a href="mailto:partnership@ewarga.id" class="text-emerald-600 dark:text-emerald-400 font-bold hover:underline">partnership@ewarga.id</a>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Email Penjualan / Pengadaan:</span>
                            <a href="mailto:sales@ewarga.id" class="text-emerald-600 dark:text-emerald-400 font-bold hover:underline">sales@ewarga.id</a>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Hotline WhatsApp Konsultan:</span>
                            <span class="font-mono font-bold text-slate-900 dark:text-white">+62 812-3456-7890</span>
                        </div>
                        <div class="text-[11px] text-slate-400 pt-1">
                            Layanan Survei: Jabodetabek, Bandung, Semarang, Surabaya, Denpasar.
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-8 border-t border-slate-100 dark:border-slate-800 flex flex-col md:flex-row justify-between items-center gap-4 text-slate-400">
                <div>&copy; {{ date('Y') }} E-WARGA by PT Smart Community Indonesia. Seluruh hak cipta dilindungi undang-undang.</div>
                <div class="flex items-center gap-4 text-[11px]">
                    <a href="#kerjasama" class="hover:text-slate-600 dark:hover:text-slate-300">Hubungi Konsultan</a>
                    <span>•</span>
                    <a href="{{ route('login') }}" class="hover:text-slate-600 dark:hover:text-slate-300">Login Sistem</a>
                    <span>•</span>
                    <a href="{{ route('superadmin.index') }}" class="hover:text-amber-500 font-bold">Pusat Super Admin</a>
                </div>
            </div>
        </div>
    </footer>

@elseif($isLogin)
    <!-- ================================================================= -->
    <!-- KONDISI 2: HALAMAN LOGIN (STANDALONE FULLSCREEN TANPA SIDEBAR)    -->
    <!-- ================================================================= -->
    <main class="min-h-screen w-full flex flex-col justify-between bg-slate-50 dark:bg-slate-950 transition-colors duration-200">
        @yield('content')
    </main>

@else
    <!-- ================================================================= -->
    <!-- KONDISI 3: AREA INTERNAL OPERASIONAL (DASHBOARD, SATPAM, DLL)     -->
    <!-- MENGGUNAKAN MODE LACI COLLAPSIBLE DESKTOP & STRICT RBAC           -->
    <!-- ================================================================= -->

    <!-- BACKDROP MOBILE SAAT DRAWER DIBUKA (HANYA LAYAR KECIL) -->
    <div 
        x-show="sidebarOpen" 
        @click="closeDrawer()" 
        x-cloak 
        class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-xs lg:hidden transition-opacity"
    ></div>

    <!-- ======================================================== -->
    <!-- LACI NAVIGASI DESKTOP (COLLAPSIBLE DRAWER SIDEBAR)       -->
    <!-- ======================================================== -->
    <aside 
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed top-0 left-0 z-50 h-screen w-72 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-2xl transition-transform duration-300 ease-in-out shrink-0"
    >
        <!-- 1. HEADER LACI (LOGO + COLLAPSE + TENANT SWITCHER) -->
        <div class="p-5 pb-4 border-b border-slate-100 dark:border-slate-800 shrink-0 space-y-4">
            <div class="flex items-center justify-between">
                <x-logo size="sm" href="{{ route('home') }}" />
                
                <!-- Tombol Dorong Masuk Laci (Collapse Drawer) -->
                <button 
                    @click="closeDrawer()" 
                    type="button"
                    class="p-1.5 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                    title="Dorong / Tutup Laci Menu"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                </button>
            </div>

            <!-- MULTI-TENANT SWITCHER KHUSUS SUPER ADMIN ATAU STATUS WILAYAH -->
            @if(($currentUser?->role ?? '') === 'super_admin')
                <div class="p-3 rounded-2xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/80 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[9px] font-black uppercase tracking-wider text-amber-800 dark:text-amber-300 flex items-center gap-1">
                            <span>⚡</span> WILAYAH AKTIF KENDALI:
                        </span>
                        @if(request()->routeIs('superadmin.index'))
                            <span class="text-[9px] font-black text-amber-700 dark:text-amber-300 bg-amber-200/60 dark:bg-amber-900/60 px-2 py-0.5 rounded-full border border-amber-300 dark:border-amber-700" title="Anda sedang berada di halaman Pusat Hub Multi-Wilayah">
                                ● Halaman Hub
                            </span>
                        @else
                            <a href="{{ route('superadmin.index') }}" class="text-[9px] font-extrabold text-amber-700 dark:text-amber-300 hover:text-amber-900 dark:hover:text-amber-100 bg-amber-100 dark:bg-amber-900/50 hover:bg-amber-200 px-2 py-0.5 rounded-full border border-amber-300 dark:border-amber-700 transition flex items-center gap-1 shadow-2xs" title="Buka Pusat Pengelolaan Seluruh Wilayah (Hub)">
                                <span>Pusat Hub</span>
                                <span>➔</span>
                            </a>
                        @endif
                    </div>
                    <div class="font-extrabold text-xs text-slate-900 dark:text-white truncate">
                        {{ $activeTenant?->name ?? 'Cluster Melati Mas' }}
                    </div>

                    <!-- 1-KLIK SWITCHER LINGKUNGAN DEMO (1 CLUSTER vs 1 DESA) -->
                    @php
                        $clusterDemo = \App\Models\Tenant::where('environment_type', 'cluster')->first();
                        $desaDemo = \App\Models\Tenant::where('environment_type', 'kampung')->first();
                    @endphp
                    <div class="grid grid-cols-2 gap-1.5 pt-1 border-t border-amber-200/60 dark:border-amber-800/60">
                        @if($clusterDemo)
                            <form action="{{ route('superadmin.switch-tenant', $clusterDemo->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-[10px] font-bold py-1.5 px-2 rounded-xl transition cursor-pointer {{ ($activeTenant?->id == $clusterDemo->id) ? 'bg-blue-600 text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-blue-50' }}">
                                    🏢 Cluster
                                </button>
                            </form>
                        @endif
                        @if($desaDemo)
                            <form action="{{ route('superadmin.switch-tenant', $desaDemo->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-[10px] font-bold py-1.5 px-2 rounded-xl transition cursor-pointer {{ ($activeTenant?->id == $desaDemo->id) ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-emerald-50' }}">
                                    🌾 Desa RT
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @else
                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-1">
                    <div class="flex items-center justify-between text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        <span>Wilayah Lingkungan:</span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] {{ ($activeTenant?->environment_type ?? 'cluster') === 'cluster' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200' }}">
                            {{ ($activeTenant?->environment_type ?? 'cluster') === 'cluster' ? 'Cluster' : 'Desa' }}
                        </span>
                    </div>
                    <div class="font-extrabold text-xs text-slate-800 dark:text-slate-100 truncate">
                        {{ $activeTenant?->name ?? 'Cluster Melati Mas' }}
                    </div>
                </div>
            @endif
        </div>

        <!-- 2. KONTEN MENU NAVIGASI DENGAN STRICT ROLE-BASED ACCESS CONTROL (RBAC) -->
        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-5 custom-drawer-scroll text-xs font-semibold">
            
            <!-- MENU SUPER ADMIN (PUSAT PLATFORM) -->
            @if(($currentUser?->role ?? '') === 'super_admin')
                <div>
                    <div class="px-3 pb-2 text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center justify-between">
                        <span>Pusat Kendali Platform</span>
                        <span class="text-[9px] px-1 rounded-sm bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 font-extrabold">KEPALA</span>
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('superadmin.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('superadmin.index') ? 'bg-amber-500 text-slate-950 font-black shadow-md' : 'text-slate-700 dark:text-slate-300 hover:bg-amber-50 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="text-base">⚡</span>
                                <span>Platform Hub & Multi-Wilayah</span>
                            </div>
                            <span class="text-[10px]">🏢🌾</span>
                        </a>
                    </div>
                </div>
            @endif

            <!-- MENU OPERASIONAL RT & RW / LINGKUNGAN -->
            @if(in_array($currentUser?->role ?? '', ['super_admin', 'ketua_rw', 'ketua_rt', 'bendahara']))
                <div>
                    <div class="px-3 pb-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        Manajemen Lingkungan
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('dashboard') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="text-base">📊</span>
                                <span>{{ ($currentUser?->role ?? '') === 'ketua_rw' ? 'Dashboard Supervisi RW & Kas' : 'Dashboard Utama RT & Kas' }}</span>
                            </div>
                            @if(request()->routeIs('dashboard'))
                                <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                            @endif
                        </a>

                        @if(in_array($currentUser?->role ?? '', ['super_admin', 'ketua_rw', 'ketua_rt']))
                            <a href="{{ route('letters.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('letters.*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-base">📜</span>
                                    <span>{{ ($currentUser?->role ?? '') === 'ketua_rw' ? 'Legalisasi Surat RW & RT' : 'Surat Pengantar RT' }}</span>
                                </div>
                                @if($pendingLettersCount > 0)
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-500 text-white font-black animate-pulse">
                                        {{ $pendingLettersCount }}
                                    </span>
                                @endif
                            </a>

                            <a href="{{ route('posyandu.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('posyandu.*') ? 'bg-pink-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-base">🩺</span>
                                    <span>Posyandu Digital RT/RW</span>
                                </div>
                                <span class="text-[9px] px-1.5 py-0.5 rounded-md bg-pink-100 text-pink-700 dark:bg-pink-950/60 dark:text-pink-300 font-bold">KMS</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- MENU KEAMANAN & HARDWARE (SUPER ADMIN, KETUA RW, KETUA RT, SATPAM) -->
            @if(in_array($currentUser?->role ?? '', ['super_admin', 'ketua_rw', 'ketua_rt', 'satpam']))
                <div>
                    <div class="px-3 pb-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        Keamanan & Pos Jaga
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('satpam.console') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('satpam.console') ? 'bg-slate-800 text-white font-bold shadow-xs dark:bg-slate-700' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="text-base">🛡️</span>
                                <span>Pos Pantau Satpam</span>
                            </div>
                            <span class="text-[9px] font-mono text-cyan-500 font-bold">GATE</span>
                        </a>

                        <a href="{{ route('schedules.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('schedules.*') ? 'bg-amber-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="text-base">🔦</span>
                                <span>Siap Satpam & Ronda</span>
                            </div>
                        </a>

                        <a href="{{ route('simulator') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('simulator') ? 'bg-teal-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="text-base">🧪</span>
                                <span>Simulator Smart Gate</span>
                            </div>
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        </a>
                    </div>
                </div>
            @endif

            <!-- MENU WARGA MANDIRI (WARGA & SUPER ADMIN UNTUK INSPEKSI) -->
            @if(in_array($currentUser?->role ?? '', ['super_admin', 'warga']))
                <div>
                    <div class="px-3 pb-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        Portal Warga Mandiri
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('warga.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('warga.index') ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="text-base">🏡</span>
                                <span>Portal Layanan Warga</span>
                            </div>
                            <span class="text-[9px] px-1.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 font-bold">WARGA</span>
                        </a>

                        <a href="{{ route('warga.profile') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('warga.profile') ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="text-base">🪪</span>
                                <span>Pengaturan Akun & Data Diri</span>
                            </div>
                            @if(!$currentUser?->canAccessGate() && ($currentUser?->role ?? '') === 'warga')
                                <span class="text-[9px] px-1.5 py-0.5 rounded-md bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300 font-bold animate-pulse">LENGKAPI</span>
                            @endif
                        </a>

                        @if(($currentUser?->role ?? '') === 'warga')
                            <a href="{{ route('letters.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('letters.*') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-base">📜</span>
                                    <span>Ajukan Surat Pengantar</span>
                                </div>
                            </a>

                            <a href="{{ route('schedules.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('schedules.*') ? 'bg-amber-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-base">🔦</span>
                                    <span>Siap Satpam</span>
                                </div>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- PORTAL PUBLIK LINK -->
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-500 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <span class="text-sm">🌐</span>
                    <span>Lihat Halaman Promosi / Landing</span>
                </a>
            </div>

        </div>

        <!-- 3. FOOTER LACI (DARK/LIGHT TOGGLE, ROLE SWITCHER POPOVER, PROFIL PENGGUNA) -->
        <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/90 dark:bg-slate-900/95 shrink-0 space-y-3">
            
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
                        class="w-full py-2 px-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs cursor-pointer" 
                        :title="isDark ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap'"
                    >
                        <svg x-show="isDark" x-cloak class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <svg x-show="!isDark" x-cloak class="w-3.5 h-3.5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <span class="text-[11px]" x-text="isDark ? 'Mode Terang' : 'Mode Gelap'"></span>
                    </button>
                </div>

                <!-- Role Switcher Trigger (Popover Anti-Clipping) -->
                <div x-data="{ openRole: false }" class="relative">
                    <button 
                        @click="openRole = !openRole" 
                        type="button" 
                        class="py-2 px-3 bg-slate-800 dark:bg-slate-700 hover:bg-slate-700 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1 cursor-pointer" 
                        title="Ganti Peran Pengujian Cepat"
                    >
                        <span>Role</span>
                        <span class="text-[9px]" x-text="openRole ? '▲' : '▼'"></span>
                    </button>

                    <!-- Popover Dropdown Luas Anti-Clipping -->
                    <div 
                        x-show="openRole" 
                        @click.away="openRole = false" 
                        x-cloak 
                        class="absolute bottom-full right-0 mb-3 w-64 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 p-2.5 z-50 text-xs space-y-1"
                    >
                        <div class="px-2 py-1 text-[9px] font-black text-slate-400 uppercase tracking-wider flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-1.5 mb-1">
                            <span>Ganti Peran Demo:</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold">1-KLIK</span>
                        </div>

                        <!-- Super Admin Platform (Utama) -->
                        <a href="{{ route('quick.login', 'super_admin') }}" class="flex items-center justify-between px-2.5 py-2 rounded-xl {{ ($currentUser?->role ?? '') === 'super_admin' ? 'bg-amber-50 dark:bg-amber-950/70 text-amber-900 dark:text-amber-200 font-bold border border-amber-300 dark:border-amber-700' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-2">
                                <span class="text-amber-500 font-black">⚡</span>
                                <div>
                                    <div class="font-bold">Fabian (Super Admin)</div>
                                    <div class="text-[9px] text-slate-400">Pusat & Kepala Seluruh Wilayah</div>
                                </div>
                            </div>
                            @if(($currentUser?->role ?? '') === 'super_admin')
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            @endif
                        </a>

                        <!-- Ketua RW (Rukun Warga - Jabatan Lebih Tinggi dari RT) -->
                        <a href="{{ route('quick.login', 'ketua_rw') }}" class="flex items-center justify-between px-2.5 py-2 rounded-xl {{ ($currentUser?->role ?? '') === 'ketua_rw' ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-900 dark:text-indigo-200 font-bold border border-indigo-300 dark:border-indigo-700' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-2">
                                <span class="text-base">🏛️</span>
                                <div>
                                    <div class="font-bold">Ketua RW (Bpk. H. Rahmat H.)</div>
                                    <div class="text-[9px] text-slate-400">Supervisi Seluruh RT & Legalisir RW</div>
                                </div>
                            </div>
                            @if(($currentUser?->role ?? '') === 'ketua_rw')
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            @endif
                        </a>

                        <!-- Ketua RT -->
                        <a href="{{ route('quick.login', 'ketua_rt') }}" class="flex items-center justify-between px-2.5 py-2 rounded-xl {{ ($currentUser?->role ?? '') === 'ketua_rt' ? 'bg-blue-50 dark:bg-blue-950/70 text-blue-900 dark:text-blue-200 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-2">
                                <span>👔</span>
                                <div>
                                    <div class="font-bold">Ketua RT (Bpk. H. Sutrisno)</div>
                                    <div class="text-[9px] text-slate-400">Persetujuan surat & kas</div>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('quick.login', 'bendahara') }}" class="flex items-center justify-between px-2.5 py-2 rounded-xl {{ ($currentUser?->role ?? '') === 'bendahara' ? 'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-900 dark:text-emerald-200 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-2">
                                <span>💰</span>
                                <div>
                                    <div class="font-bold">Bendahara (Bpk. Joko)</div>
                                    <div class="text-[9px] text-slate-400">Kelola buku kas RT</div>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('quick.login', 'satpam') }}" class="flex items-center justify-between px-2.5 py-2 rounded-xl {{ ($currentUser?->role ?? '') === 'satpam' ? 'bg-cyan-50 dark:bg-cyan-950/70 text-cyan-900 dark:text-cyan-200 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-2">
                                <span>🛡️</span>
                                <div>
                                    <div class="font-bold">Satpam (Pak Slamet)</div>
                                    <div class="text-[9px] text-slate-400">Palang pintu & pos jaga</div>
                                </div>
                            </div>
                        </a>

                        <!-- Warga Terverifikasi RW (Akses Gerbang Aktif) -->
                        <a href="{{ route('quick.login', 'warga') }}" class="flex items-center justify-between px-2.5 py-2 rounded-xl {{ ($currentUser?->email ?? '') === 'bambang@warga.id' ? 'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-900 dark:text-emerald-200 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-2">
                                <span>🏡</span>
                                <div>
                                    <div class="font-bold">Warga (Bpk. Bambang)</div>
                                    <div class="text-[9px] text-emerald-600 dark:text-emerald-400 font-semibold">✓ Terverifikasi RW (Gerbang Aktif)</div>
                                </div>
                            </div>
                        </a>

                        <!-- Warga Menunggu ACC RW -->
                        <a href="{{ route('quick.login', 'warga-pending') }}" class="flex items-center justify-between px-2.5 py-2 rounded-xl {{ ($currentUser?->email ?? '') === 'siti@warga.id' ? 'bg-amber-50 dark:bg-amber-950/70 text-amber-900 dark:text-amber-200 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-2">
                                <span>⏳</span>
                                <div>
                                    <div class="font-bold">Warga (Ibu Siti)</div>
                                    <div class="text-[9px] text-amber-600 dark:text-amber-400 font-semibold">Sedang Ditinjau Ketua RW</div>
                                </div>
                            </div>
                        </a>

                        <!-- Warga Baru (Akses Terkunci Total) -->
                        <a href="{{ route('quick.login', 'warga-baru') }}" class="flex items-center justify-between px-2.5 py-2 rounded-xl {{ ($currentUser?->email ?? '') === 'hendra@warga.id' ? 'bg-rose-50 dark:bg-rose-950/70 text-rose-900 dark:text-rose-200 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-2">
                                <span>🔒</span>
                                <div>
                                    <div class="font-bold">Warga Baru (Bpk. Hendra)</div>
                                    <div class="text-[9px] text-rose-600 dark:text-rose-400 font-semibold">Belum Isi KTP (Akses Terkunci)</div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- PROFIL PENGGUNA YANG SEDANG AKTIF -->
            @auth
                <div class="flex items-center justify-between pt-2 border-t border-slate-200/80 dark:border-slate-800/80">
                    <button type="button" @click="showUserAccountModal = true" class="flex items-center gap-2.5 truncate hover:opacity-80 transition text-left cursor-pointer" title="Lihat Profil Akun & Hak Akses">
                        <div class="w-7 h-7 rounded-full {{ $currentUser->isSuperAdmin() ? 'bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-700' : 'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300' }} flex items-center justify-center font-black text-xs shrink-0">
                            {{ $currentUser->isSuperAdmin() ? '⚡' : substr($currentUser->name, 0, 1) }}
                        </div>
                        <div class="truncate">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ $currentUser->name }}</div>
                            <div class="text-[10px] {{ $currentUser->isSuperAdmin() ? 'text-amber-600 dark:text-amber-400 font-extrabold' : 'text-emerald-600 dark:text-emerald-400 font-semibold' }}">
                                {{ $currentUser->role_label }}
                            </div>
                        </div>
                    </button>
                    <form action="{{ route('logout') }}" method="POST" class="inline shrink-0">
                        @csrf
                        <button type="submit" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 transition cursor-pointer" title="Keluar dari Sistem">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </aside>

    <!-- ======================================================== -->
    <!-- KONTEN UTAMA OPERASIONAL (BERGESER ERGONOMIS DENGAN LACI)-->
    <!-- ======================================================== -->
    <div 
        :class="sidebarOpen ? 'lg:pl-72' : 'pl-0'" 
        class="flex-1 flex flex-col min-w-0 min-h-screen transition-all duration-300 ease-in-out"
    >
        <!-- TOPBAR TOOLBAR TIPIS OPERASIONAL -->
        <header class="h-16 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 sticky top-0 z-30 px-6 flex items-center justify-between gap-4 transition-colors duration-200">
            <!-- Kiri: Tombol Laci & Breadcrumb -->
            <div class="flex items-center gap-3">
                <button 
                    @click="toggleDrawer()" 
                    type="button"
                    class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition cursor-pointer flex items-center gap-1.5 text-xs font-bold"
                    :title="sidebarOpen ? 'Tutup Laci Menu' : 'Buka Laci Menu'"
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

            <!-- Kanan: Tanggal & Wilayah Aktif -->
            <div class="flex items-center gap-3">
                @if(($currentUser?->role ?? '') === 'super_admin')
                    @if(request()->routeIs('superadmin.index'))
                        <a href="{{ route('dashboard') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/70 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold hover:bg-emerald-100 transition" title="Buka Dashboard Lingkungan Aktif">
                            <span>📊</span>
                            <span>Buka Dashboard Operasional ➔</span>
                        </a>
                    @else
                        <a href="{{ route('superadmin.index') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-amber-950/70 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs font-bold hover:bg-amber-100 transition" title="Buka Pusat Pengelolaan Seluruh Wilayah">
                            <span>⚡</span>
                            <span>Pusat Super Admin Hub ➔</span>
                        </a>
                    @endif
                @endif

                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 text-xs font-semibold">
                    <span>🗓️</span>
                    <span>{{ now()->translatedFormat('l, d M Y') }}</span>
                </div>

                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold {{ ($activeTenant?->environment_type ?? 'cluster') === 'cluster' ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' }}">
                    <span class="w-2 h-2 rounded-full {{ ($activeTenant?->environment_type ?? 'cluster') === 'cluster' ? 'bg-blue-500' : 'bg-emerald-500' }} animate-pulse"></span>
                    <span class="hidden md:inline">{{ $activeTenant?->name ?? 'Cluster Melati Mas' }}</span>
                </div>
            </div>
        </header>

        <!-- Area Notifikasi Flash Message Operasional -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            @if(session('success'))
                <div class="bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 px-4 py-3 rounded-2xl flex items-center gap-3 text-sm font-medium shadow-xs mb-4">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 px-4 py-3 rounded-2xl flex items-center gap-3 text-sm font-medium shadow-xs mb-4">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('panic_sent'))
                <div class="bg-red-600 text-white px-5 py-4 rounded-2xl flex items-center gap-4 text-base font-bold shadow-lg animate-pulse mb-4">
                    <span class="text-2xl">🚨</span>
                    <div>
                        <div>PERINGATAN DARURAT TERKIRIM KE POS SATPAM</div>
                        <div class="text-xs font-normal text-red-100">{{ session('panic_sent') }}</div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Area Konten Halaman Aktif -->
        <main class="flex-1 p-3.5 sm:p-6 lg:p-8 pb-28 lg:pb-8">
            @yield('content')
        </main>

        <!-- Footer Halaman Internal Operasional -->
        <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-5 px-6 text-center text-xs text-slate-400 transition-colors duration-200 pb-20 lg:pb-5">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-2">
                <div>
                    <span class="font-bold text-slate-700 dark:text-slate-300">E-WARGA Smart Community</span> &bull; Lingkungan Pintar Masa Depan
                </div>
                <div>
                    &copy; {{ date('Y') }} E-WARGA Indonesia. All rights reserved.
                </div>
            </div>
        </footer>

        <!-- ======================================================== -->
        <!-- MOBILE BOTTOM NAVIGATION BAR (APP BAR KHUSUS SMARTPHONE) -->
        <!-- ======================================================== -->
        <nav 
            class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200/90 dark:border-slate-800/90 lg:hidden px-2 pt-2 pb-safe shadow-[0_-4px_25px_rgba(0,0,0,0.1)] transition-colors duration-200"
            aria-label="Navigasi Cepat Smartphone"
        >
            <div class="flex items-center justify-around max-w-lg mx-auto">
                
                <!-- Tab 1: Dashboard / Hub -->
                @php
                    $isHub = ($currentUser?->role ?? '') === 'super_admin' && request()->routeIs('superadmin.index');
                    $isDash = request()->routeIs('dashboard');
                    $activeDash = $isHub || $isDash;
                    $dashRoute = ($currentUser?->role ?? '') === 'super_admin' ? (request()->routeIs('superadmin.index') ? route('dashboard') : route('superadmin.index')) : route('dashboard');
                @endphp
                <a 
                    href="{{ $dashRoute }}" 
                    class="flex flex-col items-center justify-center flex-1 py-1 text-center transition group touch-press {{ $activeDash ? 'text-emerald-600 dark:text-emerald-400 font-extrabold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-medium' }}"
                >
                    <div class="relative">
                        <span class="text-xl">📊</span>
                        @if($activeDash)
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        @endif
                    </div>
                    <span class="text-[10px] tracking-tight mt-1">Dashboard</span>
                </a>

                <!-- Tab 2: Portal Warga / Buka Gerbang -->
                <a 
                    href="{{ route('warga.index') }}" 
                    class="flex flex-col items-center justify-center flex-1 py-1 text-center transition group touch-press {{ request()->routeIs('warga.*') ? 'text-emerald-600 dark:text-emerald-400 font-extrabold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-medium' }}"
                >
                    <div class="relative">
                        <span class="text-xl">🚪</span>
                        @if(request()->routeIs('warga.*'))
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        @endif
                    </div>
                    <span class="text-[10px] tracking-tight mt-1">Warga & Pintu</span>
                </a>

                <!-- Tab 3: Pos Satpam / Simulator -->
                <a 
                    href="{{ ($currentUser?->role ?? '') === 'satpam' ? route('satpam.console') : route('simulator') }}" 
                    class="flex flex-col items-center justify-center flex-1 py-1 text-center transition group touch-press {{ request()->routeIs('satpam.*') || request()->routeIs('simulator') ? 'text-emerald-600 dark:text-emerald-400 font-extrabold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-medium' }}"
                >
                    <div class="relative">
                        <span class="text-xl">👮</span>
                        @if(request()->routeIs('satpam.*') || request()->routeIs('simulator'))
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        @endif
                    </div>
                    <span class="text-[10px] tracking-tight mt-1">Pos Satpam</span>
                </a>

                <!-- Tab 4: Surat Pengantar Online -->
                <a 
                    href="{{ route('letters.index') }}" 
                    class="flex flex-col items-center justify-center flex-1 py-1 text-center transition group touch-press relative {{ request()->routeIs('letters.*') ? 'text-emerald-600 dark:text-emerald-400 font-extrabold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-medium' }}"
                >
                    <div class="relative">
                        <span class="text-xl">📜</span>
                        @if($pendingLettersCount > 0)
                            <span class="absolute -top-1 -right-2 px-1.5 py-0.2 bg-amber-500 text-slate-950 font-black text-[9px] rounded-full shadow-xs animate-pulse">
                                {{ $pendingLettersCount }}
                            </span>
                        @endif
                        @if(request()->routeIs('letters.*'))
                            <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        @endif
                    </div>
                    <span class="text-[10px] tracking-tight mt-1">Surat RT</span>
                </a>

                <!-- Tab 5: Menu Lengkap Drawer -->
                <button 
                    type="button" 
                    @click="toggleDrawer()" 
                    class="flex flex-col items-center justify-center flex-1 py-1 text-center transition group touch-press cursor-pointer text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-medium"
                >
                    <div class="relative">
                        <span class="text-xl" :class="sidebarOpen ? 'text-emerald-500' : ''">☰</span>
                    </div>
                    <span class="text-[10px] tracking-tight mt-1" x-text="sidebarOpen ? 'Tutup' : 'Menu'"></span>
                </button>

            </div>
        </nav>
    </div>

@endif

    <!-- ======================================================== -->
    <!-- MODAL 1: GLOBAL POP-UP FITUR COMING SOON                 -->
    <!-- ======================================================== -->
    <div 
        x-show="openComingSoon" 
        x-cloak 
        x-on:open-coming-soon.window="triggerComingSoon($event.detail.title, $event.detail.desc)"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs transition-opacity"
    >
        <div 
            @click.away="openComingSoon = false" 
            class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5 animate-in fade-in zoom-in-95 duration-200"
        >
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-950/80 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl font-black shrink-0 shadow-xs">
                    🚀
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">INFORMASI SISTEM E-WARGA</span>
                    <h3 class="text-base font-black text-slate-900 dark:text-white" x-text="comingSoonTitle || 'Maaf, Fitur Belum Tersedia'"></h3>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-300 leading-relaxed" x-text="comingSoonDesc || 'Modul ini sedang dalam tahap pengembangan dan integrasi sistem. Silakan nantikan rilis pembaruan berikutnya.'">
            </div>

            <div class="flex items-center justify-end gap-2 pt-1">
                <button 
                    type="button" 
                    @click="openComingSoon = false" 
                    class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-100 dark:hover:bg-white text-white dark:text-slate-900 font-extrabold text-xs transition cursor-pointer shadow-sm"
                >
                    Siap, Saya Paham
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 2: DETAIL PROFIL & HAK AKSES PENGGUNA              -->
    <!-- ======================================================== -->
    @auth
    <div 
        x-show="showUserAccountModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs transition-opacity"
    >
        <div 
            @click.away="showUserAccountModal = false" 
            class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-6"
        >
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl {{ $currentUser->isSuperAdmin() ? 'bg-amber-100 dark:bg-amber-950/80 text-amber-600 dark:text-amber-400 border border-amber-300 dark:border-amber-700' : 'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300' }} flex items-center justify-center font-black text-xl shadow-xs">
                        {{ $currentUser->isSuperAdmin() ? '⚡' : substr($currentUser->name, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">{{ $currentUser->name }}</h3>
                        <span class="inline-flex items-center gap-1 text-[11px] font-extrabold px-2.5 py-0.5 rounded-full {{ $currentUser->isSuperAdmin() ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' }}">
                            {{ $currentUser->role_label }}
                        </span>
                    </div>
                </div>
                <button @click="showUserAccountModal = false" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="grid grid-cols-2 gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase block">Email Login:</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200 truncate block">{{ $currentUser->email ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase block">No. WhatsApp / HP:</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200 truncate block">{{ $currentUser->phone_number ?? '-' }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase block">Wilayah Aktif Tugas:</span>
                        <span class="font-extrabold text-slate-900 dark:text-white truncate block">{{ $activeTenant?->name ?? 'Cluster Melati Mas' }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase block">Status Hak Akses:</span>
                        <span class="font-extrabold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                            <span>●</span>
                            <span>Aktif Terverifikasi</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-100 dark:border-slate-800">
                @if($currentUser->role === 'warga')
                    <a href="{{ route('warga.profile') }}" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs transition flex items-center justify-center gap-1.5 shadow-sm">
                        <span>🪪 Atur Data Diri & KTP</span>
                        <span>➔</span>
                    </a>
                @else
                    <button 
                        type="button" 
                        @click="triggerComingSoon('Ubah Password Akun Staf', 'Untuk menjaga kepatuhan SOP keamanan lingkungan, pembaruan kata sandi akun pejabat/pengurus RT dapat dikoordinasikan langsung ke Administrator Utama Platform.'); showUserAccountModal = false;" 
                        class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition cursor-pointer"
                    >
                        🔒 Ganti Kata Sandi
                    </button>
                @endif

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="button" @click="showUserAccountModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 font-bold text-xs transition cursor-pointer">
                        Tutup
                    </button>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs transition flex items-center gap-1 cursor-pointer shadow-sm" title="Keluar dari sesi ini">
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endauth

    <script>
        window.triggerComingSoon = function(title, desc) {
            window.dispatchEvent(new CustomEvent('open-coming-soon', { detail: { title: title, desc: desc } }));
        };
    </script>
</body>
</html>
