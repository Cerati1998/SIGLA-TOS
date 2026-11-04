<?php

namespace Tests\Feature;

use App\Services\SunatLookupStatus;
use App\Services\SunatService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SunatServiceTest extends TestCase
{
    private const string RUC_URL = 'https://lookup.test/ruc';

    private const string DNI_URL = 'https://lookup.test/dni';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.sunat.ruc_url' => self::RUC_URL,
            'services.sunat.dni_url' => self::DNI_URL,
            'services.sunat.token' => 'test-token',
        ]);
    }

    public function test_returns_the_business_name_and_address_of_a_found_ruc(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::RUC_URL.'*' => Http::response([
            'razon_social' => 'EMPRESA DE EJEMPLO S.A.C.',
            'numero_documento' => '20000000001',
            'direccion' => 'AV. DE EJEMPLO 123 ',
        ])]);

        $result = (new SunatService)->lookupRuc('20000000001');

        $this->assertSame(SunatLookupStatus::Found, $result->status);
        $this->assertSame([
            'business_name' => 'EMPRESA DE EJEMPLO S.A.C.',
            'address' => 'AV. DE EJEMPLO 123',
        ], $result->data);
        Http::assertSent(fn (Request $request): bool => $request->url() === self::RUC_URL.'?numero=20000000001'
            && $request->hasHeader('Authorization', 'Bearer test-token'));
    }

    public function test_returns_no_address_when_sunat_answers_with_a_dash(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::RUC_URL.'*' => Http::response([
            'razon_social' => 'EMPRESA DE EJEMPLO S.A.C.',
            'direccion' => '-',
        ])]);

        $result = (new SunatService)->lookupRuc('20000000001');

        $this->assertNull($result->data['address']);
    }

    public function test_returns_the_names_of_a_found_dni(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::DNI_URL.'*' => Http::response([
            'first_name' => 'NOMBRE',
            'first_last_name' => 'PATERNO',
            'second_last_name' => 'MATERNO',
            'full_name' => 'PATERNO MATERNO NOMBRE',
            'document_number' => '00000001',
        ])]);

        $result = (new SunatService)->lookupDni('00000001');

        $this->assertSame(SunatLookupStatus::Found, $result->status);
        $this->assertSame([
            'first_name' => 'NOMBRE',
            'first_last_name' => 'PATERNO',
            'second_last_name' => 'MATERNO',
            'full_name' => 'PATERNO MATERNO NOMBRE',
        ], $result->data);
        Http::assertSent(fn (Request $request): bool => $request->url() === self::DNI_URL.'?numero=00000001');
    }

    /**
     * @return array<string, array{int}>
     */
    public static function notFoundStatuses(): array
    {
        return ['404' => [404], '422' => [422]];
    }

    #[DataProvider('notFoundStatuses')]
    public function test_returns_not_found_when_the_service_rejects_the_document(int $status): void
    {
        Http::preventStrayRequests();
        Http::fake([self::RUC_URL.'*' => Http::response(['message' => 'not found'], $status)]);

        $result = (new SunatService)->lookupRuc('20000000001');

        $this->assertSame(SunatLookupStatus::NotFound, $result->status);
        $this->assertSame([], $result->data);
    }

    public function test_returns_not_found_when_the_service_says_the_ruc_is_not_valid(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::RUC_URL.'*' => Http::response(['message' => 'ruc no valido'], 400)]);

        $result = (new SunatService)->lookupRuc('20000000001');

        $this->assertSame(SunatLookupStatus::NotFound, $result->status);
    }

    public function test_returns_not_found_when_the_service_says_the_dni_is_not_valid(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::DNI_URL.'*' => Http::response(['message' => 'dni no valido'], 400)]);

        $result = (new SunatService)->lookupDni('00000001');

        $this->assertSame(SunatLookupStatus::NotFound, $result->status);
    }

    public function test_returns_unavailable_when_the_message_is_about_something_other_than_the_document(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::RUC_URL.'*' => Http::response(['message' => 'token no valido'], 400)]);

        $result = (new SunatService)->lookupRuc('20000000001');

        $this->assertSame(SunatLookupStatus::Unavailable, $result->status);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function failedStatuses(): array
    {
        return ['bad token' => [401], 'no credit' => [402], 'rate limit' => [429], 'server error' => [500]];
    }

    #[DataProvider('failedStatuses')]
    public function test_returns_unavailable_when_the_service_fails(int $status): void
    {
        Http::preventStrayRequests();
        Http::fake([self::RUC_URL.'*' => Http::response(['message' => 'error'], $status)]);

        $result = (new SunatService)->lookupRuc('20000000001');

        $this->assertSame(SunatLookupStatus::Unavailable, $result->status);
    }

    public function test_returns_unavailable_when_the_response_lacks_the_required_field(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::RUC_URL.'*' => Http::response(['direccion' => 'AV. DE EJEMPLO 123'])]);

        $result = (new SunatService)->lookupRuc('20000000001');

        $this->assertSame(SunatLookupStatus::Unavailable, $result->status);
    }

    public function test_returns_unavailable_without_sending_a_request_when_the_token_is_missing(): void
    {
        config(['services.sunat.token' => null]);
        Http::preventStrayRequests();
        Http::fake([self::RUC_URL.'*' => Http::response()]);

        $result = (new SunatService)->lookupRuc('20000000001');

        $this->assertSame(SunatLookupStatus::Unavailable, $result->status);
        Http::assertNothingSent();
    }

    public function test_returns_unavailable_and_keeps_the_dni_out_of_the_log_when_the_connection_fails(): void
    {
        Log::spy();
        Http::preventStrayRequests();
        Http::fake([self::DNI_URL.'*' => Http::failedConnection()]);

        $result = (new SunatService)->lookupDni('00000001');

        $this->assertSame(SunatLookupStatus::Unavailable, $result->status);
        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => ! str_contains($message.json_encode($context), '00000001'));
    }
}
