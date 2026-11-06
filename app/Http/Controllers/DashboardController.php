<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\CashBook;
use App\Models\GateLog;
use App\Models\House;
use App\Models\Payment;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user && $user->role === 'satpam') {
            return redirect()->route('satpam.console')->with('error', 'Akses dialihkan: Petugas Satpam hanya memiliki akses ke Konsol Pos Jaga.');
        }
        if ($user && $user->role === 'warga') {
            return redirect()->route('warga.index')->with('error', 'Akses dialihkan: Warga diarahkan ke Portal Warga Mandiri.');
        }

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $totalHouses = House::where('tenant_id', $tenant->id)->count();
        $totalWarga = User::where('tenant_id', $tenant->id)->where('role', 'warga')->count();

        // Keuangan scoped to tenant
        $totalIncome = CashBook::where('tenant_id', $tenant->id)->where('type', 'income')->sum('amount');
        $totalExpense = CashBook::where('tenant_id', $tenant->id)->where('type', 'expense')->sum('amount');
        $saldoKas = $totalIncome - $totalExpense;

        // Tagihan scoped to tenant
        $paidBillings = Billing::where('tenant_id', $tenant->id)->where('status', 'paid')->count();
        $unpaidBillings = Billing::where('tenant_id', $tenant->id)->where('status', 'unpaid')->count();
        $pendingPayments = Payment::whereNull('verified_at')
            ->whereHas('billing', fn($q) => $q->where('tenant_id', $tenant->id))
            ->with(['billing.house', 'payer'])
            ->get();

        // Verifikasi KTP Warga oleh Ketua RW
        $pendingWargaCount = User::where('tenant_id', $tenant->id)->where('verification_status', 'pending_rw')->count();
        $wargaVerifications = User::where('tenant_id', $tenant->id)
            ->where('role', 'warga')
            ->with(['house', 'verifiedBy'])
            ->orderByRaw("CASE WHEN verification_status = 'pending_rw' THEN 1 WHEN verification_status = 'unverified' THEN 2 ELSE 3 END")
            ->get();

        // Data Grafik 1: Proporsi Pengeluaran per Kategori (Pie Chart)
        $expenseByCategory = CashBook::where('tenant_id', $tenant->id)
            ->where('type', 'expense')
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->pluck('total', 'category');

        $chartExpenseLabels = $expenseByCategory->keys()->toArray();
        $chartExpenseTotals = $expenseByCategory->values()->map(fn($v) => (float)$v)->toArray();

        if (empty($chartExpenseLabels)) {
            $chartExpenseLabels = ['Kas Belum Ada Pengeluaran'];
            $chartExpenseTotals = [0];
        }

        // Data Grafik 2: Kepatuhan Pembayaran Iuran (Donut Chart)
        $chartBillingStatus = [
            'Lunas' => $paidBillings,
            'Belum Bayar' => $unpaidBillings,
            'Menunggu Verifikasi' => $pendingPayments->count(),
        ];

        // Data Grafik 3: Perbandingan Pemasukan vs Pengeluaran (Bar Chart)
        $chartCashflow = [
            'Pemasukan' => (float)$totalIncome,
            'Pengeluaran' => (float)$totalExpense,
            'Saldo Bersih' => (float)max(0, $saldoKas),
        ];

        // Log Pintu Terkini scoped
        $gateIds = SmartGate::where('tenant_id', $tenant->id)->pluck('id');
        $recentGateLogs = GateLog::whereIn('smart_gate_id', $gateIds)->with(['user', 'smartGate'])->latest('triggered_at')->take(6)->get();

        // Data Rumah & Penghuni
        $houses = House::where('tenant_id', $tenant->id)->with(['residents', 'vehicles'])->get();

        // Riwayat Kas Terakhir
        $recentCashBooks = CashBook::where('tenant_id', $tenant->id)->latest('transaction_date')->take(8)->get();

        return view('pages.dashboard', compact(
            'tenant',
            'totalHouses',
            'totalWarga',
            'totalIncome',
            'totalExpense',
            'saldoKas',
            'paidBillings',
            'unpaidBillings',
            'pendingPayments',
            'pendingWargaCount',
            'wargaVerifications',
            'chartExpenseLabels',
            'chartExpenseTotals',
            'chartBillingStatus',
            'chartCashflow',
            'recentGateLogs',
            'houses',
            'recentCashBooks'
        ));
    }

    public function verifyPayment(Request $request, $id)
    {
        $payment = Payment::with(['billing.house'])->findOrFail($id);
        $action = $request->input('action'); // 'approve' or 'reject'

        $verifierId = Auth::id() ?? User::where('role', 'bendahara')->value('id') ?? User::first()?->id;

        if ($action === 'approve') {
            $payment->update([
                'verified_by_user_id' => $verifierId,
                'verified_at' => now(),
            ]);

            if ($payment->billing) {
                $payment->billing->update([
                    'status' => 'paid',
                ]);

                $houseInfo = $payment->billing->house?->block_and_number ?? 'Warga';

                // Catat otomatis ke buku kas umum
                CashBook::create([
                    'tenant_id' => $payment->billing->tenant_id,
                    'created_by_user_id' => $verifierId,
                    'type' => 'income',
                    'category' => 'Iuran Warga',
                    'title' => 'Iuran Rumah ' . $houseInfo,
                    'amount' => $payment->amount_paid,
                    'transaction_date' => now(),
                    'notes' => 'Pembayaran diverifikasi bendahara',
                ]);
            }

            return redirect()->back()->with('success', 'Pembayaran berhasil diverifikasi dan masuk ke kas RT.');
        } else {
            $payment->update([
                'verified_by_user_id' => $verifierId,
                'rejection_reason' => $request->input('reason', 'Bukti transfer tidak sesuai / tidak terbaca.'),
            ]);

            return redirect()->back()->with('warning', 'Pembayaran ditolak.');
        }
    }

    public function storeCashBook(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:100',
            'title' => 'required|string|max:200',
            'amount' => 'required|numeric|min:1000',
            'notes' => 'nullable|string',
        ]);

        $tenant = Tenant::first();
        if (!$tenant) {
            return redirect()->back()->with('error', 'Data komplek belum tersedia.');
        }

        $userId = Auth::id() ?? User::where('role', 'bendahara')->value('id') ?? User::first()?->id;

        CashBook::create([
            'tenant_id' => $tenant->id,
            'created_by_user_id' => $userId,
            'type' => $validated['type'],
            'category' => $validated['category'],
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'transaction_date' => now(),
            'notes' => $validated['notes'],
        ]);

        return redirect()->back()->with('success', 'Transaksi kas berhasil dicatat!');
    }

    public function approveWargaVerification($id)
    {
        $targetUser = User::where('role', 'warga')->findOrFail($id);
        $approver = Auth::user() ?? User::where('role', 'ketua_rw')->first();

        // Pastikan NIK dan rfid_tag tersedia
        $rfid = $targetUser->rfid_tag;
        if (empty($rfid)) {
            $rfid = 'RFID-' . ($targetUser->nik ?? ('AUTO-' . $targetUser->id));
        }

        $targetUser->update([
            'verification_status' => 'approved',
            'verified_by_user_id' => $approver?->id,
            'verified_at' => now(),
            'gate_access_enabled' => true,
            'rfid_tag' => $rfid,
            'rejection_reason' => null,
        ]);

        // Buat log sistem bahwa palang gerbang dan satpam telah disinkronkan
        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $gate = SmartGate::where('tenant_id', $tenant?->id)->first() ?? SmartGate::first();
        if ($gate) {
            GateLog::create([
                'tenant_id' => $tenant?->id ?? $gate->tenant_id,
                'smart_gate_id' => $gate->id,
                'user_id' => $targetUser->id,
                'access_method' => 'rfid_card',
                'direction' => 'entry',
                'status' => 'granted',
                'reason' => "Sinkronisasi Otorisasi RW: E-KTP & RFID {$targetUser->name} ({$rfid}) resmi aktif di gerbang dan pos satpam",
                'triggered_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', "Identitas KTP & RFID Bpk/Ibu {$targetUser->name} berhasil DI-ACC OLEH KETUA RW! Akses palang pintu otomatis telah aktif dan disinkronkan ke Pos Satpam.");
    }

    public function rejectWargaVerification(Request $request, $id)
    {
        $targetUser = User::where('role', 'warga')->findOrFail($id);
        $reason = $request->input('reason', 'Foto KTP tidak jelas / NIK tidak cocok dengan data kependudukan.');

        $targetUser->update([
            'verification_status' => 'rejected',
            'gate_access_enabled' => false,
            'rejection_reason' => $reason,
        ]);

        return redirect()->back()->with('warning', "Pengajuan verifikasi KTP untuk {$targetUser->name} telah ditolak dengan catatan: {$reason}");
    }
}
