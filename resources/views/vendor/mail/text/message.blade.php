<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header>
            SIGLA
        </x-mail::header>
    </x-slot:header>

    {{-- Body --}}
    {{ $slot }}

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            Este es un mensaje automático de SIGLA. Por favor, no respondas a este correo.
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>
