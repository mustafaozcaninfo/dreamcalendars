<?php

require_once __DIR__ . '/dc_index_env.php';
require_once dirname(__DIR__, 2) . '/includes/sitemap/builder.php';

/** Build indexable URL inventory — mirrors sitemap segments */
function dc_index_build_inventory(?mysqli $conn = null): array
{
    $base = rtrim((string) dc_index_cfg('site_url', 'https://www.dreamcalendars.com'), '/');
    if (!str_ends_with($base, '/')) {
        $base .= '/';
    }

    $builder = new DcSitemapBuilder($base, $conn);
    return $builder->allUrlsForIndexing();
}
