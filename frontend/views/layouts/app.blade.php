<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="#10B981">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>{{ $title ?? 'E-WARGA - Smart Community & IoT Living Ecosystem' }}</title>
    
    <!-- Favicon Resmi SVG -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    <!-- Compiled Assets (Vite Engine) - Prioritas sebelum Google Fonts agar CSS lokal langsung render -->
    @vite(['css/app.css', 'js/app.js'])

    <!-- Fonts (Non-Blocking CSS agar tidak menahan render awal saat refresh) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"></noscript>

    <!-- Dark / Light Mode & Pre-Alpine Drawer State Early Bootstrapper (Mencegah FOUC & geser dari kiri saat refresh) -->
    <script>
        (function () {
            var root = document.documentElement;
            if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                root.classList.add('dark');
            } else {
                root.classList.remove('dark');
            }
            var drawerOpen = window.innerWidth >= 1024 ? (localStorage.getItem('ewarga_drawer_open') !== 'false') : false;
            root.classList.toggle('ewarga-drawer-open', drawerOpen);
        })();
    </script>

    <style>
        html {
            color-scheme: light;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }
        html.dark {
            color-scheme: dark;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        [x-cloak] { display: none !important; }

        /* Pre-Alpine Drawer Lock: pastikan posisi laci & konten utama langsung final sebelum Alpine.js aktif */
        :where(html:not(.ewarga-alpine-ready)) #ewarga-sidebar,
        :where(html:not(.ewarga-alpine-ready)) #ewarga-main-shell {
            transition: none !important;
        }
        @media (min-width: 1024px) {
            :where(html.ewarga-drawer-open) #ewarga-main-shell {
                padding-left: 18rem;
            }
            :where(html:not(.ewarga-drawer-open)) #ewarga-sidebar {
                transform: translateX(-100%);
            }
        }
        @media (max-width: 1023.98px) {
            :where(html:not(.ewarga-drawer-open)) #ewarga-sidebar {
                transform: translateX(-100%);
            }
        }

        /* Vercel Web Interface Standards: Respect prefers-reduced-motion across all animations */
        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
        
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
        init() {
            document.documentElement.classList.toggle('ewarga-drawer-open', this.sidebarOpen);
            requestAnimationFrame(() => {
                document.documentElement.classList.add('ewarga-alpine-ready');
            });
        },
        toggleDrawer() {
            this.sidebarOpen = !this.sidebarOpen;
            document.documentElement.classList.toggle('ewarga-drawer-open', this.sidebarOpen);
            if (window.innerWidth >= 1024) {
                localStorage.setItem('ewarga_drawer_open', this.sidebarOpen);
            }
        },
        closeDrawer() {
            this.sidebarOpen = false;
            document.documentElement.classList.remove('ewarga-drawer-open');
            if (window.innerWidth >= 1024) {
                localStorage.setItem('ewarga_drawer_open', 'false');
            }
        },
        openDrawer() {
            this.sidebarOpen = true;
            document.documentElement.classList.add('ewarga-drawer-open');
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
    $isLogin = request()->routeIs('login', 'register', 'register.post');

    $activeTenant = \App\Models\Tenant::getActiveTenant();
    $pendingLettersCount = \App\Models\LetterRequest::where('status', 'pending')->count();
    $pendingPaymentsCount = $activeTenant
        ? \App\Models\Payment::whereNull('verified_at')
            ->whereNull('rejection_reason')
            ->whereHas('billing', fn($q) => $q->where('tenant_id', $activeTenant->id))
            ->count()
        : 0;
    $idleTimeoutMinutes = max(1, (int) config('session.idle_timeout', 15));
    $currentUser = \Illuminate\Support\Facades\Auth::user();
@endphp

@if($isLanding)
    <!-- ================================================================= -->
    <!-- KONDISI 1: LANDING PAGE STANDALONE (MURNI PROMOSI / BRANDING)    -->
    <!-- SAMA SEKALI TIDAK ADA SIDEBAR / LACI MENU DI LANDING PAGE         -->
    <!-- ================================================================= -->

    <!-- TOPBAR NAVBAR KHUSUS LANDING PAGE (FLOATING GLASSMORPHISM CAPSULE DOCK) -->
    <div 
        x-data="{ 
            isScrolled: false,
            activeSection: 'hero',
            landingMobileMenu: false,
            init() {
                const handleScroll = () => {
                    this.isScrolled = window.scrollY > 20;
                    const sections = ['hero', 'fitur', 'hardware', 'posyandu', 'komparasi', 'testimoni', 'kerjasama'];
                    const scrollPos = window.scrollY + 140;
                    for (const s of sections) {
                        const el = document.getElementById(s);
                        if (el) {
                            const top = el.offsetTop;
                            const height = el.offsetHeight;
                            if (scrollPos >= top && scrollPos < top + height) {
                                this.activeSection = s;
                                break;
                            }
                        }
                    }
                };
                window.addEventListener('scroll', handleScroll, { passive: true });
                handleScroll();
            }
        }"
        class="sticky top-0 z-50 transition-colors duration-300 px-2 sm:px-4 xl:px-6 w-full"
        :class="isScrolled ? 'pt-2 sm:pt-3' : 'pt-3 sm:pt-4'"
    >
        <div class="max-w-[1440px] mx-auto relative">
            <header 
                class="relative w-full max-w-full rounded-2xl sm:rounded-full transition-colors transition-shadow duration-300 px-3 sm:px-4 xl:px-5 border flex items-center justify-between gap-2 xl:gap-3 backdrop-blur-xl"
                :class="isScrolled 
                    ? 'bg-white/90 dark:bg-slate-950/90 border-slate-200/90 dark:border-white/10 shadow-xl shadow-slate-900/5 dark:shadow-emerald-950/30 h-16 sm:h-17 ring-1 ring-slate-900/5 dark:ring-white/5' 
                    : 'bg-white/75 dark:bg-slate-950/75 border-slate-200/60 dark:border-white/10 shadow-md shadow-slate-900/5 h-18 sm:h-20'"
            >
                <!-- Subtle Top Edge Light Bar -->
                <div class="absolute inset-x-8 -top-px h-px bg-gradient-to-r from-transparent via-emerald-500/30 dark:via-emerald-400/30 to-transparent pointer-events-none"></div>

                <!-- Brand Logo SVG dengan Micro-Hover Lift -->
                <div class="shrink-0 transform hover:scale-[1.02] transition-transform duration-200">
                    <x-logo size="md" href="{{ route('home') }}" />
                </div>

                <!-- Desktop Nav Items (Responsif di layar 1280px+ tanpa overflow kanan) -->
                <nav class="hidden xl:flex items-center min-w-0 gap-0.5 2xl:gap-1 text-xs 2xl:text-[13px] font-medium text-slate-600 dark:text-slate-300 [&>a]:whitespace-nowrap [&>a]:px-2 2xl:[&>a]:px-3 [&>a]:py-1.5">
                    <a href="#hero" 
                       @click="activeSection = 'hero'"
                       class="relative rounded-full transition-colors duration-200 group flex items-center gap-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                       :class="activeSection === 'hero' 
                           ? 'text-emerald-600 dark:text-emerald-400 font-bold bg-slate-100 dark:bg-white/10 shadow-xs' 
                           : 'hover:text-emerald-600 dark:hover:text-emerald-300 hover:bg-slate-100/70 dark:hover:bg-white/5'">
                        <span>Beranda</span>
                    </a>

                    <a href="#fitur" 
                       @click="activeSection = 'fitur'"
                       class="relative rounded-full transition-colors duration-200 group flex items-center gap-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                       :class="activeSection === 'fitur' 
                           ? 'text-emerald-600 dark:text-emerald-400 font-bold bg-slate-100 dark:bg-white/10 shadow-xs' 
                           : 'hover:text-emerald-600 dark:hover:text-emerald-300 hover:bg-slate-100/70 dark:hover:bg-white/5'">
                        <span>Fitur<span class="hidden 2xl:inline"> Solusi</span></span>
                    </a>

                    <a href="#hardware" 
                       @click="activeSection = 'hardware'"
                       class="relative rounded-full transition-colors duration-200 group inline-flex items-center gap-1.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                       :class="activeSection === 'hardware' 
                           ? 'text-emerald-600 dark:text-emerald-400 font-bold bg-slate-100 dark:bg-white/10 shadow-xs' 
                           : 'hover:text-emerald-600 dark:hover:text-emerald-300 hover:bg-slate-100/70 dark:hover:bg-white/5'">
                        <span>Smart Gate IoT</span>
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[8px] 2xl:text-[9px] font-black uppercase tracking-wider bg-emerald-500/10 dark:bg-emerald-400/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 group-hover:border-emerald-500/40 transition-colors">
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                            </span>
                            <span>R&D</span>
                        </span>
                    </a>

                    <a href="#posyandu" 
                       @click="activeSection = 'posyandu'"
                       class="relative rounded-full transition-colors duration-200 group flex items-center gap-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                       :class="activeSection === 'posyandu' 
                           ? 'text-emerald-600 dark:text-emerald-400 font-bold bg-slate-100 dark:bg-white/10 shadow-xs' 
                           : 'hover:text-emerald-600 dark:hover:text-emerald-300 hover:bg-slate-100/70 dark:hover:bg-white/5'">
                        <span>Posyandu<span class="hidden 2xl:inline"> Digital</span></span>
                    </a>

                    <a href="#komparasi" 
                       @click="activeSection = 'komparasi'"
                       class="relative rounded-full transition-colors duration-200 group flex items-center gap-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                       :class="activeSection === 'komparasi' 
                           ? 'text-emerald-600 dark:text-emerald-400 font-bold bg-slate-100 dark:bg-white/10 shadow-xs' 
                           : 'hover:text-emerald-600 dark:hover:text-emerald-300 hover:bg-slate-100/70 dark:hover:bg-white/5'">
                        <span>Mengapa Kami</span>
                    </a>

                    <a href="#testimoni" 
                       @click="activeSection = 'testimoni'"
                       class="relative rounded-full transition-colors duration-200 group flex items-center gap-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                       :class="activeSection === 'testimoni' 
                           ? 'text-emerald-600 dark:text-emerald-400 font-bold bg-slate-100 dark:bg-white/10 shadow-xs' 
                           : 'hover:text-emerald-600 dark:hover:text-emerald-300 hover:bg-slate-100/70 dark:hover:bg-white/5'">
                        <span>Testimoni</span>
                    </a>

                    <a href="#kerjasama" 
                       @click="activeSection = 'kerjasama'"
                       class="relative rounded-full transition-colors duration-200 group flex items-center gap-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                       :class="activeSection === 'kerjasama' 
                           ? 'text-emerald-600 dark:text-emerald-400 font-bold bg-slate-100 dark:bg-white/10 shadow-xs' 
                           : 'hover:text-emerald-600 dark:hover:text-emerald-300 hover:bg-slate-100/70 dark:hover:bg-white/5'">
                        <span>Kemitraan</span>
                    </a>
                </nav>

                <!-- Topbar Right Controls (Theme Toggle, Masuk & Flagship CTA) -->
                <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                    <!-- Dark / Light Theme Toggle dengan Smooth Rotation Icon -->
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
                            class="w-9 h-9 rounded-full border border-slate-200/80 dark:border-white/10 bg-slate-100/80 dark:bg-slate-900/80 hover:bg-slate-200/80 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition-colors duration-200 shadow-xs cursor-pointer flex items-center justify-center active:scale-95 group touch-press focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500" 
                            :title="isDark ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap'"
                            aria-label="Toggle Dark / Light Theme"
                        >
                            <svg x-show="isDark" x-cloak class="w-4 h-4 text-amber-400 transition-transform duration-500 group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <svg x-show="!isDark" x-cloak class="w-4 h-4 text-slate-700 transition-transform duration-500 group-hover:-rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        </button>
                    </div>

                    <!-- CTA Masuk Sistem / Dashboard -->
                    @auth
                        @php
                            $desktopPortalUrl = match($currentUser?->role) {
                                'super_admin' => route('superadmin.index'),
                                'warga' => route('warga.index'),
                                'satpam' => route('satpam.console'),
                                default => route('dashboard'),
                            };
                            $desktopPortalLabel = match($currentUser?->role) {
                                'super_admin' => '⚡ Super Admin',
                                'warga' => 'Portal Warga',
                                'satpam' => 'Pos Satpam',
                                default => 'Buka Dashboard',
                            };
                        @endphp
                        @if($currentUser?->role === 'super_admin')
                            <a href="{{ $desktopPortalUrl }}" class="whitespace-nowrap px-4 py-2 rounded-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-md shadow-amber-500/25 transition-transform duration-200 hover:scale-105 active:scale-95 flex items-center gap-1.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">
                                <span>{{ $desktopPortalLabel }}</span>
                                <span>➔</span>
                            </a>
                        @else
                            <a href="{{ $desktopPortalUrl }}" class="whitespace-nowrap px-4 py-2 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs shadow-md shadow-emerald-600/25 transition-transform duration-200 hover:scale-105 active:scale-95 flex items-center gap-1.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400">
                                <span>{{ $desktopPortalLabel }}</span>
                                <span>➔</span>
                            </a>
                        @endif
                    @else
                        <!-- Tombol Daftar Warga Baru -->
                        <a href="{{ route('register') }}" class="hidden md:inline-flex whitespace-nowrap items-center gap-1.5 px-3 py-1.5 xl:py-2 rounded-full border border-emerald-500/40 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 font-extrabold text-xs transition-colors duration-200 active:scale-95 shadow-xs touch-press focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                            <span>Daftar Warga</span>
                        </a>

                        <!-- Tombol Masuk Berprofil Frosted Glass -->
                        <a href="{{ route('login') }}" class="inline-flex whitespace-nowrap items-center gap-1.5 px-3 xl:px-3.5 py-1.5 xl:py-2 rounded-full border border-slate-300/80 dark:border-white/15 bg-white/40 dark:bg-white/5 hover:bg-slate-100 dark:hover:bg-white/10 text-slate-800 dark:text-slate-100 font-bold text-xs transition-colors duration-200 active:scale-95 shadow-xs group touch-press focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                            <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-emerald-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            <span>Masuk</span>
                        </a>

                        <!-- Tombol CTA Utama dengan Efek Linear Shimmer & Glide Arrow -->
                        <a href="#kerjasama" class="whitespace-nowrap relative group overflow-hidden px-3 xl:px-4 py-1.5 xl:py-2 rounded-full bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-500 hover:from-emerald-500 hover:to-teal-400 text-white font-extrabold text-[11px] xl:text-xs shadow-lg shadow-emerald-600/30 transition-transform duration-300 transform hover:-translate-y-0.5 active:scale-95 flex items-center gap-1.5 cursor-pointer touch-press btn-shimmer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400">
                            <span class="relative z-10">Konsultasi<span class="hidden sm:inline"> & Survei</span></span>
                            <span class="relative z-10 transition-transform duration-300 group-hover:translate-x-1 font-bold">➔</span>
                        </a>
                    @endauth

                    <!-- Mobile / Tablet Menu Hamburger Button (< 1280px) -->
                    <button 
                        @click="landingMobileMenu = !landingMobileMenu" 
                        type="button" 
                        class="w-9 h-9 rounded-full border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-200 xl:hidden hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer touch-press flex items-center justify-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                        aria-label="Toggle Navigation Menu"
                    >
                        <svg x-show="!landingMobileMenu" class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="landingMobileMenu" x-cloak class="w-5 h-5 text-rose-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </header>

            <!-- Mobile / Tablet Floating Dropdown Sheet (Glassmorphism Modal < 1280px) -->
            <div 
                x-show="landingMobileMenu" 
                x-cloak 
                x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="opacity-0 -translate-y-3 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                @click.away="landingMobileMenu = false"
                class="xl:hidden mt-2 rounded-3xl bg-white/95 dark:bg-slate-950/95 backdrop-blur-2xl border border-slate-200 dark:border-white/10 shadow-2xl p-4 space-y-3 overscroll-contain"
            >
                <div class="grid grid-cols-2 gap-2 text-xs font-bold text-slate-700 dark:text-slate-200">
                    <a @click="landingMobileMenu = false" href="#hero" class="p-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900/80 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:text-emerald-600 transition-colors flex items-center gap-2">
                        <span>🏠</span> Beranda
                    </a>
                    <a @click="landingMobileMenu = false" href="#fitur" class="p-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900/80 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:text-emerald-600 transition-colors flex items-center gap-2">
                        <span>⚡</span> Fitur Solusi
                    </a>
                    <a @click="landingMobileMenu = false" href="#hardware" class="p-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900/80 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:text-emerald-600 transition-colors flex items-center gap-2">
                        <span>🚧</span> Smart Gate IoT
                    </a>
                    <a @click="landingMobileMenu = false" href="#posyandu" class="p-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900/80 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:text-emerald-600 transition-colors flex items-center gap-2">
                        <span>👶</span> Posyandu
                    </a>
                    <a @click="landingMobileMenu = false" href="#komparasi" class="p-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900/80 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:text-emerald-600 transition-colors flex items-center gap-2">
                        <span>⚖️</span> Keunggulan
                    </a>
                    <a @click="landingMobileMenu = false" href="#testimoni" class="p-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900/80 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:text-emerald-600 transition-colors flex items-center gap-2">
                        <span>💬</span> Testimoni
                    </a>
                    <a @click="landingMobileMenu = false" href="#kerjasama" class="p-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900/80 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:text-emerald-600 transition-colors flex items-center gap-2">
                        <span>🤝</span> Kemitraan
                    </a>
                    <a @click="landingMobileMenu = false" href="{{ route('simulator') }}" class="p-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900/80 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:text-emerald-600 transition-colors flex items-center gap-2">
                        <span>🧪</span> Simulator IoT
                    </a>
                </div>

                <div class="pt-2 border-t border-slate-100 dark:border-white/10 space-y-2">
                    @auth
                        @php
                            $mobilePortalUrl = match($currentUser?->role) {
                                'super_admin' => route('superadmin.index'),
                                'warga' => route('warga.index'),
                                'satpam' => route('satpam.console'),
                                default => route('dashboard'),
                            };
                            $mobilePortalLabel = match($currentUser?->role) {
                                'super_admin' => '⚡ Buka Pusat Super Admin',
                                'warga' => 'Buka Portal Mandiri Warga',
                                'satpam' => 'Buka Console Pos Satpam',
                                default => 'Buka Dashboard Operasional',
                            };
                        @endphp
                        @if($currentUser?->role === 'super_admin')
                            <a href="{{ $mobilePortalUrl }}" class="w-full py-3 rounded-2xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs shadow-md transition-colors flex items-center justify-center gap-2 touch-press">
                                <span>{{ $mobilePortalLabel }}</span>
                            </a>
                        @else
                            <a href="{{ $mobilePortalUrl }}" class="w-full py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md transition-colors flex items-center justify-center gap-2 touch-press">
                                <span>{{ $mobilePortalLabel }}</span>
                            </a>
                        @endif
                    @else
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('login') }}" class="py-2.5 rounded-2xl border border-slate-300 dark:border-white/15 text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5 font-bold text-xs text-center transition-colors touch-press">
                                Masuk Akun
                            </a>
                            <a href="{{ route('register') }}" class="py-2.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold text-xs text-center shadow-md shadow-emerald-600/20 transition-transform touch-press">
                                Daftar Warga
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Area Flash Message -->
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 pt-4">
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

        @if(session('warning'))
            <div class="bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 px-4 py-3 rounded-2xl flex items-center gap-3 text-sm font-medium shadow-xs mb-4">
                <span class="text-lg shrink-0">⚠️</span>
                <span>{{ session('warning') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300 px-4 py-3 rounded-2xl flex items-center gap-3 text-sm font-medium shadow-xs mb-4">
                <span class="text-lg shrink-0">ℹ️</span>
                <span>{{ session('info') }}</span>
            </div>
        @endif
    </div>

    <!-- KONTEN MURNI LANDING PAGE (FULL WIDTH DESKTOP) -->
    <main class="w-full">
        @yield('content')
    </main>

    <!-- FOOTER RESMI BRANDING E-WARGA -->
    <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 pt-16 pb-32 sm:pb-36 lg:pb-28 text-xs text-slate-500 dark:text-slate-400">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-12">
                <div class="space-y-4">
                    <x-logo size="md" href="{{ route('home') }}" />
                    <p class="text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                        Platform Enterprise Smart Community & Managed IoT Living #1 di Indonesia. Mengintegrasikan Palang Pintu Otomatis, Transparansi Kas Keuangan, Layanan Surat Digital, Posyandu Terpadu, dan Keamanan Wilayah.
                    </p>
                    <div class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                        Fabian Ganteng
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
                            <a href="mailto:partnership@ewarga.id" class="text-emerald-600 dark:text-emerald-400 font-bold hover:underline">Fabian Ganteng</a>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Email Penjualan / Pengadaan:</span>
                            <a href="mailto:sales@ewarga.id" class="text-emerald-600 dark:text-emerald-400 font-bold hover:underline">Fabian Ganteng</a>
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

            <div class="pt-8 border-t border-slate-100 dark:border-slate-800 flex flex-col md:flex-row justify-between items-center gap-4 text-slate-400 pb-24 sm:pb-28 lg:pb-12">
                <div class="text-xs text-center md:text-left">&copy; {{ date('Y') }} E-WARGA by Fabian Ganteng &bull; Seluruh hak cipta dilindungi undang-undang.</div>
                <div class="flex flex-wrap items-center justify-center md:justify-end gap-x-4 gap-y-2 text-[11px] sm:pr-56 md:pr-64 lg:pr-72 xl:pr-80">
                    <a href="{{ route('register') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 font-bold transition-colors">Daftar Warga Baru</a>
                    <span>•</span>
                    <a href="#kerjasama" class="hover:text-slate-600 dark:hover:text-slate-300 transition-colors">Hubungi Konsultan</a>
                    <span>•</span>
                    <a href="{{ route('login') }}" class="hover:text-slate-600 dark:hover:text-slate-300 transition-colors">Login Sistem</a>
                    <span>•</span>
                    @auth
                        <a href="{{ route('superadmin.index') }}" class="hover:text-amber-500 font-bold transition-colors">Pusat Super Admin</a>
                    @else
                        <a href="{{ route('quick.login', 'super_admin') }}" class="hover:text-amber-500 font-bold transition-colors" title="Masuk Langsung sebagai Super Admin">Pusat Super Admin</a>
                    @endauth
                </div>
            </div>
        </div>
    </footer>

    <!-- ======================================================== -->
    <!-- MOBILE BOTTOM NAVIGATION BAR FOR LANDING VISITORS        -->
    <!-- ======================================================== -->
    <nav 
        class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200/90 dark:border-slate-800/90 lg:hidden px-2 pt-1.5 pb-[max(env(safe-area-inset-bottom),0.5rem)] shadow-[0_-4px_25px_rgba(0,0,0,0.12)] transition-colors duration-200"
        aria-label="Navigasi Cepat Smartphone Android"
    >
        <div class="flex items-center justify-around max-w-lg mx-auto">
            <a 
                href="{{ route('home') }}#beranda" 
                class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press text-slate-600 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 font-semibold"
            >
                <div class="relative px-3 py-1 rounded-full transition-colors">
                    <span class="text-xl">🏠</span>
                </div>
                <span class="text-[11px] tracking-tight mt-0.5">Beranda</span>
            </a>

            <a 
                href="{{ route('home') }}#fitur" 
                class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press text-slate-600 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 font-semibold"
            >
                <div class="relative px-3 py-1 rounded-full transition-colors">
                    <span class="text-xl">⚡</span>
                </div>
                <span class="text-[11px] tracking-tight mt-0.5">Fitur</span>
            </a>

            <a 
                href="{{ route('home') }}#hardware" 
                class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press text-slate-600 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 font-semibold"
            >
                <div class="relative px-3 py-1 rounded-full transition-colors">
                    <span class="text-xl">🚪</span>
                </div>
                <span class="text-[11px] tracking-tight mt-0.5">IoT Palang</span>
            </a>

            <a 
                href="{{ route('home') }}#kerjasama" 
                class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press text-slate-600 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 font-semibold"
            >
                <div class="relative px-3 py-1 rounded-full transition-colors">
                    <span class="text-xl">💬</span>
                </div>
                <span class="text-[11px] tracking-tight mt-0.5">Konsultasi</span>
            </a>

            @auth
                <a 
                    href="{{ Auth::user()->role === 'warga' ? route('warga.index') : (Auth::user()->role === 'satpam' ? route('satpam.console') : route('dashboard')) }}" 
                    class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press text-emerald-700 dark:text-emerald-300 font-extrabold"
                >
                    <div class="relative px-3 py-1 rounded-full bg-emerald-500/15 dark:bg-emerald-500/25 transition-colors">
                        <span class="text-xl">📊</span>
                    </div>
                    <span class="text-[11px] tracking-tight mt-0.5">Portal</span>
                </a>
            @else
                <a 
                    href="{{ route('login') }}" 
                    class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press text-emerald-700 dark:text-emerald-300 font-extrabold"
                >
                    <div class="relative px-3 py-1 rounded-full bg-emerald-500/15 dark:bg-emerald-500/25 transition-colors">
                        <span class="text-xl">🔑</span>
                    </div>
                    <span class="text-[11px] tracking-tight mt-0.5">Masuk</span>
                </a>
            @endauth
        </div>
    </nav>

@elseif($isLogin)
    <!-- ================================================================= -->
    <!-- KONDISI 2: HALAMAN LOGIN & REGISTRASI (STANDALONE FULLSCREEN)     -->
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
        id="ewarga-sidebar"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed top-0 left-0 z-50 h-screen w-72 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-2xl transition-transform duration-200 ease-out shrink-0"
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
            @if(in_array($currentUser?->role ?? '', ['super_admin', 'ketua_rw', 'ketua_rt', 'sekretaris', 'bendahara', 'pengurus_rt']))
                <div>
                    <div class="px-3 pb-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        Manajemen Lingkungan
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('dashboard') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="text-base">📊</span>
                                <span>{{ ($currentUser?->role ?? '') === 'ketua_rw' ? 'Dashboard Supervisi RW' : 'Dashboard Utama RT' }}</span>
                            </div>
                            @if(request()->routeIs('dashboard'))
                                <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                            @endif
                        </a>

                        @if(in_array($currentUser?->role ?? '', ['super_admin', 'ketua_rw', 'ketua_rt', 'sekretaris', 'bendahara', 'pengurus_rt']))
                            <a href="{{ route('keuangan.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('keuangan.*') ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-base">💰</span>
                                    <span>Keuangan &amp; Iuran RT</span>
                                </div>
                                @if($pendingPaymentsCount > 0)
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-500 text-slate-950 font-black animate-pulse tabular-nums">
                                        {{ $pendingPaymentsCount }}
                                    </span>
                                @elseif(request()->routeIs('keuangan.*'))
                                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                @endif
                            </a>
                        @endif

                        @if(in_array($currentUser?->role ?? '', ['super_admin', 'ketua_rw', 'ketua_rt', 'sekretaris', 'bendahara', 'pengurus_rt']))
                            <a href="{{ route('residents.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('residents.*') ? 'bg-teal-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-base">👨‍👩‍👧‍👦</span>
                                    <span>Data Warga &amp; Kartu Keluarga</span>
                                </div>
                            </a>
                        @endif

                        @if(in_array($currentUser?->role ?? '', ['super_admin', 'ketua_rw', 'ketua_rt', 'sekretaris', 'pengurus_rt']))
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

                        <a href="{{ route('reports.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('reports.*') ? 'bg-amber-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="text-base">📣</span>
                                <span>Laporan &amp; Aspirasi Warga</span>
                            </div>
                        </a>
                    </div>
                </div>
            @endif

            @php
                $sidebarGateOnline = \App\Services\RfidService::getGateOnlineStatus(($activeTenant ?? null)?->id)['online'];
            @endphp

            <!-- MENU KEAMANAN & HARDWARE (SUPER ADMIN, KETUA RW, KETUA RT, SATPAM) -->
            @if(in_array($currentUser?->role ?? '', ['super_admin', 'ketua_rw', 'ketua_rt', 'sekretaris', 'satpam']))
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

                        <a href="{{ route('warga.smart-gate') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('warga.smart-gate') ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="text-base">🚧</span>
                                <span>Akses Smart Gate</span>
                            </div>
                            <span
                                x-data="{ gateOnline: {{ $sidebarGateOnline ? 'true' : 'false' }} }"
                                @ewarga-gate-status.window="gateOnline = !!$event.detail.gateOnline"
                                class="text-[9px] px-1.5 py-0.5 rounded-md font-bold transition-colors"
                                :class="gateOnline ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300'"
                                x-text="gateOnline ? 'ONLINE' : 'OFFLINE'"
                            >
                                {{ $sidebarGateOnline ? 'ONLINE' : 'OFFLINE' }}
                            </span>
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

                        @if(($currentUser?->role ?? '') === 'satpam')
                            <a href="{{ route('reports.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('reports.*') ? 'bg-amber-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-base">📣</span>
                                    <span>Laporan Keamanan Warga</span>
                                </div>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            @php
                $pendingVisitorCount = ($currentUser?->house_id && in_array($currentUser?->role ?? '', ['super_admin', 'warga']))
                    ? \App\Models\Visitor::where('host_house_id', $currentUser->house_id)->where('approval_status', 'pending')->where('valid_until', '>=', now())->count()
                    : 0;
            @endphp

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

                        <a href="{{ route('warga.smart-gate') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('warga.smart-gate') ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="text-base">🚧</span>
                                <span>Smart Gate Perumahan</span>
                            </div>
                            @if($pendingVisitorCount > 0)
                                <span class="text-[9px] px-2 py-0.5 rounded-full bg-amber-500 text-slate-950 font-black animate-pulse">{{ $pendingVisitorCount }} Tamu</span>
                            @elseif($currentUser?->canAccessGate())
                                <span
                                    x-data="{ gateOnline: {{ ($sidebarGateOnline ?? false) ? 'true' : 'false' }} }"
                                    @ewarga-gate-status.window="gateOnline = !!$event.detail.gateOnline"
                                    class="text-[9px] px-1.5 py-0.5 rounded-md font-bold transition-colors"
                                    :class="gateOnline ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300'"
                                    x-text="gateOnline ? 'ONLINE' : 'OFFLINE'"
                                >
                                    {{ ($sidebarGateOnline ?? false) ? 'ONLINE' : 'OFFLINE' }}
                                </span>
                            @else
                                <span class="text-[9px] px-1.5 py-0.5 rounded-md bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300 font-bold">KUNCI</span>
                            @endif
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
                            <a href="{{ route('reports.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ request()->routeIs('reports.*') ? 'bg-amber-600 text-white font-bold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-base">📣</span>
                                    <span>Laporan &amp; Aspirasi</span>
                                </div>
                            </a>

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

                        <!-- Sekretaris RT -->
                        <a href="{{ route('quick.login', 'sekretaris') }}" class="flex items-center justify-between px-2.5 py-2 rounded-xl {{ ($currentUser?->role ?? '') === 'sekretaris' ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-900 dark:text-indigo-200 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-2">
                                <span>🗂️</span>
                                <div>
                                    <div class="font-bold">Sekretaris (Ibu Farida)</div>
                                    <div class="text-[9px] text-slate-400">Administrasi warga, KK &amp; surat</div>
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
                    <form action="{{ route('logout') }}" method="POST" class="inline shrink-0" @submit.prevent="window.ewargaConfirmSubmit($el, { title: 'Keluar dari Sistem?', message: 'Anda yakin ingin logout dari akun ini?', confirmText: 'Ya, Keluar', tone: 'danger' })">
                        @csrf
                        <button type="submit" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 transition-colors cursor-pointer" title="Keluar dari Sistem">
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
        id="ewarga-main-shell"
        :class="sidebarOpen ? 'lg:pl-72' : 'pl-0'" 
        class="flex-1 flex flex-col min-w-0 min-h-screen transition-[padding] duration-200 ease-out"
    >
        <!-- TOPBAR TOOLBAR TIPIS OPERASIONAL -->
        <header class="h-16 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 sticky top-0 z-30 px-3.5 sm:px-6 flex items-center justify-between gap-3 sm:gap-4 transition-colors duration-200">
            <!-- Kiri: Tombol Laci & Breadcrumb -->
            <div class="flex items-center gap-3">
                <button 
                    @click="toggleDrawer()" 
                    type="button"
                    class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer flex items-center gap-1.5 text-xs font-bold"
                    :title="sidebarOpen ? 'Tutup Laci Menu' : 'Buka Laci Menu'"
                >
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <span class="hidden sm:inline" x-text="sidebarOpen ? 'Tutup Laci' : 'Buka Laci'">Tutup Laci</span>
                </button>
                
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                    <span class="text-slate-900 dark:text-white font-bold">E-WARGA</span>
                    <span>/</span>
                    <span class="text-emerald-600 dark:text-emerald-400 capitalize">
                        {{ str_replace('-', ' ', request()->segment(1) ?: 'Dashboard') }}
                    </span>
                </div>
            </div>

            <!-- Kanan: Mode Gelap/Terang, Tanggal & Wilayah Aktif -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Dark / Light Theme Toggle Topbar -->
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
                }">
                    <button 
                        @click="toggle()" 
                        type="button" 
                        class="p-2 sm:px-3 sm:py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer"
                        :title="isDark ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap'"
                    >
                        <svg x-show="isDark" x-cloak class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <svg x-show="!isDark" x-cloak class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <span class="hidden sm:inline text-[11px]" x-text="isDark ? 'Mode Terang' : 'Mode Gelap'"></span>
                    </button>
                </div>

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
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
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

            @if(session('warning'))
                <div class="bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 px-4 py-3 rounded-2xl flex items-center gap-3 text-sm font-medium shadow-xs mb-4">
                    <span class="text-lg shrink-0">⚠️</span>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300 px-4 py-3 rounded-2xl flex items-center gap-3 text-sm font-medium shadow-xs mb-4">
                    <span class="text-lg shrink-0">ℹ️</span>
                    <span>{{ session('info') }}</span>
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
        <main class="flex-1 p-3.5 sm:p-5 lg:p-6 pb-28 lg:pb-8">
            @yield('content')
        </main>

        <!-- Footer Halaman Internal Operasional -->
        <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-5 px-6 text-center text-xs text-slate-400 transition-colors duration-200 pb-24 lg:pb-6">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-2 lg:mr-72">
                <div>
                    <span class="font-bold text-slate-700 dark:text-slate-300">E-WARGA by Fabian Ganteng</span> &bull; Lingkungan Pintar Masa Depan
                </div>
                <div>
                    &copy; {{ date('Y') }} E-WARGA by Fabian Ganteng. All rights reserved.
                </div>
            </div>
        </footer>

        <!-- ======================================================== -->
        <!-- MOBILE BOTTOM NAVIGATION BAR (ANDROID MATERIAL 3 DOCK)   -->
        <!-- ======================================================== -->
        <nav 
            class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200/90 dark:border-slate-800/90 lg:hidden px-2 pt-1.5 pb-[max(env(safe-area-inset-bottom),0.5rem)] shadow-[0_-4px_25px_rgba(0,0,0,0.12)] transition-colors duration-200"
            aria-label="Navigasi Cepat Smartphone Android"
        >
            <div class="flex items-center justify-around max-w-lg mx-auto">
                @if(($currentUser?->role ?? '') === 'warga')
                    <!-- Tab Warga 1: Beranda Warga -->
                    <a 
                        href="{{ route('warga.index') }}" 
                        class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press {{ request()->routeIs('warga.index') ? 'text-emerald-700 dark:text-emerald-300 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-semibold' }}"
                    >
                        <div class="relative px-3 py-1 rounded-full {{ request()->routeIs('warga.index') ? 'bg-emerald-500/15 dark:bg-emerald-500/25' : '' }} transition-colors">
                            <span class="text-xl">🏠</span>
                        </div>
                        <span class="text-[11px] tracking-tight mt-0.5">Beranda</span>
                    </a>

                    <!-- Tab Warga 2: Iuran Warga -->
                    <a 
                        href="{{ route('warga.index') }}#step-billing" 
                        class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-semibold"
                    >
                        <div class="relative px-3 py-1 rounded-full transition-colors">
                            <span class="text-xl">💳</span>
                        </div>
                        <span class="text-[11px] tracking-tight mt-0.5">Iuran</span>
                    </a>

                    <!-- Tab Warga 3: Smart Gate & Tamu -->
                    <a 
                        href="{{ route('warga.smart-gate') }}" 
                        class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press {{ request()->routeIs('warga.smart-gate') ? 'text-emerald-700 dark:text-emerald-300 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-semibold' }}"
                    >
                        <div class="relative px-3 py-1 rounded-full {{ request()->routeIs('warga.smart-gate') ? 'bg-emerald-500/20 border border-emerald-500/40 text-emerald-600 dark:text-emerald-300' : 'bg-emerald-500/10' }} transition-colors">
                            <span class="text-xl">🚧</span>
                            @if($pendingVisitorCount > 0)
                                <span class="absolute -top-1 -right-1 px-1.5 py-0.2 bg-amber-500 text-slate-950 font-black text-[9px] rounded-full shadow-xs animate-pulse">
                                    {{ $pendingVisitorCount }}
                                </span>
                            @endif
                        </div>
                        <span class="text-[11px] tracking-tight mt-0.5">Smart Gate</span>
                    </a>

                    <!-- Tab Warga 4: Surat RT Online -->
                    <a 
                        href="{{ route('letters.index') }}" 
                        class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press relative {{ request()->routeIs('letters.*') ? 'text-emerald-700 dark:text-emerald-300 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-semibold' }}"
                    >
                        <div class="relative px-3 py-1 rounded-full {{ request()->routeIs('letters.*') ? 'bg-emerald-500/15 dark:bg-emerald-500/25' : '' }} transition-colors">
                            <span class="text-xl">📜</span>
                            @if($pendingLettersCount > 0)
                                <span class="absolute -top-1 -right-1 px-1.5 py-0.2 bg-amber-500 text-slate-950 font-black text-[9px] rounded-full shadow-xs animate-pulse">
                                    {{ $pendingLettersCount }}
                                </span>
                            @endif
                        </div>
                        <span class="text-[11px] tracking-tight mt-0.5">Surat RT</span>
                    </a>

                    <!-- Tab Warga 5: Profil Akun -->
                    <a 
                        href="{{ route('warga.profile') }}" 
                        class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press {{ request()->routeIs('warga.profile') ? 'text-emerald-700 dark:text-emerald-300 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-semibold' }}"
                    >
                        <div class="relative px-3 py-1 rounded-full {{ request()->routeIs('warga.profile') ? 'bg-emerald-500/15 dark:bg-emerald-500/25' : '' }} transition-colors">
                            <span class="text-xl">🪪</span>
                        </div>
                        <span class="text-[11px] tracking-tight mt-0.5">Profil</span>
                    </a>

                @elseif(($currentUser?->role ?? '') === 'satpam')
                    <!-- Tab Satpam 1: Konsol Pos Jaga -->
                    <a 
                        href="{{ route('satpam.console') }}" 
                        class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press {{ request()->routeIs('satpam.console') ? 'text-emerald-700 dark:text-emerald-300 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-semibold' }}"
                    >
                        <div class="relative px-3 py-1 rounded-full {{ request()->routeIs('satpam.console') ? 'bg-emerald-500/15 dark:bg-emerald-500/25' : '' }} transition-colors">
                            <span class="text-xl">🛡️</span>
                        </div>
                        <span class="text-[11px] tracking-tight mt-0.5">Pos Jaga</span>
                    </a>

                    <!-- Tab Satpam 2: Simulator Palang -->
                    <a 
                        href="{{ route('simulator') }}" 
                        class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press {{ request()->routeIs('simulator') ? 'text-emerald-700 dark:text-emerald-300 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-semibold' }}"
                    >
                        <div class="relative px-3 py-1 rounded-full {{ request()->routeIs('simulator') ? 'bg-emerald-500/15 dark:bg-emerald-500/25' : '' }} transition-colors">
                            <span class="text-xl">🚪</span>
                        </div>
                        <span class="text-[11px] tracking-tight mt-0.5">Palang IoT</span>
                    </a>

                    <!-- Tab Satpam 3: Tanya AI -->
                    <button 
                        type="button" 
                        @click="$dispatch('open-ai-chat')" 
                        class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press cursor-pointer text-emerald-600 dark:text-emerald-400 font-extrabold"
                    >
                        <div class="relative px-3 py-1 rounded-full bg-emerald-500/15 dark:bg-emerald-500/25 border border-emerald-500/30 shadow-xs transition-transform group-active:scale-95">
                            <span class="text-xl animate-bounce">🤖</span>
                        </div>
                        <span class="text-[11px] tracking-tight mt-0.5 text-emerald-700 dark:text-emerald-300">Tanya AI</span>
                    </button>

                    <!-- Tab Satpam 4: Jadwal Shift -->
                    <a 
                        href="{{ route('schedules.index') }}" 
                        class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press {{ request()->routeIs('schedules.*') ? 'text-emerald-700 dark:text-emerald-300 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-semibold' }}"
                    >
                        <div class="relative px-3 py-1 rounded-full {{ request()->routeIs('schedules.*') ? 'bg-emerald-500/15 dark:bg-emerald-500/25' : '' }} transition-colors">
                            <span class="text-xl">📋</span>
                        </div>
                        <span class="text-[11px] tracking-tight mt-0.5">Jadwal</span>
                    </a>

                    <!-- Tab Satpam 5: Akun Staf -->
                    <button 
                        type="button" 
                        @click="showUserAccountModal = true" 
                        class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press cursor-pointer text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-semibold"
                    >
                        <div class="relative px-3 py-1 rounded-full transition-colors">
                            <span class="text-xl">👤</span>
                        </div>
                        <span class="text-[11px] tracking-tight mt-0.5">Profil</span>
                    </button>

                @else
                    <!-- Tab Pengurus / Super Admin / RT / RW / Bendahara -->
                    @php
                        $isSuperAdmin = ($currentUser?->role ?? '') === 'super_admin';
                        $dashUrl = $isSuperAdmin ? route('superadmin.index') : route('dashboard');
                        $isDashActive = request()->routeIs('dashboard') || ($isSuperAdmin && request()->routeIs('superadmin.index'));
                    @endphp
                    <!-- Tab 1: Beranda Operasional -->
                    <a 
                        href="{{ $dashUrl }}" 
                        class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press {{ $isDashActive ? 'text-emerald-700 dark:text-emerald-300 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-semibold' }}"
                    >
                        <div class="relative px-3 py-1 rounded-full {{ $isDashActive ? 'bg-emerald-500/15 dark:bg-emerald-500/25' : '' }} transition-colors">
                            <span class="text-xl">📊</span>
                        </div>
                        <span class="text-[11px] tracking-tight mt-0.5">Beranda</span>
                    </a>

                    <!-- Tab 2: Kas & Iuran -->
                    <a 
                        href="{{ route('keuangan.index') }}" 
                        class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press relative {{ request()->routeIs('keuangan.*') ? 'text-emerald-700 dark:text-emerald-300 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-semibold' }}"
                    >
                        <div class="relative px-3 py-1 rounded-full {{ request()->routeIs('keuangan.*') ? 'bg-emerald-500/15 dark:bg-emerald-500/25' : '' }} transition-colors">
                            <span class="text-xl">💳</span>
                            @if($pendingPaymentsCount > 0)
                                <span class="absolute -top-1 -right-1 px-1.5 py-0.2 bg-amber-500 text-slate-950 font-black text-[9px] rounded-full shadow-xs animate-pulse tabular-nums">
                                    {{ $pendingPaymentsCount }}
                                </span>
                            @endif
                        </div>
                        <span class="text-[11px] tracking-tight mt-0.5">Kas RT</span>
                    </a>

                    <!-- Tab 3: Surat RT Online -->
                    <a 
                        href="{{ route('letters.index') }}" 
                        class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press relative {{ request()->routeIs('letters.*') ? 'text-emerald-700 dark:text-emerald-300 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-semibold' }}"
                    >
                        <div class="relative px-3 py-1 rounded-full {{ request()->routeIs('letters.*') ? 'bg-emerald-500/15 dark:bg-emerald-500/25' : '' }} transition-colors">
                            <span class="text-xl">📜</span>
                            @if($pendingLettersCount > 0)
                                <span class="absolute -top-1 -right-1 px-1.5 py-0.2 bg-amber-500 text-slate-950 font-black text-[9px] rounded-full shadow-xs animate-pulse">
                                    {{ $pendingLettersCount }}
                                </span>
                            @endif
                        </div>
                        <span class="text-[11px] tracking-tight mt-0.5">Surat RT</span>
                    </a>

                    <!-- Tab 4: Data Warga & Kartu Keluarga -->
                    <a 
                        href="{{ route('residents.index') }}" 
                        class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 rounded-xl {{ request()->routeIs('residents.*') ? 'text-emerald-700 dark:text-emerald-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-semibold' }}"
                    >
                        <div class="relative px-3 py-1 rounded-full transition-colors {{ request()->routeIs('residents.*') ? 'bg-emerald-100 dark:bg-emerald-950/80 shadow-inner' : '' }}">
                            <span class="text-xl">🪪</span>
                        </div>
                        <span class="text-[11px] tracking-tight mt-0.5">Warga</span>
                    </a>

                    <!-- Tab 5: Menu & Akun -->
                    <button 
                        type="button" 
                        @click="showUserAccountModal = true" 
                        class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-colors group touch-press cursor-pointer text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-semibold"
                    >
                        <div class="relative px-3 py-1 rounded-full transition-colors">
                            <span class="text-xl">👤</span>
                        </div>
                        <span class="text-[11px] tracking-tight mt-0.5">Profil</span>
                    </button>
                @endif
            </div>
        </nav>
    </div>

@endif

    <!-- ======================================================== -->
    <!-- ASISTEN AI BANG WARGA (KOMPONEN HYBRID GLOBAL)           -->
    <!-- ======================================================== -->
    <x-ai-assistant :has-bottom-bar="!$isLanding && !$isLogin" />

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

            <div x-data="{ showStaffPasswordForm: false }" class="space-y-3">
                @if($currentUser->role !== 'warga')
                    <form
                        x-show="showStaffPasswordForm"
                        x-cloak
                        action="{{ route('warga.change-password') }}"
                        method="POST"
                        class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 space-y-2.5 text-left"
                    >
                        @csrf
                        <div class="text-xs font-extrabold text-slate-800 dark:text-slate-100">Ganti Kata Sandi Akun</div>
                        <div>
                            <label for="staff_current_password" class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Kata Sandi Saat Ini</label>
                            <input
                                id="staff_current_password"
                                type="password"
                                name="current_password"
                                autocomplete="current-password"
                                required
                                placeholder="Masukkan kata sandi saat ini…"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs text-slate-900 dark:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                            >
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label for="staff_new_password" class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Kata Sandi Baru</label>
                                <input
                                    id="staff_new_password"
                                    type="password"
                                    name="new_password"
                                    autocomplete="new-password"
                                    minlength="6"
                                    required
                                    placeholder="Minimal 6 karakter…"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs text-slate-900 dark:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                                >
                            </div>
                            <div>
                                <label for="staff_new_password_confirmation" class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">Konfirmasi Sandi Baru</label>
                                <input
                                    id="staff_new_password_confirmation"
                                    type="password"
                                    name="new_password_confirmation"
                                    autocomplete="new-password"
                                    minlength="6"
                                    required
                                    placeholder="Ulangi sandi baru…"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs text-slate-900 dark:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                                >
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-1">
                            <button
                                type="button"
                                @click="showStaffPasswordForm = false"
                                class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors cursor-pointer touch-press focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-extrabold transition-colors cursor-pointer touch-press focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                            >
                                Simpan Kata Sandi
                            </button>
                        </div>
                    </form>
                @endif

                <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-100 dark:border-slate-800">
                    @if($currentUser->role === 'warga')
                        <a href="{{ route('warga.profile') }}" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs transition-colors flex items-center justify-center gap-1.5 shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                            <span>🪪 Atur Data Diri &amp; KTP</span>
                            <span>➔</span>
                        </a>
                    @else
                        <button 
                            type="button" 
                            @click="showStaffPasswordForm = !showStaffPasswordForm" 
                            :aria-expanded="showStaffPasswordForm"
                            class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition-colors cursor-pointer touch-press focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                        >
                            🔒 Ganti Kata Sandi
                        </button>
                    @endif

                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                        <button type="button" @click="showUserAccountModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 font-bold text-xs transition-colors cursor-pointer touch-press focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                            Tutup
                        </button>
                        <form action="{{ route('logout') }}" method="POST" class="inline" @submit.prevent="window.ewargaConfirmSubmit($el, { title: 'Keluar dari Sistem?', message: 'Anda yakin ingin logout dari akun ini?', confirmText: 'Ya, Keluar', tone: 'danger' })">
                            @csrf
                            <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs transition-colors flex items-center gap-1 cursor-pointer touch-press shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500" title="Keluar dari sesi ini">
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Tersembunyi Auto-Logout Idle Session -->
    <form id="ewarga-idle-logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="idle" value="1">
    </form>
    @endauth

    <!-- ======================================================== -->
    <!-- FLOATING TOAST NOTIFICATIONS (SUCCESS / ERROR / WARNING / INFO) -->
    <!-- ======================================================== -->
    <div
        x-data="{
            toasts: [],
            init() {
                const initial = @js([
                    session('success') ? ['type' => 'success', 'text' => session('success')] : null,
                    session('error') ? ['type' => 'error', 'text' => session('error')] : null,
                    session('warning') ? ['type' => 'warning', 'text' => session('warning')] : null,
                    session('info') ? ['type' => 'info', 'text' => session('info')] : null,
                ]);
                initial.filter(Boolean).forEach((t, idx) => {
                    setTimeout(() => this.push(t.type, t.text), idx * 120);
                });
                window.addEventListener('ewarga-toast', (e) => {
                    if (e.detail && e.detail.text) {
                        this.push(e.detail.type || 'info', e.detail.text, e.detail.duration || 5500);
                    }
                });
            },
            push(type, text, duration = 5500) {
                const id = Date.now() + Math.random();
                this.toasts.push({ id, type, text });
                if (duration > 0) {
                    setTimeout(() => this.dismiss(id), duration);
                }
            },
            dismiss(id) {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }
        }"
        aria-live="polite"
        class="fixed top-4 right-4 left-4 sm:left-auto z-[70] flex flex-col gap-2.5 sm:max-w-sm w-auto pointer-events-none"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-transition:enter="transition-transform transition-opacity duration-200 ease-out"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition-opacity duration-150 ease-in"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="pointer-events-auto rounded-2xl px-4 py-3.5 shadow-2xl border backdrop-blur-md flex items-start gap-3 text-xs font-semibold leading-relaxed"
                :class="{
                    'bg-emerald-950/95 border-emerald-500/60 text-emerald-100': toast.type === 'success',
                    'bg-rose-950/95 border-rose-500/60 text-rose-100': toast.type === 'error',
                    'bg-amber-950/95 border-amber-500/60 text-amber-100': toast.type === 'warning',
                    'bg-slate-900/95 border-cyan-500/50 text-slate-100': toast.type === 'info'
                }"
            >
                <span class="text-base shrink-0 leading-none mt-0.5" x-text="toast.type === 'success' ? '✅' : (toast.type === 'error' ? '❌' : (toast.type === 'warning' ? '⚠️' : 'ℹ️'))"></span>
                <p class="flex-1 text-pretty" x-text="toast.text"></p>
                <button
                    type="button"
                    @click="dismiss(toast.id)"
                    class="shrink-0 text-white/70 hover:text-white text-base leading-none font-bold px-1 cursor-pointer focus-visible:ring-2 focus-visible:ring-white rounded"
                    aria-label="Tutup notifikasi"
                >&times;</button>
            </div>
        </template>
    </div>

    <!-- ======================================================== -->
    <!-- GLOBAL CUSTOM CONFIRMATION DIALOG (PENGGANTI CONFIRM NATIVE) -->
    <!-- ======================================================== -->
    <div
        x-data="{
            open: false,
            title: 'Konfirmasi Tindakan',
            message: 'Apakah Anda yakin?',
            confirmText: 'Ya, Lanjutkan',
            cancelText: 'Batal',
            tone: 'danger',
            pendingForm: null,
            handleOpen(detail) {
                this.pendingForm = detail.form || null;
                this.title = detail.title || 'Konfirmasi Tindakan';
                this.message = detail.message || 'Anda yakin ingin melanjutkan tindakan ini?';
                this.confirmText = detail.confirmText || 'Ya, Lanjutkan';
                this.cancelText = detail.cancelText || 'Batal';
                this.tone = detail.tone || 'danger';
                this.open = true;
            },
            confirmNow() {
                const form = this.pendingForm;
                this.open = false;
                this.pendingForm = null;
                if (form) {
                    HTMLFormElement.prototype.submit.call(form);
                }
            }
        }"
        x-show="open"
        x-cloak
        x-on:ewarga-confirm-open.window="handleOpen($event.detail)"
        x-on:keydown.escape.window="open = false"
        class="fixed inset-0 z-[80] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs overscroll-contain transition-opacity"
    >
        <div
            @click.away="open = false"
            class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5"
        >
            <div class="flex items-start gap-3.5">
                <div
                    class="w-11 h-11 rounded-2xl flex items-center justify-center text-xl shrink-0"
                    :class="tone === 'danger' ? 'bg-rose-100 dark:bg-rose-950/80 text-rose-600 dark:text-rose-400' : 'bg-amber-100 dark:bg-amber-950/80 text-amber-600 dark:text-amber-400'"
                >
                    <span x-text="tone === 'danger' ? '🗑️' : '⚠️'"></span>
                </div>
                <div class="space-y-1">
                    <h3 class="text-base font-black text-slate-900 dark:text-white text-balance" x-text="title"></h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-pretty" x-text="message"></p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button
                    type="button"
                    @click="open = false"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 font-bold text-xs transition-colors cursor-pointer touch-press focus-visible:ring-2 focus-visible:ring-slate-400"
                    x-text="cancelText"
                ></button>
                <button
                    type="button"
                    @click="confirmNow()"
                    class="px-5 py-2.5 rounded-xl text-white font-extrabold text-xs shadow-md transition-colors cursor-pointer touch-press focus-visible:ring-2 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900"
                    :class="tone === 'danger' ? 'bg-rose-600 hover:bg-rose-700 focus-visible:ring-rose-500' : 'bg-emerald-600 hover:bg-emerald-700 focus-visible:ring-emerald-500'"
                    x-text="confirmText"
                ></button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- IDLE SESSION COUNTDOWN WARNING BANNER (PENGGUNA AKTIF)   -->
    <!-- ======================================================== -->
    @auth
    <div
        id="ewarga-idle-banner"
        x-data="{
            warningActive: false,
            secondsLeft: 60,
            stayLoggedIn() {
                window.dispatchEvent(new CustomEvent('ewarga-reset-idle'));
            }
        }"
        x-show="warningActive"
        x-cloak
        x-on:ewarga-idle-warning.window="warningActive = true; secondsLeft = $event.detail.secondsLeft"
        x-on:ewarga-idle-cleared.window="warningActive = false"
        class="fixed bottom-20 lg:bottom-5 right-4 left-4 sm:left-auto z-[75] sm:max-w-sm bg-amber-950/95 border border-amber-400/70 text-amber-50 rounded-2xl p-4 shadow-2xl space-y-2.5 backdrop-blur-md"
    >
        <div class="flex items-start gap-2.5">
            <span class="text-lg shrink-0">⏱️</span>
            <div class="text-xs leading-relaxed">
                <div class="font-black text-amber-200">Tidak Ada Aktivitas Terdeteksi</div>
                <p class="mt-0.5 tabular-nums">Akun akan keluar otomatis dalam <strong x-text="secondsLeft"></strong> detik demi keamanan sesi Anda.</p>
            </div>
        </div>
        <div class="flex justify-end gap-2">
            <button
                type="button"
                @click="stayLoggedIn()"
                class="px-3.5 py-1.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs transition-colors cursor-pointer focus-visible:ring-2 focus-visible:ring-amber-300"
            >
                Tetap Masuk
            </button>
        </div>
    </div>
    @endauth

    <script>
        window.triggerComingSoon = function(title, desc) {
            window.dispatchEvent(new CustomEvent('open-coming-soon', { detail: { title: title, desc: desc } }));
        };
        window.ewargaToast = function(type, text, duration = 5500) {
            window.dispatchEvent(new CustomEvent('ewarga-toast', { detail: { type: type, text: text, duration: duration } }));
        };
        window.ewargaConfirmSubmit = function(formEl, options = {}) {
            window.dispatchEvent(new CustomEvent('ewarga-confirm-open', {
                detail: Object.assign({ form: formEl }, options || {})
            }));
        };
    </script>

    @auth
    <script>
        (function () {
            const timeoutMinutes = {{ $idleTimeoutMinutes }};
            const timeoutMs = timeoutMinutes * 60 * 1000;
            const warningMs = Math.min(60000, Math.floor(timeoutMs * 0.25));
            const STORAGE_KEY = 'ewarga_last_activity_at';
            let lastActivity = Date.now();
            let lastWriteAt = lastActivity;
            let warningShown = false;
            let loggedOut = false;

            try {
                localStorage.setItem(STORAGE_KEY, String(lastActivity));
            } catch (e) {}

            function markActive(force) {
                const now = Date.now();
                if (!force && (now - lastWriteAt) < 4000 && !warningShown) {
                    return;
                }
                lastActivity = now;
                lastWriteAt = now;
                if (warningShown) {
                    warningShown = false;
                    window.dispatchEvent(new CustomEvent('ewarga-idle-cleared'));
                }
                try {
                    localStorage.setItem(STORAGE_KEY, String(now));
                } catch (e) {}
            }

            ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'].forEach(function (evt) {
                window.addEventListener(evt, function () { markActive(false); }, { passive: true });
            });

            window.addEventListener('ewarga-reset-idle', function () {
                markActive(true);
            });

            window.addEventListener('storage', function (e) {
                if (e.key === STORAGE_KEY && e.newValue) {
                    const parsed = parseInt(e.newValue, 10);
                    if (!isNaN(parsed) && parsed > lastActivity) {
                        lastActivity = parsed;
                        if (warningShown) {
                            warningShown = false;
                            window.dispatchEvent(new CustomEvent('ewarga-idle-cleared'));
                        }
                    }
                }
            });

            setInterval(function () {
                if (loggedOut) return;
                const elapsed = Date.now() - lastActivity;
                const remainingMs = timeoutMs - elapsed;

                if (remainingMs <= 0) {
                    loggedOut = true;
                    const form = document.getElementById('ewarga-idle-logout-form');
                    if (form) {
                        HTMLFormElement.prototype.submit.call(form);
                    }
                    return;
                }

                if (remainingMs <= warningMs) {
                    warningShown = true;
                    const secondsLeft = Math.max(1, Math.ceil(remainingMs / 1000));
                    window.dispatchEvent(new CustomEvent('ewarga-idle-warning', { detail: { secondsLeft: secondsLeft } }));
                }
            }, 1000);
        })();
    </script>
    @endauth
</body>
</html>
