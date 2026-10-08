<?php
/**
 * DreamCalendars search-engine indexing config.
 * Override secrets in config/indexing.local.php (gitignored).
 */
$dcIndexing = [
    'site_url' => 'https://www.dreamcalendars.com',
    'host'     => 'www.dreamcalendars.com',

    // IndexNow — key file: public_html/{key}.txt + config/indexnow.key
    'indexnow_key' => trim(@file_get_contents(__DIR__ . '/indexnow.key') ?: ''),

    // Bing Webmaster (SubmitFeed + redundant IndexNow host)
    'bing_api_key'  => '',
    'bing_site_url' => 'https://www.dreamcalendars.com/',

    // Yandex Webmaster API v4 (recrawl + sitemap)
    'yandex_enabled'   => false,
    'yandex_oauth_token' => '',
    'yandex_user_id'   => '',
    'yandex_host_id'   => '',
    'yandex_recrawl_daily_limit' => 145,

    // Batch sizes per cron run (every 2h)
    'indexnow_batch' => 10000,
    'yandex_batch'   => 12,
    'bing_sitemap_cron_hours' => 24,
];

$local = __DIR__ . '/indexing.local.php';
if (is_readable($local)) {
    $overrides = require $local;
    if (is_array($overrides)) {
        $dcIndexing = array_merge($dcIndexing, $overrides);
    }
}

return $dcIndexing;
