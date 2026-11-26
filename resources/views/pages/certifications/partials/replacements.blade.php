{{--
    The devices of the kit replaced by extra devices (H38, criterion 8): when, who, why, in which section, which device
    left and which one came in, and the photo of the discard, opened in the viewer of the detail. The PDF does not
    carry this list.
--}}
<flux:card class="flex flex-col gap-3.5 p-5.25! max-md:p-4!">
    <div class="flex flex-wrap items-baseline justify-between gap-2">
        <h3 class="m-0 font-heading text-h3 font-normal">Dispositivos reemplazados</h3>
        <span class="text-sm text-ink-muted">{{ trans_choice(':count reemplazo|:count reemplazos', $replacements->count()) }}</span>
    </div>

    <div class="overflow-x-auto rounded-card border border-line max-md:hidden">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="bg-table-head text-left text-xs tracking-wider text-ink-muted uppercase">
                    <th class="px-3.5 py-2.5 font-medium whitespace-nowrap">Fecha y hora</th>
                    <th class="px-3.5 py-2.5 font-medium">Sección</th>
                    <th class="px-3.5 py-2.5 font-medium">Sale</th>
                    <th class="px-3.5 py-2.5 font-medium">Entra</th>
                    <th class="px-3.5 py-2.5 font-medium">Motivo</th>
                    <th class="px-3.5 py-2.5 font-medium">Quién</th>
                    <th class="px-3.5 py-2.5 font-medium">Foto</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($replacements as $replacement)
                    @php $replacedAt = $lima($replacement->created_at); @endphp
                    <tr wire:key="replacement-{{ $replacement->id }}" class="border-t border-line align-top even:bg-surface">
                        <td class="px-3.5 py-3 whitespace-nowrap">{{ $replacedAt?->format('d/m/Y') }}<span class="block text-ink-muted">{{ $replacedAt?->format('H:i') }}</span></td>
                        <td class="px-3.5 py-3">{{ $replacement->step->position }} · {{ $replacement->step->section->name }}<span class="block text-ink-muted">{{ $replacement->step->type->label() }}</span></td>
                        <td class="px-3.5 py-3"><strong class="whitespace-nowrap text-badge-danger-ink">{{ $replacement->removedDevice->serial_number }}</strong><span class="block text-ink-muted">{{ $replacement->removedDevice->lot->securityDeviceType->name }} · Descartado</span></td>
                        <td class="px-3.5 py-3"><strong class="whitespace-nowrap text-badge-success-ink">{{ $replacement->installedDevice->serial_number }}</strong><span class="block text-ink-muted">{{ $replacement->bulkDispatch->number() }}</span></td>
                        <td class="min-w-56 px-3.5 py-3 break-words">{{ $replacement->reason }}</td>
                        <td class="px-3.5 py-3 whitespace-nowrap">{{ $replacement->replacer->name }}</td>
                        <td class="px-3.5 py-3">
                            @if ($replacement->removedDevice->discard_photo_path !== null)
                                <x-wire-button xs primary title="Ver la foto del descarte" aria-label="Ver la foto del descarte de {{ $replacement->removedDevice->serial_number }}"
                                    x-on:click="show({{ \Illuminate\Support\Js::from(route('intranet.security-devices.discard-photo', $replacement->removedDevice)) }}, {{ \Illuminate\Support\Js::from($replacement->replacer->name) }}, {{ \Illuminate\Support\Js::from($replacedAt?->format('d/m/Y H:i')) }})">
                                    <i class="fa-solid fa-eye text-sm" aria-hidden="true"></i>
                                </x-wire-button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex flex-col md:hidden">
        @foreach ($replacements as $replacement)
            @php $replacedAt = $lima($replacement->created_at); @endphp
            <article wire:key="replacement-card-{{ $replacement->id }}" class="flex flex-col gap-2.5 border-t border-line py-3.5 first:border-t-0">
                <div class="flex items-center justify-between gap-2">
                    <strong class="text-base">{{ $replacement->step->position }} · {{ $replacement->step->section->name }}</strong>
                    <x-status-badge :variant="$replacement->step->type === \App\Enums\CertificationSectionType::Internal ? 'purple' : 'info'">{{ $replacement->step->type->label() }}</x-status-badge>
                </div>
                <div class="grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-center gap-2" aria-label="Sale {{ $replacement->removedDevice->serial_number }}, entra {{ $replacement->installedDevice->serial_number }}">
                    <span class="flex min-w-0 flex-col gap-0.5">
                        <span class="text-xs tracking-wider text-ink-muted uppercase">Sale · Descartado</span>
                        <strong class="font-heading text-base font-medium break-words text-badge-danger-ink">{{ $replacement->removedDevice->serial_number }}</strong>
                    </span>
                    <i class="fa-solid fa-arrow-right text-ink-muted" aria-hidden="true"></i>
                    <span class="flex min-w-0 flex-col gap-0.5">
                        <span class="text-xs tracking-wider text-ink-muted uppercase">Entra · {{ $replacement->bulkDispatch->number() }}</span>
                        <strong class="font-heading text-base font-medium break-words text-badge-success-ink">{{ $replacement->installedDevice->serial_number }}</strong>
                    </span>
                </div>
                <p class="m-0 text-base break-words">«{{ $replacement->reason }}»</p>
                @if ($replacement->removedDevice->discard_photo_path !== null)
                    <button type="button" x-on:click="show(@js(route('intranet.security-devices.discard-photo', $replacement->removedDevice)), @js($replacement->replacer->name), @js($replacedAt?->format('d/m/Y H:i')))"
                        class="flex min-h-11 items-center gap-2.5 rounded-control border border-line bg-white py-1.5 pr-2.5 pl-1.5 text-left text-base font-medium text-brand">
                        <img src="{{ route('intranet.security-devices.discard-photo.thumbnail', $replacement->removedDevice) }}" alt="" loading="lazy" class="size-10 flex-none rounded object-cover">
                        Ver foto del descarte
                    </button>
                @endif
                <span class="text-sm text-ink-muted">{{ $replacement->removedDevice->lot->securityDeviceType->name }} · el {{ $replacedAt?->format('d/m/Y') }} a las {{ $replacedAt?->format('H:i') }} por <strong class="text-ink">{{ $replacement->replacer->name }}</strong></span>
            </article>
        @endforeach
    </div>

    <span class="text-sm text-ink-muted">El PDF no lleva esta lista: muestra el dispositivo nuevo en su sección.</span>
</flux:card>
