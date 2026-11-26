@php
    $dispatchedAt = ($bulkDispatch->created_at ?? now())->timezone(config('app.display_timezone'))->format('d/m/Y H:i');
    $receiverOrigin = $bulkDispatch->receiver->is_own ? 'Propio' : 'De un proveedor';
    $isSale = $bulkDispatch->reason === \App\Enums\BulkDispatchReason::Sale;
    $deviceCount = array_sum(array_column($types, 'count'));
    $seriesPerRow = 6;
    $assignmentHalves = $assignment === null ? [] : array_chunk($assignment, (int) ceil(count($assignment) / 2), true);
@endphp

@extends('pdf.layout')

@section('title', 'Despacho '.$bulkDispatch->number())
@section('heading', 'Despacho de dispositivos a granel')
@section('subheading', 'Bodega · Despachos · A granel')
@section('number-label', 'N.º de despacho')
@section('number', $bulkDispatch->number())

@section('content')
    <table class="pareja">
        <tr>
            <td>
                <p class="seccion">Despacho</p>
                <table class="datos">
                    <tr><td class="rotulo" style="width: 110px;">Fecha:</td><td>{{ $dispatchedAt }}</td></tr>
                    <tr><td class="rotulo" style="width: 110px;">Semana:</td><td>{{ $bulkDispatch->week() }}</td></tr>
                    <tr><td class="rotulo" style="width: 110px;">Entregó:</td><td>{{ $bulkDispatch->creator->name }}</td></tr>
                    <tr><td class="rotulo" style="width: 110px;">Recibió:</td><td>{{ $bulkDispatch->receiver->full_name }}</td></tr>
                    <tr><td class="rotulo" style="width: 110px;">Procedencia:</td><td>{{ $receiverOrigin }}</td></tr>
                </table>
            </td>
            <td>
                <p class="seccion">Salida</p>
                <table class="datos">
                    <tr><td class="rotulo" style="width: 110px;">Motivo:</td><td>{{ $bulkDispatch->reason->label() }}</td></tr>
                    @if ($isSale)
                        <tr><td class="rotulo" style="width: 110px;">Cliente:</td><td>{{ $bulkDispatch->client?->business_name }}</td></tr>
                    @else
                        <tr><td class="rotulo" style="width: 110px;">Kit:</td><td>{{ $bulkDispatch->kit?->pouch_code }} · {{ $bulkDispatch->kit?->kitType->name }}</td></tr>
                    @endif
                    <tr><td class="rotulo" style="width: 110px;">Patio destino:</td><td>{{ $bulkDispatch->yardZone->name }}</td></tr>
                    <tr><td class="rotulo" style="width: 110px;">Observación:</td><td>{{ $bulkDispatch->notes ?? '-' }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    @if ($assignmentHalves !== [])
        <div class="caja sin-corte" style="margin-top: 14px; padding: 10px 12px;">
            <p class="seccion">Asignación del kit</p>
            <table>
                <tr>
                    @foreach ($assignmentHalves as $half)
                        <td style="width: 50%; vertical-align: top; padding: 0 {{ $loop->first ? 14 : 0 }}px 0 0;">
                            <table class="datos">
                                @foreach ($half as $label => $value)
                                    <tr><td class="rotulo" style="width: 110px;">{{ $label }}:</td><td>{{ $value }}</td></tr>
                                @endforeach
                            </table>
                        </td>
                    @endforeach
                </tr>
            </table>
        </div>
    @endif

    <div class="caja sin-corte" style="margin-top: 14px; padding: 10px 12px;">
        <p class="seccion">Dispositivos entregados</p>
        <table class="lista">
            <thead><tr><th>Tipo de dispositivo</th><th>Rango</th><th class="derecha" style="width: 60px;">Cant.</th></tr></thead>
            <tbody>
                @foreach ($types as $type)
                    <tr @class(['par' => $loop->even])>
                        <td style="font-weight: bold;">{{ $type['type'] }}</td>
                        <td>{{ implode(', ', $type['ranges']) }}</td>
                        <td class="derecha">{{ $type['count'] }}</td>
                    </tr>
                @endforeach
                <tr class="total"><td colspan="2">Total · {{ trans_choice('{1} :count tipo|[0,*] :count tipos', count($types)) }}</td><td class="derecha">{{ $deviceCount }}</td></tr>
            </tbody>
        </table>
    </div>

    <p class="seccion" style="margin-top: 16px;">Series por tipo de dispositivo</p>

    @foreach ($types as $type)
        <div class="caja" style="margin-top: {{ $loop->first ? 0 : 14 }}px; padding: 10px 12px;">
            <div style="padding-bottom: 6px; margin-bottom: 8px; border-bottom: 1px solid #c9ced6;">
                <span style="font-size: 12px; font-weight: bold;">{{ $type['type'] }}</span>
                <span style="color: #4b5563; margin-left: 10px;">
                    {{ count($type['ranges']) === 1 ? 'Rango' : 'Rangos' }} {{ implode(', ', $type['ranges']) }} · {{ trans_choice('{1} :count dispositivo|[0,*] :count dispositivos', $type['count']) }}
                </span>
            </div>
            <table class="lista">
                <thead>
                    <tr>
                        @for ($column = 0; $column < $seriesPerRow; $column++)
                            <th class="derecha" style="width: 22px;">N.º</th><th>Serie</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach (array_chunk($type['serialNumbers'], $seriesPerRow) as $row => $serialNumbers)
                        <tr @class(['par' => $row % 2 === 1])>
                            @for ($column = 0; $column < $seriesPerRow; $column++)
                                @if (isset($serialNumbers[$column]))
                                    <td class="derecha" style="color: #4b5563;">{{ $row * $seriesPerRow + $column + 1 }}</td><td>{{ $serialNumbers[$column] }}</td>
                                @else
                                    <td></td><td></td>
                                @endif
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <table class="caja sin-corte" style="margin-top: 14px;">
        <tr>
            <td style="width: 50%; padding: 10px 12px; vertical-align: top;">
                <p class="seccion">Entrega</p>
                <table class="datos">
                    <tr><td class="rotulo" style="width: 90px;">Entregó:</td><td>{{ $bulkDispatch->creator->name }}</td></tr>
                    <tr><td class="rotulo" style="width: 90px;">Fecha:</td><td>{{ $dispatchedAt }}</td></tr>
                </table>
            </td>
            <td style="width: 50%; padding: 10px 12px; vertical-align: top; border-left: 1px solid #4b5563;">
                <p class="seccion">Recepción</p>
                <table class="datos">
                    <tr><td class="rotulo" style="width: 90px;">Recibió:</td><td>{{ $bulkDispatch->receiver->full_name }}</td></tr>
                    <tr><td class="rotulo" style="width: 90px;">Procedencia:</td><td>{{ $receiverOrigin }}</td></tr>
                </table>
            </td>
        </tr>
    </table>
@endsection
