<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $letter->letter_type_title }} - {{ $letter->letter_number }} | E-WARGA</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; padding: 0 !important; }
            .print-container { box-shadow: none !important; border: none !important; width: 100% !important; margin: 0 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 font-sans antialiased text-slate-800 dark:text-slate-200 min-h-screen py-8 px-4 flex flex-col items-center">

    <!-- Top Action Bar (No Print) -->
    <div class="no-print max-w-3xl w-full mb-6 flex items-center justify-between bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
        <a href="{{ route('letters.index') }}" class="text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-blue-600 flex items-center gap-2">
            &larr; Kembali ke Daftar Surat
        </a>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300">
                ✓ Terverifikasi Digital
            </span>
            <button onclick="window.print()" class="px-4 py-2 rounded-xl text-sm font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Dokumen Resmi
            </button>
        </div>
    </div>

    <!-- Official Letter Paper Container (A4 Printable Canvas) -->
    <div class="print-container bg-white text-slate-900 max-w-3xl w-full p-10 md:p-14 rounded-2xl shadow-xl border border-slate-200 font-serif">
        
        <!-- Kop Surat Resmi RT/RW -->
        <div class="text-center border-b-4 border-double border-slate-900 pb-4 mb-6">
            <div class="flex items-center justify-center gap-4 mb-2">
                <img src="/images/logo.svg" alt="Logo RT" class="w-14 h-14 object-contain">
                <div>
                    <h2 class="text-lg font-bold tracking-widest uppercase">RUKUN TETANGGA 04 / RUKUN WARGA 02</h2>
                    <h3 class="text-base font-bold uppercase tracking-wider text-slate-800">PERUMAHAN GRAHA ASRI RESIDENCE</h3>
                    <p class="text-xs font-sans text-slate-600">
                        Kecamatan Cikarang Utara, Kabupaten Bekasi, Provinsi Jawa Barat 17530
                    </p>
                </div>
            </div>
        </div>

        <!-- Judul & Nomor Surat -->
        <div class="text-center mb-8">
            <h1 class="text-xl font-bold uppercase underline tracking-wide">{{ $letter->letter_type_title }}</h1>
            <p class="text-sm font-sans font-medium text-slate-700 mt-1">Nomor: {{ $letter->letter_number }}</p>
        </div>

        <!-- Isi Surat -->
        <div class="font-sans text-sm leading-relaxed space-y-4 mb-8 text-slate-800">
            <p>
                Yang bertanda tangan di bawah ini, Ketua Rukun Tetangga (RT) 04 Rukun Warga (RW) 02 Perumahan Graha Asri Residence, menerangkan dengan sebenarnya bahwa:
            </p>

            <div class="pl-6 space-y-2 py-2">
                <div class="grid grid-cols-12 gap-2">
                    <div class="col-span-4 font-semibold text-slate-600">Nama Lengkap</div>
                    <div class="col-span-8 font-bold text-slate-900">: {{ $letter->applicant_name }}</div>
                </div>
                <div class="grid grid-cols-12 gap-2">
                    <div class="col-span-4 font-semibold text-slate-600">NIK (Nomor KTP)</div>
                    <div class="col-span-8 font-mono font-bold text-slate-900">: {{ $letter->nik }}</div>
                </div>
                <div class="grid grid-cols-12 gap-2">
                    <div class="col-span-4 font-semibold text-slate-600">Alamat Tempat Tinggal</div>
                    <div class="col-span-8 text-slate-900">: {{ $letter->house_block }}, RT 04 / RW 02 Graha Asri Residence</div>
                </div>
                <div class="grid grid-cols-12 gap-2">
                    <div class="col-span-4 font-semibold text-slate-600">Status Kependudukan</div>
                    <div class="col-span-8 text-slate-900">: Warga Tetap RT 04</div>
                </div>
                <div class="grid grid-cols-12 gap-2">
                    <div class="col-span-4 font-semibold text-slate-600">Keperluan</div>
                    <div class="col-span-8 font-bold text-slate-900">: {{ $letter->purpose }}</div>
                </div>
            </div>

            <p>
                Berdasarkan data kependudukan dan catatan lingkungan kami, yang bersangkutan adalah benar warga lingkungan kami yang berkelakuan baik serta tertib administrasi.
            </p>

            <p>
                Demikian surat pengantar ini dibuat dengan sebenar-benarnya untuk dipergunakan sebagaimana mestinya oleh pihak yang berkepentingan.
            </p>
        </div>

        <!-- Tanda Tangan & QR Code Autentikasi -->
        <div class="grid grid-cols-2 pt-6 font-sans">
            <div>
                <!-- Authentic QR Code Seal -->
                <div class="p-4 rounded-xl border border-dashed border-slate-300 w-fit bg-slate-50">
                    <div class="w-24 h-24 bg-white border border-slate-200 flex items-center justify-center p-2 mb-2">
                        <!-- SVG QR Representation -->
                        <svg class="w-full h-full text-slate-900" viewBox="0 0 100 100" fill="currentColor">
                            <rect x="0" y="0" width="30" height="30"/>
                            <rect x="5" y="5" width="20" height="20" fill="white"/>
                            <rect x="10" y="10" width="10" height="10"/>
                            <rect x="70" y="0" width="30" height="30"/>
                            <rect x="75" y="5" width="20" height="20" fill="white"/>
                            <rect x="80" y="10" width="10" height="10"/>
                            <rect x="0" y="70" width="30" height="30"/>
                            <rect x="5" y="75" width="20" height="20" fill="white"/>
                            <rect x="10" y="80" width="10" height="10"/>
                            <rect x="35" y="10" width="10" height="10"/>
                            <rect x="50" y="10" width="10" height="10"/>
                            <rect x="35" y="25" width="10" height="10"/>
                            <rect x="40" y="40" width="20" height="20"/>
                            <rect x="70" y="40" width="10" height="10"/>
                            <rect x="85" y="55" width="10" height="10"/>
                            <rect x="35" y="70" width="15" height="15"/>
                            <rect x="60" y="70" width="10" height="10"/>
                            <rect x="75" y="80" width="15" height="15"/>
                        </svg>
                    </div>
                    <div class="text-[10px] font-mono text-slate-500 font-bold uppercase tracking-wider text-center">
                        {{ $letter->qr_verification_code }}
                    </div>
                    <div class="text-[9px] text-emerald-600 font-semibold text-center mt-0.5">
                        Dokumen Asli Bersertifikat
                    </div>
                </div>
            </div>

            <div class="text-right">
                <div class="text-sm text-slate-600">
                    Bekasi, {{ $letter->signed_at?->translatedFormat('d F Y') ?? date('d F Y') }}
                </div>
                <div class="text-sm font-bold text-slate-900 mt-1">
                    Ketua RT 04 / RW 02
                </div>
                
                <div class="my-6">
                    <span class="inline-block px-3 py-1 border border-emerald-500 rounded text-[11px] font-bold text-emerald-700 bg-emerald-50">
                        ✓ TERTANDA SECARA ELEKTRONIK
                    </span>
                </div>

                <div class="text-sm font-bold underline text-slate-900">
                    {{ $letter->signer?->name ?? 'Bpk. Ahmad Subarjo' }}
                </div>
                <div class="text-xs text-slate-500">
                    NIP / Register: RT04-2026-001
                </div>
            </div>
        </div>

    </div>

</body>
</html>

