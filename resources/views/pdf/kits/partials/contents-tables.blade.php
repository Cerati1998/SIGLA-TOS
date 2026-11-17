{{-- What a kit carries: its summary by type and its devices. With $withTitles, each table has its section title. --}}
<table>
    <tr>
        <td style="width: 235px; padding-right: 14px; vertical-align: top;">
            @if ($withTitles)
                <p class="seccion">Resumen por tipo</p>
            @endif
            <table class="lista">
                <thead><tr><th>Tipo de dispositivo</th><th class="derecha">Cant.</th></tr></thead>
                <tbody>
                    @foreach ($slots as $slot)
                        <tr @class(['par' => $loop->even])>
                            <td>{{ \App\Actions\Kits\AddSecurityDeviceToKit::slotLabel($slot) }}</td>
                            <td class="derecha">{{ $slot['added'] }}</td>
                        </tr>
                    @endforeach
                    <tr class="total"><td>Total</td><td class="derecha">{{ count($devices) }}</td></tr>
                </tbody>
            </table>
        </td>
        <td style="vertical-align: top;">
            @if ($withTitles)
                <p class="seccion">Contenido del kit</p>
            @endif
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
