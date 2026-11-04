<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\CashBook;
use App\Models\GateLog;
use App\Models\House;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $tenant = Tenant::first();
        $totalHouses = House::count();
        $totalWarga = User::where('role', 'warga')->count();

        // Keuangan
        $totalIncome = CashBook::where('type', 'income')->sum('amount');
        $totalExpense = CashBook::where('type', 'expense')->sum('amount');
        $saldoKas = $totalIncome - $totalExpense;

        // Tagihan
        $paidBillings = Billing::where('status', 'paid')->count();
        $unpaidBillings = Billing::where('status', 'unpaid')->count();
        $pendingPayments = Payment::whereNull('verified_at')->with(['billing.house', 'payer'])->get();

        // Log Pintu Terkini
        $recentGateLogs = GateLog::with(['user', 'smartGate'])->latest('triggered_at')->take(6)->get();

        // Data Rumah & Penghuni
        $houses = House::with(['residents', 'vehicles'])->get();

        // Riwayat Kas Terakhir
        $recentCashBooks = CashBook::latest('transaction_date')->take(8)->get();

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
            'recentGateLogs',
            'houses',
            'recentCashBooks'
        ));
    }

    public function verifyPayment(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        $action = $request->input('action'); // 'approve' or 'reject'

        if ($action === 'approve') {
            $payment->update([
                'verified_by_user_id' => Auth::id() ?? 1,
                'verified_at' => now(),
            ]);

            $payment->billing->update([
                'status' => 'paid',
            ]);

            // Catat otomatis ke buku kas umum
            CashBook::create([
                'tenant_id' => $payment->billing->tenant_id,
                'created_by_user_id' => Auth::id() ?? 1,
                'type' => 'income',
                'category' => 'Iuran Warga',
                'title' => 'Iuran Rumah ' . ($payment->billing->house->block_name . ' No. ' . $payment->billing->house->house_number),
                'amount' => $payment->amount_paid,
                'transaction_date' => now(),
                'notes' => 'Pembayaran diverifikasi bendahara',
            ]);

            return redirect()->back()->with('success', 'Pembayaran berhasil diverifikasi dan masuk ke kas RT.');
        } else {
            $payment->update([
                'verified_by_user_id' => Auth::id() ?? 1,
                'rejection_reason' => $request->input('reason', 'Bukti tidak sesuai / tidak terbaca'),
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

        CashBook::create([
            'tenant_id' => $tenant->id,
            'created_by_user_id' => Auth::id() ?? 1,
            'type' => $validated['type'],
            'category' => $validated['category'],
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'transaction_date' => now(),
            'notes' => $validated['notes'],
        ]);

        return redirect()->back()->with('success', 'Transaksi kas berhasil dicatat!');
    }
}

