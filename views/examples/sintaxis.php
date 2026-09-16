<div class="mx-auto flex max-w-3xl flex-col items-start gap-6 p-8">

    <ui:badge class="w-auto">Sintaxis de tags</ui:badge>

    <ui:card class="w-full">
        <ui:card-header>
            <ui:card-title>&lt;ui:…&gt; en vez de echo + buffers</ui:card-title>
            <ui:card-description>
                Los children son HTML y PHP reales: el compilador los traduce internamente
                a open()/into()/close(). Ni ob_start ni closures.
            </ui:card-description>
        </ui:card-header>
        <ui:card-content>
            <div class="flex flex-col gap-4">
                <ui:input x-data="{ q: '' }" x-model="q" placeholder="Buscar artículos…" @keydown.enter="alert('buscando: ' + q)" />
                <div class="flex flex-wrap gap-2">
                    <ui:button variant="default" @click="guardar = 1">Guardar</ui:button>
                    <ui:button variant="destructive" :disabled="$deshabilitado">Deshabilitado</ui:button>
                    <ui:button variant="outline"><i data-lucide="settings-2" class="size-4"></i> Ajustes</ui:button>
                </div>
            </div>
        </ui:card-content>
    </ui:card>

    <ui:alert class="w-full">
        <ui:alert-title>Es solo PHP por debajo</ui:alert-title>
        <ui:alert-description>
            El compilador resuelve &lt;ui:card&gt; → $__ui->open('ui.card', …), los children
            pasan intactos y &lt;/ui:card&gt; → $__ui->close(). El resto del archivo (HTML,
            PHP, scripts, estilos) no se toca.
        </ui:alert-description>
    </ui:alert>

    <ui:separator class="w-full" />

    <div class="flex items-center gap-3">
        <ui:avatar class="size-12">
            <ui:avatar-image src="/favicon.ico" alt="Hot-UI" />
            <ui:avatar-fallback>HU</ui:avatar-fallback>
        </ui:avatar>
        <div class="flex flex-col">
            <span class="font-semibold">Vista renderizada con ui_view()</span>
            <span class="text-sm text-muted-foreground">Atributos dinámicos con :, eventos con @, Alpine con x-*</span>
        </div>
    </div>

    <ui:separator class="w-full" />

    <pre class="w-full overflow-auto rounded-lg border bg-muted p-4 text-xs leading-relaxed"><code>&lt;ui:card class="w-full"&gt;
    &lt;ui:card-header&gt;
        &lt;ui:card-title&gt;&hellip;&lt;/ui:card-title&gt;
    &lt;/ui:card-header&gt;
    &lt;ui:button :variant="$variant" @click="guardar"&gt;Guardar&lt;/ui:button&gt;
&lt;/ui:card&gt;</code></pre>

</div>