@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6 sm:space-y-8">

    <!-- Header Pusat Kendali Super Admin -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-slate-800">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950/70 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs font-black uppercase tracking-wider mb-2">
                <span>⚡ PUSAT KENDALI PLATFORM</span>
                <span>•</span>
                <span>SUPER ADMIN HUB</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">Pusat Pengelolaan Seluruh Wilayah</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Sebagai Super Admin (Pusat/Kepala), Anda mengawasi seluruh cluster perumahan dan desa/kampung yang terdaftar di platform E-WARGA.
            </p>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <a href="{{ route('dashboard') }}" class="w-full sm:w-auto text-center justify-center px-5 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-xs rounded-2xl shadow-lg shadow-emerald-600/20 transition flex items-center gap-2 cursor-pointer touch-press" title="Buka Dashboard operasional untuk wilayah yang sedang aktif">
                <span>Masuk ke Dashboard ({{ $activeTenantId == $desaTenant?->id ? ($desaTenant?->name ?? 'Desa Sukamaju Guyub') : ($clusterTenant?->name ?? 'Cluster Melati Mas') }})</span>
                <span>➔</span>
            </a>
        </div>
    </div>

    <!-- Metrik Agregat Platform (Pusat) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Total Lingkungan Terdaftar -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Wilayah Demo</div>
            <div class="text-3xl font-black text-slate-900 dark:text-white mt-2">
                {{ $totalTenants }} Lingkungan
            </div>
            <div class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1 font-semibold">
                1 Cluster Modern + 1 Desa Guyub
            </div>
        </div>

        <!-- Total Unit Rumah -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Unit Rumah</div>
            <div class="text-3xl font-black text-[#1B365D] dark:text-cyan-400 mt-2">
                {{ $totalHouses }} Rumah
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                Terdata di seluruh wilayah
            </div>
        </div>

        <!-- Total Warga & Pengurus -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Jiwa / Pengguna</div>
            <div class="text-3xl font-black text-purple-600 dark:text-purple-400 mt-2">
                {{ $totalUsers }} Pengguna
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                Ketua RT, Bendahara, Satpam, Warga
            </div>
        </div>

        <!-- Kas Agregat Seluruh Wilayah -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Kas Seluruh Wilayah</div>
            <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-2">
                Rp {{ number_format($aggregateCash, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1 font-semibold">
                Agregat gabungan Cluster & Desa
            </div>
        </div>
    </div>

    <!-- SEKSI PILIHAN LINGKUNGAN / TEMPAT (1 CLUSTER & 1 DESA DEMO) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-black text-slate-900 dark:text-white">Pilih Wilayah Demo yang Ingin Dikelola</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pilih salah satu lingkungan untuk masuk menginspeksi atau mengelola sistemnya secara langsung:</p>
            </div>
            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800">
                MULTI-TENANCY READY
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- KARTU 1: CLUSTER PERUMAHAN MODERN (CLUSTER MELATI MAS) -->
            <div class="rounded-3xl border {{ $activeTenantId == $clusterTenant?->id ? 'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/10' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900' }} p-8 shadow-xl space-y-6 relative overflow-hidden transition">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3.5">
                        <span class="w-14 h-14 rounded-2xl bg-blue-100 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 flex items-center justify-center font-black text-2xl shadow-xs">
                            🏢
                        </span>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-blue-600 dark:text-blue-400">TIPE: CLUSTER PERUMAHAN</span>
                            <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ $clusterTenant?->name ?? 'Cluster Melati Mas' }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $clusterTenant?->rt_rw }} &bull; {{ $clusterTenant?->city }}</p>
                        </div>
                    </div>

                    @if($activeTenantId == $clusterTenant?->id)
                        <span class="px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 text-xs font-black flex items-center gap-1.5 shadow-xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Sedang Aktif</span>
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 text-xs font-bold">
                            Standby
                        </span>
                    @endif
                </div>

                <!-- Fitur yang Berjalan di Cluster Ini -->
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 space-y-2 text-xs">
                    <div class="font-bold text-slate-700 dark:text-slate-300">Fitur Operasional Aktif:</div>
                    <ul class="space-y-1.5 text-slate-600 dark:text-slate-400">
                        <li class="flex items-center gap-2">
                            <span class="text-emerald-500 font-bold">✓</span>
                            <span><strong>Palang Pintu IoT (Barrier Gate):</strong> ESP32 RFID 13.56MHz & QR Kurir</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-emerald-500 font-bold">✓</span>
                            <span><strong>Keamanan:</strong> Regu Satpam 3-Shift (Pagi, Siang, Malam)</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-emerald-500 font-bold">✓</span>
                            <span><strong>Keuangan:</strong> Iuran Pengelolaan Lingkungan (IPL) & Pie Chart Transparan</span>
                        </li>
                    </ul>
                </div>

                <!-- Tombol Buka Dashboard / Beralih -->
                @if($activeTenantId == $clusterTenant?->id)
                    <a href="{{ route('dashboard') }}" class="w-full py-3.5 px-6 rounded-2xl font-black text-xs sm:text-sm bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-2 cursor-pointer">
                        <span>✓ Sedang Aktif — Buka Dashboard Cluster Ini</span>
                        <span>➔</span>
                    </a>
                @else
                    <form action="{{ route('superadmin.switch-tenant', $clusterTenant?->id ?? 1) }}" method="POST">
                        @csrf
                        <input type="hidden" name="redirect_to_dashboard" value="1">
                        <button type="submit" class="w-full py-3.5 px-6 rounded-2xl font-black text-xs sm:text-sm bg-blue-600 hover:bg-blue-700 text-white shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                            <span>Beralih & Buka Dashboard Cluster Ini</span>
                            <span>➔</span>
                        </button>
                    </form>
                @endif
            </div>

            <!-- KARTU 2: PERKAMPUNGAN / DESA (DESA SUKAMAJU GUYUB) -->
            <div class="rounded-3xl border {{ $activeTenantId == $desaTenant?->id ? 'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/10' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900' }} p-8 shadow-xl space-y-6 relative overflow-hidden transition">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3.5">
                        <span class="w-14 h-14 rounded-2xl bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 flex items-center justify-center font-black text-2xl shadow-xs">
                            🌾
                        </span>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">TIPE: PERKAMPUNGAN / DESA GUYUB</span>
                            <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ $desaTenant?->name ?? 'Desa Sukamaju Guyub' }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $desaTenant?->rt_rw }} &bull; {{ $desaTenant?->city }}</p>
                        </div>
                    </div>

                    @if($activeTenantId == $desaTenant?->id)
                        <span class="px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 text-xs font-black flex items-center gap-1.5 shadow-xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Sedang Aktif</span>
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 text-xs font-bold">
                            Standby
                        </span>
                    @endif
                </div>

                <!-- Fitur yang Berjalan di Desa Ini -->
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 space-y-2 text-xs">
                    <div class="font-bold text-slate-700 dark:text-slate-300">Fitur Operasional Aktif:</div>
                    <ul class="space-y-1.5 text-slate-600 dark:text-slate-400">
                        <li class="flex items-center gap-2">
                            <span class="text-emerald-500 font-bold">✓</span>
                            <span><strong>Siskamling & Ronda Malam:</strong> Jadwal giliran warga RT & absen pos kamling</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-emerald-500 font-bold">✓</span>
                            <span><strong>Kas Jimpitan Beras:</strong> Gotong royong swakarsa kas rukun warga</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-emerald-500 font-bold">✓</span>
                            <span><strong>Posyandu Terpadu:</strong> Pemeriksaan rutin balita stunting & lansia desa</span>
                        </li>
                    </ul>
                </div>

                <!-- Tombol Buka Dashboard / Beralih -->
                @if($activeTenantId == $desaTenant?->id)
                    <a href="{{ route('dashboard') }}" class="w-full py-3.5 px-6 rounded-2xl font-black text-xs sm:text-sm bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-2 cursor-pointer">
                        <span>✓ Sedang Aktif — Buka Dashboard Desa Ini</span>
                        <span>➔</span>
                    </a>
                @else
                    <form action="{{ route('superadmin.switch-tenant', $desaTenant?->id ?? 2) }}" method="POST">
                        @csrf
                        <input type="hidden" name="redirect_to_dashboard" value="1">
                        <button type="submit" class="w-full py-3.5 px-6 rounded-2xl font-black text-xs sm:text-sm bg-emerald-600 hover:bg-emerald-700 text-white shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                            <span>Beralih & Buka Dashboard Desa Ini</span>
                            <span>➔</span>
                        </button>
                    </form>
                @endif
            </div>

        </div>
    </div>

</div>
@endsection

