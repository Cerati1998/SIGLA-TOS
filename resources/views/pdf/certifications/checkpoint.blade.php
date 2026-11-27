{{--
    The checkpoint at the end of the PDF of the certification, only once it is closed (T94): its data, each series it
    verified with its final result, a box for each reinspection and the photos of its steps, the extra ones and the one
    of the container, in the format of the photos of the inspection.
--}}
@php
    $record = $checkpoint['checkpoint'];
    $at = fn ($date): string => $date === null ? '—' : $lima($date)->format('d/m/Y H:i');
    $verificationSteps = $checkpoint['steps']->filter(fn ($step) => $step->isVerification());
    $photoSteps = $checkpoint['steps']->reject(fn ($step) => $step->isVerification());
    $reinspections = $checkpoint['reinspections'];
    $result = fn ($mark): array => match ($mark->is_correct) {
        true => ['color' => '#004f3b', 'label' => '✓ Correcta'],
        false => ['color' => '#9f0712', 'label' => '✗ Incorrecta'],
        default => ['color' => '#4b5563', 'label' => 'Sin marcar'],
    };
    $rows = $verificationSteps->flatMap(fn ($step) => collect($checkpoint['markRows'][$step->id] ?? [])
        ->reject(fn (array $row): bool => $row['superseded'])
        ->map(fn (array $row): array => [...$row, 'step' => $step]))
        ->values();
@endphp

<div style="page-break-before: always;">
    <table class="cabecera">
        <tr>
            <td style="width: 150px; height: 34px; padding: 6px;"><img src="{{ $logo }}" alt="SIGLA, Powered by Macromar" style="max-height: 30px; max-width: 130px;"></td>
            <td style="padding: 6px;"><span style="font-size: 12px; font-weight: bold; text-transform: uppercase;">Certificación de contenedor · Checkpoint</span></td>
            <td style="width: 150px; padding: 6px; background: #f3f5f9;"><span style="font-size: 14px; font-weight: bold; color: #002f7f;">{{ $certification->container }}</span></td>
        </tr>
    </table>

    <p class="seccion" style="margin-top: 12px;">Checkpoint</p>
    <table class="pareja" style="margin-top: 0;">
        <tr>
            <td>
                <table class="datos">
                    <tr><td class="rotulo">Estado:</td><td style="font-weight: bold;">{{ $record->status()->label() }}</td></tr>
                    <tr><td class="rotulo">Iniciado:</td><td>{{ $at($record->created_at) }} · {{ $record->starter?->name ?? '—' }}</td></tr>
                    <tr><td class="rotulo">Cerrado:</td><td>{{ $at($record->closed_at) }} · {{ $record->closer?->name ?? '—' }}</td></tr>
                    <tr><td class="rotulo">Pasos:</td><td>{{ $checkpoint['steps']->count() }} · {{ trans_choice(':count foto|:count fotos', $checkpoint['photos']) }} · {{ trans_choice(':count serie|:count series', $checkpoint['series']) }}</td></tr>
                </table>
            </td>
            <td>
                <table class="datos">
                    <tr><td class="rotulo">Observación:</td><td>{{ filled($record->observations) ? '«'.$record->observations.'»' : '—' }}</td></tr>
                    <tr><td class="rotulo">Reinspecciones:</td><td>{{ $reinspections->isEmpty() ? 'Ninguna' : $reinspections->count() }}</td></tr>
                    <tr><td class="rotulo">Fotos extra:</td><td>{{ $checkpoint['extraPhotos']->count() }}</td></tr>
                    <tr><td class="rotulo">Foto del contenedor:</td><td>{{ $checkpoint['containerPhoto'] === null ? 'No' : 'Sí' }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <p class="seccion" style="margin-top: 14px;">Series verificadas</p>
    <table class="lista">
        <thead><tr><th>Paso</th><th>Serie</th><th>Sección de la certificación</th><th>Resultado</th><th>Quién y cuándo</th></tr></thead>
        <tbody>
            @forelse ($rows as $row)
                @php
                    $mark = $row['mark'];
                    $text = $result($mark);
                @endphp
                <tr @class(['par' => $loop->even])>
                    <td>{{ $row['step']->position }} · {{ $row['step']->section->name }}</td>
                    <td>{{ $mark->device->serial_number }}</td>
                    <td>{{ $checkpoint['deviceSections'][$mark->security_device_id] ?? '—' }}</td>
                    <td><span style="font-weight: bold; color: {{ $text['color'] }};">{{ $text['label'] }}</span>@if ($mark->is_correct === false && $row['step']->skips_reinspection) · sin reinspección @endif</td>
                    <td>{{ $mark->marker?->name ?? '—' }} · {{ $at($mark->marked_at) }}@if ($row['round'] !== null) · reinspección {{ $row['round'] }}@endif</td>
                </tr>
            @empty
                <tr><td colspan="5">Sin series que verificar.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if ($reinspections->isNotEmpty())
        <p class="seccion" style="margin-top: 14px;">Reinspecciones</p>
        @foreach ($reinspections as $reinspection)
            @php
                $sections = $reinspection->devices->map(fn ($device) => $device->step->section->name)->unique()->join(', ', ' y ');
                $sectionCount = $reinspection->devices->pluck('certification_step_id')->unique()->count();
            @endphp
            <div class="caja sin-corte" style="padding: 8px 12px; margin-bottom: 10px;">
                <div style="padding-bottom: 5px; margin-bottom: 6px; border-bottom: 1px solid #c9ced6;">
                    <span style="font-size: 11px; font-weight: bold;">Reinspección {{ $checkpoint['rounds'][$reinspection->id] ?? $loop->iteration }}</span>
                    <span style="font-size: 9px; color: #4b5563; margin-left: 8px;">{{ $reinspection->isClosed() ? 'Cerrada' : 'Abierta' }}</span>
                </div>
                <table class="datos">
                    <tr><td class="rotulo">Motivo:</td><td>«{{ $reinspection->reason }}»</td></tr>
                    <tr><td class="rotulo">Iniciada:</td><td>{{ $at($reinspection->created_at) }} · {{ $reinspection->starter->name }}, en el checkpoint</td></tr>
                    <tr><td class="rotulo">{{ $sectionCount === 1 ? 'Sección reabierta:' : 'Secciones reabiertas:' }}</td><td>{{ $sections === '' ? '—' : $sections }}</td></tr>
                    <tr>
                        <td class="rotulo">{{ $reinspection->devices->count() === 1 ? 'Reemplazo:' : 'Reemplazos:' }}</td>
                        <td>
                            @foreach ($reinspection->devices as $device)
                                <div><s>{{ $device->device->serial_number }}</s> →
                                    @if ($device->replacement !== null)
                                        <strong>{{ $device->replacement->installedDevice->serial_number }}</strong> · despacho a granel {{ $device->replacement->bulkDispatch->number() }}
                                    @else
                                        falta reemplazarla
                                    @endif
                                </div>
                            @endforeach
                        </td>
                    </tr>
                    <tr><td class="rotulo">Cerrada:</td><td>{{ $reinspection->isClosed() ? $at($reinspection->closed_at).' · '.$reinspection->closer?->name : '—' }}</td></tr>
                    <tr>
                        <td class="rotulo">Segunda verificación:</td>
                        <td>
                            @forelse ($checkpoint['secondMarks'][$reinspection->id] ?? [] as $mark)
                                @php $text = $result($mark); @endphp
                                <div>{{ $mark->device->serial_number }} · <span style="font-weight: bold; color: {{ $text['color'] }};">{{ $text['label'] }}</span> · {{ $at($mark->marked_at) }} · {{ $mark->marker?->name ?? '—' }}</div>
                            @empty
                                —
                            @endforelse
                        </td>
                    </tr>
                </table>
            </div>
        @endforeach
    @endif

    <p class="seccion" style="margin-top: 14px;">Fotos del checkpoint</p>

    @foreach ($photoSteps as $step)
        @php $photoRows = $step->photos->chunk(3)->values(); @endphp
        <div class="caja" style="padding: 8px 12px; margin-bottom: 10px;">
            <div class="sin-corte">
                <div style="padding-bottom: 5px; margin-bottom: 8px; border-bottom: 1px solid #c9ced6;">
                    <span style="font-size: 11px; font-weight: bold;">{{ $step->position }} · {{ $step->section->name }}</span>
                    <span style="font-size: 9px; color: #4b5563; margin-left: 8px;">Checkpoint · {{ trans_choice(':count foto|:count fotos', $step->photos->count()) }}</span>
                </div>
                @if ($photoRows->isNotEmpty())
                    @include('pdf.certifications.photo-row', ['row' => $photoRows->first()])
                @endif
            </div>
            @foreach ($photoRows->slice(1) as $row)
                @include('pdf.certifications.photo-row', ['row' => $row])
            @endforeach
        </div>
    @endforeach

    @if ($checkpoint['extraPhotos']->isNotEmpty())
        @php $photoRows = $checkpoint['extraPhotos']->chunk(3)->values(); @endphp
        <div class="caja" style="padding: 8px 12px; margin-bottom: 10px;">
            <div class="sin-corte">
                <div style="padding-bottom: 5px; margin-bottom: 8px; border-bottom: 1px solid #c9ced6;">
                    <span style="font-size: 11px; font-weight: bold;">Fotos extra</span>
                    <span style="font-size: 9px; color: #4b5563; margin-left: 8px;">Checkpoint · {{ trans_choice(':count foto|:count fotos', $checkpoint['extraPhotos']->count()) }}</span>
                </div>
                @include('pdf.certifications.photo-row', ['row' => $photoRows->first()])
            </div>
            @foreach ($photoRows->slice(1) as $row)
                @include('pdf.certifications.photo-row', ['row' => $row])
            @endforeach
        </div>
    @endif

    @if ($checkpoint['containerPhoto'] !== null)
        <div class="caja sin-corte" style="padding: 8px 12px; margin-bottom: 10px;">
            <div style="padding-bottom: 5px; margin-bottom: 8px; border-bottom: 1px solid #c9ced6;">
                <span style="font-size: 11px; font-weight: bold;">Foto del contenedor</span>
                <span style="font-size: 9px; color: #4b5563; margin-left: 8px;">Checkpoint · al abrir la unidad</span>
            </div>
            <table>
                <tr>
                    <td style="width: 33.33%; vertical-align: top; padding: 0 5px 10px; text-align: center;">
                        <div style="height: 172px;">
                            <img src="{{ $checkpoint['containerPhoto'] }}" alt="" style="max-width: 222px; max-height: 170px; border: 1px solid #c9ced6;">
                        </div>
                        <div style="font-size: 8px; color: #4b5563; margin-top: 2px; text-align: left;">{{ $record->containerPhotographer?->name ?? '—' }} · {{ $at($record->container_photo_at) }}</div>
                    </td>
                    <td style="width: 33.33%;"></td>
                    <td style="width: 33.33%;"></td>
                </tr>
            </table>
        </div>
    @endif
</div>
