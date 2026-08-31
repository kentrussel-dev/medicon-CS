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
     * Handle incoming AI chat query with active screen and route context.
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'patient_id' => ['nullable', 'integer'],
            'conversation_history' => ['nullable', 'array', 'max:20'],
            'conversation_history.*.role' => ['required_with:conversation_history', 'string', 'in:user,assistant,system'],
            'conversation_history.*.content' => ['required_with:conversation_history', 'string', 'max:2000'],
            'screen_context' => ['nullable', 'array'],
            'screen_context.path' => ['nullable', 'string', 'max:255'],
            'screen_context.name' => ['nullable', 'string', 'max:255'],
            'screen_context.title' => ['nullable', 'string', 'max:255'],
            'screen_context.description' => ['nullable', 'string', 'max:500'],
            'screen_context.details' => ['nullable', 'array'],
        ]);

        $user = $request->user();
        $patientId = $validated['patient_id'] ?? null;

        // Security boundary: Patients cannot query other patients' records
        if ($user->role?->value === 'patient') {
            $patientId = $user->patient?->id;
        }

        $history = $validated['conversation_history'] ?? [];
        $screenContext = $validated['screen_context'] ?? null;

        $result = $this->chatService->chat($user, $validated['message'], $patientId, $history, $screenContext);

        return response()->json($result);
    }
}
