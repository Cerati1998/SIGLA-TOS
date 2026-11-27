<div>
    <x-wire-modal-card :title="$gpsDeviceId === null ? 'Conectividad' : 'Conectividad de '.$this->gpsDevice->code" wire:model="openModal">
        @if ($gpsDeviceId !== null)
            @php
                $gpsDevice = $this->gpsDevice;
                $connectivity = $gpsDevice->connectivity();
                $timezone = config('app.display_timezone');
            @endphp

            <div class="grid gap-4">
                <h4 class="m-0 font-heading text-base font-normal text-brand">Dispositivo</h4>
                <dl class="m-0 grid grid-cols-2 gap-x-4 gap-y-3">
                    @foreach ([
                        'Código' => $gpsDevice->code,
                        'ID de 24HM' => $gpsDevice->monitoring_asset_id ?? '—',
                        'Modelo' => $gpsDevice->gpsDeviceModel->name.' · '.$gpsDevice->gpsDeviceModel->brand->business_name,
                        'Propiedad' => $gpsDevice->ownership->label().($gpsDevice->rentalProvider !== null ? ' · '.$gpsDevice->rentalProvider->business_name : ''),
                        'Sucursal' => $gpsDevice->branch->name,
                    ] as $term => $value)
                        <div class="flex min-w-0 flex-col gap-0.5">
                            <dt class="text-xs tracking-wide text-ink-muted uppercase">{{ $term }}</dt>
                            <dd class="m-0 break-words">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>

                <hr class="m-0 border-line">

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h4 class="m-0 font-heading text-base font-normal text-brand">Último reporte</h4>
                    <x-status-badge :variant="$connectivity->badge()" class="font-medium">{{ $connectivity->label() }}</x-status-badge>
                </div>

                @if ($gpsDevice->monitoring_asset_id === null)
                    <p role="note" class="m-0 flex gap-2.5 rounded-control bg-badge-info p-3.5 text-sm text-badge-info-ink">
                        <x-feather name="info" class="mt-0.5 size-4.5 shrink-0" />
                        <span>Este dispositivo no tiene ID de 24HM. Agrégalo en su edición para poder verificarlo.</span>
                    </p>
                @endif

                @if ($failure !== null)
                    <p role="alert" class="m-0 flex gap-2.5 rounded-control bg-badge-danger p-3.5 text-sm text-badge-danger-ink">
                        <x-feather name="alert-circle" class="mt-0.5 size-4.5 shrink-0" />
                        <span>No se pudo verificar: {{ $failure }}</span>
                    </p>
                @endif

                @if ($gpsDevice->last_reported_at === null)
                    <p class="m-0 text-sm text-ink-muted">Todavía no se consultó a 24HM. Pulsa «Verificar ahora».</p>
                @else
                    <p class="m-0">
                        Reportó {{ $gpsDevice->last_reported_at->locale('es')->diffForHumans() }}
                        <span class="text-sm text-ink-muted">· {{ $gpsDevice->last_reported_at->timezone($timezone)->format('d/m/Y H:i') }}</span>
                    </p>
                    <dl class="m-0 grid grid-cols-2 gap-x-4 gap-y-3">
                        @foreach ([
                            'Dirección' => $gpsDevice->last_address ?? '—',
                            'Posición' => $gpsDevice->last_latitude === null ? '—' : $gpsDevice->last_latitude.', '.$gpsDevice->last_longitude,
                            'Voltaje' => $gpsDevice->supply_voltage === null ? '—' : $gpsDevice->supply_voltage.' V',
                            'Estado de servicio' => $gpsDevice->service_status ?? '—',
                            'Estado operativo' => $gpsDevice->operational_status ?? '—',
                        ] as $term => $value)
                            <div @class(['flex min-w-0 flex-col gap-0.5', 'col-span-2' => $term === 'Dirección'])>
                                <dt class="text-xs tracking-wide text-ink-muted uppercase">{{ $term }}</dt>
                                <dd class="m-0 break-words">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                @if ($gpsDevice->last_checked_at !== null)
                    <p class="m-0 text-sm text-ink-muted">Verificado con 24HM el {{ $gpsDevice->last_checked_at->timezone($timezone)->format('d/m/Y') }} a las {{ $gpsDevice->last_checked_at->timezone($timezone)->format('H:i') }}.</p>
                @endif
            </div>
        @endif

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <flux:button type="button" x-on:click="close">Cerrar</flux:button>
                <flux:button
                    variant="primary"
                    icon="arrow-path"
                    wire:click="verify"
                    wire:loading.attr="disabled"
                    wire:target="verify"
                    :disabled="$gpsDeviceId === null || $this->gpsDevice->monitoring_asset_id === null"
                >
                    <span wire:loading.remove wire:target="verify">Verificar ahora</span>
                    <span wire:loading wire:target="verify">Verificando…</span>
                </flux:button>
            </div>
        </x-slot:footer>
    </x-wire-modal-card>
</div>
