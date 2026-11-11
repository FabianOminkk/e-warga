@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 py-3 sm:py-8 space-y-5 sm:space-y-8" x-data="{ openAddModal: false, openSwapModal: false, selectedScheduleId: null, selectedOfficerName: '' }">

    <!-- Komponen Android Mobile Weather & Fast Status Bar (Tampil di Layar HP) -->
    <x-mobile-android-header :tenant="$tenant" />

    <!-- Header & Mode Lingkungan Switcher -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase tracking-wide {{ ($tenant->environment_type ?? 'cluster') === 'cluster' ? 'bg-blue-100 dark:bg-blue-950/70 text-blue-800 dark:text-blue-300' : 'bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300' }}">
                    {{ ($tenant->environment_type ?? 'cluster') === 'cluster' ? '🏢 Mode Cluster / Perumahan' : '🏘️ Mode Perkampungan / Desa' }}
                </span>
                <span class="text-xs text-slate-400 dark:text-slate-500">•</span>
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ $tenant->name ?? 'Cluster Melati Mas' }}</span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white mt-1">Siap Satpam & Jadwal Keamanan Bulan Ini</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Transparansi petugas satpam yang berdinas dan regu ronda malam (Siskamling) warga.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Tombol Beralih Mode Lingkungan (HANYA HAK AKSES SUPER ADMIN PLATFORM) -->
            @if(auth()->user()?->isSuperAdmin())
                <form action="{{ route('environment.toggle') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 rounded-xl border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-950/60 hover:bg-amber-100 text-amber-900 dark:text-amber-200 font-extrabold text-xs shadow-xs transition flex items-center gap-2 cursor-pointer" title="Khusus Super Admin: Ganti Karakter Lingkungan">
                        <span>⚡ Ganti Mode: {{ ($tenant->environment_type ?? 'cluster') === 'cluster' ? 'Beralih ke Perkampungan' : 'Beralih ke Cluster' }}</span>
                    </button>
                </form>
            @endif

            <!-- Tombol Tambah Jadwal Baru (Khusus Admin / Pengurus / Satpam, BUKAN Warga Biasa) -->
            @if(in_array(auth()->user()?->role ?? '', ['super_admin', 'ketua_rw', 'ketua_rt', 'satpam']))
                <button @click="openAddModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition flex items-center gap-2 cursor-pointer">
                    <span>➕ Tambah Jadwal Dinas</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Banner Siapa yang Bertugas / Jaga Hari Ini? -->
    <div class="bg-gradient-to-r from-[#1B365D] to-slate-900 rounded-3xl p-6 lg:p-8 text-white shadow-xl relative overflow-hidden border border-slate-800">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="px-3 py-1 bg-cyan-400/20 text-cyan-300 font-bold text-xs rounded-full border border-cyan-400/30 uppercase tracking-widest">
                    PETUGAS & RONDA HARI INI ({{ now()->isoFormat('dddd, D MMMM Y') }})
                </span>
                <h2 class="text-2xl font-black mt-2">Siapa yang Bertugas Siaga Hari Ini?</h2>
                <p class="text-xs text-slate-300 mt-1">Warga dapat langsung memantau keamanan lingkungan dan menghubungi pos jaga.</p>
            </div>

            <!-- Kartu Petugas Hari Ini -->
            <div class="flex flex-wrap gap-4">
                @forelse($todaySchedules as $ts)
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl flex items-center gap-4 min-w-[260px]">
                        <div class="w-12 h-12 rounded-xl {{ $ts->schedule_type === 'satpam_shift' ? 'bg-cyan-500/20 text-cyan-300' : 'bg-amber-500/20 text-amber-300' }} flex items-center justify-center text-2xl font-bold">
                            {{ $ts->schedule_type === 'satpam_shift' ? '🛡️' : '🔦' }}
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-extrabold text-white">{{ $ts->officer->name ?? 'Petugas' }}</div>
                            <div class="text-xs text-cyan-200 font-semibold">{{ $ts->shift_name }}</div>
                            <div class="text-[11px] text-slate-300 mt-0.5">{{ $ts->formatted_time }}</div>
                        </div>
                        @if($ts->status === 'present')
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 ring-4 ring-emerald-400/30" title="Sedang Siaga di Pos"></span>
                        @endif
                    </div>
                @empty
                    <div class="bg-white/10 border border-white/20 p-4 rounded-2xl text-xs text-slate-300 italic">
                        Belum ada petugas terjadwal hari ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Filter & Navigasi Jadwal Bulanan -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Tabs Tipe Jadwal -->
        <div class="flex items-center gap-2">
            <a href="{{ route('schedules.index', ['month' => $month]) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ !$type ? 'bg-slate-900 dark:bg-slate-700 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                Semua Jadwal
            </a>
            <a href="{{ route('schedules.index', ['type' => 'satpam_shift', 'month' => $month]) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $type === 'satpam_shift' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                🛡️ Shift Satpam (Cluster)
            </a>
            <a href="{{ route('schedules.index', ['type' => 'ronda_warga', 'month' => $month]) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $type === 'ronda_warga' ? 'bg-amber-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                🔦 Ronda Warga (Siskamling)
            </a>
        </div>

        <!-- Filter Bulan -->
        <form action="{{ route('schedules.index') }}" method="GET" class="flex items-center gap-2">
            @if($type)
                <input type="hidden" name="type" value="{{ $type }}">
            @endif
            <label class="text-xs font-bold text-slate-500 dark:text-slate-400">Pilih Bulan:</label>
            <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 outline-hidden">
        </form>
    </div>

    <!-- Tabel Daftar Jadwal Lengkap Bulan Ini -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Agenda Penugasan Keamanan (Periode {{ Carbon\Carbon::parse($month)->isoFormat('MMMM Y') }})</h3>
            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total: {{ $schedules->count() }} Jadwal Dinas</span>
        </div>

        @if($schedules->isEmpty())
            <div class="text-center py-12 text-slate-400 dark:text-slate-500">
                <span class="text-4xl block mb-2">📅</span>
                <p class="text-sm font-semibold">Belum ada jadwal penugasan keamanan untuk bulan ini.</p>
                <button @click="openAddModal = true" class="mt-3 text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                    + Tambahkan Jadwal Pertama Sekarang
                </button>
            </div>
        @else
            <div class="overflow-x-auto -mx-2 sm:mx-0 px-2 sm:px-0">
                <table class="w-full text-left text-xs min-w-[620px]">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                            <th class="py-3">Tanggal Dinas</th>
                            <th class="py-3">Nama Petugas / Warga</th>
                            <th class="py-3">Tipe & Nama Shift</th>
                            <th class="py-3">Jam Jaga</th>
                            <th class="py-3">Status Kehadiran</th>
                            <th class="py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-sans">
                        @foreach($schedules as $s)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3.5 font-bold text-slate-900 dark:text-white">
                                    {{ $s->duty_date->isoFormat('dddd, D MMMM Y') }}
                                    @if($s->duty_date->isToday())
                                        <span class="ml-1 px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 text-[10px] font-black">HARI INI</span>
                                    @endif
                                </td>
                                <td class="py-3.5 font-extrabold text-slate-800 dark:text-slate-200">
                                    <div class="flex items-center gap-2">
                                        <span>{{ $s->officer->name ?? 'Petugas' }}</span>
                                        @if($s->officer?->house)
                                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-normal">({{ $s->officer->house->block_and_number }})</span>
                                        @endif
                                    </div>
                                    @if($s->replacement)
                                        <div class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold mt-0.5">
                                            🔄 Digantikan oleh: {{ $s->replacement->name }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3.5">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase {{ $s->schedule_type === 'satpam_shift' ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800' }}">
                                        {{ $s->shift_name }}
                                    </span>
                                </td>
                                <td class="py-3.5 font-mono text-slate-600 dark:text-slate-300">{{ $s->formatted_time }}</td>
                                <td class="py-3.5">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold {{ $s->status_badge['class'] }}">
                                        {{ $s->status_badge['label'] }}
                                    </span>
                                    @if($s->checked_in_at)
                                        <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono mt-0.5">Absen: {{ $s->checked_in_at->format('H:i') }} WIB</div>
                                    @endif
                                </td>
                                <td class="py-3.5 text-right space-x-1.5">
                                    @if($s->status === 'scheduled')
                                        <!-- Tombol Absen Hadir -->
                                        <form action="{{ route('schedules.checkin', $s->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] rounded-lg shadow-xs transition" title="Catat kehadiran petugas di pos">
                                                ✅ Hadir
                                            </button>
                                        </form>

                                        <!-- Tombol Ajukan Tukar Jadwal -->
                                        <button @click="openSwapModal = true; selectedScheduleId = '{{ $s->id }}'; selectedOfficerName = '{{ $s->officer->name ?? 'Petugas' }}'" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-[11px] rounded-lg transition" title="Ajukan penggantian/tukar shift dinas">
                                            🔄 Tukar
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- MODAL 1: Tambah Jadwal Dinas Baru -->
    <div x-show="openAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs">
        <div @click.away="openAddModal = false" class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Tambah Jadwal Keamanan Baru</h3>
                <button @click="openAddModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-bold">&times;</button>
            </div>

            <form action="{{ route('schedules.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Tipe Jadwal</label>
                        <select name="schedule_type" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 outline-hidden font-bold">
                            <option value="satpam_shift">🛡️ Shift Satpam (Cluster)</option>
                            <option value="ronda_warga">🔦 Ronda Warga (Kampung)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Tanggal Tugas</label>
                        <input type="date" name="duty_date" value="{{ now()->format('Y-m-d') }}" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 outline-hidden font-bold">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Pilih Petugas Satpam / Warga</label>
                    <select name="user_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 outline-hidden">
                        <optgroup label="Petugas Satpam Pos:">
                            @foreach($satpamUsers as $sat)
                                <option value="{{ $sat->id }}">{{ $sat->name }} (Satpam)</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Warga (Peserta Ronda Siskamling):">
                            @foreach($wargaUsers as $w)
                                <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->house?->block_and_number ?? 'Warga' }})</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Nama Shift / Regu</label>
                    <input type="text" name="shift_name" placeholder="Misal: Shift Pagi (Pos Timur) atau Regu Ronda Malam Kamis" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 outline-hidden">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Jam Mulai</label>
                        <input type="time" name="start_time" value="15:00" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 outline-hidden font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Jam Selesai</label>
                        <input type="time" name="end_time" value="23:00" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 outline-hidden font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Catatan Tugas (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Titik patroli / perlengkapan..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 outline-hidden"></textarea>
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="button" @click="openAddModal = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-xs transition">Batal</button>
                    <button type="submit" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md transition">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Ajukan Tukar Jadwal -->
    <div x-show="openSwapModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs">
        <div @click.away="openSwapModal = false" class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Ajukan Tukar Jadwal Dinas</h3>
                <button @click="openSwapModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-bold">&times;</button>
            </div>

            <p class="text-xs text-slate-500 dark:text-slate-400">
                Tukar jadwal dinas atas nama <strong class="text-slate-800 dark:text-slate-200" x-text="selectedOfficerName"></strong> dengan rekan/tetangga lain:
            </p>

            <form :action="'{{ url('/jadwal-keamanan') }}/' + selectedScheduleId + '/tukar'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Pilih Rekan Pengganti</label>
                    <select name="replacement_user_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 outline-hidden">
                        @foreach($wargaUsers as $w)
                            <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->house?->block_and_number ?? 'Warga' }})</option>
                        @endforeach
                        @foreach($satpamUsers as $sat)
                            <option value="{{ $sat->id }}">{{ $sat->name }} (Satpam)</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Alasan Penukaran</label>
                    <input type="text" name="notes" placeholder="Dinas luar kota / acara keluarga..." required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 outline-hidden">
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="button" @click="openSwapModal = false" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-xs transition">Batal</button>
                    <button type="submit" class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold text-xs shadow-md transition">Konfirmasi Tukar</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
