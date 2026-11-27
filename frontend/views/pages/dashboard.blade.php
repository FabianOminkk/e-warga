@extends('layouts.app')

@section('content')
<div class="max-w-[1440px] mx-auto px-2 sm:px-6 lg:px-8 py-3 sm:py-6 space-y-5 sm:space-y-6">

    <!-- Komponen Android Mobile Weather & Fast Status Bar (Tampil di Layar HP) -->
    <x-mobile-android-header :tenant="$tenant" />

    <!-- Header & Lingkungan Info -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-slate-800">
        <div>
            @if(Auth::user()?->role === 'ketua_rw')
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/70 border border-indigo-200 dark:border-indigo-800 text-indigo-800 dark:text-indigo-300 text-xs font-black uppercase tracking-wider mb-1.5">
                    <span>🏛️ TINGKAT JABATAN: KETUA RW</span>
                    <span>•</span>
                    <span>SUPERVISI SELURUH RT</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-1">Dashboard Supervisi Wilayah RW</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Sebagai Ketua RW, Anda membina seluruh RT di wilayah ini, memantau kas lingkungan, keamanan pos jaga utama, dan legalisasi surat rekomendasi warga.</p>
            @else
                <div class="flex items-center gap-2 text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">
                    <span>{{ $tenant->name ?? 'Cluster Melati Mas' }}</span>
                    <span>•</span>
                    <span>{{ $tenant->rt_rw ?? 'RT 05 / RW 12' }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-1">Dashboard Pengurus Lingkungan</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Pusat kendali keuangan kas, persetujuan iuran warga, dan analisis grafik keuangan.</p>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <a href="{{ route('keuangan.index') }}" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs rounded-xl shadow-xs transition-colors flex items-center gap-2 touch-press">
                <span>💰 Keuangan &amp; Iuran</span>
            </a>
            <a href="{{ route('residents.index') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors flex items-center gap-2 touch-press">
                <span>👨‍👩‍👧‍👦 Data Warga &amp; KK</span>
            </a>
            <a href="{{ route('simulator') }}" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors flex items-center gap-2 border border-transparent dark:border-slate-700 touch-press">
                <span>🧪 Buka Simulator</span>
            </a>
            <a href="{{ route('satpam.console') }}" class="px-4 py-2.5 bg-[#1B365D] hover:bg-slate-800 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-xs transition-colors flex items-center gap-2 touch-press">
                <span>🛡️ Monitor Satpam</span>
            </a>
        </div>
    </div>

    @include('partials.finance-summary')

    <!-- ================================================================= -->
    <!-- MEJA VERIFIKASI KTP & OTORISASI RFID WARGA OLEH KETUA RW         -->
    <!-- (SETIAP WARGA BARU WAJIB DI-ACC RW AGAR SINKRON KE SATPAM & GATE) -->
    <!-- ================================================================= -->
    <div id="seksi-verifikasi" class="bg-white dark:bg-slate-900 rounded-3xl border-2 border-emerald-500/30 p-6 lg:p-8 shadow-sm space-y-6 scroll-mt-20">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 text-[11px] font-black uppercase tracking-wide">
                        Kewenangan Khusus Ketua RW & Pengurus
                    </span>
                    @if($pendingWargaCount > 0)
                        <span class="px-2.5 py-0.5 rounded-full bg-amber-500 text-white text-[11px] font-black animate-pulse">
                            {{ $pendingWargaCount }} Berkas KTP Menunggu ACC
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold">
                            ✓ Seluruh Berkas KTP Telah Selesai Ditinjau
                        </span>
                    @endif
                </div>
                <h2 class="text-xl font-extrabold text-slate-900 dark:text-white mt-1">Meja Verifikasi KTP & Otorisasi Akses Gerbang Pintar</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Warga baru wajib mengisi data identitas KTP dan foto KTP. Setelah disahkan oleh <strong>Ketua RW</strong>, nomor KTP & chip RFID otomatis diteruskan ke <strong>Pos Satpam</strong> dan palang pintu terbuka otomatis mengenali identitas warga.
                </p>
            </div>
            
            <div class="text-xs text-slate-500 dark:text-slate-400 shrink-0">
                Total Warga Terdaftar: <strong class="text-slate-900 dark:text-white">{{ $wargaVerifications->count() }} Orang</strong>
            </div>
        </div>

        <!-- TAMPILAN 1: TATA LETAK KARTU VERTIKAL PADA SMARTPHONE (MOBILE-FIRST) -->
        <div class="block lg:hidden space-y-3.5">
            @forelse($wargaVerifications as $wv)
                <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            @if($wv->ktp_photo)
                                <a href="{{ $wv->ktp_url }}" target="_blank" class="shrink-0" title="Klik untuk lihat foto KTP">
                                    <img src="{{ $wv->ktp_url }}" alt="Foto KTP" class="w-14 h-10 rounded-xl object-cover border border-slate-300 dark:border-slate-700 shadow-xs">
                                </a>
                            @else
                                <div class="w-14 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-[10px] text-slate-500 font-bold border border-dashed border-slate-300 dark:border-slate-700 shrink-0">
                                    No KTP
                                </div>
                            @endif
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white leading-snug">{{ $wv->name }}</h3>
                                <div class="text-xs text-slate-600 dark:text-slate-400 font-semibold">{{ $wv->phone_number ?? '-' }}</div>
                            </div>
                        </div>

                        <span class="px-2.5 py-1 rounded-full text-[11px] font-black border shrink-0 {{ $wv->verification_badge['badge'] }}">
                            {{ $wv->verification_badge['icon'] }} {{ $wv->verification_badge['label'] }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 dark:border-slate-800 text-xs">
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Unit Rumah:</span>
                            <span class="font-extrabold text-emerald-600 dark:text-emerald-400 truncate block">{{ $wv->house?->block_and_number ?? 'Belum Terdata' }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">UID RFID:</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200 truncate block">{{ $wv->rfid_tag ?? 'Belum Ada' }}</span>
                        </div>
                    </div>

                    <div class="text-xs text-slate-600 dark:text-slate-300 font-mono">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block font-sans">NIK KTP:</span>
                        <span class="font-bold tracking-wider">{{ $wv->nik ?? 'Belum Diisi Warga' }}</span>
                    </div>

                    @if($wv->verification_status === 'pending_rw' || $wv->verification_status === 'unverified')
                        <div class="pt-2 flex items-center gap-2.5 border-t border-slate-100 dark:border-slate-800">
                            <form action="{{ route('rw.verifications.approve', $wv->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full min-h-[44px] px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md shadow-emerald-600/20 transition-transform active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer touch-press" title="ACC & Aktifkan RFID ke Pos Satpam">
                                    <span>✓ ACC KTP</span>
                                </button>
                            </form>

                            @if($wv->verification_status === 'pending_rw')
                                <form action="{{ route('rw.verifications.reject', $wv->id) }}" method="POST" @submit.prevent="window.ewargaConfirmSubmit($el, { title: 'Tolak Berkas KTP?', message: 'Tolak berkas KTP ini dan minta warga perbaiki foto/data?', confirmText: 'Ya, Tolak Berkas', tone: 'danger' })" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full min-h-[44px] px-3 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:hover:bg-rose-900 dark:text-rose-300 font-extrabold text-xs border border-rose-200 dark:border-rose-800 transition-transform active:scale-95 flex items-center justify-center gap-1 cursor-pointer touch-press">
                                        <span>✕ Tolak</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    @else
                        <div class="pt-2 text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5 border-t border-slate-100 dark:border-slate-800">
                            <span>✓ Terverifikasi & Sinkron ke Pos Satpam</span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-6 text-slate-400 text-xs">Belum ada warga terdaftar.</div>
            @endforelse
        </div>

        <!-- TAMPILAN 2: TABEL LEBAR KHUSUS DESKTOP (LARGE SCREEN) -->
        <div class="hidden lg:block overflow-x-auto -mx-3 sm:mx-0 px-3 sm:px-0">
            <table class="w-full text-left text-xs min-w-[680px]">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-3">Warga & Foto KTP</th>
                        <th class="py-3 px-3">Nomor KTP (NIK)</th>
                        <th class="py-3 px-3">Unit Rumah / Alamat</th>
                        <th class="py-3 px-3">UID RFID E-KTP</th>
                        <th class="py-3 px-3">Status Verifikasi</th>
                        <th class="py-3 px-3 text-right">Tindakan Ketua RW</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($wargaVerifications as $wv)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-3">
                                <div class="flex items-center gap-3">
                                    @if($wv->ktp_photo)
                                        <a href="{{ $wv->ktp_url }}" target="_blank" class="shrink-0" title="Klik untuk perbesar foto KTP">
                                            <img src="{{ $wv->ktp_url }}" alt="Foto KTP" class="w-12 h-8 rounded-lg object-cover border border-slate-300 dark:border-slate-700 hover:scale-110 transition shadow-xs">
                                        </a>
                                    @else
                                        <div class="w-12 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-[10px] text-slate-400 font-bold border border-dashed border-slate-300 dark:border-slate-700 shrink-0">
                                            No KTP
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-extrabold text-slate-900 dark:text-white text-sm">{{ $wv->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $wv->phone_number ?? '-' }} • {{ $wv->birth_place ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-3 font-mono font-bold text-slate-700 dark:text-slate-300">
                                {{ $wv->nik ?? 'Belum Diisi Warga' }}
                            </td>
                            <td class="py-3.5 px-3">
                                <span class="font-bold text-emerald-600 dark:text-emerald-400 block">{{ $wv->house?->block_and_number ?? 'Belum Terdata' }}</span>
                                <span class="text-[10px] text-slate-400 truncate max-w-[180px] block">{{ $wv->address_ktp ?? ($wv->house?->full_address ?? '-') }}</span>
                            </td>
                            <td class="py-3.5 px-3 font-mono text-[11px]">
                                @if($wv->rfid_tag)
                                    <span class="px-2 py-0.5 rounded-md bg-cyan-50 dark:bg-cyan-950/70 text-cyan-700 dark:text-cyan-300 font-bold border border-cyan-200 dark:border-cyan-800">
                                        {{ $wv->rfid_tag }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Belum digenerate</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $wv->verification_badge['badge'] }}">
                                    {{ $wv->verification_badge['icon'] }} {{ $wv->verification_badge['label'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-3 text-right">
                                @if($wv->verification_status === 'pending_rw' || $wv->verification_status === 'unverified')
                                    <div class="flex items-center justify-end gap-2">
                                        <form action="{{ route('rw.verifications.approve', $wv->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-xs transition flex items-center gap-1 cursor-pointer" title="ACC & Aktifkan RFID ke Pos Satpam">
                                                <span>✓ ACC KTP</span>
                                            </button>
                                        </form>

                                        @if($wv->verification_status === 'pending_rw')
                                            <form action="{{ route('rw.verifications.reject', $wv->id) }}" method="POST" @submit.prevent="window.ewargaConfirmSubmit($el, { title: 'Tolak Berkas KTP?', message: 'Tolak berkas KTP ini dan minta warga perbaiki foto/data?', confirmText: 'Ya, Tolak Berkas', tone: 'danger' })">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-rose-100 text-slate-600 hover:text-rose-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-rose-950 font-bold text-xs transition-colors cursor-pointer">
                                                    <span>✕ Tolak</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center justify-end gap-1">
                                        <span>✓ Aktif di Satpam</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 text-slate-400">Belum ada warga terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Log Palang Pintu Terkini (Lebar Penuh) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Aktivitas Palang Pintu</h3>
            <a href="{{ route('satpam.console') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">Monitor Pos &rarr;</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($recentGateLogs as $gl)
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full {{ $gl->status === 'granted' ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                        <div>
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $gl->user?->name ?? 'Tamu / Satpam' }}</div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ $gl->reason }}</div>
                        </div>
                    </div>
                    <span class="text-[11px] font-mono text-slate-400 dark:text-slate-500 tabular-nums">{{ $gl->triggered_at->format('H:i:s') }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
