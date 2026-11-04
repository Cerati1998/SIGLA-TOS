<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SunatService
{
    /**
     * Look up a RUC, returning `business_name` and `address` when it is found.
     */
    public function lookupRuc(string $ruc): SunatLookupResult
    {
        return $this->lookup('ruc', $ruc, [
            'business_name' => 'razon_social',
            'address' => 'direccion',
        ], 'razon_social');
    }

    /**
     * Look up a DNI, returning `first_name`, `first_last_name`, `second_last_name` and `full_name` when it is found.
     */
    public function lookupDni(string $dni): SunatLookupResult
    {
        return $this->lookup('dni', $dni, [
            'first_name' => 'first_name',
            'first_last_name' => 'first_last_name',
            'second_last_name' => 'second_last_name',
            'full_name' => 'full_name',
        ], 'full_name');
    }

    /**
     * Query the external service. The document number and the exception message never reach the log,
     * because a DNI is personal data and the message of a failed request includes its URL.
     *
     * @param  array<string, string>  $fields  The result field for each field of the service.
     */
    private function lookup(string $source, string $number, array $fields, string $requiredField): SunatLookupResult
    {
        $url = (string) config("services.sunat.{$source}_url");
        $token = (string) config('services.sunat.token');

        if ($url === '' || $token === '') {
            return $this->unavailable($source, 'not configured');
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout((int) config('services.sunat.timeout'))
                ->get($url, ['numero' => $number]);
        } catch (ConnectionException) {
            return $this->unavailable($source, 'connection failed');
        }

        if (in_array($response->status(), [404, 422], true)) {
            return new SunatLookupResult(SunatLookupStatus::NotFound);
        }

        $payload = $response->json();

        if (! $response->successful() || ! is_array($payload) || $this->text($payload, $requiredField) === null) {
            return $this->unavailable($source, "unexpected response with status {$response->status()}");
        }

        return new SunatLookupResult(
            SunatLookupStatus::Found,
            array_map(fn (string $field): ?string => $this->text($payload, $field), $fields),
        );
    }

    /**
     * Get a trimmed text from the response, treating the dash SUNAT uses for empty fields as no value.
     *
     * @param  array<array-key, mixed>  $payload
     */
    private function text(array $payload, string $field): ?string
    {
        $value = $payload[$field] ?? null;

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return in_array($value, ['', '-'], true) ? null : $value;
    }

    /**
     * Log why the service could not answer and build the result.
     */
    private function unavailable(string $source, string $reason): SunatLookupResult
    {
        Log::warning('The document lookup service is unavailable.', ['source' => $source, 'reason' => $reason]);

        return new SunatLookupResult(SunatLookupStatus::Unavailable);
    }
}
