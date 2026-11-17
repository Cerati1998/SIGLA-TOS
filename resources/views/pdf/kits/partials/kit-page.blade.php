@php
    $total = array_sum(array_column($slots, 'added'));
@endphp

<table class="cabecera">
    <tr>
        <td style="width: 190px; height: 64px;"><img src="{{ $logo }}" alt="SIGLA, Powered by Macromar" style="max-height: 48px; max-width: 170px;"></td>
        <td>
            <div class="titulo">Despacho de kit de seguridad</div>
            <div class="subtitulo">Bodega · Kits</div>
        </td>
        <td style="width: 150px; background: #f3f5f9;">
            <div class="numero-rotulo">N.º de despacho</div>
            <div class="numero">{{ $dispatch->number() }}</div>
        </td>
    </tr>
</table>

<table class="pareja">
    <tr>
        <td>
                <p class="seccion">Kit</p>
                <table class="datos">
                    <tr><td class="rotulo">Funda:</td><td style="font-size: 13px; font-weight: bold;">{{ $kit->pouch_code }}</td></tr>
                    <tr><td class="rotulo">Tipo de kit:</td><td>{{ $kit->kitType->name }}</td></tr>
                    <tr><td class="rotulo">Naviera:</td><td>{{ $kit->shippingLine->code }} · {{ $kit->shippingLine->name }}</td></tr>
                    <tr><td class="rotulo">Semana del kit:</td><td>{{ $kit->week() }}</td></tr>
                    <tr><td class="rotulo">Dispositivos:</td><td>{{ count($devices) }}</td></tr>
                </table>
        </td>
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
    </tr>
</table>

<div class="caja" style="margin-top: 14px; padding: 10px 12px;">
    <table>
        <tr>
            <td style="width: 235px; padding-right: 14px; vertical-align: top;">
                <p class="seccion">Resumen por tipo</p>
                <table class="lista">
                    <thead><tr><th>Tipo de dispositivo</th><th class="derecha">Cant.</th></tr></thead>
                    <tbody>
                        @foreach ($slots as $slot)
                            <tr @class(['par' => $loop->even])>
                                <td>{{ \App\Actions\Kits\AddSecurityDeviceToKit::slotLabel($slot) }}</td>
                                <td class="derecha">{{ $slot['added'] }}</td>
                            </tr>
                        @endforeach
                        <tr class="total"><td>Total</td><td class="derecha">{{ $total }}</td></tr>
                    </tbody>
                </table>
            </td>
            <td style="vertical-align: top;">
                <p class="seccion">Contenido del kit</p>
                <table class="lista">
                    <thead><tr><th class="derecha" style="width: 28px;">N.º</th><th>Serie</th><th>Tipo de dispositivo</th><th>Asignación</th></tr></thead>
                    <tbody>
                        @foreach ($devices as $device)
                            <tr @class(['par' => $loop->even])>
                                <td class="derecha">{{ $loop->iteration }}</td>
                                <td>{{ $device['serialNumber'] }}</td>
                                <td>{{ $device['type'] }}</td>
                                <td>{{ $device['assignment'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</div>

<table class="caja" style="margin-top: 14px;">
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
