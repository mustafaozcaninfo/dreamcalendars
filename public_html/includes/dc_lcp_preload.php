<?php

/**
 * Optional LCP image preload tags — set $dc_lcp_preloads before including.
 *
 * @var list<array{href:string,media?:string}> $dc_lcp_preloads
 */
if (empty($dc_lcp_preloads) || !is_array($dc_lcp_preloads)) {
    return;
}

foreach ($dc_lcp_preloads as $preload) {
    if (empty($preload['href'])) {
        continue;
    }
    $href = htmlspecialchars((string) $preload['href'], ENT_QUOTES, 'UTF-8');
    $media = !empty($preload['media'])
        ? ' media="' . htmlspecialchars((string) $preload['media'], ENT_QUOTES, 'UTF-8') . '"'
        : '';
    echo '<link rel="preload" as="image" href="' . $href . '"' . $media . ' fetchpriority="high">' . "\n    ";
}
