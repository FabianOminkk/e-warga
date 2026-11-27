@extends('layouts.app')

@section('content')
<div class="max-w-[1440px] mx-auto px-2 sm:px-6 lg:px-8 py-3 sm:py-6 space-y-5 sm:space-y-6">

    <!-- Komponen Android Mobile Weather & Fast Status Bar (Tampil di Layar HP) -->
    <x-mobile-android-header :tenant="$tenant" />

    <!-- Header & Lingkungan Info Keuangan -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">
                <span>{{ $tenant->name ?? 'Cluster Melati Mas' }}</span>
                <span>•</span>
                <span>{{ $tenant->rt_rw ?? 'RT 05 / RW 12' }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-1">Keuangan &amp; Iuran RT</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Pusat pencatatan kas lingkungan, verifikasi pembayaran iuran warga, dan penerbitan tagihan bulanan.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <a href="{{ route('dashboard.cashbook.export') }}" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors flex items-center gap-2 border border-transparent dark:border-slate-700 touch-press">
                <span>📥 Ekspor CSV Kas</span>
            </a>
            <a href="{{ route('dashboard') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors flex items-center gap-2 touch-press">
                <span>📊 Kembali ke Dashboard</span>
            </a>
        </div>
    </div>

    @include('partials.finance-summary')

    <!-- Row: Antrian Verifikasi Iuran & Catat Transaksi Baru -->
    <div id="seksi-keuangan" class="grid grid-cols-1 lg:grid-cols-12 gap-8 scroll-mt-20">
        
        <!-- Kolom Kiri: Verifikasi Pembayaran Pending & Penerbitan Tagihan (Bendahara Focus) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Antrian Verifikasi Pembayaran Iuran</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Struk transfer yang diunggah warga menunggu persetujuan Bendahara.</p>
                    </div>
                    <span class="px-2.5 py-1 bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 text-xs font-black rounded-full tabular-nums">
                        {{ $pendingPayments->count() }} Menunggu
                    </span>
                </div>

                @if($pendingPayments->isEmpty())
                    <div class="text-center py-8 text-slate-400 dark:text-slate-500 text-sm">
                        <span class="text-3xl block mb-2">🎉</span>
                        Tidak ada antrian pembayaran tertunda. Semua pembayaran telah terverifikasi!
                    </div>
                @else
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($pendingPayments as $p)
                            <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $p->payer?->name ?? 'Warga' }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 tabular-nums">{{ $p->billing?->house?->block_and_number ?? 'Unit Rumah' }} • Tagihan: Rp {{ number_format($p->amount_paid, 0, ',', '.') }}</div>
                                    <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5 flex flex-wrap items-center gap-2">
                                        <span>Metode: {{ strtoupper(str_replace('_', ' ', $p->payment_method)) }} • Diupload: {{ $p->created_at->diffForHumans() }}</span>
                                        @if($p->proof_url)
                                            <a href="{{ $p->proof_url }}" target="_blank" class="text-cyan-600 dark:text-cyan-400 font-bold hover:underline">📎 Lihat Bukti</a>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <form action="{{ route('dashboard.verify-payment', $p->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition-colors shadow-xs cursor-pointer focus-visible:ring-2 focus-visible:ring-emerald-500">
                                            ✅ Terima
                                        </button>
                                    </form>
                                    <form action="{{ route('dashboard.verify-payment', $p->id) }}" method="POST" @submit.prevent="window.ewargaConfirmSubmit($el, { title: 'Tolak Pembayaran Iuran?', message: 'Anda yakin ingin menolak bukti pembayaran iuran ini? Tagihan warga akan dikembalikan menjadi belum dibayar.', confirmText: 'Ya, Tolak', tone: 'danger' })">
                                        @csrf
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-red-50 hover:text-red-600 dark:bg-slate-800 dark:hover:bg-rose-950/60 dark:text-slate-300 dark:hover:text-rose-300 font-bold text-xs rounded-lg transition-colors cursor-pointer focus-visible:ring-2 focus-visible:ring-rose-500">
                                            Tolak
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Form Terbitkan Tagihan Iuran Bulanan Massal -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Terbitkan Tagihan Iuran Bulanan</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Buat tagihan IPL otomatis ke seluruh rumah berpenghuni untuk periode bulan tertentu.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 text-[11px] font-bold">
                        Otomatis per Unit
                    </span>
                </div>

                <form action="{{ route('dashboard.generate-billings') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Periode Bulan</label>
                        <input type="month" name="billing_month" value="{{ now()->format('Y-m') }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-semibold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Nominal Iuran (Rp)</label>
                        <input type="number" name="amount" value="150000" min="1000" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-mono font-bold tabular-nums">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Jatuh Tempo</label>
                        <input type="date" name="due_date" value="{{ now()->startOfMonth()->addDays(14)->format('Y-m-d') }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Keterangan Tagihan</label>
                        <input type="text" name="description" value="Iuran Kebersihan, Keamanan, &amp; Kas RT" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-sm">
                    </div>
                    <div class="sm:col-span-2 flex justify-end pt-1">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-emerald-600 dark:hover:bg-emerald-700 text-white font-black text-xs cursor-pointer transition-colors">
                            + Terbitkan Tagihan ke Rumah Warga
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Kolom Kanan: Form Catat Kas Baru -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white pb-2 border-b border-slate-100 dark:border-slate-800">Catat Transaksi Kas RT</h3>
            <form action="{{ route('dashboard.store-cashbook') }}" method="POST" class="space-y-4" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">Jenis Kas</label>
                        <select name="type" class="w-full px-4 py-3 text-base rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden font-semibold">
                            <option value="income">🟢 Pemasukan (+)</option>
                            <option value="expense">🔴 Pengeluaran (-)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">Kategori</label>
                        <input type="text" name="category" placeholder="Misal: Gaji Satpam…" required class="w-full px-4 py-3 text-base rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">Judul / Keterangan Transaksi</label>
                    <input type="text" name="title" placeholder="Misal: Pembayaran Listrik Pos Satpam…" required class="w-full px-4 py-3 text-base rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">Jumlah Nominal (Rp)</label>
                    <input type="number" name="amount" inputmode="numeric" placeholder="500000" min="1000" required class="w-full px-4 py-3 text-base rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden font-mono font-bold tabular-nums">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">Catatan Tambahan (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Nomor kuitansi / nota toko…" class="w-full px-4 py-3 text-base rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden leading-relaxed"></textarea>
                </div>

                <button type="submit" :disabled="isSubmitting" class="w-full min-h-[48px] py-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-extrabold text-sm rounded-2xl shadow-md shadow-emerald-600/25 transition-transform flex items-center justify-center gap-2 cursor-pointer touch-press">
                    <span x-show="isSubmitting" x-cloak class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-text="isSubmitting ? 'Menyimpan Transaksi…' : 'Simpan Catatan Kas'">Simpan Catatan Kas</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Riwayat Buku Kas Terbuka RT (Lebar Penuh) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Buku Kas Terbuka RT</h3>
                <span class="text-xs text-slate-400 dark:text-slate-500">8 Terakhir</span>
            </div>
            <a 
                href="{{ route('dashboard.cashbook.export') }}" 
                class="text-xs font-bold px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white dark:text-slate-200 transition-colors flex items-center gap-1.5 shadow-2xs"
            >
                <span>📥 Ekspor CSV Kas</span>
            </a>
        </div>

        <!-- TAMPILAN 1: TATA LETAK KARTU VERTIKAL PADA SMARTPHONE (MOBILE-FIRST) -->
        <div class="block lg:hidden space-y-2.5">
            @foreach($recentCashBooks as $cb)
                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-mono text-slate-500 dark:text-slate-400 font-semibold">{{ $cb->transaction_date->format('d/m/Y') }}</span>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $cb->type === 'income' ? 'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800' }}">
                            {{ $cb->category }}
                        </span>
                    </div>
                    <div class="text-sm font-extrabold text-slate-900 dark:text-white leading-snug">
                        {{ $cb->title }}
                    </div>
                    <div class="pt-1 flex items-center justify-between border-t border-slate-100 dark:border-slate-800 text-xs">
                        <span class="text-slate-400 text-[11px]">{{ $cb->type === 'income' ? 'Pemasukan Kas' : 'Pengeluaran Lingkungan' }}</span>
                        <span class="font-extrabold text-base tabular-nums {{ $cb->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $cb->formatted_amount }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- TAMPILAN 2: TABEL LEBAR KHUSUS DESKTOP (LARGE SCREEN) -->
        <div class="hidden lg:block overflow-x-auto -mx-3 sm:mx-0 px-3 sm:px-0">
            <table class="w-full text-left text-xs min-w-[500px]">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                        <th class="py-2">Tanggal</th>
                        <th class="py-2">Transaksi</th>
                        <th class="py-2">Kategori</th>
                        <th class="py-2 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($recentCashBooks as $cb)
                        <tr>
                            <td class="py-3 text-slate-500 dark:text-slate-400 font-mono tabular-nums">{{ $cb->transaction_date->format('d/m/Y') }}</td>
                            <td class="py-3 font-semibold text-slate-800 dark:text-slate-200">{{ $cb->title }}</td>
                            <td class="py-3 text-slate-500 dark:text-slate-400">{{ $cb->category }}</td>
                            <td class="py-3 text-right font-bold tabular-nums {{ $cb->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-rose-400' }}">
                                {{ $cb->formatted_amount }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
