@extends('layouts.app')

@section('content')
<div class="py-3 sm:py-8 px-2 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-5" x-data="wargaApp()">

    <!-- Komponen Android Mobile Weather & Fast Status Bar (Tampil di Layar HP) -->
    <x-mobile-android-header :tenant="$tenant" :user="$user" />

    <!-- ======================================================== -->
    <!-- ANDROID MOBILE ABOVE-THE-FOLD QUICK ACTION & STATUS DECK -->
    <!-- (Terlihat Langsung di Layar HP Tanpa Perlu Scroll)       -->
    <!-- ======================================================== -->
    <div class="block lg:hidden space-y-3.5">
        
        <!-- 1. Ringkasan Status Iuran Bulan Ini (Above-The-Fold Card) -->
        <div class="p-4 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl {{ ($billing && $billing->status === 'paid') ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-950/80 dark:text-rose-300' }} flex items-center justify-center text-xl shrink-0">
                    💳
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Iuran Lingkungan Bulan Ini</div>
                    <div class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-1.5 mt-0.5">
                        @if($billing && $billing->status === 'paid')
                            <span class="text-emerald-600 dark:text-emerald-400">LUNAS</span>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 tabular-nums">({{ $billing->formatted_amount }})</span>
                        @else
                            <span class="text-rose-600 dark:text-rose-400">BELUM LUNAS</span>
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 tabular-nums">(Rp&nbsp;150.000)</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="shrink-0">
                @if($billing && $billing->status === 'paid')
                    <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-xs font-bold border border-emerald-200 dark:border-emerald-800">
                        ✅ Terverifikasi
                    </span>
                @else
                    <a 
                        href="#step-billing" 
                        class="min-h-[44px] px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition-colors flex items-center gap-1.5 touch-press"
                    >
                        <span>Bayar</span>
                        <span>➔</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- 2. Tombol Cepat Akses Gerbang & Pass QR Tamu -->
        <div class="grid grid-cols-2 gap-2.5">
            @if($user->canAccessGate())
                <form action="{{ route('warga.open-gate') }}" method="POST" class="w-full">
                    @csrf
                    <button 
                        type="submit" 
                        @click="handleGateClick($event)"
                        :disabled="isOpening"
                        class="w-full min-h-[52px] px-4 py-3 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 active:scale-95 text-white font-extrabold text-xs shadow-md shadow-emerald-600/25 transition-transform flex items-center justify-center gap-2 cursor-pointer touch-press"
                    >
                        <span class="text-xl" x-text="isOpening ? '⏳' : '🟢'"></span>
                        <span class="text-left leading-tight">
                            <span class="block font-black" x-text="isOpening ? 'Membuka…' : 'Buka Gerbang'"></span>
                            <span class="text-[10px] font-medium text-emerald-100 opacity-90">Sensor IoT Aktif</span>
                        </span>
                    </button>
                </form>
            @else
                <button 
                    type="button" 
                    @click="showLockedModal('Kontrol Palang Gerbang')" 
                    class="w-full min-h-[52px] px-4 py-3 rounded-2xl bg-amber-50 dark:bg-amber-950/60 border border-amber-300 dark:border-amber-800 text-amber-900 dark:text-amber-200 font-extrabold text-xs flex items-center justify-center gap-2 touch-press cursor-pointer"
                >
                    <span class="text-xl">🔒</span>
                    <span class="text-left leading-tight">
                        <span class="block font-black">Palang Terkunci</span>
                        <span class="text-[10px] font-medium text-amber-700 dark:text-amber-300">Butuh ACC RW</span>
                    </span>
                </button>
            @endif

            <button 
                type="button" 
                @click="{{ $user->canAccessGate() ? 'openGuestModal = true' : 'showLockedModal(\'Pass QR Tamu\')' }}" 
                class="w-full min-h-[52px] px-4 py-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 active:scale-95 text-slate-800 dark:text-slate-200 hover:border-emerald-500 font-extrabold text-xs shadow-xs transition-transform flex items-center justify-center gap-2 cursor-pointer touch-press"
            >
                <span class="text-xl">🎫</span>
                <span class="text-left leading-tight">
                    <span class="block font-black">Pass QR Tamu</span>
                    <span class="text-[10px] font-medium {{ $user->canAccessGate() ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                        {{ $user->canAccessGate() ? 'Tiket Kurir/Ojol' : 'Terkunci' }}
                    </span>
                </span>
            </button>
        </div>

        <!-- 3. Pengumuman Terbaru Lingkungan RT (Above-The-Fold Highlight) -->
        <div class="p-3.5 rounded-2xl bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-800/80 flex items-start gap-3">
            <span class="text-lg shrink-0 mt-0.5">📢</span>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-black text-blue-900 dark:text-blue-200 truncate">Kerja Bakti Saluran Lingkungan</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/80 text-blue-800 dark:text-blue-300 shrink-0">Info RT</span>
                </div>
                <p class="text-xs text-slate-700 dark:text-slate-300 mt-0.5 leading-relaxed">
                    Hari Minggu pukul 07.00 WIB di Balai Warga. Diharapkan bapak-bapak hadir membawa perlengkapan.
                </p>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- 1. HEADER UTAMA PORTAL WARGA (DESKTOP-FIRST HERO BAR)    -->
    <!-- ======================================================== -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 sm:p-8 shadow-xs border border-slate-200 dark:border-slate-800 transition-colors">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            
            <!-- Info Profil Warga -->
            <div class="flex items-start sm:items-center gap-5">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl {{ $user->canAccessGate() ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border-2 border-emerald-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border-2 border-dashed border-amber-400' }} flex items-center justify-center font-black text-2xl sm:text-3xl shrink-0 shadow-inner">
                    @if($user->canAccessGate())
                        🏡
                    @elseif($user->verification_status === 'pending_rw')
                        ⏳
                    @else
                        🔒
                    @endif
                </div>

                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                            {{ $user->name ?? 'Warga Terdaftar' }}
                        </h1>
                        <span class="text-xs px-2.5 py-1 rounded-full font-bold border {{ $user->verification_badge['badge'] }}">
                            {{ $user->verification_badge['icon'] }} {{ $user->verification_badge['label'] }}
                        </span>
                    </div>

                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 flex flex-wrap items-center gap-2">
                        <span>{{ $tenant->name ?? 'Cluster Melati Mas' }}</span>
                        <span>•</span>
                        <span class="font-extrabold text-emerald-600 dark:text-emerald-400">
                            {{ $user?->house?->block_and_number ?? 'Unit Rumah Belum Terdata' }}
                        </span>
                        <span>•</span>
                        <span class="font-mono text-xs text-slate-400 dark:text-slate-500 tabular-nums">
                            NIK: {{ $user->masked_nik }}
                        </span>
                    </p>
                </div>
            </div>

            <!-- Tombol Aksi Pengaturan Akun Terpisah & Pendaftaran KTP -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <button 
                    type="button" 
                    @click="openRfidModal = true" 
                    class="px-4 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-black shadow-md shadow-cyan-600/20 transition-colors flex items-center gap-2 cursor-pointer touch-press"
                >
                    <span>🪪</span>
                    <span>Scan / Daftarkan e-KTP Fisik</span>
                </button>

                <a 
                    href="{{ route('warga.profile') }}" 
                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-md shadow-emerald-600/20 transition-colors flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Pengaturan Akun & Biodata</span>
                </a>

                <button 
                    @click="startTour()" 
                    type="button" 
                    class="px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-colors flex items-center gap-1.5 cursor-pointer"
                >
                    <span>❓ Panduan</span>
                </button>
            </div>

        </div>
    </div>

    <!-- ======================================================== -->
    <!-- DECK NOTIFIKASI WARGA BARU: HAK AKSES TERKUNCI           -->
    <!-- (Ditampilkan jika verification_status !== 'approved')    -->
    <!-- ======================================================== -->
    @if($user->verification_status !== 'approved')
    <div class="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent dark:from-amber-950/40 dark:via-amber-950/20 rounded-3xl p-5 sm:p-7 border border-amber-300 dark:border-amber-800 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 flex items-center justify-center text-2xl shrink-0 border border-amber-300 dark:border-amber-700">
                    🔒
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-amber-500 text-slate-950">
                            Akses Terkunci Sementara
                        </span>
                        <span class="text-xs font-bold text-amber-800 dark:text-amber-300">
                            {{ $user->verification_status === 'pending_rw' ? 'Menunggu Konfirmasi Admin / Pengurus RT/RW' : 'Lengkapi Berkas Identitas KTP' }}
                        </span>
                    </div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">
                        Hak Akses Portal Terkunci: Menunggu Konfirmasi Admin & Melengkapi Biodata
                    </h2>
                    <p class="text-xs text-slate-600 dark:text-slate-300 max-w-3xl leading-relaxed">
                        @if($user->verification_status === 'pending_rw')
                            Halo <strong>{{ $user->name }}</strong>, seluruh modul layanan dan kendali gerbang RFID di bawah ini ditampilkan agar Anda dapat meninjau ekosistem perumahan, namun berada dalam status <strong>Terkunci</strong> sampai berkas kependudukan Anda disetujui resmi oleh Ketua RW (Bpk. H. Rahmat Hidayat).
                        @else
                            Halo <strong>{{ $user->name }}</strong>, akun warga Anda belum melengkapi identitas KTP & tanggal lahir. Seluruh fitur gerbang dan administrasi di bawah ini berstatus <strong>Terkunci</strong> sampai Anda melengkapi biodata di menu Pengaturan Akun.
                        @endif
                    </p>
                </div>
            </div>

            <!-- Tombol Aksi Cepat Buka Kunci -->
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <a 
                    href="{{ route('warga.profile') }}" 
                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md transition-colors flex items-center gap-1.5 touch-press"
                >
                    <span>📝</span>
                    <span>{{ $user->verification_status === 'pending_rw' ? 'Periksa Biodata KTP' : 'Lengkapi Biodata KTP' }}</span>
                </a>

                <a 
                    href="https://wa.me/628110000008?text={{ urlencode('Halo Pengurus RT/RW ' . ($tenant->name ?? 'E-WARGA') . ', saya warga baru atas nama ' . $user->name . ($user->house ? ' (' . $user->house->block_and_number . ')' : '') . '. Mohon bantuan verifikasi dan konfirmasi akun saya di portal E-WARGA agar hak akses gerbang aktif. Terima kasih.') }}" 
                    target="_blank" 
                    rel="noopener noreferrer"
                    class="px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-200 font-bold text-xs transition-colors flex items-center gap-1.5 touch-press"
                >
                    <span>💬 WhatsApp Pengurus</span>
                </a>

                <button 
                    type="button" 
                    onclick="window.location.reload()" 
                    class="px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 text-xs font-bold transition-colors cursor-pointer"
                    title="Perbarui Status"
                >
                    <span>🔄 Refresh</span>
                </button>
            </div>
        </div>

        <!-- 4-Step Checklist Progress Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-2 text-xs">
            <div class="p-3 rounded-2xl bg-white/80 dark:bg-slate-900/80 border border-emerald-300 dark:border-emerald-700 text-emerald-800 dark:text-emerald-300">
                <div class="font-extrabold flex items-center gap-1">
                    <span>✓</span>
                    <span>1. Registrasi Akun</span>
                </div>
                <div class="text-[10px] mt-0.5 opacity-80">Akun Terdaftar</div>
            </div>

            <div class="p-3 rounded-2xl bg-white/80 dark:bg-slate-900/80 border {{ $user->nik && $user->ktp_photo ? 'border-emerald-300 dark:border-emerald-700 text-emerald-800 dark:text-emerald-300' : 'border-amber-300 dark:border-amber-700 text-amber-800 dark:text-amber-300' }}">
                <div class="font-extrabold flex items-center gap-1">
                    <span>{{ $user->nik && $user->ktp_photo ? '✓' : '⚠️' }}</span>
                    <span>2. Biodata & KTP</span>
                </div>
                <div class="text-[10px] mt-0.5 opacity-80">{{ $user->nik && $user->ktp_photo ? 'Berkas Lengkap' : 'Wajib Dilengkapi' }}</div>
            </div>

            <div class="p-3 rounded-2xl bg-white/80 dark:bg-slate-900/80 border {{ $user->rfid_tag ? 'border-emerald-300 dark:border-emerald-700 text-emerald-800 dark:text-emerald-300' : 'border-cyan-300 dark:border-cyan-700 text-cyan-800 dark:text-cyan-300' }}">
                <div class="font-extrabold flex items-center gap-1">
                    <span>{{ $user->rfid_tag ? '✓' : '🪪' }}</span>
                    <span>3. Sensor e-KTP RFID</span>
                </div>
                <div class="text-[10px] mt-0.5 opacity-80 truncate">{{ $user->rfid_tag ? $user->rfid_tag : 'Belum Dipasangkan' }}</div>
            </div>

            <div class="p-3 rounded-2xl bg-white/80 dark:bg-slate-900/80 border {{ $user->verification_status === 'pending_rw' ? 'border-amber-300 dark:border-amber-700 text-amber-800 dark:text-amber-300 font-bold' : 'border-slate-200 dark:border-slate-800 text-slate-500' }}">
                <div class="font-extrabold flex items-center gap-1">
                    <span>{{ $user->verification_status === 'pending_rw' ? '⏳' : '🔒' }}</span>
                    <span>4. Persetujuan RW</span>
                </div>
                <div class="text-[10px] mt-0.5 opacity-80">{{ $user->verification_status === 'pending_rw' ? 'Sedang Ditinjau' : 'Menunggu Berkas' }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- ======================================================== -->
    <!-- 2. GRID 12 KOLOM UTAMA PORTAL WARGA                      -->
    <!-- (Tampil Utuh untuk Semua Warga, Terkunci Jika Belum ACC) -->
    <!-- ======================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- KOLOM UTAMA KIRI (8 KOLOM): GERBANG, RFID, MENU, RIWAYAT -->
        <div class="lg:col-span-8 space-y-8">

            <!-- TOMBOL KENDALI PALANG GERBANG -->
            <div id="step-open-gate" class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full {{ $user->canAccessGate() ? 'bg-emerald-500 animate-ping' : 'bg-rose-500' }}"></span>
                        <h2 class="font-black text-slate-900 dark:text-white text-base uppercase tracking-wider">
                            {{ $user->canAccessGate() ? 'KONTROL PALANG GERBANG MASUK' : 'STATUS PALANG GERBANG: TERKUNCI' }}
                        </h2>
                    </div>
                    <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg {{ $user->canAccessGate() ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400' }}">
                        {{ $smartGate->status ?? 'ONLINE' }} • {{ $smartGate->name ?? 'Gerbang Utama' }}
                    </span>
                </div>

                @if($user->canAccessGate())
                    <form action="{{ route('warga.open-gate') }}" method="POST">
                        @csrf
                        <button 
                            type="submit" 
                            @click="handleGateClick($event)"
                            :disabled="isOpening"
                            class="w-full py-8 sm:py-10 px-6 rounded-3xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-xl sm:text-2xl shadow-xl shadow-emerald-600/30 active:scale-98 transition-transform duration-200 flex flex-col items-center justify-center gap-2.5 group border-4 border-emerald-400/40 cursor-pointer"
                        >
                            <span class="text-4xl sm:text-5xl group-hover:scale-110 transition duration-200" x-text="isOpening ? '⏳' : '🟢'"></span>
                            <span x-text="isOpening ? 'PALANG SEDANG TERBUKA…' : 'BUKA GERBANG PORTAL'"></span>
                            <span class="text-xs sm:text-sm font-medium text-emerald-100 tracking-wide font-sans">
                                Sensor RFID E-KTP Aktif • Tekan saat kendaraan tiba di depan palang pos satpam
                            </span>
                        </button>
                    </form>
                @else
                    <div class="py-8 sm:py-10 px-6 rounded-3xl bg-slate-100 dark:bg-slate-800/80 border-2 border-dashed border-amber-300 dark:border-amber-700 text-center flex flex-col items-center justify-center gap-3 relative overflow-hidden">
                        <span class="text-5xl text-amber-500">🔒</span>
                        <div class="space-y-1">
                            <h3 class="text-lg font-black text-slate-800 dark:text-slate-200">
                                Palang Gerbang Masih Terkunci
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto leading-relaxed">
                                @if($user->verification_status === 'pending_rw')
                                    Berkas KTP Anda sedang menunggu persetujuan resmi dari <strong>Ketua RW</strong>. Tombol hijau pembuka palang akan aktif otomatis begitu akun Anda diverifikasi.
                                @else
                                    Akun Anda berstatus warga baru. Silakan lengkapi biodata KTP & foto di menu Pengaturan Akun agar dapat diajukan ke Ketua RW.
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center justify-center gap-2.5 mt-2">
                            <button 
                                type="button" 
                                @click="showLockedModal('Kontrol Palang Gerbang')" 
                                class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-md transition-colors cursor-pointer touch-press"
                            >
                                🔒 Informasi Hak Akses & Persyaratan
                            </button>
                            <a 
                                href="{{ route('warga.profile') }}" 
                                class="px-5 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs shadow-xs transition-colors"
                            >
                                📝 Pengaturan Akun & Berkas KTP
                            </a>
                        </div>
                    </div>
                @endif

                <div class="mt-4 flex items-center justify-between text-xs text-slate-400 dark:text-slate-500">
                    <span>Protokol Keamanan: IoT E-WARGA Gate Controller v2.4</span>
                    <span class="tabular-nums">Durasi Palang Terangkat: 5 Detik Otomatis</span>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- CARD UTAMA: REGISTRASI & SENSOR E-KTP FISIK (RFID / NFC) -->
            <!-- ======================================================== -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-7 border border-slate-200 dark:border-slate-800 shadow-xs relative overflow-hidden space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xl">🪪</span>
                            <h2 class="font-black text-slate-900 dark:text-white text-base uppercase tracking-wider">
                                Registrasi & Sensor e-KTP Fisik (RFID & NFC)
                            </h2>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Tautkan nomor UID chip e-KTP atau kartu RFID 13.56&nbsp;MHz Anda ke akun warga ini untuk membuka palang portal perumahan secara otomatis.
                        </p>
                    </div>

                    <div class="shrink-0">
                        <span class="text-xs font-mono font-bold px-3 py-1.5 rounded-xl {{ $user->rfid_tag ? 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                            {{ $user->rfid_tag ? 'Terhubung: ' . $user->rfid_tag : 'Belum Ada RFID' }}
                        </span>
                    </div>
                </div>

                <!-- Status Banner RFID Saat Ini -->
                <div class="p-4 rounded-2xl {{ $user->rfid_tag ? 'bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800' }} flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl {{ $user->rfid_tag ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300' }} flex items-center justify-center text-xl shrink-0">
                            {{ $user->rfid_tag ? '📡' : '⚠️' }}
                        </div>
                        <div>
                            <div class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <span>{{ $user->rfid_tag ? 'UID e-KTP Terdaftar di Akun' : 'Fisik e-KTP Belum Dipasangkan' }}</span>
                                @if($user->rfid_tag)
                                    <span class="font-mono text-xs px-2 py-0.5 rounded-md bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                                        {{ $user->rfid_tag }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                @if($user->rfid_tag)
                                    Status Otorisasi Gerbang: 
                                    <strong class="{{ $user->canAccessGate() ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                        {{ $user->canAccessGate() ? 'Aktif Terbuka di Palang Portal' : 'Menunggu Persetujuan Ketua RW' }}
                                    </strong>
                                @else
                                    Daftarkan nomor UID fisik e-KTP Anda melalui Web NFC di HP Android, scanner ESP32 gerbang, atau input manual.
                                @endif
                            </div>
                        </div>
                    </div>

                    <button 
                        type="button" 
                        @click="openRfidModal = true" 
                        class="min-h-[44px] px-4 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs shadow-md transition-colors flex items-center justify-center gap-2 shrink-0 cursor-pointer touch-press"
                    >
                        <span>{{ $user->rfid_tag ? '🔄 Ganti / Scan Ulang e-KTP' : '🔴 Mulai Scan e-KTP Sekarang' }}</span>
                    </button>
                </div>

                <!-- 3 Panduan Cara Scanning e-KTP -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 space-y-1">
                        <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                            <span>📱</span>
                            <span>1. Web NFC (HP Android)</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                            Buka browser Chrome di Android ber-NFC, tekan scan, dan tempelkan fisik e-KTP ke belakang bodi ponsel.
                        </p>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 space-y-1">
                        <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                            <span>📡</span>
                            <span>2. Scanner ESP32 Gerbang</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                            Aktifkan sesi pairing 60 detik di aplikasi, lalu tempelkan e-KTP Anda ke alat sensor RFID di pos satpam / gerbang.
                        </p>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 space-y-1">
                        <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                            <span>⌨️</span>
                            <span>3. Masukkan UID Manual</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                            Ketikkan 8–14 digit heksadesimal UID e-KTP Anda langsung jika menggunakan pembaca kartu USB desktop.
                        </p>
                    </div>
                </div>
            </div>

            <!-- KOTAK 4 MENU UTAMA KELAYANAN WARGA -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Card 1: Buat Tiket Izin Tamu / Kurir -->
                <div 
                    id="step-guest-pass" 
                    @click="{{ $user->canAccessGate() ? 'openGuestModal = true' : 'showLockedModal(\'Izin Masuk Tamu / Kurir\')' }}" 
                    class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs hover:border-emerald-500 transition-colors cursor-pointer group relative overflow-hidden"
                >
                    @if(!$user->canAccessGate())
                        <div class="absolute top-3 right-3 text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                            🔒 TERKUNCI (BUTUH ACC RW)
                        </div>
                    @endif
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform">
                        🎫
                    </div>
                    <div class="font-black text-base text-slate-900 dark:text-white">Buat Izin Masuk Tamu / Kurir</div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        Kirimkan kode QR tiket tamu ke paket kurir, ojol, atau kerabat via WhatsApp.
                    </p>
                    <div class="mt-4 flex items-center text-xs font-bold text-emerald-600 dark:text-emerald-400 gap-1">
                        <span>{{ $user->canAccessGate() ? 'Buat Tiket Tamu' : 'Lengkapi Biodata & ACC RW' }}</span>
                        <span>&rarr;</span>
                    </div>
                </div>

                <!-- Card 2: Status Iuran Kas Lingkungan -->
                <div 
                    id="step-billing" 
                    class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs relative flex flex-col justify-between"
                >
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl mb-4">
                            💳
                        </div>
                        <div class="font-black text-base text-slate-900 dark:text-white">Iuran Lingkungan Bulan Ini</div>
                        <div class="mt-2">
                            @if($billing && $billing->status === 'paid')
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 text-xs font-black">
                                    <span>✅ LUNAS</span>
                                    <span class="font-normal tabular-nums">({{ $billing->formatted_amount }})</span>
                                </div>
                                <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-2">
                                    IPL & Kas Terverifikasi oleh Bendahara RT
                                </div>
                            @else
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 text-xs font-black">
                                    <span>⏳ MENUNGGU PEMBAYARAN</span>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">
                                    Nominal: <strong class="tabular-nums">Rp&nbsp;150.000 / Bulan</strong> (Setor ke Rekening Kas RT)
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
                        @if($billing && $billing->status === 'paid')
                            <button 
                                type="button" 
                                @click="triggerComingSoon('E-Kwitansi & Tanda Terima Kas Resmi', 'Fitur unduh e-Kwitansi PDF berstempel QR digital resmi dari Bendahara lingkungan sedang disiapkan. Bukti pembayaran Anda telah tercatat aman di sistem.')" 
                                class="w-full px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
                            >
                                <span>📄 Unduh E-Kwitansi</span>
                                <span class="text-[10px] text-slate-400 font-extrabold">(Coming Soon)</span>
                            </button>
                        @else
                            <button 
                                type="button" 
                                @click="triggerComingSoon('Pembayaran Online via QRIS & Virtual Account', 'Fitur pembayaran iuran otomatis melalui QRIS statis/dinamis dan Virtual Account Bank BCA/BRI/Mandiri sedang dalam integrasi gateway resmi. Untuk saat ini silakan setor tunai ke Bendahara atau transfer bank manual.')" 
                                class="w-full px-3 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 dark:bg-blue-950 dark:hover:bg-blue-900 text-blue-700 dark:text-blue-300 text-xs font-bold transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
                            >
                                <span>💳 Bayar via QRIS</span>
                                <span class="text-[10px] text-amber-600 dark:text-amber-400 font-black">(Coming Soon)</span>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Card 3: Layanan Surat Pengantar RT Online -->
                <div 
                    @click="{{ $user->canAccessGate() ? 'window.location.href=\''.route('letters.index').'\'' : 'showLockedModal(\'Layanan Surat Pengantar RT/RW\')' }}" 
                    class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs hover:border-emerald-500 transition-colors group relative cursor-pointer"
                >
                    @if(!$user->canAccessGate())
                        <div class="absolute top-3 right-3 text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                            🔒 TERKUNCI
                        </div>
                    @endif
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform">
                        📜
                    </div>
                    <div class="font-black text-base text-slate-900 dark:text-white">Layanan Surat Pengantar RT/RW</div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        Permohonan surat domisili, SKCK, dan pengantar kelurahan ber-QR code sah.
                    </p>
                    <div class="mt-4 flex items-center text-xs font-bold text-indigo-600 dark:text-indigo-400 gap-1">
                        <span>{{ $user->canAccessGate() ? 'Ajukan Surat Online' : 'Lengkapi KTP Terlebih Dahulu' }}</span>
                        <span>&rarr;</span>
                    </div>
                </div>

                <!-- Card 4: Siap Satpam (Jadwal Keamanan & Ronda) -->
                <a 
                    href="{{ route('schedules.index') }}" 
                    class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs hover:border-emerald-500 transition-colors group"
                >
                    <div class="w-12 h-12 rounded-2xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform">
                        🛡️
                    </div>
                    <div class="font-black text-base text-slate-900 dark:text-white">Siap Satpam & Ronda</div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        Pantau jadwal siaga satpam pos jaga dan regu ronda siskamling malam ini.
                    </p>
                    <div class="mt-4 flex items-center text-xs font-bold text-cyan-600 dark:text-cyan-400 gap-1">
                        <span>Cek Siap Satpam</span>
                        <span>&rarr;</span>
                    </div>
                </a>

            </div>

            <!-- RIWAYAT AKSES PALANG PINTU TERBARU UNTUK RUMAH INI -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-7 border border-slate-200 dark:border-slate-800 shadow-xs">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="font-black text-slate-900 dark:text-white text-base">Aktivitas Gerbang Warga Terkini</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Log pembukaan palang pintu oleh akun atau e-KTP rumah Anda</p>
                    </div>
                    <span class="text-xs font-mono text-slate-400">Real-Time IoT Log</span>
                </div>

                @if($myGateLogs->isEmpty())
                    <div class="text-center py-8 text-slate-400 dark:text-slate-500 text-xs">
                        Belum ada riwayat pembukaan pintu untuk akun ini.
                    </div>
                @else
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($myGateLogs as $glog)
                            <div class="py-3 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-3">
                                    <span class="p-2 rounded-xl {{ $glog->status === 'granted' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300' }}">
                                        {{ $glog->status === 'granted' ? '🚪' : '⛔' }}
                                    </span>
                                    <div>
                                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ $glog->reason }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono">Metode: {{ $glog->access_method }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="font-mono font-bold text-slate-600 dark:text-slate-300 tabular-nums">{{ $glog->triggered_at->format('H:i:s') }} WIB</span>
                                    <div class="text-[10px] text-slate-400">{{ $glog->triggered_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        <!-- KOLOM KANAN (4 KOLOM): E-KTP CARD, DARURAT, PENGUMUMAN -->
        <div class="lg:col-span-4 space-y-8">

            <!-- KARTU E-KTP DIGITAL RESMI (VISUALISASI INDONESIA E-ID) -->
            <div class="bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 rounded-3xl p-6 text-white shadow-xl border border-blue-800/40 relative overflow-hidden">
                <div class="flex items-center justify-between pb-3 border-b border-blue-800/60 mb-4">
                    <div class="text-left">
                        <div class="text-[10px] uppercase font-black tracking-widest text-blue-300">REPUBLIK INDONESIA</div>
                        <div class="text-xs font-bold text-slate-200">KARTU TANDA PENDUDUK DIGITAL</div>
                    </div>
                    <span class="text-lg">🇮🇩</span>
                </div>

                @if($user->ktp_photo)
                    <div class="mb-4 rounded-xl overflow-hidden border border-blue-700/50 bg-slate-950">
                        <img src="{{ $user->ktp_url }}" alt="Foto KTP Warga" class="w-full h-32 object-cover">
                    </div>
                @endif

                <div class="space-y-2.5 text-xs">
                    <div>
                        <div class="text-[10px] text-blue-300 font-bold uppercase">Nomor Induk Kependudukan (NIK)</div>
                        <div class="font-mono text-sm font-black tracking-wider text-amber-300 tabular-nums">
                            {{ $user->nik ?? 'BELUM DILENGKAPI' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <div>
                            <div class="text-[10px] text-blue-300 font-bold uppercase">Nama Lengkap</div>
                            <div class="font-bold truncate text-slate-100">{{ $user->name }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] text-blue-300 font-bold uppercase">Tempat/Tgl Lahir</div>
                            <div class="font-semibold text-slate-200 truncate tabular-nums">
                                {{ $user->birth_place ?? '-' }}, {{ $user->birth_date ? $user->birth_date->format('d/m/Y') : '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="pt-1">
                        <div class="text-[10px] text-blue-300 font-bold uppercase">Nomor UID Tag RFID E-KTP</div>
                        <div class="font-mono font-bold text-cyan-300 flex items-center justify-between">
                            <span class="truncate">{{ $user->rfid_tag ?? 'Belum Ada RFID' }}</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full {{ $user->canAccessGate() ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-rose-500/20 text-rose-300 border border-rose-500/40' }}">
                                {{ $user->canAccessGate() ? 'AKTIF DI PALANG' : 'NON-AKTIF' }}
                            </span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-blue-800/60 flex items-center justify-between text-[11px]">
                        <div>
                            <span class="text-slate-400 block text-[9px] uppercase">Otorisasi Legalitas:</span>
                            <strong class="{{ $user->canAccessGate() ? 'text-emerald-400' : 'text-amber-400' }}">
                                {{ $user->verification_badge['label'] }}
                            </strong>
                        </div>
                        @if($user->verifiedBy)
                            <div class="text-right">
                                <span class="text-slate-400 block text-[9px]">Oleh:</span>
                                <span class="text-[10px] text-slate-200 font-semibold">{{ $user->verifiedBy->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-blue-800/60 flex items-center gap-2">
                    <button 
                        type="button" 
                        @click="openRfidModal = true" 
                        class="flex-1 py-2.5 rounded-xl bg-cyan-600/80 hover:bg-cyan-600 text-white font-bold text-xs transition-colors border border-cyan-500/40 cursor-pointer touch-press text-center"
                    >
                        🪪 Scan Fisik e-KTP
                    </button>
                    <a 
                        href="{{ route('warga.profile') }}" 
                        class="flex-1 py-2.5 rounded-xl bg-blue-600/60 hover:bg-blue-600 text-white font-bold text-xs transition-colors border border-blue-500/40 text-center"
                    >
                        ✏️ Edit Data KTP
                    </a>
                </div>
            </div>

            <!-- TOMBOL DARURAT (PANIC BUTTON) -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-xs">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-xl">🚨</span>
                    <h3 class="font-black text-rose-600 dark:text-rose-400 text-sm uppercase tracking-wider">Tombol Darurat (Panic)</h3>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
                    Gunakan hanya pada situasi darurat (kebakaran, pencurian, medis). Sinyal akan langsung membunyikan sirine di Pos Satpam.
                </p>
                @if($user->canAccessGate() || $user->isPengurus())
                    <form action="{{ route('warga.panic') }}" method="POST" onsubmit="return confirm('APAKAH ANDA YAKIN? Sinyal bahaya darurat akan langsung dibunyikan ke Pos Satpam dan diteruskan ke Pengurus RT!')">
                        @csrf
                        <button 
                            type="submit" 
                            class="w-full py-4 rounded-2xl bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-500 hover:to-red-600 text-white font-black text-xs shadow-lg shadow-rose-600/30 flex items-center justify-center gap-2 transition-transform active:scale-98 cursor-pointer touch-press"
                        >
                            <span>🚨</span>
                            <span>BUNYIKAN SIRINE POS SATPAM</span>
                        </button>
                    </form>
                @else
                    <button 
                        type="button" 
                        @click="showLockedModal('Tombol Darurat (Panic Button)')" 
                        class="w-full py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 font-bold text-xs flex items-center justify-center gap-2 cursor-pointer touch-press"
                    >
                        <span>🔒</span>
                        <span>Sirine Terkunci (Butuh ACC KTP)</span>
                    </button>
                @endif
            </div>

            <!-- PENGUMUMAN TERBARU PENGURUS RT -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-xs">
                <div class="flex items-center gap-2 text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-3">
                    <span>📢 PENGUMUMAN LINGKUNGAN</span>
                </div>
                <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">Kerja Bakti Saluran Lingkungan</h4>
                <p class="text-xs text-slate-600 dark:text-slate-300 mt-1.5 leading-relaxed">
                    Hari Minggu pukul 07.00 WIB. Diharapkan bapak-bapak membawa cangkul & sapu lidi. Sarapan kopi disediakan kas RT.
                </p>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Oleh: Ketua RT 04 (Bpk. H. Sutrisno)</span>
                    <span>Warga RT</span>
                </div>
            </div>

            <!-- KONTAK DARURAT PENGURUS LINGKUNGAN -->
            <div class="p-5 rounded-3xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-800 text-xs space-y-3">
                <div class="font-extrabold text-slate-800 dark:text-slate-200">Kontak Cepat Pengurus:</div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Pos Satpam Utama:</span>
                        <a href="tel:08110000005" class="font-bold text-emerald-600 dark:text-emerald-400 hover:underline tabular-nums">0811-0000-005 (Pak Slamet)</a>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Ketua RW 02:</span>
                        <a href="https://wa.me/628110000008" target="_blank" class="font-bold text-blue-600 dark:text-blue-400 hover:underline tabular-nums">0811-0000-008 (Pak RW Rahmat)</a>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Ketua RT 04:</span>
                        <a href="https://wa.me/628110000002" target="_blank" class="font-bold text-emerald-600 dark:text-emerald-400 hover:underline tabular-nums">0811-0000-002 (Pak RT Sutrisno)</a>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- ======================================================== -->
    <!-- MODAL 1: HAK AKSES TERKUNCI (INTERAKTIF & JELAS)         -->
    <!-- ======================================================== -->
    <div x-show="openLockedInfoModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs overscroll-contain">
        <div @click.away="openLockedInfoModal = false" class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl space-y-5 border border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 flex items-center justify-center text-lg">
                        🔒
                    </div>
                    <h3 class="font-black text-slate-900 dark:text-white text-base">
                        Hak Akses Terkunci
                    </h3>
                </div>
                <button @click="openLockedInfoModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-bold text-xl cursor-pointer">&times;</button>
            </div>

            <div class="space-y-3 text-xs leading-relaxed text-slate-600 dark:text-slate-300">
                <p>
                    Fitur <strong class="text-slate-900 dark:text-white" x-text="lockedFeatureName"></strong> saat ini masih dalam status <strong class="text-amber-600 dark:text-amber-400">Terkunci</strong>.
                </p>
                <p>
                    Sesuai protokol keamanan lingkungan tertutup (*gated community*), seluruh hak akses pembuka gerbang, pembuatan izin tamu, dan administrasi surat resmi memerlukan verifikasi identitas kependudukan dan persetujuan dari Pengurus RT/RW.
                </p>

                <!-- Checklist Syarat Pembukaan Akses -->
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 space-y-2.5">
                    <div class="font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider text-[11px]">
                        Tahapan Aktivasi Akun Warga:
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-[10px] font-bold">✓</span>
                            <span>Pendaftaran Akun (Selesai)</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="w-5 h-5 rounded-full {{ $user->nik && $user->ktp_photo ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300' : 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300' }} flex items-center justify-center text-[10px] font-bold">
                                {{ $user->nik && $user->ktp_photo ? '✓' : '⚠️' }}
                            </span>
                            <span>Lengkapi NIK KTP, Foto, & Tanggal Lahir ({{ $user->nik && $user->ktp_photo ? 'Lengkap' : 'Belum Lengkap' }})</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="w-5 h-5 rounded-full {{ $user->rfid_tag ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300' : 'bg-cyan-100 dark:bg-cyan-950 text-cyan-700 dark:text-cyan-300' }} flex items-center justify-center text-[10px] font-bold">
                                {{ $user->rfid_tag ? '✓' : '🪪' }}
                            </span>
                            <span>Daftarkan UID Fisik e-KTP ({{ $user->rfid_tag ? 'Terdaftar' : 'Belum Dipasangkan' }})</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="w-5 h-5 rounded-full {{ $user->verification_status === 'pending_rw' ? 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }} flex items-center justify-center text-[10px] font-bold">
                                {{ $user->verification_status === 'pending_rw' ? '⏳' : '🔒' }}
                            </span>
                            <span>Konfirmasi & Persetujuan Ketua RW ({{ $user->verification_status === 'pending_rw' ? 'Sedang Ditinjau' : 'Menunggu Berkas' }})</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row items-center gap-2.5">
                <a 
                    href="{{ route('warga.profile') }}" 
                    class="w-full sm:flex-1 min-h-[48px] px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md transition-colors flex items-center justify-center gap-2 touch-press"
                >
                    <span>📝 Lengkapi Biodata KTP Sekarang</span>
                </a>

                <button 
                    type="button" 
                    @click="openLockedInfoModal = false; openRfidModal = true;" 
                    class="w-full sm:flex-1 min-h-[48px] px-4 py-3 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-extrabold text-xs shadow-md transition-colors flex items-center justify-center gap-2 cursor-pointer touch-press"
                >
                    <span>🪪 Daftarkan Fisik e-KTP</span>
                </button>
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <a 
                    href="https://wa.me/628110000008?text={{ urlencode('Halo Pengurus RT/RW ' . ($tenant->name ?? 'E-WARGA') . ', saya ' . $user->name . '. Mohon bantuan verifikasi akun saya.') }}" 
                    target="_blank" 
                    class="font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1"
                >
                    <span>💬 Hubungi Pengurus via WhatsApp</span>
                </a>
                <button 
                    type="button" 
                    @click="openLockedInfoModal = false" 
                    class="font-bold text-slate-400 hover:text-slate-600 cursor-pointer"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 2: PAIRING & SCAN e-KTP FISIK (NFC & ESP32)        -->
    <!-- ======================================================== -->
    <div x-show="openRfidModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs overscroll-contain">
        <div @click.away="openRfidModal = false" class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl space-y-5 border border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-cyan-100 dark:bg-cyan-950 text-cyan-700 dark:text-cyan-300 flex items-center justify-center text-xl">
                        🪪
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 dark:text-white text-base">
                            Daftarkan e-KTP Fisik Anda
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Integrasi Sensor RFID 13.56&nbsp;MHz Gerbang</p>
                    </div>
                </div>
                <button @click="openRfidModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-bold text-xl cursor-pointer">&times;</button>
            </div>

            <!-- Tab Switcher -->
            <div class="grid grid-cols-3 gap-1.5 p-1 rounded-2xl bg-slate-100 dark:bg-slate-800 text-xs font-bold">
                <button 
                    type="button" 
                    @click="rfidTab = 'nfc'" 
                    class="py-2.5 rounded-xl transition-colors cursor-pointer text-center"
                    :class="rfidTab === 'nfc' ? 'bg-white dark:bg-slate-900 text-cyan-600 dark:text-cyan-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                >
                    📱 Web NFC (HP)
                </button>
                <button 
                    type="button" 
                    @click="rfidTab = 'esp32'; checkEsp32DeviceStatus();" 
                    class="py-2.5 rounded-xl transition-colors cursor-pointer text-center flex items-center justify-center gap-1.5"
                    :class="rfidTab === 'esp32' ? 'bg-white dark:bg-slate-900 text-cyan-600 dark:text-cyan-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                >
                    <span>📡 Sensor ESP32</span>
                    <span class="w-2 h-2 rounded-full shrink-0" :class="esp32Online ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
                </button>
                <button 
                    type="button" 
                    @click="rfidTab = 'manual'" 
                    class="py-2.5 rounded-xl transition-colors cursor-pointer text-center"
                    :class="rfidTab === 'manual' ? 'bg-white dark:bg-slate-900 text-cyan-600 dark:text-cyan-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                >
                    ⌨️ Input UID
                </button>
            </div>

            <!-- TAB 1: WEB NFC PADA HP ANDROID -->
            <div x-show="rfidTab === 'nfc'" class="space-y-4 text-center">
                <div class="py-6 px-4 rounded-3xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 space-y-3">
                    <div class="w-16 h-16 rounded-2xl bg-cyan-100 text-cyan-700 dark:bg-cyan-950 dark:text-cyan-300 flex items-center justify-center text-3xl mx-auto" :class="nfcScanning ? 'animate-pulse' : ''">
                        <span x-text="nfcSuccess ? '✅' : '📶'"></span>
                    </div>

                    <div>
                        <h4 class="font-black text-slate-900 dark:text-white text-sm">
                            Scan Fisik e-KTP Lewat Belakang Ponsel
                        </h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto leading-relaxed">
                            Buka portal ini di Google Chrome pada smartphone Android yang memiliki fitur NFC, lalu tempelkan kartu e-KTP Anda ke bodi belakang ponsel.
                        </p>
                    </div>

                    <!-- Indikator Feedback Status Scan -->
                    <div x-show="nfcMessage" class="p-3 rounded-xl text-xs font-semibold" :class="nfcSuccess ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-cyan-100 text-cyan-800 dark:bg-cyan-950 dark:text-cyan-300'">
                        <span x-text="nfcMessage"></span>
                    </div>
                </div>

                <button 
                    type="button" 
                    @click="startNfcScan()" 
                    :disabled="nfcScanning"
                    class="w-full min-h-[50px] py-3.5 rounded-2xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 active:scale-98 text-white font-black text-xs shadow-lg shadow-cyan-600/30 transition-transform flex items-center justify-center gap-2 cursor-pointer touch-press"
                >
                    <span x-show="nfcScanning" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-text="nfcScanning ? 'Membaca Chip e-KTP… Tempelkan Sekarang' : '🔴 Mulai Scan NFC e-KTP Sekarang'"></span>
                </button>
            </div>

            <!-- TAB 2: PAIRING HARDWARE ESP32 DI GERBANG / POS SATPAM -->
            <div x-show="rfidTab === 'esp32'" class="space-y-4 text-center">
                <!-- Status Koneksi Hardware ESP32 Scanner (Online / Offline) -->
                <div class="p-3.5 rounded-2xl border text-left transition-colors space-y-1.5"
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
                            <span>Cek Status</span>
                        </button>
                    </div>
                    <p class="text-[11px] opacity-90 leading-relaxed" x-text="esp32Online 
                        ? 'Alat ESP32 sudah terhubung ke hotspot Anda (&quot;BIAN&quot;). Sensor siap menerima tap kartu e-KTP fisik.' 
                        : 'Alat scanner belum terhubung ke hotspot. Pastikan hotspot HP (&quot;BIAN&quot;) aktif dan modul ESP32 sudah menyala.'">
                    </p>
                </div>

                <div class="py-6 px-4 rounded-3xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 space-y-3">
                    <div class="w-16 h-16 rounded-2xl bg-cyan-100 text-cyan-700 dark:bg-cyan-950 dark:text-cyan-300 flex items-center justify-center text-3xl mx-auto" :class="esp32PairingActive ? 'animate-bounce' : ''">
                        <span x-text="esp32Success ? '✅' : '📡'"></span>
                    </div>

                    <div>
                        <h4 class="font-black text-slate-900 dark:text-white text-sm">
                            Pairing Kartu Langsung di Scanner Gerbang ESP32
                        </h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto leading-relaxed">
                            Tekan tombol pairing di bawah, lalu tempelkan e-KTP fisik Anda ke sensor RFID RC522 pada tiang palang gerbang atau meja pos satpam.
                        </p>
                    </div>

                    <!-- Countdown Timer -->
                    <div x-show="esp32PairingActive" class="space-y-1">
                        <div class="text-2xl font-black font-mono text-cyan-600 dark:text-cyan-400 tabular-nums" x-text="esp32Countdown + 's'"></div>
                        <div class="text-[11px] text-slate-400">Menunggu kartu e-KTP Anda ditempelkan ke tiang sensor…</div>
                    </div>

                    <div x-show="esp32Message" class="p-3 rounded-xl text-xs font-semibold" :class="esp32Success ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : (esp32Online ? 'bg-cyan-100 text-cyan-800 dark:bg-cyan-950 dark:text-cyan-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300')">
                        <span x-text="esp32Message"></span>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    <button 
                        type="button" 
                        x-show="!esp32PairingActive"
                        @click="startEsp32Pairing()" 
                        class="flex-1 min-h-[50px] py-3.5 rounded-2xl font-black text-xs shadow-lg transition-transform active:scale-98 flex items-center justify-center gap-2 cursor-pointer touch-press text-white"
                        :class="esp32Online 
                            ? 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 shadow-emerald-600/30' 
                            : 'bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 shadow-cyan-600/30'"
                    >
                        <span class="w-2 h-2 rounded-full shrink-0" :class="esp32Online ? 'bg-white animate-pulse' : 'bg-rose-400'"></span>
                        <span x-text="esp32Online ? '📡 Tap di Scanner ESP32 (Online)' : '📡 Mulai Sesi Pairing (ESP32 Offline)'"></span>
                    </button>

                    <button 
                        type="button" 
                        x-show="esp32PairingActive"
                        @click="stopEsp32Pairing('Sesi pairing dibatalkan.')" 
                        class="flex-1 min-h-[50px] py-3.5 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white font-black text-xs shadow-lg transition-transform active:scale-98 flex items-center justify-center gap-2 cursor-pointer touch-press"
                    >
                        <span>Batalkan Sesi Pairing</span>
                    </button>
                </div>
            </div>

            <!-- TAB 3: INPUT NOMOR UID MANUAL -->
            <div x-show="rfidTab === 'manual'" class="space-y-4">
                <form @submit.prevent="submitManualUid()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                            Nomor UID Tag Kartu e-KTP / RFID
                        </label>
                        <input 
                            type="text" 
                            x-model="manualUid"
                            placeholder="Contoh: 04:A2:3B:8C atau 4A5B6C7D" 
                            required
                            spellcheck="false"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-base focus-visible:ring-2 focus-visible:ring-emerald-500 font-mono tracking-widest uppercase font-bold"
                        >
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                            Format heksadesimal otomatis dinormalisasi tanpa spasi atau tanda titik dua.
                        </p>
                    </div>

                    <div x-show="manualError" class="p-3 rounded-xl bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 text-xs font-semibold">
                        <span x-text="manualError"></span>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="isSavingManualUid"
                        class="w-full min-h-[50px] py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-lg shadow-emerald-600/30 transition-transform active:scale-98 flex items-center justify-center gap-2 cursor-pointer touch-press"
                    >
                        <span x-show="isSavingManualUid" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span x-text="isSavingManualUid ? 'Menyimpan UID…' : 'Simpan & Hubungkan ke Akun Ini'"></span>
                    </button>
                </form>
            </div>

            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-center">
                <button 
                    type="button" 
                    @click="openRfidModal = false" 
                    class="text-xs font-bold text-slate-500 dark:text-slate-400 hover:underline cursor-pointer"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 3: BUAT UNDANGAN TAMU / KURIR                      -->
    <!-- ======================================================== -->
    <div x-show="openGuestModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs overscroll-contain">
        <div @click.away="openGuestModal = false" class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Buat Izin Masuk Tamu / Kurir</h3>
                <button @click="openGuestModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-bold">&times;</button>
            </div>

            <form action="{{ route('warga.create-visitor') }}" method="POST" class="space-y-4" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">Nama Tamu / Kurir</label>
                    <input type="text" name="guest_name" inputmode="text" placeholder="Misal: Kurir Shopee / Paman Rudi" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-base focus-visible:ring-2 focus-visible:ring-emerald-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">Kategori</label>
                        <select name="guest_type" class="w-full px-3 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-base focus-visible:ring-2 focus-visible:ring-emerald-500 font-semibold">
                            <option value="courier">📦 Kurir Paket</option>
                            <option value="guest">🚗 Tamu Keluarga</option>
                            <option value="ride_hailing">🛵 Ojek Online</option>
                            <option value="contractor">🔨 Tukang Bangunan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">Berlaku Selama</label>
                        <select name="duration_hours" class="w-full px-3 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-base focus-visible:ring-2 focus-visible:ring-emerald-500 font-semibold">
                            <option value="2">2 Jam</option>
                            <option value="6">6 Jam</option>
                            <option value="24">1 Hari Penuh</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">Plat Nomor Kendaraan (Opsional)</label>
                    <input type="text" name="vehicle_plate" inputmode="text" autocapitalize="characters" placeholder="Misal: B 1234 XYZ" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-base focus-visible:ring-2 focus-visible:ring-emerald-500 uppercase font-mono">
                </div>

                <div class="pt-2 flex items-center gap-3">
                    <button type="button" @click="openGuestModal = false" :disabled="isSubmitting" class="flex-1 min-h-[48px] py-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-xs transition-colors cursor-pointer touch-press">Batal</button>
                    <button type="submit" :disabled="isSubmitting" class="flex-1 min-h-[48px] py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md shadow-emerald-600/30 transition-colors flex items-center justify-center gap-2 cursor-pointer touch-press">
                        <span x-show="isSubmitting" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span x-text="isSubmitting ? 'Membuat Tiket…' : 'Buat Tiket QR'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Flash Modal Hasil Tiket Tamu Baru (Kirim ke WhatsApp) -->
    @if(session('new_visitor'))
        @php $v = session('new_visitor'); @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 overscroll-contain">
            <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-sm w-full p-6 text-center space-y-4 shadow-2xl border border-slate-200 dark:border-slate-800">
                <div class="w-14 h-14 rounded-2xl bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 text-3xl flex items-center justify-center mx-auto">
                    ✅
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">Tiket Tamu Berhasil Dibuat!</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Berikan kode QR ini kepada tamu/kurir untuk ditunjukkan ke Pos Satpam:</p>
                </div>

                <!-- QR Token Card -->
                <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 font-mono text-center">
                    <div class="text-xs text-slate-400 dark:text-slate-500">KODE TIKET GERBANG:</div>
                    <div class="text-2xl font-black text-[#1B365D] dark:text-cyan-400 tracking-widest mt-1">{{ $v->qr_token }}</div>
                    <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 tabular-nums">Berlaku sampai: {{ $v->valid_until->format('d/m H:i') }} WIB</div>
                </div>

                @php
                    $waText = urlencode("Halo {$v->guest_name}, ini adalah izin masuk gerbang untuk ke rumah saya di " . ($tenant->name ?? 'Cluster Melati Mas') . " (" . ($v->hostHouse?->block_and_number ?? 'Rumah Warga') . "). Tunjukkan kode ini di Pos Satpam: *{$v->qr_token}*");
                    $waUrl = "https://wa.me/?text={$waText}";
                @endphp

                <a href="{{ $waUrl }}" target="_blank" class="block w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md transition-colors">
                    📲 Bagikan Kode ke WhatsApp Tamu
                </a>

                <a href="{{ route('warga.index') }}" class="block text-xs font-bold text-slate-500 dark:text-slate-400 hover:underline">
                    Tutup
                </a>
            </div>
        </div>
    @endif

    <!-- OVERLAY TUTORIAL INTERAKTIF (3-STEP GUIDED TOUR) -->
    <div x-show="tourStep > 0" x-cloak class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-6 overscroll-contain">
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-sm w-full p-6 shadow-2xl text-center space-y-4 border border-slate-100 dark:border-slate-800">
            <template x-if="tourStep === 1">
                <div>
                    <div class="w-16 h-16 rounded-2xl bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 text-4xl flex items-center justify-center mx-auto mb-3">🚪</div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-0.5 rounded-full">Langkah 1 dari 3</span>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white mt-2">Tombol Buka Gerbang</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-2 leading-relaxed">
                        Saat mobil/motor Anda tiba di depan palang portal, tekan tombol hijau ini di HP atau tempelkan e-KTP fisik Anda ke sensor tiang RFID.
                    </p>
                </div>
            </template>
            <template x-if="tourStep === 2">
                <div>
                    <div class="w-16 h-16 rounded-2xl bg-purple-100 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400 text-4xl flex items-center justify-center mx-auto mb-3">🎫</div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/60 px-2.5 py-0.5 rounded-full">Langkah 2 dari 3</span>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white mt-2">Izin Masuk Tamu / Kurir</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-2 leading-relaxed">
                        Ada paket belanja online atau tamu keluarga? Buatkan tiket QR instan dan kirimkan langsung ke WhatsApp mereka dari menu ini.
                    </p>
                </div>
            </template>
            <template x-if="tourStep === 3">
                <div>
                    <div class="w-16 h-16 rounded-2xl bg-blue-100 dark:bg-blue-950/70 text-blue-600 dark:text-blue-400 text-4xl flex items-center justify-center mx-auto mb-3">🪪</div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/60 px-2.5 py-0.5 rounded-full">Langkah 3 dari 3</span>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white mt-2">Integrasi e-KTP & Verifikasi RW</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-2 leading-relaxed">
                        Seluruh warga baru wajib melengkapi identitas KTP di halaman Pengaturan Akun agar di-ACC oleh Ketua RW.
                    </p>
                </div>
            </template>
            <div class="pt-4 flex items-center justify-between gap-3">
                <button type="button" @click="tourStep = 0" class="text-xs font-bold text-slate-400 hover:text-slate-600 cursor-pointer">Lewati</button>
                <button type="button" @click="tourStep < 3 ? tourStep++ : tourStep = 0" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-md transition-colors cursor-pointer touch-press">
                    <span x-text="tourStep < 3 ? 'Lanjut ➔' : 'Siap, Saya Paham!'"></span>
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function wargaApp() {
    return {
        isOpening: false,
        openGuestModal: false,
        openLockedInfoModal: false,
        lockedFeatureName: '',
        openRfidModal: false,
        rfidTab: 'nfc',
        tourStep: 0,
        
        // Web NFC State
        nfcScanning: false,
        nfcMessage: '',
        nfcSuccess: false,
        
        // ESP32 Pairing & Connection State
        esp32Online: false,
        isCheckingStatus: false,
        esp32StatusHeartbeatInterval: null,
        esp32PairingActive: false,
        esp32Countdown: 60,
        esp32TimerInterval: null,
        esp32StatusInterval: null,
        esp32Message: '',
        esp32Success: false,

        // Manual UID State
        manualUid: '',
        isSavingManualUid: false,
        manualError: '',

        init() {
            this.checkEsp32DeviceStatus();
            this.esp32StatusHeartbeatInterval = setInterval(() => {
                this.checkEsp32DeviceStatus();
            }, 4000);
        },

        showLockedModal(featureName) {
            this.lockedFeatureName = featureName;
            this.openLockedInfoModal = true;
        },

        triggerComingSoon(featureTitle, featureDesc) {
            alert(featureTitle + '\n\n' + featureDesc);
        },

        startTour() {
            this.tourStep = 1;
        },

        handleGateClick(e) {
            this.isOpening = true;
            if (navigator.vibrate) {
                navigator.vibrate(200);
            }
        },

        // Web NFC Scan (Android Chrome)
        async startNfcScan() {
            if (!('NDEFReader' in window)) {
                this.nfcMessage = 'Perangkat / browser Anda belum mendukung Web NFC. Gunakan Google Chrome di HP Android atau gunakan tab "Sensor ESP32" / "Input UID" di atas.';
                return;
            }
            try {
                this.nfcScanning = true;
                this.nfcMessage = 'Siap! Tempelkan fisik e-KTP Anda ke bodi belakang ponsel sekarang…';
                const ndef = new NDEFReader();
                await ndef.scan();
                
                ndef.addEventListener("reading", async ({ serialNumber }) => {
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                    this.nfcMessage = 'Chip e-KTP terdeteksi! Memproses pendaftaran UID: ' + serialNumber;
                    await this.submitRfidTag(serialNumber, 'web_nfc');
                });

                ndef.addEventListener("readingerror", () => {
                    this.nfcMessage = 'Gagal membaca e-KTP. Pastikan kartu menempel erat pada antena NFC ponsel Anda.';
                });
            } catch (err) {
                this.nfcScanning = false;
                this.nfcMessage = 'Izin NFC ditolak atau tidak tersedia: ' + err.message;
            }
        },

        // Cek Status Koneksi Online / Offline ESP32 ke Hotspot BIAN
        async checkEsp32DeviceStatus(manual = false) {
            if (manual) this.isCheckingStatus = true;
            try {
                const res = await fetch("{{ route('warga.esp32-status') }}");
                const data = await res.json();
                this.esp32Online = !!data.online;
                if (this.esp32PairingActive && !this.esp32Success) {
                    if (this.esp32Online) {
                        this.esp32Message = 'Sensor siap! Tempelkan fisik e-KTP Anda ke scanner RFID di pos satpam / gerbang sekarang…';
                    } else {
                        this.esp32Message = 'Scanner Offline: Alat ESP32 belum terhubung ke hotspot "BIAN". Menunggu alat online…';
                    }
                }
            } catch (e) {
                this.esp32Online = false;
            } finally {
                if (manual) setTimeout(() => { this.isCheckingStatus = false; }, 400);
            }
        },

        // ESP32 Hardware Pairing Mode
        async startEsp32Pairing() {
            this.esp32PairingActive = true;
            this.esp32Countdown = 60;
            this.esp32Message = 'Menghubungkan ke alat scanner ESP32…';
            this.esp32Success = false;
            await this.checkEsp32DeviceStatus();

            try {
                const res = await fetch("{{ route('warga.esp32-pair.start') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await res.json();
                if (!data.success) {
                    this.esp32Message = 'Gagal memulai mode pairing: ' + data.message;
                    this.esp32PairingActive = false;
                    return;
                }

                this.esp32Message = this.esp32Online
                    ? 'Sensor siap! Tempelkan fisik e-KTP Anda ke scanner RFID di pos satpam / gerbang sekarang…'
                    : 'Peringatan: Scanner ESP32 sedang Offline (Belum terhubung ke hotspot "BIAN").';

                // Start countdown
                if (this.esp32TimerInterval) clearInterval(this.esp32TimerInterval);
                this.esp32TimerInterval = setInterval(() => {
                    this.esp32Countdown--;
                    if (this.esp32Countdown <= 0) {
                        this.stopEsp32Pairing('Waktu pairing habis (60 detik). Silakan coba lagi.');
                    }
                }, 1000);

                // Start polling status
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
                const res = await fetch("{{ route('warga.esp32-pair.status') }}");
                const data = await res.json();

                if (data.is_online !== undefined) {
                    this.esp32Online = !!data.is_online;
                }

                if (data.paired) {
                    this.stopEsp32Pairing();
                    this.esp32Success = true;
                    this.esp32Message = data.message;
                    if (navigator.vibrate) navigator.vibrate([100, 100, 200]);
                    setTimeout(() => {
                        window.location.reload();
                    }, 2200);
                }
            } catch (e) {}
        },

        stopEsp32Pairing(msg = null) {
            if (this.esp32TimerInterval) clearInterval(this.esp32TimerInterval);
            if (this.esp32StatusInterval) clearInterval(this.esp32StatusInterval);
            this.esp32PairingActive = false;
            if (msg) this.esp32Message = msg;
        },

        // Manual UID Submission
        async submitManualUid() {
            if (!this.manualUid || this.manualUid.trim().length < 4) {
                this.manualError = 'Masukkan nomor UID e-KTP minimal 4 karakter.';
                return;
            }
            this.isSavingManualUid = true;
            this.manualError = '';
            await this.submitRfidTag(this.manualUid, 'manual_input');
            this.isSavingManualUid = false;
        },

        // Shared Submit Method
        async submitRfidTag(uid, method = 'web_nfc') {
            try {
                const res = await fetch("{{ route('warga.pair-rfid') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        rfid_uid: uid,
                        scan_method: method
                    })
                });

                const data = await res.json();
                if (data.success) {
                    this.nfcScanning = false;
                    this.nfcSuccess = true;
                    this.nfcMessage = 'Berhasil! ' + data.message;
                    setTimeout(() => {
                        window.location.reload();
                    }, 1800);
                } else {
                    this.nfcScanning = false;
                    this.nfcMessage = 'Gagal mendaftar: ' + data.message;
                    this.manualError = data.message;
                }
            } catch (err) {
                this.nfcScanning = false;
                this.nfcMessage = 'Terjadi kesalahan sistem: ' + err.message;
                this.manualError = err.message;
            }
        }
    }
}
</script>
@endsection
