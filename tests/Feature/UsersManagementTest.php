<?php

namespace Tests\Feature;

use App\Livewire\Tables\UsersTable;
use App\Livewire\Users\FormModal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UsersManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
    }

    public function test_guests_cannot_enter_the_users_screen(): void
    {
        $this->get(route('intranet.users.index'))->assertRedirect(route('login'));
    }

    public function test_the_list_shows_active_and_deactivated_users_with_their_status(): void
    {
        User::factory()->create(['name' => 'Ana Activa']);
        User::factory()->trashed()->create(['name' => 'Dora Desactivada']);

        $this->actingAs($this->admin)
            ->get(route('intranet.users.index'))
            ->assertOk()
            ->assertSeeText('Ana Activa')
            ->assertSeeText('Dora Desactivada')
            ->assertSeeText('Activo')
            ->assertSeeText('Inactivo');
    }

    public function test_the_list_filters_by_search_and_status(): void
    {
        User::factory()->create(['name' => 'Ana Activa', 'email' => 'ana@example.com']);
        User::factory()->trashed()->create(['name' => 'Dora Desactivada', 'email' => 'dora@example.com']);

        $this->actingAs($this->admin);

        Livewire::test(UsersTable::class)
            ->set('search', 'dora@')
            ->assertSeeText('Dora Desactivada')
            ->assertDontSeeText('Ana Activa')
            ->set('search', '')
            ->set('filterComponents.status', 'active')
            ->assertSeeText('Ana Activa')
            ->assertDontSeeText('Dora Desactivada')
            ->set('filterComponents.status', 'inactive')
            ->assertSeeText('Dora Desactivada')
            ->assertDontSeeText('Ana Activa');
    }

    public function test_each_row_passes_its_user_to_the_actions(): void
    {
        $user = User::factory()->create(['name' => "Ana O'Brien"]);

        $this->actingAs($this->admin);

        Livewire::test(UsersTable::class)
            ->assertSeeHtml('x-data="{ id: '.$user->id.', name: ')
            ->assertSeeHtml('title="Desactivar"');
    }

    public function test_a_user_is_created_with_a_random_password_that_is_shown_and_lets_them_log_in(): void
    {
        $this->actingAs($this->admin);

        $component = Livewire::test(FormModal::class)
            ->dispatch('create-user')
            ->assertSet('form.openModal', true)
            ->set('form.name', 'Nuevo Usuario')
            ->set('form.email', 'nuevo@example.com')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertSet('showPassword', true)
            ->assertDispatched('refreshDatatable')
            ->assertSeeText('No se volverá a mostrar');

        $password = $component->get('generatedPassword');
        $user = User::query()->where('email', 'nuevo@example.com')->firstOrFail();

        $this->assertIsString($password);
        $this->assertSame('Nuevo Usuario', $user->name);
        $this->assertNotSame($password, $user->password);
        $this->assertTrue(Hash::check($password, $user->password));

        Auth::logout();

        $this->post(route('login.store'), ['email' => 'nuevo@example.com', 'password' => $password])
            ->assertRedirect(route('intranet.dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_two_users_cannot_share_an_email_even_if_one_is_deactivated(): void
    {
        User::factory()->create(['email' => 'activo@example.com']);
        User::factory()->trashed()->create(['email' => 'inactivo@example.com']);
        $this->app->setLocale('es');

        $this->actingAs($this->admin);

        foreach (['activo@example.com', 'inactivo@example.com'] as $email) {
            Livewire::test(FormModal::class)
                ->call('create')
                ->set('form.name', 'Otro Usuario')
                ->set('form.email', $email)
                ->call('save')
                ->assertHasErrors(['form.email' => 'unique'])
                ->assertSeeText('El valor de correo electrónico ya está en uso.')
                ->assertSet('form.openModal', true)
                ->assertSet('generatedPassword', null);
        }

        $this->assertDatabaseCount('users', 3);
    }

    public function test_closing_the_password_window_forgets_the_password(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(FormModal::class)
            ->call('create')
            ->set('form.name', 'Nuevo Usuario')
            ->set('form.email', 'nuevo@example.com')
            ->call('save')
            ->call('closePassword')
            ->assertSet('showPassword', false)
            ->assertSet('generatedPassword', null)
            ->assertDontSeeText('No se volverá a mostrar');
    }

    public function test_a_user_can_be_edited_keeping_their_own_email(): void
    {
        $user = User::factory()->create(['email' => 'igual@example.com']);
        $password = $user->password;

        $this->actingAs($this->admin);

        Livewire::test(FormModal::class)
            ->dispatch('edit-user', userId: $user->id)
            ->assertSet('form.openModal', true)
            ->assertSet('form.email', 'igual@example.com')
            ->set('form.name', 'Nombre Editado')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertSet('generatedPassword', null)
            ->assertDispatched('refreshDatatable');

        $user->refresh();

        $this->assertSame('Nombre Editado', $user->name);
        $this->assertSame('igual@example.com', $user->email);
        $this->assertSame($password, $user->password);
    }

    public function test_editing_cannot_take_the_email_of_another_user(): void
    {
        $user = User::factory()->create();
        User::factory()->create(['email' => 'ocupado@example.com']);

        $this->actingAs($this->admin);

        Livewire::test(FormModal::class)
            ->call('edit', $user->id)
            ->set('form.email', 'ocupado@example.com')
            ->call('save')
            ->assertHasErrors(['form.email' => 'unique']);
    }

    public function test_a_deactivated_user_can_still_be_opened_for_editing(): void
    {
        $user = User::factory()->trashed()->create(['name' => 'Dora Desactivada']);

        $this->actingAs($this->admin);

        Livewire::test(FormModal::class)
            ->call('edit', $user->id)
            ->assertSet('form.openModal', true)
            ->assertSet('form.name', 'Dora Desactivada');
    }

    public function test_deactivating_keeps_the_record_and_blocks_the_login(): void
    {
        $user = User::factory()->create(['email' => 'baja@example.com']);

        $this->actingAs($this->admin);

        Livewire::test(UsersTable::class)->call('deactivate', $user->id)->assertOk();

        $this->assertSoftDeleted($user);

        Auth::logout();

        $this->post(route('login.store'), ['email' => 'baja@example.com', 'password' => 'password']);

        $this->assertGuest();
    }

    public function test_deactivating_closes_the_open_session_of_that_user(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password']);
        $this->get(route('intranet.dashboard'))->assertOk();

        $user->delete();
        Auth::forgetGuards();

        $this->get(route('intranet.dashboard'))->assertRedirect(route('login'));
    }

    public function test_reactivating_lets_the_user_log_in_again(): void
    {
        $user = User::factory()->trashed()->create(['email' => 'vuelve@example.com']);

        $this->actingAs($this->admin);

        Livewire::test(UsersTable::class)->call('reactivate', $user->id)->assertOk();

        $this->assertNotSoftDeleted($user);

        Auth::logout();

        $this->post(route('login.store'), ['email' => 'vuelve@example.com', 'password' => 'password']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_deactivating_an_already_deactivated_user_only_warns(): void
    {
        $user = User::factory()->trashed()->create();
        $deletedAt = $user->deleted_at;

        $this->actingAs($this->admin);

        Livewire::test(UsersTable::class)
            ->call('deactivate', $user->id)
            ->assertOk()
            ->assertDispatched('toast', icon: 'warning');

        $this->assertEquals($deletedAt, $user->fresh()->deleted_at);
    }

    public function test_users_cannot_deactivate_themselves(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(UsersTable::class)
            ->assertDontSeeHtml('title="Desactivar"')
            ->call('deactivate', $this->admin->id)
            ->assertForbidden();

        $this->assertNotSoftDeleted($this->admin);
    }

    public function test_users_cannot_reactivate_themselves(): void
    {
        $this->actingAs($this->admin);
        $this->admin->delete();

        Livewire::test(UsersTable::class)
            ->call('reactivate', $this->admin->id)
            ->assertForbidden();

        $this->assertSoftDeleted($this->admin);
    }

    public function test_the_menu_links_to_the_users_list(): void
    {
        $this->actingAs($this->admin)
            ->get(route('intranet.dashboard'))
            ->assertSee('href="'.route('intranet.users.index').'"', false);
    }
}
