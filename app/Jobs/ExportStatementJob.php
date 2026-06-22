<?php

namespace App\Jobs;

use App\Exports\StatementExport;
use App\Mail\StatementFailed;
use App\Mail\StatementReady;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ExportStatementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour timeout for long-running exports

    public function __construct(
        public int $userId,
        public string $startDate,
        public string $endDate
    ) {}

    public function handle()
    {
        $user = User::find($this->userId);

        if (! $user) {
            Log::error("User {$this->userId} not found for statement export");

            return;
        }

        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();
        $downloadFileName = sprintf('%s-%s-%s-%s.xlsx', $user->id, $startDate->format('Ymd'), $endDate->format('Ymd'), Str::random(8));
        $fileName = "statements/{$downloadFileName}";

        try {
            (new StatementExport($user->id, $startDate->toDateString(), $endDate->toDateString()))
                ->store($fileName, 'local');

            Log::info("Statement exported successfully for user {$user->id}");

            Mail::to($user)
                ->queue(new StatementReady(
                    $user,
                    $downloadFileName,
                    route('statement.download', ['file' => $downloadFileName]),
                    $startDate,
                    $endDate
                ));
        } catch (\Throwable $e) {
            Log::error("SOA export failed for user {$user->id}: {$e->getMessage()}");

            try {
                Mail::to($user)
                    ->queue(new StatementFailed(
                        $user,
                        $startDate,
                        $endDate,
                        $e->getMessage()
                    ));
            } catch (\Throwable $mailException) {
                Log::error("Statement failed email could not be queued: {$mailException->getMessage()}");
            }
        }
    }
}
