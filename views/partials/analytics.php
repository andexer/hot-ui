<?php

declare(strict_types=1);

/**
 * Analytics loader fragment.
 *
 * Expected variables:
 *
 * @var string|null $analyticsId Measurement id; nothing renders when absent.
 * @var string|null $scriptUrl   Optional self-hosted script URL.
 */
$analyticsId ??= null;
$scriptUrl ??= null;
?>
<?php if ($analyticsId !== null): ?>
<script async src="<?= e(safe_url($scriptUrl ?? 'https://www.googletagmanager.com/gtag/js?id='.$analyticsId)) ?>"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());
    gtag('config', <?= js($analyticsId) ?>);
</script>
<?php endif; ?>
