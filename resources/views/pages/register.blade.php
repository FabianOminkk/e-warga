@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col justify-between bg-slate-50 dark:bg-slate-950 px-4 sm:px-6 lg:px-8 py-6">

    <!-- Top Navigation Bar Header Halaman Registrasi -->
    <header class="max-w-5xl w-full mx-auto flex items-center justify-between pb-6">
        <x-logo size="md" href="{{ route('home') }}" />
        
        <div class="flex items-center gap-3">
            <!-- Dark / Light Theme Toggle -->
            <div x-data="{
                isDark: document.documentElement.classList.contains('dark'),
                toggle() {
                    this.isDark = !this.isDark;
                    if (this.isDark) {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    }
                }
            }">
                <button 
                    @click="toggle()" 
                    type="button" 
                    class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors shadow-xs cursor-pointer flex items-center justify-center focus-visible:ring-2 focus-visible:ring-emerald-500" 
                    :title="isDark ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap'"
                >
                    <svg x-show="isDark" x-cloak class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg x-show="!isDark" x-cloak class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
            </div>

            <a href="{{ route('login') }}" class="px-3.5 sm:px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300 transition-colors shadow-xs flex items-center gap-1.5 focus-visible:ring-2 focus-visible:ring-emerald-500">
                <span>Sudah Punya Akun?</span>
                <span class="text-emerald-600 dark:text-emerald-400 font-black">Masuk ➔</span>
            </a>
        </div>
    </header>

    <!-- Main Register Container -->
    <div 
        x-data="{
            method: '{{ old('registration_method', 'gmail') }}',
            selectedTenantId: '{{ old('tenant_id', $selectedTenant?->id ?? '') }}',
            customHouse: {{ old('new_block_name') ? 'true' : 'false' }},
            houses: @js($houses),
            isLoadingHouses: false,
            isSubmitting: false,
            ktpPreview: null,
            nikInput: '{{ old('nik', '') }}',
            gmailPreset: '',
            
            init() {
                if (this.selectedTenantId) {
                    this.fetchHouses(this.selectedTenantId);
                }
            },

            async fetchHouses(tenantId) {
                if (!tenantId) return;
                this.isLoadingHouses = true;
                try {
                    const res = await fetch(`/api/tenants/${tenantId}/houses`);
                    if (res.ok) {
                        const json = await res.json();
                        this.houses = json.data || [];
                    }
                } catch (e) {
                    console.error('Gagal memuat daftar rumah:', e);
                } finally {
                    this.isLoadingHouses = false;
                }
            },

            previewKtp(event) {
                const file = event.target.files[0];
                if (file) {
                    if (file.size > 3 * 1024 * 1024) {
                        alert('Ukuran berkas KTP melebihi batas maksimal 3 MB.');
                        event.target.value = '';
                        this.ktpPreview = null;
                        return;
                    }
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.ktpPreview = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        this.ktpPreview = 'PDF_DOCUMENT';
                    }
                }
            },

            formatNik(e) {
                let val = e.target.value.replace(/\D/g, '');
                if (val.length > 16) val = val.slice(0, 16);
                this.nikInput = val;
                e.target.value = val;
            },

            useGmailDemo(name, email) {
                this.gmailPreset = email;
                document.getElementById('reg_name').value = name;
                document.getElementById('reg_email').value = email;
            }
        }"
        class="max-w-5xl w-full mx-auto my-auto grid grid-cols-1 lg:grid-cols-12 bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden"
    >
        
        <!-- SISI KIRI: BENEFIT & JAMINAN KEAMANAN PDP (5 COLS) -->
        <div class="order-2 lg:order-1 lg:col-span-5 bg-gradient-to-br from-[#0F172A] via-[#1B365D] to-emerald-950 p-6 sm:p-10 text-white flex flex-col justify-between relative overflow-hidden">
            <!-- Background Ambient Glow -->
            <div class="absolute -top-24 -left-24 w-64 h-64 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-cyan-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="space-y-6 relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-400/20 text-emerald-300 border border-emerald-400/30 text-[10px] font-black uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>PENDAFTARAN WARGA RESMI</span>
                </div>

                <div>
                    <h2 class="text-2xl sm:text-3xl font-black leading-tight tracking-tight">
                        Integrasi Ekosistem Pintar Lingkungan
                    </h2>
                    <p class="text-xs text-slate-300 mt-3 leading-relaxed">
                        Daftarkan diri Anda sebagai warga resmi untuk menikmati hak akses operasional gerbang RFID, surat pengantar digital, transparansi iuran kas, dan jadwal posyandu lingkungan.
                    </p>
                </div>

                <!-- 4 Keunggulan Registrasi Warga -->
                <div class="space-y-3.5 pt-2">
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">✓</span>
                        <div class="text-xs leading-snug">
                            <strong class="text-white">Akses Palang Otomatis RFID:</strong> Sinkronisasi nomor KTP langsung dengan kartu RFID & pass QR tamu.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">✓</span>
                        <div class="text-xs leading-snug">
                            <strong class="text-white">Persetujuan Surat 1-Klik:</strong> Pengajuan surat keterangan RT/RW tanpa perlu antre fisik.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">✓</span>
                        <div class="text-xs leading-snug">
                            <strong class="text-white">Transparansi Laporan Kas:</strong> Pantau saldo kas bulanan dan riwayat iuran secara langsung.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0 text-xs font-bold mt-0.5">✓</span>
                        <div class="text-xs leading-snug">
                            <strong class="text-white">Perlindungan Data Pribadi:</strong> Data identitas disimpan terenkripsi sesuai UU PDP No. 27/2022.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jaminan Privasi UU PDP -->
            <div class="pt-6 border-t border-white/10 relative z-10 mt-6 space-y-2">
                <div class="flex items-center gap-2 text-xs text-emerald-300 font-bold">
                    <span>🔒</span>
                    <span>Privasi Terproteksi UU PDP</span>
                </div>
                <p class="text-[11px] text-slate-300 leading-relaxed">
                    Berkas KTP Anda hanya dapat diverifikasi oleh Pengurus RT/RW yang sah dan tidak pernah dipublikasikan ke pihak luar.
                </p>
            </div>
        </div>

        <!-- SISI KANAN: FORMULIR MULTI-TAB REGISTER (7 COLS) -->
        <div class="order-1 lg:order-2 lg:col-span-7 p-6 sm:p-10 flex flex-col justify-between space-y-6">
            <div>
                <!-- Header Formulir -->
                <div class="pb-4 border-b border-slate-100 dark:border-slate-800 space-y-1">
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Formulir Pendaftaran Warga</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pilih metode pendaftaran yang sesuai dengan kebutuhan Anda:</p>
                </div>

                <!-- Notifikasi Error Validasi -->
                @if ($errors->any())
                    <div class="mt-4 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 text-xs space-y-1">
                        <div class="font-extrabold flex items-center gap-1.5">
                            <span>⚠️</span>
                            <span>Mohon periksa kembali isian formulir:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-[11px] pl-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- TAB SWITCHER: GMAIL vs e-KTP -->
                <div class="mt-5 grid grid-cols-2 gap-2 p-1.5 rounded-2xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60">
                    <button 
                        type="button" 
                        @click="method = 'gmail'" 
                        :class="method === 'gmail' ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-md shadow-emerald-950/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        class="py-2.5 px-3 rounded-xl font-black text-xs transition-colors flex items-center justify-center gap-2 cursor-pointer focus-visible:ring-2 focus-visible:ring-emerald-500 touch-press"
                    >
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                        </svg>
                        <span>Daftar via Gmail</span>
                    </button>

                    <button 
                        type="button" 
                        @click="method = 'ktp'" 
                        :class="method === 'ktp' ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-md shadow-emerald-950/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        class="py-2.5 px-3 rounded-xl font-black text-xs transition-colors flex items-center justify-center gap-2 cursor-pointer focus-visible:ring-2 focus-visible:ring-emerald-500 touch-press"
                    >
                        <span class="text-sm">🪪</span>
                        <span>Verifikasi e-KTP</span>
                    </button>
                </div>

                <!-- Formulir Input Pendaftaran -->
                <form 
                    action="{{ route('register.post') }}" 
                    method="POST" 
                    enctype="multipart/form-data" 
                    @submit="isSubmitting = true"
                    class="mt-6 space-y-4"
                >
                    @csrf
                    <input type="hidden" name="registration_method" :value="method">

                    <!-- Info Alert per Mode -->
                    <div x-show="method === 'gmail'" class="p-3.5 rounded-2xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-blue-900 dark:text-blue-300 text-xs leading-relaxed flex items-start gap-2.5">
                        <span class="text-base shrink-0">⚡</span>
                        <div>
                            <span class="font-bold">Pendaftaran Cepat Akun Gmail:</span>
                            <p class="text-[11px] text-blue-700 dark:text-blue-400 mt-0.5">
                                Akun aktif seketika. Untuk mengaktifkan palang otomatis RFID & permohonan surat, lengkapi foto KTP Anda setelah berhasil masuk.
                            </p>
                        </div>
                    </div>

                    <div x-show="method === 'ktp'" x-cloak class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-300 text-xs leading-relaxed flex items-start gap-2.5">
                        <span class="text-base shrink-0">🪪</span>
                        <div>
                            <span class="font-bold">Pendaftaran Resmi dengan Berkas e-KTP:</span>
                            <p class="text-[11px] text-emerald-700 dark:text-emerald-400 mt-0.5">
                                Berkas identitas Anda langsung diteruskan ke antrean verifikasi Pengurus RT/RW agar hak akses gerbang RFID aktif otomatis.
                            </p>
                        </div>
                    </div>

                    <!-- 1. Pilihan Komplek / Lingkungan RT -->
                    <div class="space-y-1.5">
                        <label for="reg_tenant_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                            Pilih Lingkungan RT/RW / Komplek Perumahan <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            name="tenant_id" 
                            id="reg_tenant_id" 
                            x-model="selectedTenantId"
                            @change="fetchHouses($event.target.value)"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors"
                        >
                            @foreach ($tenants as $t)
                                <option value="{{ $t->id }}" @selected(old('tenant_id', $selectedTenant?->id) == $t->id)>
                                    {{ $t->name }} ({{ $t->rt_rw }} - {{ $t->city ?? 'Indonesia' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. Nama Lengkap & WhatsApp -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label for="reg_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="name" 
                                id="reg_name" 
                                value="{{ old('name') }}"
                                required 
                                placeholder="Contoh: Fabian Warga Pratama…" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label for="reg_phone" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Nomor WhatsApp Aktif <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="tel" 
                                name="phone_number" 
                                id="reg_phone" 
                                inputmode="numeric"
                                value="{{ old('phone_number') }}"
                                required 
                                placeholder="Contoh: 081234567890…" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors tabular-nums"
                            >
                        </div>
                    </div>

                    <!-- 3. Alamat Email / Gmail -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label for="reg_email" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Alamat Email / Akun Gmail <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] text-slate-400">Untuk login & pengiriman pass tamu</span>
                        </div>
                        <div class="relative">
                            <input 
                                type="email" 
                                name="email" 
                                id="reg_email" 
                                inputmode="email"
                                autocomplete="email"
                                spellcheck="false"
                                value="{{ old('email') }}"
                                required 
                                placeholder="nama.anda@gmail.com…" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors"
                            >
                        </div>
                    </div>

                    <!-- 4. KHUSUS MODE e-KTP: NIK, FOTO KTP, DATA IDENTITAS -->
                    <div x-show="method === 'ktp'" x-cloak class="space-y-4 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label for="reg_nik" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                        Nomor NIK KTP (16 Digit) <span class="text-rose-500">*</span>
                                    </label>
                                    <span class="text-[10px] font-mono tabular-nums text-slate-400" x-text="nikInput.length + '/16'">0/16</span>
                                </div>
                                <input 
                                    type="text" 
                                    name="nik" 
                                    id="reg_nik" 
                                    inputmode="numeric"
                                    maxlength="16"
                                    @input="formatNik"
                                    value="{{ old('nik') }}"
                                    :required="method === 'ktp'"
                                    placeholder="3201xxxxxxxxxxxx…" 
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors tabular-nums font-mono"
                                >
                            </div>

                            <div class="space-y-1.5">
                                <label for="reg_gender" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Jenis Kelamin <span class="text-rose-500">*</span>
                                </label>
                                <select 
                                    name="gender" 
                                    id="reg_gender" 
                                    :required="method === 'ktp'"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors"
                                >
                                    <option value="male" @selected(old('gender') === 'male')>Laki-laki</option>
                                    <option value="female" @selected(old('gender') === 'female')>Perempuan</option>
                                </select>
                            </div>
                        </div>

                        <!-- Unggah Berkas KTP -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Unggah Foto e-KTP Asli <span class="text-slate-400 font-normal">(Maksimal 3&nbsp;MB)</span>
                            </label>
                            
                            <div class="relative border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-emerald-500 rounded-2xl p-4 text-center transition-colors bg-slate-50/50 dark:bg-slate-800/40">
                                <input 
                                    type="file" 
                                    name="ktp_photo" 
                                    id="reg_ktp_photo" 
                                    accept="image/*,application/pdf"
                                    @change="previewKtp"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                >
                                
                                <template x-if="!ktpPreview">
                                    <div class="space-y-1 py-2">
                                        <div class="text-2xl">📸</div>
                                        <div class="text-xs font-bold text-slate-700 dark:text-slate-300">Klik untuk ambil foto KTP atau pilih berkas</div>
                                        <div class="text-[11px] text-slate-400">Format didukung: JPG, PNG, WEBP, atau PDF (Maks 3&nbsp;MB)</div>
                                    </div>
                                </template>

                                <template x-if="ktpPreview && ktpPreview !== 'PDF_DOCUMENT'">
                                    <div class="space-y-2">
                                        <img :src="ktpPreview" alt="Pratinjau KTP" class="max-h-40 mx-auto rounded-xl shadow-md border border-slate-200 dark:border-slate-700 object-contain">
                                        <div class="text-xs text-emerald-600 dark:text-emerald-400 font-bold">Berkas KTP terpilih. Klik untuk mengganti.</div>
                                    </div>
                                </template>

                                <template x-if="ktpPreview === 'PDF_DOCUMENT'">
                                    <div class="space-y-1 py-2 text-emerald-600 dark:text-emerald-400">
                                        <div class="text-2xl">📄</div>
                                        <div class="text-xs font-bold">Dokumen PDF KTP terpilih</div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Alamat Sesuai KTP -->
                        <div class="space-y-1.5">
                            <label for="reg_address_ktp" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Alamat Lengkap Sesuai KTP <span class="text-rose-500">*</span>
                            </label>
                            <textarea 
                                name="address_ktp" 
                                id="reg_address_ktp" 
                                rows="2"
                                :required="method === 'ktp'"
                                placeholder="Tuliskan nama jalan, RT/RW, kelurahan, dan kecamatan sesuai KTP…"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors"
                            >{{ old('address_ktp') }}</textarea>
                        </div>
                    </div>

                    <!-- 5. Lokasi Rumah di Lingkungan RT/RW -->
                    <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Alamat Rumah / Blok Hunian di Lingkungan Ini
                            </label>
                            <button 
                                type="button" 
                                @click="customHouse = !customHouse" 
                                class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline cursor-pointer"
                                x-text="customHouse ? 'Pilih dari data rumah terdaftar ➔' : '+ Input blok/nomor rumah baru'"
                            ></button>
                        </div>

                        <!-- Dropdown Rumah Terdaftar -->
                        <div x-show="!customHouse">
                            <select 
                                name="house_id" 
                                id="reg_house_id" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors"
                            >
                                <option value="">-- Pilih Blok / Nomor Rumah Terdaftar --</option>
                                <template x-for="h in houses" :key="h.id">
                                    <option :value="h.id" x-text="h.block_name + ' No. ' + h.house_number + (h.full_address ? ' (' + h.full_address + ')' : '')"></option>
                                </template>
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Jika blok/nomor rumah Anda belum terdaftar, klik tombol di atas untuk menuliskan nomor baru.</p>
                        </div>

                        <!-- Form Tambah Rumah Baru -->
                        <div x-show="customHouse" x-cloak class="grid grid-cols-2 gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                            <div class="space-y-1">
                                <label for="reg_new_block" class="block text-[11px] font-bold text-slate-600 dark:text-slate-400">Nama Blok (Contoh: Blok B)</label>
                                <input 
                                    type="text" 
                                    name="new_block_name" 
                                    id="reg_new_block" 
                                    value="{{ old('new_block_name') }}"
                                    placeholder="Blok C…" 
                                    class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden"
                                >
                            </div>
                            <div class="space-y-1">
                                <label for="reg_new_number" class="block text-[11px] font-bold text-slate-600 dark:text-slate-400">Nomor Rumah (Contoh: 14)</label>
                                <input 
                                    type="text" 
                                    name="new_house_number" 
                                    id="reg_new_number" 
                                    value="{{ old('new_house_number') }}"
                                    placeholder="14…" 
                                    class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs focus:border-emerald-500 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- 6. Password & Konfirmasi Password -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <div class="space-y-1.5">
                            <label for="reg_password" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Buat Kata Sandi (Password) <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                name="password" 
                                id="reg_password" 
                                autocomplete="new-password"
                                required 
                                minlength="6"
                                placeholder="Minimal 6 karakter…" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label for="reg_password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Ulangi Kata Sandi <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                name="password_confirmation" 
                                id="reg_password_confirmation" 
                                autocomplete="new-password"
                                required 
                                minlength="6"
                                placeholder="Ketik ulang kata sandi…" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 text-slate-900 dark:text-white text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus-visible:ring-2 focus-visible:ring-emerald-500 outline-hidden transition-colors"
                            >
                        </div>
                    </div>

                    <!-- Checkbox Kepala Keluarga -->
                    <div class="pt-1">
                        <label class="flex items-center gap-2.5 cursor-pointer min-h-[44px]">
                            <input 
                                type="checkbox" 
                                name="is_family_head" 
                                value="1" 
                                checked
                                class="w-5 h-5 rounded-md text-emerald-600 border-slate-300 dark:border-slate-700 focus:ring-emerald-500"
                            >
                            <span class="text-xs text-slate-700 dark:text-slate-300 font-semibold">
                                Saya adalah Kepala Keluarga di unit rumah ini
                            </span>
                        </label>
                    </div>

                    <!-- Tombol Submit Pendaftaran -->
                    <button 
                        type="submit" 
                        :disabled="isSubmitting"
                        class="w-full min-h-[48px] py-3.5 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 hover:from-emerald-500 hover:to-cyan-500 active:scale-95 text-white font-extrabold text-sm shadow-xl shadow-emerald-600/25 transition-transform flex items-center justify-center gap-2 cursor-pointer touch-press"
                    >
                        <span x-show="isSubmitting" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span x-text="isSubmitting ? 'Memproses Pendaftaran…' : (method === 'ktp' ? 'Kirim Pendaftaran & Berkas KTP ➔' : 'Selesaikan Pendaftaran Cepat Gmail ➔')"></span>
                    </button>
                </form>
            </div>

            <!-- Footer Tautan Kembali ke Login -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 text-center">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Sudah memiliki akun warga atau pengurus?
                    <a href="{{ route('login') }}" class="font-black text-emerald-600 dark:text-emerald-400 hover:underline">
                        Masuk ke Dashboard Sistem ➔
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- Footer Halaman Register -->
    <footer class="max-w-5xl w-full mx-auto text-center text-xs text-slate-400 pt-6 pb-8">
        &copy; {{ date('Y') }} E-WARGA by Fabian Ganteng. Seluruh hak cipta dilindungi undang-undang.
    </footer>
</div>
@endsection
