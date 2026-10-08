<?php

/** @return array<string, mixed> */
function dc_seo_routes_data(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $candidates = [
        __DIR__ . '/../config/seo-routes.json',
        dirname(__DIR__, 2) . '/config/seo-routes.json',
    ];
    $path = '';
    foreach ($candidates as $candidate) {
        if (is_readable($candidate)) {
            $path = $candidate;
            break;
        }
    }
    if ($path === '') {
        return $data = [];
    }
    $json = json_decode((string) file_get_contents($path), true);
    return $data = is_array($json) ? $json : [];
}

function dc_seo_replace(string $template, array $vars): string
{
    return preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', static function (array $m) use ($vars): string {
        $key = $m[1];
        return isset($vars[$key]) ? (string) $vars[$key] : $m[0];
    }, $template) ?? $template;
}

/** @return array{title?:string,description?:string} */
function dc_seo_hint(string $routeKey, array $vars = []): array
{
    $routes = dc_seo_routes_data();
    if (!isset($routes[$routeKey]) || !is_array($routes[$routeKey])) {
        return [];
    }
    $row = $routes[$routeKey];
    $out = [];
    if (!empty($row['title'])) {
        $out['title'] = dc_seo_replace((string) $row['title'], $vars);
    }
    if (!empty($row['description'])) {
        $out['description'] = dc_seo_replace((string) $row['description'], $vars);
    }
    return $out;
}

function dc_seo_default_vars(): array
{
    global $site, $sitename, $nowyear, $nowmonthdesc, $nextyear;
    return [
        'year' => (string) ($nowyear ?? date('Y')),
        'nextYear' => (string) ($nextyear ?? date('Y', strtotime('+1 year'))),
        'month' => strtolower((string) ($nowmonthdesc ?? date('F'))),
        'monthTitle' => (string) ($nowmonthdesc ?? date('F')),
        'sitename' => (string) ($sitename ?? 'Dream Calendars'),
        'site' => rtrim((string) ($site ?? 'https://www.dreamcalendars.com/'), '/'),
    ];
}

/** @return list<string> */
function dc_article_noindex_slugs(): array
{
    static $slugs = null;
    if ($slugs !== null) {
        return $slugs;
    }
    $candidates = [
        __DIR__ . '/../config/article-seo.json',
        dirname(__DIR__, 2) . '/config/article-seo.json',
    ];
    foreach ($candidates as $path) {
        if (!is_readable($path)) {
            continue;
        }
        $json = json_decode((string) file_get_contents($path), true);
        if (is_array($json) && isset($json['noindex_slugs']) && is_array($json['noindex_slugs'])) {
            return $slugs = array_values(array_filter(array_map('strval', $json['noindex_slugs'])));
        }
    }
    return $slugs = [];
}

function dc_article_is_noindex(string $slug): bool
{
    return in_array($slug, dc_article_noindex_slugs(), true);
}
