<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Attachment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Support\Facades\Log;

class DataRetentionService
{
    protected int $softDeletedRetentionDays;
    protected int $auditLogRetentionDays;

    public function __construct()
    {
        $this->softDeletedRetentionDays = (int) config('services.retention.soft_deleted_days', 365);
        $this->auditLogRetentionDays = (int) config('services.retention.audit_log_days', 2555);
    }

    public function purgeExpiredSoftDeletes(): array
    {
        $cutoffDate = now()->subDays($this->softDeletedRetentionDays);
        $results = [];

        // Purge expired appointments
        $purgedAppointments = Appointment::onlyTrashed()
            ->where('deleted_at', '<=', $cutoffDate)
            ->forceDelete();
        $results['appointments_purged'] = $purgedAppointments;

        // Purge expired attachments
        $purgedAttachments = Attachment::onlyTrashed()
            ->where('deleted_at', '<=', $cutoffDate)
            ->forceDelete();
        $results['attachments_purged'] = $purgedAttachments;

        // Medical records and prescriptions retention (HIPAA requires strict preservation of clinical records)
        $flaggedRecords = MedicalRecord::onlyTrashed()
            ->where('deleted_at', '<=', $cutoffDate)
            ->count();
        $results['medical_records_archived_count'] = $flaggedRecords;

        Log::channel('audit')->info('DATA_RETENTION_PURGE_EXECUTED', [
            'cutoff_date' => $cutoffDate->toDateString(),
            'results' => $results,
            'timestamp' => now()->toIso8601String(),
        ]);

        return $results;
    }
}
