<?php

namespace Tests\Feature;

use App\Models\ShippingLine;
use App\Services\ShippingLineRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingLineRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_line_is_found_by_its_name_without_minding_upper_or_lower_case(): void
    {
        $seatrade = ShippingLine::factory()->create(['code' => 'SEA', 'name' => 'Seatrade']);

        $this->assertTrue($seatrade->is(app(ShippingLineRegistry::class)->findOrRegister('  SEATRADE ')));
        $this->assertSame(1, ShippingLine::query()->count());
    }

    public function test_a_deactivated_line_with_that_name_is_reactivated_instead_of_registering_another(): void
    {
        $seatrade = ShippingLine::factory()->create(['code' => 'SEA', 'name' => 'SEATRADE']);
        $seatrade->delete();

        $found = app(ShippingLineRegistry::class)->findOrRegister('SEATRADE');

        $this->assertTrue($found->is($seatrade));
        $this->assertFalse($found->trashed());
        $this->assertSame(1, ShippingLine::withTrashed()->count());
    }

    public function test_a_new_line_gets_the_first_four_characters_of_its_name_as_its_code(): void
    {
        $line = app(ShippingLineRegistry::class)->findOrRegister('Hapag Lloyd');

        $this->assertSame(['HAPA', 'Hapag Lloyd'], [$line->code, $line->name]);
    }

    public function test_a_code_that_is_taken_even_by_a_deactivated_line_gets_a_number(): void
    {
        ShippingLine::factory()->create(['code' => 'MAER', 'name' => 'Maersk']);
        ShippingLine::factory()->create(['code' => 'MAER1', 'name' => 'Maersk Antigua'])->delete();
        $registry = app(ShippingLineRegistry::class);

        $this->assertSame('MAER2', $registry->findOrRegister('MAERSK LINE')->code);
    }
}
