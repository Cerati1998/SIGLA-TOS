<?php

namespace Tests\Feature;

use App\Livewire\Branches\EditModal as BranchEditModal;
use App\Livewire\Company\EditForm;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CompanyAndBranchInSessionTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $company = Company::factory()->create([
            'ruc' => '20474360955',
            'business_name' => 'Empresa Razón Social S.A.C.',
            'trade_name' => 'Macromar',
            'address' => 'Av. Original 123',
            'ubigeo' => '070101',
            'sol_pass' => 'clave-secreta',
        ]);
        $this->branch = Branch::factory()->recycle($company)->create(['name' => 'Callao']);
        $this->user = User::factory()->recycle($this->branch)->create();
    }

    public function test_the_first_screen_after_signing_in_stores_only_the_id_and_name_of_the_company_and_branch(): void
    {
        $this->actingAs($this->user)
            ->get(route('intranet.dashboard'))
            ->assertOk()
            ->assertSessionHas('company', ['id' => $this->branch->company_id, 'name' => 'Macromar'])
            ->assertSessionHas('branch', ['id' => $this->branch->id, 'name' => 'Callao']);
    }

    public function test_the_business_name_is_used_when_the_company_has_no_trade_name(): void
    {
        $this->branch->company->update(['trade_name' => null]);

        $this->actingAs($this->user)
            ->get(route('intranet.dashboard'))
            ->assertSessionHas('company.name', 'Empresa Razón Social S.A.C.');
    }

    public function test_guests_get_no_company_in_the_session(): void
    {
        $this->get(route('login'))->assertOk()->assertSessionMissing('company');
    }

    public function test_editing_the_company_refreshes_its_name_in_the_session(): void
    {
        $this->actingAs($this->user)->get(route('intranet.dashboard'));

        Livewire::test(EditForm::class)
            ->set('form.tradeName', 'Nuevo Nombre')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Nuevo Nombre', session('company.name'));
    }

    public function test_editing_the_branch_of_the_user_refreshes_its_name_in_the_session(): void
    {
        $this->actingAs($this->user)->get(route('intranet.dashboard'));

        Livewire::test(BranchEditModal::class)
            ->dispatch('edit-branch', branchId: $this->branch->id)
            ->set('form.name', 'Paita')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Paita', session('branch.name'));
    }
}
