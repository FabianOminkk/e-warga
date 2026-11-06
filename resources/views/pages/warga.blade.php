@extends('layouts.app')

@section('content')
<div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto" x-data="wargaApp()">

    <!-- ======================================================== -->
    <!-- 1. HEADER UTAMA PORTAL WARGA (DESKTOP-FIRST HERO BAR)    -->
    <!-- ======================================================== -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 shadow-xs border border-slate-200 dark:border-slate-800 mb-8 transition">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            
            <!-- Info Profil Warga -->
            <div class="flex items-start sm:items-center gap-5">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl {{ $user->canAccessGate() ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border-2 border-emerald-400' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-2 border-dashed border-slate-300 dark:border-slate-700' }} flex items-center justify-center font-black text-2xl sm:text-3xl shrink-0 shadow-inner">
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
                            {{ $user->name ?? 'Bpk. Bambang Pamungkas' }}
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
                        <span class="font-mono text-xs text-slate-400 dark:text-slate-500">
                            NIK: {{ $user->masked_nik }}
                        </span>
                    </p>
                </div>
            </div>

            <!-- Tombol Aksi Pengaturan Akun Terpisah & Bantuan -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a 
                    href="{{ route('warga.profile') }}" 
                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-md shadow-emerald-600/20 transition flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Pengaturan Akun & Data Diri</span>
                </a>

                <button 
                    @click="startTour()" 
                    type="button" 
                    class="px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
                >
                    <span>❓ Panduan</span>
                </button>
            </div>

        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 2. BANNER STATUS VERIFIKASI IDENTITAS & ACC KETUA RW     -->
    <!-- ======================================================== -->
    @if(!$user->canAccessGate())
        <div class="mb-8 rounded-3xl p-6 sm:p-7 border {{ $user->verification_status === 'pending_rw' ? 'bg-amber-500/10 border-amber-500/30' : 'bg-rose-500/10 border-rose-500/30' }}">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                
                <div class="space-y-2">
                    <div class="flex items-center gap-2.5">
                        <span class="text-2xl">{{ $user->verification_status === 'pending_rw' ? '⏳' : '🔒' }}</span>
                        <h2 class="text-base sm:text-lg font-black {{ $user->verification_status === 'pending_rw' ? 'text-amber-800 dark:text-amber-300' : 'text-rose-800 dark:text-rose-300' }}">
                            @if($user->verification_status === 'pending_rw')
                                BERKAS KTP SEDANG DITINJAU OLEH KETUA RW (Bpk. H. Rahmat Hidayat)
                            @else
                                AKSES PORTAL TERKUNCI: WAJIB MELENGKAPI IDENTITAS KTP & DATA DIRI
                            @endif
                        </h2>
                    </div>

                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed max-w-3xl">
                        @if($user->verification_status === 'pending_rw')
                            Data NIK KTP Anda (<strong>{{ $user->nik }}</strong>) telah dikirimkan ke meja kerja Ketua RW. Setelah disetujui Pak RW, data RFID/E-KTP Anda otomatis disinkronkan ke Pos Pantau Satpam dan palang gerbang pintar dapat dibuka seketika.
                        @else
                            Sesuai standar keamanan terpadu lingkungan, seluruh warga baru yang mendaftar <strong>terkunci aksesnya</strong> sampai melengkapi nomor KTP (NIK), foto KTP, dan tanggal lahir. Buka halaman pengaturan akun untuk mengisi identitas lengkap Anda.
                        @endif
                    </p>

                    <!-- 4-Langkah Progres Verifikasi -->
                    <div class="pt-3 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div class="p-3 rounded-xl border {{ $user->nik ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-700 text-emerald-800 dark:text-emerald-300' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-500' }}">
                            <div class="font-extrabold flex items-center gap-1.5">
                                <span>{{ $user->nik ? '✓' : '1.' }}</span>
                                <span>Input KTP & Profil</span>
                            </div>
                            <div class="text-[10px] mt-0.5 opacity-80">{{ $user->nik ? 'Selesai diunggah' : 'Belum diisi' }}</div>
                        </div>

                        <div class="p-3 rounded-xl border {{ $user->verification_status === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-700 text-emerald-800 dark:text-emerald-300' : ($user->verification_status === 'pending_rw' ? 'bg-amber-100 dark:bg-amber-950/60 border-amber-300 dark:border-amber-700 text-amber-900 dark:text-amber-200 font-bold' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-500') }}">
                            <div class="font-extrabold flex items-center gap-1.5">
                                <span>{{ $user->verification_status === 'approved' ? '✓' : '2.' }}</span>
                                <span>Review Ketua RW</span>
                            </div>
                            <div class="text-[10px] mt-0.5 opacity-80">{{ $user->verification_status === 'pending_rw' ? 'Sedang Ditinjau' : ($user->verification_status === 'approved' ? 'Disetujui' : 'Menunggu Berkas') }}</div>
                        </div>

                        <div class="p-3 rounded-xl border {{ $user->canAccessGate() ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-700 text-emerald-800 dark:text-emerald-300' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-500' }}">
                            <div class="font-extrabold flex items-center gap-1.5">
                                <span>{{ $user->canAccessGate() ? '✓' : '3.' }}</span>
                                <span>Sinkron RFID Satpam</span>
                            </div>
                            <div class="text-[10px] mt-0.5 opacity-80">{{ $user->canAccessGate() ? 'Tersinkron' : 'Menunggu ACC RW' }}</div>
                        </div>

                        <div class="p-3 rounded-xl border {{ $user->canAccessGate() ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-700 text-emerald-800 dark:text-emerald-300' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-500' }}">
                            <div class="font-extrabold flex items-center gap-1.5">
                                <span>{{ $user->canAccessGate() ? '✓' : '4.' }}</span>
                                <span>Palang Pintu Aktif</span>
                            </div>
                            <div class="text-[10px] mt-0.5 opacity-80">{{ $user->canAccessGate() ? 'Bebas Akses' : 'Terkunci' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Tombol CTA Selesaikan -->
                <div class="shrink-0">
                    <a 
                        href="{{ route('warga.profile') }}" 
                        class="px-5 py-3 rounded-2xl font-black text-xs shadow-lg transition flex items-center gap-2 {{ $user->verification_status === 'pending_rw' ? 'bg-amber-600 hover:bg-amber-700 text-white shadow-amber-600/30' : 'bg-rose-600 hover:bg-rose-700 text-white shadow-rose-600/30' }}"
                    >
                        <span>{{ $user->verification_status === 'pending_rw' ? 'Periksa / Ubah Berkas KTP' : 'Buka Pengaturan Akun & Lengkapi KTP' }}</span>
                        <span>➔</span>
                    </a>
                </div>

            </div>
        </div>
    @endif

    <!-- ======================================================== -->
    <!-- 3. GRID 12 KOLOM DESKTOP PORTAL WARGA                    -->
    <!-- ======================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- KOLOM UTAMA KIRI (8 KOLOM): GERBANG, MENU UTAMA, RIWAYAT -->
        <div class="lg:col-span-8 space-y-8">

            <!-- TOMBOL RAKSASA KENDALI PALANG GERBANG -->
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
                            class="w-full py-8 sm:py-10 px-6 rounded-3xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-xl sm:text-2xl shadow-xl shadow-emerald-600/30 active:scale-98 transition-all duration-200 flex flex-col items-center justify-center gap-2.5 group border-4 border-emerald-400/40 cursor-pointer"
                        >
                            <span class="text-4xl sm:text-5xl group-hover:scale-110 transition duration-200" x-text="isOpening ? '⏳' : '🟢'"></span>
                            <span x-text="isOpening ? 'PALANG SEDANG TERBUKA...' : 'BUKA GERBANG PORTAL'"></span>
                            <span class="text-xs sm:text-sm font-medium text-emerald-100 tracking-wide font-sans">
                                Sensor RFID E-KTP Aktif • Pencet saat kendaraan tiba di depan palang pos satpam
                            </span>
                        </button>
                    </form>
                @else
                    <div class="py-10 px-6 rounded-3xl bg-slate-100 dark:bg-slate-800/80 border-2 border-dashed border-slate-300 dark:border-slate-700 text-center flex flex-col items-center justify-center gap-3">
                        <span class="text-5xl text-slate-400">🔒</span>
                        <div class="space-y-1">
                            <h3 class="text-lg font-black text-slate-800 dark:text-slate-200">Palang Gerbang Belum Dapat Diakses</h3>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                                @if($user->verification_status === 'pending_rw')
                                    Berkas KTP Anda sedang menunggu persetujuan dan pengesahan dari <strong>Ketua RW</strong>. Begitu di-ACC, tombol hijau pembuka palang akan aktif otomatis.
                                @else
                                    Akun Anda berstatus warga baru. Silakan buka halaman pengaturan akun untuk melengkapi data KTP, foto, dan tanggal lahir.
                                @endif
                            </p>
                        </div>
                        <a 
                            href="{{ route('warga.profile') }}" 
                            class="mt-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition"
                        >
                            📝 Buka Pengaturan Akun & Berkas KTP
                        </a>
                    </div>
                @endif

                <div class="mt-4 flex items-center justify-between text-xs text-slate-400 dark:text-slate-500">
                    <span>Protokol Keamanan: IoT E-WARGA Gate Controller v2.4</span>
                    <span>Durasi Palang Terangkat: 5 Detik Otomatis</span>
                </div>
            </div>

            <!-- KOTAK 4 MENU UTAMA KELAYANAN WARGA -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Card 1: Buat Tiket Izin Tamu / Kurir -->
                <div 
                    id="step-guest-pass" 
                    @click="{{ $user->canAccessGate() ? 'openGuestModal = true' : 'redirectToProfile()' }}" 
                    class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs hover:border-emerald-500 transition cursor-pointer group relative overflow-hidden"
                >
                    @if(!$user->canAccessGate())
                        <div class="absolute top-3 right-3 text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300">
                            TERKUNCI
                        </div>
                    @endif
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition">
                        🎫
                    </div>
                    <div class="font-black text-base text-slate-900 dark:text-white">Buat Izin Masuk Tamu / Kurir</div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        Kirimkan kode QR tiket tamu ke paket kurir, ojol, atau kerabat via WhatsApp.
                    </p>
                    <div class="mt-4 flex items-center text-xs font-bold text-emerald-600 dark:text-emerald-400 gap-1">
                        <span>{{ $user->canAccessGate() ? 'Buat Tiket Tamu' : 'Lengkapi KTP di Pengaturan' }}</span>
                        <span>&rarr;</span>
                    </div>
                </div>

                <!-- Card 2: Status Iuran Kas Lingkungan -->
                <div 
                    id="step-billing" 
                    class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs relative"
                >
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl mb-4">
                        💳
                    </div>
                    <div class="font-black text-base text-slate-900 dark:text-white">Iuran Lingkungan Bulan Ini</div>
                    <div class="mt-2">
                        @if($billing && $billing->status === 'paid')
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 text-xs font-black">
                                <span>✅ LUNAS</span>
                                <span class="font-normal">({{ $billing->formatted_amount }})</span>
                            </div>
                            <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-2">
                                IPL & Kas Terverifikasi oleh Bendahara (Bpk. Joko Wardoyo)
                            </div>
                        @else
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 text-xs font-black">
                                <span>⏳ MENUNGGU PEMBAYARAN</span>
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">
                                Nominal: Rp 150.000 / Bulan (Transfer ke Rekening Kas RT)
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Card 3: Layanan Surat Pengantar RT Online -->
                <a 
                    href="{{ $user->canAccessGate() ? route('letters.index') : route('warga.profile') }}" 
                    class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs hover:border-emerald-500 transition group relative"
                >
                    @if(!$user->canAccessGate())
                        <div class="absolute top-3 right-3 text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300">
                            TERKUNCI
                        </div>
                    @endif
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition">
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
                </a>

                <!-- Card 4: Siap Satpam (Jadwal Keamanan & Ronda) -->
                <a 
                    href="{{ route('schedules.index') }}" 
                    class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs hover:border-emerald-500 transition group"
                >
                    <div class="w-12 h-12 rounded-2xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition">
                        🛡️
                    </div>
                    <div class="font-black text-base text-slate-900 dark:text-white">Siap Satpam</div>
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
                        <p class="text-xs text-slate-500 dark:text-slate-400">Log pembukaan palang pintu oleh akun atau E-KTP rumah Anda</p>
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
                                    <span class="font-mono font-bold text-slate-600 dark:text-slate-300">{{ $glog->triggered_at->format('H:i:s') }} WIB</span>
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
                        <img src="{{ asset($user->ktp_photo) }}" alt="Foto KTP Warga" class="w-full h-32 object-cover">
                    </div>
                @endif

                <div class="space-y-2.5 text-xs">
                    <div>
                        <div class="text-[10px] text-blue-300 font-bold uppercase">Nomor Induk Kependudukan (NIK)</div>
                        <div class="font-mono text-sm font-black tracking-wider text-amber-300">
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
                            <div class="font-semibold text-slate-200 truncate">
                                {{ $user->birth_place ?? '-' }}, {{ $user->birth_date ? $user->birth_date->format('d/m/Y') : '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="pt-1">
                        <div class="text-[10px] text-blue-300 font-bold uppercase">Nomor UID Tag RFID E-KTP</div>
                        <div class="font-mono font-bold text-cyan-300 flex items-center justify-between">
                            <span>{{ $user->rfid_tag ?? 'Belum Ada RFID' }}</span>
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

                <a 
                    href="{{ route('warga.profile') }}" 
                    class="mt-4 block text-center w-full py-2.5 rounded-xl bg-blue-600/60 hover:bg-blue-600 text-white font-bold text-xs transition border border-blue-500/40"
                >
                    ✏️ Buka Pengaturan Akun & Data Diri
                </a>
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
                <form action="{{ route('warga.panic') }}" method="POST" onsubmit="return confirm('APAKAH ANDA YAKIN? Sinyal bahaya darurat akan langsung dibunyikan ke Pos Satpam dan diteruskan ke Pengurus RT!')">
                    @csrf
                    <button 
                        type="submit" 
                        class="w-full py-4 rounded-2xl bg-gradient-to-r from-rose-600 to-red-700 hover:from-rose-500 hover:to-red-600 text-white font-black text-xs shadow-lg shadow-rose-600/30 flex items-center justify-center gap-2 transition active:scale-98 cursor-pointer"
                    >
                        <span>🚨</span>
                        <span>BUNYIKAN SIRINE POS SATPAM</span>
                    </button>
                </form>
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
                        <a href="tel:08110000005" class="font-bold text-emerald-600 dark:text-emerald-400 hover:underline">0811-0000-005 (Pak Slamet)</a>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Ketua RW 02:</span>
                        <a href="https://wa.me/628110000008" target="_blank" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">0811-0000-008 (Pak RW Rahmat)</a>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Ketua RT 04:</span>
                        <a href="https://wa.me/628110000002" target="_blank" class="font-bold text-emerald-600 dark:text-emerald-400 hover:underline">0811-0000-002 (Pak RT Sutrisno)</a>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- ======================================================== -->
    <!-- 4. MODAL BUAT UNDANGAN TAMU / KURIR                      -->
    <!-- ======================================================== -->
    <div x-show="openGuestModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs">
        <div @click.away="openGuestModal = false" class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Buat Izin Masuk Tamu / Kurir</h3>
                <button @click="openGuestModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-bold">&times;</button>
            </div>

            <form action="{{ route('warga.create-visitor') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Nama Tamu / Kurir</label>
                    <input type="text" name="guest_name" placeholder="Misal: Kurir Shopee / Paman Rudi" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:border-emerald-500 outline-hidden">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Kategori</label>
                        <select name="guest_type" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:border-emerald-500 outline-hidden font-semibold">
                            <option value="courier">📦 Kurir Paket</option>
                            <option value="guest">🚗 Tamu Keluarga</option>
                            <option value="ride_hailing">🛵 Ojek Online</option>
                            <option value="contractor">🔨 Tukang Bangunan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Berlaku Selama</label>
                        <select name="duration_hours" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:border-emerald-500 outline-hidden font-semibold">
                            <option value="2">2 Jam</option>
                            <option value="6">6 Jam</option>
                            <option value="24">1 Hari Penuh</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Plat Nomor Kendaraan (Opsional)</label>
                    <input type="text" name="vehicle_plate" placeholder="Misal: B 1234 XYZ" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:border-emerald-500 outline-hidden uppercase">
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="button" @click="openGuestModal = false" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-xs transition">Batal</button>
                    <button type="submit" class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md shadow-emerald-600/30 transition">Buat Tiket QR</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Flash Modal Hasil Tiket Tamu Baru (Kirim ke WhatsApp) -->
    @if(session('new_visitor'))
        @php $v = session('new_visitor'); @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80">
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
                    <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Berlaku sampai: {{ $v->valid_until->format('d/m H:i') }} WIB</div>
                </div>

                @php
                    $waText = urlencode("Halo {$v->guest_name}, ini adalah izin masuk gerbang untuk ke rumah saya di " . ($tenant->name ?? 'Cluster Melati Mas') . " (" . ($v->hostHouse?->block_and_number ?? 'Rumah Warga') . "). Tunjukkan kode ini di Pos Satpam: *{$v->qr_token}*");
                    $waUrl = "https://wa.me/?text={$waText}";
                @endphp

                <a href="{{ $waUrl }}" target="_blank" class="block w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md transition">
                    📲 Bagikan Kode ke WhatsApp Tamu
                </a>

                <a href="{{ route('warga.index') }}" class="block text-xs font-bold text-slate-500 dark:text-slate-400 hover:underline">
                    Tutup
                </a>
            </div>
        </div>
    @endif

    <!-- OVERLAY TUTORIAL INTERAKTIF (3-STEP GUIDED TOUR) -->
    <div x-show="tourStep > 0" x-cloak class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-6">
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-sm w-full p-6 shadow-2xl text-center space-y-4 border border-slate-100 dark:border-slate-800">
            <template x-if="tourStep === 1">
                <div>
                    <div class="w-16 h-16 rounded-2xl bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 text-4xl flex items-center justify-center mx-auto mb-3">🚪</div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-0.5 rounded-full">Langkah 1 dari 3</span>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white mt-2">Tombol Buka Gerbang</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-2 leading-relaxed">
                        Saat mobil/motor Anda tiba di depan palang portal, pencet tombol hijau ini di HP atau tap E-KTP fisik Anda ke sensor tiang RFID.
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
                    <h3 class="text-lg font-black text-slate-900 dark:text-white mt-2">Integrasi E-KTP & Verifikasi RW</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-2 leading-relaxed">
                        Semua warga baru wajib melengkapi identitas KTP di halaman Pengaturan Akun agar di-ACC oleh Ketua RW.
                    </p>
                </div>
            </template>
            <div class="pt-4 flex items-center justify-between gap-3">
                <button type="button" @click="tourStep = 0" class="text-xs font-bold text-slate-400 hover:text-slate-600">Lewati</button>
                <button type="button" @click="tourStep < 3 ? tourStep++ : tourStep = 0" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-md transition">
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
        tourStep: 0,

        startTour() {
            this.tourStep = 1;
        },

        redirectToProfile() {
            window.location.href = "{{ route('warga.profile') }}";
        },

        handleGateClick(e) {
            this.isOpening = true;
            if (navigator.vibrate) {
                navigator.vibrate(200);
            }
        }
    }
}
</script>
@endsection
