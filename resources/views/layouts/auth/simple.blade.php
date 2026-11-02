<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-svh bg-surface font-sans text-ink antialiased">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 px-4 py-8">
            <flux:card class="flex w-full max-w-110 flex-col gap-6 border-t-6! border-brand p-8">
                <img src="{{ asset('images/sigla-logo.png') }}" alt="SIGLA, Powered by Macromar" class="h-auto w-55 max-w-full self-center">

                {{ $slot }}
            </flux:card>

            <span class="text-center text-sm text-ink-muted">SIGLA 2.0 · Acceso solo para personal autorizado</span>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
