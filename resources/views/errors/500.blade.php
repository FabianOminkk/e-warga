@extends('layouts.app')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-xl w-full text-center space-y-6">
        
        <!-- Icon & Status Badge -->
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 text-indigo-500 text-5xl shadow-xl shadow-indigo-500/10">
            ⚙️
        </div>

        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 text-xs font-black tracking-wide uppercase border border-indigo-200 dark:border-indigo-800">
                <span>Pemulihan Otomatis Sistem</span>
            </div>
            
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                Layanan Sedang Disinkronkan
            </h1>
            
            <p class="text-sm text-slate-600 dark:text-slate-400 max-w-md mx-auto leading-relaxed">
                Terjadi kendala teknis sementara saat memproses permintaan Anda. Sistem telah mencatat aktivitas ini untuk pemulihan otomatis.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="pt-4 flex flex-wrap items-center justify-center gap-3">
            <button 
                type="button" 
                onclick="window.location.reload()" 
                class="px-5 py-3 rounded-2xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 font-extrabold text-xs shadow-xs transition flex items-center gap-2 cursor-pointer"
            >
                <span>🔄 Muat Ulang Halaman</span>
            </button>

            <a 
                href="{{ route('dashboard') }}" 
                class="px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-lg shadow-emerald-600/20 transition flex items-center gap-2 cursor-pointer"
            >
                <span>🏠 Kembali ke Dashboard Aman ➔</span>
            </a>
        </div>

        <div class="pt-6 border-t border-slate-100 dark:border-slate-800/80 text-[11px] text-slate-400 dark:text-slate-500">
            Pusat Bantuan E-WARGA &bull; Kode Status: 500 Pemulihan Otomatis
        </div>

    </div>
</div>
@endsection

