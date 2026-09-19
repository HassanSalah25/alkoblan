<?php

namespace App\Services\Import;

use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin HTTP + HTML-extraction helper for the alkoblan.com.sa content
 * migration. Deliberately dependency-free (no DOM-crawler package) since
 * the source site's markup is simple enough for targeted regex extraction,
 * validated against the live site during development of this importer.
 *
 * Each instance keeps its own cookie jar so it can be pinned to a single
 * language for its lifetime: call switchLanguage() once (the site stores
 * the locale in the session) and every subsequent get()/post() on that
 * instance keeps returning that language. Use a separate instance per
 * language rather than switching back and forth on one instance.
 */
class AlkoblanClient
{
    public const BASE = 'https://www.alkoblan.com.sa';

    protected int $timeout;

    protected int $delayMs;

    protected CookieJar $cookieJar;

    public array $failedPages = [];

    public function __construct(int $timeout = 20, int $delayMs = 150)
    {
        $this->timeout = $timeout;
        $this->delayMs = $delayMs;
        $this->cookieJar = new CookieJar;
    }

    /** Switches this client's session to the given site locale ("arabic"|"english"). */
    public function switchLanguage(string $locale): void
    {
        $this->get('lang/'.$locale);
    }

    public function get(string $path): ?string
    {
        $url = str_starts_with($path, 'http') ? $path : self::BASE.'/'.ltrim($path, '/');

        try {
            $response = Http::withOptions(['cookies' => $this->cookieJar])->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; AlkoblanContentImporter/1.0; +https://alkoblan.com.sa)',
            ])->timeout($this->timeout)->get($url);

            usleep($this->delayMs * 1000);

            if (! $response->successful()) {
                $this->failedPages[] = ['url' => $url, 'reason' => 'HTTP '.$response->status()];

                return null;
            }

            return $response->body();
        } catch (\Throwable $e) {
            Log::warning("Alkoblan import: failed to fetch {$url}: ".$e->getMessage());
            $this->failedPages[] = ['url' => $url, 'reason' => $e->getMessage()];

            return null;
        }
    }

    public function post(string $path, array $data): ?string
    {
        $url = str_starts_with($path, 'http') ? $path : self::BASE.'/'.ltrim($path, '/');

        try {
            $response = Http::withOptions(['cookies' => $this->cookieJar])->asForm()->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; AlkoblanContentImporter/1.0)',
                'X-Requested-With' => 'XMLHttpRequest',
            ])->timeout($this->timeout)->post($url, $data);

            usleep($this->delayMs * 1000);

            if (! $response->successful()) {
                $this->failedPages[] = ['url' => $url, 'reason' => 'HTTP '.$response->status()];

                return null;
            }

            return $response->body();
        } catch (\Throwable $e) {
            $this->failedPages[] = ['url' => $url, 'reason' => $e->getMessage()];

            return null;
        }
    }

    // -----------------------------------------------------------------
    // Extraction helpers
    // -----------------------------------------------------------------

    /** Raw HTML of the <main>...</main> region of a page, with the hero title banner removed. */
    public static function mainHtml(string $html): ?string
    {
        if (! preg_match('#<main[^>]*>(.*)</main>#is', $html, $m)) {
            return null;
        }

        return preg_replace('#<section class="hero-block">.*?</section>#is', '', $m[1], 1);
    }

    /** Plain-text lines from the <main>...</main> region of a page (tags stripped, blanks removed). */
    public static function mainTextLines(string $html): array
    {
        $body = $html;
        if (preg_match('#<main[^>]*>(.*?)</main>#is', $html, $m)) {
            $body = $m[1];
        }
        $body = preg_replace('#<script.*?</script>#is', '', $body);
        $body = preg_replace('#<style.*?</style>#is', '', $body);
        $text = preg_replace('#<[^>]+>#', "\n", $body);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5);
        $lines = array_map('trim', explode("\n", $text));

        return array_values(array_filter($lines, fn ($l) => $l !== ''));
    }

    /** All unique image URLs anywhere in the page matching a path fragment (e.g. "uploads/products"). */
    public static function imageUrls(string $html, string $pathContains): array
    {
        preg_match_all('#src="([^"]*'.preg_quote($pathContains, '#').'[^"]*)"#i', $html, $m);

        return array_values(array_unique($m[1] ?? []));
    }

    /** All href values anywhere on the page matching a regex fragment. */
    public static function hrefs(string $html, string $pattern): array
    {
        preg_match_all('#href="('.$pattern.')"#i', $html, $m);

        return array_values(array_unique($m[1] ?? []));
    }
}
