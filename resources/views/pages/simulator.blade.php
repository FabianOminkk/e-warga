@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="gateSimulator()">

    <!-- Header Simulator -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-cyan-500 animate-ping"></span>
                <span class="text-xs font-bold text-cyan-600 dark:text-cyan-400 uppercase tracking-widest">DEVELOPER ENVIRONMENT TOOL</span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white mt-1">Virtual Smart Gate Simulator</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Uji coba respon palang pintu otomatis secara lokal tanpa perlu menyambungkan hardware fisik.</p>
        </div>

        <div class="flex items-center gap-3">
            <span class="px-3 py-1.5 rounded-full text-xs font-bold flex items-center gap-2" :class="isOpen ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300' : 'bg-red-100 text-red-800 dark:bg-rose-950/70 dark:text-rose-300'">
                <span class="w-2.5 h-2.5 rounded-full" :class="isOpen ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'"></span>
                <span x-text="isOpen ? 'GERBANG TERBUKA (OPEN)' : 'GERBANG TERTUTUP (CLOSED)'"></span>
            </span>
        </div>
    </div>

    <!-- Main Visual Stage: Animated Barrier Gate -->
    <div class="bg-gradient-to-b from-slate-900 to-slate-800 rounded-3xl p-8 lg:p-12 text-white shadow-2xl relative overflow-hidden">
        
        <!-- Overhead Signal Lamp & Timer -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-4">
                <!-- Traffic Light Simulation -->
                <div class="bg-slate-950 px-3 py-2 rounded-2xl border border-slate-700 flex items-center gap-3">
                    <div class="w-6 h-6 rounded-full transition-all duration-300" :class="!isOpen ? 'bg-red-500 shadow-lg shadow-red-500/80 ring-2 ring-red-300' : 'bg-red-950'"></div>
                    <div class="w-6 h-6 rounded-full transition-all duration-300" :class="isOpen ? 'bg-emerald-400 shadow-lg shadow-emerald-400/80 ring-2 ring-emerald-300' : 'bg-emerald-950'"></div>
                </div>
                <div>
                    <div class="text-xs font-mono text-slate-400">STATUS PALANG PINTU:</div>
                    <div class="text-lg font-black tracking-wide" :class="isOpen ? 'text-emerald-400' : 'text-red-400'" x-text="isOpen ? 'PALANG TERANGKAT (SILAKAN LEWAT)' : 'PALANG TERKUNCI'"></div>
                </div>
            </div>

            <!-- Auto-Close Countdown Display -->
            <div x-show="isOpen" x-cloak class="bg-slate-950/80 px-5 py-2.5 rounded-2xl border border-emerald-500/40 text-center">
                <div class="text-[10px] text-emerald-300 uppercase font-bold tracking-widest">Menutup Otomatis Dalam</div>
                <div class="text-2xl font-black font-mono text-emerald-400"><span x-text="countdown"></span> Detik</div>
            </div>
        </div>

        <!-- Barrier Gate Graphic Canvas (CSS 3D Art) -->
        <div class="w-full h-64 bg-slate-950/60 rounded-2xl border border-slate-700 relative flex items-end px-12 pb-6 overflow-hidden">
            <!-- Road Asphalt Markings -->
            <div class="absolute inset-x-0 bottom-0 h-16 bg-slate-900 border-t-2 border-slate-700 flex items-center justify-around">
                <div class="w-16 h-2 bg-amber-400"></div>
                <div class="w-16 h-2 bg-amber-400"></div>
                <div class="w-16 h-2 bg-amber-400"></div>
                <div class="w-16 h-2 bg-amber-400"></div>
            </div>

            <!-- Gate Housing (Tiang Portal) -->
            <div class="relative z-20 flex flex-col items-center">
                <div class="w-14 h-36 bg-gradient-to-r from-amber-500 via-amber-400 to-amber-600 rounded-t-xl border-2 border-amber-300 shadow-2xl flex flex-col items-center justify-between py-3">
                    <div class="w-6 h-6 rounded-full bg-slate-900 border border-slate-700 flex items-center justify-center text-[10px] font-bold text-cyan-400">IoT</div>
                    <div class="w-10 h-10 rounded-lg bg-slate-900 border border-amber-300 flex items-center justify-center text-xs">📶</div>
                    <div class="text-[8px] font-black text-slate-900 tracking-tighter">E-WARGA</div>
                </div>
                <div class="w-20 h-4 bg-slate-700 rounded-t"></div>
            </div>

            <!-- Motor Pivot Axis & The Boom Arm (Palang Pintu Berputar) -->
            <div class="absolute left-[110px] bottom-[115px] z-30">
                <!-- Rotating Pivot Anchor -->
                <div class="relative">
                    <div class="w-8 h-8 rounded-full bg-slate-800 border-4 border-slate-400 shadow-xl flex items-center justify-center">
                        <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                    </div>

                    <!-- Boom Barrier Arm (Garis Merah Putih) -->
                    <div class="absolute left-4 top-2 h-4 w-72 sm:w-96 rounded-r-md shadow-2xl transition-transform duration-700 ease-in-out origin-left flex overflow-hidden border border-slate-900"
                         :style="isOpen ? 'transform: rotate(-80deg);' : 'transform: rotate(0deg);'">
                        <!-- Striping Merah Putih -->
                        <div class="h-full w-12 bg-red-600"></div>
                        <div class="h-full w-12 bg-white"></div>
                        <div class="h-full w-12 bg-red-600"></div>
                        <div class="h-full w-12 bg-white"></div>
                        <div class="h-full w-12 bg-red-600"></div>
                        <div class="h-full w-12 bg-white"></div>
                        <div class="h-full w-12 bg-red-600"></div>
                        <div class="h-full w-12 bg-white"></div>
                    </div>
                </div>
            </div>

            <!-- Virtual Passing Car Simulation -->
            <div x-show="isOpen" x-cloak class="absolute bottom-6 z-10 animate-[slideRight_4s_linear] text-4xl">
                🚗
            </div>
        </div>

        <!-- Testing Control Console (Trigger Buttons) -->
        <div class="mt-8 pt-6 border-t border-slate-700">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Simulasikan Aksi Pemicu:</div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Test 1: Tombol HP Warga -->
                <button @click="triggerGate('mobile_button')" :disabled="isOpen" class="p-4 rounded-2xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-bold text-xs text-left shadow-lg transition flex items-center gap-3">
                    <span class="text-2xl">📱</span>
                    <div>
                        <div class="font-extrabold text-sm">Warga Buka via HP</div>
                        <div class="text-[11px] text-emerald-100 font-normal">Klik tombol di aplikasi HP</div>
                    </div>
                </button>

                <!-- Test 2: Tap Kartu RFID -->
                <button @click="triggerGate('rfid_card')" :disabled="isOpen" class="p-4 rounded-2xl bg-cyan-700 hover:bg-cyan-600 disabled:opacity-50 text-white font-bold text-xs text-left shadow-lg transition flex items-center gap-3">
                    <span class="text-2xl">💳</span>
                    <div>
                        <div class="font-extrabold text-sm">Warga Tap RFID</div>
                        <div class="text-[11px] text-cyan-100 font-normal">Tempel kartu di tiang portal</div>
                    </div>
                </button>

                <!-- Test 3: Scan QR Tamu / Kurir -->
                <button @click="triggerGate('qr_code')" :disabled="isOpen" class="p-4 rounded-2xl bg-purple-700 hover:bg-purple-600 disabled:opacity-50 text-white font-bold text-xs text-left shadow-lg transition flex items-center gap-3">
                    <span class="text-2xl">🎫</span>
                    <div>
                        <div class="font-extrabold text-sm">Scan QR Tamu/Kurir</div>
                        <div class="text-[11px] text-purple-100 font-normal">Izin masuk terverifikasi</div>
                    </div>
                </button>

                <!-- Test 4: Satpam Manual Override -->
                <button @click="triggerGate('satpam_manual')" :disabled="isOpen" class="p-4 rounded-2xl bg-slate-700 hover:bg-slate-600 disabled:opacity-50 text-white font-bold text-xs text-left shadow-lg transition flex items-center gap-3">
                    <span class="text-2xl">🛡️</span>
                    <div>
                        <div class="font-extrabold text-sm">Satpam Buka Manual</div>
                        <div class="text-[11px] text-slate-300 font-normal">Kondisi darurat pos jaga</div>
                    </div>
                </button>
            </div>
        </div>

    </div>

    <!-- Live Log Console -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Console Log Simulator</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Respon data dari database ke simulator gerbang secara real-time.</p>
            </div>
            <button @click="fetchLogs()" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline">Refresh Log</button>
        </div>

        <div class="bg-slate-950 text-emerald-400 p-4 rounded-xl font-mono text-xs space-y-2 max-h-60 overflow-y-auto">
            <template x-for="item in logs" :key="item.id">
                <div class="flex items-start gap-2">
                    <span class="text-slate-500" x-text="'[' + item.time + ']'"></span>
                    <span class="text-cyan-400 font-bold" x-text="item.method.toUpperCase()"></span>
                    <span class="text-slate-200" x-text="item.reason"></span>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function gateSimulator() {
    return {
        isOpen: false,
        countdown: 5,
        timer: null,
        logs: [
            @foreach($recentLogs as $log)
            {
                id: {{ $log->id }},
                time: '{{ $log->triggered_at->format('H:i:s') }}',
                method: '{{ $log->access_method }}',
                reason: '{{ $log->reason }}'
            },
            @endforeach
        ],

        triggerGate(method) {
            if (this.isOpen) return;

            fetch('{{ route('simulator.trigger') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ method: method })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.isOpen = true;
                    this.countdown = data.hold_duration_seconds || 5;

                    // Tambahkan ke log
                    this.logs.unshift({
                        id: data.log.id,
                        time: data.log.time,
                        method: data.log.method,
                        reason: data.log.reason
                    });

                    // Countdown timer
                    if (this.timer) clearInterval(this.timer);
                    this.timer = setInterval(() => {
                        this.countdown--;
                        if (this.countdown <= 0) {
                            clearInterval(this.timer);
                            this.isOpen = false;
                        }
                    }, 1000);
                }
            })
            .catch(err => console.error(err));
        },

        fetchLogs() {
            fetch('{{ route('simulator.logs') }}')
                .then(res => res.json())
                .then(data => {
                    this.logs = data.logs;
                });
        }
    }
}
</script>
@endsection

