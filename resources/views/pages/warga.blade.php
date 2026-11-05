@extends('layouts.app')

@section('content')
<div class="py-6 sm:py-10 max-w-lg mx-auto px-4" x-data="wargaApp()">

    <!-- Header Profil Warga -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-extrabold text-lg">
                🏡
            </div>
            <div>
                <h1 class="font-extrabold text-slate-900 text-base">{{ $user->name ?? 'Bpk. Bambang Pamungkas' }}</h1>
                <p class="text-xs text-slate-500">{{ $tenant->name ?? 'Cluster Melati Mas' }} • <strong class="text-emerald-700">{{ $user?->house?->block_and_number ?? 'Unit Rumah Belum Terdata' }}</strong></p>
            </div>
        </div>

        <!-- Tombol Bantuan Tutorial -->
        <button @click="startTour()" type="button" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5">
            <span>❓ Panduan</span>
        </button>
    </div>

    <!-- TOMBOL RAKSASA: BUKA GERBANG MASUK -->
    <div id="step-open-gate" class="mb-8">
        <form action="{{ route('warga.open-gate') }}" method="POST">
            @csrf
            <button type="submit" 
                    @click="handleGateClick($event)"
                    :disabled="isOpening"
                    class="w-full py-8 px-6 rounded-3xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-xl shadow-xl shadow-emerald-600/30 active:scale-95 transition-all duration-200 flex flex-col items-center justify-center gap-2 group border-4 border-emerald-400/40">
                <span class="text-4xl group-hover:scale-110 transition duration-200" x-text="isOpening ? '⏳' : '🟢'"></span>
                <span x-text="isOpening ? 'PALANG SEDANG TERBUKA...' : 'BUKA GERBANG PORTAL'"></span>
                <span class="text-xs font-medium text-emerald-100 tracking-wide font-sans">Pencet tombol ini saat kendaraan tiba di pos satpam</span>
            </button>
        </form>
    </div>

    <!-- Kotak Menu Utama -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <!-- Card 1: Buat Undangan Tamu -->
        <div id="step-guest-pass" @click="openGuestModal = true" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs hover:border-emerald-500 cursor-pointer transition">
            <span class="text-2xl block mb-2">🎫</span>
            <div class="font-extrabold text-sm text-slate-900">Buat Izin Tamu</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Kirim tiket QR ke kurir/tamu via WhatsApp</div>
        </div>

        <!-- Card 2: Status Iuran Rumah -->
        <div id="step-billing" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-2xl block mb-2">💳</span>
            <div class="font-extrabold text-sm text-slate-900">Iuran Bulan Ini</div>
            @if($billing && $billing->status === 'paid')
                <div class="text-xs font-black text-emerald-600 mt-1 flex items-center gap-1">
                    <span>✅ LUNAS</span>
                    <span class="text-[10px] text-slate-400 font-normal">({{ $billing->formatted_amount }})</span>
                </div>
            @else
                <div class="text-xs font-black text-amber-600 mt-1 flex items-center gap-1">
                    <span>⏳ BELUM BAYAR</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Card Jadwal Jaga Pos / Ronda Hari Ini -->
    <a href="{{ route('schedules.index') }}" class="block bg-white rounded-2xl border border-slate-200 p-4 shadow-xs mb-6 hover:border-emerald-500 transition group">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-700 flex items-center justify-center text-xl font-bold">
                    🛡️
                </span>
                <div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jadwal Keamanan Hari Ini</div>
                    <div class="text-sm font-extrabold text-slate-900 group-hover:text-emerald-700">Lihat Siapa yang Jaga Pos / Ronda &rarr;</div>
                </div>
            </div>
            <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg">Cek Jadwal</span>
        </div>
    </a>

    <!-- Pengumuman RT Terkini -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs mb-6">
        <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
            <span>📢 PENGUMUMAN RT TERBARU</span>
        </div>
        <h4 class="text-sm font-extrabold text-slate-900">Kerja Bakti Bersih Saluran Lingkungan</h4>
        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
            Hari Minggu, 08 November 2026 pukul 07.00 WIB. Diharapkan bapak-bapak membawa cangkul & sapu lidi. Konsumsi kopi disediakan kas RT.
        </p>
    </div>

    <!-- TOMBOL DARURAT (PANIC BUTTON) -->
    <div class="pt-2">
        <form action="{{ route('warga.panic') }}" method="POST" onsubmit="return confirm('APAKAH ANDA YAKIN? Tombol ini akan langsung membunyikan sirine di Pos Satpam dan mengirim peringatan ke seluruh pengurus RT.')">
            @csrf
            <button type="submit" class="w-full py-4 rounded-2xl bg-red-600 hover:bg-red-700 text-white font-extrabold text-sm shadow-lg shadow-red-600/30 flex items-center justify-center gap-2 transition active:scale-98">
                <span class="text-xl">🚨</span>
                <span>TOMBOL DARURAT (PANIC BUTTON)</span>
            </button>
        </form>
    </div>

    <!-- Modal Buat Undangan Tamu -->
    <div x-show="openGuestModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs">
        <div @click.away="openGuestModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-extrabold text-slate-900 text-base">Buat Izin Masuk Tamu / Kurir</h3>
                <button @click="openGuestModal = false" class="text-slate-400 hover:text-slate-700 font-bold">&times;</button>
            </div>

            <form action="{{ route('warga.create-visitor') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Tamu / Kurir</label>
                    <input type="text" name="guest_name" placeholder="Misal: Kurir Shopee / Paman Rudi" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-emerald-500 outline-hidden">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kategori</label>
                        <select name="guest_type" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-emerald-500 outline-hidden font-semibold">
                            <option value="courier">📦 Kurir Paket</option>
                            <option value="guest">🚗 Tamu Keluarga</option>
                            <option value="ride_hailing">🛵 Ojek Online</option>
                            <option value="contractor">🔨 Tukang Bangunan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Berlaku Selama</label>
                        <select name="duration_hours" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-emerald-500 outline-hidden font-semibold">
                            <option value="2">2 Jam</option>
                            <option value="6">6 Jam</option>
                            <option value="24">1 Hari Penuh</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Plat Nomor Kendaraan (Opsional)</label>
                    <input type="text" name="vehicle_plate" placeholder="Misal: B 1234 XYZ" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-emerald-500 outline-hidden uppercase">
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="button" @click="openGuestModal = false" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-xs transition">Batal</button>
                    <button type="submit" class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md shadow-emerald-600/30 transition">Buat Tiket QR</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Flash Modal Hasil Tiket Tamu Baru (Kirim ke WhatsApp) -->
    @if(session('new_visitor'))
        @php $v = session('new_visitor'); @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80">
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 text-center space-y-4 shadow-2xl">
                <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 text-3xl flex items-center justify-center mx-auto">
                    ✅
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">Tiket Tamu Berhasil Dibuat!</h3>
                    <p class="text-xs text-slate-500 mt-1">Berikan kode QR ini kepada tamu/kurir untuk ditunjukkan ke Pos Satpam:</p>
                </div>

                <!-- QR Token Card -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 font-mono text-center">
                    <div class="text-xs text-slate-400">KODE TIKET GERBANG:</div>
                    <div class="text-2xl font-black text-[#1B365D] tracking-widest mt-1">{{ $v->qr_token }}</div>
                    <div class="text-[10px] text-slate-400 mt-1">Berlaku sampai: {{ $v->valid_until->format('d/m H:i') }} WIB</div>
                </div>

                @php
                    $waText = urlencode("Halo {$v->guest_name}, ini adalah izin masuk gerbang untuk ke rumah saya di " . ($tenant->name ?? 'Cluster Melati Mas') . " (" . ($v->hostHouse?->block_and_number ?? 'Rumah Warga') . "). Tunjukkan kode ini di Pos Satpam: *{$v->qr_token}*");
                    $waUrl = "https://wa.me/?text={$waText}";
                @endphp

                <a href="{{ $waUrl }}" target="_blank" class="block w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md transition">
                    📲 Bagikan Kode ke WhatsApp Tamu
                </a>

                <a href="{{ route('warga.index') }}" class="block text-xs font-bold text-slate-500 hover:underline">
                    Tutup
                </a>
            </div>
        </div>
    @endif

    <!-- OVERLAY TUTORIAL INTERAKTIF (3-STEP GUIDED TOUR UNTUK WARGA AWAM) -->
    <div x-show="tourStep > 0" x-cloak class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-6">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl text-center space-y-4 border border-slate-100 animate-in fade-in zoom-in duration-200">
            
            <!-- Step 1: Tombol Gerbang -->
            <template x-if="tourStep === 1">
                <div>
                    <div class="w-16 h-16 rounded-2xl bg-emerald-100 text-emerald-600 text-4xl flex items-center justify-center mx-auto mb-3">
                        🚪
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full">Langkah 1 dari 3</span>
                    <h3 class="text-lg font-black text-slate-900 mt-2">Tombol Buka Gerbang</h3>
                    <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                        Saat mobil atau motor Anda tiba di depan palang portal komplek, cukup pencet tombol hijau besar ini di HP Anda. Palang gerbang akan otomatis terangkat.
                    </p>
                </div>
            </template>

            <!-- Step 2: Izin Tamu -->
            <template x-if="tourStep === 2">
                <div>
                    <div class="w-16 h-16 rounded-2xl bg-purple-100 text-purple-600 text-4xl flex items-center justify-center mx-auto mb-3">
                        🎫
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-purple-600 bg-purple-50 px-2.5 py-0.5 rounded-full">Langkah 2 dari 3</span>
                    <h3 class="text-lg font-black text-slate-900 mt-2">Izin Masuk Tamu / Kurir</h3>
                    <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                        Ada pesanan paket Shopee/Tokopedia atau tamu keluarga? Buatkan tiket QR instan dan kirimkan langsung ke WhatsApp mereka dari menu ini.
                    </p>
                </div>
            </template>

            <!-- Step 3: Status Iuran -->
            <template x-if="tourStep === 3">
                <div>
                    <div class="w-16 h-16 rounded-2xl bg-blue-100 text-blue-600 text-4xl flex items-center justify-center mx-auto mb-3">
                        💳
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded-full">Langkah 3 dari 3</span>
                    <h3 class="text-lg font-black text-slate-900 mt-2">Transparansi Iuran Kas RT</h3>
                    <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                        Anda dapat memeriksa apakah iuran bulanan rumah Anda sudah tercatat Lunas, serta melihat laporan saldo kas RT secara terbuka tanpa ada rahasia.
                    </p>
                </div>
            </template>

            <!-- Tombol Navigasi Tour -->
            <div class="pt-4 flex items-center justify-between gap-3">
                <button type="button" @click="tourStep = 0" class="text-xs font-bold text-slate-400 hover:text-slate-600">
                    Lewati
                </button>
                <button type="button" 
                        @click="tourStep < 3 ? tourStep++ : tourStep = 0" 
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-md transition">
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

        handleGateClick(e) {
            this.isOpening = true;
            // Getaran Haptic di HP
            if (navigator.vibrate) {
                navigator.vibrate(200);
            }
        }
    }
}
</script>
@endsection

