<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E-Kwitansi Iuran #{{ $billing->id }} | {{ $tenant->name ?? 'E-WARGA' }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    @vite(['css/app.css', 'js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; padding: 0 !important; }
            .print-card { box-shadow: none !important; border: 1px solid #cbd5e1 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen py-8 px-4 flex flex-col items-center">

    <div class="no-print max-w-2xl w-full mb-6 flex items-center justify-between bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
        <a href="{{ route('warga.index') }}" class="text-xs sm:text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-emerald-600">
            &larr; Kembali ke Portal Warga
        </a>
        <button type="button" onclick="window.print()" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black cursor-pointer">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <div class="print-card max-w-2xl w-full bg-white text-slate-900 rounded-3xl p-6 sm:p-10 shadow-xl border border-slate-200 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b-2 border-slate-900">
            <div>
                <div class="text-xs font-black uppercase tracking-widest text-emerald-700">{{ $tenant->name ?? 'E-WARGA Residence' }}</div>
                <h1 class="text-xl sm:text-2xl font-black tracking-tight mt-0.5">E-KWITANSI PEMBAYARAN IURAN</h1>
                <p class="text-xs text-slate-500">{{ $tenant->rt_rw ?? 'RT/RW' }} &bull; {{ $tenant->city ?? 'Indonesia' }}</p>
            </div>
            <div class="text-left sm:text-right">
                <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-black uppercase">
                    ✓ LUNAS TERVERIFIKASI
                </span>
                <div class="text-xs font-mono font-bold text-slate-500 mt-1 tabular-nums">
                    No: KWT-{{ str_pad((string) $billing->id, 5, '0', STR_PAD_LEFT) }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm bg-slate-50 p-4 rounded-2xl border border-slate-200">
            <div>
                <div class="text-[11px] uppercase font-bold text-slate-500">Sudah Terima Dari</div>
                <div class="font-black text-slate-900">{{ $payment?->payer?->name ?? $user->name }}</div>
                <div class="text-xs text-slate-600">{{ $billing->house?->block_and_number ?? ($user->house?->block_and_number ?? '-') }}</div>
            </div>
            <div>
                <div class="text-[11px] uppercase font-bold text-slate-500">Periode Tagihan</div>
                <div class="font-bold text-slate-900">{{ $billing->billing_period?->translatedFormat('F Y') ?? '-' }}</div>
                <div class="text-xs text-slate-600">Metode: {{ strtoupper(str_replace('_', ' ', $payment?->payment_method ?? 'manual_transfer')) }}</div>
            </div>
        </div>

        <div class="space-y-2 text-sm">
            <div class="flex items-center justify-between py-2 border-b border-slate-200">
                <span class="text-slate-600">Keterangan Iuran</span>
                <span class="font-bold text-slate-900 text-right">{{ $billing->description }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-slate-200">
                <span class="text-slate-600">Tanggal Verifikasi Bendahara</span>
                <span class="font-mono font-bold tabular-nums">{{ $payment?->verified_at?->format('d/m/Y H:i') ?? $billing->updated_at?->format('d/m/Y H:i') }} WIB</span>
            </div>
            <div class="flex items-center justify-between py-3 bg-emerald-50 px-4 rounded-xl border border-emerald-200 mt-2">
                <span class="font-black text-emerald-900">JUMLAH DITERIMA</span>
                <span class="text-xl font-black text-emerald-700 font-mono tabular-nums">{{ $billing->formatted_amount }}</span>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-200 flex items-end justify-between text-xs text-slate-500">
            <div>
                Dokumen bukti terima elektronik sah tercatat di Buku Kas Terbuka {{ $tenant->name ?? 'RT/RW' }}.
            </div>
            <div class="text-right shrink-0">
                <div class="font-bold text-slate-900">Bendahara Lingkungan</div>
                <div class="mt-6 font-bold underline text-slate-900">{{ $payment?->verifier?->name ?? 'Bendahara RT' }}</div>
            </div>
        </div>
    </div>

</body>
</html>
