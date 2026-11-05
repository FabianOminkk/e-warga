<?php

namespace App\Http\Controllers;

use App\Models\LetterRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LetterController extends Controller
{
    public function index(Request $request)
    {
        $tenant = Tenant::first() ?? Tenant::create([
            'name' => 'RT 04 / RW 02 - Graha Asri Residence',
            'slug' => 'graha-asri-rt04',
            'rt_rw' => 'RT 04 / RW 02',
            'province' => 'Jawa Barat',
            'city' => 'Bekasi',
            'district' => 'Cikarang Utara',
            'postal_code' => '17530',
            'subscription_plan' => 'growth',
            'environment_type' => 'cluster',
            'is_active' => true,
        ]);

        $filterStatus = $request->query('status', 'all');

        $query = LetterRequest::where('tenant_id', $tenant->id)
            ->with(['user', 'signer'])
            ->orderBy('created_at', 'desc');

        if ($filterStatus !== 'all' && in_array($filterStatus, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $filterStatus);
        }

        $letters = $query->get();

        $stats = [
            'total' => LetterRequest::where('tenant_id', $tenant->id)->count(),
            'pending' => LetterRequest::where('tenant_id', $tenant->id)->where('status', 'pending')->count(),
            'approved' => LetterRequest::where('tenant_id', $tenant->id)->where('status', 'approved')->count(),
            'rejected' => LetterRequest::where('tenant_id', $tenant->id)->where('status', 'rejected')->count(),
        ];

        return view('pages.letters', compact('tenant', 'letters', 'stats', 'filterStatus'));
    }

    public function store(Request $request)
    {
        $tenant = Tenant::first();
        $wargaUser = User::where('role', 'warga')->first() ?? User::first();

        $validated = $request->validate([
            'letter_type' => 'required|in:pengantar_ktp,pengantar_kk,domisili,sktm,izin_usaha,pengantar_nikah,kematian',
            'applicant_name' => 'required|string|max:255',
            'nik' => 'required|string|max:20',
            'house_block' => 'required|string|max:50',
            'purpose' => 'required|string|max:500',
            'supporting_data' => 'nullable|string',
        ]);

        $validated['tenant_id'] = $tenant->id;
        $validated['user_id'] = $wargaUser?->id ?? 1;
        $validated['status'] = 'pending';

        LetterRequest::create($validated);

        return back()->with('success', 'Permohonan surat berhasil diajukan! Menunggu penandatanganan oleh Ketua RT.');
    }

    public function approve(Request $request, $id)
    {
        $tenant = Tenant::first();
        $letter = LetterRequest::findOrFail($id);
        $rtUser = User::where('role', 'ketua_rt')->first() ?? User::first();

        $romans = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        $currentMonthRoman = $romans[(int)date('n')] ?? 'XI';
        $currentYear = date('Y');
        $letterSequence = str_pad($letter->id, 3, '0', STR_PAD_LEFT);

        $letterNumber = "470/{$letterSequence}/RT.04/RW.02/{$currentMonthRoman}/{$currentYear}";
        $qrCode = 'QR-SURAT-' . strtoupper(Str::random(10));

        $letter->update([
            'status' => 'approved',
            'letter_number' => $letterNumber,
            'qr_verification_code' => $qrCode,
            'signed_at' => now(),
            'signed_by' => $rtUser?->id,
        ]);

        return back()->with('success', "Surat resmi nomor {$letterNumber} berhasil ditandatangani secara digital oleh Ketua RT!");
    }

    public function reject(Request $request, $id)
    {
        $letter = LetterRequest::findOrFail($id);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);

        $letter->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', "Permohonan surat telah ditolak dengan catatan: " . $validated['rejection_reason']);
    }

    public function verify($code)
    {
        $letter = LetterRequest::where('qr_verification_code', $code)->with(['tenant', 'signer'])->firstOrFail();

        return view('pages.letter-verify', compact('letter'));
    }
}
