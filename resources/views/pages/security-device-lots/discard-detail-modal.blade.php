@php
    $device = $this->device;
@endphp

<div x-data="{ viewerOpen: false }">
    <x-wire-modal-card :title="$device === null ? 'Descarte' : 'Descarte de '.$device->serial_number" wire:model="openModal">
        @if ($device !== null)
            <div class="grid gap-4">
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs tracking-wider text-ink-muted uppercase">Motivo</span>
                    <p class="m-0 text-base break-words">{{ $device->discard_reason ?? '—' }}</p>
                </div>

                @if ($device->discard_thumbnail_path !== null)
                    <button type="button" x-on:click="viewerOpen = true" class="block cursor-zoom-in overflow-hidden rounded-card" aria-label="Ver la foto en grande">
                        <img src="{{ route('intranet.security-devices.discard-photo.thumbnail', $device) }}" alt="Foto del descarte de {{ $device->serial_number }}" class="h-48 w-full object-cover">
                    </button>
                @else
                    <p class="m-0 rounded-control bg-surface p-3.5 text-sm text-ink-muted">Este descarte no tiene foto.</p>
                @endif

                <p class="m-0 text-sm text-ink-muted">
                    Lo descartó <strong class="text-ink">{{ $device->discarder?->name ?? '—' }}</strong>
                    @if ($device->discarded_at !== null)
                        el {{ $device->discarded_at->timezone('America/Lima')->format('d/m/Y') }} a las {{ $device->discarded_at->timezone('America/Lima')->format('H:i') }}.
                    @endif
                </p>
            </div>
        @endif

        <x-slot:footer>
            <div class="flex justify-end">
                <flux:button type="button" x-on:click="close" class="max-md:h-11">Cerrar</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>

    @if ($device?->discard_photo_path !== null)
        <template x-teleport="body">
            <div x-show="viewerOpen" x-cloak role="dialog" aria-modal="true" aria-label="Foto del descarte de {{ $device->serial_number }}" x-on:keydown.escape.window="viewerOpen = false" class="fixed inset-0 z-70 flex flex-col bg-ink text-white">
                <div class="flex h-16 flex-none items-center justify-between px-4">
                    <strong class="font-heading text-lg font-medium">{{ $device->serial_number }}</strong>
                    <button type="button" x-on:click="viewerOpen = false" class="inline-flex size-11 items-center justify-center rounded-full bg-white/15" aria-label="Cerrar la foto">
                        <i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="flex min-h-0 flex-1 items-center justify-center bg-black p-2">
                    <img src="{{ route('intranet.security-devices.discard-photo', $device) }}" alt="Foto del descarte de {{ $device->serial_number }}" class="max-h-full max-w-full object-contain" loading="lazy">
                </div>
                <div class="flex flex-none justify-center p-4 pb-6">
                    <flux:button variant="primary" x-on:click="viewerOpen = false" class="max-md:h-11">Cerrar</flux:button>
                </div>
            </div>
        </template>
    @endif
</div>
