<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\CashBook;
use App\Models\GateLog;
use App\Models\House;
use App\Models\Payment;
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
    }
}
