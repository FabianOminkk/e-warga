@extends('layouts.app')

@section('content')
<div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-8" x-data="wargaProfileApp()">

    <!-- ======================================================== -->
    <!-- 1. HEADER HALAMAN PENGATURAN AKUN & DATA DIRI            -->
    <!-- ======================================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('warga.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400">Portal Layanan Warga</a>
                <span>/</span>
                <span class="text-emerald-600 dark:text-emerald-400 font-bold">Pengaturan Akun & Data Diri</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                Pengaturan Akun & Kelengkapan Identitas KTP
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Lengkapi identitas kependudukan resmi Anda untuk otorisasi Ketua RW, integrasi RFID gerbang, dan aktivasi seluruh layanan warga.
            </p>
        </div>

        <div class="shrink-0">
            <a href="{{ route('warga.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-black shadow-xs transition flex items-center gap-2">
                <span>← Kembali ke Portal Warga</span>
            </a>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 2. GRID 12 KOLOM FORMULIR PENGATURAN AKUN TERPISAH       -->
    <!-- ======================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- KOLOM KIRI (8 KOLOM): FORMULIR DATA DIRI & KTP -->
        <div class="lg:col-span-8 space-y-6">
            
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-xs space-y-6">
                
                <div class="pb-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <span>🪪</span>
                            <span>Berkas Identitas Kependudukan Resmi</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pastikan data yang Anda masukkan sesuai dengan Kartu Tanda Penduduk (KTP) fisik asli.</p>
                    </div>
                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-full border {{ $user->verification_badge['badge'] }}">
                        {{ $user->verification_badge['icon'] }} {{ $user->verification_badge['label'] }}
                    </span>
                </div>

                <form action="{{ route('warga.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <!-- Nama Lengkap & No WhatsApp -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                Nama Lengkap Sesuai KTP <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="name" 
                                value="{{ old('name', $user->name) }}" 
                                required 
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:border-emerald-500 outline-hidden font-semibold"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                Nomor WhatsApp Aktif <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="phone_number" 
                                value="{{ old('phone_number', $user->phone_number) }}" 
                                required 
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:border-emerald-500 outline-hidden font-mono"
                            >
                        </div>
                    </div>

                    <!-- NIK KTP (16 Digit Wajib) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                            Nomor Induk Kependudukan (NIK - Wajib 16 Digit) <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="nik" 
                            maxlength="16" 
                            minlength="16"
                            pattern="\d{16}"
                            value="{{ old('nik', $user->nik) }}" 
                            placeholder="Contoh: 3275011205820001" 
                            required 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-base focus:border-emerald-500 outline-hidden font-mono tracking-widest font-black"
                        >
                        <p class="text-[11px] text-slate-400 mt-1">
                            NIK KTP ini akan dihubungkan secara otomatis dengan chip RFID / E-KTP untuk membuka palang portal komplek.
                        </p>
                    </div>

                    <!-- Tempat & Tanggal Lahir -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                Tempat Lahir Sesuai KTP <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="birth_place" 
                                value="{{ old('birth_place', $user->birth_place) }}" 
                                placeholder="Kota Lahir (Misal: Bandung)" 
                                required 
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:border-emerald-500 outline-hidden"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                Tanggal Lahir <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="date" 
                                name="birth_date" 
                                value="{{ old('birth_date', $user->birth_date ? $user->birth_date->format('Y-m-d') : '') }}" 
                                required 
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:border-emerald-500 outline-hidden font-semibold"
                            >
                        </div>
                    </div>

                    <!-- Jenis Kelamin, Agama, Pekerjaan -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                Jenis Kelamin <span class="text-rose-500">*</span>
                            </label>
                            <select name="gender" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:border-emerald-500 outline-hidden font-semibold">
                                <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                Agama
                            </label>
                            <select name="religion" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:border-emerald-500 outline-hidden font-semibold">
                                <option value="Islam" {{ old('religion', $user->religion) === 'Islam' ? 'selected' : '' }}>Islam</option>
                                <option value="Kristen" {{ old('religion', $user->religion) === 'Kristen' ? 'selected' : '' }}>Kristen</option>
                                <option value="Katolik" {{ old('religion', $user->religion) === 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                <option value="Hindu" {{ old('religion', $user->religion) === 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                <option value="Buddha" {{ old('religion', $user->religion) === 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                <option value="Konghucu" {{ old('religion', $user->religion) === 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                Pekerjaan
                            </label>
                            <input 
                                type="text" 
                                name="occupation" 
                                value="{{ old('occupation', $user->occupation) }}" 
                                placeholder="Karyawan / Wiraswasta" 
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:border-emerald-500 outline-hidden"
                            >
                        </div>
                    </div>

                    <!-- Unit Rumah Tinggal & Upload Foto KTP Asli -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                Unit Rumah Tinggal di Komplek
                            </label>
                            <select name="house_id" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:border-emerald-500 outline-hidden font-semibold">
                                @foreach($houses as $h)
                                    <option value="{{ $h->id }}" {{ old('house_id', $user->house_id) == $h->id ? 'selected' : '' }}>
                                        {{ $h->block_and_number }} ({{ $h->full_address }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                Unggah Foto Fisik KTP Asli
                            </label>
                            <input 
                                type="file" 
                                name="ktp_photo" 
                                accept="image/*" 
                                class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-emerald-950 dark:file:text-emerald-300"
                            >
                        </div>
                    </div>

                    <!-- Alamat KTP -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                            Alamat Lengkap Sesuai KTP <span class="text-rose-500">*</span>
                        </label>
                        <textarea 
                            name="address_ktp" 
                            rows="2" 
                            required 
                            placeholder="Tuliskan alamat lengkap beserta RT/RW, Kelurahan, dan Kota sesuai KTP Anda" 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:border-emerald-500 outline-hidden leading-relaxed"
                        >{{ old('address_ktp', $user->address_ktp) }}</textarea>
                    </div>

                    <!-- Tombol Submit -->
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-4">
                        <div class="text-xs text-slate-400">
                            Status saat ini: <strong class="{{ $user->canAccessGate() ? 'text-emerald-600' : 'text-amber-600' }}">{{ $user->verification_badge['label'] }}</strong>
                        </div>
                        <button 
                            type="submit" 
                            class="px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-xs shadow-lg shadow-emerald-600/30 transition flex items-center gap-2 cursor-pointer"
                        >
                            <span>Simpan & Ajukan Pengesahan ke Ketua RW</span>
                            <span>➔</span>
                        </button>
                    </div>

                </form>

            </div>

        </div>

        <!-- KOLOM KANAN (4 KOLOM): KARTU E-KTP, PROGRES ACC, GANTI PASSWORD -->
        <div class="lg:col-span-4 space-y-6">

            <!-- 1. KARTU E-KTP DIGITAL RESMI WARGA -->
            <div class="bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 rounded-3xl p-6 text-white shadow-xl border border-blue-800/40 relative overflow-hidden">
                <div class="flex items-center justify-between pb-3 border-b border-blue-800/60 mb-4">
                    <div class="text-left">
                        <div class="text-[10px] uppercase font-black tracking-widest text-blue-300">REPUBLIK INDONESIA</div>
                        <div class="text-xs font-bold text-slate-200">KARTU TANDA PENDUDUK DIGITAL</div>
                    </div>
                    <span class="text-lg">🇮🇩</span>
                </div>

                @if($user->ktp_photo)
                    <div class="mb-4 rounded-xl overflow-hidden border border-blue-700/50 bg-slate-950">
                        <img src="{{ asset($user->ktp_photo) }}" alt="Foto KTP Warga" class="w-full h-36 object-cover">
                    </div>
                @endif

                <div class="space-y-2.5 text-xs">
                    <div>
                        <div class="text-[10px] text-blue-300 font-bold uppercase">Nomor Induk Kependudukan (NIK)</div>
                        <div class="font-mono text-sm font-black tracking-wider text-amber-300">
                            {{ $user->nik ?? 'BELUM DILENGKAPI' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <div>
                            <div class="text-[10px] text-blue-300 font-bold uppercase">Nama Lengkap</div>
                            <div class="font-bold truncate text-slate-100">{{ $user->name }}</div>
                        </div>
                        <div>
                            <div class="text-[10px] text-blue-300 font-bold uppercase">Tempat/Tgl Lahir</div>
                            <div class="font-semibold text-slate-200 truncate">
                                {{ $user->birth_place ?? '-' }}, {{ $user->birth_date ? $user->birth_date->format('d/m/Y') : '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="pt-1">
                        <div class="text-[10px] text-blue-300 font-bold uppercase">UID Tag RFID E-KTP Gerbang</div>
                        <div class="font-mono font-bold text-cyan-300 flex items-center justify-between">
                            <span>{{ $user->rfid_tag ?? 'Belum Ada RFID' }}</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full {{ $user->canAccessGate() ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-rose-500/20 text-rose-300 border border-rose-500/40' }}">
                                {{ $user->canAccessGate() ? 'AKTIF DI PALANG' : 'NON-AKTIF' }}
                            </span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-blue-800/60 flex items-center justify-between text-[11px]">
                        <div>
                            <span class="text-slate-400 block text-[9px] uppercase">Otorisasi Legalitas:</span>
                            <strong class="{{ $user->canAccessGate() ? 'text-emerald-400' : 'text-amber-400' }}">
                                {{ $user->verification_badge['label'] }}
                            </strong>
                        </div>
                        @if($user->verifiedBy)
                            <div class="text-right">
                                <span class="text-slate-400 block text-[9px]">Diverifikasi oleh:</span>
                                <span class="text-[10px] text-slate-200 font-semibold">{{ $user->verifiedBy->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 2. STATUS ALUR INTEGRASI VERIFIKASI RW & SATPAM -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
                <h3 class="font-black text-slate-900 dark:text-white text-sm uppercase tracking-wider">
                    Tahapan Otorisasi & Keamanan
                </h3>
                <div class="space-y-3 text-xs">
                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs shrink-0 {{ $user->nik ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-500' }}">
                            {{ $user->nik ? '✓' : '1' }}
                        </span>
                        <div>
                            <div class="font-extrabold text-slate-800 dark:text-slate-200">Input Data KTP & Foto</div>
                            <div class="text-[11px] text-slate-400">{{ $user->nik ? 'Selesai diunggah' : 'Wajib diisi di formulir kiri' }}</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs shrink-0 {{ $user->verification_status === 'approved' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : ($user->verification_status === 'pending_rw' ? 'bg-amber-100 text-amber-700 animate-pulse' : 'bg-slate-100 text-slate-500') }}">
                            {{ $user->verification_status === 'approved' ? '✓' : '2' }}
                        </span>
                        <div>
                            <div class="font-extrabold text-slate-800 dark:text-slate-200">Pengesahan oleh Ketua RW</div>
                            <div class="text-[11px] text-slate-400">{{ $user->verification_status === 'approved' ? 'Telah di-ACC oleh Bpk. H. Rahmat Hidayat' : ($user->verification_status === 'pending_rw' ? 'Sedang Ditinjau Ketua RW' : 'Menunggu Berkas Dikirim') }}</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs shrink-0 {{ $user->canAccessGate() ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-500' }}">
                            {{ $user->canAccessGate() ? '✓' : '3' }}
                        </span>
                        <div>
                            <div class="font-extrabold text-slate-800 dark:text-slate-200">Sinkronisasi ke Pos Satpam</div>
                            <div class="text-[11px] text-slate-400">{{ $user->canAccessGate() ? 'Whitelist RFID E-KTP Aktif di Pos Jaga' : 'Menunggu ACC Pak RW' }}</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs shrink-0 {{ $user->canAccessGate() ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-500' }}">
                            {{ $user->canAccessGate() ? '✓' : '4' }}
                        </span>
                        <div>
                            <div class="font-extrabold text-slate-800 dark:text-slate-200">Palang Gerbang Otomatis Aktif</div>
                            <div class="text-[11px] text-slate-400">{{ $user->canAccessGate() ? 'Buka via Tombol HP & Tap Kartu Aktif' : 'Palang Terkunci' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. FORMULIR GANTI PASSWORD MANDIRI -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
                <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-base">🔑</span>
                    <h3 class="font-black text-slate-900 dark:text-white text-sm uppercase tracking-wider">Ganti Password Akun</h3>
                </div>

                <form action="{{ route('warga.change-password') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Password Saat Ini</label>
                        <input type="password" name="current_password" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:border-emerald-500 outline-hidden">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Password Baru (Min 6 Karakter)</label>
                        <input type="password" name="new_password" required minlength="6" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:border-emerald-500 outline-hidden">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Konfirmasi Password Baru</label>
                        <input type="password" name="new_password_confirmation" required minlength="6" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:border-emerald-500 outline-hidden">
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl font-bold text-xs shadow-xs transition">
                        Perbarui Password
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>

<script>
function wargaProfileApp() {
    return {
        init() {
            // Inisialisasi
        }
    }
}
</script>
@endsection
