<?php

namespace Tests\Feature;

use App\Livewire\Branches\CreateModal;
use App\Livewire\Branches\EditModal;
use App\Livewire\Tables\BranchesTable;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class BranchesManagementTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->admin = User::factory()->recycle($this->company)->create();

        $this->actingAs($this->admin);
    }

    public function test_guests_cannot_open_the_screen(): void
    {
        auth()->logout();

        $this->get(route('intranet.branches.index'))->assertRedirect(route('login'));
    }

    public function test_the_menu_leads_to_the_list_with_active_and_deactivated_branches(): void
    {
        Branch::factory()->recycle($this->company)->create(['name' => 'Sede Activa']);
        Branch::factory()->recycle($this->company)->trashed()->create(['name' => 'Sede Cerrada']);

        $this->get(route('intranet.branches.index'))
            ->assertOk()
            ->assertSeeInOrder(['Configuración', 'Empresa', 'Sucursales'])
            ->assertSee(route('intranet.branches.index'))
            ->assertSeeText('Sede Activa')
            ->assertSeeText('Sede Cerrada')
            ->assertSeeText('Activa')
            ->assertSeeText('Inactiva');
    }

    public function test_each_row_receives_its_branch_id(): void
    {
        $branch = Branch::factory()->recycle($this->company)->create();

        Livewire::test(BranchesTable::class)->assertSeeHtml("id: {$branch->id},");
    }

    public function test_the_list_is_ordered_by_code(): void
    {
        Branch::factory()->recycle($this->company)->create(['code' => '70002', 'name' => 'Segunda Sede']);
        Branch::factory()->recycle($this->company)->create(['code' => '70001', 'name' => 'Primera Sede']);

        Livewire::test(BranchesTable::class)->assertSeeInOrder(['Primera Sede', 'Segunda Sede']);
    }

    public function test_the_list_filters_by_search_and_status(): void
    {
        Branch::factory()->recycle($this->company)->create(['code' => '70001', 'name' => 'Sede Activa']);
        Branch::factory()->recycle($this->company)->trashed()->create(['code' => '70002', 'name' => 'Sede Cerrada']);

        Livewire::test(BranchesTable::class)
            ->set('search', '70002')
            ->assertSeeText('Sede Cerrada')
            ->assertDontSeeText('Sede Activa');

        Livewire::test(BranchesTable::class)
            ->set('filterComponents.status', 'inactive')
            ->assertSeeText('Sede Cerrada')
            ->assertDontSeeText('Sede Activa');
    }

    public function test_a_branch_can_be_created_in_the_company(): void
    {
        Livewire::test(CreateModal::class)
            ->set('form.openModal', true)
            ->set('form.name', 'Depot Callao')
            ->set('form.ubigeo', '070101')
            ->set('form.address', 'Av. Faucett 100')
            ->set('form.phone', '')
            ->set('form.email', 'callao@example.com')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Sucursal creada')
            ->assertDispatched('refreshDatatable');

        $this->assertDatabaseHas('branches', [
            'company_id' => $this->company->id,
            'name' => 'Depot Callao',
            'ubigeo' => '070101',
            'address' => 'Av. Faucett 100',
            'phone' => null,
            'email' => 'callao@example.com',
        ]);
    }

    public function test_the_fields_of_a_branch_are_validated(): void
    {
        $this->app->setLocale('es');

        Livewire::test(CreateModal::class)
            ->set('form.ubigeo', 'abc')
            ->set('form.email', 'no-es-un-correo')
            ->call('save')
            ->assertHasErrors([
                'form.name' => 'required',
                'form.ubigeo' => 'digits',
                'form.address' => 'required',
                'form.email' => 'email',
            ])
            ->assertSeeText('El campo ubigeo debe tener 6 dígitos.')
            ->assertNotDispatched('toast');
    }

    public function test_the_code_follows_the_highest_one_even_if_it_is_deactivated(): void
    {
        Branch::factory()->recycle($this->company)->create(['code' => '70001']);
        Branch::factory()->recycle($this->company)->trashed()->create(['code' => '70005']);
        Branch::factory()->recycle($this->company)->create(['code' => 'SIN-NUMERO']);

        $this->createBranch('Otra Sede')->assertHasNoErrors();

        $this->assertDatabaseHas('branches', ['name' => 'Otra Sede', 'code' => '70006']);
    }

    public function test_the_code_keeps_four_digits(): void
    {
        Branch::factory()->recycle($this->company)->create(['code' => '0002']);
        Branch::query()->where('code', '!=', '0002')->update(['code' => '0000']);

        $this->createBranch('Otra Sede')->assertDispatched('toast', text: 'La sucursal se creó con el código 0003.');

        $this->assertDatabaseHas('branches', ['name' => 'Otra Sede', 'code' => '0003']);
    }

    public function test_a_branch_can_be_edited_and_keep_its_code(): void
    {
        $branch = Branch::factory()->recycle($this->company)->create(['code' => '70001', 'name' => 'Nombre Anterior']);

        Livewire::test(EditModal::class)
            ->dispatch('edit-branch', branchId: $branch->id)
            ->assertSet('form.openModal', true)
            ->assertSet('form.code', '70001')
            ->assertSet('form.name', 'Nombre Anterior')
            ->set('form.name', 'Nombre Nuevo')
            ->set('form.website', 'macromar.pe')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.openModal', false)
            ->assertDispatched('toast', icon: 'success', title: 'Sucursal actualizada')
            ->assertDispatched('refreshDatatable');

        $branch->refresh();

        $this->assertSame('70001', $branch->code);
        $this->assertSame('Nombre Nuevo', $branch->name);
        $this->assertSame('macromar.pe', $branch->website);
    }

    public function test_a_deactivated_branch_can_still_be_edited(): void
    {
        $branch = Branch::factory()->recycle($this->company)->trashed()->create();

        Livewire::test(EditModal::class)
            ->dispatch('edit-branch', branchId: $branch->id)
            ->set('form.name', 'Nombre Nuevo')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Nombre Nuevo', $branch->refresh()->name);
        $this->assertTrue($branch->trashed());
    }

    public function test_a_branch_without_active_users_can_be_deactivated(): void
    {
        $branch = Branch::factory()->recycle($this->company)->create();
        User::factory()->recycle($this->company)->trashed()->create(['branch_id' => $branch->id]);

        Livewire::test(BranchesTable::class)
            ->call('deactivate', $branch->id)
            ->assertDispatched('toast', icon: 'success', title: 'Sucursal desactivada');

        $this->assertSoftDeleted($branch);
    }

    public function test_a_branch_with_active_users_cannot_be_deactivated(): void
    {
        $branch = Branch::factory()->recycle($this->company)->create();
        User::factory()->count(2)->recycle($this->company)->create(['branch_id' => $branch->id]);

        Livewire::test(BranchesTable::class)
            ->call('deactivate', $branch->id)
            ->assertDispatched(
                'toast',
                icon: 'warning',
                title: 'No se puede desactivar',
                text: 'Tiene 2 usuarios activos. Muévelos a otra sucursal antes de desactivarla.',
            );

        $this->assertNotSoftDeleted($branch);
    }

    public function test_the_last_active_branch_cannot_be_deactivated(): void
    {
        $this->admin->branch()->firstOrFail()->delete();
        $lastBranch = Branch::factory()->recycle($this->company)->create();

        Livewire::test(BranchesTable::class)
            ->call('deactivate', $lastBranch->id)
            ->assertDispatched('toast', icon: 'warning', title: 'No se puede desactivar');

        $this->assertNotSoftDeleted($lastBranch);
    }

    public function test_deactivating_an_already_deactivated_branch_only_warns(): void
    {
        $branch = Branch::factory()->recycle($this->company)->trashed()->create();

        Livewire::test(BranchesTable::class)
            ->call('deactivate', $branch->id)
            ->assertDispatched('toast', icon: 'warning', title: 'Sucursal ya desactivada');
    }

    public function test_a_deactivated_branch_can_be_reactivated(): void
    {
        $branch = Branch::factory()->recycle($this->company)->trashed()->create();

        Livewire::test(BranchesTable::class)
            ->call('reactivate', $branch->id)
            ->assertDispatched('toast', icon: 'success', title: 'Sucursal reactivada');

        $this->assertNotSoftDeleted($branch);
    }

    public function test_reactivating_an_active_branch_only_warns(): void
    {
        $branch = Branch::factory()->recycle($this->company)->create();

        Livewire::test(BranchesTable::class)
            ->call('reactivate', $branch->id)
            ->assertDispatched('toast', icon: 'warning', title: 'Sucursal ya activa');
    }

    /**
     * Save a new branch with valid values.
     */
    private function createBranch(string $name): Testable
    {
        return Livewire::test(CreateModal::class)
            ->set('form.name', $name)
            ->set('form.ubigeo', '070101')
            ->set('form.address', 'Av. Faucett 100')
            ->call('save');
    }
}
