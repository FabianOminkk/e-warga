<?php

namespace App\Http\Controllers;

use App\Models\CashBook;
use App\Models\GateLog;
use App\Models\House;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

    public function showLogin()
    {
        $tenant = Tenant::first();
        $totalHouses = House::count();
        $totalUsers = User::count();
        $totalCash = CashBook::where('type', 'income')->sum('amount') - CashBook::where('type', 'expense')->sum('amount');

        return view('pages.login', compact('tenant', 'totalHouses', 'totalUsers', 'totalCash'));
    }

    public function processLogin(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['login'])
            ->orWhere('phone_number', $credentials['login'])
            ->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            Auth::login($user, $request->boolean('remember'));
            if ($user->role === 'satpam') {
                return redirect()->route('satpam.console')->with('success', "Selamat bertugas, {$user->name}!");
            } elseif ($user->role === 'warga') {
                return redirect()->route('warga.index')->with('success', "Selamat datang, {$user->name}!");
            }
            return redirect()->route('dashboard')->with('success', "Selamat datang di Dashboard E-WARGA, {$user->name}!");
        }

        return redirect()->back()->withInput()->with('error', 'Email/No. WhatsApp atau password yang Anda masukkan salah. (Password demo: admin123)');
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

        $baseSlug = Str::slug($validated['complex_name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $tenant = Tenant::create([
            'name' => $validated['complex_name'],
            'slug' => $slug,
            'rt_rw' => $validated['rt_rw'],
            'city' => $validated['city'],
            'subscription_plan' => 'starter',
            'is_active' => true,
        ]);

        if (!empty($validated['need_barrier_gate'])) {
            SmartGate::create([
                'tenant_id' => $tenant->id,
                'name' => 'Gerbang Utama - ' . $tenant->name,
                'device_uid' => 'ESP32-' . strtoupper(Str::random(8)),
                'status' => 'online',
                'last_ping_at' => now(),
            ]);
        }

        // Buat akun Ketua RT untuk pendaftar
        $adminEmail = Str::slug($validated['contact_name']) . '@' . $slug . '.ewarga.id';
        User::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['contact_name'],
            'email' => $adminEmail,
            'phone_number' => $validated['contact_phone'],
            'password' => Hash::make('admin123'),
            'role' => 'ketua_rt',
            'is_family_head' => true,
            'status' => 'active',
        ]);

        return redirect()->route('login')->with('success', "Pendaftaran komplek '{$tenant->name}' berhasil dikirim! Akun Ketua RT telah siap (No HP: {$validated['contact_phone']}, Password: admin123). Tim teknisi kami akan segera menghubungi Anda untuk survei palang pintu.");
    }

    public function quickLogin($role)
    {
        $user = null;

        if ($role === 'warga-baru') {
            $user = User::where('email', 'hendra@warga.id')->first() 
                ?? User::where('role', 'warga')->where('verification_status', 'unverified')->first();
        } elseif ($role === 'warga-pending') {
            $user = User::where('email', 'siti@warga.id')->first()
                ?? User::where('role', 'warga')->where('verification_status', 'pending_rw')->first();
        } elseif ($role === 'warga') {
            $user = User::where('email', 'bambang@warga.id')->first()
                ?? User::where('role', 'warga')->where('verification_status', 'approved')->first();
        } else {
            $user = User::where('role', $role)->first();
        }

        if ($user) {
            Auth::login($user);
            if ($user->role === 'super_admin') {
                return redirect()->route('superadmin.index');
            } elseif ($user->role === 'ketua_rw') {
                return redirect()->route('dashboard')->with('success', "Selamat datang, {$user->name}. Anda login sebagai Ketua RW dengan hak supervisi & pembinaan seluruh RT di lingkungan RW.");
            } elseif ($user->role === 'satpam') {
                return redirect()->route('satpam.console');
            } elseif ($user->role === 'warga') {
                if ($user->verification_status === 'unverified') {
                    return redirect()->route('warga.index')->with('warning', "Selamat datang, {$user->name}! Status akun Anda: TERKUNCI. Mohon lengkapi data identitas KTP & tanggal lahir untuk verifikasi oleh Ketua RW.");
                } elseif ($user->verification_status === 'pending_rw') {
                    return redirect()->route('warga.index')->with('info', "Selamat datang, {$user->name}! Berkas KTP Anda sedang dalam proses peninjauan oleh Ketua RW.");
                }
                return redirect()->route('warga.index')->with('success', "Selamat datang, {$user->name}! Akun & E-KTP Anda telah terverifikasi resmi oleh Ketua RW.");
            }
            return redirect()->route('dashboard');
        }

        return redirect()->route('home')->with('error', "Akun percontohan untuk role '{$role}' tidak ditemukan.");
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda telah keluar dari sistem.');
    }
}
