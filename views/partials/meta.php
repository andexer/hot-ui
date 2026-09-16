<?php

declare(strict_types=1);

/**
 * Head meta fragment: charset, viewport, title, description, canonical.
 *
 * Expected variables:
 *
 * @var string      $title       Document title.
 * @var string|null $description Meta description.
 * @var string|null $canonical   Absolute canonical URL.
 * @var bool        $indexable   Whether search engines may index the page.
 */
$title ??= 'Hot-UI';
$description ??= null;
$canonical ??= null;
$indexable ??= true;
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<?php if ($description !== null) { ?>
<meta name="description" content="<?= e($description) ?>">
<?php } ?>
<?php if (! $indexable) { ?>
<meta name="robots" content="noindex, nofollow">
<?php } ?>
<?php if ($canonical !== null) { ?>
<link rel="canonical" href="<?= e(safe_url($canonical)) ?>">
<?php } ?>
