<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\CashBook;
use App\Models\GateLog;
use App\Models\House;
use App\Models\LetterRequest;
use App\Models\Payment;
use App\Models\PosyanduMember;
use App\Models\PosyanduRecord;
use App\Models\SecuritySchedule;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $defaultStaffPassword = Hash::make('admin123');
        $defaultWargaPassword = Hash::make('warga123');

        // 1. Super Admin Platform
        User::create([
            'tenant_id' => null,
            'house_id' => null,
            'name' => 'Super Admin E-WARGA',
            'phone_number' => '08110000001',
            'email' => 'superadmin@ewarga.id',
            'password' => $defaultStaffPassword,
            'role' => 'super_admin',
            'is_family_head' => false,
            'status' => 'active',
        ]);

        // 2. Tenant / Komplek Contoh
        $tenant = Tenant::create([
            'name' => 'Cluster Melati Mas',
            'slug' => 'cluster-melati-mas',
            'rt_rw' => 'RT 05 / RW 12',
            'province' => 'Jawa Barat',
            'city' => 'Kota Bandung',
            'district' => 'Coblong',
            'postal_code' => '40132',
            'subscription_plan' => 'pro',
            'is_active' => true,
        ]);

        // 3. Rumah-rumah di Komplek
        $houseA1 = House::create([
            'tenant_id' => $tenant->id,
            'block_name' => 'Blok A',
            'house_number' => '01',
            'full_address' => 'Jl. Melati Mas I No. 01',
            'occupancy_status' => 'occupied',
        ]);

        $houseA2 = House::create([
            'tenant_id' => $tenant->id,
            'block_name' => 'Blok A',
            'house_number' => '02',
            'full_address' => 'Jl. Melati Mas I No. 02',
            'occupancy_status' => 'occupied',
        ]);

        $houseB5 = House::create([
            'tenant_id' => $tenant->id,
            'block_name' => 'Blok B',
            'house_number' => '05',
            'full_address' => 'Jl. Melati Mas V No. 05',
            'occupancy_status' => 'occupied',
        ]);

        $houseC12 = House::create([
            'tenant_id' => $tenant->id,
            'block_name' => 'Blok C',
            'house_number' => '12',
            'full_address' => 'Jl. Melati Mas IX No. 12',
            'occupancy_status' => 'occupied',
        ]);

        // 4. Pengurus Lingkungan RT (Password: admin123)
        $ketuaRt = User::create([
            'tenant_id' => $tenant->id,
            'house_id' => $houseA1->id,
            'name' => 'Bpk. H. Sutrisno (Ketua RT)',
            'phone_number' => '08110000002',
            'email' => 'ketuart@ewarga.id',
            'password' => $defaultStaffPassword,
            'role' => 'ketua_rt',
            'is_family_head' => true,
            'status' => 'active',
        ]);

        $sekretaris = User::create([
            'tenant_id' => $tenant->id,
            'house_id' => $houseA2->id,
            'name' => 'Ibu Farida (Sekretaris RT)',
            'phone_number' => '08110000003',
            'email' => 'sekretaris@ewarga.id',
            'password' => $defaultStaffPassword,
            'role' => 'sekretaris',
            'is_family_head' => true,
            'status' => 'active',
        ]);

        $bendahara = User::create([
            'tenant_id' => $tenant->id,
            'house_id' => $houseB5->id,
            'name' => 'Bpk. Joko Wardoyo (Bendahara RT)',
            'phone_number' => '08110000004',
            'email' => 'bendahara@ewarga.id',
            'password' => $defaultStaffPassword,
            'role' => 'bendahara',
            'is_family_head' => true,
            'status' => 'active',
        ]);

        $satpam = User::create([
            'tenant_id' => $tenant->id,
            'house_id' => null,
            'name' => 'Pak Slamet (Komandan Satpam)',
            'phone_number' => '08110000005',
            'email' => 'satpam@ewarga.id',
            'password' => $defaultStaffPassword,
            'role' => 'satpam',
            'is_family_head' => false,
            'status' => 'active',
        ]);

        // 5. Warga Contoh (Kepala Keluarga & Anggota)
        $wargaBambang = User::create([
            'tenant_id' => $tenant->id,
            'house_id' => $houseC12->id,
            'name' => 'Bpk. Bambang Pamungkas',
            'phone_number' => '081234567890',
            'email' => 'bambang@warga.id',
            'password' => $defaultWargaPassword,
            'role' => 'warga',
            'is_family_head' => true,
            'status' => 'active',
        ]);

        $wargaDewi = User::create([
            'tenant_id' => $tenant->id,
            'house_id' => $houseC12->id,
            'name' => 'Ibu Dewi Lestari',
            'phone_number' => '081234567891',
            'email' => 'dewi@warga.id',
            'password' => $defaultWargaPassword,
            'role' => 'warga',
            'is_family_head' => false,
            'status' => 'active',
        ]);

        // 6. Kendaraan
        Vehicle::create([
            'house_id' => $houseC12->id,
            'user_id' => $wargaBambang->id,
            'license_plate' => 'B 1892 KRF',
            'type' => 'car',
            'brand_model' => 'Honda CR-V Hitam',
            'rfid_card_uid' => 'RFID-BAMBANG-01',
        ]);

        // 7. Perangkat Gerbang Pintar (Smart Gate)
        $smartGate = SmartGate::create([
            'tenant_id' => $tenant->id,
            'name' => 'Gerbang Utama Pos Timur',
            'device_uid' => 'ESP32-GATE-MELATI-01',
            'secret_token' => Str::random(32),
            'ip_address' => '192.168.1.200',
            'status' => 'online',
            'last_ping_at' => now(),
        ]);

        // 8. Riwayat Log Gerbang
        GateLog::create([
            'tenant_id' => $tenant->id,
            'smart_gate_id' => $smartGate->id,
            'user_id' => $wargaBambang->id,
            'access_method' => 'mobile_button',
            'direction' => 'entry',
            'status' => 'granted',
            'reason' => 'Akses Tombol Aplikasi HP Warga',
            'triggered_at' => Carbon::now()->subMinutes(12),
        ]);

        GateLog::create([
            'tenant_id' => $tenant->id,
            'smart_gate_id' => $smartGate->id,
            'user_id' => $satpam->id,
            'access_method' => 'satpam_manual',
            'direction' => 'entry',
            'status' => 'granted',
            'reason' => 'Buka Manual Pos Satpam - Tamu Keluarga',
            'triggered_at' => Carbon::now()->subMinutes(45),
        ]);

        // 9. Keuangan & Kas RT
        CashBook::create([
            'tenant_id' => $tenant->id,
            'created_by_user_id' => $bendahara->id,
            'type' => 'income',
            'category' => 'Iuran Warga',
            'title' => 'Penerimaan Iuran Kas Warga Periode Berjalan',
            'amount' => 18500000.00,
            'transaction_date' => Carbon::now()->startOfMonth(),
            'notes' => 'Terkumpul dari 123 rumah',
        ]);

        CashBook::create([
            'tenant_id' => $tenant->id,
            'created_by_user_id' => $bendahara->id,
            'type' => 'expense',
            'category' => 'Gaji Keamanan',
            'title' => 'Gaji 4 Petugas Satpam Pos Timur & Barat',
            'amount' => 8000000.00,
            'transaction_date' => Carbon::now()->startOfMonth()->addDays(2),
            'notes' => 'Pembayaran gaji satpam operasional',
        ]);

        CashBook::create([
            'tenant_id' => $tenant->id,
            'created_by_user_id' => $bendahara->id,
            'type' => 'expense',
            'category' => 'Listrik & Kebersihan',
            'title' => 'Listrik Pos Satpam, Pompa Air Taman, & Iuran Truk Sampah',
            'amount' => 1750000.00,
            'transaction_date' => Carbon::now()->startOfMonth()->addDays(5),
            'notes' => 'Operasional bulanan lingkungan',
        ]);

        // 10. Tagihan Iuran Warga
        $billingBambang = Billing::create([
            'tenant_id' => $tenant->id,
            'house_id' => $houseC12->id,
            'billing_period' => Carbon::now()->startOfMonth(),
            'amount' => 150000.00,
            'description' => 'Iuran Keamanan, Kebersihan, & Kas Lingkungan RT',
            'status' => 'paid',
            'due_date' => Carbon::now()->startOfMonth()->addDays(15),
        ]);

        Payment::create([
            'billing_id' => $billingBambang->id,
            'payer_user_id' => $wargaBambang->id,
            'payment_method' => 'manual_transfer',
            'amount_paid' => 150000.00,
            'verified_by_user_id' => $bendahara->id,
            'verified_at' => Carbon::now()->subDays(2),
        ]);

        // 11. Tiket Tamu Contoh
        Visitor::create([
            'tenant_id' => $tenant->id,
            'host_house_id' => $houseC12->id,
            'invited_by_user_id' => $wargaBambang->id,
            'guest_name' => 'Kurir Paket J&T Express',
            'guest_type' => 'courier',
            'vehicle_plate' => 'B 4091 TTR',
            'qr_token' => 'PASS-' . strtoupper(Str::random(10)),
            'valid_from' => Carbon::now()->subHour(),
            'valid_until' => Carbon::now()->addHours(3),
            'is_used' => false,
        ]);

        // 12. Jadwal Keamanan Bulan Ini (Satpam & Ronda Warga)
        SecuritySchedule::create([
            'tenant_id' => $tenant->id,
            'user_id' => $satpam->id,
            'schedule_type' => 'satpam_shift',
            'duty_date' => Carbon::today(),
            'shift_name' => 'Shift Siang (15:00 - 23:00)',
            'start_time' => '15:00:00',
            'end_time' => '23:00:00',
            'status' => 'present',
            'checked_in_at' => Carbon::today()->setHour(14)->setMinute(55),
            'notes' => 'Pos Gerbang Utama Barat & Timur',
        ]);

        SecuritySchedule::create([
            'tenant_id' => $tenant->id,
            'user_id' => $satpam->id,
            'schedule_type' => 'satpam_shift',
            'duty_date' => Carbon::tomorrow(),
            'shift_name' => 'Shift Pagi (07:00 - 15:00)',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'status' => 'scheduled',
            'notes' => 'Patroli lingkungan pukul 10:00 WIB',
        ]);

        SecuritySchedule::create([
            'tenant_id' => $tenant->id,
            'user_id' => $wargaBambang->id,
            'schedule_type' => 'ronda_warga',
            'duty_date' => Carbon::today(),
            'shift_name' => 'Regu Ronda Malam Kamis (Blok C)',
            'start_time' => '22:00:00',
            'end_time' => '04:00:00',
            'status' => 'scheduled',
            'notes' => 'Membawa senter & keliling lingkungan',
        ]);

        // 13. Data Warga Posyandu Digital (Tracking Balita, Anak, Ibu Hamil, Ibu-ibu, Lansia)
        $balitaRayyan = PosyanduMember::create([
            'tenant_id' => $tenant->id,
            'user_id' => $wargaBambang->id,
            'family_card_number' => '3216012903820001',
            'nik' => '3216010505240003',
            'name' => 'Rayyan Al-Fatih',
            'gender' => 'L',
            'birth_date' => Carbon::now()->subMonths(18), // 18 bulan
            'category' => 'balita',
            'parent_name' => 'Bambang Pamungkas & Dewi Lestari',
            'address' => 'Blok C No. 12',
            'phone' => '081234567890',
            'notes' => 'Alergi dingin ringan',
        ]);

        $balitaAlya = PosyanduMember::create([
            'tenant_id' => $tenant->id,
            'user_id' => null,
            'family_card_number' => '3216012903820002',
            'nik' => '3216011210230005',
            'name' => 'Alya Putri Salsabila',
            'gender' => 'P',
            'birth_date' => Carbon::now()->subMonths(24), // 24 bulan
            'category' => 'balita',
            'parent_name' => 'Budi Santoso & Siti Aminah',
            'address' => 'Blok B No. 04',
            'phone' => '081298765432',
            'notes' => 'Pemantauan tinggi badan rutin',
        ]);

        $anakKenzo = PosyanduMember::create([
            'tenant_id' => $tenant->id,
            'user_id' => $wargaBambang->id,
            'family_card_number' => '3216012903820001',
            'nik' => '3216011508180002',
            'name' => 'Kenzo Putra Pratama',
            'gender' => 'L',
            'birth_date' => Carbon::now()->subYears(7), // 7 tahun
            'category' => 'anak',
            'parent_name' => 'Bambang Pamungkas',
            'address' => 'Blok C No. 12',
            'phone' => '081234567890',
            'notes' => 'Kelas 2 SD, aktif olahraga',
        ]);

        $ibuHamilSiti = PosyanduMember::create([
            'tenant_id' => $tenant->id,
            'user_id' => null,
            'family_card_number' => '3216012903820003',
            'nik' => '3216014506950007',
            'name' => 'Ny. Siti Nurhaliza',
            'gender' => 'P',
            'birth_date' => Carbon::now()->subYears(28),
            'category' => 'ibu_hamil',
            'parent_name' => 'Suami: Rahmat Hidayat',
            'address' => 'Blok A No. 07',
            'phone' => '081377889900',
            'notes' => 'HPL: 20 Desember 2026, Trimester 3',
        ]);

        $ibuKaderRina = PosyanduMember::create([
            'tenant_id' => $tenant->id,
            'user_id' => null,
            'family_card_number' => '3216012903820004',
            'nik' => '3216015509880008',
            'name' => 'Ibu Rina Marlina (Ketua Kader Posyandu)',
            'gender' => 'P',
            'birth_date' => Carbon::now()->subYears(36),
            'category' => 'ibu',
            'parent_name' => 'Suami: Deden Kurniawan',
            'address' => 'Blok D No. 02',
            'phone' => '081566778899',
            'notes' => 'Kader Aktif PKK & Posyandu Melati',
        ]);

        $lansiaMbahSastro = PosyanduMember::create([
            'tenant_id' => $tenant->id,
            'user_id' => null,
            'family_card_number' => '3216012903820005',
            'nik' => '3216010101560009',
            'name' => 'Opa Sastro Wardoyo',
            'gender' => 'L',
            'birth_date' => Carbon::now()->subYears(70),
            'category' => 'lansia',
            'parent_name' => 'Keluarga Bpk. Joko Wardoyo',
            'address' => 'Blok B No. 05',
            'phone' => '08110000004',
            'notes' => 'Riwayat hipertensi ringan',
        ]);

        // Catatan Pemeriksaan Posyandu Bulanan
        PosyanduRecord::create([
            'tenant_id' => $tenant->id,
            'posyandu_member_id' => $balitaRayyan->id,
            'checkup_date' => Carbon::now()->subDays(5),
            'weight_kg' => 11.2,
            'height_cm' => 84.5,
            'head_circumference_cm' => 47.0,
            'arm_circumference_cm' => 15.0,
            'nutrition_status' => 'gizi_baik',
            'vitamins_or_vaccines' => 'Vitamin A Merah kapsul, DPT Booster',
            'examiner_name' => 'Bidan Nina Kurnia, S.Tr.Keb',
            'action_plan' => 'Tumbuh kembang sangat baik sesuai kurva WHO KMS.',
        ]);

        PosyanduRecord::create([
            'tenant_id' => $tenant->id,
            'posyandu_member_id' => $balitaAlya->id,
            'checkup_date' => Carbon::now()->subDays(5),
            'weight_kg' => 9.4,
            'height_cm' => 77.0,
            'head_circumference_cm' => 45.2,
            'arm_circumference_cm' => 13.5,
            'nutrition_status' => 'risiko_stunting',
            'vitamins_or_vaccines' => 'Vitamin A, PMT Biskuit Balita',
            'examiner_name' => 'Bidan Nina Kurnia, S.Tr.Keb',
            'action_plan' => 'Diberikan PMT pemulihan & konseling gizi protein hewani telur.',
        ]);

        PosyanduRecord::create([
            'tenant_id' => $tenant->id,
            'posyandu_member_id' => $ibuHamilSiti->id,
            'checkup_date' => Carbon::now()->subDays(5),
            'weight_kg' => 64.0,
            'height_cm' => 158.0,
            'arm_circumference_cm' => 25.5,
            'blood_pressure' => '115/75',
            'gestational_age_weeks' => '32 Minggu',
            'nutrition_status' => 'normal',
            'vitamins_or_vaccines' => 'Tablet Tambah Darah (TTD) 30 butir, Kalsium',
            'examiner_name' => 'Bidan Nina Kurnia, S.Tr.Keb',
            'action_plan' => 'Kondisi janin dan ibu sehat optimal.',
        ]);

        PosyanduRecord::create([
            'tenant_id' => $tenant->id,
            'posyandu_member_id' => $lansiaMbahSastro->id,
            'checkup_date' => Carbon::now()->subDays(5),
            'weight_kg' => 62.0,
            'height_cm' => 165.0,
            'blood_pressure' => '145/90',
            'blood_sugar' => 125,
            'cholesterol' => 190,
            'uric_acid' => 6.2,
            'nutrition_status' => 'hipertensi',
            'vitamins_or_vaccines' => 'Vitamin B Kompleks & Amlodipine 5mg',
            'examiner_name' => 'dr. Hendra Pratama (Puskesmas)',
            'action_plan' => 'Kurangi asupan garam, perbanyak jalan santai pagi.',
        ]);

        // 14. Data Pengajuan Surat Pengantar RT Online
        LetterRequest::create([
            'tenant_id' => $tenant->id,
            'user_id' => $wargaBambang->id,
            'letter_type' => 'domisili',
            'letter_number' => '470/001/RT.04/RW.02/XI/2026',
            'applicant_name' => 'Bambang Pamungkas',
            'nik' => '3216012903820001',
            'house_block' => 'Blok C No. 12',
            'purpose' => 'Persyaratan pembukaan rekening bank syariah & pengurusan paspor',
            'supporting_data' => 'KTP terlampir, lunas iuran IPL tahun berjalan',
            'status' => 'approved',
            'qr_verification_code' => 'QR-SURAT-DOMISILI-001',
            'signed_at' => Carbon::now()->subDays(2),
            'signed_by' => $ketuaRt->id,
        ]);

        LetterRequest::create([
            'tenant_id' => $tenant->id,
            'user_id' => $wargaBambang->id,
            'letter_type' => 'pengantar_ktp',
            'letter_number' => null,
            'applicant_name' => 'Bambang Pamungkas',
            'nik' => '3216012903820001',
            'house_block' => 'Blok C No. 12',
            'purpose' => 'Perpanjangan e-KTP hilang ke Kantor Kelurahan',
            'supporting_data' => 'Surat laporan kehilangan polsek terlampir',
            'status' => 'pending',
            'qr_verification_code' => null,
        ]);
    }
}
