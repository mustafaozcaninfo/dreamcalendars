<?php

declare(strict_types=1);

require_once __DIR__ . '/classes/MetaManager.php';

/**
 * @param list<array{label: string, url?: string, current?: bool}>|null $crumbs
 * @param bool $fullWidth Match col-md-12 pages (week-numbers, daylight-saving-time)
 */
function dc_render_breadcrumb(?array $crumbs = null, bool $fullWidth = false): void
{
    if ($crumbs === null) {
        $crumbs = dc_meta()->uiBreadcrumbs();
    }
    if ($crumbs === []) {
        return;
    }

    $colClass = $fullWidth ? 'col-md-12' : 'col-md-12 col-lg-9';

    echo '<div class="row dc-breadcrumb-row"><div class="' . $colClass . '">';
    echo '<nav class="dc-breadcrumb" aria-label="Breadcrumb"><ol class="breadcrumb">';
    echo '<li class="breadcrumb-item"><a href="/">Home</a></li>';
    foreach ($crumbs as $crumb) {
        $label = htmlspecialchars((string) $crumb['label'], ENT_QUOTES, 'UTF-8');
        $isCurrent = !empty($crumb['current']) || empty($crumb['url']);
        echo '<li class="breadcrumb-item' . ($isCurrent ? ' active' : '') . '"' . ($isCurrent ? ' aria-current="page"' : '') . '>';
        if (!$isCurrent && !empty($crumb['url'])) {
            echo '<a href="' . htmlspecialchars((string) $crumb['url'], ENT_QUOTES, 'UTF-8') . '">' . $label . '</a>';
        } else {
            echo $label;
        }
        echo '</li>';
    }
    echo '</ol></nav></div></div>';
}
