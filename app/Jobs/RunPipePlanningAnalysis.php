<?php

namespace App\Jobs;

use App\Models\PipePlanningAnalysis;
use App\Services\PipePlanner\PipePlanningAnalyzer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunPipePlanningAnalysis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Provider retries are bounded inside OpenRouterClient; the job itself never re-runs (avoids double billing).
    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(public PipePlanningAnalysis $analysis) {}

    public function handle(PipePlanningAnalyzer $analyzer): void
    {
        if ($this->analysis->status !== 'queued') {
            return;
        }
        $analyzer->run($this->analysis);
    }

    public function failed(?\Throwable $e): void
    {
        $this->analysis->refresh();
        if (in_array($this->analysis->status, PipePlanningAnalysis::ACTIVE_STATUSES, true)) {
            $this->analysis->update(['status' => 'failed', 'stage' => null, 'failure_category' => 'job_failed', 'completed_at' => now()]);
            $this->analysis->project()->update(['analysis_status' => 'failed']);
        }
    }
}
