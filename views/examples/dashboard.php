<?php

declare(strict_types=1);

$stats = [
    ['label' => 'Ingresos mensuales', 'value' => '12.480 €', 'change' => '+8,2 %'],
    ['label' => 'Usuarios activos', 'value' => '3.942', 'change' => '+3,1 %'],
    ['label' => 'Tickets abiertos', 'value' => '57', 'change' => '-12 %'],
    ['label' => 'Tasa de rebote', 'value' => '4,7 %', 'change' => '+0,4 %'],
];

$rows = [
    ['plan' => 'Pro', 'usuario' => 'lucia@ejemplo.com', 'estado' => 'activa', 'importe' => '29 €'],
    ['plan' => 'Free', 'usuario' => 'marc@ejemplo.com', 'estado' => 'prueba', 'importe' => '0 €'],
    ['plan' => 'Team', 'usuario' => 'sofia@ejemplo.com', 'estado' => 'activa', 'importe' => '99 €'],
    ['plan' => 'Pro', 'usuario' => 'ivan@ejemplo.com', 'estado' => 'suspendida', 'importe' => '29 €'],
];

$stateTone = static fn (string $estado): string => match ($estado) {
    'activa' => 'success',
    'prueba' => 'info',
    default => 'warning',
};
?>
<ui:container size="lg">
    <div class="space-y-6 py-8">

        <ui:page-header
            title="Panel general"
            description="Resumen de actividad de tu organización."
            separator
        />

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <?php foreach ($stats as $s) { ?>
            <ui:stat :label="$s['label']" :value="$s['value']" :change="$s['change']" />
            <?php } ?>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">

            <ui:card class="lg:col-span-2">
                <ui:card-header>
                    <ui:card-title>Últimas suscripciones</ui:card-title>
                    <ui:card-description>Altas y cambios de plan de esta semana.</ui:card-description>
                </ui:card-header>
                <ui:card-content>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" data-slot="example-table">
                            <thead>
                                <tr class="text-muted-foreground border-b text-left">
                                    <th class="py-2 pe-4 font-medium">Plan</th>
                                    <th class="py-2 pe-4 font-medium">Usuario</th>
                                    <th class="py-2 pe-4 font-medium">Estado</th>
                                    <th class="py-2 text-end font-medium">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $r) { ?>
                                <tr class="last:border-b-0 border-b">
                                    <td class="py-2.5 pe-4 font-medium"><?= e($r['plan']) ?></td>
                                    <td class="py-2.5 pe-4"><?= e($r['usuario']) ?></td>
                                    <td class="py-2.5 pe-4">
                                        <ui:badge :tone="$stateTone($r['estado'])" size="sm"><?= e($r['estado']) ?></ui:badge>
                                    </td>
                                    <td class="py-2.5 text-end tabular-nums"><?= e($r['importe']) ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </ui:card-content>
            </ui:card>

            <ui:card>
                <ui:card-header>
                    <ui:card-title>Almacenamiento</ui:card-title>
                    <ui:card-description>Uso del plan Team</ui:card-description>
                </ui:card-header>
                <ui:card-content class="space-y-5">
                    <?php foreach ([['Imágenes', 72], ['Vídeos', 38], ['Backups', 54]] as [$barLabel, $barValue]) { ?>
                    <div class="space-y-1.5">
                        <div class="flex justify-between text-sm">
                            <span><?= e($barLabel) ?></span>
                            <span class="text-muted-foreground tabular-nums"><?= e((string) $barValue) ?> %</span>
                        </div>
                        <ui:progress :value="$barValue" />
                    </div>
                    <?php } ?>
                    <ui:separator class="my-2" />
                    <ui:button variant="outline" size="sm" class="w-full">Ampliar almacenamiento</ui:button>
                </ui:card-content>
            </ui:card>

        </div>
    </div>
</ui:container>