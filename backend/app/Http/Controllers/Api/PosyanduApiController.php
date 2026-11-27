<?php

namespace App\Http\Controllers\Api;

use App\Models\PosyanduMember;
use App\Models\PosyanduRecord;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosyanduApiController extends BaseApiController
{
    /**
     * Retrieve list of registered posyandu members and demographic counts.
     */
    public function index(Request $request): JsonResponse
    {
        $tenant = Tenant::getActiveTenant() ?? Tenant::first();

        $filterCategory = $request->query('category', 'all');

        $membersQuery = PosyanduMember::where('tenant_id', $tenant->id)
            ->with(['latestRecord', 'records'])
            ->orderByDesc('created_at');

        if ($filterCategory !== 'all' && in_array($filterCategory, ['balita', 'anak', 'ibu_hamil', 'ibu', 'lansia'])) {
            $membersQuery->where('category', $filterCategory);
        }

        $members = $membersQuery->get();

        $counts = [
            'balita'    => PosyanduMember::where('tenant_id', $tenant->id)->where('category', 'balita')->count(),
            'anak'      => PosyanduMember::where('tenant_id', $tenant->id)->where('category', 'anak')->count(),
            'ibu_hamil' => PosyanduMember::where('tenant_id', $tenant->id)->where('category', 'ibu_hamil')->count(),
            'ibu'       => PosyanduMember::where('tenant_id', $tenant->id)->where('category', 'ibu')->count(),
            'lansia'    => PosyanduMember::where('tenant_id', $tenant->id)->where('category', 'lansia')->count(),
            'total'     => PosyanduMember::where('tenant_id', $tenant->id)->count(),
        ];

        $latestRecordIds = PosyanduRecord::where('tenant_id', $tenant->id)
            ->selectRaw('MAX(id) as id')
            ->groupBy('posyandu_member_id')
            ->pluck('id');

        $stuntingRiskCount = PosyanduRecord::whereIn('id', $latestRecordIds)
            ->where('nutrition_status', 'risiko_stunting')
            ->count();

        return $this->success([
            'counts'              => $counts,
            'stunting_risk_count' => $stuntingRiskCount,
            'members'             => $members,
        ], 'Data posyandu digital berhasil diambil.');
    }

    /**
     * Register a new member into the Posyandu digital system.
     */
    public function storeMember(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category'           => 'required|in:balita,anak,ibu_hamil,ibu,lansia',
            'name'               => 'required|string|max:150',
            'family_card_number' => 'nullable|string|max:20',
            'nik'                => 'nullable|string|max:20',
            'gender'             => 'required|in:L,P',
            'birth_date'         => 'required|date',
            'parent_name'        => 'nullable|string|max:150',
            'address'            => 'nullable|string',
            'phone'              => 'nullable|string|max:20',
            'notes'              => 'nullable|string',
            'medical_notes'      => 'nullable|string',
        ]);

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();

        $member = PosyanduMember::create([
            'tenant_id'          => $tenant?->id ?? 1,
            'category'           => $validated['category'],
            'name'               => $validated['name'],
            'family_card_number' => $validated['family_card_number'] ?? null,
            'nik'                => $validated['nik'] ?? null,
            'gender'             => $validated['gender'],
            'birth_date'         => $validated['birth_date'],
            'parent_name'        => $validated['parent_name'] ?? null,
            'address'            => $validated['address'] ?? null,
            'phone'              => $validated['phone'] ?? null,
            'notes'              => $validated['notes'] ?? $validated['medical_notes'] ?? null,
        ]);

        return $this->success($member, 'Warga sasaran posyandu berhasil didaftarkan.', 201);
    }

    /**
     * Store health checkup / growth measurement with nutrition & stunting analysis.
     */
    public function storeRecord(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'posyandu_member_id'    => 'required|exists:posyandu_members,id',
            'checkup_date'          => 'required|date',
            'weight_kg'             => 'required|numeric|min:1|max:300',
            'height_cm'             => 'nullable|numeric|min:10|max:250',
            'head_circumference_cm' => 'nullable|numeric',
            'arm_circumference_cm'  => 'nullable|numeric',
            'systolic'              => 'nullable|integer',
            'diastolic'             => 'nullable|integer',
            'blood_pressure'        => 'nullable|string|max:20',
            'blood_sugar'           => 'nullable|numeric',
            'cholesterol'           => 'nullable|integer',
            'uric_acid'             => 'nullable|numeric',
            'gestational_age_weeks' => 'nullable|string|max:30',
            'vitamin_a'             => 'nullable|boolean',
            'deworming_medicine'    => 'nullable|boolean',
            'immunization_name'     => 'nullable|string|max:100',
            'vitamins_or_vaccines'  => 'nullable|string|max:255',
            'examiner_name'         => 'nullable|string|max:100',
            'action_plan'           => 'nullable|string',
            'notes'                 => 'nullable|string',
        ]);

        $member = PosyanduMember::findOrFail($validated['posyandu_member_id']);
        $tenant = Tenant::getActiveTenant() ?? Tenant::first();

        // Calculate age in months
        $birthDate = Carbon::parse($member->birth_date);
        $checkupDate = Carbon::parse($validated['checkup_date']);
        $ageMonths = $birthDate->diffInMonths($checkupDate);

        // Analyze Nutrition Status & Stunting Risk for Balita / Anak
        $nutritionStatus = 'normal';
        $stuntingAnalysis = 'Pertumbuhan Terpantau Baik';

        if (in_array($member->category, ['balita', 'anak']) && !empty($validated['height_cm'])) {
            $height = (float) $validated['height_cm'];
            $weight = (float) $validated['weight_kg'];

            $expectedHeight = 50 + ($ageMonths * 1.5);
            $lowerThreshold = $expectedHeight * 0.88;

            if ($height < $lowerThreshold) {
                $nutritionStatus = 'risiko_stunting';
                $stuntingAnalysis = "Tinggi badan ({$height} cm) berada di bawah ambang batas kurva WHO untuk usia {$ageMonths} bulan. Perlu pemantauan gizi intensif.";
            } elseif ($weight < ($ageMonths * 0.4 + 3.0)) {
                $nutritionStatus = 'gizi_kurang';
                $stuntingAnalysis = "Berat badan relatif rendah untuk usia {$ageMonths} bulan. Disarankan suplementasi protein & PMT.";
            }
        } elseif ($member->category === 'lansia' && !empty($validated['systolic'])) {
            if ($validated['systolic'] >= 140 || ($validated['diastolic'] ?? 0) >= 90) {
                $nutritionStatus = 'hipertensi';
                $stuntingAnalysis = "Tekanan darah tinggi ({$validated['systolic']}/{$validated['diastolic']} mmHg). Disarankan kurangi garam dan istirahat cukup.";
            }
        }

        $bloodPressure = null;
        if (!empty($validated['blood_pressure'])) {
            $bloodPressure = $validated['blood_pressure'];
        } elseif (!empty($validated['systolic'])) {
            $bloodPressure = $validated['systolic'] . '/' . ($validated['diastolic'] ?? 80);
        }

        $vitamins = [];
        if (!empty($validated['vitamins_or_vaccines'])) {
            $vitamins[] = $validated['vitamins_or_vaccines'];
        }
        if ($request->boolean('vitamin_a')) {
            $vitamins[] = 'Vitamin A';
        }
        if ($request->boolean('deworming_medicine')) {
            $vitamins[] = 'Obat Cacing';
        }
        if (!empty($validated['immunization_name'])) {
            $vitamins[] = 'Imunisasi: ' . $validated['immunization_name'];
        }
        $vitaminsText = !empty($vitamins) ? implode(', ', $vitamins) : null;

        $actionPlan = $validated['action_plan'] ?? null;
        if (empty($actionPlan)) {
            $planParts = [];
            if (!empty($stuntingAnalysis)) {
                $planParts[] = $stuntingAnalysis;
            }
            if (!empty($validated['notes'])) {
                $planParts[] = 'Catatan: ' . $validated['notes'];
            }
            $actionPlan = !empty($planParts) ? implode(' | ', $planParts) : null;
        }

        $record = PosyanduRecord::create([
            'tenant_id'              => $tenant?->id ?? 1,
            'posyandu_member_id'     => $member->id,
            'checkup_date'           => $validated['checkup_date'],
            'weight_kg'              => $validated['weight_kg'],
            'height_cm'              => $validated['height_cm'] ?? null,
            'head_circumference_cm'  => $validated['head_circumference_cm'] ?? null,
            'arm_circumference_cm'   => $validated['arm_circumference_cm'] ?? null,
            'blood_pressure'         => $bloodPressure,
            'blood_sugar'            => $validated['blood_sugar'] ?? null,
            'cholesterol'            => $validated['cholesterol'] ?? null,
            'uric_acid'              => $validated['uric_acid'] ?? null,
            'gestational_age_weeks'  => $validated['gestational_age_weeks'] ?? null,
            'nutrition_status'       => $nutritionStatus,
            'vitamins_or_vaccines'   => $vitaminsText,
            'examiner_name'          => $validated['examiner_name'] ?? 'Bidan Desa / Kader Posyandu',
            'action_plan'            => $actionPlan,
        ]);

        return $this->success($record, 'Catatan pemeriksaan kesehatan posyandu berhasil disimpan.', 201);
    }

    /**
     * Remove a member from the Posyandu digital register.
     */
    public function destroyMember(Request $request, int $id): JsonResponse
    {
        $member = PosyanduMember::findOrFail($id);
        $name = $member->name;
        $member->delete();

        return $this->success(null, "Data sasaran posyandu atas nama {$name} telah dihapus.");
    }
}

