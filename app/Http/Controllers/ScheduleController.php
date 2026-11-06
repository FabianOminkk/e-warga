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
        $month = $request->input('month', now()->format('Y-m'));

        $query = SecuritySchedule::where('tenant_id', $tenant?->id ?? 1)
            ->whereYear('duty_date', Carbon::parse($month)->year)
            ->whereMonth('duty_date', Carbon::parse($month)->month)
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

        $satpamUsers = User::where('role', 'satpam')->get();
        $wargaUsers = User::where('role', 'warga')->with('house')->get();

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
            'tenant_id' => $tenant->id,
            'user_id' => $validated['user_id'],
            'schedule_type' => $validated['schedule_type'],
            'duty_date' => $validated['duty_date'],
            'shift_name' => $validated['shift_name'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => 'scheduled',
            'notes' => $validated['notes'],
        ]);

        return redirect()->back()->with('success', 'Jadwal petugas/ronda baru berhasil ditambahkan!');
    }

    public function checkIn($id)
    {
        $schedule = SecuritySchedule::findOrFail($id);
        $schedule->update([
            'status' => 'present',
            'checked_in_at' => now(),
        ]);

        return redirect()->back()->with('success', "Absensi siaga berhasil dicatat untuk: {$schedule->officer->name}.");
    }

    public function requestSwap(Request $request, $id)
    {
        $validated = $request->validate([
            'replacement_user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $schedule = SecuritySchedule::findOrFail($id);
        $schedule->update([
            'status' => 'swapped',
            'replacement_user_id' => $validated['replacement_user_id'],
            'notes' => 'Tukar dinas dengan ' . User::find($validated['replacement_user_id'])->name . '. ' . ($validated['notes'] ?? ''),
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

