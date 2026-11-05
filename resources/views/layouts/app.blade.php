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
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased min-h-screen flex flex-col transition-colors duration-200">

    <!-- Top Navigation Bar -->
    <header class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-50 shadow-xs transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Brand Logo -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                        <img src="{{ asset('images/logo.jpg') }}" alt="Logo E-WARGA" class="h-10 w-auto object-contain">
                    </a>
                </div>

                <!-- Navigation Links -->
                <nav class="hidden lg:flex items-center gap-5 text-sm font-semibold text-slate-600 dark:text-slate-300">
                    <a href="{{ route('home') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition {{ request()->routeIs('home') ? 'text-emerald-600 dark:text-emerald-400 font-bold' : '' }}">Beranda</a>
                    <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition {{ request()->routeIs('dashboard') ? 'text-emerald-600 dark:text-emerald-400 font-bold' : '' }}">Dashboard RT</a>
                    <a href="{{ route('satpam.console') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition {{ request()->routeIs('satpam.console') ? 'text-emerald-600 dark:text-emerald-400 font-bold' : '' }}">Pos Satpam</a>
                    <a href="{{ route('schedules.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition {{ request()->routeIs('schedules.*') ? 'text-emerald-600 dark:text-emerald-400 font-bold' : '' }}">Jadwal & Ronda</a>
                    <a href="{{ route('posyandu.index') }}" class="hover:text-pink-600 dark:hover:text-pink-400 transition {{ request()->routeIs('posyandu.*') ? 'text-pink-600 dark:text-pink-400 font-bold' : '' }}">Posyandu Digital</a>
                    <a href="{{ route('letters.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition {{ request()->routeIs('letters.*') ? 'text-blue-600 dark:text-blue-400 font-bold' : '' }}">Surat Pengantar</a>
                    <a href="{{ route('simulator') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition flex items-center gap-1.5 {{ request()->routeIs('simulator') ? 'text-emerald-600 dark:text-emerald-400 font-bold' : '' }}">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Simulator
                    </a>
                    <a href="{{ route('warga.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition {{ request()->routeIs('warga.index') ? 'text-emerald-600 dark:text-emerald-400 font-bold' : '' }}">Akses Warga</a>
                </nav>

                <!-- Role Switcher, Dark Mode Toggle & User Profile -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Dark / Light Theme Toggle Button -->
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
                            class="p-2 sm:px-3 sm:py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition shadow-xs flex items-center gap-1.5 text-xs font-bold" 
                            :title="isDark ? 'Ganti ke Mode Terang (Light Mode)' : 'Ganti ke Mode Gelap (Dark Mode)'"
                            aria-label="Toggle Dark / Light Theme"
                        >
                            <!-- Sun icon (shown in dark mode) -->
                            <svg x-show="isDark" x-cloak class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <!-- Moon icon (shown in light mode) -->
                            <svg x-show="!isDark" x-cloak class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                            <span class="hidden sm:inline" x-text="isDark ? 'Dark' : 'Light'"></span>
                        </button>
                    </div>

                    @auth
                        <div class="hidden sm:flex flex-col text-right">
                            <span class="text-xs font-bold text-slate-900 dark:text-white">{{ Auth::user()->name }}</span>
                            <span class="text-[11px] font-medium text-emerald-700 bg-emerald-50 dark:bg-emerald-950/60 dark:text-emerald-300 px-2 py-0.5 rounded-full inline-block">{{ Auth::user()->role_label }}</span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs font-semibold px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg transition">Keluar</button>
                        </form>
                    @else
                        <!-- Quick Role Switcher Dropdown (Dev Mode) -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" type="button" class="inline-flex items-center gap-2 bg-[#1B365D] hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white text-xs font-bold px-3 py-2 rounded-xl shadow-xs transition">
                                <span>Switch Role</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-900 rounded-xl shadow-xl border border-slate-100 dark:border-slate-800 py-2 z-50">
                                <div class="px-3 py-1.5 text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Pilih Role Pengujian:</div>
                                <a href="{{ route('quick.login', 'ketua_rt') }}" class="block px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-emerald-600 dark:hover:text-emerald-400">👔 Ketua RT (Approver)</a>
                                <a href="{{ route('quick.login', 'bendahara') }}" class="block px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-emerald-600 dark:hover:text-emerald-400">💰 Bendahara (Kas & Iuran)</a>
                                <a href="{{ route('quick.login', 'satpam') }}" class="block px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-emerald-600 dark:hover:text-emerald-400">🛡️ Satpam (Pos Jaga & QR)</a>
                                <a href="{{ route('quick.login', 'warga') }}" class="block px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-emerald-600 dark:hover:text-emerald-400">🏡 Warga (Buka Gerbang HP)</a>
                                <a href="{{ route('quick.login', 'super_admin') }}" class="block px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-emerald-600 dark:hover:text-emerald-400">⚡ Super Admin (Platform)</a>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>
            </div>
        </div>
    </header>

    <!-- Flash Notifications -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3 text-sm font-medium shadow-xs">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex items-center gap-3 text-sm font-medium shadow-xs">
                <svg class="w-5 h-5 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(session('panic_sent'))
            <div class="bg-red-600 text-white px-5 py-4 rounded-xl flex items-center gap-4 text-base font-bold shadow-lg animate-pulse">
                <span class="text-2xl">🚨</span>
                <div>
                    <div>PERINGATAN DARURAT TERKIRIM</div>
                    <div class="text-xs font-normal text-red-100">{{ session('panic_sent') }}</div>
                </div>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-8 mt-12 text-center text-xs text-slate-500 dark:text-slate-400 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Logo E-WARGA" class="h-6 w-auto object-contain">
                    <span class="font-bold text-slate-700 dark:text-slate-300">E-WARGA</span> - Smart Community & IoT Living Ecosystem
                </div>
                <div class="text-slate-400 dark:text-slate-500">
                    &copy; {{ date('Y') }} E-WARGA Indonesia. All rights reserved.
                </div>
            </div>
        </div>
    </footer>

</body>
</html>

