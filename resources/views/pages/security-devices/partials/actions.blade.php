@if ($device->status === \App\Enums\SecurityDeviceStatus::Discarded)
    <div x-data="{ id: {{ $device->id }} }" class="flex items-center gap-1 [&_button]:max-md:size-11">
        <x-wire-button primary x-on:click="$dispatch('show-security-device-discard', { securityDeviceId: id })" title="Ver descarte" aria-label="Ver descarte" xs>
            <i class="fa-solid fa-eye text-sm" aria-hidden="true"></i>
        </x-wire-button>
    </div>
@elseif ($device->status->canBeDiscarded() && $device->kits->isEmpty())
    <div x-data="{ id: {{ $device->id }} }" class="flex items-center gap-1 [&_button]:max-md:size-11">
        <x-wire-button negative x-on:click="$dispatch('discard-security-device', { securityDeviceId: id })" title="Descartar" aria-label="Descartar" xs>
            <i class="fa-solid fa-ban text-sm" aria-hidden="true"></i>
        </x-wire-button>
    </div>
@endif
