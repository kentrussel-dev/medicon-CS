<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('medicon:apply-retention-policy')->dailyAt('02:00');
Schedule::command('medicon:backup-db')->dailyAt('03:00');
