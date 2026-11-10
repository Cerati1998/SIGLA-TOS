<div>
    <x-wire-modal-card
        title="Ordenar secciones"
        name="certification-section-order"
        wire:model="openModal"
        x-on:open="$wire.$refresh()"
    >
        <div class="flex flex-col gap-3.5">
            <p class="m-0 flex gap-2 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
                <x-feather name="info" class="mt-0.5 size-4.5" />
                <span>
                    <span class="max-md:hidden">Arrastra cada sección desde el asa.</span>
                    <span class="md:hidden">Sube o baja cada sección.</span>
                    El orden es el de los pasos de la certificación y se guarda al momento.
                </span>
            </p>

            @if ($sections->isEmpty())
                <p class="m-0 text-sm text-ink-muted">No hay secciones activas para ordenar.</p>
            @else
                <ol wire:sort="sort" class="m-0 flex list-none flex-col gap-2 p-0">
                    @foreach ($sections as $section)
                        <li wire:key="section-{{ $section->id }}" wire:sort:item="{{ $section->id }}" class="flex items-center gap-3 rounded-control border border-line bg-white py-2 ps-3 pe-2 text-sm md:py-2.5">
                            <span wire:sort:handle class="cursor-grab text-ink-muted max-md:hidden" title="Arrastrar" aria-hidden="true">
                                <i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>
                            </span>
                            <span class="inline-flex size-6.5 flex-none items-center justify-center rounded-full bg-table-head font-heading font-medium text-brand">{{ $loop->iteration }}</span>
                            <span class="flex min-w-0 flex-1 flex-col gap-0.5 md:flex-row md:items-center md:justify-between md:gap-3">
                                <span class="break-words">{{ $section->name }}</span>
                                <span class="max-md:text-xs max-md:text-ink-muted md:hidden">{{ $section->type->label() }}</span>
                                <x-status-badge :variant="$section->type->badgeVariant()" class="max-md:hidden">{{ $section->type->label() }}</x-status-badge>
                            </span>
                            <span class="flex flex-none gap-1 md:hidden">
                                <flux:button size="sm" square wire:click="move({{ $section->id }}, -1)" wire:loading.attr="disabled" wire:target="move,sort" :disabled="$loop->first" class="size-11!" aria-label="Subir {{ $section->name }}">
                                    <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>
                                </flux:button>
                                <flux:button size="sm" square wire:click="move({{ $section->id }}, 1)" wire:loading.attr="disabled" wire:target="move,sort" :disabled="$loop->last" class="size-11!" aria-label="Bajar {{ $section->name }}">
                                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                                </flux:button>
                            </span>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>

        <x-slot:footer>
            <div class="flex justify-end">
                <flux:button variant="primary" type="button" x-on:click="close" class="max-md:w-full">Listo</flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
