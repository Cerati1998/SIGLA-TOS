{{-- Fields shared by the create and edit modals: the section (H11) and the security devices it carries (H12). --}}
<div
    x-data="{
        types: @js($deviceTypes),
        typeLabels: @js($types),
        pick: '',
        find(id) {
            return this.types.find((type) => type.id === Number(id)) ?? { name: '', kind: '', trashed: false };
        },
        get rows() {
            return $wire.form.devices ?? [];
        },
        get available() {
            const used = this.rows.map((device) => Number(device.id));

            return this.types.filter((type) => ! type.trashed && ! used.includes(type.id));
        },
        add() {
            if (this.pick === '') {
                return;
            }

            $wire.form.devices.push({ id: Number(this.pick), quantity: 1 });
            this.pick = '';
        },
        remove(index) {
            $wire.form.devices.splice(index, 1);
        },
    }"
    class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_17.5rem]"
>
    <div class="flex min-w-0 flex-col gap-6">
        <section class="flex flex-col gap-4" aria-labelledby="{{ $prefix }}-step-1">
            <x-panel.form-step number="1" id="{{ $prefix }}-step-1" title="Datos de la sección" text="Nombre y tipo, interna o externa." />

            <div class="grid gap-4 lg:ps-10">
                <x-wire-input id="{{ $prefix }}-name" wire:model="form.name" label="Nombre" type="text" maxlength="100" required autofocus autocomplete="off" />

                <fieldset class="m-0 flex flex-col gap-1.75 border-0 p-0">
                    <legend class="mb-1.75 p-0 text-sm font-medium text-ink">Tipo</legend>
                    <div class="grid grid-cols-2 gap-2.5">
                        @foreach ($types as $value => $label)
                            <label for="{{ $prefix }}-type-{{ $value }}" class="flex min-h-11 cursor-pointer items-center gap-2 rounded-control border border-control px-3 py-2 has-checked:border-2 has-checked:border-brand has-checked:bg-badge-info md:min-h-10.25">
                                <input id="{{ $prefix }}-type-{{ $value }}" type="radio" wire:model="form.type" value="{{ $value }}" class="size-4 accent-brand">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <flux:error name="form.type" />
                </fieldset>

                @isset($hint)
                    <span class="text-sm text-ink-muted">{{ $hint }}</span>
                @endisset
            </div>
        </section>

        <section class="flex flex-col gap-4" aria-labelledby="{{ $prefix }}-step-2">
            <x-panel.form-step number="2" id="{{ $prefix }}-step-2" title="¿Qué dispositivos lleva?" text="Lo que el inspector debe colocar en esta sección. Puede no llevar ninguno." />

            @include('pages.certification-sections.devices')
        </section>
    </div>

    @include('pages.certification-sections.inspector-preview')
</div>
