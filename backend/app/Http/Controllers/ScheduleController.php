<?php

namespace App\Http\Controllers;

use App\Models\SecuritySchedule;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $type = $request->input('type'); // 'satpam_shift' or 'ronda_warga' or all
        $rawMonth = trim((string) $request->input('month', ''));
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $rawMonth) === 1) {
            $month = $rawMonth;
            $parsedMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } else {
            $parsedMonth = now()->startOfMonth();
            $month = $parsedMonth->format('Y-m');
        }

        $query = SecuritySchedule::where('tenant_id', $tenant?->id ?? 1)
            ->whereYear('duty_date', $parsedMonth->year)
            ->whereMonth('duty_date', $parsedMonth->month)
            ->with(['officer.house', 'replacement']);

        if ($type) {
            $query->where('schedule_type', $type);
        }

        $schedules = $query->orderBy('duty_date')->orderBy('start_time')->get();

        // Siapa yang bertugas hari ini?
        $todaySchedules = SecuritySchedule::where('tenant_id', $tenant?->id ?? 1)
            ->whereDate('duty_date', Carbon::today())
            ->with(['officer.house'])
            ->get();

        $satpamUsers = User::where('tenant_id', $tenant?->id ?? 1)->where('role', 'satpam')->get();
        if ($satpamUsers->isEmpty()) {
            $satpamUsers = User::where('role', 'satpam')->get();
        }
        $wargaUsers = User::where('tenant_id', $tenant?->id ?? 1)->where('role', 'warga')->with('house')->get();
        if ($wargaUsers->isEmpty()) {
            $wargaUsers = User::where('role', 'warga')->with('house')->get();
        }

        return view('pages.schedules', compact('tenant', 'schedules', 'todaySchedules', 'type', 'month', 'satpamUsers', 'wargaUsers'));
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser || !in_array($currentUser->role, ['super_admin', 'ketua_rw', 'ketua_rt', 'satpam'])) {
            return redirect()->back()->with('error', 'Akses ditolak: Hanya pengurus RT/RW, Satpam, atau Super Admin yang dapat menambahkan jadwal dinas.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'schedule_type' => 'required|in:satpam_shift,ronda_warga',
            'duty_date' => 'required|date',
            'shift_name' => 'required|string|max:100',
            'start_time' => 'required',
            'end_time' => 'required',
            'notes' => 'nullable|string',
        ]);

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();

        SecuritySchedule::create([
            'tenant_id' => $tenant?->id ?? 1,
            'user_id' => $validated['user_id'],
            'schedule_type' => $validated['schedule_type'],
            'duty_date' => $validated['duty_date'],
            'shift_name' => $validated['shift_name'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => 'scheduled',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Jadwal petugas/ronda baru berhasil ditambahkan!');
    }

    public function checkIn($id)
    {
        $currentUser = Auth::user();
        $schedule = SecuritySchedule::findOrFail($id);

        if ($currentUser && !$currentUser->isSuperAdmin() && (int) $currentUser->tenant_id !== (int) $schedule->tenant_id) {
            return redirect()->back()->with('error', 'Akses ditolak: Jadwal bukan milik wilayah Anda.');
        }

        if ($currentUser && !in_array($currentUser->role, ['super_admin', 'ketua_rw', 'ketua_rt', 'sekretaris', 'satpam', 'pengurus_rt']) && (int) $currentUser->id !== (int) $schedule->user_id) {
            return redirect()->back()->with('error', 'Akses ditolak: Anda hanya dapat melakukan absensi untuk jadwal dinas Anda sendiri.');
        }

        $schedule->update([
            'status' => 'present',
            'checked_in_at' => now(),
        ]);

        $officerName = $schedule->officer ? $schedule->officer->name : 'Petugas';
        return redirect()->back()->with('success', "Absensi siaga berhasil dicatat untuk: {$officerName}.");
    }

    public function requestSwap(Request $request, $id)
    {
        $currentUser = Auth::user();
        $schedule = SecuritySchedule::findOrFail($id);

        if ($currentUser && !$currentUser->isSuperAdmin() && (int) $currentUser->tenant_id !== (int) $schedule->tenant_id) {
            return redirect()->back()->with('error', 'Akses ditolak: Jadwal bukan milik wilayah Anda.');
        }

        if ($currentUser && !in_array($currentUser->role, ['super_admin', 'ketua_rw', 'ketua_rt', 'sekretaris', 'satpam', 'pengurus_rt']) && (int) $currentUser->id !== (int) $schedule->user_id) {
            return redirect()->back()->with('error', 'Akses ditolak: Anda hanya dapat mengajukan tukar jadwal untuk tugas Anda sendiri.');
        }

        $validated = $request->validate([
            'replacement_user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $replacementUser = User::find($validated['replacement_user_id']);
        $replacementName = $replacementUser ? $replacementUser->name : 'Petugas Pengganti';

        $schedule->update([
            'status' => 'swapped',
            'replacement_user_id' => $validated['replacement_user_id'],
            'notes' => 'Tukar dinas dengan ' . $replacementName . '. ' . ($validated['notes'] ?? ''),
        ]);

        return redirect()->back()->with('success', 'Permintaan tukar jadwal berhasil diproses.');
    }

    public function toggleEnvironmentMode(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Akses ditolak: Hanya Super Admin Platform yang berwenang mengganti mode lingkungan wilayah.');
        }

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        if ($tenant) {
            $newMode = $tenant->environment_type === 'cluster' ? 'kampung' : 'cluster';
            $tenant->update(['environment_type' => $newMode]);
            $label = $newMode === 'cluster' ? 'Mode Perumahan / Cluster' : 'Mode Perkampungan / Desa';
            return redirect()->back()->with('success', "Sistem lingkungan berhasil dialihkan ke: {$label}.");
        }

        return redirect()->back()->with('error', 'Komplek tidak ditemukan.');
    }
}

