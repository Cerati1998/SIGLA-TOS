<div x-data="securityDeviceLotApproval">
    <x-wire-modal-card
        title="Registrar aprobación de la naviera"
        name="security-device-lot-approve"
        wire:model="form.openModal"
        x-on:open="startApproval"
        x-on:close="forgetApproval"
    >
        <form id="security-device-lot-approve-form" wire:submit="save" class="grid gap-4">
            <p role="note" class="m-0 flex gap-2.5 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
                <x-feather name="info" class="mt-0.5 size-4.5 shrink-0" />
                <span>Registra solo el tramo que la naviera ya aprobó por correo. Sus dispositivos pasan de Inactivo a Disponible. <strong>Una aprobación no se deshace.</strong></span>
            </p>

            <div class="grid gap-4 sm:grid-cols-2" x-on:input.debounce.500ms="checkStretch">
                <x-wire-input id="security-device-lot-approve-start" wire:model="form.correlativeStart" label="Correlativo inicial" type="text" inputmode="numeric" maxlength="18" required autocomplete="off" />
                <x-wire-input id="security-device-lot-approve-end" wire:model="form.correlativeEnd" label="Correlativo final" type="text" inputmode="numeric" maxlength="18" required autocomplete="off" />
            </div>

            <span class="-mt-2 text-sm text-ink-muted">Dentro del lote: del {{ $form->seriesStart }} al {{ $form->seriesEnd }}.</span>

            @if ($preview !== null)
                <p role="status" x-show="isComplete" class="m-0 flex gap-2.5 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
                    <x-feather name="info" class="mt-0.5 size-4.5 shrink-0" />
                    @if ($preview['toApprove'] > 0)
                        <span>
                            <strong>{{ number_format($preview['toApprove'], 0, ',', ' ') }} {{ $preview['toApprove'] === 1 ? 'dispositivo pasa' : 'dispositivos pasan' }} a Disponible</strong>
                            · primera serie <strong>{{ $preview['firstSerialNumber'] }}</strong>
                            · última serie <strong>{{ $preview['lastSerialNumber'] }}</strong>
                            @if ($preview['alreadyApproved'] > 0)
                                <br>{{ number_format($preview['alreadyApproved'], 0, ',', ' ') }} del tramo ya {{ $preview['alreadyApproved'] === 1 ? 'estaba aprobado' : 'estaban aprobados' }}: se dejan como están.
                            @endif
                        </span>
                    @else
                        <span><strong>0 pasan a Disponible.</strong> {{ $preview['alreadyApproved'] === 1 ? 'El dispositivo del tramo ya estaba aprobado.' : 'Los '.number_format($preview['alreadyApproved'], 0, ',', ' ').' dispositivos del tramo ya estaban aprobados.' }}</span>
                    @endif
                </p>
            @endif
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3 max-md:flex-col-reverse">
                <flux:button type="button" x-on:click="close">Cancelar</flux:button>
                <flux:button
                    variant="primary"
                    type="submit"
                    form="security-device-lot-approve-form"
                    :disabled="$preview === null || $preview['toApprove'] === 0"
                    wire:loading.attr="disabled"
                    wire:target="save"
                >
                    <span wire:loading.remove wire:target="save">
                        {{ $preview !== null && $preview['toApprove'] > 0 ? 'Aprobar '.number_format($preview['toApprove'], 0, ',', ' ').($preview['toApprove'] === 1 ? ' dispositivo' : ' dispositivos') : 'Aprobar' }}
                    </span>
                    <span wire:loading wire:target="save">Aprobando…</span>
                </flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>

    @script
    <script>
        /**
         * The window «Registrar aprobación de la naviera» (H29) in the browser. The server checks the stretch and
         * previews it, because only the server knows which devices were approved already. It is asked only when the
         * stretch has something new to show: when both correlatives are written and changed, or once when a checked
         * stretch is left incomplete, to clear its preview and its errors. While the user keeps typing an incomplete
         * stretch, nothing is sent.
         */
        Alpine.data('securityDeviceLotApproval', () => ({
            /**
             * The last stretch the server checked, as "start-end", or null when it checked none or an incomplete one.
             */
            checkedStretch: null,

            get stretch() {
                const start = String($wire.form.correlativeStart ?? '').trim();
                const end = String($wire.form.correlativeEnd ?? '').trim();

                return start !== '' && end !== '' ? `${start}-${end}` : null;
            },

            get isComplete() {
                return this.stretch !== null;
            },

            startApproval() {
                $wire.form.correlativeStart = '';
                $wire.form.correlativeEnd = '';
                this.checkedStretch = null;
            },

            forgetApproval() {
                if ($wire.$errors.any()) {
                    $wire.clearErrors();
                }
            },

            checkStretch() {
                if (this.stretch === this.checkedStretch) {
                    return;
                }

                this.checkedStretch = this.stretch;
                $wire.$commit();
            },
        }));
    </script>
    @endscript
</div>
