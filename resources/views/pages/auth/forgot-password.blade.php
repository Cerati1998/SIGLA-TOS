<x-layouts::auth title="Recuperar contraseña">
    <div class="flex flex-col gap-6">
        <x-auth-header title="Recuperar contraseña" description="Escribe tu correo y te enviaremos un enlace para crear una contraseña nueva." />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-3.5">
            @csrf

            <flux:input
                name="email"
                label="Correo electrónico"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="correo@empresa.com"
            >
                <x-slot name="icon">
                    <x-feather name="mail" class="size-4 text-ink-muted" />
                </x-slot>
            </flux:input>

            <div class="flex justify-center">
                <flux:button variant="primary" type="submit" data-test="email-password-reset-link-button">Enviar enlace</flux:button>
            </div>
        </form>

        <div class="text-center">
            <flux:link class="inline-flex items-center text-sm max-md:min-h-11" :href="route('login')" wire:navigate>
                Volver a iniciar sesión
            </flux:link>
        </div>
    </div>
</x-layouts::auth>
