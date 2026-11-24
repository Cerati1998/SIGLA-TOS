@php
    use App\Enums\CertificationSectionType;

    $lima = fn ($date) => $date?->copy()->timezone('America/Lima');
    $value = fn (?string $text): string => $text === null || $text === '' ? '—' : $text;
    $logo = 'data:image/png;base64,'.base64_encode((string) file_get_contents(public_path('images/sigla-logo-cropped.png')));
    $drawing = fn (string $name): string => 'data:image/svg+xml;base64,'.base64_encode((string) file_get_contents(resource_path("images/pdf/{$name}.svg")));
    $schedule = function (array $inspection) use ($lima): string {
        if ($inspection['startedAt'] === null) {
            return '—';
        }

        return $lima($inspection['startedAt'])->format('H:i').' a '.$lima($inspection['finishedAt'])->format('H:i').' · '.implode(', ', $inspection['inspectors']);
    };
    $byType = collect($inspections)->keyBy(fn (array $inspection): string => $inspection['type']->value);
    $number = 0;
    $devicesOf = fn (CertificationSectionType $type) => $steps->where('type', $type)
        ->flatMap(fn ($step) => $step->devices->map(fn ($device) => ['device' => $device, 'section' => $step->section->name]))
        ->values();
    $views = [
        ['type' => CertificationSectionType::Internal, 'drawings' => [['container-inside', 'Interior · puertas abiertas']]],
        ['type' => CertificationSectionType::External, 'drawings' => [['container-doors', 'Exterior · lado de las puertas'], ['container-machine', 'Exterior · lado de la máquina']]],
    ];
@endphp

@extends('pdf.layout')

@section('title', 'Certificación '.$certification->container)
@section('heading', 'Certificación de contenedor')
@section('subheading', 'Inspección interna y externa')
@section('number-label', 'Contenedor')
@section('number', $certification->container)
@section('number-note', $certification->inspection_sheet_code === null ? '' : 'Formato '.$certification->inspection_sheet_code)

@section('content')
    <table class="pareja">
        <tr>
            <td>
                <p class="seccion">Certificación</p>
                <table class="datos">
                    <tr><td class="rotulo">Cliente:</td><td>{{ $value($certification->client) }}</td></tr>
                    <tr><td class="rotulo">Producto:</td><td>{{ $value($certification->product) }}</td></tr>
                    <tr><td class="rotulo">Naviera:</td><td>{{ $certification->shippingLine->code }} · {{ $certification->shippingLine->name }}</td></tr>
                    <tr><td class="rotulo">Booking:</td><td>{{ $certification->booking }}</td></tr>
                    <tr><td class="rotulo">Zona de patio:</td><td>{{ $certification->yardZone->name }}</td></tr>
                    <tr><td class="rotulo">Asignada:</td><td>{{ $lima($certification->started_at)->format('d/m/Y H:i') }} · {{ $certification->certifier->name }}</td></tr>
                    <tr><td class="rotulo">Insp. interna:</td><td>{{ $schedule($byType['internal']) }}</td></tr>
                    <tr><td class="rotulo">Insp. externa:</td><td>{{ $schedule($byType['external']) }}</td></tr>
                    <tr><td class="rotulo">Cierre:</td><td>{{ $lima($certification->closed_at)?->format('d/m/Y H:i') }} · {{ $certification->closer?->name }}</td></tr>
                </table>
            </td>
            <td>
                <p class="seccion">Transporte y kit</p>
                <table class="datos">
                    <tr><td class="rotulo">Transportista:</td><td>{{ $value($certification->carrier?->business_name) }}</td></tr>
                    <tr><td class="rotulo">Conductor:</td><td>{{ $value($certification->driver?->full_name) }}</td></tr>
                    <tr><td class="rotulo">DNI:</td><td>{{ $value($certification->driver?->document_number) }}</td></tr>
                    <tr><td class="rotulo">Remolcador:</td><td>{{ $value($certification->tractor?->plate) }}</td></tr>
                    <tr><td class="rotulo">Semirremolque:</td><td>{{ $value($certification->semiTrailer?->plate) }}</td></tr>
                    <tr><td class="rotulo">Funda del kit:</td><td>{{ $certification->kit->pouch_code }}</td></tr>
                    <tr><td class="rotulo">Tipo de kit:</td><td>{{ $certification->kit->kitType->name }}</td></tr>
                    <tr><td class="rotulo">Dispositivos:</td><td>{{ $devices }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <p class="seccion" style="margin-top: 14px;">Dispositivos asignados</p>
    <div class="caja" style="padding: 4px 12px;">
        @foreach ($views as $view)
            <table class="sin-corte" style="{{ $loop->first ? '' : 'border-top: 1px solid #c9ced6;' }}">
                <tr>
                    <td style="width: 232px; vertical-align: top; padding: 10px 12px 10px 0; text-align: center;">
                        @foreach ($view['drawings'] as [$file, $caption])
                            <img src="{{ $drawing($file) }}" alt="" style="width: 220px; {{ $loop->first ? '' : 'margin-top: 8px;' }}">
                            <div style="font-size: 8.5px; font-weight: bold; text-transform: uppercase; color: #4b5563; letter-spacing: 0.5px; margin-top: 2px;">{{ $caption }}</div>
                        @endforeach
                    </td>
                    <td style="vertical-align: top; padding: 10px 0;">
                        <table class="lista">
                            <thead><tr><th class="derecha" style="width: 24px;">N.º</th><th>Serie</th><th>Dispositivo</th><th>Sección</th></tr></thead>
                            <tbody>
                                @forelse ($devicesOf($view['type']) as $row)
                                    <tr @class(['par' => $loop->even])>
                                        <td class="derecha">{{ ++$number }}</td>
                                        <td>{{ $row['device']->serial_number }}</td>
                                        <td>{{ $row['device']->lot->securityDeviceType->name }}</td>
                                        <td>{{ $row['section'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4">Sin dispositivos en las secciones {{ $view['type'] === CertificationSectionType::Internal ? 'internas' : 'externas' }}.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        @endforeach
    </div>

    @foreach ($views as $view)
        @php $typeSteps = $steps->where('type', $view['type']); @endphp
        @continue($typeSteps->isEmpty())

        <div style="page-break-before: always;">
            <table class="cabecera">
                <tr>
                    <td style="width: 150px; height: 34px; padding: 6px;"><img src="{{ $logo }}" alt="SIGLA, Powered by Macromar" style="max-height: 30px; max-width: 130px;"></td>
                    <td style="padding: 6px;"><span style="font-size: 12px; font-weight: bold; text-transform: uppercase;">Certificación de contenedor · Fotos</span></td>
                    <td style="width: 150px; padding: 6px; background: #f3f5f9;"><span style="font-size: 14px; font-weight: bold; color: #002f7f;">{{ $certification->container }}</span></td>
                </tr>
            </table>

            <p class="seccion" style="margin-top: 12px;">Inspección {{ mb_strtolower($view['type']->label()) }}</p>

            @foreach ($typeSteps as $step)
                <div class="caja" style="padding: 8px 12px; margin-bottom: 10px;">
                    <div style="padding-bottom: 5px; margin-bottom: 8px; border-bottom: 1px solid #c9ced6;">
                        <span style="font-size: 11px; font-weight: bold;">{{ $step->position }} · {{ $step->section->name }}</span>
                        <span style="font-size: 9px; color: #4b5563; margin-left: 8px;">
                            {{ $step->type->label() }} · {{ $step->devices->isEmpty() ? 'Solo foto' : $step->devices->pluck('serial_number')->implode(', ') }} · {{ trans_choice(':count foto|:count fotos', $step->photos->count()) }}
                        </span>
                    </div>
                    <table>
                        @foreach ($step->photos->chunk(4) as $row)
                            <tr class="sin-corte">
                                @foreach ($row as $photo)
                                    <td style="width: 25%; vertical-align: top; padding: 0 4px 8px;">
                                        <img src="{{ $photoData($photo) }}" alt="" style="width: 100%; border: 1px solid #c9ced6;">
                                        <div style="font-size: 8px; color: #4b5563; margin-top: 2px;">{{ $photo->uploader->name }} · {{ $lima($photo->created_at)?->format('d/m/Y H:i') }}</div>
                                    </td>
                                @endforeach
                                @for ($empty = $row->count(); $empty < 4; $empty++)
                                    <td style="width: 25%;"></td>
                                @endfor
                            </tr>
                        @endforeach
                    </table>
                </div>
            @endforeach
        </div>
    @endforeach
@endsection
