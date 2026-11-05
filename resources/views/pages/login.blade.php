@extends('layouts.app')

@section('content')
<div class="min-h-[85vh] flex items-center justify-center py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl w-full grid grid-cols-1 lg:grid-cols-12 bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        
        <!-- ============================================== -->
        <!-- SISI KIRI: PROMOSI, BENEFIT SAAS, & SOCIAL PROOF -->
        <!-- ============================================== -->
        <div class="lg:col-span-5 bg-gradient-to-br from-[#1B365D] via-slate-900 to-emerald-950 p-8 sm:p-10 text-white flex flex-col justify-between relative overflow-hidden">
            <!-- Background Decorative Glow -->
            <div class="absolute -top-24 -left-24 w-64 h-64 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-cyan-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="space-y-6 relative z-10">
                <!-- Brand Header -->
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Logo E-WARGA" class="h-10 w-auto object-contain rounded-xl bg-white p-1">
                    <div>
                        <span class="font-extrabold text-lg tracking-tight block leading-tight">E-WARGA</span>
                        <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest block">Smart Community OS</span>
                    </div>
                </div>

                <!-- Headline Promosi -->
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black leading-snug">
                        Transformasi Digital Lingkungan RT/RW & Cluster Modern
                    </h2>
                    <p class="text-xs text-slate-300 mt-2 leading-relaxed">
                        Platform manajemen warga terpadu yang telah dipercaya lebih dari 120+ komplek perumahan dan rukun warga di Indonesia.
                    </p>
                </div>

                <!-- 4 Keunggulan Fitur Promosi -->
                <div class="space-y-3 pt-2">
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 text-xs font-bold">✓</span>
                        <div class="text-xs">
                            <strong class="text-white">Smart Barrier Gate IoT:</strong> Akses otomatis RFID warga, tiket QR kurir paket, dan tombol buka palang via HP.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 text-xs font-bold">✓</span>
                        <div class="text-xs">
                            <strong class="text-white">Transparansi Kas & Pie Chart:</strong> Pembukuan iuran terbuka, verifikasi struk transfer, dan analisis grafik keuangan.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 text-xs font-bold">✓</span>
                        <div class="text-xs">
                            <strong class="text-white">Posyandu & Deteksi Stunting:</strong> Kartu Menuju Sehat (KMS) digital, pantau balita, lansia, dan imunisasi.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 text-xs font-bold">✓</span>
                        <div class="text-xs">
                            <strong class="text-white">Surat Pengantar RT Online:</strong> Tanda tangan digital Ketua RT resmi dan validasi barcode QR kelurahan.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian Bawah: Ajakan Pasang Komplek Baru -->
            <div class="pt-8 border-t border-white/10 relative z-10 mt-6 space-y-3">
                <div class="text-xs text-slate-300">
                    Belum mendaftarkan komplek perumahan Anda?
                </div>
                <a href="{{ route('home') }}#daftar-komplek" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-bold transition">
                    <span>Ajukan Survei & Pemasangan Palang</span>
                    <span>→</span>
                </a>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- SISI KANAN: FORM LOGIN & 1-CLICK DEMO AKUN     -->
        <!-- ============================================== -->
        <div class="lg:col-span-7 p-8 sm:p-10 flex flex-col justify-between space-y-6">
            <div>
                <!-- Header Form -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white">Masuk ke Sistem</h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Masukkan kredensial Anda atau gunakan tombol uji coba 1-klik di bawah.</p>
                    </div>
                    <a href="{{ route('home') }}" class="text-xs font-bold text-slate-500 hover:text-emerald-600 transition flex items-center gap-1">
                        <span>← Beranda</span>
                    </a>
                </div>

                <!-- Notifikasi Error / Sukses -->
                @if(session('success'))
                    <div class="mt-4 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-medium leading-relaxed">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mt-4 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-medium leading-relaxed">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Form Login Biasa -->
                <form action="{{ route('login.post') }}" method="POST" class="mt-6 space-y-4">
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
                            placeholder="Contoh: 08110000001 atau superadmin@ewarga.id"
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
                            <span class="text-xs text-slate-600 dark:text-slate-400">Ingat sesi saya</span>
                        </label>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full py-3.5 px-6 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-sm shadow-lg shadow-emerald-600/30 transition-all transform hover:-translate-y-0.5 cursor-pointer"
                    >
                        Masuk ke Dashboard E-WARGA
                    </button>
                </form>
            </div>

            <!-- SEKSI 1-KLIK AKUN DEMO KHUSUS PENGUJIAN -->
            <div class="pt-6 border-t border-slate-100 dark:border-slate-800 space-y-3">
                <div class="text-[11px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 flex items-center justify-between">
                    <span>Akses Cepat 1-Klik Peran Demo:</span>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">Tanpa Password</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <!-- Super Admin Platform (Utama) -->
                    <a href="{{ route('quick.login', 'super_admin') }}" class="sm:col-span-2 flex items-center justify-between p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-700 hover:border-emerald-500 hover:bg-emerald-100/70 transition group">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">⚡</span>
                            <div>
                                <div class="text-xs font-black text-emerald-950 dark:text-emerald-200 group-hover:text-emerald-700">Fabian (Super Admin Platform)</div>
                                <div class="text-[10px] text-emerald-700 dark:text-emerald-400">Akses Penuh Seluruh Sistem & Modul</div>
                            </div>
                        </div>
                        <span class="text-xs font-black text-emerald-600 dark:text-emerald-400">Masuk ➔</span>
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
                    <a href="{{ route('quick.login', 'bendahara') }}" class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-amber-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
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
</div>
@endsection

