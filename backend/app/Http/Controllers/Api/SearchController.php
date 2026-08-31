<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\MedicalRecordResource;
use App\Http\Resources\PrescriptionResource;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Role-Scoped Unified Full-Text Search
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim($request->input('q', ''));
        $type = $request->input('type', 'all'); // 'doctors', 'records', 'prescriptions', 'all'
        $user = $request->user();

        if (empty($query)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'doctors' => [],
                    'records' => [],
                    'prescriptions' => [],
                ],
            ]);
        }

        $results = [
            'doctors' => [],
            'records' => [],
            'prescriptions' => [],
        ];

        // 1. Doctors Search (Public / All Roles)
        if (in_array($type, ['all', 'doctors'], true)) {
            $doctors = Doctor::with(['user', 'availabilities'])
                ->where('is_active', true)
                ->where(function ($q) use ($query) {
                    $q->where('specialty', 'LIKE', "%{$query}%")
                        ->orWhere('bio', 'LIKE', "%{$query}%")
                        ->orWhere('license_number', 'LIKE', "%{$query}%")
                        ->orWhereHas('user', function ($uq) use ($query) {
                            $uq->where('name', 'LIKE', "%{$query}%")
                                ->orWhere('email', 'LIKE', "%{$query}%");
                        });
                })
                ->limit(10)
                ->get();

            $results['doctors'] = DoctorResource::collection($doctors);
        }

        // 2. Medical Records Search (Strictly Scoped by Role)
        if (in_array($type, ['all', 'records'], true) && $user) {
            $recordsQuery = MedicalRecord::with(['patient.user', 'doctor.user', 'appointment']);

            if ($user->isPatient()) {
                $patientId = $user->patient?->id;
                if ($patientId) {
                    $recordsQuery->where('patient_id', $patientId);
                } else {
                    $recordsQuery->whereRaw('1 = 0');
                }
            } elseif ($user->isDoctor()) {
                $doctorId = $user->doctor?->id;
                if ($doctorId) {
                    $recordsQuery->where('doctor_id', $doctorId);
                } else {
                    $recordsQuery->whereRaw('1 = 0');
                }
            }

            $records = $recordsQuery->get()->filter(function ($record) use ($query) {
                $diagnosisMatch = stripos($record->diagnosis ?? '', $query) !== false;
                $notesMatch = stripos($record->clinical_notes ?? '', $query) !== false;
                $treatmentMatch = stripos($record->treatment_plan ?? '', $query) !== false;
                $icdMatch = is_array($record->icd_10_codes) && count(array_filter($record->icd_10_codes, fn ($c) => stripos($c, $query) !== false)) > 0;
                $doctorNameMatch = stripos($record->doctor?->user?->name ?? '', $query) !== false;

                return $diagnosisMatch || $notesMatch || $treatmentMatch || $icdMatch || $doctorNameMatch;
            })->take(10)->values();

            $results['records'] = MedicalRecordResource::collection($records);
        }

        // 3. Prescriptions Search (Strictly Scoped by Role)
        if (in_array($type, ['all', 'prescriptions'], true) && $user) {
            $rxQuery = Prescription::with(['patient.user', 'doctor.user', 'items']);

            if ($user->isPatient()) {
                $patientId = $user->patient?->id;
                if ($patientId) {
                    $rxQuery->where('patient_id', $patientId);
                } else {
                    $rxQuery->whereRaw('1 = 0');
                }
            } elseif ($user->isDoctor()) {
                $doctorId = $user->doctor?->id;
                if ($doctorId) {
                    $rxQuery->where('doctor_id', $doctorId);
                } else {
                    $rxQuery->whereRaw('1 = 0');
                }
            }

            $prescriptions = $rxQuery->get()->filter(function ($rx) use ($query) {
                $notesMatch = stripos($rx->notes ?? '', $query) !== false;
                $doctorMatch = stripos($rx->doctor?->user?->name ?? '', $query) !== false;
                $itemsMatch = $rx->items->contains(function ($item) use ($query) {
                    return stripos($item->medication_name, $query) !== false
                        || stripos($item->instructions ?? '', $query) !== false
                        || stripos($item->dosage ?? '', $query) !== false;
                });

                return $notesMatch || $doctorMatch || $itemsMatch;
            })->take(10)->values();

            $results['prescriptions'] = PrescriptionResource::collection($prescriptions);
        }

        return response()->json([
            'success' => true,
            'query' => $query,
            'data' => $results,
        ]);
    }
}
