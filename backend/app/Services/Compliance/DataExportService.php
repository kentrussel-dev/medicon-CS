<?php

namespace App\Services\Compliance;

use App\Models\User;
use Carbon\Carbon;

class DataExportService
{
    /**
     * Generate complete structured clinical & financial data export for a user
     */
    public function exportUserData(User $user): array
    {
        $patient = $user->patient;

        $appointments = [];
        $medicalRecords = [];
        $prescriptions = [];
        $payments = [];
        $auditLogs = [];

        if ($patient) {
            // 1. Appointments History
            $appointments = $patient->appointments()
                ->with(['doctor.user', 'payment'])
                ->get()
                ->map(fn ($appt) => [
                    'id' => $appt->id,
                    'scheduled_start' => $appt->scheduled_start?->toIso8601String(),
                    'scheduled_end' => $appt->scheduled_end?->toIso8601String(),
                    'status' => $appt->status?->value ?? (string) $appt->status,
                    'type' => $appt->type?->value ?? (string) $appt->type,
                    'reason' => $appt->reason,
                    'doctor_name' => $appt->doctor?->user?->name,
                    'doctor_specialty' => $appt->doctor?->specialty,
                    'payment_status' => $appt->payment_status,
                    'consultation_fee_pesos' => $appt->consultation_fee_pesos,
                ])
                ->all();

            // 2. Clinical Encounters & EHR Records
            $medicalRecords = $patient->medicalRecords()
                ->with('doctor.user')
                ->get()
                ->map(fn ($rec) => [
                    'id' => $rec->id,
                    'record_date' => $rec->record_date?->toDateString(),
                    'diagnosis' => $rec->diagnosis,
                    'clinical_notes' => $rec->clinical_notes,
                    'treatment_plan' => $rec->treatment_plan,
                    'vital_signs' => $rec->vital_signs,
                    'icd_10_codes' => $rec->icd_10_codes,
                    'attending_doctor' => $rec->doctor?->user?->name,
                ])
                ->all();

            // 3. Electronic Prescriptions
            $prescriptions = $patient->prescriptions()
                ->with(['doctor.user', 'items'])
                ->get()
                ->map(fn ($rx) => [
                    'id' => $rx->id,
                    'prescribing_doctor' => $rx->doctor?->user?->name,
                    'notes' => $rx->notes,
                    'is_dispensed' => $rx->is_dispensed,
                    'valid_until' => $rx->valid_until?->toDateString(),
                    'items' => $rx->items->map(fn ($item) => [
                        'medication_name' => $item->medication_name,
                        'dosage' => $item->dosage,
                        'frequency' => $item->frequency,
                        'instructions' => $item->instructions,
                        'refills_remaining' => $item->refills_remaining,
                    ])->all(),
                ])
                ->all();
        }

        // 4. Payments & Transactions
        $payments = $user->payments()
            ->with('appointment')
            ->get()
            ->map(fn ($pay) => [
                'id' => $pay->id,
                'appointment_id' => $pay->appointment_id,
                'amount_cents' => $pay->amount_cents,
                'amount_pesos' => $pay->amount_pesos,
                'currency' => $pay->currency,
                'gateway' => $pay->gateway,
                'payment_method' => $pay->payment_method,
                'status' => $pay->status,
                'refund_amount_pesos' => $pay->refund_amount_pesos,
                'refunded_at' => $pay->refunded_at?->toIso8601String(),
                'created_at' => $pay->created_at->toIso8601String(),
            ])
            ->all();

        // 5. Forensic Audit Trail Entries
        $auditLogs = $user->auditLogs()
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'action' => $log->action?->value ?? (string) $log->action,
                'entity_type' => $log->entity_type,
                'entity_id' => $log->entity_id,
                'ip_address' => $log->ip_address,
                'timestamp' => $log->created_at->toIso8601String(),
            ])
            ->all();

        return [
            'compliance_standard' => 'HIPAA / Data Privacy Act (DPA) Complete Health Record Export',
            'export_generated_at' => Carbon::now()->toIso8601String(),
            'patient_profile' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role?->value ?? (string) $user->role,
                'blood_type' => $patient?->blood_type,
                'allergies' => $patient?->allergies,
                'gender' => $patient?->gender,
                'date_of_birth' => $patient?->date_of_birth?->toDateString(),
                'emergency_contact_name' => $patient?->emergency_contact_name,
                'emergency_contact_phone' => $patient?->emergency_contact_phone,
            ],
            'appointments' => $appointments,
            'medical_records' => $medicalRecords,
            'prescriptions' => $prescriptions,
            'payments' => $payments,
            'audit_logs' => $auditLogs,
        ];
    }
}
