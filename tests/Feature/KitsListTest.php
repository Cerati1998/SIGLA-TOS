<?php

namespace Tests\Feature;

use App\Enums\KitStatus;
use App\Livewire\Tables\KitsTable;
use App\Models\Kit;
use App\Models\KitType;
use App\Models\ShippingLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KitsListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_the_list_finds_a_kit_by_its_pouch_typed_or_scanned(): void
    {
        Kit::factory()->create(['pouch_code' => 'KIT-000046']);
        Kit::factory()->create(['pouch_code' => 'KIT-000047']);

        Livewire::test(KitsTable::class)
            ->assertSeeText('KIT-000046')
            ->assertSeeText('KIT-000047')
            ->set('search', 'KIT-000046')
            ->assertSeeText('KIT-000046')
            ->assertDontSeeText('KIT-000047');
    }

    public function test_the_list_filters_the_kits_by_kit_type_shipping_line_week_and_status(): void
    {
        $reeferKit = KitType::factory()->create(['name' => 'Kit de seguridad reefer']);
        $sealsKit = KitType::factory()->create(['name' => 'Kit de cambio de sellos']);
        $maersk = ShippingLine::factory()->create(['code' => 'MAEU']);
        $hapag = ShippingLine::factory()->create(['code' => 'HLCU']);

        Kit::factory()->for($reeferKit)->for($maersk)->create(['pouch_code' => 'KIT-000046', 'status' => KitStatus::Built]);
        Kit::factory()->for($sealsKit)->for($hapag)->create(['pouch_code' => 'KIT-000047', 'status' => KitStatus::Dispatched]);
        Kit::factory()->for($reeferKit)->for($maersk)->create(['pouch_code' => 'KIT-000048', 'status' => KitStatus::Built, 'created_at' => now()->subWeeks(3)]);

        Livewire::test(KitsTable::class)
            ->set('filterComponents.kit_type', (string) $sealsKit->id)
            ->assertSeeText('KIT-000047')
            ->assertDontSeeText('KIT-000046');

        Livewire::test(KitsTable::class)
            ->set('filterComponents.shipping_line', (string) $maersk->id)
            ->assertSeeText('KIT-000046')
            ->assertDontSeeText('KIT-000047');

        Livewire::test(KitsTable::class)
            ->set('filterComponents.week', now('America/Lima')->format('o-\WW'))
            ->assertSeeText('KIT-000046')
            ->assertSeeText('KIT-000047')
            ->assertDontSeeText('KIT-000048');

        Livewire::test(KitsTable::class)
            ->set('filterComponents.status', KitStatus::Dispatched->value)
            ->assertSeeText('KIT-000047')
            ->assertDontSeeText('KIT-000046')
            ->assertDontSeeText('KIT-000048');
    }
}
