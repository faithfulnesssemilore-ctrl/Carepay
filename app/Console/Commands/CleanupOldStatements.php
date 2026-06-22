<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOldStatements extends Command
{
    protected $signature = 'statements:cleanup';

    protected $description = 'Delete statements older than 30 days';

    public function handle()
    {
        $files = Storage::files('statements');
        $deleted = 0;

        foreach ($files as $file) {
            $fileTime = Storage::lastModified($file);

            // Delete if older than 30 days
            if ($fileTime < now()->subDays(30)->timestamp) {
                Storage::delete($file);
                $deleted++;
            }
        }

        $this->info("Deleted {$deleted} old statement files.");

        return Command::SUCCESS;
    }
}
