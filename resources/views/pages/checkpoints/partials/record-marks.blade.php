{{--
    The series of a verification step in the detail of a checkpoint (H36, criterion 3): each one with its section, its
    mark and who marked it and when. A series a reinspection replaced is crossed out, with the new one right after it.
    A table on the desktop and a list on the phone.
--}}
@php
    $markText = fn ($mark): array => match ($mark->is_correct) {
        true => ['class' => 'text-badge-success-ink', 'icon' => 'check', 'label' => 'Correcta'],
        false => ['class' => 'text-badge-danger-ink', 'icon' => 'x', 'label' => 'Incorrecta'],
        default => ['class' => 'text-ink-muted', 'icon' => 'minus', 'label' => 'Sin marcar'],
    };
    $who = function (array $row) use ($lima): string {
        $mark = $row['mark'];
        $parts = $mark->marked_at === null ? [] : [($mark->marker?->name ?? '—').' · '.$lima($mark->marked_at)?->format('H:i')];

        if ($row['superseded']) {
            $parts[] = 'reemplazada';
        }

        if ($row['round'] !== null) {
            $parts[] = 'reinspección '.$row['round'];
        }

        return $parts === [] ? '—' : implode(' · ', $parts);
    };
@endphp

@if ($rows === [])
    <span class="text-sm text-ink-muted">Sin series que verificar.</span>
@else
    <div class="overflow-x-auto rounded-card border border-line max-md:hidden">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="bg-table-head text-left text-xs tracking-wider text-ink-muted uppercase">
                    <th class="px-3.5 py-2.5 font-medium">Serie</th>
                    <th class="px-3.5 py-2.5 font-medium">Sección</th>
                    <th class="px-3.5 py-2.5 font-medium">Marca</th>
                    <th class="px-3.5 py-2.5 font-medium">Quién y cuándo</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    @php $text = $markText($row['mark']); @endphp
                    <tr wire:key="mark-{{ $row['mark']->id }}" class="border-t border-line">
                        <td class="px-3.5 py-2.5 font-heading font-medium whitespace-nowrap">
                            @if ($row['superseded'])<s>{{ $row['mark']->device->serial_number }}</s>@else{{ $row['mark']->device->serial_number }}@endif
                        </td>
                        <td class="px-3.5 py-2.5">{{ $deviceSections[$row['mark']->security_device_id] ?? '—' }}</td>
                        <td class="px-3.5 py-2.5 whitespace-nowrap"><span class="inline-flex items-center gap-1.5 font-semibold {{ $text['class'] }}"><x-feather :name="$text['icon']" class="size-4" />{{ $text['label'] }}</span></td>
                        <td class="px-3.5 py-2.5">{{ $who($row) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <ul class="m-0 flex list-none flex-col p-0 md:hidden" aria-label="Series">
        @foreach ($rows as $row)
            @php $text = $markText($row['mark']); @endphp
            <li wire:key="mark-phone-{{ $row['mark']->id }}" class="flex items-center justify-between gap-2 border-t border-line py-2 text-sm first:border-t-0">
                <span class="min-w-0">
                    <b class="font-heading text-base font-medium">@if ($row['superseded'])<s>{{ $row['mark']->device->serial_number }}</s>@else{{ $row['mark']->device->serial_number }}@endif</b>
                    <small class="block text-xs text-ink-muted">{{ $deviceSections[$row['mark']->security_device_id] ?? '—' }} · {{ $who($row) }}</small>
                </span>
                <span class="inline-flex flex-none items-center gap-1 font-semibold {{ $text['class'] }}"><x-feather :name="$text['icon']" class="size-4" />{{ $text['label'] }}</span>
            </li>
        @endforeach
    </ul>
@endif
