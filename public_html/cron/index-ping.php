#!/usr/bin/env php
<?php
/**
 * DreamCalendars search-engine indexing cron.
 * IndexNow (api.indexnow.org + bing.com + yandex.com) · Bing SubmitFeed · Yandex recrawl
 *
 * Crontab every 2 hours:
 * 0 0,2,4,6,8,10,12,14,16,18,20,22 * * * php .../cron/index-ping.php
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

require_once dirname(__DIR__) . '/includes/indexing/runner.php';

try {
    $result = dc_index_run('cron');
    if (!empty($result['error'])) {
        exit(1);
    }
    exit(0);
} catch (Throwable $e) {
    dc_index_log('FATAL: ' . $e->getMessage());
    exit(1);
}
