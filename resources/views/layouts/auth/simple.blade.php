<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-svh bg-brand font-sans text-ink antialiased">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 px-4 py-8">
            <flux:card class="flex w-full max-w-135 flex-col gap-6 p-10 shadow-auth!">
                <img src="{{ asset('images/sigla-logo2.png') }}" alt="SIGLA, Powered by Macromar" class="h-auto w-50 max-w-full self-center">

                {{ $slot }}
            </flux:card>

            <span class="text-center text-sm text-white">SIGLA 2.0 · Acceso solo para personal autorizado</span>
        </div>

        @persist('toast')
            <flux:toast.group position="top end">
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
