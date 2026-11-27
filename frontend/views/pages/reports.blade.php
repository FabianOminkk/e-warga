@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 py-3 sm:py-8 space-y-5 sm:space-y-8" x-data="reportsApp()">

    <x-mobile-android-header :tenant="$tenant" :user="$user" />

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
        <div class="flex items-start sm:items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 flex items-center justify-center text-2xl shrink-0">
                📣
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white text-balance">
                    Laporan, Aspirasi &amp; Pengaduan Warga
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    Saluran resmi aduan fasilitas, kebersihan, keamanan, dan usulan warga untuk {{ $tenant->name ?? 'lingkungan' }}.
                </p>
            </div>
        </div>

        <button type="button" @click="openCreateModal = true" class="px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-sm transition-colors flex items-center justify-center gap-2 cursor-pointer touch-press">
            <span>+ Kirim Laporan / Aspirasi</span>
        </button>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs space-y-1">
            <div class="font-black">Periksa kembali data laporan:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-5">
        <a href="{{ route('reports.index', ['status' => 'all']) }}" class="p-4 sm:p-5 rounded-2xl border transition-colors {{ $filterStatus === 'all' ? 'bg-slate-900 text-white border-slate-900 dark:bg-emerald-950/50 dark:border-emerald-700' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800' }}">
            <div class="text-[11px] font-bold uppercase tracking-wider {{ $filterStatus === 'all' ? 'text-slate-300' : 'text-slate-400' }}">Total Masuk</div>
            <div class="text-2xl font-black mt-1 tabular-nums">{{ $stats['total'] }}</div>
        </a>
        <a href="{{ route('reports.index', ['status' => 'pending']) }}" class="p-4 sm:p-5 rounded-2xl border transition-colors {{ $filterStatus === 'pending' ? 'bg-amber-50 dark:bg-amber-950/50 border-amber-400' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800' }}">
            <div class="text-[11px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Menunggu</div>
            <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1 tabular-nums">{{ $stats['pending'] }}</div>
        </a>
        <a href="{{ route('reports.index', ['status' => 'in_progress']) }}" class="p-4 sm:p-5 rounded-2xl border transition-colors {{ $filterStatus === 'in_progress' ? 'bg-blue-50 dark:bg-blue-950/50 border-blue-400' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800' }}">
            <div class="text-[11px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Diproses</div>
            <div class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-1 tabular-nums">{{ $stats['in_progress'] }}</div>
        </a>
        <a href="{{ route('reports.index', ['status' => 'resolved']) }}" class="p-4 sm:p-5 rounded-2xl border transition-colors {{ $filterStatus === 'resolved' ? 'bg-emerald-50 dark:bg-emerald-950/50 border-emerald-400' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800' }}">
            <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Selesai</div>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 tabular-nums">{{ $stats['resolved'] }}</div>
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode, judul, atau lokasi laporan…" class="sm:col-span-2 px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white">
            <select name="type" class="px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-semibold text-slate-800 dark:text-slate-200">
                <option value="all" @selected($filterType === 'all')>Semua Kategori</option>
                <option value="pengaduan" @selected($filterType === 'pengaduan')>Pengaduan Warga</option>
                <option value="aspirasi" @selected($filterType === 'aspirasi')>Aspirasi &amp; Usulan</option>
                <option value="kerusakan_fasilitas" @selected($filterType === 'kerusakan_fasilitas')>Kerusakan Fasilitas</option>
                <option value="kebersihan" @selected($filterType === 'kebersihan')>Kebersihan &amp; Sampah</option>
                <option value="keamanan" @selected($filterType === 'keamanan')>Laporan Keamanan</option>
            </select>
            <div class="flex gap-2">
                <input type="hidden" name="status" value="{{ $filterStatus }}">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-slate-900 dark:bg-emerald-600 text-white font-bold cursor-pointer">Filter</button>
                @if($search !== '' || $filterType !== 'all' || $filterStatus !== 'all')
                    <a href="{{ route('reports.index') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 font-bold text-slate-600 dark:text-slate-300">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Feed Laporan & Aspirasi (Responsive Card Grid untuk Mobile & Desktop) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        @php
            $isManager = in_array($user->role, ['super_admin', 'ketua_rw', 'ketua_rt', 'sekretaris', 'bendahara', 'satpam', 'pengurus_rt']);
        @endphp
        @forelse($reports as $report)
            @php
                $sBadge = $report->status_badge;
                $pBadge = $report->priority_badge;
                $reporterLabel = ($report->is_anonymous && !$isManager && $report->user_id !== $user->id)
                    ? 'Warga Anonim'
                    : ($report->user?->name ?? 'Warga');
            @endphp
            <article class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-xs flex flex-col justify-between gap-4">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 font-mono text-[11px] font-bold text-slate-700 dark:text-slate-300 tabular-nums">
                                {{ $report->report_code }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase {{ $pBadge['color'] }}">
                                {{ $pBadge['label'] }}
                            </span>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-black border {{ $sBadge['color'] }}">
                            {{ $sBadge['label'] }}
                        </span>
                    </div>

                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                            {{ $report->type_label }} &bull; {{ $report->category }}
                        </div>
                        <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-0.5 text-pretty">
                            {{ $report->title }}
                        </h2>
                    </div>

                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ $report->description }}
                    </p>

                    @if($report->photo_url)
                        <a href="{{ $report->photo_url }}" target="_blank" class="block rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-slate-950">
                            <img src="{{ $report->photo_url }}" alt="Foto lampiran {{ $report->title }}" class="w-full h-44 object-cover">
                        </a>
                    @endif

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-slate-500 dark:text-slate-400 pt-1">
                        <span>👤 Pelapor: <strong class="text-slate-700 dark:text-slate-200">{{ $reporterLabel }}</strong></span>
                        @if($report->location)
                            <span>📍 Lokasi: <strong class="text-slate-700 dark:text-slate-200">{{ $report->location }}</strong></span>
                        @endif
                        <span class="tabular-nums">🕒 {{ $report->created_at?->diffForHumans() }}</span>
                    </div>

                    @if($report->official_response)
                        <div class="p-3.5 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-xs space-y-1">
                            <div class="font-extrabold text-emerald-800 dark:text-emerald-300 flex items-center justify-between">
                                <span>✓ Tanggapan Pengurus {{ $report->handler ? '(' . $report->handler->name . ')' : '' }}</span>
                                <span class="text-[10px] font-mono tabular-nums">{{ $report->responded_at?->format('d/m H:i') }}</span>
                            </div>
                            <p class="text-slate-700 dark:text-slate-200 leading-relaxed">{{ $report->official_response }}</p>
                        </div>
                    @endif
                </div>

                @if($isManager)
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end">
                        <button
                            type="button"
                            @click="openRespond({{ Js::from([
                                'id' => $report->id,
                                'code' => $report->report_code,
                                'title' => $report->title,
                                'status' => $report->status,
                                'official_response' => $report->official_response ?? '',
                            ]) }})"
                            class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white font-bold text-xs cursor-pointer transition-colors"
                        >
                            Tindak Lanjuti / Update Status
                        </button>
                    </div>
                @endif
            </article>
        @empty
            <div class="col-span-full bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-12 text-center space-y-2">
                <div class="text-4xl">📭</div>
                <div class="font-black text-slate-800 dark:text-slate-200">Belum Ada Laporan / Aspirasi</div>
                <p class="text-xs text-slate-500">Klik tombol "+ Kirim Laporan / Aspirasi" untuk menyampaikan informasi atau usulan Anda.</p>
            </div>
        @endforelse
    </div>

    <!-- MODAL: KIRIM LAPORAN / ASPIRASI WARGA -->
    <div x-show="openCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs overscroll-contain">
        <div @click.away="openCreateModal = false" class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-black text-slate-900 dark:text-white text-base">Kirim Laporan / Aspirasi Warga</h3>
                <button type="button" @click="openCreateModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Jenis Laporan</label>
                        <select name="type" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-semibold text-slate-900 dark:text-white">
                            <option value="pengaduan">Pengaduan Warga</option>
                            <option value="aspirasi">Aspirasi / Usulan Program</option>
                            <option value="kerusakan_fasilitas">Kerusakan Fasilitas Umum</option>
                            <option value="kebersihan">Kebersihan / Sampah / Got</option>
                            <option value="keamanan">Laporan Keamanan Lingkungan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Prioritas</label>
                        <select name="priority" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-semibold text-slate-900 dark:text-white">
                            <option value="medium">Normal</option>
                            <option value="low">Rendah / Masukan</option>
                            <option value="high">Prioritas Tinggi</option>
                            <option value="urgent">Mendesak</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Bidang / Kategori</label>
                        <input type="text" name="category" required value="Fasilitas Lingkungan" placeholder="Misal: Penerangan, Sampah, Got…" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Lokasi Kejadian</label>
                        <input type="text" name="location" placeholder="Misal: Depan Blok B No. 05…" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Judul Singkat</label>
                    <input type="text" name="title" required placeholder="Misal: Got tersumbat di pertigaan Blok B…" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-semibold text-slate-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Uraian Detail</label>
                    <textarea name="description" rows="3" required placeholder="Jelaskan kronologi atau usulan Anda secara jelas…" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white leading-relaxed"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Foto Lampiran (Opsional)</label>
                    <input type="file" name="photo" accept="image/*" class="w-full text-xs text-slate-500 cursor-pointer file:cursor-pointer file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700">
                </div>

                <label class="flex items-center gap-2 text-xs cursor-pointer">
                    <input type="checkbox" name="is_anonymous" value="1" class="rounded text-emerald-600">
                    <span class="font-semibold text-slate-700 dark:text-slate-300">Sembunyikan nama saya dari sesama warga (Anonim)</span>
                </label>

                <div class="pt-2 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openCreateModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 cursor-pointer">Batal</button>
                    <button type="submit" :disabled="isSubmitting" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs cursor-pointer">
                        <span x-text="isSubmitting ? 'Mengirim…' : 'Kirim Sekarang'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: TANGGAPAN PENGURUS RT/RW -->
    <div x-show="openRespondModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs overscroll-contain">
        <div @click.away="openRespondModal = false" class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="font-black text-slate-900 dark:text-white text-base">Tindak Lanjut Pengurus</h3>
                    <p class="text-xs text-slate-500" x-text="respondForm.code + ' • ' + respondForm.title"></p>
                </div>
                <button type="button" @click="openRespondModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>
            <form :action="'{{ url('/laporan-warga') }}/' + respondForm.id + '/respond'" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Status Penanganan</label>
                    <select name="status" x-model="respondForm.status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-semibold text-slate-900 dark:text-white">
                        <option value="pending">Menunggu Tindak Lanjut</option>
                        <option value="in_progress">Sedang Diproses</option>
                        <option value="resolved">Selesai Ditangani</option>
                        <option value="rejected">Ditolak / Tidak Valid</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Tanggapan Resmi Pengurus</label>
                    <textarea name="official_response" x-model="respondForm.official_response" rows="3" required placeholder="Tuliskan hasil pengecekan atau jadwal perbaikan…" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white"></textarea>
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="openRespondModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs cursor-pointer">Simpan Tanggapan</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function reportsApp() {
    return {
        openCreateModal: false,
        openRespondModal: false,
        respondForm: { id: null, code: '', title: '', status: 'in_progress', official_response: '' },
        openRespond(data) {
            this.respondForm = { ...data };
            this.openRespondModal = true;
        }
    };
}
</script>
@endsection
