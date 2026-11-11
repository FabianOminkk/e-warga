@extends('layouts.app')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-xl w-full text-center space-y-6">
        
        <!-- Icon & Status Badge -->
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-500 text-5xl shadow-xl shadow-rose-500/10">
            🔒
        </div>

        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-300 text-xs font-black tracking-wide uppercase border border-rose-200 dark:border-rose-800">
                <span>Akses Terbatas / Memerlukan Verifikasi</span>
            </div>
            
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                Hak Akses Anda Belum Diotorisasi
            </h1>
            
            <p class="text-sm text-slate-600 dark:text-slate-400 max-w-md mx-auto leading-relaxed">
                Halaman ini memerlukan peran akun atau persetujuan khusus dari Ketua RW. Jika Anda warga baru, pastikan identitas KTP Anda sudah diverifikasi.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="pt-4 flex flex-wrap items-center justify-center gap-3">
            <button 
                type="button" 
                onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ route('dashboard') }}'" 
                class="px-5 py-3 rounded-2xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 font-extrabold text-xs shadow-xs transition flex items-center gap-2 cursor-pointer"
            >
                <span>⬅ Kembali</span>
            </button>

            @auth
                @if(Auth::user()->role === 'warga')
                    <a 
                        href="{{ route('warga.profile') }}" 
                        class="px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-lg shadow-emerald-600/20 transition flex items-center gap-2 cursor-pointer"
                    >
                        <span>🪪 Lengkapi Berkas KTP Anda ➔</span>
                    </a>
                @else
                    <a 
                        href="{{ route('dashboard') }}" 
                        class="px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-lg shadow-emerald-600/20 transition flex items-center gap-2 cursor-pointer"
                    >
                        <span>📊 Buka Dashboard ➔</span>
                    </a>
                @endif
            @else
                <a 
                    href="{{ route('login') }}" 
                    class="px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-lg shadow-emerald-600/20 transition flex items-center gap-2 cursor-pointer"
                >
                    <span>Masuk ke Akun Anda ➔</span>
                </a>
            @endauth
        </div>

        <div class="pt-6 border-t border-slate-100 dark:border-slate-800/80 text-[11px] text-slate-400 dark:text-slate-500">
            Pusat Bantuan E-WARGA &bull; Kode Status: 403 Akses Dibatasi
        </div>

    </div>
</div>
@endsection

