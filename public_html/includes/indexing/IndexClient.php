<?php

require_once __DIR__ . '/dc_index_env.php';

/**
 * DreamCalendars IndexClient — IndexNow (multi-host), Bing Webmaster, Yandex Webmaster.
 * Yahoo is covered via IndexNow/Bing network (no separate Yahoo API).
 */
class DcIndexClient
{
    public const INDEXNOW_SHARED  = 'https://api.indexnow.org/indexnow';
    public const INDEXNOW_BING    = 'https://www.bing.com/indexnow';
    public const INDEXNOW_YANDEX  = 'https://yandex.com/indexnow';
    public const BING_API         = 'https://ssl.bing.com/webmaster/api.svc/json';
    public const YANDEX_API       = 'https://api.webmaster.yandex.net/v4';

    private string $baseUrl;
    private string $host;
    private string $configKeyPath;
    private string $webRoot;

    public function __construct(?string $webRoot = null)
    {
        $this->baseUrl = rtrim((string) dc_index_cfg('site_url', 'https://www.dreamcalendars.com'), '/');
        $this->host = (string) dc_index_cfg('host', 'www.dreamcalendars.com');
        $this->configKeyPath = dirname(__DIR__, 3) . '/config/indexnow.key';
        $this->webRoot = $webRoot ?? dirname(__DIR__, 2);
    }

    public function key(): string
    {
        $env = trim((string) dc_index_cfg('indexnow_key', ''));
        if ($env !== '') {
            return $env;
        }
        if (is_readable($this->configKeyPath)) {
            return trim((string) file_get_contents($this->configKeyPath));
        }
        return '';
    }

    public function keyLocation(?string $key = null): string
    {
        $key = $key ?? $this->key();
        return $this->baseUrl . '/' . rawurlencode($key) . '.txt';
    }

    public function ensureKeyFile(): array
    {
        $key = $this->key();
        if ($key === '') {
            return ['ok' => false, 'errors' => ['No IndexNow key']];
        }
        $path = $this->webRoot . '/' . $key . '.txt';
        if (is_readable($path) && trim((string) file_get_contents($path)) === $key) {
            return ['ok' => true, 'path' => $path];
        }
        if (@file_put_contents($path, $key) === false) {
            return ['ok' => false, 'errors' => ['Cannot write ' . $path]];
        }
        return ['ok' => true, 'path' => $path, 'created' => true];
    }

    /** Submit to shared + Bing + Yandex IndexNow endpoints (redundant pings). */
    public function submitIndexNowAll(array $urls): array
    {
        $results = [];
        foreach ([self::INDEXNOW_SHARED, self::INDEXNOW_BING, self::INDEXNOW_YANDEX] as $endpoint) {
            $label = parse_url($endpoint, PHP_URL_HOST) ?: $endpoint;
            $results[$label] = $this->submitIndexNowTo($endpoint, $urls);
        }
        $ok = false;
        foreach ($results as $r) {
            if (!empty($r['ok'])) {
                $ok = true;
                break;
            }
        }
        return ['ok' => $ok, 'results' => $results, 'submitted' => count($this->cleanUrls($urls))];
    }

    public function submitIndexNowTo(string $endpoint, array $urls): array
    {
        $urls = $this->cleanUrls($urls);
        if ($urls === []) {
            return ['ok' => false, 'errors' => ['No valid URLs'], 'status' => 0];
        }
        $key = $this->key();
        if ($key === '') {
            return ['ok' => false, 'errors' => ['IndexNow key missing'], 'status' => 0];
        }
        $urls = array_slice($urls, 0, 10000);
        $payload = json_encode([
            'host' => $this->host,
            'key' => $key,
            'keyLocation' => $this->keyLocation($key),
            'urlList' => array_values($urls),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $resp = $this->http('POST', $endpoint, $payload, [
            'Content-Type: application/json; charset=utf-8',
        ]);
        $ok = in_array($resp['status'], [200, 202], true);
        return [
            'ok' => $ok,
            'status' => $resp['status'],
            'errors' => $ok ? [] : [$this->indexNowError($resp['status'], $resp['raw'])],
        ];
    }

    public function bingConfigured(): bool
    {
        return trim((string) dc_index_cfg('bing_api_key', '')) !== '';
    }

    public function bingSubmitSitemap(?string $feedUrl = null): array
    {
        $apiKey = trim((string) dc_index_cfg('bing_api_key', ''));
        if ($apiKey === '') {
            return ['ok' => false, 'errors' => ['BING_API_KEY not set'], 'status' => 0];
        }
        $feedUrl = $feedUrl ?? $this->baseUrl . '/sitemap.xml';
        $siteUrl = (string) dc_index_cfg('bing_site_url', $this->baseUrl . '/');
        $url = self::BING_API . '/SubmitFeed?apikey=' . rawurlencode($apiKey);
        $resp = $this->http('POST', $url, json_encode([
            'siteUrl' => $siteUrl,
            'feedUrl' => $feedUrl,
        ]), ['Content-Type: application/json; charset=utf-8']);
        $j = json_decode($resp['raw'], true);
        $ok = $resp['status'] === 200 && (empty($j['ErrorCode']) || (int) $j['ErrorCode'] === 0);
        return [
            'ok' => $ok,
            'status' => $resp['status'],
            'errors' => $ok ? [] : [(string) ($j['Message'] ?? $j['ErrorMessage'] ?? 'Bing SubmitFeed failed')],
        ];
    }

    public function yandexConfigured(): bool
    {
        if (!dc_index_cfg('yandex_enabled', false)) {
            return false;
        }
        return trim((string) dc_index_cfg('yandex_oauth_token', '')) !== ''
            && trim((string) dc_index_cfg('yandex_user_id', '')) !== ''
            && trim((string) dc_index_cfg('yandex_host_id', '')) !== '';
    }

    private function yandexHostBase(): string
    {
        return self::YANDEX_API
            . '/user/' . rawurlencode((string) dc_index_cfg('yandex_user_id'))
            . '/hosts/' . rawurlencode((string) dc_index_cfg('yandex_host_id'));
    }

    public function yandexAddSitemap(?string $sitemapUrl = null): array
    {
        if (!$this->yandexConfigured()) {
            return ['ok' => false, 'errors' => ['Yandex not configured'], 'status' => 0];
        }
        $sitemapUrl = $sitemapUrl ?? $this->baseUrl . '/sitemap.xml';
        $token = (string) dc_index_cfg('yandex_oauth_token');
        $resp = $this->http('POST', $this->yandexHostBase() . '/user-added-sitemaps', json_encode(['url' => $sitemapUrl]), [
            'Authorization: OAuth ' . $token,
            'Content-Type: application/json',
        ]);
        $ok = in_array($resp['status'], [200, 201, 202], true);
        return ['ok' => $ok, 'status' => $resp['status'], 'errors' => $ok ? [] : ['Yandex sitemap HTTP ' . $resp['status']]];
    }

    public function yandexRecrawlQuota(): array
    {
        if (!$this->yandexConfigured()) {
            return ['ok' => false, 'errors' => ['Yandex not configured']];
        }
        $token = (string) dc_index_cfg('yandex_oauth_token');
        $resp = $this->http('GET', $this->yandexHostBase() . '/recrawl/quota', null, [
            'Authorization: OAuth ' . $token,
        ]);
        $j = json_decode($resp['raw'], true);
        $ok = $resp['status'] === 200 && is_array($j);
        return ['ok' => $ok, 'quota' => $j, 'status' => $resp['status']];
    }

    /** @param string[] $urls */
    public function yandexRecrawl(array $urls): array
    {
        if (!$this->yandexConfigured()) {
            return ['ok' => false, 'errors' => ['Yandex not configured'], 'ok_count' => 0];
        }
        $urls = $this->cleanUrls($urls);
        $token = (string) dc_index_cfg('yandex_oauth_token');
        $endpoint = $this->yandexHostBase() . '/recrawl/queue';
        $headers = ['Authorization: OAuth ' . $token, 'Content-Type: application/json'];
        $ok = 0;
        $fail = 0;
        $transient = false;
        foreach ($urls as $url) {
            $resp = $this->http('POST', $endpoint, json_encode(['url' => $url]), $headers);
            if ($resp['status'] === 202) {
                $ok++;
            } else {
                $fail++;
                if ($resp['status'] === 429) {
                    $transient = true;
                    break;
                }
            }
        }
        return ['ok' => $fail === 0, 'ok_count' => $ok, 'fail_count' => $fail, 'transient' => $transient];
    }

    public function cleanUrls(array $urls): array
    {
        $out = [];
        $host = strtolower($this->host);
        foreach ($urls as $u) {
            $u = trim((string) $u);
            if ($u === '' || !filter_var($u, FILTER_VALIDATE_URL)) {
                continue;
            }
            $h = strtolower((string) parse_url($u, PHP_URL_HOST));
            if ($h === $host || ($h !== '' && str_ends_with($h, '.' . $host))) {
                $out[$u] = $u;
            }
        }
        return array_values($out);
    }

    public static function isTransientError(string $msg): bool
    {
        $m = strtolower($msg);
        foreach (['quota', '429', 'rate limit', 'too many', 'unavailable', 'timeout', '503', '502'] as $needle) {
            if (str_contains($m, $needle)) {
                return true;
            }
        }
        return false;
    }

    private function indexNowError(int $status, string $raw): string
    {
        $map = [400 => 'Bad request', 403 => 'Forbidden (check key file)', 422 => 'Unprocessable', 429 => 'Rate limited'];
        $msg = $map[$status] ?? ('HTTP ' . $status);
        $raw = trim($raw);
        return $raw !== '' && strlen($raw) < 200 ? $msg . ': ' . $raw : $msg;
    }

    private function http(string $method, string $url, ?string $body = null, array $headers = []): array
    {
        if (!function_exists('curl_init')) {
            return ['status' => 0, 'raw' => 'curl missing'];
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => array_merge($headers, ['User-Agent: DC-IndexClient/1.0']),
            CURLOPT_TIMEOUT => 25,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($raw === false) {
            $raw = curl_error($ch) ?: 'network error';
        }
        curl_close($ch);
        return ['status' => $status, 'raw' => (string) $raw];
    }
}
