<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CashBook;
use App\Models\House;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SuperAdminController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'super_admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak: Pusat Kendali Super Admin hanya dapat diakses oleh Super Admin Platform.');
        }

        $tenants = Tenant::withCount(['houses', 'users', 'smartGates'])->get();
        $totalTenants = $tenants->count();
        $totalHouses = House::count();
        $totalUsers = User::count();
        $totalGates = SmartGate::count();

        $totalIncome = CashBook::where('type', 'income')->sum('amount');
        $totalExpense = CashBook::where('type', 'expense')->sum('amount');
        $aggregateCash = $totalIncome - $totalExpense;

        $clusterTenant = Tenant::where('environment_type', 'cluster')->first();
        $desaTenant = Tenant::where('environment_type', 'kampung')->first();

        $activeTenantId = session('active_tenant_id', $clusterTenant?->id ?? Tenant::first()?->id);

        // Filter & Paginasi Audit Trail / Activity Log
        $filters = $request->only(['search', 'severity', 'tenant_id', 'action']);
        $logsQuery = ActivityLog::with(['user', 'tenant'])
            ->filter($filters)
            ->latest('created_at');

        $activityLogs = $logsQuery->paginate(15)->withQueryString();

        // Statistik Audit Keamanan
        $auditStats = [
            'total_logs' => ActivityLog::count(),
            'total_danger' => ActivityLog::where('severity', 'danger')->count(),
            'total_warning' => ActivityLog::where('severity', 'warning')->count(),
            'logins_today' => ActivityLog::where('action', 'auth.login')->whereDate('created_at', today())->count(),
        ];

        // Daftar unik aksi untuk dropdown filter
        $availableActions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('pages.superadmin', compact(
            'tenants',
            'totalTenants',
            'totalHouses',
            'totalUsers',
            'totalGates',
            'aggregateCash',
            'clusterTenant',
            'desaTenant',
            'activeTenantId',
            'activityLogs',
            'auditStats',
            'availableActions',
            'filters'
        ));
    }

    public function switchTenant(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'super_admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak: Hanya Super Admin yang dapat beralih lingkungan.');
        }

        $tenant = Tenant::findOrFail($id);
        session(['active_tenant_id' => $tenant->id]);

        if ($request->boolean('redirect_to_dashboard') || $request->input('open_dashboard')) {
            return redirect()->route('dashboard')->with('success', "Pusat Kendali dialihkan ke: {$tenant->name}. Selamat datang di Dashboard!");
        }

        return redirect()->back()->with('success', "Pusat Kendali beralih ke lingkungan: {$tenant->name} (" . strtoupper($tenant->environment_type) . ")");
    }

    /**
     * Ekspor riwayat audit log ke file CSV untuk kebutuhan investigasi atau laporan fisik RT/RW.
     */
    public function exportLogs(Request $request): StreamedResponse
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'super_admin') {
            abort(403, 'Akses ditolak: Hanya Super Admin yang dapat mengekspor berkas audit log.');
        }

        $filters = $request->only(['search', 'severity', 'tenant_id', 'action']);
        $filename = 'e-warga-activity-logs-' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($filters) {
            $handle = fopen('php://output', 'w');
            
            // Tulis BOM UTF-8 agar kompatibel dengan Microsoft Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header kolom
            fputcsv($handle, [
                'ID',
                'Waktu (WIB)',
                'Wilayah/Tenant',
                'Nama Aktor',
                'Role Akun',
                'Kode Aksi',
                'Tingkat Urgensi',
                'Deskripsi Ringkas',
                'IP Address',
                'User Agent',
            ]);

            ActivityLog::with(['user', 'tenant'])
                ->filter($filters)
                ->latest('created_at')
                ->chunk(200, function ($logs) use ($handle) {
                    foreach ($logs as $log) {
                        fputcsv($handle, [
                            $log->id,
                            $log->created_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') ?? '-',
                            $log->tenant?->name ?? 'Platform Global',
                            $log->actor_name ?? 'Tamu / Sistem',
                            strtoupper($log->actor_role ?? 'guest'),
                            $log->action,
                            strtoupper($log->severity),
                            $log->description,
                            $log->ip_address ?? '-',
                            $log->user_agent ?? '-',
                        ]);
                    }
                });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
