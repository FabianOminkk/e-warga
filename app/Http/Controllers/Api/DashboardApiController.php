<?php

namespace App\Http\Controllers\Api;

use App\Models\Billing;
use App\Models\CashBook;
use App\Models\GateLog;
use App\Models\House;
use App\Models\Payment;
use App\Models\SmartGate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardApiController extends BaseApiController
{
    /**
     * Get executive dashboard statistics for RT/RW/Bendahara.
     */
    public function summary(Request $request): JsonResponse
    {
        $tenant = Tenant::getActiveTenant() ?? Tenant::first();

        if (!$tenant) {
            return $this->error('Tenant komplek belum terdaftar.', null, 404);
        }

        $totalHouses = House::where('tenant_id', $tenant->id)->count();
        $totalWarga = User::where('tenant_id', $tenant->id)->where('role', 'warga')->count();

        $totalIncome = (float) CashBook::where('tenant_id', $tenant->id)->where('type', 'income')->sum('amount');
        $totalExpense = (float) CashBook::where('tenant_id', $tenant->id)->where('type', 'expense')->sum('amount');
        $saldoKas = $totalIncome - $totalExpense;

        $paidBillings = Billing::where('tenant_id', $tenant->id)->where('status', 'paid')->count();
        $unpaidBillings = Billing::where('tenant_id', $tenant->id)->where('status', 'unpaid')->count();

        $pendingPaymentsCount = Payment::whereNull('verified_at')
            ->whereHas('billing', fn($q) => $q->where('tenant_id', $tenant->id))
            ->count();

        $pendingWargaCount = User::where('tenant_id', $tenant->id)
            ->where('verification_status', 'pending_rw')
            ->count();

        $recentGateLogs = GateLog::where('tenant_id', $tenant->id)
            ->with(['user', 'smartGate'])
            ->latest('triggered_at')
            ->limit(10)
            ->get();

        return $this->success([
            'tenant' => $tenant,
            'statistics' => [
                'total_houses'           => $totalHouses,
                'total_warga'            => $totalWarga,
                'saldo_kas'              => $saldoKas,
                'total_income'           => $totalIncome,
                'total_expense'          => $totalExpense,
                'paid_billings'          => $paidBillings,
                'unpaid_billings'        => $unpaidBillings,
                'pending_payments_count' => $pendingPaymentsCount,
                'pending_verifications'  => $pendingWargaCount,
            ],
            'recent_gate_logs' => $recentGateLogs,
        ], 'Ringkasan data dashboard berhasil dimuat.');
    }

    /**
     * Get cash book transactions list with optional filter.
     */
    public function cashbook(Request $request): JsonResponse
    {
        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $query = CashBook::where('tenant_id', $tenant->id)->with('creator');

        if ($request->has('type') && in_array($request->type, ['income', 'expense'])) {
            $query->where('type', $request->type);
        }

        $transactions = $query->orderByDesc('transaction_date')->paginate(20);

        return $this->success($transactions, 'Daftar pembukuan kas berhasil diambil.');
    }

    /**
     * Store a new income or expense cash book entry.
     */
    public function storeCashBook(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'        => 'required|in:income,expense',
            'category'    => 'required|string|max:100',
            'title'       => 'required|string|max:200',
            'amount'      => 'required|numeric|min:1000',
            'notes'       => 'nullable|string',
        ]);

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $userId = Auth::id() ?? User::where('role', 'bendahara')->value('id') ?? User::first()?->id;

        $entry = CashBook::create([
            'tenant_id'          => $tenant->id,
            'created_by_user_id' => $userId,
            'type'               => $validated['type'],
            'category'           => $validated['category'],
            'title'              => $validated['title'],
            'amount'             => $validated['amount'],
            'transaction_date'   => now(),
            'notes'              => $validated['notes'] ?? null,
        ]);

        return $this->success($entry, 'Transaksi kas berhasil dicatat.', 201);
    }

    /**
     * Verify or reject resident payment proof.
     */
    public function verifyPayment(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'reason' => 'nullable|string',
        ]);

        $payment = Payment::with(['billing', 'payer'])->findOrFail($id);
        $verifierId = Auth::id() ?? User::where('role', 'bendahara')->value('id') ?? User::first()?->id;

        if ($validated['action'] === 'approve') {
            $payment->update([
                'verified_at'         => now(),
                'verified_by_user_id' => $verifierId,
            ]);

            $payment->billing->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);

            $tenant = Tenant::getActiveTenant() ?? Tenant::first();
            CashBook::create([
                'tenant_id'          => $tenant->id,
                'created_by_user_id' => $verifierId,
                'type'               => 'income',
                'category'           => 'Iuran Warga',
                'title'              => 'Pembayaran ' . $payment->billing->title,
                'amount'             => $payment->amount,
                'transaction_date'   => now(),
                'notes'              => 'Otomatis dari verifikasi pembayaran transfer #' . $payment->id,
            ]);

            return $this->success($payment, 'Pembayaran iuran berhasil disetujui.');
        } else {
            $payment->update([
                'verified_by_user_id' => $verifierId,
                'rejection_reason'    => $validated['reason'] ?? 'Bukti transfer tidak sesuai / tidak terbaca.',
            ]);

            return $this->success($payment, 'Pembayaran iuran telah ditolak.');
        }
    }

    /**
     * List citizen verification requests for RW approval.
     */
    public function verifications(Request $request): JsonResponse
    {
        $tenant = Tenant::getActiveTenant() ?? Tenant::first();

        $citizens = User::where('tenant_id', $tenant->id)
            ->where('role', 'warga')
            ->with(['house', 'verifiedBy'])
            ->orderByRaw("CASE WHEN verification_status = 'pending_rw' THEN 1 WHEN verification_status = 'unverified' THEN 2 ELSE 3 END")
            ->get();

        return $this->success($citizens, 'Daftar verifikasi KTP warga berhasil diambil.');
    }

    /**
     * Approve citizen verification and activate smart gate RFID access.
     */
    public function approveVerification(Request $request, int $id): JsonResponse
    {
        $targetUser = User::where('role', 'warga')->findOrFail($id);
        $approver = Auth::user() ?? User::where('role', 'ketua_rw')->first();

        $rfid = $targetUser->rfid_tag;
        if (empty($rfid)) {
            $rfid = 'RFID-' . ($targetUser->nik ?? ('AUTO-' . $targetUser->id));
        }

        $targetUser->update([
            'verification_status' => 'approved',
            'verified_by_user_id' => $approver?->id,
            'verified_at'         => now(),
            'gate_access_enabled' => true,
            'rfid_tag'            => $rfid,
            'rejection_reason'    => null,
        ]);

        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $gate = SmartGate::where('tenant_id', $tenant?->id)->first() ?? SmartGate::first();
        if ($gate) {
            GateLog::create([
                'tenant_id'      => $tenant?->id ?? $gate->tenant_id,
                'smart_gate_id'  => $gate->id,
                'user_id'        => $targetUser->id,
                'access_method'  => 'rfid_card',
                'direction'      => 'entry',
                'status'         => 'granted',
                'reason'         => "Otorisasi API RW: E-KTP & RFID {$targetUser->name} ({$rfid}) resmi aktif",
                'triggered_at'   => now(),
            ]);
        }

        return $this->success($targetUser, "Verifikasi identitas {$targetUser->name} disetujui. Akses gerbang aktif.");
    }

    /**
     * Reject citizen verification with reason.
     */
    public function rejectVerification(Request $request, int $id): JsonResponse
    {
        $targetUser = User::where('role', 'warga')->findOrFail($id);
        $reason = $request->input('reason', 'Foto KTP tidak jelas atau NIK tidak sesuai.');

        $targetUser->update([
            'verification_status' => 'rejected',
            'gate_access_enabled' => false,
            'rejection_reason'    => $reason,
        ]);

        return $this->success($targetUser, "Verifikasi identitas {$targetUser->name} ditolak.");
    }
}
