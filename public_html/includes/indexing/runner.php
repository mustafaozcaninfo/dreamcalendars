<?php

require_once __DIR__ . '/dc_index_env.php';
require_once __DIR__ . '/IndexClient.php';
require_once __DIR__ . '/inventory.php';
require_once __DIR__ . '/queue.php';

/**
 * Main indexing cron runner.
 * @return array<string,mixed>
 */
function dc_index_run(string $scope = 'cron'): array
{
    $client = new DcIndexClient();
    $summary = ['scope' => $scope, 'steps' => []];

    $keyCheck = $client->ensureKeyFile();
    $summary['key_file'] = $keyCheck;
    if (empty($keyCheck['ok'])) {
        dc_index_log('ERROR: IndexNow key file missing');
        return $summary;
    }

    $pdo = dc_index_pdo();
    if (!dc_index_tables_exist($pdo)) {
        dc_index_log('ERROR: index_queue tables missing — run scripts/sql/001_index_queue.sql');
        $summary['error'] = 'tables_missing';
        return $summary;
    }

    $conn = null;
    $connFile = dirname(__DIR__, 2) . '/connection.php';
    if (is_readable($connFile)) {
        include $connFile;
        if (isset($conn) && $conn instanceof mysqli) {
            // use existing mysqli from connection.php
        }
    }

    $inventory = dc_index_build_inventory($conn ?? null);
    $sync = dc_index_sync_inventory($pdo, $inventory);
    $summary['sync'] = $sync;
    dc_index_log('sync inventory: ' . json_encode($sync));

    // --- IndexNow (Bing + Yandex + Yahoo via shared network) ---
    $batch = (int) dc_index_cfg('indexnow_batch', 10000);
    $pending = dc_index_fetch_pending($pdo, 'indexnow', $batch);
    if ($pending !== []) {
        $urls = array_column($pending, 'url');
        $ids = array_column($pending, 'id');
        $res = $client->submitIndexNowAll($urls);
        $ok = !empty($res['ok']);
        dc_index_mark($pdo, $ids, 'indexnow', $ok ? 'submitted' : 'error');
        $detail = json_encode(array_map(static fn ($r) => $r['status'] ?? 0, $res['results'] ?? []));
        dc_index_log_submission($pdo, 'indexnow', $scope, count($urls), $ok ? count($urls) : 0, $ok ? 202 : 0, $detail);
        $summary['steps']['indexnow'] = ['urls' => count($urls), 'ok' => $ok, 'endpoints' => $res['results'] ?? []];
        dc_index_log('IndexNow: ' . count($urls) . ' urls — ' . ($ok ? 'ok' : 'fail'));
    } else {
        $summary['steps']['indexnow'] = ['urls' => 0, 'ok' => true, 'note' => 'queue empty'];
    }

    // --- Bing sitemap submit (daily) ---
    if ($client->bingConfigured()) {
        $last = dc_index_meta_get($pdo, 'bing_sitemap_at');
        $hours = (int) dc_index_cfg('bing_sitemap_cron_hours', 24);
        $due = $last === null || (time() - strtotime($last . ' UTC')) >= $hours * 3600;
        if ($due) {
            $bing = $client->bingSubmitSitemap();
            dc_index_log_submission($pdo, 'bing_sitemap', $scope, 1, $bing['ok'] ? 1 : 0, (int) ($bing['status'] ?? 0), implode('; ', $bing['errors'] ?? []));
            if ($bing['ok']) {
                dc_index_meta_set($pdo, 'bing_sitemap_at', gmdate('Y-m-d H:i:s'));
            }
            $summary['steps']['bing_sitemap'] = $bing;
            dc_index_log('Bing sitemap: ' . ($bing['ok'] ? 'ok' : implode(', ', $bing['errors'] ?? [])));
        }
    } else {
        $summary['steps']['bing_sitemap'] = ['skipped' => 'BING_API_KEY not set'];
    }

    // --- Yandex sitemap (weekly) + recrawl rotation ---
    if ($client->yandexConfigured()) {
        $lastYaSite = dc_index_meta_get($pdo, 'yandex_sitemap_at');
        if ($lastYaSite === null || (time() - strtotime($lastYaSite . ' UTC')) >= 7 * 86400) {
            $yaSite = $client->yandexAddSitemap();
            if ($yaSite['ok']) {
                dc_index_meta_set($pdo, 'yandex_sitemap_at', gmdate('Y-m-d H:i:s'));
            }
            dc_index_log_submission($pdo, 'yandex_sitemap', $scope, 1, $yaSite['ok'] ? 1 : 0, (int) ($yaSite['status'] ?? 0), implode('; ', $yaSite['errors'] ?? []));
            $summary['steps']['yandex_sitemap'] = $yaSite;
        }

        $dailyLimit = (int) dc_index_cfg('yandex_recrawl_daily_limit', 145);
        $used = dc_index_used_today($pdo, 'yandex_recrawl');
        $budget = max(0, $dailyLimit - $used);
        $batchYa = min((int) dc_index_cfg('yandex_batch', 12), $budget);
        if ($batchYa > 0) {
            $yaPending = dc_index_fetch_pending($pdo, 'yandex', $batchYa);
            if ($yaPending !== []) {
                $yaUrls = array_column($yaPending, 'url');
                $yaIds = array_column($yaPending, 'id');
                $yaRes = $client->yandexRecrawl($yaUrls);
                $yaOk = !empty($yaRes['ok']) || (int) ($yaRes['ok_count'] ?? 0) > 0;
                $status = !empty($yaRes['transient']) ? 'pending' : ($yaOk ? 'submitted' : 'error');
                dc_index_mark($pdo, $yaIds, 'yandex', $status);
                dc_index_log_submission($pdo, 'yandex_recrawl', $scope, count($yaUrls), (int) ($yaRes['ok_count'] ?? 0), 202, json_encode($yaRes));
                $summary['steps']['yandex_recrawl'] = $yaRes;
                dc_index_log('Yandex recrawl: ' . ($yaRes['ok_count'] ?? 0) . '/' . count($yaUrls));
            }
        }
    } else {
        $summary['steps']['yandex'] = ['skipped' => 'Yandex API not configured'];
    }

    // Priority live ping: homepage + sitemap always nudged
    $live = $client->submitIndexNowAll([
        rtrim((string) dc_index_cfg('site_url'), '/') . '/',
        rtrim((string) dc_index_cfg('site_url'), '/') . '/sitemap.xml',
    ]);
    $summary['steps']['live_ping'] = ['ok' => !empty($live['ok'])];
    dc_index_log('live ping home+sitemap: ' . (!empty($live['ok']) ? 'ok' : 'fail'));

    return $summary;
}

/** Queue URLs for next cron (call after content publish). */
function dc_index_notify_urls(array $urls, int $priority = 9): void
{
    try {
        $pdo = dc_index_pdo();
        if (!dc_index_tables_exist($pdo)) {
            return;
        }
        dc_index_enqueue_urls($pdo, $urls, $priority);
        $client = new DcIndexClient();
        $clean = $client->cleanUrls($urls);
        if ($clean !== [] && $client->key() !== '') {
            $client->submitIndexNowAll(array_slice($clean, 0, 100));
        }
    } catch (Throwable $e) {
        dc_index_log('notify error: ' . $e->getMessage());
    }
}
