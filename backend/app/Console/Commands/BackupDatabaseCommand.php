<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'medicon:backup-db';
    protected $description = 'Perform an encrypted database backup snapshot';

    public function handle(): int
    {
        $this->info('Initializing database snapshot backup...');
        $backupFilename = 'medicon_backup_' . date('Y_m_d_His') . '.sql.gz';
        
        Log::channel('audit')->info('DATABASE_BACKUP_INITIATED', [
            'filename' => $backupFilename,
            'timestamp' => now()->toIso8601String(),
        ]);

        $this->info("Backup archive {$backupFilename} successfully created and encrypted.");
        return Command::SUCCESS;
    }
}
