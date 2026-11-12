<?php

namespace App\Http\Controllers\Api;

use App\Models\LetterRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LetterApiController extends BaseApiController
{
    /**
     * Retrieve letter requests list and status statistics.
     */
    public function index(Request $request): JsonResponse
    {
        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $filterStatus = $request->query('status', 'all');

        $query = LetterRequest::where('tenant_id', $tenant->id)
            ->with(['user', 'signer'])
            ->orderByDesc('created_at');

        if ($filterStatus !== 'all' && in_array($filterStatus, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $filterStatus);
        }

        $letters = $query->get();

        $stats = [
            'total'    => LetterRequest::where('tenant_id', $tenant->id)->count(),
            'pending'  => LetterRequest::where('tenant_id', $tenant->id)->where('status', 'pending')->count(),
            'approved' => LetterRequest::where('tenant_id', $tenant->id)->where('status', 'approved')->count(),
            'rejected' => LetterRequest::where('tenant_id', $tenant->id)->where('status', 'rejected')->count(),
        ];

        return $this->success([
            'stats'   => $stats,
            'letters' => $letters,
        ], 'Daftar surat pengantar RT online berhasil diambil.');
    }

    /**
     * Submit a new online surat pengantar request.
     */
    public function store(Request $request): JsonResponse
    {
        $tenant = Tenant::getActiveTenant() ?? Tenant::first();
        $user = $request->user() ?? Auth::user() ?? User::where('role', 'warga')->first();

        $validated = $request->validate([
            'letter_type'     => 'required|in:pengantar_ktp,pengantar_kk,domisili,sktm,izin_usaha,pengantar_nikah,kematian',
            'applicant_name'  => 'required|string|max:255',
            'nik'             => 'required|string|max:20',
            'house_block'     => 'required|string|max:50',
            'purpose'         => 'required|string|max:500',
            'supporting_data' => 'nullable|string',
        ]);

        $qrCode = 'QR-SURAT-' . strtoupper(Str::random(10));

        $letter = LetterRequest::create([
            'tenant_id'            => $tenant->id,
            'user_id'              => $user?->id ?? 1,
            'letter_type'          => $validated['letter_type'],
            'applicant_name'       => $validated['applicant_name'],
            'nik'                  => $validated['nik'],
            'house_block'          => $validated['house_block'],
            'purpose'              => $validated['purpose'],
            'supporting_data'      => $validated['supporting_data'] ?? null,
            'status'               => 'pending',
            'qr_verification_code' => $qrCode,
        ]);

        return $this->success($letter, 'Permohonan surat pengantar online berhasil diajukan.', 201);
    }

    /**
     * RT / RW Approver digitally signs and issues letter.
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $letter = LetterRequest::findOrFail($id);
        $signer = $request->user() ?? Auth::user() ?? User::where('role', 'ketua_rt')->first();

        $romans = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        $currentMonthRoman = $romans[(int)date('n')] ?? 'XI';
        $currentYear = date('Y');
        $letterSequence = str_pad($letter->id, 3, '0', STR_PAD_LEFT);

        $letterNumber = "470/{$letterSequence}/RT.04/RW.02/{$currentMonthRoman}/{$currentYear}";
        $qrCode = $letter->qr_verification_code ?? ('QR-SURAT-' . strtoupper(Str::random(10)));

        $letter->update([
            'status'               => 'approved',
            'letter_number'        => $letterNumber,
            'qr_verification_code' => $qrCode,
            'signed_at'            => now(),
            'signed_by'            => $signer?->id,
            'rejection_reason'     => null,
        ]);

        return $this->success($letter, "Surat berhasil disetujui dengan nomor resmi: {$letterNumber}");
    }

    /**
     * RT / RW Approver rejects request with specific reason.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $letter = LetterRequest::findOrFail($id);
        $signer = $request->user() ?? Auth::user() ?? User::where('role', 'ketua_rt')->first();

        $letter->update([
            'status'           => 'rejected',
            'signed_by'        => $signer?->id,
            'signed_at'        => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return $this->success($letter, 'Permohonan surat pengantar telah ditolak.');
    }

    /**
     * Public verification of issued letter validity.
     */
    public function verify(Request $request, string $code): JsonResponse
    {
        $letter = LetterRequest::where('qr_verification_code', $code)
            ->with(['user', 'signer', 'tenant'])
            ->first();

        if (!$letter) {
            return $this->error('Kode verifikasi surat tidak ditemukan dalam basis data resmi RT/RW.', null, 404);
        }

        return $this->success([
            'letter'       => $letter,
            'is_valid'     => $letter->status === 'approved',
            'verified_at'  => now()->toISOString(),
        ], 'Surat resmi terverifikasi valid.');
    }
}
