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
