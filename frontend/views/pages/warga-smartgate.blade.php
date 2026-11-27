@extends('layouts.app')

@section('title', 'Smart Gate Perumahan & Izin Akses Warga')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6" x-data="{
    isGateOnline: {{ $isGateOnline ? 'true' : 'false' }},
    openGateModal: false,
    gateLoading: false,
    gateMessage: '',
    gateTimer: 0,
    gateInterval: null,
    showNewVisitorModal: false,
    visitorCategory: 'courier',
    quickDuration: 3,
    init() {
        setInterval(async () => {
            try {
                const r = await fetch('{{ route('api.rfid.status') }}', { headers: { 'Accept': 'application/json' } });
                if (r.ok) {
                    const d = await r.json();
                    this.isGateOnline = !!d.online;
                }
            } catch (e) {}
        }, 4000);
    },
    async triggerOpenGate() {
        if (!this.isGateOnline) {
            alert('Smart Gate sedang OFFLINE! Hubungkan perangkat ESP32 ke hotspot/WiFi terlebih dahulu.');
            return;
        }
        this.openGateModal = true;
        this.gateLoading = true;
        this.gateMessage = 'Mengirimkan sinyal verifikasi identitas ke tiang palang gerbang…';
        try {
            const res = await fetch('{{ route('warga.open-gate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success) {
                this.gateMessage = data.message;
                this.gateTimer = data.duration || 5;
                if (this.gateInterval) clearInterval(this.gateInterval);
                this.gateInterval = setInterval(() => {
                    this.gateTimer--;
                    if (this.gateTimer <= 0) {
                        clearInterval(this.gateInterval);
                        this.openGateModal = false;
                        location.reload();
                    }
                }, 1000);
            } else {
                alert(data.message || 'Gagal membuka palang gerbang.');
                this.openGateModal = false;
            }
        } catch (err) {
            alert('Terjadi kendala jaringan saat menghubungi perangkat gerbang.');
            this.openGateModal = false;
        } finally {
            this.gateLoading = false;
        }
    }
}">

    <!-- BREADCRUMB & HEADER SECTION -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('warga.index') }}" class="hover:text-emerald-600 transition-colors">Portal Warga</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-200">Smart Gate Lingkungan</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <span>🚧 Smart Gate & Izin Akses Perumahan</span>
            </h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1 text-pretty">
                Akses portal perumahan otomatis dengan tap fisik e-KTP terverifikasi, dan persetujuan manual kunjungan tamu serta kurir.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Hardware IoT Live Status Badge -->
            <div
                class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-extrabold border transition-colors"
                :class="isGateOnline ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300' : 'bg-rose-50 dark:bg-rose-950/40 border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-300'"
            >
                <span class="relative flex h-2.5 w-2.5">
                    <span x-show="isGateOnline" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5" :class="isGateOnline ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                </span>
                <span x-text="isGateOnline ? 'ESP32 Gate: ONLINE' : 'ESP32 Gate: OFFLINE'">
                    ESP32 Gate: {{ $isGateOnline ? 'ONLINE' : 'OFFLINE' }}
                </span>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-black/10 dark:bg-white/10 font-mono">Hotspot BIAN</span>
            </div>

            <button 
                type="button" 
                @click="showNewVisitorModal = true"
                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-sm hover:shadow transition-colors flex items-center gap-1.5 focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900"
            >
                <span>+ Buat Izin Tamu / Kurir</span>
            </button>
        </div>
    </div>

    <!-- VALIDATION ERRORS & NEW VISITOR QR PASS BANNER -->
    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-semibold space-y-1 shadow-xs">
            <div class="font-black">Periksa kembali input Anda:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('new_visitor'))
        @php $nv = session('new_visitor'); @endphp
        <div class="p-5 rounded-3xl bg-emerald-50 dark:bg-emerald-950/50 border-2 border-emerald-400 dark:border-emerald-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
            <div class="space-y-1">
                <div class="text-xs font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300">✅ Tiket Izin Masuk Berhasil Diterbitkan</div>
                <div class="text-base font-black text-slate-900 dark:text-white">{{ $nv->guest_name }} ({{ $nv->guest_type_label }})</div>
                <div class="text-xs text-slate-600 dark:text-slate-300 tabular-nums">
                    Kode QR: <code class="px-2 py-0.5 rounded bg-white dark:bg-slate-900 font-mono font-black text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">{{ $nv->qr_token }}</code>
                    • Berlaku s/d {{ $nv->valid_until ? $nv->valid_until->format('d/m H:i') : '-' }} WIB
                </div>
            </div>
            @php
                $nvWaText = urlencode("Halo {$nv->guest_name}, ini kode izin masuk gerbang menuju " . ($tenant->name ?? 'Cluster Melati Mas') . " (" . ($nv->hostHouse?->block_and_number ?? 'Rumah Warga') . "). Tunjukkan ke Pos Satpam / scanner QR: *{$nv->qr_token}*");
            @endphp
            <a href="https://wa.me/?text={{ $nvWaText }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black text-center shrink-0 transition-colors">
                📲 Kirim QR ke WhatsApp
            </a>
        </div>
    @endif

    <!-- PERMINTAAN PERSETUJUAN MANUAL TAMU & KURIR (PENDING APPROVAL ALERT BANNER) -->
    @if($pendingApprovals->isNotEmpty())
        <div class="p-5 sm:p-6 rounded-3xl bg-amber-500/10 dark:bg-amber-950/40 border-2 border-amber-400 dark:border-amber-600 shadow-md space-y-4">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-3">
                    <span class="text-3xl animate-bounce">🔔</span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-black text-slate-900 dark:text-white">Menunggu Persetujuan Masuk Warga</h2>
                            <span class="px-2 py-0.5 rounded-full bg-amber-500 text-slate-950 font-black text-xs tabular-nums animate-pulse">
                                {{ $pendingApprovals->count() }} Permintaan
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                            Tamu atau kurir paket telah tiba di pos satpam / gerbang menuju rumah Anda. Berikan persetujuan untuk membuka portal otomatis.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Grid Permintaan Pending -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($pendingApprovals as $pending)
                    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-800/80 shadow-xs flex flex-col justify-between gap-4">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $pending->guest_type === 'courier' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300' : ($pending->guest_type === 'ride_hailing' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300') }}">
                                    {{ $pending->guest_type_label }}
                                </span>
                                <span class="text-xs font-mono text-slate-500 tabular-nums">Tiba {{ $pending->created_at->diffForHumans() }}</span>
                            </div>

                            <div class="text-base font-black text-slate-900 dark:text-white">
                                {{ $pending->guest_name }}
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-xs text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-950/60 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Unit Rumah</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $pending->hostHouse ? $pending->hostHouse->block_and_number : ($user->house ? $user->house->block_and_number : 'Rumah Anda') }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Kendaraan</span>
                                    <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $pending->vehicle_plate ?: 'Tanpa Plat' }}</span>
                                </div>
                                @if($pending->notes)
                                    <div class="col-span-2 pt-1 border-t border-slate-200 dark:border-slate-800">
                                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Keterangan / Paket</span>
                                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $pending->notes }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Tombol Aksi Persetujuan Manual -->
                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <form action="{{ route('warga.visitors.reject', $pending->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="reason" value="Ditolak oleh pemilik rumah via Smart Gate Warga">
                                <button 
                                    type="submit" 
                                    onclick="return confirm('Apakah Anda yakin ingin menolak izin masuk kunjungan ini?')"
                                    class="w-full py-2.5 px-3 rounded-xl border border-rose-300 dark:border-rose-800 hover:bg-rose-50 dark:hover:bg-rose-950/50 text-rose-700 dark:text-rose-300 text-xs font-black transition-colors flex items-center justify-center gap-1.5"
                                >
                                    <span>✕ Tolak</span>
                                </button>
                            </form>

                            <form action="{{ route('warga.visitors.approve', $pending->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="notes" value="Disetujui oleh pemilik rumah via Smart Gate Warga">
                                <button 
                                    type="submit" 
                                    class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-sm transition-colors flex items-center justify-center gap-1.5 focus-visible:ring-2 focus-visible:ring-emerald-500"
                                >
                                    <span>✓ Izinkan & Buka</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- GRID 2 KOLOM: STATUS KARTU E-KTP WARGA & PENGENDALI GERBANG -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- KOLOM KIRI (7 Kolom): IDENTITAS KARTU E-KTP & KONTROL AKSES OTOMATIS -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- KARTU FISIK E-KTP DIGITAL (HOLOGRAPHIC CHIP STYLE) -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-950 p-6 sm:p-7 text-white shadow-xl border border-slate-700/60">
                <!-- Watermark Background -->
                <div class="absolute -right-8 -bottom-8 opacity-10 pointer-events-none select-none text-9xl">
                    🦅
                </div>

                <div class="relative z-10 space-y-6">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">🪪</span>
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-wider text-emerald-400">Kartu Akses Portal Resmi</h3>
                                <p class="text-sm font-extrabold text-slate-200">KTP Elektronik Terdaftar Lingkungan</p>
                            </div>
                        </div>

                        <!-- Chip RFID Emulation Icon -->
                        <div class="px-2.5 py-1.5 rounded-lg bg-amber-400/20 border border-amber-300/40 flex items-center gap-1.5">
                            <span class="text-base">📡</span>
                            <span class="text-[10px] font-black tracking-widest text-amber-300 font-mono">13.56&nbsp;MHz</span>
                        </div>
                    </div>

                    <!-- Detail Data KTP -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nama Pemilik e-KTP</span>
                            <span class="text-lg font-black tracking-tight text-white block">{{ $user->name }}</span>
                            <span class="text-xs text-slate-400 font-mono tabular-nums">NIK: {{ $user->masked_nik }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Unit Rumah Warga</span>
                            <span class="text-base font-black text-emerald-300 block">
                                {{ $user->house ? $user->house->block_and_number : 'Belum Terhubung Unit' }}
                            </span>
                            <span class="text-xs text-slate-400">{{ $tenant->name ?? 'Cluster Melati Mas' }}</span>
                        </div>
                    </div>

                    <!-- UID Chip Kartu RFID & Status Verifikasi RW -->
                    <div class="p-4 rounded-2xl bg-black/40 border border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Nomor UID Kartu RFID (e-KTP)</span>
                            <div class="flex items-center gap-2 mt-0.5">
                                @if($user->rfid_tag)
                                    <code class="text-sm sm:text-base font-black font-mono tracking-wider text-emerald-300 tabular-nums">
                                        {{ $user->rfid_tag }}
                                    </code>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-500/20 text-emerald-300 font-bold border border-emerald-500/30">TERDAFTAR</span>
                                @else
                                    <span class="text-xs text-amber-300 font-semibold italic">Belum ada kartu e-KTP fisik yang terhubung</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block sm:text-right">Hak Buka Portal</span>
                            <div class="flex sm:justify-end mt-0.5">
                                @if($user->canAccessGate())
                                    <span class="px-3 py-1 rounded-xl bg-emerald-500 text-slate-950 font-black text-xs shadow-xs flex items-center gap-1.5">
                                        <span>✓</span>
                                        <span>Akses Portal Aktif</span>
                                    </span>
                                @elseif($user->verification_status === 'pending_rw')
                                    <span class="px-3 py-1 rounded-xl bg-amber-400 text-slate-950 font-black text-xs shadow-xs flex items-center gap-1.5">
                                        <span>⏳</span>
                                        <span>Menunggu ACC Ketua RW</span>
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-xl bg-rose-500 text-white font-black text-xs shadow-xs flex items-center gap-1.5">
                                        <span>✕</span>
                                        <span>KTP Belum Lengkap</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Panduan Operasional di Tiang Palang Gerbang -->
                    <div class="text-xs text-slate-300 bg-white/5 p-3.5 rounded-xl border border-white/5 space-y-1">
                        <div class="font-bold text-emerald-400 flex items-center gap-1.5">
                            <span>💡</span>
                            <span>Cara Menggunakan e-KTP di Tiang Gerbang:</span>
                        </div>
                        <p class="text-slate-300 leading-relaxed text-pretty">
                            Saat Anda tiba di depan portal gerbang perumahan, tempelkan sisi kiri e-KTP (area lambang Garuda) langsung ke sensor RFID RC522 pada tiang gerbang selama 1–2 detik. Palang pintu otomatis terangkat dan menutup kembali setelah kendaraan Anda lewat.
                        </p>
                    </div>

                    <!-- Tombol Buka Gerbang Mandiri 1-Klik -->
                    <div class="pt-2">
                        @if($user->canAccessGate())
                            <button 
                                type="button" 
                                @click="triggerOpenGate()"
                                :disabled="!isGateOnline || gateLoading"
                                class="w-full py-3.5 px-4 rounded-2xl font-black text-sm tracking-wide shadow-lg transition-colors flex items-center justify-center gap-2 focus-visible:ring-2 focus-visible:ring-emerald-400"
                                :class="isGateOnline ? 'bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-slate-950 shadow-emerald-950/40 active:scale-[0.98] cursor-pointer' : 'bg-slate-800 border border-rose-500/40 text-rose-300 cursor-not-allowed'"
                            >
                                <span class="text-lg" x-text="isGateOnline ? '🚪' : '🔌'"></span>
                                <span x-text="isGateOnline ? 'Buka Palang Gerbang Sekarang (1-Klik via HP)' : 'Smart Gate Offline — Hubungkan ESP32 Terlebih Dahulu'">
                                    {{ $isGateOnline ? 'Buka Palang Gerbang Sekarang (1-Klik via HP)' : 'Smart Gate Offline — Hubungkan ESP32 Terlebih Dahulu' }}
                                </span>
                            </button>
                        @else
                            <div class="p-3 rounded-xl bg-rose-950/60 border border-rose-800 text-rose-300 text-xs font-semibold text-center">
                                Akses gerbang terkunci sementara. Pastikan identitas KTP Anda telah disetujui oleh pengurus RT/RW.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- MODAL BUKA GERBANG FEEDBACK DIALOG -->
            <div 
                x-show="openGateModal" 
                x-cloak 
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
            >
                <div 
                    @click.away="if(!gateTimer) openGateModal = false"
                    class="w-full max-w-md rounded-3xl bg-slate-900 border border-slate-700 p-6 text-white text-center space-y-5 shadow-2xl"
                >
                    <div class="w-16 h-16 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-400/30 flex items-center justify-center mx-auto text-3xl">
                        <template x-if="gateLoading">
                            <span class="animate-spin text-2xl">⏳</span>
                        </template>
                        <template x-if="!gateLoading && gateTimer > 0">
                            <span class="text-3xl animate-bounce">🚪</span>
                        </template>
                        <template x-if="!gateLoading && gateTimer <= 0">
                            <span class="text-3xl">✅</span>
                        </template>
                    </div>

                    <div>
                        <h4 class="text-xl font-black text-white" x-text="gateTimer > 0 ? 'Palang Pintu Terbuka!' : 'Sinyal Dikirimkan'"></h4>
                        <p class="text-xs text-slate-300 mt-1" x-text="gateMessage"></p>
                    </div>

                    <template x-if="gateTimer > 0">
                        <div class="p-4 rounded-2xl bg-black/40 border border-emerald-500/30">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Menutup Otomatis Dalam</span>
                            <span class="text-4xl font-black text-emerald-400 font-mono tabular-nums" x-text="gateTimer + 's'"></span>
                        </div>
                    </template>

                    <button 
                        type="button" 
                        @click="openGateModal = false"
                        class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition-colors"
                    >
                        Tutup Jendela
                    </button>
                </div>
            </div>

            <!-- RIWAYAT LOG AKSES GERBANG TERBARU -->
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span>📋 Riwayat Akses Gerbang Unit Anda</span>
                    </h3>
                    <span class="text-xs text-slate-500 font-bold">{{ $gateLogs->count() }} Transaksi</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 text-[10px] uppercase font-black tracking-wider">
                                <th class="pb-2.5">Waktu</th>
                                <th class="pb-2.5">Metode Akses</th>
                                <th class="pb-2.5">Status</th>
                                <th class="pb-2.5">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($gateLogs as $log)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                    <td class="py-3 font-mono text-slate-500 tabular-nums">
                                        {{ $log->triggered_at ? $log->triggered_at->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td class="py-3">
                                        @if($log->access_method === 'rfid_card')
                                            <span class="px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 font-bold">
                                                📡 Tap e-KTP
                                            </span>
                                        @elseif($log->access_method === 'mobile_button')
                                            @if($log->visitor_id)
                                                <span class="px-2 py-0.5 rounded-md bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 font-bold">
                                                    ✓ Izin Tamu
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-md bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300 font-bold">
                                                    📱 1-Klik HP
                                                </span>
                                            @endif
                                        @elseif($log->access_method === 'satpam_manual')
                                            <span class="px-2 py-0.5 rounded-md bg-purple-100 dark:bg-purple-950 text-purple-800 dark:text-purple-300 font-bold">
                                                👮 Pos Satpam
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold">
                                                📷 {{ strtoupper($log->access_method) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        @if($log->status === 'granted')
                                            <span class="text-emerald-600 dark:text-emerald-400 font-black">TERBUKA</span>
                                        @else
                                            <span class="text-rose-600 dark:text-rose-400 font-black">DITOLAK</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-slate-600 dark:text-slate-400 max-w-xs truncate" title="{{ $log->reason }}">
                                        {{ $log->reason ?: 'Akses portal gerbang' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-slate-400 italic">
                                        Belum ada riwayat aktivitas akses gerbang yang tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN (5 Kolom): DAFTAR TAMU & FORMULIR IZIN KUNJUNGAN -->
        <div class="lg:col-span-5 space-y-6">

            <!-- DAFTAR TIKET TAMU & KURIR AKTIF HARI INI -->
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <span>👥 Tamu & Kurir Terverifikasi</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Izin masuk yang telah disetujui & masih berlaku</p>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 tabular-nums">
                        {{ $activeVisitors->count() }} Aktif
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($activeVisitors as $visitor)
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 flex flex-col justify-between gap-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase {{ $visitor->guest_type === 'courier' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300' : 'bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-300' }}">
                                    {{ $visitor->guest_type_label }}
                                </span>
                                @if($visitor->is_used)
                                    <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400">✓ SUDAH MASUK</span>
                                @else
                                    <span class="text-[10px] font-black text-amber-600 dark:text-amber-400">BELUM MASUK</span>
                                @endif
                            </div>

                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <div class="font-extrabold text-slate-900 dark:text-white text-sm">{{ $visitor->guest_name }}</div>
                                    <div class="text-xs text-slate-500 font-mono tabular-nums">
                                        {{ $visitor->vehicle_plate ?: 'Kendaraan Tidak Dicatat' }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <code class="text-xs font-black font-mono bg-white dark:bg-slate-900 px-2 py-1 rounded border border-slate-200 dark:border-slate-700 text-emerald-600 dark:text-emerald-400">
                                        {{ $visitor->qr_token }}
                                    </code>
                                    <span class="text-[10px] text-slate-400 block mt-0.5 tabular-nums">
                                        Berlaku s/d {{ $visitor->valid_until ? $visitor->valid_until->format('H:i') : '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs italic space-y-2">
                            <div class="text-3xl">📦</div>
                            <div>Belum ada tamu atau kurir aktif yang terdaftar hari ini.</div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- KARTU INFORMASI PERSETUJUAN MANUAL (KEBIJAKAN PERUMAHAN) -->
            <div class="p-5 rounded-3xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-3">
                <div class="flex items-center gap-2 font-black text-slate-900 dark:text-white text-xs uppercase tracking-wider">
                    <span>🛡️</span>
                    <span>SOP Keamanan Portal Perumahan</span>
                </div>
                <ul class="text-xs text-slate-600 dark:text-slate-300 space-y-2 leading-relaxed text-pretty">
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-500 font-bold">•</span>
                        <span><strong>Warga Penghuni:</strong> Wajib menggunakan fisik e-KTP terverifikasi untuk tap masuk portal gerbang otomatis.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-amber-500 font-bold">•</span>
                        <span><strong>Tamu / Kerabat:</strong> Memerlukan tiket pass pre-approval dari warga atau konfirmasi persetujuan saat tiba di pos satpam.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-indigo-500 font-bold">•</span>
                        <span><strong>Kurir Ekspedisi / Ojek:</strong> Satpam pos gerbang akan mencatat nomor plat dan mengirimkan permintaan konfirmasi 1-klik ke HP pemilik rumah.</span>
                    </li>
                </ul>
            </div>

        </div>

    </div>

    <!-- MODAL BUAT IZIN MASUK TAMU / KURIR MANDIRI -->
    <div 
        x-show="showNewVisitorModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
    >
        <div 
            @click.away="showNewVisitorModal = false"
            class="w-full max-w-lg rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 p-6 text-slate-900 dark:text-white space-y-5 shadow-2xl"
        >
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">🎫</span>
                    <h3 class="text-lg font-black tracking-tight">Terbitkan Izin Masuk Tamu / Kurir</h3>
                </div>
                <button type="button" @click="showNewVisitorModal = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
            </div>

            <form action="{{ route('warga.create-visitor') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                        Kategori Kunjungan
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <label class="p-2.5 rounded-xl border text-center cursor-pointer transition-colors" :class="visitorCategory === 'courier' ? 'bg-indigo-50 dark:bg-indigo-950 border-indigo-500 text-indigo-700 dark:text-indigo-300 font-black' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'">
                            <input type="radio" name="guest_type" value="courier" x-model="visitorCategory" class="sr-only">
                            <span class="text-base block">📦</span>
                            <span class="text-[11px] block mt-0.5">Kurir Paket</span>
                        </label>
                        <label class="p-2.5 rounded-xl border text-center cursor-pointer transition-colors" :class="visitorCategory === 'ride_hailing' ? 'bg-emerald-50 dark:bg-emerald-950 border-emerald-500 text-emerald-700 dark:text-emerald-300 font-black' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'">
                            <input type="radio" name="guest_type" value="ride_hailing" x-model="visitorCategory" class="sr-only">
                            <span class="text-base block">🛵</span>
                            <span class="text-[11px] block mt-0.5">Ojek / Makanan</span>
                        </label>
                        <label class="p-2.5 rounded-xl border text-center cursor-pointer transition-colors" :class="visitorCategory === 'guest' ? 'bg-amber-50 dark:bg-amber-950 border-amber-500 text-amber-700 dark:text-amber-300 font-black' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'">
                            <input type="radio" name="guest_type" value="guest" x-model="visitorCategory" class="sr-only">
                            <span class="text-base block">👥</span>
                            <span class="text-[11px] block mt-0.5">Tamu Pribadi</span>
                        </label>
                        <label class="p-2.5 rounded-xl border text-center cursor-pointer transition-colors" :class="visitorCategory === 'contractor' ? 'bg-purple-50 dark:bg-purple-950 border-purple-500 text-purple-700 dark:text-purple-300 font-black' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'">
                            <input type="radio" name="guest_type" value="contractor" x-model="visitorCategory" class="sr-only">
                            <span class="text-base block">🔧</span>
                            <span class="text-[11px] block mt-0.5">Teknisi / Tukang</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                        Nama Tamu / Ekspedisi Kurir
                    </label>
                    <input 
                        type="text" 
                        name="guest_name" 
                        required 
                        placeholder="Contoh: Kurir J&T Express / Bpk. Hendra…" 
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-bold focus-visible:ring-2 focus-visible:ring-emerald-500"
                    >
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                            Plat Nomor Kendaraan (Opsional)
                        </label>
                        <input 
                            type="text" 
                            name="vehicle_plate" 
                            placeholder="Contoh: B 1234 XYZ…" 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-mono uppercase focus-visible:ring-2 focus-visible:ring-emerald-500"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                            Masa Berlaku Izin
                        </label>
                        <select 
                            name="duration_hours" 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-bold focus-visible:ring-2 focus-visible:ring-emerald-500"
                        >
                            <option value="1">1 Jam (Kurir Cepat)</option>
                            <option value="3" selected>3 Jam (Standar Pengantaran)</option>
                            <option value="12">12 Jam (Tamu Seharian)</option>
                            <option value="24">24 Jam (Tamu Menginap)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                        Catatan Tambahan / Nomor Resi (Opsional)
                    </label>
                    <input 
                        type="text" 
                        name="notes" 
                        placeholder="Contoh: Paket 1 kardus barang elektronik…" 
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-medium focus-visible:ring-2 focus-visible:ring-emerald-500"
                    >
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button 
                        type="button" 
                        @click="showNewVisitorModal = false"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-sm transition-colors"
                    >
                        Terbitkan Tiket & Izinkan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
