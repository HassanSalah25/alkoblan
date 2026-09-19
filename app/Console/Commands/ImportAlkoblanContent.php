<?php

namespace App\Console\Commands;

use App\Services\Import\AlkoblanImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportAlkoblanContent extends Command
{
    protected $signature = 'alkoblan:import
        {--dry-run : Crawl and report counts without writing to the database}
        {--fresh : Re-fetch and overwrite previously-imported records even if unchanged}
        {--update : Alias of the default behaviour — safe to re-run, only touches previously-imported records}
        {--skip-images : Skip downloading images/documents (faster, text-only run)}
        {--download-images : No-op flag kept for compatibility — images download by default unless --skip-images is passed}';

    protected $description = 'Crawl the public alkoblan.com.sa website and import its content (products, categories, branches, FAQs, blog, events, pages, media) into this project.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $downloadImages = ! $this->option('skip-images');
        $fresh = (bool) $this->option('fresh');

        $this->info($dryRun ? 'Running in DRY-RUN mode — no database writes will occur.' : 'Starting AL-KOBLAN content import...');

        $importer = (new AlkoblanImporter)->configure($dryRun, $downloadImages, $fresh);
        $report = $importer->importAll();

        $this->newLine();
        $this->table(['Item', 'Count'], [
            ['Pages', $report['pages']],
            ['Products', $report['products']],
            ['Branches', $report['branches']],
            ['FAQs', $report['faqs']],
            ['Testimonials', $report['testimonials']],
            ['Famous Clients', $report['famous_clients']],
            ['Blog Posts', $report['blog_posts']],
            ['Events', $report['events']],
            ['Hero Slides', $report['hero_slides']],
            ['Documents', $report['documents']],
            ['Images Downloaded', $report['images_downloaded']],
            ['Images Failed', $report['images_failed']],
            ['Failed Pages', count($report['failed_pages'])],
            ['Failed Assets (excl. images)', count(array_filter($report['failed_assets'], fn ($a) => ! str_contains($a['reason'], 'HTTP')))],
        ]);

        if (! $dryRun) {
            Storage::disk('local')->put(
                'import-reports/migration-report.json',
                json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
            Storage::disk('local')->put(
                'import-reports/failed-pages.json',
                json_encode($report['failed_pages'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
            Storage::disk('local')->put(
                'import-reports/failed-assets.json',
                json_encode($report['failed_assets'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
            $this->info('Reports written to storage/app/import-reports/.');
        }

        if (! empty($report['failed_pages'])) {
            $this->warn(count($report['failed_pages']).' page(s) failed to fetch — see failed-pages.json.');
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
