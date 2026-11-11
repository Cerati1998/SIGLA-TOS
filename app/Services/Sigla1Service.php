<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Bridge with the API of SIGLA 1.0 (E7). It only reads: SIGLA 2.0 never changes anything in SIGLA 1.0.
 *
 * @phpstan-import-type GateOutRequest from Sigla1LookupResult
 * @phpstan-import-type Vehicle from Sigla1LookupResult
 */
class Sigla1Service
{
    /**
     * Find the gate out requests of a booking and a container. Both are sent trimmed and in upper case.
     * The log never gets the request data, because it holds the driver's license.
     */
    public function findGateOutRequests(string $booking, string $container): Sigla1LookupResult
    {
        $url = (string) config('services.sigla1.url');
        $token = (string) config('services.sigla1.token');

        if ($url === '' || $token === '') {
            return $this->unavailable('not configured');
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout((int) config('services.sigla1.timeout'))
                ->get($url, [
                    'op' => 'GetDespatch',
                    'booking' => $this->normalize($booking),
                    'container' => $this->normalize($container),
                ]);
        } catch (ConnectionException) {
            return $this->unavailable('connection failed');
        }

        $payload = $response->json();

        if (! $response->successful() || ! is_array($payload) || ! array_is_list($payload)) {
            return $this->unavailable("unexpected response with status {$response->status()}");
        }

        if ($payload === []) {
            return new Sigla1LookupResult(Sigla1LookupStatus::NotFound);
        }

        $gateOutRequests = [];

        foreach ($payload as $item) {
            if (! is_array($item) || $this->text($item, 'id_solicitud') === null) {
                return $this->unavailable('a request without its id');
            }

            $gateOutRequests[] = $this->gateOutRequest($item);
        }

        return new Sigla1LookupResult(Sigla1LookupStatus::Found, $gateOutRequests);
    }

    /**
     * Map one item of the API to the fields of SIGLA 2.0.
     *
     * @param  array<array-key, mixed>  $item
     * @return GateOutRequest
     */
    private function gateOutRequest(array $item): array
    {
        $license = $this->text($item, 'brevete_conductor');

        return [
            'reference' => (string) $this->text($item, 'id_solicitud'),
            'booking' => $this->text($item, 'reserva_booking'),
            'container' => $this->text($item, 'num_contenedor'),
            'shipping_line' => $this->text($item, 'linea_booking'),
            'product' => $this->text($item, 'producto_booking'),
            'vessel' => $this->text($item, 'nave_booking'),
            'voyage' => $this->text($item, 'viaje_booking'),
            'destination' => $this->text($item, 'destino_booking'),
            'dispatched_at' => $this->text($item, 'fecha_despacho'),
            'client' => [
                'document_number' => $this->text($item, 'ruc_operador'),
                'name' => $this->text($item, 'nombre_operador'),
            ],
            'shipper' => [
                'document_number' => $this->text($item, 'ruc_shippers'),
                'name' => $this->text($item, 'nombre_shippers'),
            ],
            'carrier' => [
                'document_number' => $this->text($item, 'ruc_transporte'),
                'name' => $this->text($item, 'nombre_transporte'),
            ],
            'driver' => [
                'name' => $this->text($item, 'nombre_conductor'),
                'license' => $license,
                'document_number' => $this->dniFromLicense($license),
            ],
            'tractor' => $this->vehicle($item, 'tracto'),
            'semi_trailer' => $this->vehicle($item, 'carreta'),
        ];
    }

    /**
     * @param  array<array-key, mixed>  $item
     * @return Vehicle
     */
    private function vehicle(array $item, string $suffix): array
    {
        return [
            'plate' => $this->text($item, "placa_{$suffix}"),
            'brand' => $this->text($item, "marca_{$suffix}"),
            'category' => $this->text($item, "categoria_{$suffix}"),
            'body' => $this->text($item, "carroceria_{$suffix}"),
            'color' => $this->text($item, "color_{$suffix}"),
        ];
    }

    /**
     * A Peruvian license is the driver's DNI after one letter, such as B46099190. Any other shape gives no DNI.
     */
    private function dniFromLicense(?string $license): ?string
    {
        if ($license === null || preg_match('/^[A-Z](\d{8})$/', mb_strtoupper($license), $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    private function normalize(string $value): string
    {
        return mb_strtoupper(trim($value));
    }

    /**
     * @param  array<array-key, mixed>  $item
     */
    private function text(array $item, string $field): ?string
    {
        $value = $item[$field] ?? null;

        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * Log why SIGLA 1.0 could not answer and build the result.
     */
    private function unavailable(string $reason): Sigla1LookupResult
    {
        Log::warning('The SIGLA 1.0 API is unavailable.', ['reason' => $reason]);

        return new Sigla1LookupResult(Sigla1LookupStatus::Unavailable);
    }
}
