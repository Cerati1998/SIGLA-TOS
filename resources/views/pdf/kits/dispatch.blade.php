@php
    $dispatchedAt = ($dispatch->created_at ?? now())->timezone('America/Lima')->format('d/m/Y H:i');
    $receiverOrigin = $dispatch->receiver->is_own ? 'Propio' : 'De un proveedor';
    $total = array_sum(array_column($slots, 'added'));
@endphp
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Despacho {{ $dispatch->number() }} · {{ $kit->pouch_code }}</title>
<style>
    @page { margin: 32px 32px 64px; }
    body { margin: 0; font-family: "DejaVu Sans", sans-serif; font-size: 10.5px; color: #1f2937; }
    table { border-collapse: collapse; width: 100%; }
    .caja { border: 1px solid #4b5563; border-radius: 4px; }
    .cabecera td { border: 1px solid #4b5563; vertical-align: middle; text-align: center; padding: 10px; }
    .titulo { font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px; }
    .subtitulo { font-size: 9px; color: #4b5563; text-transform: uppercase; letter-spacing: 0.8px; margin-top: 4px; }
    .numero-rotulo { font-size: 8.5px; font-weight: bold; color: #4b5563; text-transform: uppercase; letter-spacing: 0.8px; }
    .numero { font-size: 17px; font-weight: bold; color: #002f7f; margin-top: 4px; }
    .seccion { font-size: 11px; font-weight: bold; text-transform: uppercase; margin: 0 0 8px; }
    .datos td { padding: 2px 0; vertical-align: top; }
    .datos .rotulo { width: 130px; font-size: 9.5px; font-weight: bold; text-transform: uppercase; }
    .lista th { background: #002f7f; color: #ffffff; border: 1px solid #002f7f; font-size: 9px; font-weight: bold; text-transform: uppercase; text-align: left; padding: 4px 6px; }
    .lista td { border: 1px solid #c9ced6; padding: 3px 6px; }
    .lista tr.par td { background: #f3f5f9; }
    .lista tr.total td { background: #e5e7eb; font-weight: bold; border-top: 1px solid #4b5563; }
    .derecha { text-align: right; }
    .pie { position: fixed; bottom: -44px; left: 0; right: 0; border: 1px solid #4b5563; border-radius: 4px; padding: 8px 12px; font-size: 9.5px; color: #4b5563; }
    .pagina:after { content: "Página " counter(page); }
    .pareja { border-collapse: separate; border-spacing: 14px 0; margin: 14px -14px 0; width: calc(100% + 28px); }
    .pareja > tbody > tr > td { width: 50%; vertical-align: top; border: 1px solid #4b5563; border-radius: 4px; padding: 10px 12px; }
</style>
</head>
<body>
    <div class="pie">
        <table>
            <tr>
                <td>Generado por SIGLA el {{ $generatedAt->format('d/m/Y H:i') }} por {{ $generatedBy }}</td>
                <td class="derecha" style="font-weight: bold; color: #1f2937;"><span class="pagina"></span></td>
            </tr>
        </table>
    </div>

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
                        <tr><td class="rotulo">Dispositivos:</td><td>{{ $devices->count() }}</td></tr>
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
</body>
</html>
