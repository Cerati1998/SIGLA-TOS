<?php

namespace App\Services\Monitoring;

use DOMDocument;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Talks with the SOAP service of 24HM, the monitoring platform of the GPS devices (E10). Every operation takes the user
 * and the password in its body and answers a JSON inside a text; an error comes as {"Error": ...}, sometimes with the
 * JSON broken, and a malformed request as a SOAP fault. The credentials never reach a message nor a log.
 */
class Gps24hmClient implements MonitoringPlatform
{
    /**
     * Get the last report of the given asset (H42).
     *
     * @throws MonitoringPlatformException
     */
    public function asset(int $assetId): MonitoringReport
    {
        $answer = $this->call('ConsultarActivoPorIdActivo', ['IdActivo' => (string) $assetId]);

        foreach ($this->assetsOf($answer) as $asset) {
            if ((int) ($asset['IdActivo'] ?? 0) === $assetId) {
                return $this->report($asset);
            }
        }

        throw new MonitoringPlatformException("24HM no tiene el activo {$assetId}.");
    }

    /**
     * Call an operation of the service and get its answer, already decoded.
     *
     * @param  array<string, string>  $parameters
     * @return array<mixed>
     *
     * @throws MonitoringPlatformException
     */
    private function call(string $operation, array $parameters): array
    {
        $user = (string) config('services.gps_24hm.user');
        $password = (string) config('services.gps_24hm.password');

        if ($user === '' || $password === '') {
            throw new MonitoringPlatformException('Faltan las credenciales de 24HM en la configuración.');
        }

        try {
            $response = Http::timeout((int) config('services.gps_24hm.timeout'))
                ->withHeaders(['SOAPAction' => ''])
                ->withBody($this->envelope($operation, ['Usuario' => $user, 'Contrasena' => $password] + $parameters), 'text/xml; charset=utf-8')
                ->post((string) config('services.gps_24hm.url'));
        } catch (ConnectionException) {
            throw new MonitoringPlatformException('24HM no respondió. Inténtalo en unos minutos.');
        }

        return $this->decode($response->body());
    }

    /**
     * Build the SOAP envelope of an operation, with every value escaped.
     *
     * @param  array<string, string>  $parameters
     */
    private function envelope(string $operation, array $parameters): string
    {
        $body = collect($parameters)
            ->map(fn (string $value, string $name): string => "<{$name}>".htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8')."</{$name}>")
            ->implode('');

        return '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:x="http://x.me.org/">'
            ."<soapenv:Header/><soapenv:Body><x:{$operation}>{$body}</x:{$operation}></soapenv:Body></soapenv:Envelope>";
    }

    /**
     * Get the JSON inside the answer, or the reason why there is none.
     *
     * @return array<mixed>
     *
     * @throws MonitoringPlatformException
     */
    private function decode(string $xml): array
    {
        $document = new DOMDocument;

        try {
            $loaded = $xml !== '' && $document->loadXML($xml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        } catch (Throwable) {
            $loaded = false;
        }

        if (! $loaded) {
            throw new MonitoringPlatformException('24HM respondió algo que no se pudo leer.');
        }

        if ($document->getElementsByTagName('faultstring')->length > 0) {
            throw new MonitoringPlatformException('24HM rechazó la consulta.');
        }

        $text = trim((string) $document->getElementsByTagName('return')->item(0)?->textContent);
        $data = json_decode($text, true);

        if (! is_array($data)) {
            preg_match('/"Error"\s*:\s*"([^"}]*)/', $text, $error);

            throw new MonitoringPlatformException(isset($error[1]) ? $this->errorMessage($error[1]) : '24HM respondió algo que no se pudo leer.');
        }

        if (isset($data['Error'])) {
            throw new MonitoringPlatformException($this->errorMessage((string) $data['Error']));
        }

        return $data;
    }

    /**
     * Get the message shown for an error that 24HM gives.
     */
    private function errorMessage(string $error): string
    {
        return str_contains(mb_strtolower($error), 'autenticado')
            ? '24HM rechazó las credenciales.'
            : '24HM respondió: '.trim($error);
    }

    /**
     * Get the assets of an answer, which come in the list «Activos».
     *
     * @param  array<mixed>  $answer
     * @return list<array<string, mixed>>
     */
    private function assetsOf(array $answer): array
    {
        $assets = $answer['Activos'] ?? [];

        return is_array($assets) ? array_values(array_filter($assets, 'is_array')) : [];
    }

    /**
     * Turn an asset of 24HM into its last report. «FechaHora» comes in UTC (checked against a live report on
     * 2026-11-27).
     *
     * @param  array<string, mixed>  $asset
     */
    private function report(array $asset): MonitoringReport
    {
        $text = fn (string $key): ?string => isset($asset[$key]) && trim((string) $asset[$key]) !== '' ? trim((string) $asset[$key]) : null;
        $number = fn (string $key): ?float => isset($asset[$key]) && is_numeric($asset[$key]) ? (float) $asset[$key] : null;

        try {
            $reportedAt = $text('FechaHora') === null ? null : Carbon::createFromFormat('Y-m-d H:i:s', (string) $text('FechaHora'), 'UTC');
        } catch (Throwable) {
            $reportedAt = null;
        }

        return new MonitoringReport(
            assetId: (int) $asset['IdActivo'],
            alias: $text('Alias'),
            productCode: $text('IdProducto'),
            deviceTypeCode: $text('IdTipoDispositivo'),
            reportedAt: $reportedAt ?: null,
            latitude: $number('Latitud'),
            longitude: $number('Longitud'),
            address: $text('Calle') === null ? null : trim((string) preg_replace('/\s*,+\s*/', ', ', (string) $text('Calle')), ', '),
            serviceStatus: $text('EstadoServicio'),
            operationalStatus: $text('EstadoOperativo'),
            supplyVoltage: $number('VoltajeAlimentacion'),
        );
    }
}
