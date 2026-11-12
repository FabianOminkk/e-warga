@props(['tenant' => null, 'user' => null])

@php
    $tenant = $tenant ?? \App\Models\Tenant::getActiveTenant();
    $user = $user ?? \Illuminate\Support\Facades\Auth::user();
    $hour = (int) now()->format('H');
    if ($hour >= 4 && $hour < 11) {
        $greeting = 'Selamat Pagi';
        $weatherIcon = '☀️';
        $weatherCondition = 'Cerah Berawan';
        $temp = '28°C';
    } elseif ($hour >= 11 && $hour < 15) {
        $greeting = 'Selamat Siang';
        $weatherIcon = '🌤️';
        $weatherCondition = 'Cerah Hangat';
        $temp = '32°C';
    } elseif ($hour >= 15 && $hour < 18) {
        $greeting = 'Selamat Sore';
        $weatherIcon = '⛅';
        $weatherCondition = 'Sore Sejuk';
        $temp = '29°C';
    } else {
        $greeting = 'Selamat Malam';
        $weatherIcon = '🌙';
        $weatherCondition = 'Malam Cerah & Tenang';
        $temp = '25°C';
    }
@endphp

<!-- ======================================================== -->
<!-- ANDROID NATIVE MOBILE HEADER & WEATHER STATUS WIDGET     -->
<!-- (Tampil Khusus Pada Layar Smartphone / Tablet < 1024px)   -->
<!-- ======================================================== -->
<div class="lg:hidden mb-5 space-y-3.5">
    
    <!-- 1. Android Top Status Card (Weather + Community Status) -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#1B365D] via-slate-900 to-emerald-950 text-white p-5 shadow-xl border border-white/10">
        
        <!-- Background Ambient Glow -->
        <div class="absolute -top-12 -right-12 w-40 h-40 rounded-full bg-emerald-500/20 blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-10 -left-10 w-36 h-36 rounded-full bg-cyan-500/15 blur-2xl pointer-events-none"></div>

        <div class="relative z-10 space-y-4">
            
            <!-- Bar Atas: Greeting & Live Clock -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-white/15 backdrop-blur-xs font-black text-emerald-300 uppercase tracking-wider">
                        {{ $tenant?->name ?? 'Cluster Melati Mas' }}
                    </span>
                    <span class="text-[11px] text-slate-300 font-medium">
                        {{ $tenant?->rt_rw ?? 'RT 05 / RW 12' }}
                    </span>
                </div>

                <!-- Jam & Waktu Live -->
                <div class="flex items-center gap-1.5 text-xs font-bold font-mono text-cyan-300 bg-black/20 px-2.5 py-1 rounded-xl backdrop-blur-xs">
                    <span>⏰</span>
                    <span x-data="{ time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }" x-init="setInterval(() => time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }), 1000)" x-text="time + ' WIB'"></span>
                </div>
            </div>

            <!-- Bagian Tengah: Cuaca & Suhu Lingkungan (Inspired by Android Weather Card) -->
            <div class="flex items-center justify-between pt-1">
                <div>
                    <span class="text-[11px] font-semibold text-slate-300 block">
                        {{ $greeting }}, <strong class="text-white font-extrabold">{{ $user?->name ?? 'Warga Guyub' }}</strong>
                    </span>
                    <div class="flex items-baseline gap-2 mt-0.5">
                        <span class="text-3xl font-black text-white tracking-tight">{{ $temp }}</span>
                        <span class="text-xs font-bold text-emerald-400">{{ $weatherCondition }}</span>
                    </div>
                </div>

                <div class="text-right flex flex-col items-end">
                    <span class="text-3xl filter drop-shadow-sm">{{ $weatherIcon }}</span>
                    <span class="text-[10px] text-slate-300 mt-1 font-medium flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Udara Sehat: 38 AQI</span>
                    </span>
                </div>
            </div>

            <!-- Status IoT Lingkungan Bar Tipis -->
            <div class="pt-3 border-t border-white/10 grid grid-cols-2 gap-2 text-[11px]">
                <div class="flex items-center gap-2 bg-white/5 rounded-xl px-2.5 py-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <div class="truncate">
                        <span class="text-slate-400 text-[10px] block">Palang Gerbang:</span>
                        <span class="font-bold text-white text-[11px]">Aktif & Normal</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 bg-white/5 rounded-xl px-2.5 py-1.5">
                    <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                    <div class="truncate">
                        <span class="text-slate-400 text-[10px] block">Pos Satpam:</span>
                        <span class="font-bold text-white text-[11px]">Jaga Siaga (24 Jam)</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- 2. Android Quick Action Chips Carousel (Horizontal Scroll Ergonomic) -->
    <div class="space-y-1.5">
        <div class="flex items-center justify-between px-1 text-[11px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
            <span>Aksi Cepat Android:</span>
            <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">Geser ➔</span>
        </div>

        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1 px-0.5">
            
            <!-- Quick Chip 1: Buka Gerbang -->
            <a 
                href="{{ route('warga.index') }}" 
                class="flex items-center gap-2 px-3.5 py-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 text-xs font-bold shadow-xs shrink-0 hover:bg-emerald-50 dark:hover:bg-emerald-950/60 transition touch-press"
            >
                <span class="text-base">🚪</span>
                <span>Buka Gerbang</span>
            </a>

            <!-- Quick Chip 2: Tanya AI Bang Warga -->
            <button 
                type="button" 
                @click="$dispatch('open-ai-chat')" 
                class="flex items-center gap-2 px-3.5 py-2.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-xs font-black shadow-xs shrink-0 hover:opacity-95 transition touch-press cursor-pointer"
            >
                <span class="text-base">🤖</span>
                <span>Tanya Bang Warga</span>
            </button>

            <!-- Quick Chip 3: Bayar Iuran Kas -->
            <a 
                href="{{ route('warga.index') }}#iuran" 
                class="flex items-center gap-2 px-3.5 py-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 text-xs font-bold shadow-xs shrink-0 hover:bg-blue-50 dark:hover:bg-blue-950/60 transition touch-press"
            >
                <span class="text-base">💳</span>
                <span>Bayar Iuran</span>
            </a>

            <!-- Quick Chip 4: Surat RT Online -->
            <a 
                href="{{ route('letters.index') }}" 
                class="flex items-center gap-2 px-3.5 py-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 text-xs font-bold shadow-xs shrink-0 hover:bg-amber-50 dark:hover:bg-amber-950/60 transition touch-press"
            >
                <span class="text-base">📜</span>
                <span>Surat RT</span>
            </a>

            <!-- Quick Chip 5: Posyandu Digital -->
            <a 
                href="{{ route('posyandu.index') }}" 
                class="flex items-center gap-2 px-3.5 py-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 text-xs font-bold shadow-xs shrink-0 hover:bg-purple-50 dark:hover:bg-purple-950/60 transition touch-press"
            >
                <span class="text-base">👶</span>
                <span>Posyandu</span>
            </a>

            <!-- Quick Chip 6: Jadwal Ronda -->
            <a 
                href="{{ route('schedules.index') }}" 
                class="flex items-center gap-2 px-3.5 py-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 text-xs font-bold shadow-xs shrink-0 hover:bg-slate-100 dark:hover:bg-slate-800 transition touch-press"
            >
                <span class="text-base">👮</span>
                <span>Jadwal Ronda</span>
            </a>

        </div>
    </div>

</div>

