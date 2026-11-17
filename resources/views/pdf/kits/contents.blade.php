@extends('pdf.layout')

@section('title', 'Kit '.$kit->pouch_code)
@section('heading', 'Contenido de kit de seguridad')
@section('subheading', 'Bodega · Kits')
@section('number-label', 'Funda')
@section('number', $kit->pouch_code)

@section('content')
    <div class="caja" style="margin-top: 14px; padding: 10px 12px;">
        <p class="seccion">Kit</p>
        <table>
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <table class="datos">
                        <tr><td class="rotulo">Funda:</td><td style="font-size: 13px; font-weight: bold;">{{ $kit->pouch_code }}</td></tr>
                        <tr><td class="rotulo">Tipo de kit:</td><td>{{ $kit->kitType->name }}</td></tr>
                        <tr><td class="rotulo">Naviera:</td><td>{{ $kit->shippingLine->code }} · {{ $kit->shippingLine->name }}</td></tr>
                    </table>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <table class="datos">
                        <tr><td class="rotulo">Semana del kit:</td><td>{{ $kit->week() }}</td></tr>
                        <tr><td class="rotulo">Dispositivos:</td><td>{{ count($devices) }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="caja" style="margin-top: 14px; padding: 10px 12px;">
        @include('pdf.kits.partials.contents-tables', ['withTitles' => true])
    </div>
@endsection
