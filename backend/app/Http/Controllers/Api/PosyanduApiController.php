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
            'category'       => 'required|in:balita,anak,ibu_hamil,ibu,lansia',
            'name'           => 'required|string|max:150',
            'nik'            => 'nullable|string|max:20',
            'gender'         => 'required|in:L,P',
            'birth_date'     => 'required|date',
            'parent_name'    => 'nullable|string|max:150',
            'address'        => 'nullable|string',
            'phone'          => 'nullable|string|max:20',
            'blood_type'     => 'nullable|string|max:5',
            'medical_notes'  => 'nullable|string',
        ]);

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();

        $member = PosyanduMember::create([
            'tenant_id'     => $tenant->id,
            'category'      => $validated['category'],
            'name'          => $validated['name'],
            'nik'           => $validated['nik'] ?? null,
            'gender'        => $validated['gender'],
            'birth_date'    => $validated['birth_date'],
            'parent_name'   => $validated['parent_name'] ?? null,
            'address'       => $validated['address'] ?? null,
            'phone'         => $validated['phone'] ?? null,
            'blood_type'    => $validated['blood_type'] ?? null,
            'medical_notes' => $validated['medical_notes'] ?? null,
        ]);

        return $this->success($member, 'Warga sasaran posyandu berhasil didaftarkan.', 201);
    }

    /**
     * Store health checkup / growth measurement with nutrition & stunting analysis.
     */
    public function storeRecord(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'posyandu_member_id'   => 'required|exists:posyandu_members,id',
            'checkup_date'         => 'required|date',
            'weight_kg'            => 'required|numeric|min:1|max:300',
            'height_cm'            => 'nullable|numeric|min:10|max:250',
            'head_circumference_cm'=> 'nullable|numeric',
            'systolic'             => 'nullable|integer',
            'diastolic'            => 'nullable|integer',
            'blood_sugar'          => 'nullable|numeric',
            'gestational_age_weeks'=> 'nullable|integer',
            'vitamin_a'            => 'nullable|boolean',
            'deworming_medicine'   => 'nullable|boolean',
            'immunization_name'    => 'nullable|string|max:100',
            'notes'                => 'nullable|string',
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
                $nutritionStatus = 'kurang_gizi';
                $stuntingAnalysis = "Berat badan relatif rendah untuk usia {$ageMonths} bulan. Disarankan suplementasi protein & PMT.";
            }
        } elseif ($member->category === 'lansia' && !empty($validated['systolic'])) {
            if ($validated['systolic'] >= 140 || ($validated['diastolic'] ?? 0) >= 90) {
                $nutritionStatus = 'hipertensi';
                $stuntingAnalysis = "Tekanan darah tinggi ({$validated['systolic']}/{$validated['diastolic']} mmHg). Disarankan kurangi garam dan istirahat cukup.";
            }
        }

        $record = PosyanduRecord::create([
            'tenant_id'              => $tenant->id,
            'posyandu_member_id'     => $member->id,
            'recorded_by_user_id'    => Auth::id(),
            'checkup_date'           => $validated['checkup_date'],
            'age_in_months'          => $ageMonths,
            'weight_kg'              => $validated['weight_kg'],
            'height_cm'              => $validated['height_cm'] ?? null,
            'head_circumference_cm'  => $validated['head_circumference_cm'] ?? null,
            'systolic'               => $validated['systolic'] ?? null,
            'diastolic'              => $validated['diastolic'] ?? null,
            'blood_sugar'            => $validated['blood_sugar'] ?? null,
            'gestational_age_weeks'  => $validated['gestational_age_weeks'] ?? null,
            'nutrition_status'       => $nutritionStatus,
            'vitamin_a'              => $request->boolean('vitamin_a'),
            'deworming_medicine'     => $request->boolean('deworming_medicine'),
            'immunization_name'      => $validated['immunization_name'] ?? null,
            'stunting_analysis_notes'=> $stuntingAnalysis,
            'notes'                  => $validated['notes'] ?? null,
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

