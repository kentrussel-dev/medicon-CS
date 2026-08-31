<?php

namespace App\Console\Commands;

use App\Services\DataRetentionService;
use Illuminate\Console\Command;

class ApplyDataRetentionPolicyCommand extends Command
{
    protected $signature = 'medicon:apply-retention-policy';
    protected $description = 'Purge soft-deleted clinical and audit records according to HIPAA/GDPR retention policies';

    public function handle(DataRetentionService $retentionService): int
    {
        $this->info('Starting clinical data retention policy execution...');

        $results = $retentionService->purgeExpiredSoftDeletes();

        $this->table(
            ['Metric', 'Count / Value'],
            [
                ['Appointments Purged', $results['appointments_purged'] ?? 0],
                ['Attachments Purged', $results['attachments_purged'] ?? 0],
                ['Medical Records Flagged for Cold Archive', $results['medical_records_archived_count'] ?? 0],
            ]
        );

        $this->info('Data retention execution completed successfully.');
        return Command::SUCCESS;
    }
}
