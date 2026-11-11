<?php

namespace App\Http\Controllers\Api;

use App\Models\SecuritySchedule;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleApiController extends BaseApiController
{
    /**
     * Retrieve monthly security shifts and night watch schedules.
     */
    public function index(Request $request): JsonResponse
    {
        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $type = $request->input('type'); // 'satpam_shift' or 'ronda_warga'
        $month = $request->input('month', now()->format('Y-m'));

        $query = SecuritySchedule::where('tenant_id', $tenant?->id ?? 1)
            ->whereYear('duty_date', Carbon::parse($month)->year)
            ->whereMonth('duty_date', Carbon::parse($month)->month)
            ->with(['officer.house', 'replacement']);

        if ($type) {
            $query->where('schedule_type', $type);
        }

        $schedules = $query->orderBy('duty_date')->orderBy('start_time')->get();

        $todaySchedules = SecuritySchedule::where('tenant_id', $tenant?->id ?? 1)
            ->whereDate('duty_date', Carbon::today())
            ->with(['officer.house'])
            ->get();

        return $this->success([
            'environment_type' => $tenant?->environment_type ?? 'cluster',
            'month'            => $month,
            'today_duties'     => $todaySchedules,
            'schedules'        => $schedules,
        ], 'Data jadwal keamanan dan siskamling berhasil diambil.');
    }

    /**
     * Create a new security shift or night watch schedule.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id'       => 'required|exists:users,id',
            'schedule_type' => 'required|in:satpam_shift,ronda_warga',
            'duty_date'     => 'required|date',
            'shift_name'    => 'required|string|max:100',
            'start_time'    => 'required',
            'end_time'      => 'required',
            'notes'         => 'nullable|string',
        ]);

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();

        $schedule = SecuritySchedule::create([
            'tenant_id'     => $tenant?->id ?? 1,
            'user_id'       => $validated['user_id'],
            'schedule_type' => $validated['schedule_type'],
            'duty_date'     => $validated['duty_date'],
            'shift_name'    => $validated['shift_name'],
            'start_time'    => $validated['start_time'],
            'end_time'      => $validated['end_time'],
            'notes'         => $validated['notes'] ?? null,
            'status'        => 'scheduled',
        ]);

        return $this->success($schedule, 'Jadwal dinas keamanan berhasil ditambahkan.', 201);
    }

    /**
     * Check-in attendance for on-duty shift.
     */
    public function checkIn(Request $request, int $id): JsonResponse
    {
        $schedule = SecuritySchedule::with('officer')->findOrFail($id);

        $schedule->update([
            'status'        => 'present',
            'checked_in_at' => now(),
        ]);

        return $this->success($schedule, 'Presensi kehadiran check-in berhasil dicatat.');
    }

    /**
     * Request shift swap with another officer.
     */
    public function requestSwap(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'replacement_user_id' => 'required|exists:users,id',
            'swap_reason'         => 'required|string|max:200',
        ]);

        $schedule = SecuritySchedule::with(['officer', 'replacement'])->findOrFail($id);
        $replacementUser = User::find($validated['replacement_user_id']);

        $schedule->update([
            'replacement_user_id' => $validated['replacement_user_id'],
            'status'              => 'swapped',
            'notes'               => ($schedule->notes ? $schedule->notes . ' | ' : '') .
                "Ditukar ke: " . ($replacementUser?->name ?? 'Petugas Pengganti') .
                " (Alasan: {$validated['swap_reason']})",
        ]);

        return $this->success($schedule, 'Permohonan tukar jadwal dinas berhasil diproses.');
    }

    /**
     * Toggle environment mode between 'cluster' and 'desa'.
     */
    public function toggleMode(Request $request): JsonResponse
    {
        $tenant = Tenant::getActiveTenant() ?? Tenant::first();

        if (!$tenant) {
            return $this->error('Tenant tidak ditemukan.', null, 404);
        }

        $newMode = ($tenant->environment_type === 'desa') ? 'cluster' : 'desa';
        $tenant->update(['environment_type' => $newMode]);

        return $this->success([
            'environment_type' => $newMode,
            'tenant'           => $tenant,
        ], "Mode lingkungan berhasil diubah menjadi: " . strtoupper($newMode));
    }
}
