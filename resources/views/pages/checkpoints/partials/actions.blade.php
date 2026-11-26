<div class="flex items-center gap-1 [&_a]:max-md:size-11">
    <x-wire-button primary :href="route('intranet.checkpoints.details', $checkpoint)" wire:navigate title="Ver" aria-label="Ver el checkpoint de {{ $checkpoint->certification->container }}" xs>
        <i class="fa-solid fa-eye text-sm" aria-hidden="true"></i>
    </x-wire-button>
</div>
