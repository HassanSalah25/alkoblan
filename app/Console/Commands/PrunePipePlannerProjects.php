<?php

namespace App\Console\Commands;

use App\Models\PipePlanningProject;
use App\Services\PipePlanner\PlannerSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PrunePipePlannerProjects extends Command
{
    protected $signature = 'pipe-planner:prune {--dry-run : List what would be deleted without deleting}';

    protected $description = 'Delete AI Pipe Planner projects (and their private photos) past the retention period that never became quotation requests.';

    public function handle(PlannerSettings $settings): int
    {
        $days = max(7, $settings->get('retention_days'));
        $query = PipePlanningProject::where('updated_at', '<', now()->subDays($days))->doesntHave('quoteRequests');

        $count = 0;
        $query->chunkById(100, function ($projects) use (&$count) {
            foreach ($projects as $project) {
                $count++;
                if ($this->option('dry-run')) {
                    $this->line($project->reference);

                    continue;
                }
                Storage::disk(config('pipe_planner.disk'))->deleteDirectory('pipe-planner/'.$project->id);
                $project->delete();
            }
        });

        $this->info(($this->option('dry-run') ? 'Would delete' : 'Deleted')." {$count} project(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
