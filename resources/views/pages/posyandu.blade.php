@extends('layouts.app')

@section('content')
<div class="space-y-8" x-data="{ showAddMemberModal: false, showRecordModal: false, selectedMemberId: '', selectedMemberName: '' }">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-pink-100 dark:bg-pink-950/70 text-pink-600 dark:text-pink-400 flex items-center justify-center text-2xl font-bold shadow-xs">
                    🩺
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Modul Posyandu Digital RT/RW</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Pencatatan kesehatan balita, pencegahan stunting, pemantauan ibu hamil, dan posbindu lansia.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button @click="showAddMemberModal = true" class="px-4 py-2.5 rounded-xl font-semibold text-sm bg-pink-600 hover:bg-pink-700 text-white shadow-xs transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Tambah Warga Posyandu
            </button>
        </div>
    </div>

    <!-- Demographic KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <!-- Balita -->
        <a href="{{ route('posyandu.index', ['category' => 'balita']) }}" class="p-4 rounded-2xl border transition {{ $filterCategory === 'balita' ? 'bg-pink-50 dark:bg-pink-950/40 border-pink-300 dark:border-pink-800 ring-2 ring-pink-500' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-pink-300' }}">
            <div class="text-2xl mb-1">👶</div>
            <div class="text-2xl font-black text-slate-900 dark:text-white">{{ $counts['balita'] }}</div>
            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Balita (< 5 Thn)</div>
            <div class="text-[11px] text-pink-600 dark:text-pink-400 font-medium mt-1">KMS & Vaksin</div>
        </a>

        <!-- Anak-anak -->
        <a href="{{ route('posyandu.index', ['category' => 'anak']) }}" class="p-4 rounded-2xl border transition {{ $filterCategory === 'anak' ? 'bg-indigo-50 dark:bg-indigo-950/40 border-indigo-300 dark:border-indigo-800 ring-2 ring-indigo-500' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-indigo-300' }}">
            <div class="text-2xl mb-1">🧒</div>
            <div class="text-2xl font-black text-slate-900 dark:text-white">{{ $counts['anak'] }}</div>
            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Anak-anak (5-12)</div>
            <div class="text-[11px] text-indigo-600 dark:text-indigo-400 font-medium mt-1">Tumbuh Kembang</div>
        </a>

        <!-- Ibu Hamil -->
        <a href="{{ route('posyandu.index', ['category' => 'ibu_hamil']) }}" class="p-4 rounded-2xl border transition {{ $filterCategory === 'ibu_hamil' ? 'bg-amber-50 dark:bg-amber-950/40 border-amber-300 dark:border-amber-800 ring-2 ring-amber-500' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-amber-300' }}">
            <div class="text-2xl mb-1">🤰</div>
            <div class="text-2xl font-black text-slate-900 dark:text-white">{{ $counts['ibu_hamil'] }}</div>
            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Ibu Hamil</div>
            <div class="text-[11px] text-amber-600 dark:text-amber-400 font-medium mt-1">LiLA & Tensi</div>
        </a>

        <!-- Ibu-ibu / WUS -->
        <a href="{{ route('posyandu.index', ['category' => 'ibu']) }}" class="p-4 rounded-2xl border transition {{ $filterCategory === 'ibu' ? 'bg-purple-50 dark:bg-purple-950/40 border-purple-300 dark:border-purple-800 ring-2 ring-purple-500' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-purple-300' }}">
            <div class="text-2xl mb-1">👩</div>
            <div class="text-2xl font-black text-slate-900 dark:text-white">{{ $counts['ibu'] }}</div>
            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Ibu / Kader</div>
            <div class="text-[11px] text-purple-600 dark:text-purple-400 font-medium mt-1">Kader Posyandu</div>
        </a>

        <!-- Lansia -->
        <a href="{{ route('posyandu.index', ['category' => 'lansia']) }}" class="p-4 rounded-2xl border transition {{ $filterCategory === 'lansia' ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-800 ring-2 ring-emerald-500' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-emerald-300' }}">
            <div class="text-2xl mb-1">👵</div>
            <div class="text-2xl font-black text-slate-900 dark:text-white">{{ $counts['lansia'] }}</div>
            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Lansia (> 60)</div>
            <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium mt-1">Gula & Kolesterol</div>
        </a>

        <!-- Total Terdata -->
        <a href="{{ route('posyandu.index', ['category' => 'all']) }}" class="p-4 rounded-2xl border transition {{ $filterCategory === 'all' ? 'bg-blue-50 dark:bg-blue-950/40 border-blue-300 dark:border-blue-800 ring-2 ring-blue-500' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-blue-300' }}">
            <div class="text-2xl mb-1">📋</div>
            <div class="text-2xl font-black text-slate-900 dark:text-white">{{ $counts['total'] }}</div>
            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Warga</div>
            <div class="text-[11px] text-blue-600 dark:text-blue-400 font-medium mt-1">Semua Kategori</div>
        </a>
    </div>

    <!-- Health Radar & Alert Banner -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-5 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-amber-200 dark:bg-amber-900/60 text-amber-800 dark:text-amber-200 flex items-center justify-center font-bold text-lg shrink-0">
                ⚠️
            </div>
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Deteksi Stunting Dini</div>
                <div class="text-xl font-black text-amber-900 dark:text-amber-200 mt-0.5">{{ $stuntingRiskCount }} Balita Terindikasi</div>
                <p class="text-xs text-amber-800 dark:text-amber-300/90 mt-1 leading-relaxed">
                    Tinggi/berat badan di bawah ambang batas kurva pertumbuhan WHO. Perlu pemberian Pemberian Makanan Tambahan (PMT) & protein tinggi.
                </p>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-rose-200 dark:bg-rose-900/60 text-rose-800 dark:text-rose-200 flex items-center justify-center font-bold text-lg shrink-0">
                ❤️
            </div>
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400">Hipertensi Lansia (Posbindu)</div>
                <div class="text-xl font-black text-rose-900 dark:text-rose-200 mt-0.5">{{ $hypertensionCount }} Lansia Tensi Tinggi</div>
                <p class="text-xs text-rose-800 dark:text-rose-300/90 mt-1 leading-relaxed">
                    Tekanan darah sistolik di atas 140 mmHg. Disarankan senam lansia rutin dan kontrol garam.
                </p>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-200 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200 flex items-center justify-center font-bold text-lg shrink-0">
                ✅
            </div>
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Gizi Normal & Optimal</div>
                <div class="text-xl font-black text-emerald-900 dark:text-emerald-200 mt-0.5">{{ $normalCount }} Warga Sehat</div>
                <p class="text-xs text-emerald-800 dark:text-emerald-300/90 mt-1 leading-relaxed">
                    Pertumbuhan balita dan kondisi fisik ibu hamil dalam rentang hijau standar Kemenkes RI.
                </p>
            </div>
        </div>
    </div>

    <!-- Members Table Section -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Buku Register Warga Posyandu</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Menampilkan data warga terdaftar (Filter: <span class="font-semibold uppercase text-pink-600 dark:text-pink-400">{{ $filterCategory }}</span>)
                </p>
            </div>

            <!-- Filter tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
                @foreach(['all' => 'Semua', 'balita' => 'Balita', 'anak' => 'Anak', 'ibu_hamil' => 'Ibu Hamil', 'ibu' => 'Ibu', 'lansia' => 'Lansia'] as $key => $label)
                    <a href="{{ route('posyandu.index', ['category' => $key]) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $filterCategory === $key ? 'bg-pink-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="overflow-x-auto -mx-2 sm:mx-0 px-2 sm:px-0">
            <table class="w-full text-left text-sm min-w-[650px]">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 text-xs uppercase font-bold tracking-wider">
                    <tr>
                        <th class="py-3 px-6">Warga / NIK</th>
                        <th class="py-3 px-6">Kategori & Usia</th>
                        <th class="py-3 px-6">Orang Tua / Kontak</th>
                        <th class="py-3 px-6">Pemeriksaan Terakhir</th>
                        <th class="py-3 px-6">Status Kesehatan</th>
                        <th class="py-3 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($members as $m)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span>{{ $m->gender === 'L' ? '👦' : '👧' }}</span>
                                    <span>{{ $m->name }}</span>
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                    NIK: {{ $m->nik ?? '-' }} &bull; KK: {{ $m->family_card_number ?? '-' }}
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">
                                    {{ $m->category_label }}
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Usia: <span class="font-bold text-slate-700 dark:text-slate-200">{{ $m->age_text }}</span> ({{ $m->birth_date->format('d/m/Y') }})
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="text-slate-800 dark:text-slate-200 font-medium">{{ $m->parent_name ?? '-' }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $m->address ?? 'RT 04 / RW 02' }} &bull; {{ $m->phone ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-6">
                                @if($m->latestRecord)
                                    <div class="text-xs text-slate-700 dark:text-slate-200 font-semibold">
                                        @if($m->category === 'balita' || $m->category === 'anak')
                                            BB: {{ $m->latestRecord->weight_kg }} kg &bull; TB: {{ $m->latestRecord->height_cm }} cm
                                        @elseif($m->category === 'ibu_hamil')
                                            Tensi: {{ $m->latestRecord->blood_pressure ?? '-' }} &bull; LiLA: {{ $m->latestRecord->arm_circumference_cm }} cm ({{ $m->latestRecord->gestational_age_weeks }})
                                        @else
                                            Tensi: {{ $m->latestRecord->blood_pressure ?? '-' }} &bull; Gula: {{ $m->latestRecord->blood_sugar ?? '-' }} mg/dL
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        Tgl: {{ $m->latestRecord->checkup_date->format('d M Y') }} &bull; {{ $m->latestRecord->vitamins_or_vaccines ?? 'Rutin' }}
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum pernah diperiksa</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if($m->latestRecord)
                                    @php $badge = $m->latestRecord->status_badge; @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $badge['color'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right space-x-2">
                                <button 
                                    @click="showRecordModal = true; selectedMemberId = '{{ $m->id }}'; selectedMemberName = '{{ $m->name }} ({{ $m->category_label }})'" 
                                    class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 transition"
                                    title="Catat Penimbangan & Periksa"
                                >
                                    + Catat Periksa
                                </button>
                                <form action="{{ route('posyandu.members.destroy', $m->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus data warga posyandu ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 transition" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                Belum ada warga terdaftar di kategori ini. Silakan klik tombol <strong>"+ Tambah Warga Posyandu"</strong>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Checkups Activity -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Catatan Pemeriksaan Terkini (Log Posyandu)</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($recentCheckups as $rc)
                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                    <div class="flex items-center justify-between text-xs mb-2">
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $rc->member?->name }}</span>
                        <span class="text-slate-500">{{ $rc->checkup_date->format('d M Y') }}</span>
                    </div>
                    <div class="text-xs text-slate-600 dark:text-slate-300 space-y-1">
                        @if($rc->weight_kg) <div>⚖️ Berat: <span class="font-semibold">{{ $rc->weight_kg }} kg</span> | Tinggi: <span class="font-semibold">{{ $rc->height_cm }} cm</span></div> @endif
                        @if($rc->blood_pressure) <div>🩺 Tensi: <span class="font-semibold">{{ $rc->blood_pressure }} mmHg</span></div> @endif
                        @if($rc->vitamins_or_vaccines) <div>💊 Vitamin/Vaksin: <span class="font-semibold">{{ $rc->vitamins_or_vaccines }}</span></div> @endif
                    </div>
                    <div class="mt-3 pt-2 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between">
                        @php $badge = $rc->status_badge; @endphp
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-md {{ $badge['color'] }}">{{ $badge['label'] }}</span>
                        <span class="text-[10px] text-slate-400">{{ $rc->examiner_name }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- MODAL 1: Tambah Warga Posyandu -->
    <div x-show="showAddMemberModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs" x-cloak>
        <div @click.away="showAddMemberModal = false" class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Pendaftaran Warga Posyandu Baru</h3>
                <button @click="showAddMemberModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form action="{{ route('posyandu.members.store') }}" method="POST" class="space-y-4 mt-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Contoh: Muhammad Rayyan" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-pink-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Kategori Sasaran</label>
                        <select name="category" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                            <option value="balita">👶 Balita (< 5 Thn)</option>
                            <option value="anak">🧒 Anak-anak (5-12 Thn)</option>
                            <option value="ibu_hamil">🤰 Ibu Hamil</option>
                            <option value="ibu">👩 Ibu / Kader</option>
                            <option value="lansia">👵 Lansia (> 60 Thn)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Jenis Kelamin</label>
                        <select name="gender" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Tanggal Lahir</label>
                        <input type="date" name="birth_date" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">NIK (16 Digit)</label>
                        <input type="text" name="nik" placeholder="3216..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Nama Orang Tua / Penanggung Jawab</label>
                    <input type="text" name="parent_name" placeholder="Nama ayah & ibu / pasangan" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Alamat / Rumah</label>
                        <input type="text" name="address" placeholder="Blok C No. 12" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">No. WhatsApp</label>
                        <input type="text" name="phone" placeholder="0812..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Catatan Khusus / Riwayat</label>
                    <textarea name="notes" rows="2" placeholder="Catatan alergi, HPL ibu hamil, dsb." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                    <button type="button" @click="showAddMemberModal = false" class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-sm font-semibold bg-pink-600 hover:bg-pink-700 text-white">Simpan Data Warga</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Catat Pemeriksaan / Penimbangan -->
    <div x-show="showRecordModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs" x-cloak>
        <div @click.away="showRecordModal = false" class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Pencatatan Pemeriksaan Posyandu</h3>
                    <p class="text-xs text-pink-600 dark:text-pink-400 font-semibold" x-text="selectedMemberName"></p>
                </div>
                <button @click="showRecordModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form action="{{ route('posyandu.records.store') }}" method="POST" class="space-y-4 mt-4">
                @csrf
                <input type="hidden" name="posyandu_member_id" :value="selectedMemberId">

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Tanggal Pemeriksaan</label>
                    <input type="date" name="checkup_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Berat Badan (Kg)</label>
                        <input type="number" step="0.1" name="weight_kg" placeholder="11.5" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Tinggi Badan (Cm)</label>
                        <input type="number" step="0.1" name="height_cm" placeholder="85.0" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Lingkar Kepala / LiLA (Cm)</label>
                        <input type="number" step="0.1" name="arm_circumference_cm" placeholder="15.0" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Tekanan Darah (Tensi)</label>
                        <input type="text" name="blood_pressure" placeholder="120/80" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Vaksin / Vitamin Diberikan</label>
                        <input type="text" name="vitamins_or_vaccines" placeholder="Vit A, DPT, Polio" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Nama Petugas / Bidan</label>
                        <input type="text" name="examiner_name" value="Bidan Nina Kurnia, S.Tr.Keb" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Saran & Rekomendasi Medis</label>
                    <textarea name="action_plan" rows="2" placeholder="Gizi cukup, lanjutkan ASI eksklusif / kurangi konsumsi garam..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                    <button type="button" @click="showRecordModal = false" class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white">Simpan Hasil Pemeriksaan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

