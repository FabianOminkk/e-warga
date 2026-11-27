<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResidentController extends Controller
{
    private function ensureManager()
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['super_admin', 'ketua_rw', 'ketua_rt', 'sekretaris', 'bendahara', 'pengurus_rt'])) {
            return redirect()->route('warga.index')->with('error', 'Akses ditolak: Hanya pengurus RT/RW yang dapat mengakses Data Warga & Kartu Keluarga.');
        }

        return null;
    }

    /**
     * Halaman Manajemen Data Warga, Unit Rumah, Kartu Keluarga (KK), & Role Akun.
     */
    public function index(Request $request)
    {
        if ($redirect = $this->ensureManager()) {
            return $redirect;
        }

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $search = trim((string) $request->query('search', ''));
        $filterRole = $request->query('role', 'all');
        $filterVerification = $request->query('verification', 'all');
        $activeTab = $request->query('tab', 'kk'); // 'kk' or 'users'

        $housesQuery = House::where('tenant_id', $tenant?->id)
            ->with(['residents' => fn ($q) => $q->orderByDesc('is_family_head')->orderBy('name'), 'vehicles'])
            ->orderBy('block_name')
            ->orderBy('house_number');

        if ($search !== '') {
            $housesQuery->where(function ($q) use ($search) {
                $q->where('block_name', 'like', "%{$search}%")
                    ->orWhere('house_number', 'like', "%{$search}%")
                    ->orWhere('no_kk', 'like', "%{$search}%")
                    ->orWhere('full_address', 'like', "%{$search}%")
                    ->orWhereHas('residents', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%")
                            ->orWhere('no_kk', 'like', "%{$search}%");
                    });
            });
        }

        $houses = $housesQuery->get();

        $usersQuery = User::where('tenant_id', $tenant?->id)
            ->with('house')
            ->orderByRaw("CASE WHEN role = 'warga' THEN 2 ELSE 1 END")
            ->orderBy('name');

        if ($search !== '') {
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('no_kk', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($filterRole !== 'all') {
            $usersQuery->where('role', $filterRole);
        }

        if ($filterVerification !== 'all') {
            $usersQuery->where('verification_status', $filterVerification);
        }

        $residents = $usersQuery->get();
        $allHouses = House::where('tenant_id', $tenant?->id)
            ->orderBy('block_name')
            ->orderBy('house_number')
            ->get();

        $distinctKkFromUsers = User::where('tenant_id', $tenant?->id)->whereNotNull('no_kk')->where('no_kk', '!=', '')->pluck('no_kk');
        $distinctKkFromHouses = House::where('tenant_id', $tenant?->id)->whereNotNull('no_kk')->where('no_kk', '!=', '')->pluck('no_kk');
        $totalKkCount = $distinctKkFromUsers->merge($distinctKkFromHouses)->unique()->count();

        $stats = [
            'total_residents' => User::where('tenant_id', $tenant?->id)->where('role', 'warga')->count(),
            'total_users' => User::where('tenant_id', $tenant?->id)->count(),
            'total_houses' => $allHouses->count(),
            'total_kk' => $totalKkCount,
            'verified_warga' => User::where('tenant_id', $tenant?->id)->where('role', 'warga')->where('verification_status', 'approved')->count(),
        ];

        return view('pages.residents', compact(
            'tenant',
            'houses',
            'allHouses',
            'residents',
            'stats',
            'search',
            'filterRole',
            'filterVerification',
            'activeTab'
        ));
    }

    /**
     * Tambah unit rumah & nomor Kartu Keluarga (KK).
     */
    public function storeHouse(Request $request)
    {
        if ($redirect = $this->ensureManager()) {
            return $redirect;
        }

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        if (!$tenant) {
            return back()->with('error', 'Data wilayah belum tersedia.');
        }

        $validated = $request->validate([
            'block_name' => 'required|string|max:20',
            'house_number' => 'required|string|max:20',
            'no_kk' => 'nullable|digits:16',
            'full_address' => 'nullable|string|max:300',
            'occupancy_status' => 'required|in:occupied,vacant,rented',
        ], [
            'no_kk.digits' => 'Nomor Kartu Keluarga (KK) harus tepat 16 digit angka.',
        ]);

        $blockName = strtoupper(trim($validated['block_name']));
        $houseNumber = trim($validated['house_number']);

        $exists = House::where('tenant_id', $tenant->id)
            ->where('block_name', $blockName)
            ->where('house_number', $houseNumber)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', "Unit rumah {$blockName} No. {$houseNumber} sudah terdaftar di wilayah ini.");
        }

        $house = House::create([
            'tenant_id' => $tenant->id,
            'block_name' => $blockName,
            'house_number' => $houseNumber,
            'no_kk' => $validated['no_kk'] ?? null,
            'full_address' => !empty($validated['full_address']) ? $validated['full_address'] : "{$blockName} No. {$houseNumber}, {$tenant->name}",
            'occupancy_status' => $validated['occupancy_status'],
        ]);

        ActivityLogger::log(
            'resident.house_created',
            "Pengurus menambahkan unit rumah {$house->block_and_number}" . ($house->no_kk ? " (KK {$house->no_kk})" : '') . '.',
            'info',
            ['house_id' => $house->id],
            Auth::user(),
            $house
        );

        return redirect()->route('residents.index', ['tab' => 'kk'])->with('success', "Unit rumah {$house->block_and_number} berhasil ditambahkan.");
    }

    /**
     * Perbarui unit rumah & nomor Kartu Keluarga (KK).
     */
    public function updateHouse(Request $request, int $id)
    {
        if ($redirect = $this->ensureManager()) {
            return $redirect;
        }

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $house = House::where('tenant_id', $tenant?->id)->findOrFail($id);

        $validated = $request->validate([
            'block_name' => 'required|string|max:20',
            'house_number' => 'required|string|max:20',
            'no_kk' => 'nullable|digits:16',
            'full_address' => 'nullable|string|max:300',
            'occupancy_status' => 'required|in:occupied,vacant,rented',
        ], [
            'no_kk.digits' => 'Nomor Kartu Keluarga (KK) harus tepat 16 digit angka.',
        ]);

        $blockName = strtoupper(trim($validated['block_name']));
        $houseNumber = trim($validated['house_number']);

        $duplicate = House::where('tenant_id', $house->tenant_id)
            ->where('id', '!=', $house->id)
            ->where('block_name', $blockName)
            ->where('house_number', $houseNumber)
            ->exists();

        if ($duplicate) {
            return back()->with('error', "Unit rumah {$blockName} No. {$houseNumber} sudah digunakan data lain.");
        }

        $house->update([
            'block_name' => $blockName,
            'house_number' => $houseNumber,
            'no_kk' => $validated['no_kk'] ?? null,
            'full_address' => !empty($validated['full_address']) ? $validated['full_address'] : $house->full_address,
            'occupancy_status' => $validated['occupancy_status'],
        ]);

        // Sinkronkan No. KK ke warga penghuni rumah yang belum memiliki No. KK
        if (!empty($validated['no_kk'])) {
            User::where('house_id', $house->id)
                ->where(fn ($q) => $q->whereNull('no_kk')->orWhere('no_kk', ''))
                ->update(['no_kk' => $validated['no_kk']]);
        }

        return redirect()->route('residents.index', ['tab' => 'kk'])->with('success', "Data rumah & Kartu Keluarga {$house->block_and_number} berhasil diperbarui.");
    }

    /**
     * Tambah akun warga / anggota keluarga atau staf baru oleh pengurus.
     */
    public function storeResident(Request $request)
    {
        if ($redirect = $this->ensureManager()) {
            return $redirect;
        }

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        if (!$tenant) {
            return back()->with('error', 'Data wilayah belum tersedia.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'phone_number' => 'required|string|max:25|unique:users,phone_number',
            'nik' => 'nullable|digits:16|unique:users,nik',
            'no_kk' => 'nullable|digits:16',
            'house_id' => 'nullable|exists:houses,id',
            'role' => 'required|in:warga,ketua_rt,ketua_rw,sekretaris,bendahara,satpam',
            'gender' => 'nullable|in:male,female',
            'occupation' => 'nullable|string|max:100',
            'is_family_head' => 'nullable|boolean',
            'approve_immediately' => 'nullable|boolean',
            'password' => 'nullable|string|min:6',
        ], [
            'email.unique' => 'Email tersebut sudah terdaftar.',
            'phone_number.unique' => 'Nomor WhatsApp/HP tersebut sudah terdaftar.',
            'nik.unique' => 'NIK KTP sudah terdaftar pada warga lain.',
            'nik.digits' => 'NIK harus tepat 16 digit angka.',
            'no_kk.digits' => 'Nomor KK harus tepat 16 digit angka.',
        ]);

        $actor = Auth::user();
        $targetRole = $validated['role'];
        if ($targetRole === 'ketua_rw' && !in_array($actor->role, ['super_admin', 'ketua_rw'])) {
            $targetRole = 'warga';
        }

        $house = !empty($validated['house_id']) ? House::where('tenant_id', $tenant->id)->find($validated['house_id']) : null;
        $noKk = $validated['no_kk'] ?? $house?->no_kk;

        $approveNow = $request->boolean('approve_immediately') && in_array($actor->role, ['super_admin', 'ketua_rw', 'ketua_rt']);
        $verificationStatus = $approveNow ? 'approved' : (!empty($validated['nik']) ? 'pending_rw' : 'unverified');

        $newUser = User::create([
            'tenant_id' => $tenant->id,
            'house_id' => $house?->id,
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'phone_number' => trim($validated['phone_number']),
            'password' => Hash::make(!empty($validated['password']) ? $validated['password'] : ($targetRole === 'warga' ? 'warga123' : 'admin123')),
            'role' => $targetRole,
            'is_family_head' => $request->boolean('is_family_head', false),
            'nik' => $validated['nik'] ?? null,
            'no_kk' => $noKk,
            'gender' => $validated['gender'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'rfid_tag' => !empty($validated['nik']) ? ('RFID-' . $validated['nik']) : null,
            'verification_status' => $verificationStatus,
            'verified_by_user_id' => $approveNow ? $actor->id : null,
            'verified_at' => $approveNow ? now() : null,
            'gate_access_enabled' => $approveNow,
            'status' => 'active',
        ]);

        if ($house && $noKk && empty($house->no_kk)) {
            $house->update(['no_kk' => $noKk]);
        }

        ActivityLogger::log(
            'resident.created',
            "Pengurus ({$actor->name}) menambahkan data warga/akun {$newUser->name} ({$newUser->role_label}).",
            'info',
            ['target_user_id' => $newUser->id, 'role' => $newUser->role, 'no_kk' => $noKk],
            $actor,
            $newUser
        );

        return redirect()->route('residents.index', ['tab' => 'users'])->with('success', "Data warga {$newUser->name} berhasil ditambahkan.");
    }

    /**
     * Perbarui data warga, Kartu Keluarga (No. KK), unit rumah, & manajemen role.
     */
    public function updateResident(Request $request, int $id)
    {
        if ($redirect = $this->ensureManager()) {
            return $redirect;
        }

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $target = User::where('tenant_id', $tenant?->id)->findOrFail($id);
        $actor = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'phone_number' => 'required|string|max:25|unique:users,phone_number,' . $target->id,
            'nik' => 'nullable|digits:16|unique:users,nik,' . $target->id,
            'no_kk' => 'nullable|digits:16',
            'house_id' => 'nullable|exists:houses,id',
            'role' => 'required|in:warga,ketua_rt,ketua_rw,sekretaris,bendahara,satpam,super_admin',
            'status' => 'required|in:active,inactive,pending_approval',
            'is_family_head' => 'nullable|boolean',
        ], [
            'nik.digits' => 'NIK harus tepat 16 digit angka.',
            'no_kk.digits' => 'Nomor KK harus tepat 16 digit angka.',
        ]);

        // Jaga hak istimewa: hanya super_admin yang boleh menetapkan/mengubah role super_admin,
        // dan hanya super_admin / ketua_rw yang boleh menetapkan/mengubah role ketua_rw.
        $newRole = $target->role;
        if ($validated['role'] === 'super_admin' || $target->role === 'super_admin') {
            $newRole = $actor->isSuperAdmin() ? $validated['role'] : $target->role;
        } elseif ($validated['role'] === 'ketua_rw' || $target->role === 'ketua_rw') {
            $newRole = ($actor->isSuperAdmin() || $actor->isKetuaRw()) ? $validated['role'] : $target->role;
        } elseif ($target->id !== $actor->id || $actor->isSuperAdmin() || $actor->isKetuaRw()) {
            $newRole = $validated['role'];
        }

        $house = !empty($validated['house_id']) ? House::where('tenant_id', $tenant?->id)->find($validated['house_id']) : null;
        $noKk = $validated['no_kk'] ?? $house?->no_kk ?? $target->no_kk;

        $target->update([
            'name' => trim($validated['name']),
            'phone_number' => trim($validated['phone_number']),
            'nik' => $validated['nik'] ?? null,
            'no_kk' => $noKk,
            'house_id' => $house?->id,
            'role' => $newRole,
            'status' => $validated['status'],
            'is_family_head' => $request->boolean('is_family_head', false),
        ]);

        if ($house && !empty($validated['no_kk']) && $request->boolean('is_family_head')) {
            $house->update(['no_kk' => $validated['no_kk']]);
        }

        ActivityLogger::log(
            'resident.updated',
            "Pengurus ({$actor->name}) memperbarui data warga/role {$target->name} (Role: {$newRole}, Status: {$validated['status']}).",
            'warning',
            ['target_user_id' => $target->id, 'role' => $newRole, 'no_kk' => $noKk],
            $actor,
            $target
        );

        return redirect()->back()->with('success', "Data warga & role untuk {$target->name} berhasil diperbarui.");
    }

    /**
     * Hapus data warga non-admin dari wilayah.
     */
    public function destroyResident(int $id)
    {
        if ($redirect = $this->ensureManager()) {
            return $redirect;
        }

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $target = User::where('tenant_id', $tenant?->id)->findOrFail($id);
        $actor = Auth::user();

        if ($target->id === $actor->id || $target->isSuperAdmin()) {
            return back()->with('error', 'Akun yang sedang aktif atau Super Admin tidak dapat dihapus.');
        }

        $name = $target->name;
        $target->delete();

        ActivityLogger::log(
            'resident.deleted',
            "Pengurus ({$actor->name}) menghapus data akun warga: {$name}.",
            'warning',
            ['deleted_name' => $name],
            $actor
        );

        return redirect()->back()->with('success', "Data warga {$name} berhasil dihapus.");
    }

    /**
     * Ekspor rekap Data Warga & Kartu Keluarga (KK) ke file CSV.
     */
    public function exportCsv(): StreamedResponse
    {
        if ($redirect = $this->ensureManager()) {
            abort(403, 'Akses ditolak.');
        }

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $filename = 'data-warga-kk-' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($tenant) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Nama Lengkap', 'Peran/Role', 'NIK', 'No. KK', 'Status Keluarga', 'Unit Rumah', 'No. WhatsApp', 'Email', 'UID RFID', 'Status Verifikasi RW']);

            User::where('tenant_id', $tenant?->id)
                ->with('house')
                ->orderBy('name')
                ->chunk(200, function ($rows) use ($handle) {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            $row->name,
                            $row->role_label,
                            $row->nik ?? '-',
                            $row->no_kk ?? ($row->house?->no_kk ?? '-'),
                            $row->is_family_head ? 'Kepala Keluarga' : 'Anggota Keluarga',
                            $row->house?->block_and_number ?? '-',
                            $row->phone_number ?? '-',
                            $row->email,
                            $row->rfid_tag ?? '-',
                            $row->verification_status,
                        ]);
                    }
                });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
