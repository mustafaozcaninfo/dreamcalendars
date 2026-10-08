<?php
/**
 * DreamCalendars sitemap — index + segmented child sitemaps.
 *
 *   /sitemap.xml              → sitemap index
 *   /sitemap-core.xml         → home, blank, leap-years, …
 *   /sitemap-calendars.xml    → all year + month calendars (priority 1.0)
 *   /sitemap-holidays.xml     → holidays, when-is, day/week numbers
 *   /sitemap-articles.xml     → all articles
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/includes/sitemap/builder.php';

$segment = isset($_GET['segment']) ? preg_replace('/[^a-z]/', '', (string) $_GET['segment']) : 'index';

header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex');
header('Cache-Control: public, max-age=3600, s-maxage=3600');

$builder = new DcSitemapBuilder($site, $conn);

if ($segment === 'index' || $segment === '') {
    echo $builder->renderIndex();
    exit;
}

$urls = $builder->segmentUrls($segment);
if ($urls === []) {
    http_response_code(404);
    echo '<?xml version="1.0" encoding="UTF-8"?><error>Unknown sitemap segment</error>';
    exit;
}

echo $builder->renderUrlset($urls);
