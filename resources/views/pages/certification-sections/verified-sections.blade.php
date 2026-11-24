{{-- The internal or external sections a checkpoint section of verification shows (H33). Rows are added and removed in the browser. --}}
<div class="flex flex-col gap-2 lg:ps-10">
    <template x-for="(id, index) in links" x-bind:key="id">
        <div
            class="flex flex-wrap items-center gap-3 rounded-card border-2 px-3 py-2.5"
            x-bind:class="findSection(id).trashed ? 'border-dashed border-control bg-surface' : 'border-brand bg-badge-info'"
        >
            <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                <span class="text-sm font-semibold" x-bind:class="findSection(id).trashed ? 'text-ink-muted' : 'text-ink'" x-text="findSection(id).name"></span>
                <span
                    class="text-xs text-ink-muted"
                    x-text="findSection(id).trashed ? 'Desactivada: se siguen mostrando las series de las certificaciones que la tengan. Si la quitas, ya no podrás volver a elegirla.' : findSection(id).carries"
                ></span>
            </span>

            <x-status-badge variant="warning" x-show="findSection(id).trashed">Desactivada</x-status-badge>
            <x-status-badge variant="info" x-show="! findSection(id).trashed" x-text="findSection(id).typeLabel"></x-status-badge>

            <x-wire-button negative x-on:click="removeSection(index)" title="Quitar" x-bind:aria-label="'Quitar ' + findSection(id).name" class="max-md:size-11" xs>
                <i class="fa-solid fa-trash text-sm" aria-hidden="true"></i>
            </x-wire-button>
        </div>
    </template>

    <div x-show="links.length === 0" class="flex items-center gap-3 rounded-card border-2 border-dashed border-control p-4 text-sm text-ink-muted">
        <x-feather name="check-square" class="size-5.5" />
        Todavía no verifica ninguna sección: agrega al menos una.
    </div>

    @foreach (collect($errors->get('form.verifiedSectionIds*'))->flatten()->unique() as $message)
        <p class="text-sm text-danger">{{ $message }}</p>
    @endforeach

    <div class="flex flex-wrap items-end gap-2 pt-1">
        <div class="flex min-w-0 flex-1 flex-col gap-1.5">
            <label for="{{ $prefix }}-add-section" class="text-sm font-medium text-ink">Agregar una sección de la certificación</label>
            <select
                id="{{ $prefix }}-add-section"
                x-model="sectionPick"
                class="h-8.5 w-full rounded-control border border-control bg-white px-2.5 text-base text-ink max-md:h-11"
            >
                <option value="">Elige una sección interna o externa</option>
                <optgroup label="Internas">
                    <template x-for="section in availableSections('internal')" x-bind:key="section.id">
                        <option x-bind:value="section.id" x-text="section.name"></option>
                    </template>
                </optgroup>
                <optgroup label="Externas">
                    <template x-for="section in availableSections('external')" x-bind:key="section.id">
                        <option x-bind:value="section.id" x-text="section.name"></option>
                    </template>
                </optgroup>
            </select>
        </div>

        <flux:button type="button" x-on:click="addSection()" x-bind:disabled="sectionPick === ''" class="max-md:w-full">
            <i class="fa-solid fa-plus text-sm" aria-hidden="true"></i>
            Agregar
        </flux:button>
    </div>

    <p class="text-sm text-ink-muted">Solo secciones internas o externas activas que todavía no están enlazadas. Al menos una.</p>
</div>
