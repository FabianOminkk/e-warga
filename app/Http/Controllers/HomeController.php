<?php

namespace App\Http\Controllers;

use App\Models\CashBook;
use App\Models\GateLog;
use App\Models\House;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index()
    {
        $tenant = Tenant::with(['smartGates'])->first();
        $totalHouses = House::count();
        $totalUsers = User::count();
        $totalGateLogs = GateLog::count();
        $totalCash = CashBook::where('type', 'income')->sum('amount') - CashBook::where('type', 'expense')->sum('amount');

        return view('pages.landing', compact('tenant', 'totalHouses', 'totalUsers', 'totalGateLogs', 'totalCash'));
    }

    public function registerTenant(Request $request)
    {
        $validated = $request->validate([
            'complex_name' => 'required|string|max:150',
            'rt_rw' => 'required|string|max:50',
            'city' => 'required|string|max:100',
            'contact_name' => 'required|string|max:150',
            'contact_phone' => 'required|string|max:20',
            'estimated_houses' => 'nullable|integer',
            'need_barrier_gate' => 'nullable|boolean',
        ]);

        Tenant::create([
            'name' => $validated['complex_name'],
            'slug' => Str::slug($validated['complex_name'] . '-' . rand(100, 999)),
            'rt_rw' => $validated['rt_rw'],
            'city' => $validated['city'],
            'subscription_plan' => 'starter',
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Pendaftaran komplek berhasil dikirim! Tim teknisi E-WARGA akan segera menghubungi Anda via WhatsApp.');
    }

    public function quickLogin($role)
    {
        $user = User::where('role', $role)->first();
        if ($user) {
            Auth::login($user);
            if ($user->role === 'satpam') {
                return redirect()->route('satpam.console');
            } elseif ($user->role === 'warga') {
                return redirect()->route('warga.index');
            }
            return redirect()->route('dashboard');
        }

        return redirect()->route('home')->with('error', 'Akun role tidak ditemukan.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda telah keluar dari sistem.');
    }
}

