<ui:card class="w-full max-w-sm">

    <ui:card-header>
        <ui:card-title>Bienvenido de nuevo</ui:card-title>
        <ui:card-description>Introduce tus credenciales para continuar.</ui:card-description>
    </ui:card-header>

    <ui:card-content class="space-y-4">
        <ui:label for="email">Correo electrónico</ui:label>
        <ui:input id="email" name="email" type="email" placeholder="tu@correo.com" required />

        <ui:label for="password">Contraseña</ui:label>
        <ui:input id="password" name="password" type="password" required />

        <div class="flex items-center justify-between">
            <ui:label class="flex items-center gap-2 text-sm font-normal">
                <ui:checkbox name="remember" />
                Recuérdame
            </ui:label>
            <ui:link href="#recuperar" class="text-muted-foreground hover:text-foreground text-sm underline-offset-4 hover:underline">
                ¿Olvidaste tu contraseña?
            </ui:link>
        </div>

        <ui:button class="w-full">Entrar</ui:button>
        <ui:button variant="outline" class="w-full">Entrar con GitHub</ui:button>
    </ui:card-content>

    <ui:card-footer>
        <ui:link href="#registro" class="text-sm underline-offset-4 hover:underline">
            ¿No tienes cuenta? Regístrate
        </ui:link>
    </ui:card-footer>

</ui:card>