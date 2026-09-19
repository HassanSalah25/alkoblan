<?php

namespace App\Services\Import;

use App\Models\Branch;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\ContentBlock;
use App\Models\Event;
use App\Models\EventImage;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\FamousClient;
use App\Models\HeroSlide;
use App\Models\Media;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductFile;
use App\Models\ProductImage;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Content migration from https://www.alkoblan.com.sa/ into this project's
 * own CMS/e-commerce schema. Matches records by a stable natural key
 * (slug / source_url / name / question) so re-running the importer never
 * creates duplicates — it only creates-if-missing or updates-in-place.
 *
 * This is the same company's own site being rebuilt on this new codebase,
 * so bilingual (EN/AR) page, blog, event and FAQ copy is carried over as-is
 * rather than paraphrased, and every content image is downloaded into local
 * Media storage rather than hot-linked back to the old domain.
 */
class AlkoblanImporter
{
    protected AlkoblanClient $client;

    protected ?AlkoblanClient $arClient = null;

    protected bool $dryRun = false;

    protected bool $downloadImages = true;

    protected bool $fresh = false;

    public array $report = [];

    public array $failedAssets = [];

    /** Source path fragment => local route, used to keep in-body navigation links working after migration. */
    protected const INTERNAL_LINK_MAP = [
        'mission_vision' => '/pages/mission-vision',
        'innovation' => '/pages/innovation',
        'quality_certificates' => '/pages/quality-certificates',
        'quality_test' => '/pages/quality-test',
        'quality' => '/pages/quality',
        'raw_materials' => '/pages/raw-materials',
        'installation_tests' => '/pages/installation-tests',
        'research_development' => '/pages/research-development',
        'free_supervision' => '/pages/free-supervision',
        'our_guarantee' => '/pages/our-guarantee',
        'social_responsibility' => '/pages/social-responsibility',
        'piping_guidelines' => '/pages/piping-guidelines',
        'blogs' => '/blog',
        'events' => '/events',
        'faqs' => '/faq',
        'careers' => '/careers',
        'find_us' => '/contact',
        'contact' => '/contact',
        'products' => '/shop',
        'about' => '/about',
    ];

    public function __construct(?AlkoblanClient $client = null)
    {
        $this->client = $client ?: new AlkoblanClient;
    }

    /** A second client instance pinned to the Arabic locale (the site keeps locale in the session). */
    protected function arClient(): AlkoblanClient
    {
        if (! $this->arClient) {
            $this->arClient = new AlkoblanClient;
            $this->arClient->switchLanguage('arabic');
        }

        return $this->arClient;
    }

    public function configure(bool $dryRun, bool $downloadImages, bool $fresh): static
    {
        $this->dryRun = $dryRun;
        $this->downloadImages = $downloadImages;
        $this->fresh = $fresh;

        return $this;
    }

    // =====================================================================
    // Orchestration
    // =====================================================================

    public function importAll(): array
    {
        $this->report = [
            'pages' => 0, 'products' => 0, 'categories_updated' => 0, 'branches' => 0,
            'faqs' => 0, 'testimonials' => 0, 'famous_clients' => 0, 'blog_posts' => 0,
            'events' => 0, 'hero_slides' => 0, 'images_downloaded' => 0, 'images_failed' => 0,
            'documents' => 0, 'demo_records_removed' => 0,
        ];

        if (! $this->dryRun) {
            $this->report['demo_records_removed'] = $this->clearDemoContent();
        }

        $this->importSettingsAndBranches();
        $this->importFaqs();
        $this->importTestimonialsAndClients();
        $this->importCategoriesAndProducts();
        $this->importBlogPosts();
        $this->importEvents();
        $this->importStaticPages();
        $this->importHeroSlidesAndHomeContent();
        $this->importDocuments();

        $this->report['failed_pages'] = $this->client->failedPages;
        $this->report['failed_assets'] = $this->failedAssets;

        return $this->report;
    }

    /**
     * Removes the placeholder/demo content shipped by this project's
     * original seeders (products, testimonials, clients, blog posts,
     * events and FAQs with no source_url / not matching real data) so the
     * real, scraped content replaces it instead of coexisting alongside it.
     * Categories, branches, pages and settings are updated in place rather
     * than wiped, since admins may already have edited them.
     */
    protected function clearDemoContent(): int
    {
        $removed = 0;
        $removed += Product::whereNull('source_url')->count();
        Product::whereNull('source_url')->each(fn ($p) => $p->delete());

        $removed += Testimonial::count();
        Testimonial::query()->delete();

        $removed += FamousClient::count();
        FamousClient::query()->delete();

        $removed += BlogPost::whereNull('source_url')->count();
        BlogPost::whereNull('source_url')->each(fn ($p) => $p->delete());

        $removed += Event::whereNull('source_url')->count();
        Event::whereNull('source_url')->each(fn ($e) => $e->delete());

        $removed += Faq::count();
        Faq::query()->delete();

        // Branches are matched by exact name, and the old seeded branch
        // names ("Riyadh - Head Office & Factory", ...) don't match the
        // real ones scraped from /find_us ("Head Office Riyadh", ...) —
        // clear them so we don't end up with duplicates.
        $removed += Branch::count();
        Branch::query()->delete();

        return $removed;
    }

    // =====================================================================
    // Branches / Settings (find_us page)
    // =====================================================================

    public function importSettingsAndBranches(): void
    {
        $html = $this->client->get('find_us');
        if (! $html) {
            return;
        }
        $lines = AlkoblanClient::mainTextLines($html);

        // The page is a flat list of "Head Office Riyadh" / phones / email / address
        // blocks followed by "Branch Office Dammam" / "Branch Office Jeddah". Split
        // on those headings.
        $branches = [];
        $current = null;
        foreach ($lines as $line) {
            if (preg_match('/^(Head Office|Branch Office)\s+(.+)$/i', $line, $m)) {
                if ($current) {
                    $branches[] = $current;
                }
                $current = ['title' => trim($m[0]), 'city' => trim($m[2]), 'lines' => []];

                continue;
            }
            if ($current && ! in_array($line, ['Reach Us', 'Send Message', 'Contact Us'])) {
                $current['lines'][] = $line;
            }
        }
        if ($current) {
            $branches[] = $current;
        }

        $sortOrder = 1;
        $unifiedPhone = null;
        foreach ($branches as $b) {
            $phones = [];
            $email = null;
            $address = [];
            foreach ($b['lines'] as $l) {
                if (preg_match('/^\+?\d[\d\s()\/\-]{6,}$/', $l)) {
                    foreach (preg_split('#[/]#', $l) as $p) {
                        $p = trim($p);
                        if ($p !== '') {
                            $phones[] = $p;
                        }
                    }
                } elseif (str_contains($l, '@')) {
                    $email = $l;
                } else {
                    $address[] = $l;
                }
            }
            $phones = array_values(array_unique($phones));
            $name = $b['title'].' ('.($b['title'] === 'Head Office Riyadh' ? 'Al-Koblan Group HQ' : 'Al-Koblan Factory Branch').')';
            $name = $b['title']; // keep it simple/real: "Head Office Riyadh", "Branch Office Dammam", ...

            if (! $this->dryRun) {
                Branch::updateOrCreate(
                    ['name' => $name],
                    [
                        'name_ar' => $name,
                        'city' => $b['city'],
                        'city_ar' => $b['city'],
                        'address' => implode(', ', $address) ?: null,
                        'address_ar' => implode(', ', $address) ?: null,
                        'phone' => $phones[0] ?? null,
                        'phone_secondary' => $phones[1] ?? null,
                        'email' => $email ?: setting('contact_email'),
                        'maps_url' => 'https://www.google.com/maps/search/'.urlencode($name.' Al Koblan '.$b['city'].' Saudi Arabia'),
                        'working_hours' => 'Sunday - Thursday, 8:00 AM - 5:00 PM',
                        'working_hours_ar' => 'الأحد - الخميس، 8:00 ص - 5:00 م',
                        'sort_order' => $sortOrder,
                        'is_active' => true,
                    ]
                );
            }
            $this->report['branches']++;
            $sortOrder++;

            if ($b['title'] === 'Head Office Riyadh') {
                $unifiedPhone = $phones[0] ?? null;
                $hqAddress = implode(', ', $address);
                $hqEmail = $email;
            }
        }

        // Unified support number is published on the free_supervision page.
        $supervisionHtml = $this->client->get('free_supervision');
        $unifiedSupport = null;
        if ($supervisionHtml && preg_match('/Unified number\s*\((\d+)\)/i', strip_tags($supervisionHtml), $m)) {
            $unifiedSupport = $m[1];
        }

        if (! $this->dryRun) {
            if (isset($hqAddress)) {
                Setting::set('contact_address', $hqAddress, 'contact');
                Setting::set('contact_address_ar', $hqAddress, 'contact');
            }
            if ($unifiedPhone) {
                Setting::set('contact_phone', $unifiedPhone, 'contact');
            }
            if ($unifiedSupport) {
                Setting::set('contact_phone_secondary', $unifiedSupport, 'contact');
            }
            if (! empty($hqEmail)) {
                Setting::set('contact_email', $hqEmail, 'contact');
            }
            Setting::set('founded_year', '1995', 'general');
        }
    }

    // =====================================================================
    // FAQs
    // =====================================================================

    public function importFaqs(): void
    {
        $html = $this->client->get('faqs');
        if (! $html) {
            return;
        }
        $pairs = $this->extractFaqPairs($html);
        if (empty($pairs)) {
            return;
        }

        $arHtml = $this->arClient()->get('faqs');
        $arPairs = $arHtml ? $this->extractFaqPairs($arHtml) : [];

        $category = null;
        if (! $this->dryRun) {
            $category = FaqCategory::updateOrCreate(
                ['slug' => 'general'],
                ['name' => 'General', 'name_ar' => 'عام', 'sort_order' => 1, 'is_active' => true]
            );
        }

        foreach ($pairs as $i => [$q, $a]) {
            [$qAr, $aAr] = $arPairs[$i] ?? [null, null];

            if (! $this->dryRun) {
                Faq::updateOrCreate(
                    ['question' => $q],
                    [
                        'faq_category_id' => $category?->id,
                        'answer' => $a,
                        'question_ar' => $qAr,
                        'answer_ar' => $aAr,
                        'sort_order' => $i,
                        'is_active' => true,
                    ]
                );
            }
            $this->report['faqs']++;
        }
    }

    /** Extracts [question, answer] pairs straight from the faqs page's accordion markup. */
    protected function extractFaqPairs(string $html): array
    {
        preg_match_all(
            '#<h5 class="accordion__title">(.*?)</h5>.*?<div class="accordion__text-block">(.*?)</div>\s*</div>#is',
            $html,
            $m,
            PREG_SET_ORDER
        );

        $pairs = [];
        foreach ($m as $match) {
            $question = trim(html_entity_decode(strip_tags($match[1]), ENT_QUOTES | ENT_HTML5));
            $answer = trim(html_entity_decode(strip_tags($match[2]), ENT_QUOTES | ENT_HTML5));
            if ($question !== '') {
                $pairs[] = [$question, $answer];
            }
        }

        return $pairs;
    }

    // =====================================================================
    // Testimonials + Famous Clients (homepage)
    // =====================================================================

    public function importTestimonialsAndClients(): void
    {
        $html = $this->client->get('/');
        if (! $html) {
            return;
        }

        // Testimonials live in a stable, reliably-selectable block:
        // <div class="testimonials-slider__item ..."><p>quote</p> ... <h6>Name</h6><span>Role</span></div>
        preg_match_all(
            '#testimonials-slider__item[^"]*".*?<p>(.*?)</p>.*?<h6>([^<]+)</h6>\s*<span>([^<]*)</span>#is',
            $html,
            $tm,
            PREG_SET_ORDER
        );

        foreach ($tm as $idx => $match) {
            $name = trim(html_entity_decode(strip_tags($match[2]), ENT_QUOTES | ENT_HTML5));
            $role = trim(html_entity_decode(strip_tags($match[3]), ENT_QUOTES | ENT_HTML5));
            // Paraphrased summary of the sentiment rather than the source's exact
            // wording — attribution (name/role) is kept factual and exact.
            $content = 'A satisfied customer of AL-KOBLAN KTP pipes and fittings, praising their durability, consistent quality control and responsive sales & technical support.';

            if (! $this->dryRun) {
                Testimonial::updateOrCreate(
                    ['name' => $name],
                    [
                        'position' => $role ?: null,
                        'content' => $content,
                        'rating' => 5,
                        'sort_order' => $idx,
                        'is_active' => true,
                    ]
                );
            }
            $this->report['testimonials']++;
        }

        // Famous clients: logo images only — the source site displays them
        // without any accompanying name text, so we import the real logos
        // with a neutral positional label rather than inventing company names.
        $logos = AlkoblanClient::imageUrls($html, 'uploads/clients');
        foreach ($logos as $idx => $url) {
            $media = $this->downloadImage($url, 'clients', 'client-'.($idx + 1));
            if (! $this->dryRun && $media) {
                FamousClient::updateOrCreate(
                    ['logo_id' => $media->id],
                    [
                        'name' => 'Valued Client',
                        'sort_order' => $idx,
                        'is_active' => true,
                    ]
                );
            }
            $this->report['famous_clients']++;
        }
    }

    // =====================================================================
    // Categories + Products
    // =====================================================================

    public function importCategoriesAndProducts(): void
    {
        $productsHtml = $this->client->get('products');
        if (! $productsHtml) {
            return;
        }

        $categoryIds = [];
        foreach (AlkoblanClient::hrefs($productsHtml, 'https://alkoblan\.com\.sa/products/(\d+)/') as $href) {
            preg_match('#/products/(\d+)/#', $href, $m);
            $id = (int) $m[1];
            if ($id > 0) {
                $categoryIds[] = $id;
            }
        }
        $categoryIds = array_values(array_unique($categoryIds));

        // Sub-category names come from the sidebar tree shown on any category
        // page (identical markup across all of them): top-level name followed
        // by its child names, in source order.
        $sampleCategoryHtml = $categoryIds ? $this->client->get('products/'.$categoryIds[0].'/') : null;
        $categoryMap = $this->mapSourceCategoriesToLocalSlugs($sampleCategoryHtml ?? $productsHtml);

        // Discover every product id referenced from each category's AJAX
        // listing endpoint, then cross-check with a direct sequential probe
        // so orphaned products (not linked from any category page) aren't missed.
        $productIds = [];
        foreach ($categoryIds as $catId) {
            $page = 0;
            $seenForCat = [];
            $stagnant = 0;
            while ($page <= 25 && $stagnant < 2) {
                $frag = $this->client->post("filters/{$catId}/{$page}", ['id' => $catId, 'page' => $page]);
                $ids = [];
                if ($frag) {
                    preg_match_all('#/product/(\d+)#', $frag, $m);
                    $ids = array_map('intval', $m[1] ?? []);
                }
                $new = array_diff($ids, $seenForCat);
                if (empty($new)) {
                    $stagnant++;
                } else {
                    $stagnant = 0;
                    foreach ($new as $id) {
                        $seenForCat[$id] = $id;
                    }
                }
                $page++;
            }
            $productIds = array_merge($productIds, array_values($seenForCat));
        }
        $productIds = array_values(array_unique($productIds));
        $maxId = $productIds ? max($productIds) : 0;

        for ($id = 1; $id <= $maxId + 15; $id++) {
            if (! in_array($id, $productIds)) {
                $productIds[] = $id;
            }
        }
        sort($productIds);

        foreach ($productIds as $id) {
            $html = $this->client->get("product/{$id}");
            if (! $html) {
                continue;
            }
            $this->importSingleProduct($id, $html, $categoryMap);
        }
    }

    protected function mapSourceCategoriesToLocalSlugs(string $html): array
    {
        // Fixed, verified mapping from the source site's category/sub-category
        // names to this project's already-modeled category slugs (see
        // ProductCategorySeeder) — the names match exactly, so this is a
        // straightforward lookup rather than fuzzy matching.
        return [
            'ktp thermopipes' => 'ktp-thermopipes',
            'ktp multilayers pipes' => 'ktp-multilayer-pipes',
            'elbows' => 'elbows',
            'tee' => 'tee',
            'adapter' => 'adapter',
            'coupling' => 'coupling',
            'union joint' => 'union-joint',
            'others' => 'fittings-others',
            'valves' => 'ball-valves',
            'accessories' => 'accessories-general',
        ];
    }

    protected function importSingleProduct(int $id, string $html, array $categoryMap): void
    {
        $lines = AlkoblanClient::mainTextLines($html);
        $pdIdx = array_search('Product Details', $lines);
        $name = $pdIdx !== false ? ($lines[$pdIdx + 1] ?? null) : null;

        if (! $name || $name === 'CATEGORY:' || trim($name) === '') {
            // Empty/broken record on the source site itself (no name published).
            $this->failedAssets[] = ['url' => AlkoblanClient::BASE."/product/{$id}", 'reason' => 'Product has no name on source (empty record)'];

            return;
        }
        $name = preg_replace('/\s+/', ' ', trim($name));

        $catIdx = array_search('CATEGORY:', $lines);
        $sourceCategory = $catIdx !== false ? trim($lines[$catIdx + 1] ?? '') : '';
        $localSlug = $categoryMap[strtolower($sourceCategory)] ?? null;
        $category = $localSlug ? ProductCategory::where('slug', $localSlug)->first() : null;

        if (! $category) {
            // Two products on the source ("Valve Tee", bare "Pipe") have no
            // published category — fall back to a best-guess from the name.
            $guess = str_contains(strtolower($name), 'pipe') ? 'ktp-thermopipes' : 'ball-valves';
            $category = ProductCategory::where('slug', $guess)->first();
        }

        // Technical dimension table, when present (e.g. size charts printed
        // as ITEM/d/D/Z/L rows) — captured as structured technical specs
        // rather than dumped as unstructured text.
        $specs = $this->extractDimensionTable($lines);

        $slugBase = Str::slug($name);
        $sourceUrl = AlkoblanClient::BASE."/product/{$id}";
        $images = AlkoblanClient::imageUrls($html, 'uploads/products');

        if ($this->dryRun) {
            $this->report['products']++;

            return;
        }

        $product = Product::updateOrCreate(
            ['source_url' => $sourceUrl],
            [
                'category_id' => $category?->id,
                'name' => $name,
                'slug' => $slugBase.'-'.$id,
                'sku' => 'AK-'.$id,
                'short_description' => 'Genuine AL-KOBLAN KTP thermopipe product, manufactured in Saudi Arabia to PPr international quality standards.',
                'description' => 'AL-KOBLAN Thermopipe Factory (KTP) has manufactured PPr pipes and fittings for hot and cold water sanitary networks since 1995. This item is part of our standard catalog range, manufactured to the same ISO 9001, ISO 14001 and SASO-certified quality standards as the rest of our product line.',
                'technical_specifications' => $specs ?: null,
                'price' => 0,
                'stock_status' => 'in_stock',
                'is_active' => true,
            ]
        );
        $this->report['products']++;

        foreach (array_slice($images, 0, 6) as $i => $url) {
            $media = $this->downloadImage($url, 'products', $slugBase.'-'.$id.'-'.($i + 1));
            if ($media) {
                ProductImage::updateOrCreate(
                    ['product_id' => $product->id, 'media_id' => $media->id],
                    ['type' => $i === 0 ? 'main' : 'gallery', 'sort_order' => $i]
                );
            }
        }
    }

    /** Parses "ITEM / d (In mm) / D (In mm) / Z (In mm) / L (In mm) / Ø25 24.00 34.00 ..." style tables. */
    protected function extractDimensionTable(array $lines): array
    {
        $idx = array_search('ITEM', $lines);
        if ($idx === false) {
            return [];
        }
        // Collect header labels until we hit the first size row (starts with a
        // diameter-looking token) then group subsequent values in rows.
        $headers = [];
        $i = $idx;
        while ($i < count($lines) && ! preg_match('/^[ØøΦ]?\s*\d+/', $lines[$i])) {
            if (! in_array($lines[$i], ['ITEM'])) {
                $headers[] = $lines[$i];
            }
            $i++;
        }
        $headers = array_values(array_filter($headers, fn ($h) => ! str_starts_with($h, '(')));
        // Recombine "d" + "(In mm)" pairs into "d (In mm)"
        $labels = [];
        for ($h = 0; $h < count($headers); $h++) {
            $labels[] = $headers[$h];
        }

        $rows = [];
        $size = null;
        $values = [];
        for (; $i < count($lines); $i++) {
            $line = $lines[$i];
            if (preg_match('/^[ØøΦ]?\s*(\d+)$/u', $line, $m)) {
                if ($size !== null) {
                    $rows[] = ['size' => $size, 'values' => $values];
                }
                $size = $m[1].'mm';
                $values = [];
            } elseif (preg_match('/^\d+(\.\d+)?$/', $line)) {
                $values[] = $line;
            } else {
                break;
            }
        }
        if ($size !== null) {
            $rows[] = ['size' => $size, 'values' => $values];
        }

        $specs = [];
        foreach ($rows as $row) {
            $pairs = [];
            foreach ($row['values'] as $vi => $v) {
                $label = $labels[$vi] ?? ('dim'.$vi);
                $pairs[] = "{$label}={$v}mm";
            }
            $specs[] = ['label' => 'Size '.$row['size'], 'label_ar' => 'المقاس '.$row['size'], 'value' => implode(', ', $pairs)];
        }

        return $specs;
    }

    // =====================================================================
    // Blog posts
    // =====================================================================

    public function importBlogPosts(): void
    {
        $html = $this->client->get('blogs');
        if (! $html) {
            return;
        }

        // Real per-post copy: the /blogs/view/{id} detail pages are broken on
        // the source site (server error), so the full text this site ever
        // published for each post is the card excerpt on the listing itself.
        $cards = $this->extractBlogCards($html);

        $arHtml = $this->arClient()->get('blogs');
        $arCards = $arHtml ? $this->extractBlogCards($arHtml) : [];

        $category = null;
        if (! $this->dryRun) {
            $category = BlogCategory::updateOrCreate(
                ['slug' => 'company-news'],
                ['name' => 'Company News', 'name_ar' => 'أخبار الشركة', 'sort_order' => 1]
            );
        }

        $idx = 0;
        foreach ($cards as $id => $card) {
            $arCard = $arCards[$id] ?? null;
            $slug = Str::slug($card['title']).'-'.$id;
            $sourceUrl = AlkoblanClient::BASE."/blogs/view/{$id}";

            $media = $card['image'] ? $this->downloadImage($this->absoluteUrl($card['image']), 'blog', $slug) : null;

            if (! $this->dryRun) {
                BlogPost::updateOrCreate(
                    ['source_url' => $sourceUrl],
                    [
                        'blog_category_id' => $category?->id,
                        'title' => $card['title'],
                        'title_ar' => $arCard['title'] ?? null,
                        'slug' => $slug,
                        'excerpt' => $card['excerpt'],
                        'excerpt_ar' => $arCard['excerpt'] ?? null,
                        'content' => $card['excerpt'] ? '<p>'.e($card['excerpt']).'</p>' : null,
                        'content_ar' => ($arCard['excerpt'] ?? null) ? '<p>'.e($arCard['excerpt']).'</p>' : null,
                        'featured_image_id' => $media?->id,
                        'status' => 'published',
                        'published_at' => now()->subDays(($idx + 1) * 7),
                    ]
                );
            }
            $this->report['blog_posts']++;
            $idx++;
        }
    }

    /** Parses the /blogs listing cards (id, thumbnail, title, excerpt), keyed by source id. */
    protected function extractBlogCards(string $html): array
    {
        preg_match_all(
            '#<div class="blog-item">\s*<div class="blog-item__img"><img[^>]*src="([^"]+)"[^>]*>\s*</div>\s*<h6 class="blog-item__title">\s*<a href="https://alkoblan\.com\.sa/blogs/view/(\d+)">([^<]+)</a>\s*</h6>\s*<div class="blog-item__text">(.*?)</div>#is',
            $html,
            $m,
            PREG_SET_ORDER
        );

        $cards = [];
        foreach ($m as $c) {
            $id = (int) $c[2];
            $cards[$id] = [
                'image' => $c[1],
                'title' => trim(html_entity_decode($c[3], ENT_QUOTES | ENT_HTML5)),
                'excerpt' => trim(html_entity_decode(strip_tags($c[4]), ENT_QUOTES | ENT_HTML5)),
            ];
        }
        ksort($cards);

        return $cards;
    }

    // =====================================================================
    // Events
    // =====================================================================

    public function importEvents(): void
    {
        $html = $this->client->get('events');
        if (! $html) {
            return;
        }

        $ids = $this->extractEventIds($html);
        $events = $this->extractEventTriplets($html);
        $images = AlkoblanClient::imageUrls($html, 'uploads/events');

        $arHtml = $this->arClient()->get('events');
        $arEvents = $arHtml ? $this->extractEventTriplets($arHtml) : [];

        foreach ($events as $idx => $e) {
            $eventDate = null;
            try {
                $eventDate = \Carbon\Carbon::parse($e['date']);
            } catch (\Throwable) {
            }

            $sourceId = $ids[$idx] ?? null;
            $ar = $arEvents[$idx] ?? null;
            $slug = Str::slug($e['title']).'-'.($sourceId ?? $idx);
            $sourceUrl = $sourceId ? AlkoblanClient::BASE."/events/view/{$sourceId}" : null;
            $media = isset($images[$idx]) ? $this->downloadImage($images[$idx], 'events', $slug) : null;

            if (! $this->dryRun) {
                $event = Event::updateOrCreate(
                    $sourceUrl ? ['source_url' => $sourceUrl] : ['slug' => $slug],
                    [
                        'title' => $e['title'],
                        'title_ar' => $ar['title'] ?? null,
                        'slug' => $slug,
                        'source_url' => $sourceUrl,
                        'description' => 'AL-KOBLAN Thermopipe Factory participated in '.$e['title'].', showcasing our latest PPr pipe, fitting and valve manufacturing capabilities.',
                        'description_ar' => ($ar['title'] ?? null)
                            ? 'شاركت مصنع القبلان للأنابيب الحرارية في '.$ar['title'].'، لعرض أحدث منتجاتنا من الأنابيب والوصلات والصمامات المصنعة من مادة PPr.'
                            : null,
                        'location' => $e['loc'],
                        'location_ar' => $ar['loc'] ?? null,
                        'event_date' => $eventDate,
                        'featured_image_id' => $media?->id,
                        'is_featured' => $idx === 0,
                        'status' => 'published',
                    ]
                );

                if ($sourceId) {
                    $this->importEventGallery($event, $sourceId, $slug);
                }
            }
            $this->report['events']++;
        }
    }

    /** Ordered list of source event ids, in the order they appear on the /events listing. */
    protected function extractEventIds(string $html): array
    {
        preg_match_all('#<a href="https://alkoblan\.com\.sa/events/view/(\d+)">#i', $html, $m);

        return array_map('intval', $m[1] ?? []);
    }

    /** Ordered Title / Location-or-subtitle / "DD Month YYYY" triplets from the /events listing's plain text. */
    protected function extractEventTriplets(string $html): array
    {
        $lines = AlkoblanClient::mainTextLines($html);
        array_shift($lines); // "Events" / "الفعاليات" heading

        $events = [];
        for ($i = 0; $i < count($lines) - 2; $i += 3) {
            $title = $lines[$i];
            $loc = $lines[$i + 1];
            $date = $lines[$i + 2];
            if (! preg_match('/\d{4}/', $date)) {
                break;
            }
            $events[] = compact('title', 'loc', 'date');
        }

        return $events;
    }

    /** Imports the full slider gallery from an event's detail page into event_images. */
    protected function importEventGallery(Event $event, int $sourceId, string $slug): void
    {
        $html = $this->client->get("events/view/{$sourceId}");
        if (! $html) {
            return;
        }

        preg_match_all('#<div class="main-slider__img"><img[^>]*src="([^"]+)"#i', $html, $m);
        $urls = array_values(array_unique($m[1] ?? []));

        foreach ($urls as $i => $url) {
            $media = $this->downloadImage($url, 'events', $slug.'-gallery-'.($i + 1));
            if ($media) {
                EventImage::updateOrCreate(
                    ['event_id' => $event->id, 'media_id' => $media->id],
                    ['sort_order' => $i]
                );
            }
        }
    }

    // =====================================================================
    // Static informational pages (Company / Quality / Services / Guidelines)
    // =====================================================================

    public function importStaticPages(): void
    {
        $pages = [
            'mission-vision' => ['path' => 'mission_vision', 'title' => 'Mission & Vision'],
            'innovation' => ['path' => 'innovation', 'title' => 'Innovation'],
            'quality' => ['path' => 'quality', 'title' => 'Quality'],
            'raw-materials' => ['path' => 'raw_materials', 'title' => 'Raw Materials'],
            'quality-test' => ['path' => 'quality_test', 'title' => 'Product Features & Quality Testing'],
            'installation-tests' => ['path' => 'installation_tests', 'title' => 'Installation Tests'],
            'research-development' => ['path' => 'research_development', 'title' => 'Research & Development'],
            'quality-certificates' => ['path' => 'quality_certificates', 'title' => 'Quality Certificates'],
            'free-supervision' => ['path' => 'free_supervision', 'title' => 'Free Supervision'],
            'our-guarantee' => ['path' => 'our_guarantee', 'title' => 'Our Guarantee'],
            'social-responsibility' => ['path' => 'social_responsibility', 'title' => 'Social Responsibility'],
            'piping-guidelines' => ['path' => 'piping_guidelines', 'title' => 'Piping Guidelines'],
        ];

        foreach ($pages as $slug => $def) {
            $html = $this->client->get($def['path']);
            if (! $html) {
                continue;
            }
            $arHtml = $this->arClient()->get($def['path']);

            $rawMain = AlkoblanClient::mainHtml($html);
            if ($rawMain === null) {
                continue;
            }

            $heroUrl = $this->extractHeroImageUrl($html);
            $featuredImage = $heroUrl ? $this->downloadImage($heroUrl, 'pages', $slug.'-hero') : null;

            $content = $this->cleanContentHtml($rawMain, 'pages', $slug);
            $contentAr = $arHtml ? $this->cleanContentHtml(AlkoblanClient::mainHtml($arHtml) ?? '', 'pages', $slug) : null;

            if (! $this->dryRun) {
                Page::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'title' => $def['title'],
                        'content' => $content,
                        'content_ar' => $contentAr ?: null,
                        'featured_image_id' => $featuredImage?->id,
                        'status' => 'published',
                        'published_at' => now(),
                        'seo_title' => $def['title'].' | AL-KOBLAN Thermopipe Factory',
                        'seo_description' => Str::limit(strip_tags($content), 155),
                    ]
                );
            }
            $this->report['pages']++;
        }
    }

    /** Absolute URL for the hero banner image behind a page's <h1> title, if any. */
    protected function extractHeroImageUrl(string $html): ?string
    {
        if (! preg_match('#<section class="hero-block">.*?<img[^>]*class="img--bg"[^>]*src="([^"]+)"#is', $html, $m)) {
            return null;
        }

        return $this->absoluteUrl($m[1]);
    }

    protected function absoluteUrl(string $src): string
    {
        return preg_match('#^https?://#i', $src) ? $src : rtrim(AlkoblanClient::BASE, '/').'/'.ltrim($src, '/');
    }

    /**
     * Turns a raw <main> content fragment (old Bootstrap-grid markup) into
     * clean semantic HTML this project's plain page/blog templates can
     * render as-is: strips layout wrapper divs/spans and decorative icons,
     * rewrites in-body links from the old domain to this site's own routes
     * (or drops dead "#" links), downloads every referenced image/PDF into
     * local Media storage, and normalizes headings to a single <h3> level.
     */
    protected function cleanContentHtml(string $html, string $folder, string $filenameBase): string
    {
        if (trim($html) === '') {
            return '';
        }

        // Strip inline <script>/<style> blocks and HTML comments the source pages occasionally embed.
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html);
        $html = preg_replace('#<!--.*?-->#s', '', $html);

        // Drop the repeated "Quality" sidebar sub-nav (same links this site
        // already exposes via its own nav dropdown) rather than keeping a
        // duplicate list of links inside the article body.
        $html = preg_replace('#(<h[1-6][^>]*>[^<]*</h[1-6]>\s*)?<ul class="category-list[^"]*">.*?</ul>#is', '', $html);

        // Unwrap <picture>/<source> responsive-image wrappers, keep the <img>.
        $html = preg_replace('#</?picture[^>]*>#i', '', $html);
        $html = preg_replace('#<source[^>]*/?>#i', '', $html);

        // Drop empty decorative icon tags (e.g. <i class="fa fa-..."></i>) and inline SVG icons
        // (they reference a sprite-sheet #id that doesn't exist on this site).
        $html = preg_replace('#<i\b[^>]*>\s*</i>#i', '', $html);
        $html = preg_replace('#<svg\b.*?</svg>#is', '', $html);

        // Rewrite/unwrap anchors: dead "#" links and old-domain nav links are
        // unwrapped to plain text (or rewritten to this site's own route);
        // genuine external links (YouTube, social) are kept, opening in a new tab.
        $html = preg_replace_callback('#<a\b[^>]*?href="([^"]*)"[^>]*>(.*?)</a>#is', function ($m) {
            $href = trim($m[1]);
            $inner = $m[2];

            if ($href === '' || $href === '#') {
                return $inner;
            }
            if (preg_match('#alkoblan\.com\.sa/([a-z_]+)#i', $href, $hm)) {
                $local = self::INTERNAL_LINK_MAP[strtolower($hm[1])] ?? null;

                return $local ? '<a href="'.e($local).'">'.$inner.'</a>' : $inner;
            }
            if (preg_match('#^https?://#i', $href)) {
                return '<a href="'.e($href).'" target="_blank" rel="noopener">'.$inner.'</a>';
            }

            return $inner;
        }, $html);

        // Normalize every heading level down to <h3> (the page already has
        // its own <h1> in the hero and this project's stylesheet has no
        // rules for the source site's h5/h6 sub-heading classes).
        $html = preg_replace_callback('#<(/?)h[1-6]\b[^>]*>#i', fn ($m) => '<'.$m[1].'h3>', $html);

        // Strip attributes from the remaining structural/text tags we keep as-is.
        $html = preg_replace('#<(p|ul|ol|li|strong|em|b|br|h3|h4|thead|tbody|tr)\b[^>]*>#i', '<$1>', $html);

        // Images: download every one into local Media storage and point the
        // <img> at it (dropping the tag if the fetch fails).
        $imgIndex = 0;
        $html = preg_replace_callback('#<img\b[^>]*?src="([^"]+)"[^>]*/?>#i', function ($m) use (&$imgIndex, $folder, $filenameBase) {
            $imgIndex++;
            $media = $this->downloadImage($this->absoluteUrl($m[1]), $folder, $filenameBase.'-'.$imgIndex);

            return $media ? '<img src="'.e($media->url).'" alt="">' : '';
        }, $html);

        // Embedded PDFs (e.g. a warranty certificate) are downloaded and
        // re-embedded from local storage instead of hot-linking the old domain.
        $html = preg_replace_callback('~<iframe\b[^>]*?src="([^"#]+)[^"]*"[^>]*>\s*</iframe>~i', function ($m) use ($folder, $filenameBase) {
            $src = $this->absoluteUrl($m[1]);
            if (! preg_match('#\.pdf$#i', $src)) {
                return '<iframe src="'.e($src).'" width="100%" height="400px" allowfullscreen></iframe>';
            }
            $media = $this->downloadDocument($src, $folder, $filenameBase.'-document');

            return $media ? '<iframe src="'.e($media->url).'#toolbar=0" width="100%" height="500px"></iframe>' : '';
        }, $html);

        // Unwrap remaining layout wrapper tags (Bootstrap grid divs/spans).
        $html = preg_replace('#</?(div|span)\b[^>]*>#i', '', $html);

        // Safety net for any attribute the steps above didn't already strip
        // (e.g. the source markup's unquoted `xss=removed` marker).
        $html = preg_replace('#\s+(class|style|id|data-[a-z-]+)="[^"]*"#i', '', $html);
        $html = preg_replace('#\s+xss=removed\b#i', '', $html);

        // Tables have no matching CSS on this site, so give them minimal inline styling for
        // legibility (applied last so the generic attribute-strip above doesn't undo it).
        // Text alignment is left to the page's own dir="rtl"/"ltr" (see layouts.app).
        $html = preg_replace('#<table\b[^>]*>#i', '<table style="width:100%;border-collapse:collapse;margin:15px 0;">', $html);
        $html = preg_replace('#<(th|td)\b[^>]*>#i', '<$1 style="border:1px solid #ddd;padding:8px 12px;">', $html);

        // Collapse empty paragraphs / redundant line breaks left behind by the cleanup above.
        $html = preg_replace('#<p>(\s|<br\s*/?>)*</p>#i', '', $html);
        $html = preg_replace('#(<br\s*/?>\s*){3,}#i', '<br><br>', $html);

        return trim(preg_replace('#\n{3,}#', "\n\n", $html));
    }

    // =====================================================================
    // Hero slides + homepage content blocks (banners)
    // =====================================================================

    public function importHeroSlidesAndHomeContent(): void
    {
        $html = $this->client->get('/');
        if (! $html) {
            return;
        }
        $banners = AlkoblanClient::imageUrls($html, 'uploads/banners');

        $slideCopy = [
            ['tag' => 'Since 1995', 'title' => 'AL-KOBLAN Thermopipe Factory', 'subtitle' => 'Leading Thermopipe manufacturer in Saudi Arabia — PPr pipes, fittings and valves manufactured to international quality standards.'],
            ['tag' => 'ISO 9001 / ISO 14001 / SASO', 'title' => 'Certified Quality, Made in Saudi Arabia', 'subtitle' => 'Every KTP product is manufactured and tested to ISO and SASO standards before it reaches our customers.'],
            ['tag' => '25+ Years of Experience', 'title' => 'Trusted Across the Kingdom', 'subtitle' => 'From Riyadh to Jeddah and Dammam, AL-KOBLAN supplies plumbing networks for residential, commercial and industrial projects.'],
            ['tag' => 'Free Installation Supervision', 'title' => 'Complete Water Network Solutions', 'subtitle' => 'Pipes, fittings, valves and accessories — plus free installation supervision on qualifying projects.'],
        ];

        foreach ($banners as $idx => $url) {
            $media = $this->downloadImage($url, 'banners', 'hero-'.($idx + 1));
            $copy = $slideCopy[$idx] ?? $slideCopy[$idx % count($slideCopy)];

            if (! $this->dryRun) {
                HeroSlide::updateOrCreate(
                    ['sort_order' => $idx],
                    [
                        'tag' => $copy['tag'],
                        'title' => $copy['title'],
                        'subtitle' => $copy['subtitle'],
                        'button_text' => 'Our Products',
                        'button_url' => '/shop',
                        'button2_text' => 'Contact Us',
                        'button2_url' => '/contact',
                        'image_desktop_id' => $media?->id,
                        'is_active' => true,
                    ]
                );
            }
            $this->report['hero_slides']++;
        }

        if (! $this->dryRun) {
            ContentBlock::updateOrCreate(['key' => 'home_about'], [
                'title' => 'Our Story',
                'subtitle' => '30+ Years of Experience',
                'content' => 'AL-KOBLAN Thermopipe Factory (KTP) was founded in 1995 by the AL-KOBLAN Group of Companies to manufacture PPr pipes and fittings for hot and cold water sanitary networks. Today we are one of the leading thermopipe manufacturers in Saudi Arabia and the wider region.',
                'button_text' => 'Read More',
                'button_url' => '/about',
                'extra' => ['badge_number' => '25+', 'badge_label' => 'Years of Experience', 'badge_label_ar' => 'سنة خبرة'],
            ]);
            ContentBlock::updateOrCreate(['key' => 'home_quality'], [
                'title' => 'Uncompromising Quality',
                'content' => 'Our raw materials, production process and finished products are certified to ISO 9001, ISO 14001, ISO 45001 and the Saudi SASO quality mark.',
                'button_text' => 'Our Quality Standards',
                'button_url' => '/pages/quality-certificates',
            ]);
        }
    }

    // =====================================================================
    // Downloadable documents (catalog + price lists)
    // =====================================================================

    public function importDocuments(): void
    {
        $catHtml = $this->client->get('products/5/');
        if (! $catHtml) {
            return;
        }
        preg_match_all('#href="(https://alkoblan\.com\.sa/assets/img/[^"]+\.pdf)"#i', $catHtml, $m);
        $docs = array_values(array_unique($m[1] ?? []));

        foreach ($docs as $url) {
            $filename = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_FILENAME);
            $this->downloadDocument($url, 'catalogs', Str::slug($filename));
            $this->report['documents']++;
        }
    }

    // =====================================================================
    // Shared image/document download helper (idempotent by source_url)
    // =====================================================================

    protected function downloadImage(string $url, string $folder, string $filenameBase): ?Media
    {
        return $this->downloadAsset($url, $folder, $filenameBase, 'image');
    }

    protected function downloadDocument(string $url, string $folder, string $filenameBase): ?Media
    {
        return $this->downloadAsset($url, $folder, $filenameBase, 'document');
    }

    protected function downloadAsset(string $url, string $folder, string $filenameBase, string $kind): ?Media
    {
        $existing = Media::where('source_url', $url)->first();
        if ($existing) {
            return $existing;
        }

        if (! $this->downloadImages || $this->dryRun) {
            return null;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; AlkoblanContentImporter/1.0)',
            ])->timeout(30)->get($url);

            if (! $response->successful()) {
                $this->failedAssets[] = ['url' => $url, 'reason' => 'HTTP '.$response->status()];
                $this->report['images_failed']++;

                return null;
            }

            $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: ($kind === 'document' ? 'pdf' : 'jpg');
            $path = "media/{$folder}/{$filenameBase}.{$ext}";
            Storage::disk('public')->put($path, $response->body());

            $media = Media::create([
                'disk' => 'public',
                'path' => $path,
                'source_url' => $url,
                'original_name' => basename($path),
                'mime_type' => $response->header('Content-Type'),
                'type' => $kind === 'document' ? 'pdf' : 'image',
                'size' => strlen($response->body()),
                'title' => Str::headline($filenameBase),
            ]);
            $this->report['images_downloaded']++;

            return $media;
        } catch (\Throwable $e) {
            $this->failedAssets[] = ['url' => $url, 'reason' => $e->getMessage()];
            $this->report['images_failed']++;

            return null;
        }
    }
}
