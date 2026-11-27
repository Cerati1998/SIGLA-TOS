{{-- Fields shared by the create and edit modals: the section (H11), the security devices it carries (H12) and, for a checkpoint section, what it asks for and the sections it verifies (H33). --}}
<div
    x-data="{
        types: @js($deviceTypes),
        typeLabels: @js($types),
        sections: @js($verifiableSections),
        pick: '',
        sectionPick: '',
        find(id) {
            return this.types.find((type) => type.id === Number(id)) ?? { name: '', kind: '', splits: false, trashed: false };
        },
        nameOf(type) {
            const side = ['internal', 'external'].includes($wire.form.type) ? this.typeLabels[$wire.form.type] : null;

            return type.splits && side ? type.name + ' · ' + side : type.name;
        },
        findSection(id) {
            return this.sections.find((section) => section.id === Number(id)) ?? { name: '', typeLabel: '', carries: '', trashed: false };
        },
        get isCheckpoint() {
            return $wire.form.type === 'checkpoint';
        },
        get isVerification() {
            return this.isCheckpoint && $wire.form.checkpointKind === 'verification';
        },
        get rows() {
            return $wire.form.devices ?? [];
        },
        get links() {
            return $wire.form.verifiedSectionIds ?? [];
        },
        get available() {
            const used = this.rows.map((device) => Number(device.id));

            return this.types.filter((type) => ! type.trashed && ! used.includes(type.id));
        },
        availableSections(type) {
            const used = this.links.map((id) => Number(id));

            return this.sections.filter((section) => section.type === type && ! section.trashed && ! used.includes(section.id));
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
        addSection() {
            if (this.sectionPick === '') {
                return;
            }

            $wire.form.verifiedSectionIds.push(Number(this.sectionPick));
            this.sectionPick = '';
        },
        removeSection(index) {
            $wire.form.verifiedSectionIds.splice(index, 1);
        },
    }"
    class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_17.5rem]"
>
    <div class="flex min-w-0 flex-col gap-6">
        <section class="flex flex-col gap-4" aria-labelledby="{{ $prefix }}-step-1">
            <x-panel.form-step number="1" id="{{ $prefix }}-step-1" title="Datos de la sección" text="Nombre y tipo: interna, externa o checkpoint." />

            <div class="grid gap-4 lg:ps-10">
                <x-wire-input id="{{ $prefix }}-name" wire:model="form.name" label="Nombre" type="text" maxlength="100" required autofocus autocomplete="off" />

                <fieldset class="m-0 flex flex-col gap-1.75 border-0 p-0">
                    <legend class="mb-1.75 p-0 text-sm font-medium text-ink">Tipo</legend>
                    <div class="grid gap-2.5 sm:grid-cols-3">
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

        <section x-show="! isCheckpoint" class="flex flex-col gap-4" aria-labelledby="{{ $prefix }}-step-2">
            <x-panel.form-step number="2" id="{{ $prefix }}-step-2" title="¿Qué dispositivos lleva?" text="Lo que el inspector debe colocar en esta sección. Puede no llevar ninguno." />

            @include('pages.certification-sections.devices')
        </section>

        <section x-show="isCheckpoint" x-cloak class="flex flex-col gap-4" aria-labelledby="{{ $prefix }}-step-kind">
            <x-panel.form-step number="2" id="{{ $prefix }}-step-kind" title="¿Qué pide este paso?" text="Solo para secciones de checkpoint. Las dos son obligatorias." />

            <fieldset class="m-0 flex flex-col gap-1.75 border-0 p-0 lg:ps-10">
                <legend class="sr-only">Qué pide el paso</legend>
                <div class="grid gap-2.5 sm:grid-cols-2">
                    @foreach ($kinds as $kind)
                        <label for="{{ $prefix }}-kind-{{ $kind->value }}" class="flex min-h-11 cursor-pointer items-start gap-2.5 rounded-card border border-control p-3 has-checked:border-2 has-checked:border-brand has-checked:bg-badge-info">
                            <input id="{{ $prefix }}-kind-{{ $kind->value }}" type="radio" wire:model="form.checkpointKind" value="{{ $kind->value }}" class="mt-1 size-4 accent-brand">
                            <span class="flex flex-col gap-0.5">
                                <span class="font-semibold text-ink">{{ $kind->label() }}</span>
                                <span class="text-[13px] text-ink-muted">{{ $kind->description() }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <flux:error name="form.checkpointKind" />
            </fieldset>
        </section>

        <section x-show="isVerification" x-cloak class="flex flex-col gap-4" aria-labelledby="{{ $prefix }}-step-verifies">
            <x-panel.form-step number="3" id="{{ $prefix }}-step-verifies" title="¿Qué secciones verifica?" text="Se muestran las series que la certificación dejó en estas secciones." />

            @include('pages.certification-sections.verified-sections')
        </section>

        <section x-show="isVerification" x-cloak class="flex flex-col gap-4" aria-labelledby="{{ $prefix }}-step-reinspection">
            <x-panel.form-step number="4" id="{{ $prefix }}-step-reinspection" title="¿Una ✗ pide reinspección?" text="Solo para secciones de verificación." />

            <label for="{{ $prefix }}-skips-reinspection" class="flex cursor-pointer items-start gap-2.5 rounded-card border border-control p-3 has-checked:border-2 has-checked:border-brand has-checked:bg-badge-info lg:ms-10">
                <input id="{{ $prefix }}-skips-reinspection" type="checkbox" wire:model="form.skipsReinspection" class="mt-1 size-4.5 flex-none accent-brand">
                <span class="flex flex-col gap-0.5">
                    <span class="font-semibold text-ink">Una ✗ aquí no pide reinspección</span>
                    <span class="text-[13px] text-ink-muted">El checkpoint se cierra igual, con una observación obligatoria. Por ejemplo, en «Verificación de cinta de puerta».</span>
                </span>
            </label>
        </section>
    </div>

    @include('pages.certification-sections.inspector-preview')
</div>
