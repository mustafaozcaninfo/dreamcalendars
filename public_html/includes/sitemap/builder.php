<?php

/**
 * DreamCalendars sitemap URL inventory — shared by sitemap.php and indexing cron.
 */
class DcSitemapBuilder
{
    private string $base;
    private int $year;
    private int $month;
    private string $today;
    private string $todayIso;
    /** @var list<string> */
    private array $monthSlugs = [
        'january', 'february', 'march', 'april', 'may', 'june',
        'july', 'august', 'september', 'october', 'november', 'december',
    ];
    private int $yearMin = 2019;
    private int $yearMax = 2099;

    public function __construct(string $siteUrl, private ?mysqli $conn = null)
    {
        $this->base = rtrim($siteUrl, '/') . '/';
        $this->year = (int) date('Y');
        $this->month = (int) date('n');
        $this->today = date('Y-m-d');
        $this->todayIso = date('c');
    }

    /** @return list<array{loc:string,priority:string,changefreq:string,lastmod:string}> */
    public function segmentCore(): array
    {
        $urls = [];
        $this->add($urls, $this->base, '1.0', 'daily');
        $this->add($urls, $this->base . 'calendar/blank', '1.0', 'daily');
        $this->add($urls, $this->base . 'leap-years', '1.0', 'weekly');
        $this->add($urls, $this->base . 'calendar-download', '0.1', 'monthly');
        $this->add($urls, $this->base . 'daylight-saving-time', '0.9', 'monthly');
        $this->add($urls, $this->base . 'current-moon', '0.9', 'daily');
        $this->add($urls, $this->base . 'todays-moon-phase', '0.9', 'daily');
        $this->add($urls, $this->base . 'pages/contact', '0.3', 'monthly');
        $this->add($urls, $this->base . 'pages/privacy-policy', '0.3', 'yearly');
        $this->add($urls, $this->base . 'pages/terms-of-use', '0.3', 'yearly');
        return $this->dedupe($urls);
    }

    /** Year + month calendar URLs — priority 1.0 for all calendar routes */
    public function segmentCalendars(): array
    {
        $urls = [];
        for ($y = $this->yearMin; $y <= $this->yearMax; $y++) {
            $priority = $this->calendarYearPriority($y);
            $freq = abs($y - $this->year) <= 1 ? 'daily' : 'weekly';
            $this->add($urls, $this->base . 'calendar/' . $y, $priority, $freq);
            for ($m = 1; $m <= 12; $m++) {
                $slug = $this->monthSlugs[$m - 1];
                $mp = $this->calendarMonthPriority($y, $m);
                $mf = ($y === $this->year && $m === $this->month) ? 'daily' : $freq;
                $this->add($urls, $this->base . 'calendar/' . $slug . '-' . $y, $mp, $mf);
            }
        }
        return $this->dedupe($urls);
    }

    /** Holidays, when-is, day/week numbers */
    public function segmentHolidays(): array
    {
        $urls = [];
        for ($y = $this->yearMin; $y <= $this->yearMax; $y++) {
            $p = abs($y - $this->year) <= 1 ? '1.0' : '0.9';
            $f = abs($y - $this->year) <= 1 ? 'daily' : 'weekly';
            $this->add($urls, $this->base . 'holidays/' . $y, $p, $f);
            $this->add($urls, $this->base . 'day-numbers/' . $y, $p, $f);
            $this->add($urls, $this->base . 'week-numbers/' . $y, $p, $f);
        }

        $whenDir = dirname(__DIR__, 2) . '/templates/when-is';
        if (is_dir($whenDir)) {
            foreach (glob($whenDir . '/*.php') ?: [] as $file) {
                $slug = basename($file, '.php');
                $this->add($urls, $this->base . 'when-is/' . $slug, '1.0', 'weekly');
            }
        }

        if ($this->conn instanceof mysqli) {
            $sql = 'SELECT DISTINCT link FROM holidays WHERE link IS NOT NULL AND link != "" ORDER BY link';
            if ($res = $this->conn->query($sql)) {
                while ($row = $res->fetch_assoc()) {
                    $link = trim((string) ($row['link'] ?? ''));
                    if ($link !== '') {
                        $this->add($urls, $this->base . 'when-is/' . $link, '1.0', 'weekly');
                    }
                }
            }
        }

        return $this->dedupe($urls);
    }

    /** Blog / article pages */
    public function segmentArticles(): array
    {
        require_once dirname(__DIR__) . '/seo_config.php';
        $noindex = dc_article_noindex_slugs();
        $urls = [];
        $dir = dirname(__DIR__, 2) . '/templates/articles';
        if (!is_dir($dir)) {
            return $urls;
        }
        foreach (glob($dir . '/*.php') ?: [] as $file) {
            $slug = basename($file, '.php');
            if (in_array($slug, $noindex, true)) {
                continue;
            }
            $this->add($urls, $this->base . 'article/' . $slug, '1.0', 'weekly');
        }
        return $this->dedupe($urls);
    }

    /** @return list<array{segment:string,loc:string,count:int}> */
    public function indexManifest(): array
    {
        return [
            ['segment' => 'core', 'loc' => $this->base . 'sitemap-core.xml', 'count' => count($this->segmentCore())],
            ['segment' => 'calendars', 'loc' => $this->base . 'sitemap-calendars.xml', 'count' => count($this->segmentCalendars())],
            ['segment' => 'holidays', 'loc' => $this->base . 'sitemap-holidays.xml', 'count' => count($this->segmentHolidays())],
            ['segment' => 'articles', 'loc' => $this->base . 'sitemap-articles.xml', 'count' => count($this->segmentArticles())],
        ];
    }

    public function segmentUrls(string $segment): array
    {
        return match ($segment) {
            'core' => $this->segmentCore(),
            'calendars' => $this->segmentCalendars(),
            'holidays' => $this->segmentHolidays(),
            'articles' => $this->segmentArticles(),
            default => [],
        };
    }

    public function renderIndex(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($this->indexManifest() as $row) {
            $xml .= "  <sitemap>\n";
            $xml .= '    <loc>' . $this->esc($row['loc']) . "</loc>\n";
            $xml .= '    <lastmod>' . $this->esc($this->todayIso) . "</lastmod>\n";
            $xml .= "  </sitemap>\n";
        }
        $xml .= '</sitemapindex>';
        return $xml;
    }

    /** @param list<array{loc:string,priority:string,changefreq:string,lastmod:string}> $urls */
    public function renderUrlset(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . $this->esc($u['loc']) . "</loc>\n";
            $xml .= '    <lastmod>' . $this->esc($u['lastmod']) . "</lastmod>\n";
            $xml .= '    <changefreq>' . $this->esc($u['changefreq']) . "</changefreq>\n";
            $xml .= '    <priority>' . $this->esc($u['priority']) . "</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';
        return $xml;
    }

    /** Flat list for indexing queue — mirrors sitemap URLs */
  public function allUrlsForIndexing(): array
    {
        $all = array_merge(
            $this->segmentCore(),
            $this->segmentCalendars(),
            $this->segmentHolidays(),
            $this->segmentArticles()
        );
        $out = [];
        foreach ($this->dedupe($all) as $u) {
            $prio = (int) round((float) $u['priority'] * 10);
            $out[] = [
                'url' => $u['loc'],
                'type' => 'sitemap',
                'priority' => max(1, min(10, $prio)),
            ];
        }
        return $out;
    }

    private function calendarYearPriority(int $y): string
    {
        return '1.0';
    }

    private function calendarMonthPriority(int $y, int $m): string
    {
        return '1.0';
    }

    /** @param list<array{loc:string,priority:string,changefreq:string,lastmod:string}> $urls */
    private function add(array &$urls, string $loc, string $priority, string $changefreq): void
    {
        $urls[] = [
            'loc' => $loc,
            'priority' => $priority,
            'changefreq' => $changefreq,
            'lastmod' => $this->todayIso,
        ];
    }

    /** @param list<array{loc:string,priority:string,changefreq:string,lastmod:string}> $urls */
    private function dedupe(array $urls): array
    {
        $seen = [];
        $out = [];
        foreach ($urls as $u) {
            if (isset($seen[$u['loc']])) {
                continue;
            }
            $seen[$u['loc']] = true;
            $out[] = $u;
        }
        return $out;
    }

    private function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
