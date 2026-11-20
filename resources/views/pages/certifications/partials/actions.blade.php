@php
    $status = $certification->status();
    $progress = $certification->progress();
    $missing = collect(['internal' => 'la interna', 'external' => 'la externa'])
        ->filter(fn (string $label, string $key): bool => $progress[$key]['done'] < $progress[$key]['total'])
        ->values()
        ->join(', ', ' y ');
    $wasReopened = $status === \App\Enums\CertificationStatus::Reopened;
    $kitText = $wasReopened
        ? "El kit {$certification->kit->pouch_code} sigue en Usado."
        : "El kit {$certification->kit->pouch_code} y sus {$certification->kit->devices_count} dispositivos pasan a Usado.";
@endphp

<div x-data="{
        id: {{ $certification->id }},
        container: @js($certification->container),
        closeText: @js("La interna ({$progress['internal']['done']}/{$progress['internal']['total']}) y la externa ({$progress['external']['done']}/{$progress['external']['total']}) están completas. {$kitText}"),
    }" class="flex items-center gap-1 [&_a]:max-md:size-11 [&_button]:max-md:size-11">
    <x-wire-button primary :href="route('intranet.certifications.show', $certification)" wire:navigate title="Ver" aria-label="Ver {{ $certification->container }}" xs>
        <i class="fa-solid fa-eye text-sm" aria-hidden="true"></i>
    </x-wire-button>

    @if ($status === \App\Enums\CertificationStatus::Closed)
        <x-wire-button
            primary
            x-on:click="$dispatch('reopen-certification', { certificationId: id })"
            title="Reaperturar"
            aria-label="Reaperturar la certificación de {{ $certification->container }}"
            xs
        >
            <i class="fa-solid fa-lock-open text-sm" aria-hidden="true"></i>
        </x-wire-button>
    @elseif ($certification->inspectionsAreComplete())
        <x-wire-button
            primary
            x-on:click="confirmAction('¿Cerrar la certificación de ' + container + '?', closeText, () => $wire.close(id))"
            wire:loading.attr="disabled"
            wire:target="close"
            title="{{ $wasReopened ? 'Cerrar de nuevo' : 'Cerrar' }}"
            aria-label="{{ $wasReopened ? 'Cerrar de nuevo' : 'Cerrar' }} la certificación de {{ $certification->container }}"
            xs
        >
            <i class="fa-solid fa-lock text-sm" aria-hidden="true"></i>
        </x-wire-button>
    @else
        <x-wire-button
            primary
            disabled
            title="Cerrar: faltan fotos en {{ $missing }}"
            aria-label="Cerrar, no disponible: faltan fotos en {{ $missing }}"
            xs
        >
            <i class="fa-solid fa-lock text-sm" aria-hidden="true"></i>
        </x-wire-button>
    @endif
</div>
