<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GeminiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    public function __construct(
        protected GeminiChatService $chatService
    ) {}

    /**
     * Handle incoming AI chat query.
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'patient_id' => ['nullable', 'integer'],
            'conversation_history' => ['nullable', 'array', 'max:20'],
            'conversation_history.*.role' => ['required_with:conversation_history', 'string', 'in:user,assistant,system'],
            'conversation_history.*.content' => ['required_with:conversation_history', 'string', 'max:2000'],
        ]);

        $user = $request->user();
        $patientId = $validated['patient_id'] ?? null;

        // Security boundary: Patients cannot query other patients' records
        if ($user->role?->value === 'patient') {
            $patientId = $user->patient?->id;
        }

        $history = $validated['conversation_history'] ?? [];
        $result = $this->chatService->chat($user, $validated['message'], $patientId, $history);

        return response()->json($result);
    }
}
