@extends('layouts.app')

@section('content')
<div class="space-y-24 py-6 sm:py-12">

    <!-- ========================================== -->
    <!-- 1. HERO SECTION                            -->
    <!-- ========================================== -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Left Hero Content -->
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-black tracking-wide">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                    <span>PLATFORM SMART COMMUNITY RT/RW #1 DI INDONESIA</span>
                </div>
                
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 dark:text-white tracking-tight leading-[1.15]">
                    Modernisasi Lingkungan RT/RW & <span class="bg-gradient-to-r from-emerald-600 to-teal-500 bg-clip-text text-transparent">Cluster Perumahan</span> Anda.
                </h1>

                <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 leading-relaxed font-normal">
                    Tinggalkan buku kas kertas dan portal manual. <strong>E-WARGA</strong> mengintegrasikan <em>Palang Pintu Otomatis (Smart Gate)</em>, buku kas terbuka anti-curiga, jadwal ronda siskamling, posyandu balita & lansia, serta surat pengantar online ber-QR code dalam satu sistem siap pakai.
                </p>

                <!-- Action Buttons CTA -->
                <div class="flex flex-wrap items-center gap-4 pt-2">
                    <a href="{{ route('dashboard') }}" class="px-7 py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-2xl shadow-xl shadow-emerald-600/30 text-sm transition-all transform hover:-translate-y-0.5 flex items-center gap-3">
                        <span>Buka Dashboard Demo</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <a href="#daftar-komplek" class="px-7 py-4 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 font-extrabold rounded-2xl border border-slate-300 dark:border-slate-700 text-sm transition shadow-xs flex items-center gap-2">
                        <span>Daftarkan Komplek / Survei</span>
                    </a>
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
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">Saldo Kas Terbuka</div>
                    </div>
                </div>
            </div>

            <!-- Right Hero Card: Test Drive Peran Langsung -->
            <div class="lg:col-span-5">
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 space-y-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/app-icon.png') }}" alt="App Icon" class="w-12 h-12 rounded-2xl shadow-xs">
                            <div>
                                <h3 class="font-black text-slate-900 dark:text-white text-base">Uji Coba Pengalaman Nyata</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Pilih peran untuk langsung mencoba sistem:</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300">
                            1-KLIK DEMO
                        </span>
                    </div>

                    <div class="space-y-3">
                        <!-- Ketua RT -->
                        <a href="{{ route('quick.login', 'ketua_rt') }}" class="flex items-center justify-between p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-emerald-500 hover:bg-emerald-50/50 dark:hover:bg-slate-800 transition group">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-950/70 text-blue-800 dark:text-blue-300 flex items-center justify-center font-bold text-lg">👔</span>
                                <div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400">Ketua RT (Bpk. H. Sutrisno)</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Persetujuan surat, pantau kas, & kontrol warga</div>
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
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Grafik Chart.js kas RT, approval struk transfer</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-slate-400 group-hover:text-emerald-600">&rarr;</span>
                        </a>

                        <!-- Satpam -->
                        <a href="{{ route('quick.login', 'satpam') }}" class="flex items-center justify-between p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-emerald-500 hover:bg-emerald-50/50 dark:hover:bg-slate-800 transition group">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-slate-800 dark:bg-slate-700 text-white flex items-center justify-center font-bold text-lg">🛡️</span>
                                <div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400">Komandan Satpam (Pak Slamet)</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Console pos jaga, scanner QR tamu, buka manual</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-slate-400 group-hover:text-emerald-600">&rarr;</span>
                        </a>

                        <!-- Warga -->
                        <a href="{{ route('quick.login', 'warga') }}" class="flex items-center justify-between p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-emerald-500 hover:bg-emerald-50/50 dark:hover:bg-slate-800 transition group">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 flex items-center justify-center font-bold text-lg">🏡</span>
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

    <!-- ========================================== -->
    <!-- 2. SOCIAL PROOF & CLIENT TRUST STRIP       -->
    <!-- ========================================== -->
    <section class="border-y border-slate-200 dark:border-slate-800 py-10 bg-slate-100/50 dark:bg-slate-900/40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
            <p class="text-xs font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">
                Solusi Fleksibel Yang Dipercaya Berbagai Tipe Lingkungan Hunian di Indonesia
            </p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-2">
                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                    <div class="text-2xl mb-1">🏢</div>
                    <div class="font-black text-sm text-slate-900 dark:text-white">Cluster Townhouse Modern</div>
                    <div class="text-[11px] text-slate-500">Single Gate & Satpam 24 Jam</div>
                </div>
                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                    <div class="text-2xl mb-1">🏡</div>
                    <div class="font-black text-sm text-slate-900 dark:text-white">Komplek Perumahan & Paguyuban</div>
                    <div class="text-[11px] text-slate-500">Transparansi Kas & Iuran IPL</div>
                </div>
                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                    <div class="text-2xl mb-1">🌾</div>
                    <div class="font-black text-sm text-slate-900 dark:text-white">RT Perkampungan & Desa</div>
                    <div class="text-[11px] text-slate-500">Siskamling Ronda & Posyandu</div>
                </div>
                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                    <div class="text-2xl mb-1">🏗️</div>
                    <div class="font-black text-sm text-slate-900 dark:text-white">Developer & Pengembang Properti</div>
                    <div class="text-[11px] text-slate-500">Nilai Jual Smart Living Tinggi</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- 3. SEBELUM VS SESUDAH (PERBANDINGAN NYATA)  -->
    <!-- ========================================== -->
    <section id="komparasi" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-14">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Komparasi Solusi</span>
            <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mt-1">Mengapa Lingkungan Anda Membutuhkan E-WARGA?</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Bandingkan kerepotan tata kelola manual dengan efisiensi sistem digital modern.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Kolom Cara Lama (Manual) -->
            <div class="p-8 rounded-3xl bg-rose-50/70 dark:bg-rose-950/20 border-2 border-rose-200 dark:border-rose-900/50 space-y-5">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-rose-200 dark:bg-rose-900/60 text-rose-800 dark:text-rose-200 flex items-center justify-center text-xl font-bold">✕</span>
                    <div>
                        <h3 class="font-black text-lg text-rose-950 dark:text-rose-200">Cara Lama (Manual & Konvensional)</h3>
                        <p class="text-xs text-rose-700 dark:text-rose-300">Rentan konflik, lambat, dan memicu rasa curiga</p>
                    </div>
                </div>

                <ul class="space-y-4 text-xs text-slate-700 dark:text-slate-300">
                    <li class="flex items-start gap-3">
                        <span class="text-rose-500 font-bold text-sm shrink-0">❌</span>
                        <span><strong>Buku Kas Kertas:</strong> Sering tercecer, struk hilang, dan warga ragu apakah uang iuran benar-benar dipakai untuk lingkungan.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-rose-500 font-bold text-sm shrink-0">❌</span>
                        <span><strong>Portal Manual & Macet:</strong> Satpam repot buka tutup tali/palang bambu. Tamu gelap dan kurir tidak terdata keluar-masuk.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-rose-500 font-bold text-sm shrink-0">❌</span>
                        <span><strong>Urus Surat Repot:</strong> Warga harus mengetuk rumah Ketua RT malam-malam hanya untuk meminta selembar surat pengantar KTP/KK.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-rose-500 font-bold text-sm shrink-0">❌</span>
                        <span><strong>Data Posyandu Tercecer:</strong> Buku timbangan manual membuat indikasi stunting pada balita terlambat dideteksi Puskesmas.</span>
                    </li>
                </ul>
            </div>

            <!-- Kolom Cara Baru (E-WARGA) -->
            <div class="p-8 rounded-3xl bg-emerald-50/80 dark:bg-emerald-950/30 border-2 border-emerald-300 dark:border-emerald-800/80 space-y-5">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-emerald-200 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200 flex items-center justify-center text-xl font-bold">✓</span>
                    <div>
                        <h3 class="font-black text-lg text-emerald-950 dark:text-emerald-200">Dengan Ekosistem E-WARGA</h3>
                        <p class="text-xs text-emerald-700 dark:text-emerald-300">Otomatis, transparan, terintegrasi IoT, dan bebas repot</p>
                    </div>
                </div>

                <ul class="space-y-4 text-xs text-slate-700 dark:text-slate-300">
                    <li class="flex items-start gap-3">
                        <span class="text-emerald-600 font-bold text-sm shrink-0">✅</span>
                        <span><strong>Buku Kas Terbuka & Grafik:</strong> Warga memantau kas secara live lewat grafik Pie/Bar Chart. Bukti transfer di-upload langsung.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-emerald-600 font-bold text-sm shrink-0">✅</span>
                        <span><strong>Smart Barrier Gate Otomatis:</strong> Buka palang 1-klik via HP, kartu RFID tap cepat, dan pass tamu QR Code via WhatsApp.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-emerald-600 font-bold text-sm shrink-0">✅</span>
                        <span><strong>Surat Pengantar RT Online:</strong> Warga mengajukan dari kasur, Ketua RT approve 1-klik, keluar dokumen resmi ber-QR Code.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-emerald-600 font-bold text-sm shrink-0">✅</span>
                        <span><strong>Posyandu Digital Terpadu:</strong> Tracking 5 kategori warga (balita, anak, bumil, kader, lansia) dengan alarm deteksi dini stunting.</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- 4. ENAM FITUR UTAMA (CORE MODULES)         -->
    <!-- ========================================== -->
    <section id="fitur" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-14">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Modul Unggulan</span>
            <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mt-1">6 Pilar Solusi Cerdas Lingkungan</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Didesain ramah lansia, mudah dipahami warga biasa, dan berstandar keamanan tinggi.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- 1. Smart Gate -->
            <div id="keamanan" class="bg-white dark:bg-slate-900 p-7 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs hover:border-emerald-500 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-3xl mb-5 font-bold group-hover:scale-110 transition">
                    🚪
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">Palang Pintu Otomatis (Smart Gate)</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                    Mendukung tombol HP warga, tap kartu RFID, dan scan QR pass kurir. Dilengkapi Virtual Simulator untuk pengujian di komputer tanpa hardware fisik.
                </p>
                <a href="{{ route('simulator') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 inline-flex items-center gap-1 group-hover:underline">
                    Coba Simulator Gerbang &rarr;
                </a>
            </div>

            <!-- 2. Kas RT Terbuka -->
            <div class="bg-white dark:bg-slate-900 p-7 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs hover:border-emerald-500 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-950/70 text-blue-600 dark:text-blue-400 flex items-center justify-center text-3xl mb-5 font-bold group-hover:scale-110 transition">
                    📊
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">Buku Kas RT & Analisis Grafik</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                    Visualisasi keuangan dengan Pie Chart dan Bar Chart. Warga dapat mengecek saldo kas aktif dan bukti pembayaran iuran lingkungan transparan.
                </p>
                <a href="{{ route('dashboard') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 inline-flex items-center gap-1 group-hover:underline">
                    Lihat Grafik Keuangan &rarr;
                </a>
            </div>

            <!-- 3. Posyandu Digital -->
            <div id="posyandu" class="bg-white dark:bg-slate-900 p-7 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs hover:border-pink-500 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-pink-50 dark:bg-pink-950/70 text-pink-600 dark:text-pink-400 flex items-center justify-center text-3xl mb-5 font-bold group-hover:scale-110 transition">
                    🩺
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">Modul Posyandu & Cegah Stunting</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                    Pelacakan demografi 5 kategori (balita, anak-anak, ibu hamil, kader PKK, dan lansia). Dilengkapi deteksi dini stunting dan posbindu tensi.
                </p>
                <a href="{{ route('posyandu.index') }}" class="text-xs font-bold text-pink-600 dark:text-pink-400 inline-flex items-center gap-1 group-hover:underline">
                    Buku Register Posyandu &rarr;
                </a>
            </div>

            <!-- 4. Surat Pengantar RT Online -->
            <div class="bg-white dark:bg-slate-900 p-7 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs hover:border-emerald-500 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-3xl mb-5 font-bold group-hover:scale-110 transition">
                    📜
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">Surat Pengantar RT Online Ber-QR</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                    Warga mengajukan surat KTP, KK, Domisili, SKTM, SKU dari smartphone. Ketua RT menyetujui dengan 1-klik lengkap dengan stempel QR verifikasi resmi.
                </p>
                <a href="{{ route('letters.index') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 inline-flex items-center gap-1 group-hover:underline">
                    Layanan Pengajuan Surat &rarr;
                </a>
            </div>

            <!-- 5. Jadwal Keamanan & Ronda -->
            <div class="bg-white dark:bg-slate-900 p-7 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs hover:border-emerald-500 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-950/70 text-amber-600 dark:text-amber-400 flex items-center justify-center text-3xl mb-5 font-bold group-hover:scale-110 transition">
                    🔦
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">Jadwal Satpam & Ronda Warga</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                    Dual mode fleksibel: Shift satpam profesional untuk perumahan cluster atau jadwal ronda malam swakarsa untuk perkampungan lengkap dengan tukar shift.
                </p>
                <a href="{{ route('schedules.index') }}" class="text-xs font-bold text-amber-600 dark:text-amber-400 inline-flex items-center gap-1 group-hover:underline">
                    Lihat Jadwal Bulan Ini &rarr;
                </a>
            </div>

            <!-- 6. Mobile View Warga & Panic Button -->
            <div class="bg-white dark:bg-slate-900 p-7 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs hover:border-emerald-500 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-950/70 text-rose-600 dark:text-rose-400 flex items-center justify-center text-3xl mb-5 font-bold group-hover:scale-110 transition">
                    🚨
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">Aplikasi Mobile & Tombol Panik</h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                    Tampilan smartphone dengan tombol raksasa untuk membuka gerbang, kirim tiket tamu via WhatsApp, dan Panic Button darurat ke pos satpam.
                </p>
                <a href="{{ route('warga.index') }}" class="text-xs font-bold text-rose-600 dark:text-rose-400 inline-flex items-center gap-1 group-hover:underline">
                    Uji Tampilan Mobile &rarr;
                </a>
            </div>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- 5. TEASER SIMULATOR GERBANG INTERAKTIF     -->
    <!-- ========================================== -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-slate-900 via-[#1B365D] to-slate-950 rounded-3xl p-8 sm:p-14 text-white shadow-2xl relative overflow-hidden border border-slate-800">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-8 space-y-4">
                    <span class="px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-cyan-400/20 text-cyan-300 border border-cyan-400/30">
                        ⚡ LIVE DEVELOPER SIMULATOR
                    </span>
                    <h3 class="text-3xl sm:text-4xl font-black">Uji Coba Respon Buka Tutup Palang Pintu 5 Detik</h3>
                    <p class="text-sm text-slate-300 leading-relaxed max-w-2xl">
                        Tidak perlu menunggu hardware datang untuk membuktikan kecanggihannya. Buka layar simulator gerbang kami di tab browser lain, lalu pencet tombol buka di HP warga. Palang virtual terangkat dengan timer otomatis 5 detik.
                    </p>
                </div>
                <div class="lg:col-span-4 flex flex-col gap-3">
                    <a href="{{ route('simulator') }}" class="px-6 py-4 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black rounded-2xl text-center shadow-lg transition">
                        Buka Virtual Smart Gate ➔
                    </a>
                    <a href="{{ route('satpam.console') }}" class="px-6 py-4 bg-white/10 hover:bg-white/20 text-white font-bold rounded-2xl text-center border border-white/20 text-xs transition">
                        Lihat Monitor Pos Satpam
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- 6. TESTIMONI KLIEN & KETUA RT              -->
    <!-- ========================================== -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Suara Pengurus Lingkungan</span>
            <h2 class="text-3xl font-black text-slate-900 dark:text-white mt-1">Apa Kata Pengurus RT & Warga?</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
                <div class="text-amber-400 text-lg">★★★★★</div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed italic">
                    "Dulu warga sering nunggak iuran karena lupa atau curiga kemana larinya kas. Sejak ada E-WARGA, warga bisa lihat grafik kas terbuka lewat HP. Tingkat pembayaran iuran naik dari 45% ke 98%!"
                </p>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                    <div class="font-black text-sm text-slate-900 dark:text-white">Bpk. H. Sutrisno</div>
                    <div class="text-[11px] text-slate-400">Ketua RT 04 / RW 02 - Bekasi</div>
                </div>
            </div>

            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
                <div class="text-amber-400 text-lg">★★★★★</div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed italic">
                    "Palang otomatis dan tiket QR kurir sangat membantu pos jaga kami. Kendaraan luar yang mencurigakan tidak bisa sembarangan masuk. Posisi satpam jauh lebih dihargai dan tertib."
                </p>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                    <div class="font-black text-sm text-slate-900 dark:text-white">Pak Slamet</div>
                    <div class="text-[11px] text-slate-400">Komandan Regu Keamanan Cluster</div>
                </div>
            </div>

            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
                <div class="text-amber-400 text-lg">★★★★★</div>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed italic">
                    "Fitur Posyandu Digital luar biasa lengkap. Ada alarm otomatis kalau berat/tinggi balita masuk kategori risiko stunting, jadi kader langsung bisa beri makanan tambahan tepat waktu."
                </p>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                    <div class="font-black text-sm text-slate-900 dark:text-white">Bidan Nina Kurnia, S.Tr.Keb</div>
                    <div class="text-[11px] text-slate-400">Pembina Posyandu Melati</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- 7. FORM PENDAFTARAN & KONSULTASI SURVEI     -->
    <!-- ========================================== -->
    <section id="daftar-komplek" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-8 sm:p-14 shadow-2xl space-y-8">
            <div class="text-center max-w-2xl mx-auto space-y-2">
                <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">KONSULTASI GRATIS & SURVEI PERANGKAT</span>
                <h3 class="text-3xl font-black text-slate-900 dark:text-white">Daftarkan Lingkungan Anda Hari Ini</h3>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                    Isi formulir di bawah ini. Tim konsultan teknisi E-WARGA akan menghubungi nomor WhatsApp Anda untuk penjadwalan survei titik gerbang dan aktivasi sistem.
                </p>
            </div>

            <form action="{{ route('tenant.register') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Nama Komplek / Perumahan / RT</label>
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
                        <input type="text" name="contact_name" placeholder="Nama Anda (Ketua RT / RW / Pengurus)" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-sm outline-hidden transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Nomor WhatsApp Aktif</label>
                        <input type="tel" name="contact_phone" placeholder="08xxxxxxxxxx" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-sm outline-hidden transition">
                    </div>
                </div>

                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 rounded-2xl border border-emerald-100 dark:border-emerald-900 flex items-center gap-3">
                    <input type="checkbox" name="need_barrier_gate" id="need_barrier_gate" value="1" checked class="w-5 h-5 text-emerald-600 rounded-md border-slate-300 dark:border-slate-700 focus:ring-emerald-500">
                    <label for="need_barrier_gate" class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200">
                        Lingkungan kami berminat memasang <strong>Palang Pintu Otomatis (Smart Gate) & Kunjungan Teknisi Lapangan</strong>
                    </label>
                </div>

                <div class="text-center pt-2">
                    <button type="submit" class="w-full sm:w-auto px-12 py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-2xl shadow-xl shadow-emerald-600/30 text-sm sm:text-base transition-all transform hover:-translate-y-0.5">
                        Kirim Registrasi & Jadwalkan Survei
                    </button>
                </div>
            </form>
        </div>
    </section>

</div>
@endsection
