@extends('layouts.app')

@section('content')
<div class="space-y-24 py-6 sm:py-12">

    <!-- ======================================================== -->
    <!-- 1. HERO SECTION (DESKTOP ENTERPRISE BRANDING)             -->
    <!-- ======================================================== -->
    <section id="hero" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Left Hero Content -->
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-black tracking-wide">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>PLATFORM SMART COMMUNITY & IOT LIVING #1 DI INDONESIA</span>
                </div>
                
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 dark:text-white tracking-tight leading-[1.12]">
                    Modernisasi Lingkungan RT/RW, <span class="bg-gradient-to-r from-emerald-600 to-teal-500 bg-clip-text text-transparent">Cluster Perumahan</span> & Desa Mandiri.
                </h1>

                <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                    Tinggalkan pembukuan kas manual dan portal bambu. <strong>E-WARGA</strong> menyediakan ekosistem terpadu: integrasi <em>Palang Pintu Otomatis (Smart Gate)</em>, buku kas terbuka anti-curiga berbasis grafik, jadwal ronda siskamling & satpam, posyandu balita & lansia, serta surat pengantar online berstempel QR resmi.
                </p>

                <!-- Action CTA Buttons -->
                <div class="flex flex-wrap items-center gap-4 pt-2">
                    <a href="#kerjasama" class="px-8 py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-2xl shadow-xl shadow-emerald-600/30 text-sm transition-all transform hover:-translate-y-0.5 flex items-center gap-3">
                        <span>Konsultasi & Kemitraan Survei</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    
                    @auth
                        @if(Auth::user()->role === 'super_admin')
                            <a href="{{ route('superadmin.index') }}" class="px-7 py-4 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black rounded-2xl shadow-lg shadow-amber-500/20 text-sm transition flex items-center gap-2">
                                <span>⚡ Buka Pusat Super Admin</span>
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="px-7 py-4 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 font-extrabold rounded-2xl border border-slate-300 dark:border-slate-700 text-sm transition shadow-xs flex items-center gap-2">
                                <span>Buka Dashboard Aktif</span>
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="px-7 py-4 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 font-extrabold rounded-2xl border border-slate-300 dark:border-slate-700 text-sm transition shadow-xs flex items-center gap-2">
                            <span>Masuk ke Akun Anda</span>
                        </a>
                    @endauth
                </div>

                <!-- Live Metrics Summary -->
                <div class="grid grid-cols-3 gap-6 pt-6 border-t border-slate-200 dark:border-slate-800">
                    <div>
                        <div class="text-2xl sm:text-3xl font-black text-[#1B365D] dark:text-cyan-400">{{ $totalHouses }} Unit</div>
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">Rumah Warga Terdata</div>
                    </div>
                    <div>
                        <div class="text-2xl sm:text-3xl font-black text-[#1B365D] dark:text-cyan-400">{{ $totalUsers }} Jiwa</div>
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">Warga Terdaftar Aktif</div>
                    </div>
                    <div>
                        <div class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400">Rp {{ number_format($totalCash, 0, ',', '.') }}</div>
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">Saldo Kas Terbuka Live</div>
                    </div>
                </div>
            </div>

            <!-- Right Hero Card: 1-Click Interactive Role Experience -->
            <div class="lg:col-span-5">
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 space-y-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">TEST DRIVE LANGSUNG</span>
                            <h3 class="font-black text-slate-900 dark:text-white text-base">Uji Coba Pengalaman Tiap Peran</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Pilih peran untuk langsung mengeksplorasi hak akses:</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300">
                            1-KLIK
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        <!-- Super Admin Platform (Kepala / Pusat) -->
                        <a href="{{ route('quick.login', 'super_admin') }}" class="flex items-center justify-between p-3.5 rounded-2xl border-2 border-amber-300/80 dark:border-amber-700/80 bg-amber-50/40 dark:bg-amber-950/20 hover:bg-amber-100/60 dark:hover:bg-amber-950/40 transition group">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-amber-200 dark:bg-amber-900/60 text-amber-900 dark:text-amber-200 flex items-center justify-center font-black text-lg">⚡</span>
                                <div>
                                    <div class="text-sm font-black text-slate-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400">Super Admin (Pusat / Kepala Platform)</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Dashboard Pusat, pilih kelola 1 Cluster & 1 Desa demo</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-amber-600">&rarr;</span>
                        </a>

                        <!-- Ketua RW (Rukun Warga - Jabatan Pembina Wilayah / Di Atas RT) -->
                        <a href="{{ route('quick.login', 'ketua_rw') }}" class="flex items-center justify-between p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-indigo-500 hover:bg-indigo-50/50 dark:hover:bg-slate-800 transition group">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-950/70 text-indigo-800 dark:text-indigo-300 flex items-center justify-center font-bold text-lg">🏛️</span>
                                <div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Ketua RW (Bpk. H. Rahmat Hidayat)</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Supervisi seluruh RT, rekap agregat lingkungan, & rekomendasi RW</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-slate-400 group-hover:text-indigo-600">&rarr;</span>
                        </a>

                        <!-- Ketua RT -->
                        <a href="{{ route('quick.login', 'ketua_rt') }}" class="flex items-center justify-between p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-emerald-500 hover:bg-emerald-50/50 dark:hover:bg-slate-800 transition group">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-950/70 text-blue-800 dark:text-blue-300 flex items-center justify-center font-bold text-lg">👔</span>
                                <div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400">Ketua RT (Bpk. H. Sutrisno)</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Persetujuan surat warga, monitoring kas & posyandu</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-slate-400 group-hover:text-emerald-600">&rarr;</span>
                        </a>

                        <!-- Bendahara -->
                        <a href="{{ route('quick.login', 'bendahara') }}" class="flex items-center justify-between p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-emerald-500 hover:bg-emerald-50/50 dark:hover:bg-slate-800 transition group">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 flex items-center justify-center font-bold text-lg">💰</span>
                                <div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400">Bendahara (Bpk. Joko Wardoyo)</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Grafik Chart.js kas RT & verifikasi struk transfer iuran</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-slate-400 group-hover:text-emerald-600">&rarr;</span>
                        </a>

                        <!-- Satpam -->
                        <a href="{{ route('quick.login', 'satpam') }}" class="flex items-center justify-between p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-emerald-500 hover:bg-emerald-50/50 dark:hover:bg-slate-800 transition group">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-slate-800 dark:bg-slate-700 text-white flex items-center justify-center font-bold text-lg">🛡️</span>
                                <div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400">Satpam (Pak Slamet)</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Console pos jaga, scanner QR tamu kurir, buka manual</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-slate-400 group-hover:text-emerald-600">&rarr;</span>
                        </a>

                        <!-- Warga -->
                        <a href="{{ route('quick.login', 'warga') }}" class="flex items-center justify-between p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-emerald-500 hover:bg-emerald-50/50 dark:hover:bg-slate-800 transition group">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 flex items-center justify-center font-bold text-lg">🏡</span>
                                <div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400">Warga (Bpk. Bambang Pamungkas)</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Buka gerbang dari HP, tiket tamu WA, panic button</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-slate-400 group-hover:text-emerald-600">&rarr;</span>
                        </a>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl text-center text-xs text-slate-500 dark:text-slate-400">
                        Password default staf demo: <strong class="text-slate-900 dark:text-white">admin123</strong> &bull; Warga: <strong class="text-slate-900 dark:text-white">warga123</strong>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ======================================================== -->
    <!-- 2. SOCIAL PROOF & ADAPTASI DUAL MODE (CLUSTER & KAMPUNG) -->
    <!-- ======================================================== -->
    <section class="border-y border-slate-200 dark:border-slate-800 py-10 bg-slate-100/50 dark:bg-slate-900/40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
            <p class="text-xs font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">
                Solusi Cerdas Yang Telah Diuji di Berbagai Karakter Lingkungan di Indonesia
            </p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-2">
                <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 shadow-xs">
                    <div class="text-3xl mb-2">🏢</div>
                    <div class="font-black text-sm text-slate-900 dark:text-white">Cluster Perumahan Modern</div>
                    <div class="text-xs text-slate-500 mt-1">Single Gate System & Satpam Shift 24 Jam</div>
                </div>
                <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 shadow-xs">
                    <div class="text-3xl mb-2">🏡</div>
                    <div class="font-black text-sm text-slate-900 dark:text-white">Komplek RT/RW & Paguyuban</div>
                    <div class="text-xs text-slate-500 mt-1">Transparansi Kas Iuran & Pengantar RT</div>
                </div>
                <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 shadow-xs">
                    <div class="text-3xl mb-2">🌾</div>
                    <div class="font-black text-sm text-slate-900 dark:text-white">Perkampungan Guyub & Desa</div>
                    <div class="text-xs text-slate-500 mt-1">Jadwal Siskamling Ronda & Posyandu KMS</div>
                </div>
                <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 shadow-xs">
                    <div class="text-3xl mb-2">🏗️</div>
                    <div class="font-black text-sm text-slate-900 dark:text-white">Developer & Pengembang</div>
                    <div class="text-xs text-slate-500 mt-1">Nilai Tambah Jual Smart Residence Tinggi</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- 3. KATALOG PERANGKAT KERAS & HARDWARE IOT (TERMASUK PIPELINE COMING SOON) -->
    <!-- ======================================================== -->
    <section id="hardware" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center max-w-3xl mx-auto space-y-2">
            <span class="text-xs font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">KATALOG EKOSISTEM IOT</span>
            <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white">
                Perangkat Keras Cerdas: Siap Pasang & Inovasi Mendatang
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                E-WARGA tidak hanya berupa software web, namun terintegrasi langsung dengan mesin palang gerbang dan pipeline sensor smart city terlengkap.
            </p>
        </div>

        <!-- TABEL 1: PERANGKAT YANG SUDAH TERSEDIA & SIAP OPERASIONAL -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">Perangkat Keras Aktif (Siap Operasional & Survei)</h3>
                </div>
                <span class="px-3 py-1 rounded-full text-[11px] font-black bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300">
                    STATUS: READY STOCK
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- 1. Smart Barrier Gate -->
                <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border-2 border-emerald-500/40 dark:border-emerald-500/30 shadow-lg space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl font-bold">
                        🚪
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">HARDWARE UTAMA</div>
                        <h4 class="text-base font-black text-slate-900 dark:text-white">Smart Barrier Gate Otomatis</h4>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        Mesin palang gerbang kecepatan tinggi (1.5 - 3 detik) bertenaga brushless DC. Terhubung ke controller microchip E-WARGA via API aman, respons buka 1-klik dari HP atau kartu RFID.
                    </p>
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-400 font-semibold">Sensor Anti-Bentur:</span>
                        <span class="font-bold text-slate-800 dark:text-white">Loop Detector + Radar</span>
                    </div>
                </div>

                <!-- 2. Kiosk QR Scanner & RFID Post -->
                <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border-2 border-emerald-500/40 dark:border-emerald-500/30 shadow-lg space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-50 dark:bg-cyan-950 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-2xl font-bold">
                        📷
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-cyan-600 dark:text-cyan-400 uppercase tracking-wider">OUTDOOR POST</div>
                        <h4 class="text-base font-black text-slate-900 dark:text-white">Kiosk QR & RFID Scanner Tiang</h4>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        Tiang pedestal outdoor standar industri bersertifikasi IP66 tahan panas dan hujan. Membaca tiket pass tamu QR Code via WhatsApp kurir dan kartu tap RFID penghuni secara instan.
                    </p>
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-400 font-semibold">Kecepatan Baca:</span>
                        <span class="font-bold text-slate-800 dark:text-white">&lt; 0.3 Detik</span>
                    </div>
                </div>

                <!-- 3. Virtual Hardware Simulator -->
                <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border-2 border-emerald-500/40 dark:border-emerald-500/30 shadow-lg space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-950 text-teal-600 dark:text-teal-400 flex items-center justify-center text-2xl font-bold">
                        🧪
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-teal-600 dark:text-teal-400 uppercase tracking-wider">VIRTUAL DIGITAL TWIN</div>
                        <h4 class="text-base font-black text-slate-900 dark:text-white">Simulator Palang Pintu Cloud</h4>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        Console visual twin di layar browser untuk mensimulasikan respon buka-tutup palang, timer 5 detik, dan traffic log secara langsung tanpa perlu membeli perangkat fisik terlebih dahulu.
                    </p>
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                        <a href="{{ route('simulator') }}" class="font-bold text-emerald-600 hover:underline">Uji Simulator Gerbang ➔</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABEL 2: PIPELINE HARDWARE COMING SOON (DALAM TAHAP R&D & INTEGRASI) -->
        <div class="space-y-4 pt-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-cyan-500 animate-ping"></span>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">Inovasi Perangkat Keras Mendatang (Pipeline & Coming Soon)</h3>
                </div>
                <span class="px-3 py-1 rounded-full text-[11px] font-black bg-cyan-100 dark:bg-cyan-950/80 text-cyan-800 dark:text-cyan-300">
                    TAHAP R&D & INTEGRASI
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- CS 1: CCTV ANPR -->
                <div class="p-6 rounded-3xl bg-slate-100/70 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-3 relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <span class="text-2xl">📹</span>
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300">Coming Soon</span>
                    </div>
                    <h4 class="font-black text-sm text-slate-900 dark:text-white">CCTV ANPR (Auto Number Plate Recognition)</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Kamera AI resolusi tinggi yang otomatis memindai plat nomor polisi kendaraan warga saat mendekati gerbang. Palang terbuka tanpa perlu tap kartu sama sekali.
                    </p>
                </div>

                <!-- CS 2: Tiang Tombol Panik Publik -->
                <div class="p-6 rounded-3xl bg-slate-100/70 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-3 relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <span class="text-2xl">🚨</span>
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase bg-rose-100 dark:bg-rose-950 text-rose-800 dark:text-rose-300">Coming Soon</span>
                    </div>
                    <h4 class="font-black text-sm text-slate-900 dark:text-white">Tiang Tombol Panik Darurat (Public Pole)</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Tiang panik fisik yang dipasang di taman/gang lingkungan. Dilengkapi sirine piezoelektrik, strobo merah 360°, dan interkom suara langsung terhubung ke pos satpam.
                    </p>
                </div>

                <!-- CS 3: Sensor Banjir Drainase -->
                <div class="p-6 rounded-3xl bg-slate-100/70 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-3 relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <span class="text-2xl">🌊</span>
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase bg-teal-100 dark:bg-teal-950 text-teal-800 dark:text-teal-300">Coming Soon</span>
                    </div>
                    <h4 class="font-black text-sm text-slate-900 dark:text-white">Sensor Banjir & Saluran Air IoT</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Sensor ultrasonik pengukur ketinggian debit saluran drainase got lingkungan warga. Otomatis membunyikan alarm dan mengirim broadcast notifikasi saat siaga 1.
                    </p>
                </div>

                <!-- CS 4: Smart PJU -->
                <div class="p-6 rounded-3xl bg-slate-100/70 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-3 relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <span class="text-2xl">💡</span>
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300">Coming Soon</span>
                    </div>
                    <h4 class="font-black text-sm text-slate-900 dark:text-white">Smart PJU (Lampu Jalan Pintar Otomatis)</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Lampu penerangan jalan hemat daya bertenaga surya/listrik dengan sensor gerak microwave. Meredup saat jalanan sepi dan otomatis terang saat ada warga melintas.
                    </p>
                </div>

                <!-- CS 5: Smart TPS Bak Sampah -->
                <div class="p-6 rounded-3xl bg-slate-100/70 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-3 relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <span class="text-2xl">🗑️</span>
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300">Coming Soon</span>
                    </div>
                    <h4 class="font-black text-sm text-slate-900 dark:text-white">Smart TPS (Sensor Kapasitas Sampah)</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Monitoring tingkat kepenuhan bak sampah TPS warga menggunakan sensor sonar. Menghubungi armada pengangkut sampah secara otomatis sebelum sampah menumpuk.
                    </p>
                </div>

                <!-- CS 6: Sub-Meter IoT Fasum -->
                <div class="p-6 rounded-3xl bg-slate-100/70 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-3 relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <span class="text-2xl">⚡</span>
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase bg-indigo-100 dark:bg-indigo-950 text-indigo-800 dark:text-indigo-300">Coming Soon</span>
                    </div>
                    <h4 class="font-black text-sm text-slate-900 dark:text-white">Sub-Meter IoT Air & Listrik Fasum</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Pencatatan konsumsi listrik dan air otomatis untuk balai warga, musholla, dan lapangan olahraga. Mencegah lonjakan tagihan tak terduga pada kas lingkungan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- 4. ENAM PILAR FITUR UTAMA SISTEM                          -->
    <!-- ======================================================== -->
    <section id="fitur" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center max-w-3xl mx-auto space-y-2">
            <span class="text-xs font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">MODUL UTAMA PLATFORM</span>
            <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white">
                6 Pilar Solusi Tata Kelola Digital Lingkungan
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Dirancang khusus dengan alur kerja yang intuitif, aman, dan mudah dioperasikan oleh seluruh kalangan warga.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- 1. Smart Gate -->
            <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs hover:border-emerald-500 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-3xl mb-5 font-bold group-hover:scale-110 transition">
                    🚪
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">Palang Pintu Otomatis (Smart Gate)</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                    Buka palang gerbang langsung dari tombol HP warga, tap kartu RFID, atau scan tiket QR kurir ekspedisi. Dilengkapi log riwayat keluar-masuk kendaraan real-time.
                </p>
                <a href="{{ route('simulator') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 inline-flex items-center gap-1 group-hover:underline">
                    Coba Simulator Gerbang &rarr;
                </a>
            </div>

            <!-- 2. Kas RT Terbuka -->
            <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs hover:border-emerald-500 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-950/70 text-blue-600 dark:text-blue-400 flex items-center justify-center text-3xl mb-5 font-bold group-hover:scale-110 transition">
                    📊
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">Buku Kas RT & Analisis Grafik</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                    Transparansi total keuangan lingkungan dengan visualisasi Pie Chart proporsi pengeluaran dan Bar Chart perbandingan bulanan. Bukti transfer iuran tersimpan rapi.
                </p>
                <a href="{{ route('dashboard') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 inline-flex items-center gap-1 group-hover:underline">
                    Lihat Pembukuan Kas &rarr;
                </a>
            </div>

            <!-- 3. Posyandu Digital -->
            <div id="posyandu" class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs hover:border-pink-500 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-pink-50 dark:bg-pink-950/70 text-pink-600 dark:text-pink-400 flex items-center justify-center text-3xl mb-5 font-bold group-hover:scale-110 transition">
                    🩺
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">Posyandu Digital & Cegah Stunting</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                    Tracking komprehensif 5 kategori warga (balita, anak-anak, ibu hamil, kader PKK, dan lansia). Dilengkapi alarm deteksi dini risiko stunting balita dan riwayat tensi posbindu.
                </p>
                <a href="{{ route('posyandu.index') }}" class="text-xs font-bold text-pink-600 dark:text-pink-400 inline-flex items-center gap-1 group-hover:underline">
                    Buku Posyandu KMS &rarr;
                </a>
            </div>

            <!-- 4. Surat Pengantar RT Online -->
            <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs hover:border-indigo-500 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-3xl mb-5 font-bold group-hover:scale-110 transition">
                    📜
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">Surat Pengantar RT Online Ber-QR</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                    Warga dapat mengajukan surat permohonan KTP, KK, Domisili, SKTM, dan izin usaha secara online. Ketua RT menyetujui dalam 1-klik dengan tanda tangan stempel QR verifikasi instan.
                </p>
                <a href="{{ route('letters.index') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 inline-flex items-center gap-1 group-hover:underline">
                    Layanan Surat Digital &rarr;
                </a>
            </div>

            <!-- 5. Jadwal Keamanan Satpam & Ronda -->
            <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs hover:border-amber-500 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-950/70 text-amber-600 dark:text-amber-400 flex items-center justify-center text-3xl mb-5 font-bold group-hover:scale-110 transition">
                    🔦
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">Jadwal Satpam & Ronda Warga</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                    Dua mode fleksibel: Shift satpam profesional untuk cluster perumahan atau roster ronda siskamling malam untuk perkampungan, lengkap dengan fitur tukar shift antar warga.
                </p>
                <a href="{{ route('schedules.index') }}" class="text-xs font-bold text-amber-600 dark:text-amber-400 inline-flex items-center gap-1 group-hover:underline">
                    Jadwal Petugas & Ronda &rarr;
                </a>
            </div>

            <!-- 6. Portal Mandiri Warga & Panic Button -->
            <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs hover:border-emerald-500 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-950/70 text-rose-600 dark:text-rose-400 flex items-center justify-center text-3xl mb-5 font-bold group-hover:scale-110 transition">
                    🚨
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">Portal Mandiri Warga & Tombol Panik</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                    Akses mandiri warga untuk memantau status pembayaran iuran, membuat tiket tamu kurir WhatsApp, dan menekan Panic Button darurat yang langsung membunyikan sirene pos satpam.
                </p>
                <a href="{{ route('warga.index') }}" class="text-xs font-bold text-rose-600 dark:text-rose-400 inline-flex items-center gap-1 group-hover:underline">
                    Akses Portal Warga &rarr;
                </a>
            </div>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- 5. SEBELUM VS SESUDAH (PERBANDINGAN NYATA)                -->
    <!-- ======================================================== -->
    <section id="komparasi" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center max-w-3xl mx-auto space-y-2">
            <span class="text-xs font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">KOMPARASI EFISIENSI</span>
            <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white">
                Mengapa Lingkungan Anda Harus Beralih ke E-WARGA?
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Bandingkan kerepotan tata kelola manual konvensional dengan kenyamanan sistem digital terpadu.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Kolom Cara Lama (Manual) -->
            <div class="p-8 sm:p-10 rounded-3xl bg-rose-50/70 dark:bg-rose-950/20 border-2 border-rose-200 dark:border-rose-900/50 space-y-6">
                <div class="flex items-center gap-3">
                    <span class="w-12 h-12 rounded-2xl bg-rose-200 dark:bg-rose-900/60 text-rose-800 dark:text-rose-200 flex items-center justify-center text-xl font-bold">✕</span>
                    <div>
                        <h3 class="font-black text-lg text-rose-950 dark:text-rose-200">Cara Lama (Manual & Konvensional)</h3>
                        <p class="text-xs text-rose-700 dark:text-rose-300">Rentan konflik, lambat, dan memicu kecurigaan antar warga</p>
                    </div>
                </div>

                <ul class="space-y-4 text-xs text-slate-700 dark:text-slate-300">
                    <li class="flex items-start gap-3">
                        <span class="text-rose-500 font-bold text-sm shrink-0">❌</span>
                        <span><strong>Buku Kas Kertas:</strong> Sering tercecer, bukti transfer hilang, dan warga ragu apakah uang kas iuran benar-benar dipakai untuk lingkungan.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-rose-500 font-bold text-sm shrink-0">❌</span>
                        <span><strong>Portal Bambu & Manual:</strong> Satpam repot buka tutup tali. Tamu luar dan kurir tidak terdata riwayat keluar-masuknya.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-rose-500 font-bold text-sm shrink-0">❌</span>
                        <span><strong>Urus Surat Mengganggu Waktu:</strong> Warga harus mengetuk rumah Ketua RT malam-malam hanya untuk meminta selembar surat pengantar KTP/KK.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-rose-500 font-bold text-sm shrink-0">❌</span>
                        <span><strong>Catatan Posyandu Tercecer:</strong> Buku timbangan manual membuat indikasi stunting pada balita terlambat terdeteksi oleh Puskesmas.</span>
                    </li>
                </ul>
            </div>

            <!-- Kolom Cara Baru (E-WARGA) -->
            <div class="p-8 sm:p-10 rounded-3xl bg-emerald-50/80 dark:bg-emerald-950/30 border-2 border-emerald-300 dark:border-emerald-800/80 space-y-6">
                <div class="flex items-center gap-3">
                    <span class="w-12 h-12 rounded-2xl bg-emerald-200 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200 flex items-center justify-center text-xl font-bold">✓</span>
                    <div>
                        <h3 class="font-black text-lg text-emerald-950 dark:text-emerald-200">Dengan Ekosistem E-WARGA</h3>
                        <p class="text-xs text-emerald-700 dark:text-emerald-300">Transparan, otomatis, terhubung IoT, dan bebas repot</p>
                    </div>
                </div>

                <ul class="space-y-4 text-xs text-slate-700 dark:text-slate-300">
                    <li class="flex items-start gap-3">
                        <span class="text-emerald-600 font-bold text-sm shrink-0">✅</span>
                        <span><strong>Buku Kas Terbuka & Grafik Interaktif:</strong> Warga dapat mengecek saldo kas live lewat grafik Pie/Bar Chart. Bukti transfer diunggah langsung.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-emerald-600 font-bold text-sm shrink-0">✅</span>
                        <span><strong>Smart Barrier Gate Otomatis:</strong> Palang terangkat 1-klik via HP warga, kartu RFID tap cepat, dan pass tamu QR Code via WhatsApp.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-emerald-600 font-bold text-sm shrink-0">✅</span>
                        <span><strong>Surat Pengantar RT Online:</strong> Warga mengajukan dari rumah, Ketua RT menyetujui dalam 1-klik, menghasilkan dokumen resmi berstempel QR.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-emerald-600 font-bold text-sm shrink-0">✅</span>
                        <span><strong>Posyandu Digital Terpadu:</strong> Monitoring 5 kategori demografi warga dengan alarm otomatis deteksi risiko stunting balita.</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- 6. TESTIMONI PENGURUS RT, SATPAM, POSYANDU & WARGA        -->
    <!-- ======================================================== -->
    <section id="testimoni" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center max-w-3xl mx-auto space-y-2">
            <span class="text-xs font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">TESTIMONI NYATA</span>
            <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white">
                Pengalaman Nyata Pengurus & Warga
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Simak bagaimana E-WARGA telah mentransformasi tata kelola lingkungan hunian di berbagai kota.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
                <div class="text-amber-400 text-lg">★★★★★</div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed italic">
                    "Dulu warga sering nunggak iuran bulanan karena lupa atau curiga ke mana uang kas dialokasikan. Sejak E-WARGA dipakai, transparansi kas dengan grafik Pie Chart membuat warga percaya. Tingkat bayar iuran melonjak dari 45% ke 98%!"
                </p>
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                    <div class="font-black text-sm text-slate-900 dark:text-white">Bpk. H. Sutrisno</div>
                    <div class="text-[11px] text-slate-400">Ketua RT 04 / RW 02 - Bekasi</div>
                </div>
            </div>

            <div class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
                <div class="text-amber-400 text-lg">★★★★★</div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed italic">
                    "Palang otomatis dan tiket QR tamu kurir sangat meringankan tugas pos jaga. Kendaraan luar yang mencurigakan tidak bisa sembarangan masuk cluster. Satpam kami jauh lebih tertib dan terbantu."
                </p>
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                    <div class="font-black text-sm text-slate-900 dark:text-white">Pak Slamet</div>
                    <div class="text-[11px] text-slate-400">Komandan Regu Keamanan Cluster</div>
                </div>
            </div>

            <div class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
                <div class="text-amber-400 text-lg">★★★★★</div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed italic">
                    "Fitur Posyandu Digital luar biasa membantu kader. Begitu berat badan dan tinggi balita diinput, ada indikator otomatis jika ada risiko stunting, sehingga intervensi gizi tambahan dari Puskesmas bisa segera diberikan."
                </p>
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                    <div class="font-black text-sm text-slate-900 dark:text-white">Bidan Nina Kurnia, S.Tr.Keb</div>
                    <div class="text-[11px] text-slate-400">Pembina Posyandu Melati</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- 7. SEKSI KERJASAMA, KONSULTASI & KONTAK RESMI (#kerjasama) -->
    <!-- ======================================================== -->
    <section id="kerjasama" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        
        <!-- Kartu Kontak Resmi & Email Kerjasama -->
        <div class="bg-gradient-to-r from-slate-900 via-[#1B365D] to-slate-950 rounded-3xl p-8 sm:p-12 text-white shadow-2xl border border-slate-800 space-y-8">
            <div class="text-center max-w-2xl mx-auto space-y-3">
                <span class="px-3.5 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-emerald-400/20 text-emerald-300 border border-emerald-400/30">
                    PUSAT KEMITRAAN & PENGADAAN PERANGKAT
                </span>
                <h3 class="text-3xl font-black">Konsultasi Teknis & Kerjasama Resmi</h3>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
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
        <!-- Formulir Permohonan Kemitraan & Survei Lokasi (Khusus Pengunjung / Calon Klien) -->
        <div id="daftar-komplek" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-8 sm:p-12 shadow-2xl space-y-8">
            <div class="text-center max-w-xl mx-auto space-y-2">
                <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">KONSULTASI GRATIS & SURVEI PERANGKAT</span>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">Daftarkan Lingkungan Anda Hari Ini</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Silakan isi formulir data komplek/perumahan Anda. Tim konsultan teknis lapangan kami akan menghubungi nomor WhatsApp Anda untuk jadwal survei dan estimasi biaya pemasangan.
                </p>
            </div>

            <form action="{{ route('tenant.register') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Nama Komplek / Perumahan / Desa</label>
                        <input type="text" name="complex_name" placeholder="Misal: Cluster Graha Asri Indah" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-sm outline-hidden transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Nomor RT / RW</label>
                        <input type="text" name="rt_rw" placeholder="Misal: RT 04 / RW 02" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-sm outline-hidden transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Kota / Kabupaten</label>
                        <input type="text" name="city" placeholder="Misal: Bekasi / Bandung / Surabaya" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-sm outline-hidden transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Estimasi Jumlah Rumah / KK</label>
                        <input type="number" name="estimated_houses" placeholder="Misal: 150" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-sm outline-hidden transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Nama Pengurus / Penanggung Jawab</label>
                        <input type="text" name="contact_name" placeholder="Nama Anda (Ketua RT / RW / Pengembang)" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-sm outline-hidden transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Nomor WhatsApp Aktif</label>
                        <input type="tel" name="contact_phone" placeholder="08xxxxxxxxxx" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-sm outline-hidden transition">
                    </div>
                </div>

                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 rounded-2xl border border-emerald-100 dark:border-emerald-900 flex items-center gap-3">
                    <input type="checkbox" name="need_barrier_gate" id="need_barrier_gate" value="1" checked class="w-5 h-5 text-emerald-600 rounded-md border-slate-300 dark:border-slate-700 focus:ring-emerald-500">
                    <label for="need_barrier_gate" class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200">
                        Lingkungan kami berminat memasang <strong>Palang Pintu Otomatis (Smart Barrier Gate) & Kunjungan Teknisi Lapangan</strong>
                    </label>
                </div>

                <div class="text-center pt-2">
                    <button type="submit" class="w-full sm:w-auto px-12 py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-2xl shadow-xl shadow-emerald-600/30 text-sm sm:text-base transition-all transform hover:-translate-y-0.5 cursor-pointer">
                        Kirim Permohonan & Jadwalkan Survei
                    </button>
                </div>
            </form>
        </div>
        @else
        <!-- KETIKA SUDAH LOGIN: FORM PENDAFTARAN TIDAK MUNCUL! MUNCULKAN STATUS SESI & TOMBOL KEMBALI KE SISTEM -->
        <div class="bg-gradient-to-r from-emerald-600 via-teal-700 to-[#1B365D] rounded-3xl p-8 sm:p-12 text-white shadow-2xl flex flex-col md:flex-row items-center justify-between gap-6 border border-emerald-400/20">
            <div class="space-y-3 text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/20 text-xs font-black uppercase tracking-wider backdrop-blur-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                    <span>Sesi Aktif: {{ Auth::user()->role_label }}</span>
                </div>
                <h3 class="text-2xl sm:text-3xl font-black">
                    Halo, {{ Auth::user()->name }}!
                </h3>
                <p class="text-xs sm:text-sm text-emerald-100 max-w-xl leading-relaxed">
                    Lingkungan Anda telah aktif di sistem E-WARGA. Anda sedang login dengan hak akses <strong>{{ Auth::user()->role_label }}</strong>. Silakan kembali ke panel dashboard untuk mengelola kas, palang pintu, dan pelayanan warga.
                </p>
            </div>
            <div class="shrink-0">
                @if(Auth::user()->role === 'super_admin')
                    <a href="{{ route('superadmin.index') }}" class="px-8 py-4 bg-amber-400 hover:bg-amber-300 text-slate-950 font-black rounded-2xl shadow-xl transition transform hover:scale-105 active:scale-95 text-sm flex items-center gap-2">
                        <span>Buka Dashboard Utama</span>
                        <span>➔</span>
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="px-8 py-4 bg-white text-emerald-950 hover:bg-emerald-50 font-black rounded-2xl shadow-xl transition transform hover:scale-105 active:scale-95 text-sm flex items-center gap-2">
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
