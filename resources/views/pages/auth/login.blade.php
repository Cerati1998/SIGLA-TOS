<x-layouts::auth title="Iniciar sesión">
    <div class="flex flex-col gap-6">
        <x-auth-header title="Iniciar sesión" description="Ingresa con tu correo y tu contraseña." />

        <x-auth-session-status class="text-center" :status="session('status')" />

        @if ($errors->any())
            <div role="alert" class="rounded-control border border-danger px-3 py-2 text-sm text-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-3.5">
            @csrf

            <flux:input
                input:name="email"
                label="Correo electrónico"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="correo@empresa.com"
            >
                <x-slot name="icon">
                    <x-feather name="user" class="size-4 text-ink-muted" />
                </x-slot>
            </flux:input>

            <flux:input
                input:name="password"
                label="Contraseña"
                type="password"
                required
                autocomplete="current-password"
            >
                <x-slot name="icon">
                    <x-feather name="lock" class="size-4 text-ink-muted" />
                </x-slot>
            </flux:input>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <flux:checkbox name="remember" label="Recordarme" :checked="old('remember')" />

                <flux:link class="inline-flex items-center text-sm max-md:min-h-11" :href="route('password.request')" wire:navigate>
                    ¿Olvidaste tu contraseña?
                </flux:link>
            </div>

            <div class="flex justify-center">
                <flux:button variant="primary" type="submit" data-test="login-button">Iniciar sesión</flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
