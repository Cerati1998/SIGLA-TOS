<?php

namespace Tests\Feature;

use App\Livewire\Users\CreateModal;
use App\Livewire\Users\EditModal;
use App\Models\Branch;
use App\Models\Identity;
use App\Models\User;
use Database\Seeders\IdentitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class UsersDniLookupTest extends TestCase
{
    use RefreshDatabase;

    private const string DNI_URL = 'https://lookup.test/dni';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(IdentitySeeder::class);
        $this->actingAs(User::factory()->create());
        $this->app->setLocale('es');

        config([
            'services.sunat.dni_url' => self::DNI_URL,
            'services.sunat.token' => 'test-token',
        ]);
        Http::preventStrayRequests();
    }

    public function test_a_found_dni_fills_the_name_with_given_names_first_and_it_can_be_corrected(): void
    {
        Http::fake([self::DNI_URL.'*' => Http::response([
            'first_name' => 'JOSÉ ÁNGEL',
            'first_last_name' => 'PEÑA',
            'second_last_name' => 'GARCÍA',
            'full_name' => 'PEÑA GARCÍA JOSÉ ÁNGEL',
        ])]);
        $branch = Branch::factory()->create();

        Livewire::test(CreateModal::class)
            ->set('form.identityId', Identity::DNI)
            ->set('form.documentNumber', '12345678')
            ->call('lookup')
            ->assertHasNoErrors()
            ->assertSet('form.lookupStatus', 'found')
            ->assertSet('form.name', 'José Ángel Peña García')
            ->set('form.name', 'José Peña García')
            ->set('form.email', 'jose@example.com')
            ->set('form.branchId', (string) $branch->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.lookupStatus', null);

        Http::assertSent(fn (Request $request): bool => $request->url() === self::DNI_URL.'?numero=12345678');
        $this->assertDatabaseHas('users', ['email' => 'jose@example.com', 'name' => 'José Peña García', 'document_number' => '12345678']);
    }

    public function test_the_name_can_be_filled_by_hand_when_the_dni_is_not_found_or_the_service_is_down(): void
    {
        Http::fakeSequence(self::DNI_URL.'*')
            ->push(['message' => 'dni no valido'], 422)
            ->push('', 500);

        Livewire::test(CreateModal::class)
            ->set('form.identityId', Identity::DNI)
            ->set('form.documentNumber', '12345678')
            ->call('lookup')
            ->assertSet('form.lookupStatus', 'not_found')
            ->assertSet('form.name', '')
            ->call('lookup', true)
            ->assertSet('form.lookupStatus', 'unavailable')
            ->assertSet('form.name', '');
    }

    public function test_a_dni_with_a_wrong_format_or_another_document_type_is_not_looked_up(): void
    {
        Http::fake();

        Livewire::test(CreateModal::class)
            ->set('form.identityId', Identity::DNI)
            ->set('form.documentNumber', '1234')
            ->call('lookup')
            ->assertHasErrors(['form.documentNumber' => 'digits'])
            ->assertSeeText('El DNI debe tener 8 dígitos.')
            ->set('form.identityId', Identity::RUC)
            ->set('form.documentNumber', '20123456789')
            ->call('lookup')
            ->assertHasErrors(['form.identityId' => 'in']);

        Http::assertNothingSent();
    }

    public function test_only_the_create_modal_offers_the_lookup(): void
    {
        Livewire::test(CreateModal::class)->assertSeeHtml('aria-label="Consultar documento"');

        $user = User::factory()->create();

        Livewire::test(EditModal::class)
            ->dispatch('edit-user', userId: $user->id)
            ->assertDontSeeHtml('aria-label="Consultar documento"');

        $this->assertFalse(method_exists(EditModal::class, 'lookup'));
    }
}
