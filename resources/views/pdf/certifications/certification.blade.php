@php
    use App\Enums\CertificationSectionType;
    use App\Enums\ContainerDrawing;

    $lima = fn ($date) => $date?->copy()->timezone('America/Lima');
    $value = fn (?string $text): string => $text === null || $text === '' ? '—' : $text;
    $logo = 'data:image/png;base64,'.base64_encode((string) file_get_contents(public_path('images/sigla-logo-cropped.png')));
    $schedule = function (array $inspection) use ($lima): string {
        if ($inspection['startedAt'] === null) {
            return '—';
        }

        return $lima($inspection['startedAt'])->format('H:i').' a '.$lima($inspection['finishedAt'])->format('H:i').' · '.implode(', ', $inspection['inspectors']);
    };
    $byType = collect($inspections)->keyBy(fn (array $inspection): string => $inspection['type']->value);
    $drawingWidth = 220;
    $number = 0;
    $views = [];

    foreach ([CertificationSectionType::Internal, CertificationSectionType::External] as $type) {
        $views[] = [
            'type' => $type,
            'drawings' => ContainerDrawing::forType($type),
            'devices' => $steps->where('type', $type)
                ->flatMap(fn ($step) => $step->devices->map(fn ($device): array => ['device' => $device, 'section' => $step->section->name]))
                ->values()
                ->map(fn (array $row, int $index): array => [...$row, 'number' => $number + $index + 1]),
        ];
        $number += end($views)['devices']->count();
    }
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
                        @foreach ($view['drawings'] as $drawing)
                            {{-- Each device of a section with a spot on the drawing carries its number there (T73). The marks go before the image: after it, dompdf draws the image above its box. Each mark is the number alone, in black, and its box goes 7 px above the spot because dompdf puts the baseline lower than a browser, so the digits are centered on the spot. --}}
                            <div style="position: relative; width: {{ $drawingWidth }}px; height: {{ round($drawing->heightAt($drawingWidth), 1) }}px; {{ $loop->first ? '' : 'margin-top: 8px;' }}">
                                @foreach ($drawing->marks($view['devices'], $drawingWidth) as $mark)
                                    <div style="position: absolute; z-index: 1; left: {{ round($mark['left'] - 7, 1) }}px; top: {{ round($mark['top'] - 7, 1) }}px; width: 14px; height: 10px; line-height: 10px; color: #1f2937; font-size: 9px; font-weight: bold; text-align: center;">{{ $mark['number'] }}</div>
                                @endforeach
                                <img src="{{ $drawing->dataUri() }}" alt="" style="display: block; width: {{ $drawingWidth }}px;">
                            </div>
                            <div style="font-size: 8.5px; font-weight: bold; text-transform: uppercase; color: #4b5563; letter-spacing: 0.5px; margin-top: 2px;">{{ $drawing->caption() }}</div>
                        @endforeach
                    </td>
                    <td style="vertical-align: top; padding: 10px 0;">
                        <table class="lista">
                            <thead><tr><th class="derecha" style="width: 24px;">N.º</th><th>Serie</th><th>Dispositivo</th><th>Sección</th></tr></thead>
                            <tbody>
                                @forelse ($view['devices'] as $row)
                                    <tr @class(['par' => $loop->even])>
                                        <td class="derecha">{{ $row['number'] }}</td>
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
                @php $currentPhotos = $step->photos->whereNull('superseded_by_replacement_id'); @endphp
                @php $photoRows = $currentPhotos->chunk(3)->values(); @endphp
                <div class="caja" style="padding: 8px 12px; margin-bottom: 10px;">
                    {{-- The title goes in one block with the first row of photos, so a page never ends on a title alone (T88). --}}
                    <div class="sin-corte">
                        <div style="padding-bottom: 5px; margin-bottom: 8px; border-bottom: 1px solid #c9ced6;">
                            <span style="font-size: 11px; font-weight: bold;">{{ $step->position }} · {{ $step->section->name }}</span>
                            <span style="font-size: 9px; color: #4b5563; margin-left: 8px;">
                                {{ $step->type->label() }} · {{ $step->devices->isEmpty() ? 'Solo foto' : $step->devices->pluck('serial_number')->implode(', ') }} · {{ trans_choice(':count foto|:count fotos', $currentPhotos->count()) }}
                            </span>
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
        </div>
    @endforeach

    {{-- The checkpoint, once closed, ends the PDF (T94). --}}
    @if (($checkpoint ?? null) !== null)
        @include('pdf.certifications.checkpoint')
    @endif
@endsection
