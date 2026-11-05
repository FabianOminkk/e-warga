@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Header & Lingkungan Info -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">
                <span>{{ $tenant->name ?? 'Cluster Melati Mas' }}</span>
                <span>•</span>
                <span>{{ $tenant->rt_rw ?? 'RT 05 / RW 12' }}</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mt-1">Dashboard Pengurus Lingkungan</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Pusat kendali keuangan kas, persetujuan iuran warga, dan analisis grafik keuangan.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('simulator') }}" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-2 border border-transparent dark:border-slate-700">
                <span>🧪 Buka Simulator Gerbang</span>
            </a>
            <a href="{{ route('satpam.console') }}" class="px-4 py-2.5 bg-[#1B365D] hover:bg-slate-800 dark:bg-emerald-700 dark:hover:bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-2">
                <span>🛡️ Monitor Pos Satpam</span>
            </a>
        </div>
    </div>

    <!-- Statistik Utama (Top Metric Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Saldo Kas Bersih -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Saldo Kas RT Aktif</div>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2">
                Rp {{ number_format($saldoKas, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1">
                <span class="text-emerald-600 dark:text-emerald-400 font-bold">Transparan</span> dapat dipantau seluruh warga
            </div>
        </div>

        <!-- Pemasukan Bulan Ini -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Pemasukan Kas</div>
            <div class="text-2xl font-black text-[#1B365D] dark:text-cyan-400 mt-2">
                Rp {{ number_format($totalIncome, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                Dari iuran bulanan & donasi
            </div>
        </div>

        <!-- Pengeluaran Bulan Ini -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Pengeluaran Kas</div>
            <div class="text-2xl font-black text-red-600 dark:text-rose-400 mt-2">
                Rp {{ number_format($totalExpense, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                Gaji satpam, sampah, & listrik got
            </div>
        </div>

        <!-- Iuran Warga Lunas -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Status Pembayaran Iuran</div>
            <div class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-2">
                {{ $paidBillings }} <span class="text-sm font-normal text-slate-400 dark:text-slate-500">/ {{ $totalHouses }} Rumah</span>
            </div>
            <div class="text-[11px] text-amber-600 dark:text-amber-400 font-semibold mt-1">
                {{ $unpaidBillings }} Rumah belum melunasi iuran
            </div>
        </div>
    </div>

    <!-- SEKSI GRAFIK & ANALISIS VISUAL KEUANGAN (PIE CHART, DONUT CHART, & CASHFLOW) -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 lg:p-8 shadow-xs space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 text-[11px] font-extrabold uppercase tracking-wide">
                        Visualisasi Data Keuangan
                    </span>
                    @if($saldoKas >= 0)
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 text-[11px] font-extrabold flex items-center gap-1">
                            <span>●</span> Keuangan Sehat (Surplus)
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full bg-red-50 dark:bg-rose-950/70 text-red-700 dark:text-rose-300 text-[11px] font-extrabold flex items-center gap-1">
                            <span>●</span> Peringatan: Defisit Kas
                        </span>
                    @endif
                </div>
                <h2 class="text-xl font-extrabold text-slate-900 dark:text-white mt-1">Analisis Akurat Aliran Kas & Kepatuhan Iuran</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Membantu bendahara dan ketua RT membaca tren pengeluaran dan akurasi keuangan dalam sekejap.</p>
            </div>
            
            <div class="text-xs text-slate-400 dark:text-slate-500 font-medium">
                Pembaruan: <span class="font-bold text-slate-700 dark:text-slate-300">{{ now()->format('d M Y, H:i') }} WIB</span>
            </div>
        </div>

        <!-- Grid 3 Grafik Finansial -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
            
            <!-- Grafik 1: Proporsi Pengeluaran per Kategori (Pie Chart) -->
            <div class="bg-slate-50/70 dark:bg-slate-800/40 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 flex flex-col items-center">
                <div class="w-full text-left mb-3">
                    <div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Distribusi Pengeluaran</div>
                    <div class="text-sm font-extrabold text-slate-900 dark:text-white">Proporsi Kategori Biaya</div>
                </div>
                <div class="relative w-full aspect-square max-w-[240px] flex items-center justify-center">
                    <canvas id="expensePieChart"></canvas>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 text-center mt-3">
                    Memantau pos anggaran terbesar (Gaji Satpam, Listrik, Kebersihan).
                </p>
            </div>

            <!-- Grafik 2: Kepatuhan Pembayaran Iuran (Donut Chart) -->
            <div class="bg-slate-50/70 dark:bg-slate-800/40 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 flex flex-col items-center">
                <div class="w-full text-left mb-3">
                    <div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Kepatuhan Warga</div>
                    <div class="text-sm font-extrabold text-slate-900 dark:text-white">Status Pembayaran Iuran</div>
                </div>
                <div class="relative w-full aspect-square max-w-[240px] flex items-center justify-center">
                    <canvas id="billingDonutChart"></canvas>
                </div>
                <div class="text-center mt-3">
                    @php
                        $totalB = $paidBillings + $unpaidBillings;
                        $pct = $totalB > 0 ? round(($paidBillings / $totalB) * 100) : 0;
                    @endphp
                    <span class="inline-block text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300">
                        {{ $pct }}% Tingkat Pelunasan
                    </span>
                </div>
            </div>

            <!-- Grafik 3: Arus Kas Finansial (Bar Chart) -->
            <div class="bg-slate-50/70 dark:bg-slate-800/40 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 flex flex-col items-center">
                <div class="w-full text-left mb-3">
                    <div class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Arus Kas Masuk vs Keluar</div>
                    <div class="text-sm font-extrabold text-slate-900 dark:text-white">Komparasi Finansial RT</div>
                </div>
                <div class="relative w-full aspect-square max-w-[240px] flex items-center justify-center">
                    <canvas id="cashflowBarChart"></canvas>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 text-center mt-3">
                    Memastikan pemasukan selalu lebih besar daripada pengeluaran bulanan.
                </p>
            </div>

        </div>
    </div>

    <!-- Row: Antrian Verifikasi Iuran & Catat Transaksi Baru -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Kolom Kiri: Verifikasi Pembayaran Pending (Bendahara Focus) -->
        <div class="lg:col-span-7 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Antrian Verifikasi Pembayaran Iuran</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Struk transfer yang diunggah warga menunggu persetujuan Bendahara.</p>
                </div>
                <span class="px-2.5 py-1 bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 text-xs font-black rounded-full">
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
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $p->billing?->house?->block_and_number ?? 'Unit Rumah' }} • Tagihan: Rp {{ number_format($p->amount_paid, 0, ',', '.') }}</div>
                                <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Metode: {{ ucfirst($p->payment_method) }} • Diupload: {{ $p->created_at->diffForHumans() }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <form action="{{ route('dashboard.verify-payment', $p->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition shadow-xs">
                                        ✅ Terima
                                    </button>
                                </form>
                                <form action="{{ route('dashboard.verify-payment', $p->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-red-50 hover:text-red-600 dark:bg-slate-800 dark:hover:bg-rose-950/60 dark:text-slate-300 dark:hover:text-rose-300 font-bold text-xs rounded-lg transition">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Kolom Kanan: Form Catat Kas Baru -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white pb-2 border-b border-slate-100 dark:border-slate-800">Catat Transaksi Kas RT</h3>
            <form action="{{ route('dashboard.store-cashbook') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Jenis Kas</label>
                        <select name="type" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 outline-hidden font-semibold">
                            <option value="income">🟢 Pemasukan (+)</option>
                            <option value="expense">🔴 Pengeluaran (-)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Kategori</label>
                        <input type="text" name="category" placeholder="Misal: Gaji Satpam" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 outline-hidden">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Judul / Keterangan Transaksi</label>
                    <input type="text" name="title" placeholder="Misal: Pembayaran Listrik Pos Satpam" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 outline-hidden">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Jumlah Nominal (Rp)</label>
                    <input type="number" name="amount" placeholder="500000" min="1000" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 outline-hidden font-mono font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Nomor kuitansi / nota toko..." class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 outline-hidden"></textarea>
                </div>

                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                    Simpan Catatan Kas
                </button>
            </form>
        </div>
    </div>

    <!-- Riwayat Transaksi Kas & Log Gerbang -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Buku Kas Terbuka -->
        <div class="lg:col-span-7 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Buku Kas Terbuka RT</h3>
                <span class="text-xs text-slate-400 dark:text-slate-500">8 Transaksi Terakhir</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
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
                                <td class="py-3 text-slate-500 dark:text-slate-400 font-mono">{{ $cb->transaction_date->format('d/m/Y') }}</td>
                                <td class="py-3 font-semibold text-slate-800 dark:text-slate-200">{{ $cb->title }}</td>
                                <td class="py-3 text-slate-500 dark:text-slate-400">{{ $cb->category }}</td>
                                <td class="py-3 text-right font-bold {{ $cb->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-rose-400' }}">
                                    {{ $cb->formatted_amount }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Log Palang Pintu Terkini -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Aktivitas Palang Pintu</h3>
                <a href="{{ route('satpam.console') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">Monitor Pos &rarr;</a>
            </div>

            <div class="space-y-3">
                @foreach($recentGateLogs as $gl)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <span class="w-2.5 h-2.5 rounded-full {{ $gl->status === 'granted' ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                            <div>
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $gl->user?->name ?? 'Tamu / Satpam' }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ $gl->reason }}</div>
                            </div>
                        </div>
                        <span class="text-[11px] font-mono text-slate-400 dark:text-slate-500">{{ $gl->triggered_at->format('H:i:s') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- INISIALISASI GRAFIK CHART.JS DENGAN DUKUNGAN DARK / LIGHT MODE -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const formatRupiah = (val) => 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
    const isDarkMode = () => document.documentElement.classList.contains('dark');

    const getTextColor = () => isDarkMode() ? '#CBD5E1' : '#475569';
    const getBorderColor = () => isDarkMode() ? '#1E293B' : '#FFFFFF';
    const getGridColor = () => isDarkMode() ? '#334155' : '#F1F5F9';

    let expenseChartInstance = null;
    let billingChartInstance = null;
    let cashflowChartInstance = null;

    // 1. Pie Chart: Proporsi Pengeluaran per Kategori
    const expenseCtx = document.getElementById('expensePieChart');
    if (expenseCtx && window.Chart) {
        expenseChartInstance = new window.Chart(expenseCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($chartExpenseLabels) !!},
                datasets: [{
                    data: {!! json_encode($chartExpenseTotals) !!},
                    backgroundColor: [
                        '#1B365D', // Navy Primer
                        '#0D9488', // Teal
                        '#38BDF8', // Cyan
                        '#F59E0B', // Amber
                        '#EF4444', // Red
                        '#64748B'  // Slate
                    ],
                    borderWidth: 2,
                    borderColor: getBorderColor(),
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: getTextColor(),
                            boxWidth: 12,
                            font: { size: 10, family: 'Plus Jakarta Sans' },
                            padding: 10
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return ' ' + context.label + ': ' + formatRupiah(context.raw);
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Donut Chart: Kepatuhan Pembayaran Iuran
    const billingCtx = document.getElementById('billingDonutChart');
    if (billingCtx && window.Chart) {
        billingChartInstance = new window.Chart(billingCtx, {
            type: 'doughnut',
            data: {
                labels: ['Lunas', 'Belum Bayar'],
                datasets: [{
                    data: [{{ $paidBillings }}, {{ $unpaidBillings }}],
                    backgroundColor: ['#10B981', '#F59E0B'],
                    borderWidth: 2,
                    borderColor: getBorderColor(),
                    cutout: '70%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: getTextColor(),
                            boxWidth: 12,
                            font: { size: 10, family: 'Plus Jakarta Sans' },
                            padding: 10
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return ' ' + context.label + ': ' + context.raw + ' Rumah';
                            }
                        }
                    }
                }
            }
        });
    }

    // 3. Bar Chart: Komparasi Arus Kas
    const cashflowCtx = document.getElementById('cashflowBarChart');
    if (cashflowCtx && window.Chart) {
        cashflowChartInstance = new window.Chart(cashflowCtx, {
            type: 'bar',
            data: {
                labels: ['Masuk', 'Keluar', 'Saldo'],
                datasets: [{
                    label: 'Nominal Kas RT',
                    data: {!! json_encode(array_values($chartCashflow)) !!},
                    backgroundColor: [
                        '#10B981', // Hijau Masuk
                        '#EF4444', // Merah Keluar
                        '#38BDF8'  // Cyan Saldo
                    ],
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return ' ' + formatRupiah(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            color: getTextColor(),
                            callback: function (value) {
                                if (value >= 1000000) {
                                    return (value / 1000000) + ' Jt';
                                }
                                return value;
                            },
                            font: { size: 9, family: 'Plus Jakarta Sans' }
                        },
                        grid: { color: getGridColor() }
                    },
                    x: {
                        ticks: { color: getTextColor(), font: { size: 10, weight: 'bold', family: 'Plus Jakarta Sans' } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // Event Listener: Update Chart Theme dynamically on Dark/Light toggle
    window.addEventListener('theme-changed', function () {
        const textColor = getTextColor();
        const borderColor = getBorderColor();
        const gridColor = getGridColor();

        if (expenseChartInstance) {
            expenseChartInstance.data.datasets[0].borderColor = borderColor;
            expenseChartInstance.options.plugins.legend.labels.color = textColor;
            expenseChartInstance.update();
        }

        if (billingChartInstance) {
            billingChartInstance.data.datasets[0].borderColor = borderColor;
            billingChartInstance.options.plugins.legend.labels.color = textColor;
            billingChartInstance.update();
        }

        if (cashflowChartInstance) {
            cashflowChartInstance.options.scales.x.ticks.color = textColor;
            cashflowChartInstance.options.scales.y.ticks.color = textColor;
            cashflowChartInstance.options.scales.y.grid.color = gridColor;
            cashflowChartInstance.update();
        }
    });
});
</script>
@endsection
