<?php

namespace App\Http\Controllers;

use App\Models\PosyanduMember;
use App\Models\PosyanduRecord;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PosyanduController extends Controller
{
    public function index(Request $request)
    {
        $tenant = Tenant::first() ?? Tenant::create([
            'name' => 'RT 04 / RW 02 - Graha Asri Residence',
            'slug' => 'graha-asri-rt04',
            'rt_rw' => 'RT 04 / RW 02',
            'province' => 'Jawa Barat',
            'city' => 'Bekasi',
            'district' => 'Cikarang Utara',
            'postal_code' => '17530',
            'subscription_plan' => 'growth',
            'environment_type' => 'cluster',
            'is_active' => true,
        ]);

        $filterCategory = $request->query('category', 'all');

        $membersQuery = PosyanduMember::where('tenant_id', $tenant->id)
            ->with(['latestRecord'])
            ->orderBy('created_at', 'desc');

        if ($filterCategory !== 'all' && in_array($filterCategory, ['balita', 'anak', 'ibu_hamil', 'ibu', 'lansia'])) {
            $membersQuery->where('category', $filterCategory);
        }

        $members = $membersQuery->get();

        // Demographics stats
        $counts = [
            'balita' => PosyanduMember::where('tenant_id', $tenant->id)->where('category', 'balita')->count(),
            'anak' => PosyanduMember::where('tenant_id', $tenant->id)->where('category', 'anak')->count(),
            'ibu_hamil' => PosyanduMember::where('tenant_id', $tenant->id)->where('category', 'ibu_hamil')->count(),
            'ibu' => PosyanduMember::where('tenant_id', $tenant->id)->where('category', 'ibu')->count(),
            'lansia' => PosyanduMember::where('tenant_id', $tenant->id)->where('category', 'lansia')->count(),
            'total' => PosyanduMember::where('tenant_id', $tenant->id)->count(),
        ];

        // Health indicators from latest records
        $latestRecordIds = PosyanduRecord::where('tenant_id', $tenant->id)
            ->selectRaw('MAX(id) as id')
            ->groupBy('posyandu_member_id')
            ->pluck('id');

        $stuntingRiskCount = PosyanduRecord::whereIn('id', $latestRecordIds)
            ->where('nutrition_status', 'risiko_stunting')
            ->count();

        $normalCount = PosyanduRecord::whereIn('id', $latestRecordIds)
            ->whereIn('nutrition_status', ['gizi_baik', 'normal'])
            ->count();

        $highRiskPregnancy = PosyanduRecord::whereIn('id', $latestRecordIds)
            ->where('nutrition_status', 'risiko_tinggi')
            ->count();

        $hypertensionCount = PosyanduRecord::whereIn('id', $latestRecordIds)
            ->where('nutrition_status', 'hipertensi')
            ->count();

        // Recent checkups history
        $recentCheckups = PosyanduRecord::where('tenant_id', $tenant->id)
            ->with('member')
            ->orderBy('checkup_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        return view('pages.posyandu', compact(
            'tenant',
            'members',
            'counts',
            'filterCategory',
            'stuntingRiskCount',
            'normalCount',
            'highRiskPregnancy',
            'hypertensionCount',
            'recentCheckups'
        ));
    }

    public function storeMember(Request $request)
    {
        $tenant = Tenant::first();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:balita,anak,ibu_hamil,ibu,lansia',
            'gender' => 'required|in:L,P',
            'birth_date' => 'required|date',
            'family_card_number' => 'nullable|string|max:20',
            'nik' => 'nullable|string|max:20',
            'parent_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = $tenant->id;

        PosyanduMember::create($validated);

        return back()->with('success', 'Warga ' . $validated['name'] . ' berhasil didaftarkan ke buku Posyandu!');
    }

    public function storeRecord(Request $request)
    {
        $tenant = Tenant::first();

        $validated = $request->validate([
            'posyandu_member_id' => 'required|exists:posyandu_members,id',
            'checkup_date' => 'required|date',
            'weight_kg' => 'nullable|numeric|min:1|max:250',
            'height_cm' => 'nullable|numeric|min:20|max:220',
            'head_circumference_cm' => 'nullable|numeric|min:10|max:80',
            'arm_circumference_cm' => 'nullable|numeric|min:5|max:60',
            'blood_pressure' => 'nullable|string|max:20',
            'blood_sugar' => 'nullable|integer',
            'cholesterol' => 'nullable|integer',
            'uric_acid' => 'nullable|numeric',
            'gestational_age_weeks' => 'nullable|string|max:30',
            'nutrition_status' => 'nullable|string',
            'vitamins_or_vaccines' => 'nullable|string|max:255',
            'examiner_name' => 'nullable|string|max:100',
            'action_plan' => 'nullable|string',
        ]);

        $member = PosyanduMember::findOrFail($validated['posyandu_member_id']);

        // Auto-detection rules if nutrition_status not explicitly provided or default
        if (empty($validated['nutrition_status'])) {
            if ($member->category === 'balita') {
                $ageInMonths = Carbon::now()->diffInMonths($member->birth_date);
                $weight = $validated['weight_kg'] ?? 0;
                $height = $validated['height_cm'] ?? 0;

                // Threshold stunting sederhana: berat/tinggi di bawah standar WHO rata-rata
                if ($ageInMonths >= 12 && $height > 0 && $height < 70) {
                    $validated['nutrition_status'] = 'risiko_stunting';
                } elseif ($weight > 0 && $weight < 7.5 && $ageInMonths >= 12) {
                    $validated['nutrition_status'] = 'gizi_kurang';
                } else {
                    $validated['nutrition_status'] = 'gizi_baik';
                }
            } elseif ($member->category === 'ibu_hamil') {
                $lila = $validated['arm_circumference_cm'] ?? 0;
                // LiLA < 23.5 cm menunjukkan Kurang Energi Kronis (KEK) / Risiko Tinggi
                if ($lila > 0 && $lila < 23.5) {
                    $validated['nutrition_status'] = 'risiko_tinggi';
                } else {
                    $validated['nutrition_status'] = 'normal';
                }
            } elseif ($member->category === 'lansia') {
                $bp = $validated['blood_pressure'] ?? '';
                if (preg_match('/(\d+)\/(\d+)/', $bp, $matches)) {
                    $systolic = (int)$matches[1];
                    if ($systolic >= 140) {
                        $validated['nutrition_status'] = 'hipertensi';
                    } else {
                        $validated['nutrition_status'] = 'normal';
                    }
                } else {
                    $validated['nutrition_status'] = 'normal';
                }
            } else {
                $validated['nutrition_status'] = 'normal';
            }
        }

        $validated['tenant_id'] = $tenant->id;
        $validated['examiner_name'] = $validated['examiner_name'] ?? 'Bidan Desa / Kader Posyandu';

        PosyanduRecord::create($validated);

        return back()->with('success', 'Data pemeriksaan posyandu untuk ' . $member->name . ' berhasil dicatat!');
    }

    public function destroyMember($id)
    {
        $member = PosyanduMember::findOrFail($id);
        $name = $member->name;
        $member->delete();

        return back()->with('success', "Data warga posyandu {$name} berhasil dihapus.");
    }
}
