<?php

namespace App\Http\Controllers\Api;

use App\Models\CitizenReport;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReportApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user() ?? Auth::user();
        $tenant = $user && !$user->isSuperAdmin()
            ? (Tenant::find($user->tenant_id) ?? Tenant::getActiveTenant() ?? Tenant::first())
            : (Tenant::getActiveTenant() ?? Tenant::first());

        $query = CitizenReport::where('tenant_id', $tenant?->id)
            ->with(['user', 'house', 'handler'])
            ->latest('created_at');

        if ($request->filled('status') && in_array($request->status, ['pending', 'in_progress', 'resolved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        $reports = $query->get();

        return $this->success([
            'reports' => $reports,
            'stats' => [
                'total' => CitizenReport::where('tenant_id', $tenant?->id)->count(),
                'pending' => CitizenReport::where('tenant_id', $tenant?->id)->where('status', 'pending')->count(),
                'in_progress' => CitizenReport::where('tenant_id', $tenant?->id)->where('status', 'in_progress')->count(),
                'resolved' => CitizenReport::where('tenant_id', $tenant?->id)->where('status', 'resolved')->count(),
            ],
        ], 'Daftar laporan & aspirasi warga berhasil diambil.');
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user() ?? Auth::user();
        if (!$user) {
            return $this->error('Unauthenticated.', null, 401);
        }

        $validated = $request->validate([
            'type' => 'nullable|in:pengaduan,aspirasi,kerusakan_fasilitas,kebersihan,keamanan',
            'category' => 'nullable|string|max:80',
            'title' => 'required|string|max:180',
            'description' => 'required|string|max:2000',
            'location' => 'nullable|string|max:180',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $tenant = Tenant::find($user->tenant_id) ?? Tenant::getActiveTenant() ?? Tenant::first();
        $type = $validated['type'] ?? 'pengaduan';
        $prefix = $type === 'aspirasi' ? 'ASP' : 'ADU';

        $report = CitizenReport::create([
            'tenant_id' => $tenant?->id ?? 1,
            'user_id' => $user->id,
            'house_id' => $user->house_id,
            'report_code' => $prefix . '-' . date('Ym') . '-' . strtoupper(Str::random(4)),
            'type' => $type,
            'category' => $validated['category'] ?? 'Umum',
            'title' => trim($validated['title']),
            'description' => trim($validated['description']),
            'location' => $validated['location'] ?? null,
            'priority' => $validated['priority'] ?? 'medium',
            'status' => 'pending',
            'is_anonymous' => $request->boolean('is_anonymous', false),
        ]);

        return $this->success($report, 'Laporan / aspirasi warga berhasil dikirim.', 201);
    }

    public function respond(Request $request, int $id): JsonResponse
    {
        $user = $request->user() ?? Auth::user();
        if (!$user || !in_array($user->role, ['super_admin', 'ketua_rw', 'ketua_rt', 'sekretaris', 'bendahara', 'satpam', 'pengurus_rt'])) {
            return $this->error('Akses ditolak: Hanya pengurus yang berwenang menindaklanjuti laporan.', null, 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,resolved,rejected',
            'official_response' => 'required|string|max:1000',
        ]);

        $report = CitizenReport::findOrFail($id);
        $report->update([
            'status' => $validated['status'],
            'official_response' => trim($validated['official_response']),
            'handled_by_user_id' => $user->id,
            'responded_at' => now(),
            'resolved_at' => $validated['status'] === 'resolved' ? now() : $report->resolved_at,
        ]);

        return $this->success($report, 'Tanggapan laporan berhasil diperbarui.');
    }
}
