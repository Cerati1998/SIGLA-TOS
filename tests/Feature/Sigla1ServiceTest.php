<?php

namespace Tests\Feature;

use App\Services\Sigla1LookupStatus;
use App\Services\Sigla1Service;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class Sigla1ServiceTest extends TestCase
{
    private const string API_URL = 'https://sigla1.test/api.php';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.sigla1.url' => self::API_URL,
            'services.sigla1.token' => 'test-token',
        ]);
    }

    public function test_returns_the_data_of_a_found_request_with_the_dni_taken_from_the_license(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::API_URL.'*' => Http::response([$this->gateOutRequest()])]);

        $result = (new Sigla1Service)->findGateOutRequests(' bkg000000001 ', 'abcu1234567');

        $this->assertSame(Sigla1LookupStatus::Found, $result->status);
        $this->assertSame([[
            'reference' => '10001',
            'booking' => 'BKG000000001',
            'container' => 'ABCU1234567',
            'shipping_line' => 'NAVIERA DE EJEMPLO',
            'product' => 'PRODUCTO DE EJEMPLO',
            'vessel' => 'NAVE DE EJEMPLO',
            'voyage' => 'V001',
            'destination' => 'PUERTO DE EJEMPLO',
            'dispatched_at' => '2026-11-10 12:44:47',
            'client' => ['document_number' => '20000000001', 'name' => 'OPERADOR DE EJEMPLO S.A.C.'],
            'shipper' => ['document_number' => '20000000002', 'name' => 'EMBARCADOR DE EJEMPLO S.A.C.'],
            'carrier' => ['document_number' => '20000000003', 'name' => 'TRANSPORTES DE EJEMPLO S.R.L.'],
            'driver' => ['name' => 'NOMBRE APELLIDO DE EJEMPLO', 'license' => 'B00000001', 'document_number' => '00000001'],
            'tractor' => ['plate' => 'AAA111', 'brand' => 'MARCA TRACTO', 'category' => 'N3', 'body' => 'REMOLCADOR', 'color' => 'BLANCO'],
            'semi_trailer' => ['plate' => 'BBB222', 'brand' => 'MARCA CARRETA', 'category' => 'O4', 'body' => 'PLATAFORMA', 'color' => 'GRIS'],
        ]], $result->gateOutRequests);
        Http::assertSent(fn (Request $request): bool => $request->url() === self::API_URL.'?op=GetDespatch&booking=BKG000000001&container=ABCU1234567'
            && $request->hasHeader('Authorization', 'Bearer test-token'));
    }

    public function test_returns_every_request_when_the_api_finds_more_than_one(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::API_URL.'*' => Http::response([
            $this->gateOutRequest(),
            $this->gateOutRequest(['id_solicitud' => '10002']),
        ])]);

        $result = (new Sigla1Service)->findGateOutRequests('BKG000000001', 'ABCU1234567');

        $this->assertSame(['10001', '10002'], array_column($result->gateOutRequests, 'reference'));
    }

    public function test_returns_no_dni_when_the_license_is_not_a_letter_and_eight_digits(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::API_URL.'*' => Http::response([$this->gateOutRequest(['brevete_conductor' => 'X1234'])])]);

        $result = (new Sigla1Service)->findGateOutRequests('BKG000000001', 'ABCU1234567');

        $this->assertSame(['name' => 'NOMBRE APELLIDO DE EJEMPLO', 'license' => 'X1234', 'document_number' => null], $result->gateOutRequests[0]['driver']);
    }

    public function test_returns_not_found_when_the_api_answers_with_an_empty_list(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::API_URL.'*' => Http::response([])]);

        $result = (new Sigla1Service)->findGateOutRequests('BKG000000001', 'ABCU1234567');

        $this->assertSame(Sigla1LookupStatus::NotFound, $result->status);
        $this->assertSame([], $result->gateOutRequests);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function failedStatuses(): array
    {
        return ['bad token' => [401], 'forbidden' => [403], 'server error' => [500]];
    }

    #[DataProvider('failedStatuses')]
    public function test_returns_unavailable_when_the_api_fails(int $status): void
    {
        Http::preventStrayRequests();
        Http::fake([self::API_URL.'*' => Http::response(['message' => 'error'], $status)]);

        $result = (new Sigla1Service)->findGateOutRequests('BKG000000001', 'ABCU1234567');

        $this->assertSame(Sigla1LookupStatus::Unavailable, $result->status);
    }

    public function test_returns_unavailable_when_the_api_answers_with_something_other_than_a_list(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::API_URL.'*' => Http::response(['message' => 'error'])]);

        $result = (new Sigla1Service)->findGateOutRequests('BKG000000001', 'ABCU1234567');

        $this->assertSame(Sigla1LookupStatus::Unavailable, $result->status);
    }

    public function test_returns_unavailable_and_keeps_the_license_out_of_the_log_when_a_request_has_no_id(): void
    {
        Log::spy();
        Http::preventStrayRequests();
        Http::fake([self::API_URL.'*' => Http::response([$this->gateOutRequest(['id_solicitud' => ''])])]);

        $result = (new Sigla1Service)->findGateOutRequests('BKG000000001', 'ABCU1234567');

        $this->assertSame(Sigla1LookupStatus::Unavailable, $result->status);
        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => ! str_contains($message.json_encode($context), 'B00000001'));
    }

    public function test_returns_unavailable_when_the_connection_fails(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::API_URL.'*' => Http::failedConnection()]);

        $result = (new Sigla1Service)->findGateOutRequests('BKG000000001', 'ABCU1234567');

        $this->assertSame(Sigla1LookupStatus::Unavailable, $result->status);
    }

    public function test_returns_unavailable_without_sending_a_request_when_the_token_is_missing(): void
    {
        config(['services.sigla1.token' => null]);
        Http::preventStrayRequests();
        Http::fake([self::API_URL.'*' => Http::response([])]);

        $result = (new Sigla1Service)->findGateOutRequests('BKG000000001', 'ABCU1234567');

        $this->assertSame(Sigla1LookupStatus::Unavailable, $result->status);
        Http::assertNothingSent();
    }

    /**
     * An item of the API as SIGLA 1.0 sends it, with invented data.
     *
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function gateOutRequest(array $overrides = []): array
    {
        /** @var array<string, string> $item */
        $item = json_decode((string) file_get_contents(base_path('tests/Fixtures/sigla1/gate-out-request.json')), true);

        return [...$item, ...$overrides];
    }
}
