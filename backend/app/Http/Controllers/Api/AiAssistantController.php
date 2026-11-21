<?php

namespace App\Http\Controllers\Api;

use App\Services\AiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiAssistantController extends BaseApiController
{
    protected AiAssistantService $aiService;

    public function __construct(AiAssistantService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Interaksi tanya jawab dengan AI Assistant (Bang Warga AI)
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array',
            'history.*.sender' => 'required_with:history|string|in:user,bot',
            'history.*.text' => 'required_with:history|string|max:2000',
        ]);

        $user = Auth::user();
        $message = (string) $request->input('message');
        $history = (array) $request->input('history', []);

        $response = $this->aiService->respond($message, $user, $history);

        return $this->success($response, 'Berhasil diproses oleh Bang Warga AI');
    }

    /**
     * Dapatkan daftar saran pertanyaan cepat (Quick Suggestions)
     */
    public function suggestions(): JsonResponse
    {
        $suggestions = $this->aiService->getDefaultSuggestions();

        return $this->success([
            'suggestions' => $suggestions,
            'assistant_name' => 'Bang Warga AI',
            'role' => 'Asisten Digital Pintar Komunitas E-WARGA',
            'has_gemini' => !empty(config('services.gemini.key') ?: env('GEMINI_API_KEY')),
        ], 'Daftar saran pertanyaan berhasil diambil');
    }
}

