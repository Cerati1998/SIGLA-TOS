<x-layouts::auth title="Crear contraseña nueva">
    <div class="flex flex-col gap-6">
        <x-auth-header title="Crear contraseña nueva" description="Escribe la contraseña nueva dos veces para confirmarla." />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-3.5">
            @csrf
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <flux:input
                name="email"
                label="Correo electrónico"
                :value="old('email', request('email'))"
                type="email"
                required
                autocomplete="email"
                placeholder="correo@empresa.com"
            >
                <x-slot name="icon">
                    <x-feather name="mail" class="size-4 text-ink-muted" />
                </x-slot>
            </flux:input>

            <flux:input
                name="password"
                label="Contraseña nueva"
                type="password"
                required
                autofocus
                autocomplete="new-password"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            >
                <x-slot name="icon">
                    <x-feather name="lock" class="size-4 text-ink-muted" />
                </x-slot>
            </flux:input>

            <flux:input
                name="password_confirmation"
                label="Confirmar contraseña"
                type="password"
                required
                autocomplete="new-password"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            >
                <x-slot name="icon">
                    <x-feather name="lock" class="size-4 text-ink-muted" />
                </x-slot>
            </flux:input>

            <div class="flex justify-center">
                <flux:button variant="primary" type="submit" data-test="reset-password-button">Guardar contraseña</flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
