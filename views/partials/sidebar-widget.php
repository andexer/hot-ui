<?php

declare(strict_types=1);

/**
 * Widget de tarjeta para sidebars.
 *
 * Expected variables:
 *
 * @var string      $heading Widget title.
 * @var string|null $actionUrl Optional "see all" target.
 * @var string      $content Widget body (pre-rendered HTML).
 */
$heading ??= '';
$actionUrl ??= null;
$content ??= '';
?>
<section class="bg-card rounded-xl border p-4" data-slot="sidebar-widget">
    <header class="mb-3 flex items-center justify-between gap-2">
        <h3 class="text-sm font-semibold"><?= e($heading) ?></h3>
        <?php if ($actionUrl !== null) { ?>
        <a href="<?= e(safe_url($actionUrl)) ?>" class="text-muted-foreground hover:text-foreground text-xs">Ver todo</a>
        <?php } ?>
    </header>
    <div class="text-sm"><?= $content ?></div>
</section>
