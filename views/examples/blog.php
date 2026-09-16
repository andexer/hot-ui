<?php

declare(strict_types=1);

$posts = [
    [
        'titulo' => 'PHP vainilla en 2026: por qué sigue ganando',
        'extracto' => 'Renderers y tipado estricto: la pila mínima que rinde como ninguna.',
        'categoria' => 'PHP',
        'autor' => 'Lucía Ferrer',
        'iniciales' => 'LF',
        'fecha' => '12 ago 2026',
        'lectura' => '6 min',
    ],
    [
        'titulo' => 'Alpine.js con TypeScript estricto sin bundler',
        'extracto' => 'ESM nativo, islas por componente y cero dependencias de runtime.',
        'categoria' => 'Frontend',
        'autor' => 'Marc Solà',
        'iniciales' => 'MS',
        'fecha' => '9 ago 2026',
        'lectura' => '8 min',
    ],
    [
        'titulo' => 'Diseño con tokens: la guía definitiva',
        'extracto' => 'Radios, sombras y paletas como variables CSS vivas y exportables.',
        'categoria' => 'Diseño',
        'autor' => 'Sofia Ibáñez',
        'iniciales' => 'SI',
        'fecha' => '2 ago 2026',
        'lectura' => '11 min',
    ],
];

$categoryTone = static fn (string $cat): string => match ($cat) {
    'PHP' => 'info',
    'Frontend' => 'success',
    default => 'warning',
};
?>
<ui:container size="lg">
    <div class="space-y-8 py-8">

        <ui:page-header
            title="El blog de Hot-UI"
            description="Notas sobre PHP, Alpine.js y diseño con tokens."
            separator
        />

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($posts as $post) { ?>
            <ui:card class="group h-full transition-shadow hover:shadow-md">
                <ui:card-header>
                    <div class="flex items-center justify-between">
                        <ui:badge :tone="$categoryTone($post['categoria'])" size="sm"><?= e($post['categoria']) ?></ui:badge>
                        <span class="text-muted-foreground text-xs"><?= e($post['lectura']) ?> de lectura</span>
                    </div>
                    <ui:link href="#articulo" class="mt-2 block">
                        <ui:card-title class="group-hover:underline text-lg leading-snug"><?= e($post['titulo']) ?></ui:card-title>
                    </ui:link>
                </ui:card-header>
                <ui:card-content>
                    <p class="text-muted-foreground text-sm leading-relaxed"><?= e($post['extracto']) ?></p>
                </ui:card-content>
                <ui:card-footer class="items-center justify-between">
                    <ui:avatar class="size-7">
                        <ui:avatar-fallback><?= e($post['iniciales']) ?></ui:avatar-fallback>
                    </ui:avatar>
                    <div class="flex flex-col text-xs leading-tight">
                        <span class="font-medium"><?= e($post['autor']) ?></span>
                        <span class="text-muted-foreground"><?= e($post['fecha']) ?></span>
                    </div>
                </ui:card-footer>
            </ui:card>
            <?php } ?>
        </div>

        <ui:pagination class="justify-center">
            <ui:pagination-content>
                <ui:pagination-item>
                    <ui:pagination-previous href="#anterior" />
                </ui:pagination-item>
                <ui:pagination-item>
                    <ui:pagination-link href="#p1" is-active>1</ui:pagination-link>
                </ui:pagination-item>
                <ui:pagination-item>
                    <ui:pagination-link href="#p2">2</ui:pagination-link>
                </ui:pagination-item>
                <ui:pagination-item>
                    <ui:pagination-link href="#p3">3</ui:pagination-link>
                </ui:pagination-item>
                <ui:pagination-item>
                    <ui:pagination-next href="#siguiente" />
                </ui:pagination-item>
            </ui:pagination-content>
        </ui:pagination>

    </div>
</ui:container>