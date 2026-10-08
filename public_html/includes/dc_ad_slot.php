<?php

/**
 * Fixed-size ad slot wrapper — prevents CLS from responsive AdSense resize.
 *
 * @var string $dc_ad_class DreamCalendars_* CSS class (e.g. DreamCalendars_Month_Header)
 * @var string $dc_ad_slot AdSense slot id
 * @var string $dc_ad_format Optional data-ad-format value
 */
$dc_ad_class = $dc_ad_class ?? 'DreamCalendars_Month_Header';
$dc_ad_slot = $dc_ad_slot ?? '';
$dc_ad_format = $dc_ad_format ?? '';
if ($dc_ad_slot === '') {
    return;
}
$formatAttr = $dc_ad_format !== '' ? ' data-ad-format="' . htmlspecialchars($dc_ad_format, ENT_QUOTES, 'UTF-8') . '"' : '';
?>
<div class="dc-ad-reserve">
<ins class="adsbygoogle <?= htmlspecialchars($dc_ad_class, ENT_QUOTES, 'UTF-8') ?>"
     style="display:block"
     data-ad-client="ca-pub-6725480146756741"
     data-ad-slot="<?= htmlspecialchars($dc_ad_slot, ENT_QUOTES, 'UTF-8') ?>"<?= $formatAttr ?>></ins>
<?php require __DIR__ . '/dc_ad_push.php'; ?>
</div>
