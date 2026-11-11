<?php

namespace App\Http\Controllers;

use App\Models\CashBook;
use App\Models\House;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminController extends Controller
{
    public function index()
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

        return view('pages.superadmin', compact(
            'tenants',
            'totalTenants',
            'totalHouses',
            'totalUsers',
            'totalGates',
            'aggregateCash',
            'clusterTenant',
            'desaTenant',
            'activeTenantId'
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
}

