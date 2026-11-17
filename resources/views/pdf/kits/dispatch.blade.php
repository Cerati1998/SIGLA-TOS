@php
    $dispatchedAt = ($dispatch->created_at ?? now())->timezone('America/Lima')->format('d/m/Y H:i');
    $receiverOrigin = $dispatch->receiver->is_own ? 'Propio' : 'De un proveedor';
    $deviceCount = array_sum(array_map(fn (array $contents): int => count($contents['devices']), $kits));
@endphp

@extends('pdf.layout')

@section('title', 'Despacho '.$dispatch->number())
@section('heading', 'Despacho de kits de seguridad')
@section('subheading', 'Bodega · Despachos')
@section('number-label', 'N.º de despacho')
@section('number', $dispatch->number())

@section('content')
    <table class="pareja">
        <tr>
            <td>
                <p class="seccion">Despacho</p>
                <table class="datos">
                    <tr><td class="rotulo">Fecha:</td><td>{{ $dispatchedAt }}</td></tr>
                    <tr><td class="rotulo">Semana:</td><td>{{ $dispatch->week() }}</td></tr>
                    <tr><td class="rotulo">Entregó:</td><td>{{ $dispatch->creator->name }}</td></tr>
                    <tr><td class="rotulo">Recibió:</td><td>{{ $dispatch->receiver->full_name }}</td></tr>
                    <tr><td class="rotulo">Procedencia:</td><td>{{ $receiverOrigin }}</td></tr>
                </table>
            </td>
            <td>
                <p class="seccion">Kits entregados</p>
                <table class="lista">
                    <thead><tr><th>Funda</th><th>Tipo de kit</th><th>Naviera</th><th class="derecha">Disp.</th></tr></thead>
                    <tbody>
                        @foreach ($kits as $contents)
                            <tr @class(['par' => $loop->even])>
                                <td style="font-weight: bold;">{{ $contents['kit']->pouch_code }}</td>
                                <td>{{ $contents['kit']->kitType->name }}</td>
                                <td>{{ $contents['kit']->shippingLine->code }}</td>
                                <td class="derecha">{{ count($contents['devices']) }}</td>
                            </tr>
                        @endforeach
                        <tr class="total"><td colspan="3">Total · {{ trans_choice('{1} :count kit|[2,*] :count kits', count($kits)) }}</td><td class="derecha">{{ $deviceCount }}</td></tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <p class="seccion" style="margin-top: 16px;">Contenido por funda</p>

    @foreach ($kits as $contents)
        <div class="caja sin-corte" style="margin-top: {{ $loop->first ? 0 : 14 }}px; padding: 10px 12px;">
            <div style="padding-bottom: 6px; margin-bottom: 8px; border-bottom: 1px solid #c9ced6;">
                <span style="font-size: 12px; font-weight: bold;">Funda {{ $contents['kit']->pouch_code }}</span>
                <span style="color: #4b5563; margin-left: 10px;">
                    {{ $contents['kit']->kitType->name }} · {{ $contents['kit']->shippingLine->code }} · {{ $contents['kit']->shippingLine->name }} · Semana {{ $contents['kit']->week() }} · {{ trans_choice('{1} :count dispositivo|[0,*] :count dispositivos', count($contents['devices'])) }}
                </span>
            </div>
            @include('pdf.kits.partials.contents-tables', ['slots' => $contents['slots'], 'devices' => $contents['devices'], 'withTitles' => false])
        </div>
    @endforeach

    <table class="caja sin-corte" style="margin-top: 14px;">
        <tr>
            <td style="width: 50%; padding: 10px 12px; vertical-align: top;">
                <p class="seccion">Entrega</p>
                <table class="datos">
                    <tr><td class="rotulo" style="width: 90px;">Entregó:</td><td>{{ $dispatch->creator->name }}</td></tr>
                    <tr><td class="rotulo" style="width: 90px;">Fecha:</td><td>{{ $dispatchedAt }}</td></tr>
                </table>
            </td>
            <td style="width: 50%; padding: 10px 12px; vertical-align: top; border-left: 1px solid #4b5563;">
                <p class="seccion">Recepción</p>
                <table class="datos">
                    <tr><td class="rotulo" style="width: 90px;">Recibió:</td><td>{{ $dispatch->receiver->full_name }}</td></tr>
                    <tr><td class="rotulo" style="width: 90px;">Procedencia:</td><td>{{ $receiverOrigin }}</td></tr>
                </table>
            </td>
        </tr>
    </table>
@endsection
