@extends('layouts.app')

@section('content')
<div class="space-y-6 sm:space-y-8" x-data="{ showRequestModal: false, showRejectModal: false, selectedLetterId: null, selectedLetterTitle: '', previewLetter: null }">
    <!-- Komponen Android Mobile Weather & Fast Status Bar (Tampil di Layar HP) -->
    <x-mobile-android-header />

    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-950/70 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl font-bold shadow-xs">
                    📜
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Layanan Surat Pengantar RT Online</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Pengurusan surat pengantar digital ber-QR Code untuk KTP, KK, Domisili, SKTM, dan Keperluan Kelurahan.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button @click="showRequestModal = true" class="px-4 py-2.5 rounded-xl font-semibold text-sm bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Ajukan Surat Baru
            </button>
        </div>
    </div>

    <!-- KPI Summary Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('letters.index', ['status' => 'all']) }}" class="p-5 rounded-2xl border transition {{ $filterStatus === 'all' ? 'bg-blue-50 dark:bg-blue-950/40 border-blue-300 dark:border-blue-800 ring-2 ring-blue-500' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-blue-300' }}">
            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Pengajuan</div>
            <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $stats['total'] }}</div>
            <div class="text-[11px] text-blue-600 dark:text-blue-400 font-medium mt-1">Semua Riwayat</div>
        </a>

        <a href="{{ route('letters.index', ['status' => 'pending']) }}" class="p-5 rounded-2xl border transition {{ $filterStatus === 'pending' ? 'bg-amber-50 dark:bg-amber-950/40 border-amber-300 dark:border-amber-800 ring-2 ring-amber-500' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-amber-300' }}">
            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Menunggu TTD RT</div>
            <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ $stats['pending'] }}</div>
            <div class="text-[11px] text-amber-600 dark:text-amber-400 font-medium mt-1">Perlu Persetujuan</div>
        </a>

        <a href="{{ route('letters.index', ['status' => 'approved']) }}" class="p-5 rounded-2xl border transition {{ $filterStatus === 'approved' ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-800 ring-2 ring-emerald-500' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-emerald-300' }}">
            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Disetujui & Diterbitkan</div>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $stats['approved'] }}</div>
            <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium mt-1">Bisa Diunduh / Cetak</div>
        </a>

        <a href="{{ route('letters.index', ['status' => 'rejected']) }}" class="p-5 rounded-2xl border transition {{ $filterStatus === 'rejected' ? 'bg-rose-50 dark:bg-rose-950/40 border-rose-300 dark:border-rose-800 ring-2 ring-rose-500' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-rose-300' }}">
            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Ditolak</div>
            <div class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">{{ $stats['rejected'] }}</div>
            <div class="text-[11px] text-rose-600 dark:text-rose-400 font-medium mt-1">Perlu Revisi</div>
        </a>
    </div>

    <!-- Letter Table Section -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Daftar Berkas Pengajuan Surat</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Pengurus RT dapat menyetujui langsung dan sistem menerbitkan nomor surat serta QR Code autentik.
                </p>
            </div>

            <!-- Filter tabs -->
            <div class="flex items-center gap-1.5">
                <a href="{{ route('letters.index', ['status' => 'all']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $filterStatus === 'all' ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">Semua</a>
                <a href="{{ route('letters.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $filterStatus === 'pending' ? 'bg-amber-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">Menunggu</a>
                <a href="{{ route('letters.index', ['status' => 'approved']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $filterStatus === 'approved' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">Disetujui</a>
            </div>
        </div>

        <div class="overflow-x-auto -mx-2 sm:mx-0 px-2 sm:px-0">
            <table class="w-full text-left text-sm min-w-[580px]">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 text-xs uppercase font-bold tracking-wider">
                    <tr>
                        <th class="py-3 px-6">No. Surat / Pemohon</th>
                        <th class="py-3 px-6">Jenis Surat & Keperluan</th>
                        <th class="py-3 px-6">Tanggal Pengajuan</th>
                        <th class="py-3 px-6">Status & TTD</th>
                        <th class="py-3 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($letters as $item)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-900 dark:text-white">
                                    {{ $item->letter_number ?? '(Nomor Diterbitkan Saat Disetujui)' }}
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    👤 {{ $item->applicant_name }} &bull; {{ $item->house_block }}
                                </div>
                                <div class="text-[11px] text-slate-400">NIK: {{ $item->nik }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $item->letter_type_title }}
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1">
                                    Keperluan: {{ $item->purpose }}
                                </div>
                                @if($item->supporting_data)
                                    <div class="text-[11px] text-blue-500 mt-0.5">Catatan: {{ $item->supporting_data }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-500 dark:text-slate-400">
                                {{ $item->created_at->format('d M Y, H:i') }} WIB
                            </td>
                            <td class="py-4 px-6">
                                @php $badge = $item->status_badge; @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $badge['color'] }}">
                                    {{ $badge['label'] }}
                                </span>
                                @if($item->status === 'approved')
                                    <div class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1">
                                        Ditandatangani: {{ $item->signed_at?->format('d/m/Y') }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        {{ $item->qr_verification_code }}
                                    </div>
                                @elseif($item->status === 'rejected')
                                    <div class="text-[11px] text-rose-600 dark:text-rose-400 mt-1">
                                        Alasan: {{ $item->rejection_reason }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right space-x-2">
                                @if($item->status === 'pending')
                                    <!-- RT 1-Click Approve -->
                                    <form action="{{ route('letters.approve', $item->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-xs">
                                            ✓ Setujui & TTD
                                        </button>
                                    </form>
                                    <button 
                                        @click="showRejectModal = true; selectedLetterId = '{{ $item->id }}'; selectedLetterTitle = '{{ $item->letter_type_title }} - {{ $item->applicant_name }}'" 
                                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-950/60 dark:text-rose-300 transition"
                                    >
                                        Tolak
                                    </button>
                                @elseif($item->status === 'approved')
                                    <a 
                                        href="{{ route('letters.verify', $item->qr_verification_code) }}" 
                                        target="_blank" 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-950/60 dark:text-blue-300 transition"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Lihat Surat / Cetak
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                Tidak ada pengajuan surat pada kategori ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: Ajukan Surat Pengantar Warga -->
    <div x-show="showRequestModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs" x-cloak>
        <div @click.away="showRequestModal = false" class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Form Pengajuan Surat Pengantar RT</h3>
                <button @click="showRequestModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form action="{{ route('letters.store') }}" method="POST" class="space-y-4 mt-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Jenis Surat Pengantar</label>
                    <select name="letter_type" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                        <option value="pengantar_ktp">Surat Pengantar Pembuatan / Rekam KTP-el</option>
                        <option value="pengantar_kk">Surat Pengantar Pembuatan Kartu Keluarga (KK)</option>
                        <option value="domisili">Surat Keterangan Domisili Tempat Tinggal</option>
                        <option value="sktm">Surat Keterangan Tidak Mampu (SKTM / Beasiswa)</option>
                        <option value="izin_usaha">Surat Pengantar Keterangan Usaha (SKU)</option>
                        <option value="pengantar_nikah">Surat Pengantar Nikah (Model N1-N4)</option>
                        <option value="kematian">Surat Keterangan Kematian</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Nama Pemohon</label>
                        <input type="text" name="applicant_name" value="Bambang Pamungkas" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">NIK (16 Digit)</label>
                        <input type="text" name="nik" value="3216012903820001" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Alamat Rumah / Blok</label>
                    <input type="text" name="house_block" value="Blok C No. 12" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Keperluan Pengurusan</label>
                    <textarea name="purpose" rows="2" required placeholder="Contoh: Mengurus perpanjangan SIM & KTP di kantor Disdukcapil" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Keterangan Tambahan / Dokumen Pendukung (Opsional)</label>
                    <input type="text" name="supporting_data" placeholder="Contoh: Sudah melampirkan fotokopi akta lahir" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                    <button type="button" @click="showRequestModal = false" class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white">Kirim Pengajuan ke RT</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Tolak Pengajuan -->
    <div x-show="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs" x-cloak>
        <div @click.away="showRejectModal = false" class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tolak Pengajuan Surat</h3>
                <button @click="showRejectModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form :action="'/surat-pengantar/' + selectedLetterId + '/reject'" method="POST" class="space-y-4 mt-4">
                @csrf
                <p class="text-xs text-slate-500" x-text="selectedLetterTitle"></p>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Alasan Penolakan</label>
                    <textarea name="rejection_reason" rows="3" required placeholder="Contoh: Dokumen KK lampiran belum lengkap atau ada tunggakan IPL" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"></textarea>
                </div>
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                    <button type="button" @click="showRejectModal = false" class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-sm font-semibold bg-rose-600 hover:bg-rose-700 text-white">Tolak Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

