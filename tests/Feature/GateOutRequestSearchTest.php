<?php

namespace Tests\Feature;

use App\Livewire\GateOutRequests\Search;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class GateOutRequestSearchTest extends TestCase
{
    use RefreshDatabase;

    private const string API_URL = 'https://sigla1.test/api.php';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.sigla1.url' => self::API_URL,
            'services.sigla1.token' => 'test-token',
        ]);
        $this->app->setLocale('es');
    }

    public function test_guests_cannot_open_the_lookup(): void
    {
        $this->get(route('intranet.gate-out-requests.index'))->assertRedirect(route('login'));
    }

    public function test_the_certification_menu_leads_to_the_lookup(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('intranet.gate-out-requests.index'))
            ->assertOk()
            ->assertSeeInOrder(['Certificación', 'Solicitudes de retiro', 'Administración'])
            ->assertSeeText('Escribe el booking y el contenedor para ver los datos de la solicitud.');
    }

    public function test_a_found_request_shows_its_data_without_saving_anything(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::API_URL.'*' => Http::response([$this->gateOutRequest()])]);

        Livewire::actingAs(User::factory()->create())
            ->test(Search::class)
            ->set('booking', ' bkg000000001 ')
            ->set('container', 'abcu1234567')
            ->call('search')
            ->assertSet('booking', 'BKG000000001')
            ->assertSet('container', 'ABCU1234567')
            ->assertSeeText('Solicitud 10001')
            ->assertSeeText('OPERADOR DE EJEMPLO S.A.C.')
            ->assertSeeText('EMBARCADOR DE EJEMPLO S.A.C.')
            ->assertSeeText('10/11/2026 12:44')
            ->assertSeeText('00000001 · B00000001')
            ->assertSeeText('AAA111')
            ->assertSeeText('PLATAFORMA');

        Http::assertSent(fn (Request $request): bool => $request->method() === 'GET');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_more_than_one_request_shows_them_all(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::API_URL.'*' => Http::response([
            $this->gateOutRequest(),
            $this->gateOutRequest(['id_solicitud' => '10002']),
        ])]);

        Livewire::actingAs(User::factory()->create())
            ->test(Search::class)
            ->set('booking', 'BKG000000001')
            ->set('container', 'ABCU1234567')
            ->call('search')
            ->assertSeeText('Hay 2 solicitudes de retiro con este booking y este contenedor.')
            ->assertSeeTextInOrder(['Solicitud 10001', 'Solicitud 10002']);
    }

    public function test_booking_and_container_are_required_before_asking_sigla1(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::API_URL.'*' => Http::response([])]);

        Livewire::actingAs(User::factory()->create())
            ->test(Search::class)
            ->call('search')
            ->assertHasErrors(['booking' => 'required', 'container' => 'required'])
            ->assertSeeText('Escribe el booking.')
            ->assertSeeText('Escribe el contenedor.');

        Http::assertNothingSent();
    }

    public function test_a_missing_request_says_so_and_keeps_what_was_written(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::API_URL.'*' => Http::response([])]);

        Livewire::actingAs(User::factory()->create())
            ->test(Search::class)
            ->set('booking', 'BKG000000001')
            ->set('container', 'ABCU7654321')
            ->call('search')
            ->assertSet('container', 'ABCU7654321')
            ->assertSeeText('No hay ninguna solicitud de retiro con este booking y este contenedor.');
    }

    public function test_when_sigla1_does_not_answer_the_lookup_can_be_retried_with_what_was_written(): void
    {
        Http::preventStrayRequests();
        Http::fakeSequence(self::API_URL.'*')
            ->push(['message' => 'error'], 500)
            ->push([$this->gateOutRequest()]);

        Livewire::actingAs(User::factory()->create())
            ->test(Search::class)
            ->set('booking', 'BKG000000001')
            ->set('container', 'ABCU1234567')
            ->call('search')
            ->assertSeeText('SIGLA 1.0 no responde en este momento.')
            ->assertSeeText('Reintentar')
            ->assertSet('booking', 'BKG000000001')
            ->call('search')
            ->assertSeeText('Solicitud 10001')
            ->assertDontSeeText('SIGLA 1.0 no responde en este momento.');
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
