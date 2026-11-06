@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col justify-between bg-slate-50 dark:bg-slate-950 px-4 sm:px-6 lg:px-8 py-6">

    <!-- Top Navigation Bar Header Halaman Login -->
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
                    class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition shadow-xs cursor-pointer flex items-center justify-center" 
                    :title="isDark ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap'"
                >
                    <svg x-show="isDark" x-cloak class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg x-show="!isDark" x-cloak class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
            </div>

            <a href="{{ route('home') }}" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300 transition shadow-xs flex items-center gap-1.5">
                <span>←</span>
                <span>Kembali ke Beranda</span>
            </a>
        </div>
    </header>

    <!-- Main Login Card (Desktop 2-Column Split View) -->
    <div class="max-w-5xl w-full mx-auto my-auto grid grid-cols-1 lg:grid-cols-12 bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        
        <!-- SISI KIRI: BRANDING & BENEFIT SAAS (5 COLS) -->
        <div class="lg:col-span-5 bg-gradient-to-br from-[#0F172A] via-[#1B365D] to-emerald-950 p-8 sm:p-10 text-white flex flex-col justify-between relative overflow-hidden">
            <!-- Background Ambient Glow -->
            <div class="absolute -top-24 -left-24 w-64 h-64 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-cyan-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="space-y-6 relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-400/20 text-emerald-300 border border-emerald-400/30 text-[10px] font-black uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>ENTERPRISE SMART LIVING</span>
                </div>

                <div>
                    <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight">
                        Transformasi Digital Lingkungan RT/RW & Cluster
                    </h2>
                    <p class="text-xs text-slate-300 mt-3 leading-relaxed">
                        Satu platform terintegrasi untuk Palang Pintu Otomatis IoT, transparansi kas keuangan, posyandu terpadu, dan pelayanan surat warga online.
                    </p>
                </div>

                <!-- 4 Keunggulan Fitur Promosi -->
                <div class="space-y-3.5 pt-2">
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">✓</span>
                        <div class="text-xs leading-snug">
                            <strong class="text-white">Smart Barrier Gate IoT:</strong> Akses otomatis RFID warga & pass tamu QR via WhatsApp.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">✓</span>
                        <div class="text-xs leading-snug">
                            <strong class="text-white">Transparansi Kas RT:</strong> Laporan keuangan dengan grafik Pie & Bar Chart anti-curiga.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">✓</span>
                        <div class="text-xs leading-snug">
                            <strong class="text-white">Posyandu & Stunting:</strong> Kartu Menuju Sehat (KMS) digital dan monitoring demografi.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">✓</span>
                        <div class="text-xs leading-snug">
                            <strong class="text-white">Surat Pengantar RT Online:</strong> Persetujuan 1-klik dengan stempel QR verifikasi resmi.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian Bawah: Ajakan Pasang Baru -->
            <div class="pt-8 border-t border-white/10 relative z-10 mt-6 space-y-2">
                <div class="text-xs text-slate-300">
                    Ingin mendaftarkan perumahan Anda?
                </div>
                <a href="{{ route('home') }}#daftar-komplek" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-bold transition">
                    <span>Ajukan Survei & Pemasangan Palang</span>
                    <span>→</span>
                </a>
            </div>
        </div>

        <!-- SISI KANAN: FORM LOGIN & 1-CLICK DEMO AKUN (7 COLS) -->
        <div class="lg:col-span-7 p-8 sm:p-10 flex flex-col justify-between space-y-6">
            <div>
                <!-- Header Form -->
                <div class="pb-4 border-b border-slate-100 dark:border-slate-800 space-y-1">
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Masuk ke Sistem</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Gunakan akun percontohan 1-klik di bawah atau masukkan email login Anda.</p>
                </div>

                <!-- Notifikasi Error / Sukses -->
                @if(session('success'))
                    <div class="mt-4 p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-medium leading-relaxed">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mt-4 p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-medium leading-relaxed">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Form Login Kredensial -->
                <form action="{{ route('login.post') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label for="login" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Email atau Nomor WhatsApp
                        </label>
                        <input 
                            type="text" 
                            name="login" 
                            id="login" 
                            value="{{ old('login', session('registered_phone', 'superadmin@ewarga.id')) }}" 
                            required 
                            placeholder="Contoh: superadmin@ewarga.id atau 08xxxxxxxxxx"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-hidden transition"
                        >
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                Password
                            </label>
                            <span class="text-[11px] text-slate-400">Password default: <code class="font-bold text-emerald-600 dark:text-emerald-400">admin123</code></span>
                        </div>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            value="admin123"
                            required 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-hidden transition"
                        >
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" value="1" checked class="w-4 h-4 rounded-md text-emerald-600 border-slate-300 dark:border-slate-700 focus:ring-emerald-500">
                            <span class="text-xs text-slate-600 dark:text-slate-400">Ingat sesi saya di komputer ini</span>
                        </label>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full py-3.5 px-6 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-sm shadow-lg shadow-emerald-600/30 transition-all transform hover:-translate-y-0.5 cursor-pointer"
                    >
                        Masuk ke Dashboard E-WARGA ➔
                    </button>
                </form>
            </div>

            <!-- SEKSI 1-KLIK AKUN DEMO KHUSUS PENGUJIAN -->
            <div class="pt-5 border-t border-slate-100 dark:border-slate-800 space-y-3">
                <div class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 flex items-center justify-between">
                    <span>Akses Cepat 1-Klik Akun Percontohan:</span>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">Tanpa Ketik Password</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <!-- Super Admin Platform (Utama) -->
                    <a href="{{ route('quick.login', 'super_admin') }}" class="sm:col-span-2 flex items-center justify-between p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700/80 hover:bg-amber-100/60 transition group">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-amber-500 text-slate-950 flex items-center justify-center font-black text-sm shadow-xs">⚡</span>
                            <div>
                                <div class="text-xs font-black text-slate-900 dark:text-white group-hover:text-amber-600">Fabian (Super Admin Platform)</div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400">Pusat Kendali Seluruh Lingkungan & Multi-Tenant</div>
                            </div>
                        </div>
                        <span class="text-xs font-black text-amber-600 dark:text-amber-400">Masuk Hub ➔</span>
                    </a>

                    <!-- Ketua RT -->
                    <a href="{{ route('quick.login', 'ketua_rt') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-blue-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <span class="text-base">👔</span>
                        <div>
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">Ketua RT</div>
                            <div class="text-[10px] text-slate-400">Bpk. H. Sutrisno</div>
                        </div>
                    </a>

                    <!-- Bendahara RT -->
                    <a href="{{ route('quick.login', 'bendahara') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-emerald-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <span class="text-base">💰</span>
                        <div>
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">Bendahara RT</div>
                            <div class="text-[10px] text-slate-400">Bpk. Joko Wardoyo</div>
                        </div>
                    </a>

                    <!-- Satpam Pos Jaga -->
                    <a href="{{ route('quick.login', 'satpam') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-cyan-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <span class="text-base">🛡️</span>
                        <div>
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">Satpam Pos Jaga</div>
                            <div class="text-[10px] text-slate-400">Pak Slamet</div>
                        </div>
                    </a>

                    <!-- Warga Penghuni -->
                    <a href="{{ route('quick.login', 'warga') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-emerald-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <span class="text-base">🏡</span>
                        <div>
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">Warga Penghuni</div>
                            <div class="text-[10px] text-slate-400">Bpk. Bambang</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- Footer Tipis Halaman Login -->
    <footer class="max-w-5xl w-full mx-auto text-center text-xs text-slate-400 pt-6">
        &copy; {{ date('Y') }} E-WARGA by PT Smart Community Indonesia. Seluruh hak cipta dilindungi.
    </footer>

</div>
@endsection
