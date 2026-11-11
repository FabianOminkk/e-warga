@extends('layouts.app')

@section('content')
<div class="bg-slate-900 text-slate-100 min-h-screen py-4 sm:py-8 -mt-4">
    <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 space-y-6 sm:space-y-8">
        
        <!-- Guard Post Tactical Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-800 gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-emerald-400 uppercase tracking-widest">SISTEM KONTROL POS KEAMANAN</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white mt-1">Console Pos Jaga - {{ $smartGate->name ?? 'Gerbang Utama' }}</h1>
                <p class="text-xs text-slate-400 mt-1">Cluster Melati Mas • Status Perangkat: <span class="text-emerald-400 font-bold">ONLINE (ESP32-GATE-01)</span></p>
            </div>

            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <!-- Tombol Buka Manual Darurat -->
                <form action="{{ route('satpam.trigger-manual') }}" method="POST" class="flex-1 sm:flex-initial">
                    @csrf
                    <input type="hidden" name="reason" value="Buka Darurat Petugas Satpam">
                    <button type="submit" class="w-full sm:w-auto px-5 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center justify-center gap-2 touch-press cursor-pointer">
                        <span>🟢 BUKA PALANG MANUAL</span>
                    </button>
                </form>

                <a href="{{ route('simulator') }}" class="px-4 py-3 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs rounded-xl border border-slate-700 transition touch-press flex items-center justify-center">
                    🧪 Buka Simulator
                </a>
            </div>
        </div>

        <!-- Main Grid: Scanner QR & Log Real-Time -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Kolom Kiri: Scanner QR Tamu & Validasi -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Box Scanner QR -->
                <div class="bg-slate-800/80 backdrop-blur-xs rounded-2xl border border-slate-700 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-extrabold text-white text-base">📷 Scanner Tiket Tamu & Kurir</h3>
                        <span class="text-[11px] font-mono text-cyan-400 bg-cyan-950 px-2 py-0.5 rounded border border-cyan-800">SCANNER AKTIF</span>
                    </div>

                    <!-- Area Kamera Simulasi -->
                    <div class="relative bg-slate-950 rounded-xl border-2 border-dashed border-slate-600 p-8 text-center flex flex-col items-center justify-center overflow-hidden">
                        <div class="w-36 h-36 rounded-2xl border-2 border-emerald-500 relative flex items-center justify-center bg-emerald-950/20">
                            <!-- Laser Scan Bar Animation -->
                            <div class="absolute inset-x-0 top-0 h-1 bg-emerald-400 animate-bounce shadow-lg shadow-emerald-400"></div>
                            <span class="text-4xl">📱</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-4">Arahkan kamera ke layar QR Code HP Tamu / Kurir</p>
                    </div>

                    <!-- Input Manual Token QR -->
                    <form action="{{ route('satpam.checkin-visitor') }}" method="POST" class="space-y-3 pt-2">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Atau Ketik Kode QR Tamu (Contoh: PASS-xxxx)</label>
                            <div class="flex gap-2">
                                <input type="text" name="qr_token" placeholder="PASS-ABC123XYZ" required class="flex-1 bg-slate-900 border border-slate-700 text-white px-3 py-2.5 rounded-xl text-sm font-mono focus:border-emerald-500 outline-hidden uppercase">
                                <button type="submit" class="px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs rounded-xl transition">
                                    Verifikasi
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Daftar Tiket Tamu Menunggu Masuk -->
                <div class="bg-slate-800/80 rounded-2xl border border-slate-700 p-6 space-y-4">
                    <h3 class="font-extrabold text-white text-base pb-2 border-b border-slate-700">Daftar Tiket Tamu Terdaftar Hari Ini</h3>
                    <div class="space-y-2.5">
                        @forelse($activeVisitors as $v)
                            <div class="p-3 rounded-xl bg-slate-900 border border-slate-700/60 flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-bold text-white">{{ $v->guest_name }} <span class="text-[10px] text-cyan-400 font-normal">({{ ucfirst($v->guest_type) }})</span></div>
                                    <div class="text-[11px] text-slate-400">Tujuan: {{ $v->hostHouse?->block_and_number ?? 'Pos Satpam' }} • Plat: {{ $v->vehicle_plate ?? 'Motor/Mobil' }}</div>
                                    <div class="text-[10px] font-mono text-slate-500 mt-0.5">Kode: <span class="text-emerald-400 font-bold">{{ $v->qr_token }}</span></div>
                                </div>
                                <form action="{{ route('satpam.checkin-visitor') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="qr_token" value="{{ $v->qr_token }}">
                                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[11px] rounded-lg shadow-xs transition">
                                        Scan Masuk
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="text-xs text-slate-500 text-center py-4">Belum ada tiket tamu aktif yang dibuat warga.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Whitelist E-KTP Warga & Log Kendaraan Masuk / Keluar -->
            <div class="lg:col-span-7 space-y-6">

                <!-- PANEL BARU: WHITELIST E-KTP & RFID WARGA RESMI (OTORISASI KETUA RW) -->
                <div class="bg-slate-800/80 rounded-2xl border-2 border-emerald-500/40 p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-700">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-950 text-emerald-300 font-extrabold border border-emerald-700">
                                    SINKRON DARI KETUA RW
                                </span>
                                <h3 class="font-extrabold text-white text-base">🪪 Database Whitelist RFID & E-KTP Warga</h3>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">
                                Palang pintu otomatis hanya mengenali NIK & kartu RFID warga yang telah disahkan secara resmi oleh <strong>Ketua RW</strong>.
                            </p>
                        </div>
                        <span class="text-xs font-mono text-emerald-400 font-bold bg-emerald-950/80 px-2.5 py-1 rounded-lg border border-emerald-800">
                            {{ $verifiedWargaList->where('verification_status', 'approved')->count() }} E-KTP AKTIF
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-700 text-slate-400 uppercase tracking-wider">
                                    <th class="py-2">Warga & Rumah</th>
                                    <th class="py-2">Nomor KTP (NIK)</th>
                                    <th class="py-2">UID Tag RFID</th>
                                    <th class="py-2 text-right">Otorisasi Palang</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/60">
                                @forelse($verifiedWargaList as $vw)
                                    <tr>
                                        <td class="py-2.5">
                                            <div class="font-bold text-white">{{ $vw->name }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $vw->house?->block_and_number ?? 'Unit Rumah Belum Terdata' }}</div>
                                        </td>
                                        <td class="py-2.5 font-mono text-slate-300">
                                            {{ $vw->nik ?? '-' }}
                                        </td>
                                        <td class="py-2.5 font-mono">
                                            @if($vw->rfid_tag)
                                                <span class="text-cyan-400 font-bold">{{ $vw->rfid_tag }}</span>
                                            @else
                                                <span class="text-slate-500 italic">Belum Ada RFID</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 text-right">
                                            @if($vw->canAccessGate())
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-950 text-emerald-400 border border-emerald-700">
                                                    <span>●</span> DI-ACC KETUA RW
                                                </span>
                                            @elseif($vw->verification_status === 'pending_rw')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-950 text-amber-400 border border-amber-800">
                                                    <span>⏳</span> MENUNGGU ACC RW
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-950 text-rose-400 border border-rose-800">
                                                    <span>🔒</span> TERKUNCI
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-slate-500">Belum ada warga terdaftar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Box Log Palang Pintu Real-Time -->
                <div class="bg-slate-800/80 rounded-2xl border border-slate-700 p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-700">
                        <h3 class="font-extrabold text-white text-base">🚗 Log Aktivitas Palang Pintu Real-Time</h3>
                    <span class="text-xs text-slate-400">Update Langsung Otomatis</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-700 text-slate-400 uppercase tracking-wider">
                                <th class="py-2.5">Waktu</th>
                                <th class="py-2.5">Pengguna / Kendaraan</th>
                                <th class="py-2.5">Metode</th>
                                <th class="py-2.5">Keterangan</th>
                                <th class="py-2.5 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/60 font-mono">
                            @foreach($recentLogs as $log)
                                <tr>
                                    <td class="py-3 text-cyan-400">{{ $log->triggered_at->format('H:i:s') }}</td>
                                    <td class="py-3 font-sans font-bold text-white">
                                        {{ $log->user->name ?? 'Tamu / Umum' }}
                                    </td>
                                    <td class="py-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold 
                                            {{ $log->access_method === 'mobile_button' ? 'bg-blue-900 text-blue-200' : '' }}
                                            {{ $log->access_method === 'rfid_card' ? 'bg-purple-900 text-purple-200' : '' }}
                                            {{ $log->access_method === 'satpam_manual' ? 'bg-emerald-900 text-emerald-200' : '' }}
                                            {{ $log->access_method === 'qr_code' ? 'bg-amber-900 text-amber-200' : '' }}
                                        ">
                                            {{ str_replace('_', ' ', $log->access_method) }}
                                        </span>
                                    </td>
                                    <td class="py-3 font-sans text-slate-300">{{ $log->reason }}</td>
                                    <td class="py-3 text-right">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $log->status === 'granted' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400' }}">
                                            {{ strtoupper($log->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection

