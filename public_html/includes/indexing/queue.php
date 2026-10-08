<?php

require_once __DIR__ . '/dc_index_env.php';
require_once __DIR__ . '/IndexClient.php';

function dc_index_tables_exist(PDO $pdo): bool
{
    try {
        $pdo->query('SELECT 1 FROM index_queue LIMIT 1');
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function dc_index_sync_inventory(PDO $pdo, array $inventory): array
{
    $added = 0;
    $st = $pdo->prepare(
        'INSERT INTO index_queue (url, url_hash, type, priority, indexnow_status, yandex_status)
         VALUES (:url, :hash, :type, :prio, :in_st, :ya_st)
         ON DUPLICATE KEY UPDATE type = VALUES(type), priority = GREATEST(priority, VALUES(priority))'
    );
    $yandexDefault = (new DcIndexClient())->yandexConfigured() ? 'pending' : 'disabled';

    foreach ($inventory as $row) {
        $st->execute([
            ':url' => $row['url'],
            ':hash' => sha1($row['url']),
            ':type' => $row['type'],
            ':prio' => (int) $row['priority'],
            ':in_st' => 'pending',
            ':ya_st' => $yandexDefault,
        ]);
        if ($st->rowCount() === 1) {
            $added++;
        }
    }

    if ((new DcIndexClient())->yandexConfigured()) {
        $pdo->exec("UPDATE index_queue SET yandex_status = 'pending' WHERE yandex_status = 'disabled'");
    }
    return ['added' => $added, 'total' => count($inventory)];
}

function dc_index_fetch_pending(PDO $pdo, string $engine, int $limit): array
{
    $col = $engine === 'yandex' ? 'yandex_status' : 'indexnow_status';
    $sql = "SELECT id, url FROM index_queue WHERE {$col} = 'pending' ORDER BY priority DESC, id ASC LIMIT " . (int) $limit;
    return $pdo->query($sql)->fetchAll();
}

function dc_index_mark(PDO $pdo, array $ids, string $engine, string $status): void
{
    if ($ids === []) {
        return;
    }
    $col = $engine === 'yandex' ? 'yandex_status' : 'indexnow_status';
    $at = $engine === 'yandex' ? 'yandex_at' : 'indexnow_at';
    $in = implode(',', array_fill(0, count($ids), '?'));
    $sql = "UPDATE index_queue SET {$col} = ?, {$at} = UTC_TIMESTAMP() WHERE id IN ({$in})";
    $pdo->prepare($sql)->execute(array_merge([$status], $ids));
}

function dc_index_log_submission(PDO $pdo, string $engine, string $scope, int $urlCount, int $okCount, int $httpStatus, string $detail): void
{
    $pdo->prepare(
        'INSERT INTO index_submissions (engine, scope, url_count, ok_count, http_status, detail) VALUES (?,?,?,?,?,?)'
    )->execute([$engine, $scope, $urlCount, $okCount, $httpStatus, mb_substr($detail, 0, 500)]);
}

function dc_index_meta_get(PDO $pdo, string $key): ?string
{
    try {
        $st = $pdo->prepare('SELECT meta_value FROM index_meta WHERE meta_key = ?');
        $st->execute([$key]);
        $v = $st->fetchColumn();
        return $v === false ? null : (string) $v;
    } catch (Throwable $e) {
        return null;
    }
}

function dc_index_meta_set(PDO $pdo, string $key, string $value): void
{
    $pdo->prepare(
        'INSERT INTO index_meta (meta_key, meta_value) VALUES (?,?) ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)'
    )->execute([$key, $value]);
}

function dc_index_used_today(PDO $pdo, string $engine): int
{
    try {
        $st = $pdo->prepare(
            "SELECT COALESCE(SUM(ok_count),0) FROM index_submissions WHERE engine = ? AND created_at >= UTC_DATE()"
        );
        $st->execute([$engine]);
        return (int) $st->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

function dc_index_enqueue_urls(PDO $pdo, array $urls, int $priority = 9): void
{
    $client = new DcIndexClient();
    $urls = $client->cleanUrls($urls);
    if ($urls === []) {
        return;
    }
    $yandexDefault = $client->yandexConfigured() ? 'pending' : 'disabled';
    $st = $pdo->prepare(
        'INSERT INTO index_queue (url, url_hash, type, priority, indexnow_status, yandex_status)
         VALUES (?,?,\'custom\',?, \'pending\', ?)
         ON DUPLICATE KEY UPDATE priority = GREATEST(priority, VALUES(priority)), indexnow_status = \'pending\''
    );
    foreach ($urls as $url) {
        $st->execute([$url, sha1($url), $priority, $yandexDefault]);
    }
}
