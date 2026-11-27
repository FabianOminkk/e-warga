@extends('layouts.app')

@section('content')
<div class="max-w-[1440px] mx-auto px-2 sm:px-6 lg:px-8 py-3 sm:py-6 space-y-5 sm:space-y-6" x-data="residentManager()">

    <x-mobile-android-header :tenant="$tenant" />

    <!-- Header Page -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
        <div class="flex items-start sm:items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-2xl shrink-0">
                👨‍👩‍👧‍👦
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white text-balance">
                    Manajemen Data Warga, Kartu Keluarga &amp; Peran Akun
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    Kelola unit rumah, pengelompokan No.&nbsp;KK, anggota keluarga, dan hak akses role di {{ $tenant->name ?? 'lingkungan' }}.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('residents.export') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-colors flex items-center gap-1.5">
                <span>📥 Ekspor CSV</span>
            </a>
            <button type="button" @click="openHouseModal = true" class="px-3.5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-bold text-xs transition-colors flex items-center gap-1.5 cursor-pointer touch-press">
                <span>+ Rumah / KK Baru</span>
            </button>
            <button type="button" @click="openAddResidentModal = true" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-sm transition-colors flex items-center gap-1.5 cursor-pointer touch-press">
                <span>+ Tambah Warga / Akun</span>
            </button>
        </div>
    </div>

    <!-- Validation Error Notice -->
    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs space-y-1">
            <div class="font-black">Periksa kembali data yang dikirim:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Unit Rumah</div>
            <div class="text-2xl font-black text-slate-900 dark:text-white mt-1 tabular-nums">{{ $stats['total_houses'] }}</div>
            <div class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5">Blok &amp; Nomor Hunian</div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kartu Keluarga (KK)</div>
            <div class="text-2xl font-black text-[#1B365D] dark:text-cyan-400 mt-1 tabular-nums">{{ $stats['total_kk'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Nomor KK Terdata</div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Warga &amp; Staf</div>
            <div class="text-2xl font-black text-slate-900 dark:text-white mt-1 tabular-nums">{{ $stats['total_residents'] }} <span class="text-xs font-medium text-slate-400">/ {{ $stats['total_users'] }} Akun</span></div>
            <div class="text-[11px] text-slate-500 mt-0.5">Penghuni &amp; Pengurus</div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">KTP Terverifikasi RW</div>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 tabular-nums">{{ $stats['verified_warga'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Akses Smart Gate Aktif</div>
        </div>
    </div>

    <!-- Filter & Tab Bar -->
    <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold">
                <button type="button" @click="activeTab = 'kk'" class="px-4 py-2 rounded-lg transition-colors cursor-pointer" :class="activeTab === 'kk' ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-xs' : 'text-slate-600 dark:text-slate-400'">
                    🏠 Per Kartu Keluarga &amp; Rumah
                </button>
                <button type="button" @click="activeTab = 'users'" class="px-4 py-2 rounded-lg transition-colors cursor-pointer" :class="activeTab === 'users' ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-xs' : 'text-slate-600 dark:text-slate-400'">
                    👥 Daftar Warga &amp; Role Akun
                </button>
            </div>

            <form method="GET" action="{{ route('residents.index') }}" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="tab" :value="activeTab">
                <input type="text" name="search" value="{{ $search }}" spellcheck="false" placeholder="Cari nama, NIK, No. KK, atau blok…" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden min-w-[210px]">
                <select name="role" class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs font-semibold text-slate-800 dark:text-slate-200">
                    <option value="all" @selected($filterRole === 'all')>Semua Role</option>
                    <option value="warga" @selected($filterRole === 'warga')>Warga</option>
                    <option value="ketua_rt" @selected($filterRole === 'ketua_rt')>Ketua RT</option>
                    <option value="ketua_rw" @selected($filterRole === 'ketua_rw')>Ketua RW</option>
                    <option value="sekretaris" @selected($filterRole === 'sekretaris')>Sekretaris</option>
                    <option value="bendahara" @selected($filterRole === 'bendahara')>Bendahara</option>
                    <option value="satpam" @selected($filterRole === 'satpam')>Satpam</option>
                </select>
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs cursor-pointer">
                    Cari
                </button>
                @if($search !== '' || $filterRole !== 'all' || $filterVerification !== 'all')
                    <a href="{{ route('residents.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-600 dark:text-slate-300">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- TAB 1: KARTU KELUARGA (KK) & UNIT RUMAH -->
        <div x-show="activeTab === 'kk'" class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
            @forelse($houses as $house)
                <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/70 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-3 flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-base font-black text-slate-900 dark:text-white">{{ $house->block_and_number }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $house->occupancy_status === 'occupied' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300' : ($house->occupancy_status === 'rented' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300' : 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300') }}">
                                        {{ $house->occupancy_status === 'occupied' ? 'Dihuni Tetap' : ($house->occupancy_status === 'rented' ? 'Kontrak / Sewa' : 'Kosong') }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $house->full_address ?? '-' }}</p>
                            </div>

                            <button
                                type="button"
                                @click="openEditHouse({{ Js::from([
                                    'id' => $house->id,
                                    'block_name' => $house->block_name,
                                    'house_number' => $house->house_number,
                                    'no_kk' => $house->no_kk,
                                    'full_address' => $house->full_address,
                                    'occupancy_status' => $house->occupancy_status,
                                ]) }})"
                                class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 hover:border-emerald-500 cursor-pointer shrink-0"
                            >
                                Edit KK
                            </button>
                        </div>

                        <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-semibold">Nomor KK:</span>
                            <span class="font-mono font-black text-slate-900 dark:text-cyan-300 tabular-nums">{{ $house->no_kk ?: 'Belum Diisi' }}</span>
                        </div>

                        <div class="space-y-2">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                Anggota Keluarga Terdaftar ({{ $house->residents->count() }} Orang)
                            </div>
                            @forelse($house->residents as $member)
                                <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2 text-xs">
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 dark:text-white truncate flex items-center gap-1.5">
                                            <span>{{ $member->name }}</span>
                                            @if($member->is_family_head)
                                                <span class="px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 text-[9px] font-black">KK</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-mono tabular-nums truncate">
                                            NIK: {{ $member->nik ?? 'Belum Ada' }} &bull; {{ $member->role_label }}
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="openEditResident({{ Js::from([
                                            'id' => $member->id,
                                            'name' => $member->name,
                                            'phone_number' => $member->phone_number,
                                            'nik' => $member->nik,
                                            'no_kk' => $member->no_kk ?? $house->no_kk,
                                            'house_id' => $member->house_id,
                                            'role' => $member->role,
                                            'status' => $member->status,
                                            'is_family_head' => (bool) $member->is_family_head,
                                        ]) }})"
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 dark:bg-slate-800 dark:text-slate-300 text-[11px] font-bold cursor-pointer shrink-0"
                                    >
                                        Ubah
                                    </button>
                                </div>
                            @empty
                                <div class="text-xs text-slate-400 italic py-2">Belum ada anggota keluarga terhubung ke rumah ini.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-10 text-center text-xs text-slate-400">
                    Tidak ada unit rumah / KK yang sesuai pencarian.
                </div>
            @endforelse
        </div>

        <!-- TAB 2: DAFTAR WARGA & MANAJEMEN ROLE AKUN -->
        <div x-show="activeTab === 'users'" x-cloak class="pt-2 space-y-4">
            <!-- Mobile Card Layout -->
            <div class="block lg:hidden space-y-3">
                @forelse($residents as $res)
                    <div class="p-4 rounded-2xl bg-slate-50/70 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="font-black text-slate-900 dark:text-white text-sm">{{ $res->name }}</div>
                                <div class="text-xs text-slate-500">{{ $res->email }} &bull; {{ $res->phone_number ?? '-' }}</div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 shrink-0">
                                {{ strtoupper($res->role) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                                <span class="text-[10px] font-bold text-slate-400 uppercase block">Unit Rumah</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $res->house?->block_and_number ?? 'Non-Rumah' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                                <span class="text-[10px] font-bold text-slate-400 uppercase block">No. KK</span>
                                <span class="font-mono font-bold text-slate-800 dark:text-slate-200 tabular-nums truncate block">{{ $res->no_kk ?? $res->house?->no_kk ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-200/70 dark:border-slate-700/70">
                            <button
                                type="button"
                                @click="openEditResident({{ Js::from([
                                    'id' => $res->id,
                                    'name' => $res->name,
                                    'phone_number' => $res->phone_number,
                                    'nik' => $res->nik,
                                    'no_kk' => $res->no_kk ?? $res->house?->no_kk,
                                    'house_id' => $res->house_id,
                                    'role' => $res->role,
                                    'status' => $res->status,
                                    'is_family_head' => (bool) $res->is_family_head,
                                ]) }})"
                                class="flex-1 min-h-[42px] px-3 py-2 rounded-xl bg-slate-900 text-white dark:bg-slate-700 font-bold text-xs cursor-pointer"
                            >
                                Edit Data &amp; Role
                            </button>

                            @if($res->id !== Auth::id() && !$res->isSuperAdmin())
                                <form action="{{ route('residents.destroy', $res->id) }}" method="POST" onsubmit="return confirm('Hapus data akun warga ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="min-h-[42px] px-3 py-2 rounded-xl bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 font-bold text-xs cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">Tidak ada data warga.</div>
                @endforelse
            </div>

            <!-- Desktop Table Layout -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-3">Nama &amp; Kontak</th>
                            <th class="py-3 px-3">Role Akun</th>
                            <th class="py-3 px-3">NIK &amp; No. KK</th>
                            <th class="py-3 px-3">Unit Rumah</th>
                            <th class="py-3 px-3">Status Gerbang</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($residents as $res)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                                <td class="py-3 px-3">
                                    <div class="font-extrabold text-slate-900 dark:text-white">{{ $res->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $res->email }} &bull; {{ $res->phone_number ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold">{{ $res->role_label }}</span>
                                </td>
                                <td class="py-3 px-3 font-mono tabular-nums">
                                    <div class="text-slate-700 dark:text-slate-200">NIK: {{ $res->nik ?? '-' }}</div>
                                    <div class="text-[11px] text-cyan-700 dark:text-cyan-400">KK: {{ $res->no_kk ?? $res->house?->no_kk ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-bold text-emerald-700 dark:text-emerald-400">{{ $res->house?->block_and_number ?? '-' }}</span>
                                    @if($res->is_family_head)
                                        <span class="block text-[10px] text-indigo-600 dark:text-indigo-400 font-bold">Kepala Keluarga</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $res->verification_badge['badge'] }}">
                                        {{ $res->verification_badge['label'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right space-x-1.5 whitespace-nowrap">
                                    <button
                                        type="button"
                                        @click="openEditResident({{ Js::from([
                                            'id' => $res->id,
                                            'name' => $res->name,
                                            'phone_number' => $res->phone_number,
                                            'nik' => $res->nik,
                                            'no_kk' => $res->no_kk ?? $res->house?->no_kk,
                                            'house_id' => $res->house_id,
                                            'role' => $res->role,
                                            'status' => $res->status,
                                            'is_family_head' => (bool) $res->is_family_head,
                                        ]) }})"
                                        class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-700 dark:bg-slate-700 text-white font-bold text-xs cursor-pointer"
                                    >
                                        Edit Role &amp; KK
                                    </button>
                                    @if($res->id !== Auth::id() && !$res->isSuperAdmin())
                                        <form action="{{ route('residents.destroy', $res->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus data akun warga ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 font-bold text-xs cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">Tidak ada data warga sesuai filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL: TAMBAH UNIT RUMAH / KK -->
    <div x-show="openHouseModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs overscroll-contain">
        <div @click.away="openHouseModal = false" class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-black text-slate-900 dark:text-white text-base">Tambah Unit Rumah &amp; Kartu Keluarga</h3>
                <button type="button" @click="openHouseModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>
            <form action="{{ route('residents.houses.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Blok</label>
                        <input type="text" name="block_name" required placeholder="Blok D…" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">No. Rumah</label>
                        <input type="text" name="house_number" required placeholder="08…" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Nomor Kartu Keluarga (16 Digit)</label>
                    <input type="text" name="no_kk" inputmode="numeric" maxlength="16" minlength="16" pattern="\d{16}" placeholder="327501xxxxxxxxxx…" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-mono tabular-nums text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Status Hunian</label>
                    <select name="occupancy_status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                        <option value="occupied">Dihuni Tetap</option>
                        <option value="rented">Kontrak / Sewa</option>
                        <option value="vacant">Rumah Kosong</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Alamat Lengkap (Opsional)</label>
                    <input type="text" name="full_address" placeholder="Jl. Melati Mas Blok D No. 08…" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="openHouseModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs cursor-pointer">Simpan Rumah</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDIT UNIT RUMAH / KK -->
    <div x-show="openEditHouseModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs overscroll-contain">
        <div @click.away="openEditHouseModal = false" class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-black text-slate-900 dark:text-white text-base">Perbarui Data Rumah &amp; No.&nbsp;KK</h3>
                <button type="button" @click="openEditHouseModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>
            <form :action="'{{ url('/data-warga/houses') }}/' + houseForm.id" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Blok</label>
                        <input type="text" name="block_name" x-model="houseForm.block_name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">No. Rumah</label>
                        <input type="text" name="house_number" x-model="houseForm.house_number" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Nomor Kartu Keluarga (16 Digit)</label>
                    <input type="text" name="no_kk" x-model="houseForm.no_kk" inputmode="numeric" maxlength="16" minlength="16" pattern="\d{16}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-mono tabular-nums text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Status Hunian</label>
                    <select name="occupancy_status" x-model="houseForm.occupancy_status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                        <option value="occupied">Dihuni Tetap</option>
                        <option value="rented">Kontrak / Sewa</option>
                        <option value="vacant">Rumah Kosong</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Alamat Lengkap</label>
                    <input type="text" name="full_address" x-model="houseForm.full_address" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="openEditHouseModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs cursor-pointer">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: TAMBAH WARGA / ANGGOTA KELUARGA -->
    <div x-show="openAddResidentModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs overscroll-contain">
        <div @click.away="openAddResidentModal = false" class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-black text-slate-900 dark:text-white text-base">Tambah Warga / Anggota Keluarga</h3>
                <button type="button" @click="openAddResidentModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>
            <form action="{{ route('residents.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Nama Lengkap</label>
                        <input type="text" name="name" required placeholder="Nama sesuai KTP…" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">No. WhatsApp / HP</label>
                        <input type="tel" name="phone_number" required placeholder="0812xxxxxxxx…" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Email Akun</label>
                        <input type="email" name="email" required placeholder="warga@ewarga.id…" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Role Akun</label>
                        <select name="role" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-semibold text-slate-900 dark:text-white">
                            <option value="warga">Warga Penghuni</option>
                            <option value="ketua_rt">Ketua RT</option>
                            <option value="sekretaris">Sekretaris RT</option>
                            <option value="bendahara">Bendahara RT</option>
                            <option value="satpam">Petugas Satpam</option>
                            @if(in_array(Auth::user()?->role, ['super_admin', 'ketua_rw']))
                                <option value="ketua_rw">Ketua RW</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">NIK (16 Digit)</label>
                        <input type="text" name="nik" inputmode="numeric" maxlength="16" minlength="16" pattern="\d{16}" placeholder="327501xxxxxxxxxx…" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-mono text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Nomor KK (16 Digit)</label>
                        <input type="text" name="no_kk" inputmode="numeric" maxlength="16" minlength="16" pattern="\d{16}" placeholder="327501xxxxxxxxxx…" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-mono text-slate-900 dark:text-white">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Unit Rumah</label>
                    <select name="house_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                        <option value="">-- Tanpa Unit Rumah --</option>
                        @foreach($allHouses as $h)
                            <option value="{{ $h->id }}">{{ $h->block_and_number }} {{ $h->no_kk ? "(KK: {$h->no_kk})" : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-2 pt-1 text-xs">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_family_head" value="1" class="rounded text-emerald-600">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Tandai sebagai Kepala Keluarga</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="approve_immediately" value="1" class="rounded text-emerald-600">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Langsung verifikasi KTP &amp; aktifkan Smart Gate</span>
                    </label>
                </div>
                <div class="pt-2 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openAddResidentModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs cursor-pointer">Simpan Warga</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDIT DATA WARGA & ROLE -->
    <div x-show="openEditResidentModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs overscroll-contain">
        <div @click.away="openEditResidentModal = false" class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-black text-slate-900 dark:text-white text-base">Edit Data Warga, KK &amp; Role Akun</h3>
                <button type="button" @click="openEditResidentModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>
            <form :action="'{{ url('/data-warga/residents') }}/' + residentForm.id" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Nama Lengkap</label>
                        <input type="text" name="name" x-model="residentForm.name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">No. WhatsApp</label>
                        <input type="tel" name="phone_number" x-model="residentForm.phone_number" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">NIK (16 Digit)</label>
                        <input type="text" name="nik" x-model="residentForm.nik" inputmode="numeric" maxlength="16" minlength="16" pattern="\d{16}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-mono text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Nomor KK (16 Digit)</label>
                        <input type="text" name="no_kk" x-model="residentForm.no_kk" inputmode="numeric" maxlength="16" minlength="16" pattern="\d{16}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-mono text-slate-900 dark:text-white">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Unit Rumah</label>
                        <select name="house_id" x-model="residentForm.house_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                            <option value="">-- Tanpa Unit Rumah --</option>
                            @foreach($allHouses as $h)
                                <option value="{{ $h->id }}">{{ $h->block_and_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Role / Jabatan</label>
                        <select name="role" x-model="residentForm.role" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-semibold text-slate-900 dark:text-white">
                            <option value="warga">Warga</option>
                            <option value="satpam">Satpam</option>
                            <option value="bendahara">Bendahara RT</option>
                            <option value="sekretaris">Sekretaris RT</option>
                            <option value="ketua_rt">Ketua RT</option>
                            @if(in_array(Auth::user()?->role, ['super_admin', 'ketua_rw']))
                                <option value="ketua_rw">Ketua RW</option>
                            @else
                                <option value="ketua_rw" disabled>Ketua RW (Khusus RW/Admin)</option>
                            @endif
                            @if(Auth::user()?->isSuperAdmin())
                                <option value="super_admin">Super Admin</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Status Akun</label>
                        <select name="status" x-model="residentForm.status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-white">
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                            <option value="pending_approval">Pending Approval</option>
                        </select>
                    </div>
                    <label class="flex items-center gap-2 pt-5 cursor-pointer text-xs">
                        <input type="checkbox" name="is_family_head" value="1" x-model="residentForm.is_family_head" class="rounded text-emerald-600">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Kepala Keluarga</span>
                    </label>
                </div>
                <div class="pt-2 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openEditResidentModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs cursor-pointer">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function residentManager() {
    return {
        activeTab: @js($activeTab),
        openHouseModal: false,
        openEditHouseModal: false,
        openAddResidentModal: false,
        openEditResidentModal: false,
        houseForm: { id: null, block_name: '', house_number: '', no_kk: '', full_address: '', occupancy_status: 'occupied' },
        residentForm: { id: null, name: '', phone_number: '', nik: '', no_kk: '', house_id: '', role: 'warga', status: 'active', is_family_head: false },
        openEditHouse(data) {
            this.houseForm = { ...data, no_kk: data.no_kk || '', full_address: data.full_address || '' };
            this.openEditHouseModal = true;
        },
        openEditResident(data) {
            this.residentForm = { ...data, nik: data.nik || '', no_kk: data.no_kk || '', house_id: data.house_id || '' };
            this.openEditResidentModal = true;
        }
    };
}
</script>
@endsection
