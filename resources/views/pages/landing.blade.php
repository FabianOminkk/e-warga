@extends('layouts.app')

@section('content')
<div class="py-12">
    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold tracking-wide">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    Platform Smart Community RT/RW Se-Indonesia
                </div>
                
                <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight">
                    Kelola Administrasi, Keamanan, & <span class="text-emerald-600 dark:text-emerald-400 underline decoration-emerald-300 dark:decoration-emerald-600">Palang Pintu Otomatis</span> Lingkungan Anda.
                </h1>

                <p class="text-lg text-slate-600 dark:text-slate-300 leading-relaxed">
                    E-WARGA mengintegrasikan tata kelola RT/RW, transparansi kas bulanan, izin tamu via WhatsApp, dan kontrol gerbang pintar (*Smart Barrier Gate*) dalam satu aplikasi yang ramah bapak-bapak dan lansia.
                </p>

                <!-- Quick Action Buttons -->
                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="#daftar-komplek" class="px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/20 text-sm transition flex items-center gap-2">
                        <span>Daftarkan Komplek Anda</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <a href="{{ route('simulator') }}" class="px-6 py-3.5 bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white font-bold rounded-xl text-sm transition flex items-center gap-2 border border-transparent dark:border-slate-700">
                        <span>Uji Coba Simulator Gerbang</span>
                        <span class="text-xs bg-emerald-500 text-slate-900 font-extrabold px-1.5 py-0.5 rounded">LIVE</span>
                    </a>
                </div>

                <!-- Stats summary -->
                <div class="grid grid-cols-3 gap-4 pt-6 border-t border-slate-200 dark:border-slate-800">
                    <div>
                        <div class="text-2xl font-extrabold text-[#1B365D] dark:text-cyan-400">{{ $totalHouses }} Unit</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Rumah Terdata</div>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-[#1B365D] dark:text-cyan-400">{{ $totalUsers }} Orang</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Warga Terdaftar</div>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($totalCash, 0, ',', '.') }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Saldo Kas RT Terbuka</div>
                    </div>
                </div>
            </div>

            <!-- Interactive Demo Showcase Box -->
            <div class="lg:col-span-5">
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 space-y-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/app-icon.png') }}" alt="App Icon" class="w-12 h-12 rounded-xl shadow-xs">
                            <div>
                                <h3 class="font-extrabold text-slate-900 dark:text-white">Uji Coba Peran (Role Demo)</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Pilih peran untuk mencoba pengalaman nyata:</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <a href="{{ route('quick.login', 'ketua_rt') }}" class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-emerald-500 hover:bg-emerald-50/50 dark:hover:bg-slate-800 transition group">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-950/70 text-blue-800 dark:text-blue-300 flex items-center justify-center font-bold text-lg">👔</span>
                                <div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-400">Ketua RT (Bpk. H. Sutrisno)</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Approver utama & pemantau lingkungan</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-slate-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400">Masuk &rarr;</span>
                        </a>

                        <a href="{{ route('quick.login', 'bendahara') }}" class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-emerald-500 hover:bg-emerald-50/50 dark:hover:bg-slate-800 transition group">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 flex items-center justify-center font-bold text-lg">💰</span>
                                <div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-400">Bendahara (Bpk. Joko Wardoyo)</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Kelola buku kas, iuran, & approval struk</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-slate-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400">Masuk &rarr;</span>
                        </a>

                        <a href="{{ route('quick.login', 'satpam') }}" class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-emerald-500 hover:bg-emerald-50/50 dark:hover:bg-slate-800 transition group">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-lg bg-slate-800 dark:bg-slate-700 text-white flex items-center justify-center font-bold text-lg">🛡️</span>
                                <div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-400">Satpam (Pak Slamet)</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Console pos jaga, scanner QR tamu, buka gate</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-slate-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400">Masuk &rarr;</span>
                        </a>

                        <a href="{{ route('quick.login', 'warga') }}" class="flex items-center justify-between p-3.5 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-emerald-500 hover:bg-emerald-50/50 dark:hover:bg-slate-800 transition group">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 flex items-center justify-center font-bold text-lg">🏡</span>
                                <div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-emerald-700 dark:group-hover:text-emerald-400">Warga (Bpk. Bambang Pamungkas)</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Buka gerbang dari HP, buat izin tamu, bayar iuran</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-slate-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400">Masuk &rarr;</span>
                        </a>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl text-center text-xs text-slate-500 dark:text-slate-400">
                        Password default seluruh akun staf pengurus: <strong class="text-slate-800 dark:text-slate-200">admin123</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Fitur Utama -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-20">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Fitur Unggulan</h2>
            <p class="text-3xl font-extrabold text-slate-900 dark:text-white mt-2">Didesain Khusus untuk Perumahan & Cluster Indonesia</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Card 1: Smart Barrier Gate -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl mb-4 font-bold">
                    🚪
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Palang Pintu Otomatis (Smart Gate)</h3>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Warga cukup memencet tombol di HP saat tiba di gerbang, atau menempelkan kartu RFID. Data kendaraan dan jam masuk otomatis tercatat di log pos satpam.
                </p>
            </div>

            <!-- Card 2: Transparansi Kas RT -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/70 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl mb-4 font-bold">
                    📊
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Buku Kas & Iuran Terbuka</h3>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Warga dapat melihat saldo kas RT secara *real-time*. Bendahara mengelola pemasukan dan pengeluaran secara transparan, bebas kecurigaan.
                </p>
            </div>

            <!-- Card 3: Izin Tamu WhatsApp -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400 flex items-center justify-center text-2xl mb-4 font-bold">
                    🎫
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Visitor Pass & Izin Kurir</h3>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Punya tamu atau kiriman paket online? Buat QR code izin masuk sementara dan bagikan langsung lewat WhatsApp. Satpam tinggal scan di pos jaga.
                </p>
            </div>
        </div>
    </div>

    <!-- Section Katalog Sensor Pintar (Coming Soon) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-20">
        <div class="bg-gradient-to-br from-[#1B365D] to-slate-900 text-white rounded-3xl p-8 lg:p-12 shadow-xl border border-slate-800">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
                <div>
                    <span class="px-3 py-1 bg-cyan-500/20 text-cyan-300 font-bold text-xs rounded-full border border-cyan-400/30">ROADMAP EKOSISTEM IOT</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold mt-3">Sensor & Perangkat Pintar Masa Depan</h2>
                    <p class="text-slate-300 text-sm mt-1">E-WARGA menyediakan teknisi terkelola untuk upgrade bertahap di lingkungan Anda.</p>
                </div>
                <div class="shrink-0">
                    <span class="text-xs bg-amber-400 text-slate-900 font-black px-3 py-1.5 rounded-lg tracking-wider">STATUS: COMING SOON</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white/10 backdrop-blur-xs p-5 rounded-2xl border border-white/10">
                    <div class="text-2xl mb-2">🚗</div>
                    <h4 class="font-bold text-white text-base">1. Smart CCTV AI & ANPR</h4>
                    <p class="text-xs text-slate-300 mt-1">Kamera otomatis mendeteksi plat nomor kendaraan warga untuk membuka palang tanpa perlu tap kartu.</p>
                </div>
                <div class="bg-white/10 backdrop-blur-xs p-5 rounded-2xl border border-white/10">
                    <div class="text-2xl mb-2">🚨</div>
                    <h4 class="font-bold text-white text-base">2. Public Panic Button Pole</h4>
                    <p class="text-xs text-slate-300 mt-1">Tiang tombol darurat fisik berlampu strobo di sudut rawan/taman yang langsung membunyikan sirine di pos satpam.</p>
                </div>
                <div class="bg-white/10 backdrop-blur-xs p-5 rounded-2xl border border-white/10">
                    <div class="text-2xl mb-2">🌊</div>
                    <h4 class="font-bold text-white text-base">3. Sensor Banjir & Saluran Air</h4>
                    <p class="text-xs text-slate-300 mt-1">Sensor ultrasonik di bibir got utama yang mengirim notifikasi peringatan dini saat debit air meluap.</p>
                </div>
                <div class="bg-white/10 backdrop-blur-xs p-5 rounded-2xl border border-white/10">
                    <div class="text-2xl mb-2">💡</div>
                    <h4 class="font-bold text-white text-base">4. Smart Street Lighting (PJU)</h4>
                    <p class="text-xs text-slate-300 mt-1">Otomatisasi lampu jalan dengan sensor cahaya LDR dan jadwal hemat energi untuk menekan tagihan listrik kas RT.</p>
                </div>
                <div class="bg-white/10 backdrop-blur-xs p-5 rounded-2xl border border-white/10">
                    <div class="text-2xl mb-2">🗑️</div>
                    <h4 class="font-bold text-white text-base">5. Sensor Volume Bak Sampah</h4>
                    <p class="text-xs text-slate-300 mt-1">Alert otomatis ke petugas kebersihan saat kapasitas tempat sampah penampungan sementara mencapai 90%.</p>
                </div>
                <div class="bg-white/10 backdrop-blur-xs p-5 rounded-2xl border border-white/10">
                    <div class="text-2xl mb-2">⚡</div>
                    <h4 class="font-bold text-white text-base">6. Sub-Meter Listrik & Air Fasum</h4>
                    <p class="text-xs text-slate-300 mt-1">Pantau pemakaian listrik pos satpam, balai warga, dan pompa air taman secara real-time untuk cegah kebocoran.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Form Pendaftaran Komplek -->
    <div id="daftar-komplek" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-20">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-8 sm:p-12 shadow-xl">
            <div class="text-center max-w-2xl mx-auto mb-8">
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">Registrasi Pengurus</span>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-1">Daftarkan Lingkungan & Pasang E-WARGA</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">
                    Isi formulir singkat di bawah ini. Tim ahli teknisi kami akan menghubungi nomor WhatsApp Anda untuk survei lokasi dan pemasangan alat.
                </p>
            </div>

            <form action="{{ route('tenant.register') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Nama Komplek / Perumahan</label>
                        <input type="text" name="complex_name" placeholder="Misal: Cluster Graha Cendana" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-sm outline-hidden transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">RT / RW</label>
                        <input type="text" name="rt_rw" placeholder="Misal: RT 03 / RW 08" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-sm outline-hidden transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Kota / Kabupaten</label>
                        <input type="text" name="city" placeholder="Misal: Kota Bandung / Jakarta Selatan" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-sm outline-hidden transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Estimasi Jumlah Rumah / KK</label>
                        <input type="number" name="estimated_houses" placeholder="Misal: 120" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-sm outline-hidden transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Nama Pengurus Kontak</label>
                        <input type="text" name="contact_name" placeholder="Nama Anda (Ketua RT / Pengurus)" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-sm outline-hidden transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-2">Nomor WhatsApp Aktif</label>
                        <input type="tel" name="contact_phone" placeholder="08xxxxxxxxxx" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 text-sm outline-hidden transition">
                    </div>
                </div>

                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 rounded-2xl border border-emerald-100 dark:border-emerald-900 flex items-center gap-3">
                    <input type="checkbox" name="need_barrier_gate" id="need_barrier_gate" value="1" checked class="w-5 h-5 text-emerald-600 rounded-md border-slate-300 dark:border-slate-700 focus:ring-emerald-500">
                    <label for="need_barrier_gate" class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                        Lingkungan kami berminat memasang <strong>Palang Pintu Otomatis (Smart Gate) & Kunjungan Teknisi</strong>
                    </label>
                </div>

                <div class="text-center pt-2">
                    <button type="submit" class="w-full sm:w-auto px-10 py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-2xl shadow-xl shadow-emerald-600/30 text-base transition">
                        Kirim Pendaftaran & Jadwalkan Survei
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

