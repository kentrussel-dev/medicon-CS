<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiChatService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl;
    protected int $cacheTtl;

    public function __construct(
        protected AuditLoggerService $auditLogger,
        protected ?Request $request = null
    ) {
        $this->apiKey = (string) config('services.gemini.api_key', '');
        $this->model = (string) config('services.gemini.model', 'gemini-1.5-flash');
        $this->baseUrl = (string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');
        $this->cacheTtl = (int) config('services.gemini.cache_ttl', 86400);
        $this->request = $request ?? request();
    }

    /**
     * Process an AI chat inquiry scoped to the authenticated user's role and data.
     */
    public function chat(User $user, string $message, ?int $patientId = null, array $conversationHistory = []): array
    {
        $role = $user->role?->value ?? 'patient';
        $normalizedPrompt = trim(strtolower($message));
        $cacheKey = "ai_chat_cache:{$role}:" . md5($normalizedPrompt);

        // 1. Check Redis cache for repeated FAQ / informational questions
        $isFaqQuestion = $this->isGeneralQuestion($message);
        if ($isFaqQuestion && Cache::has($cacheKey)) {
            $cachedText = Cache::get($cacheKey);
            $this->logChatAudit($user, $message, $patientId, cached: true);

            return [
                'success' => true,
                'message' => $cachedText,
                'role' => $role,
                'cached' => true,
                'timestamp' => now()->toIso8601String(),
            ];
        }

        // 2. Assemble Role-Scoped Context & System Instruction
        $patientContext = $this->resolveAuthorizedPatientContext($user, $patientId);
        $systemInstruction = $this->buildSystemInstruction($user, $patientContext);

        // 3. Build Gemini Request Payload
        $contents = $this->formatContents($conversationHistory, $message);

        // 4. Query Gemini API with Retry & Exponential Backoff
        $responseText = null;
        $usedFallback = false;

        if (!empty($this->apiKey)) {
            try {
                $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

                $response = Http::timeout(8.0)
                    ->retry(2, 500, function ($exception) {
                        return $exception instanceof ConnectionException ||
                            (method_exists($exception, 'response') && in_array($exception->response?->status(), [429, 500, 502, 503]));
                    }, throw: false)
                    ->post($url, [
                        'system_instruction' => [
                            'parts' => [['text' => $systemInstruction]],
                        ],
                        'contents' => $contents,
                        'generationConfig' => [
                            'temperature' => 0.2,
                            'topP' => 0.85,
                            'maxOutputTokens' => 1024,
                        ],
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $responseText = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
                } else {
                    Log::warning('Gemini API returned error status', [
                        'status' => $response->status(),
                        'body' => substr($response->body(), 0, 500),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Gemini API connection failure', ['error' => $e->getMessage()]);
            }
        }

        // 5. Fallback Clinical Response if API call fails or key is unconfigured
        if (!$responseText) {
            $responseText = $this->generateFallbackResponse($user, $message, $patientContext);
            $usedFallback = true;
        }

        // 6. Cache response if it was a general / FAQ question
        if ($isFaqQuestion && $responseText && !$usedFallback) {
            Cache::put($cacheKey, $responseText, $this->cacheTtl);
        }

        // 7. HIPAA Audit Trail
        $this->logChatAudit($user, $message, $patientContext?->id ?? $patientId, cached: false);

        return [
            'success' => true,
            'message' => $responseText,
            'role' => $role,
            'cached' => false,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Resolve patient context ensuring strict authorization boundaries.
     */
    protected function resolveAuthorizedPatientContext(User $user, ?int $patientId = null): ?Patient
    {
        // For Patient: always strictly their own profile
        if ($user->role?->value === 'patient') {
            return $user->patient;
        }

        // For Doctor / Admin: allowed to lookup requested patient
        if ($patientId && ($user->role?->value === 'doctor' || $user->role?->value === 'admin')) {
            return Patient::with(['user', 'medicalRecords', 'prescriptions', 'appointments'])->find($patientId);
        }

        return null;
    }

    /**
     * Build role-specific system prompt with injected authorized clinical context.
     */
    public function buildSystemInstruction(User $user, ?Patient $patient = null): string
    {
        $role = $user->role?->value ?? 'patient';

        if ($role === 'doctor') {
            $doctor = $user->doctor;
            $doctorName = $user->name;
            $specialty = $doctor?->specialty ?? 'General Practice';
            $license = $doctor?->license_number ?? 'MD-ACTIVE';

            $prompt = "You are the Medicon Clinical Co-Pilot & Physician Reference Assistant for licensed medical staff.\n";
            $prompt .= "Physician Profile: {$doctorName} ({$specialty}, License: {$license})\n\n";

            if ($patient) {
                $dob = $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : 'Unknown';
                $gender = $patient->gender ?? 'Unknown';
                $allergies = $patient->allergies ?? 'None recorded';

                $prompt .= "--- AUTHORIZED PATIENT EHR CONTEXT ---\n";
                $prompt .= "Patient Name: {$patient->user?->name} (Age/DOB: {$dob}, Biological Gender: {$gender})\n";
                $prompt .= "Recorded Allergies (Encrypted Cast): {$allergies}\n";

                // Add recent diagnoses
                $records = MedicalRecord::where('patient_id', $patient->id)->latest()->take(3)->get();
                if ($records->isNotEmpty()) {
                    $prompt .= "Recent Clinical Diagnoses:\n";
                    foreach ($records as $r) {
                        $prompt .= "- {$r->diagnosis} (Notes: " . substr($r->clinical_notes ?? '', 0, 150) . ")\n";
                    }
                }

                // Add active prescriptions
                $rx = Prescription::where('patient_id', $patient->id)->where('status', 'ACTIVE')->get();
                if ($rx->isNotEmpty()) {
                    $prompt .= "Active Medication Courses:\n";
                    foreach ($rx as $item) {
                        $prompt .= "- {$item->medication_name} {$item->dosage} ({$item->frequency})\n";
                    }
                }
                $prompt .= "--------------------------------------\n\n";
            }

            $prompt .= "ROLE CAPABILITIES & SCOPE FOR DOCTORS:\n";
            $prompt .= "1. Summarize patient clinical timelines, diagnostic history, and vital trends before visits.\n";
            $prompt .= "2. Assist in drafting structured SOAP (Subjective, Objective, Assessment, Plan) encounter notes based on the doctor's bullet points.\n";
            $prompt .= "3. Provide reference information on drug-drug interactions, contraindications, and clinical dosage guidelines.\n";
            $prompt .= "4. DECISION SUPPORT BOUNDARY: All generated output is informational decision support only. You do not make treatment decisions; clinical decisions remain the sole responsibility of the attending physician.";

            return $prompt;
        }

        if ($role === 'admin') {
            return "You are the Medicon Hospital Operations & Compliance Assistant. Assist administrators with hospital utilization metrics, attendance risk factors, HIPAA compliance auditing rules, and system operations.";
        }

        // Default Patient System Instruction
        $patient = $patient ?? $user->patient;
        $name = $user->name;
        $allergies = $patient?->allergies ?? 'None recorded';
        $bloodType = $patient?->blood_type ?? 'O+';

        $prompt = "You are the Medicon Patient Healthcare Concierge & Assistant.\n";
        $prompt .= "Patient: {$name} | Known Allergies: {$allergies} | Blood Type: {$bloodType}\n\n";

        if ($patient) {
            // Include upcoming appointments
            $appts = Appointment::where('patient_id', $patient->id)
                ->where('status', 'CONFIRMED')
                ->where('scheduled_start', '>=', now())
                ->orderBy('scheduled_start')
                ->take(2)
                ->get();

            if ($appts->isNotEmpty()) {
                $prompt .= "Upcoming Scheduled Consultations:\n";
                foreach ($appts as $a) {
                    $prompt .= "- Doctor ID #{$a->doctor_id}, Date: {$a->scheduled_start->toDayDateTimeString()} ({$a->type})\n";
                }
            }

            // Include active prescriptions
            $rx = Prescription::where('patient_id', $patient->id)->where('status', 'ACTIVE')->take(3)->get();
            if ($rx->isNotEmpty()) {
                $prompt .= "Current Authorized Prescriptions:\n";
                foreach ($rx as $p) {
                    $prompt .= "- {$p->medication_name} {$p->dosage}: {$p->instructions}\n";
                }
            }
        }

        $prompt .= "\nPATIENT SAFETY DIRECTIVES & SCOPING:\n";
        $prompt .= "1. Help the patient understand their own appointments, clinic hours (Mon-Fri 9am-5pm), and how to schedule/reschedule.\n";
        $prompt .= "2. Explain their existing prescriptions, lab names, and medical terms in simple, reassuring, non-technical words.\n";
        $prompt .= "3. ABSOLUTE PROHIBITION: You MUST NOT diagnose symptoms, prescribe medications, or provide definitive treatment advice.\n";
        $prompt .= "4. If the patient asks 'Do I have X condition?' or describes acute symptoms (e.g. chest pain, breathing difficulty, severe bleeding), you must IMMEDIATELY instruct them: 'I cannot provide a medical diagnosis. Please seek emergency medical care immediately or call our 24/7 clinical triage hotline.'\n";
        $prompt .= "5. Always remind them to discuss medication changes directly with their attending physician.";

        return $prompt;
    }

    /**
     * Format conversation history for Gemini API.
     */
    protected function formatContents(array $history, string $newMessage): array
    {
        $contents = [];

        foreach ($history as $turn) {
            if (isset($turn['role']) && isset($turn['content'])) {
                $geminiRole = $turn['role'] === 'assistant' ? 'model' : 'user';
                $contents[] = [
                    'role' => $geminiRole,
                    'parts' => [['text' => (string) $turn['content']]],
                ];
            }
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $newMessage]],
        ];

        return $contents;
    }

    /**
     * Determine if a query is a general FAQ/informational question suitable for caching.
     */
    protected function isGeneralQuestion(string $message): bool
    {
        $lower = strtolower($message);
        $faqKeywords = [
            'visiting hour', 'clinic hour', 'location', 'phone', 'contact',
            'prepare for blood test', 'fasting', 'how to reschedule', 'how to book',
            'what is lisinopril', 'what is amoxicillin', 'what is hypertension',
            'drug interaction', 'contraindication', 'hipaa', 'refund policy',
        ];

        foreach ($faqKeywords as $keyword) {
            if (str_contains($lower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generates a context-aware fallback response when the Gemini API is offline or keyless.
     */
    public function generateFallbackResponse(User $user, string $message, ?Patient $patient = null): string
    {
        $lower = strtolower($message);
        $role = $user->role?->value ?? 'patient';

        if ($role === 'doctor') {
            if (str_contains($lower, 'soap') || str_contains($lower, 'draft') || str_contains($lower, 'note')) {
                return "**Draft SOAP Encounter Note Template**\n\n"
                    . "**S (Subjective):** Patient presents for scheduled follow-up. Reports compliance with prescribed medication regimen; denies adverse side effects.\n"
                    . "**O (Objective):** Vital Signs: BP 120/80 mmHg, HR 72 bpm, SpO2 99%. Physical examination unremarkable.\n"
                    . "**A (Assessment):** Stable chronic condition management under active medical supervision.\n"
                    . "**P (Plan):** Continue current pharmacotherapy. Recheck blood chemistry in 90 days. Schedule routine follow-up in 3 months.";
            }

            if (str_contains($lower, 'interaction') || str_contains($lower, 'potassium') || str_contains($lower, 'lisinopril')) {
                return "**Clinical Pharmacology Reference: ACE Inhibitors + Potassium**\n\n"
                    . "- **Mechanism:** ACE inhibitors (e.g. Lisinopril) reduce aldosterone secretion, leading to potential potassium retention.\n"
                    . "- **Risk:** Concomitant potassium supplements or potassium-sparing diuretics may induce hyperkalemia.\n"
                    . "- **Monitoring:** Recommend periodic serum creatinine and potassium electrolyte panels.";
            }

            return "Medicon Clinical Assistant is online. You can request SOAP note drafting assistance, patient history summaries, or pharmacology reference lookup.";
        }

        // Patient Fallback Answers
        if (str_contains($lower, 'hour') || str_contains($lower, 'open') || str_contains($lower, 'time')) {
            return "Medicon Clinical Centers are open **Monday through Friday, 8:00 AM to 5:00 PM**. Telehealth appointments operate 24/7 according to your physician's available schedule.";
        }

        if (str_contains($lower, 'appointment') || str_contains($lower, 'schedule') || str_contains($lower, 'book')) {
            return "To schedule a new consultation, click the **'Book Consultation'** button at the top of your dashboard. You can choose between an encrypted HD Telehealth Video visit or an in-person clinic visit.";
        }

        if (str_contains($lower, 'prescription') || str_contains($lower, 'medication') || str_contains($lower, 'lisinopril')) {
            return "Your active prescriptions are securely stored under the **'Prescriptions'** tab in your patient portal. Please always take medications as directed by your attending doctor, and do not alter dosages without physician consultation.";
        }

        if (str_contains($lower, 'pain') || str_contains($lower, 'symptom') || str_contains($lower, 'diagnos') || str_contains($lower, 'sick')) {
            return "**Clinical Safety Notice:** I cannot provide a medical diagnosis or evaluate acute symptoms. If you are experiencing severe or life-threatening symptoms, please call emergency services immediately or contact our urgent triage hotline at **+63-2-8521-0020**.";
        }

        return "Hello, {$user->name}! I am your Medicon Healthcare Assistant. I can help explain your existing prescriptions, upcoming appointment details, or general clinic visiting procedures.";
    }

    /**
     * Log HIPAA audit trail for AI chat interaction.
     */
    protected function logChatAudit(User $user, string $message, ?int $patientId = null, bool $cached = false): void
    {
        try {
            $this->auditLogger->log(
                action: AuditAction::AI_QUERY,
                recordType: 'AiChatAssistant',
                recordId: null,
                patientId: $patientId,
                newValues: [
                    'query_length' => strlen($message),
                    'cached' => $cached,
                    'model' => $this->model,
                ],
                actor: $user
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to write AI chat audit log', ['error' => $e->getMessage()]);
        }
    }
}
