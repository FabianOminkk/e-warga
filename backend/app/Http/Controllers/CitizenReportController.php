<?php

namespace App\Http\Controllers;

use App\Models\CitizenReport;
use App\Models\Tenant;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CitizenReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $tenant = $user->isSuperAdmin()
            ? (Tenant::getActiveTenant() ?? Tenant::first())
            : (Tenant::find($user->tenant_id) ?? Tenant::getActiveTenant() ?? Tenant::first());

        $filterStatus = $request->query('status', 'all');
        $filterType = $request->query('type', 'all');
        $search = trim((string) $request->query('search', ''));

        $query = CitizenReport::where('tenant_id', $tenant?->id)
            ->with(['user', 'house', 'handler'])
            ->latest('created_at');

        if ($filterStatus !== 'all' && in_array($filterStatus, ['pending', 'in_progress', 'resolved', 'rejected'])) {
            $query->where('status', $filterStatus);
        }

        if ($filterType !== 'all' && in_array($filterType, ['pengaduan', 'aspirasi', 'kerusakan_fasilitas', 'kebersihan', 'keamanan'])) {
            $query->where('type', $filterType);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('report_code', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $reports = $query->get();

        $baseQuery = CitizenReport::where('tenant_id', $tenant?->id);
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'resolved' => (clone $baseQuery)->where('status', 'resolved')->count(),
        ];

        return view('pages.reports', compact('tenant', 'user', 'reports', 'stats', 'filterStatus', 'filterType', 'search'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'type' => 'required|in:pengaduan,aspirasi,kerusakan_fasilitas,kebersihan,keamanan',
            'category' => 'required|string|max:80',
            'title' => 'required|string|max:180',
            'description' => 'required|string|max:2000',
            'location' => 'nullable|string|max:180',
            'priority' => 'required|in:low,medium,high,urgent',
            'is_anonymous' => 'nullable|boolean',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'photo.max' => 'Ukuran foto lampiran maksimal 5 MB.',
        ]);

        $tenant = $user->isSuperAdmin()
            ? (Tenant::getActiveTenant() ?? Tenant::first())
            : (Tenant::find($user->tenant_id) ?? Tenant::getActiveTenant() ?? Tenant::first());

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $fileName = 'report_' . $user->id . '_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $photoPath = $file->storeAs('reports', $fileName, 'local');
        }

        $prefix = $validated['type'] === 'aspirasi' ? 'ASP' : 'ADU';
        $reportCode = $prefix . '-' . date('Ym') . '-' . strtoupper(Str::random(4));

        $report = CitizenReport::create([
            'tenant_id' => $tenant?->id ?? 1,
            'user_id' => $user->id,
            'house_id' => $user->house_id,
            'report_code' => $reportCode,
            'type' => $validated['type'],
            'category' => trim($validated['category']),
            'title' => trim($validated['title']),
            'description' => trim($validated['description']),
            'location' => !empty($validated['location']) ? trim($validated['location']) : null,
            'photo_path' => $photoPath,
            'priority' => $validated['priority'],
            'status' => 'pending',
            'is_anonymous' => $request->boolean('is_anonymous', false),
        ]);

        ActivityLogger::log(
            'report.created',
            "Warga {$user->name} mengirim {$report->type_label} ({$report->report_code}): {$report->title}",
            $validated['priority'] === 'urgent' ? 'warning' : 'info',
            ['report_id' => $report->id, 'type' => $report->type, 'priority' => $report->priority],
            $user,
            $report
        );

        return redirect()->route('reports.index')->with('success', "Laporan / aspirasi Anda ({$report->report_code}) berhasil dikirim dan tercatat di antrean pengurus!");
    }

    public function respond(Request $request, int $id)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['super_admin', 'ketua_rw', 'ketua_rt', 'sekretaris', 'bendahara', 'satpam', 'pengurus_rt'])) {
            return redirect()->back()->with('error', 'Akses ditolak: Hanya pengurus atau petugas satpam yang berwenang menindaklanjuti laporan.');
        }

        $report = CitizenReport::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,resolved,rejected',
            'official_response' => 'required|string|max:1000',
        ]);

        $report->update([
            'status' => $validated['status'],
            'official_response' => trim($validated['official_response']),
            'handled_by_user_id' => $user->id,
            'responded_at' => now(),
            'resolved_at' => $validated['status'] === 'resolved' ? now() : $report->resolved_at,
        ]);

        ActivityLogger::log(
            'report.responded',
            "Pengurus ({$user->name}) menindaklanjuti laporan {$report->report_code} menjadi status " . strtoupper($validated['status']) . '.',
            'info',
            ['report_id' => $report->id, 'status' => $validated['status']],
            $user,
            $report
        );

        return redirect()->back()->with('success', "Tanggapan & status laporan {$report->report_code} berhasil diperbarui.");
    }

    public function showPhoto(int $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $report = CitizenReport::findOrFail($id);
        if (!$user->isSuperAdmin() && (int) $user->tenant_id !== (int) $report->tenant_id) {
            abort(403, 'Akses ditolak.');
        }

        if (!$report->photo_path) {
            abort(404);
        }

        if (Storage::disk('local')->exists($report->photo_path)) {
            return response()->file(Storage::disk('local')->path($report->photo_path));
        }

        if (file_exists(public_path($report->photo_path))) {
            return response()->file(public_path($report->photo_path));
        }

        abort(404);
    }
}
