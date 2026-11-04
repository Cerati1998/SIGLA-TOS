<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Identity;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\CompanySeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\IdentitySeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IdentitiesAndBranchesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_seeders_load_the_catalogs_and_can_run_again_without_duplicating(): void
    {
        $this->seed([IdentitySeeder::class, CompanySeeder::class, BranchSeeder::class]);
        $this->seed([IdentitySeeder::class, CompanySeeder::class, BranchSeeder::class]);

        $this->assertDatabaseCount('identities', 12);
        $this->assertDatabaseHas('identities', ['id' => '1', 'description' => 'DNI']);
        $this->assertDatabaseHas('identities', ['id' => '-', 'description' => 'Sin documento']);
        $this->assertDatabaseCount('companies', 1);
        $this->assertDatabaseHas('companies', ['ruc' => '20474360955', 'business_name' => 'MACROMAR LOGISTICS']);
        $this->assertDatabaseCount('branches', 2);
        $this->assertDatabaseHas('branches', ['code' => '0000', 'name' => 'Sede Principal']);
        $this->assertDatabaseHas('branches', ['code' => '0001', 'name' => 'Depot Paita - Macromar']);
        $this->assertSame(2, Branch::query()->whereRelation('company', 'ruc', CompanySeeder::RUC)->count());
    }

    public function test_the_main_seeder_creates_the_first_user_with_a_dni_and_the_main_branch(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::query()->where('email', 'eddy.barranzuela@grupomacromar.com')->firstOrFail();

        $this->assertSame('DNI', $user->identity?->description);
        $this->assertSame('Sede Principal', $user->branch?->name);
    }

    public function test_the_migration_fills_the_users_that_already_exist(): void
    {
        $migration = $this->documentAndBranchMigration();
        $migration->down();

        $activeId = $this->insertUserWithoutDocument('activa@example.com');
        $deactivatedId = $this->insertUserWithoutDocument('inactiva@example.com', now());

        $migration->up();

        $mainBranch = Branch::query()->where('code', BranchSeeder::MAIN_BRANCH_CODE)->firstOrFail();

        foreach ([$activeId, $deactivatedId] as $userId) {
            $this->assertDatabaseHas('users', [
                'id' => $userId,
                'identity_id' => '-',
                'document_number' => (string) $userId,
                'branch_id' => $mainBranch->id,
            ]);
        }
    }

    public function test_the_migration_does_not_load_the_catalogs_when_there_are_no_users(): void
    {
        $migration = $this->documentAndBranchMigration();
        $migration->down();
        $migration->up();

        $this->assertSame(0, Identity::query()->count());
        $this->assertSame(0, Company::query()->count());
        $this->assertSame(0, Branch::query()->count());
    }

    private function documentAndBranchMigration(): Migration
    {
        return require database_path('migrations/2026_11_04_163353_add_document_and_branch_to_users_table.php');
    }

    private function insertUserWithoutDocument(string $email, mixed $deletedAt = null): int
    {
        return DB::table('users')->insertGetId([
            'name' => 'Usuaria Previa',
            'email' => $email,
            'password' => 'secret',
            'deleted_at' => $deletedAt,
        ]);
    }
}
