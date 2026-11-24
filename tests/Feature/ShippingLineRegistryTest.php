<?php

namespace Tests\Feature;

use App\Models\ShippingLine;
use App\Services\ShippingLineRegistry;
use Database\Seeders\ShippingLineSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingLineRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_line_matches_by_the_sigla1_name_it_remembers(): void
    {
        $chiquita = ShippingLine::factory()->create(['code' => 'CHQT', 'name' => 'Chiquita Brands International', 'sigla1_name' => 'CHIQUITA']);
        ShippingLine::factory()->create(['code' => 'CHIQ', 'name' => 'Chiquita']);

        $this->assertTrue($chiquita->is(app(ShippingLineRegistry::class)->match(' chiquita ')));
    }

    public function test_a_line_matches_by_its_exact_name_or_code_without_minding_upper_or_lower_case(): void
    {
        $seatrade = ShippingLine::factory()->create(['code' => 'SEAT', 'name' => 'Seatrade']);
        ShippingLine::factory()->create(['code' => 'SEAL', 'name' => 'Seatrade Reefer Chartering']);
        $registry = app(ShippingLineRegistry::class);

        $this->assertTrue($seatrade->is($registry->match('SEATRADE')));
        $this->assertTrue($seatrade->is($registry->match('seat')));
    }

    public function test_a_line_matches_by_the_start_of_its_name_and_then_by_any_part_of_it(): void
    {
        $chiquita = ShippingLine::factory()->create(['code' => 'CHQT', 'name' => 'Chiquita Brands International']);
        $msc = ShippingLine::factory()->create(['code' => 'MSCU', 'name' => 'Mediterranean Shipping Company (MSC)']);
        $registry = app(ShippingLineRegistry::class);

        $this->assertTrue($chiquita->is($registry->match('CHIQUITA')));
        $this->assertTrue($msc->is($registry->match('MSC')));
    }

    public function test_when_several_lines_match_none_is_taken_and_they_are_the_candidates(): void
    {
        $delMonte = ShippingLine::factory()->create(['code' => 'DLMT', 'name' => 'Del Monte Shipping']);
        $greatWhite = ShippingLine::factory()->create(['code' => 'GREX', 'name' => 'Great White Fleet (Del Monte)']);
        $registry = app(ShippingLineRegistry::class);

        $this->assertNull($registry->match('MONTE'));
        $this->assertEqualsCanonicalizing([$delMonte->id, $greatWhite->id], $registry->candidates('MONTE')->modelKeys());
    }

    public function test_a_deactivated_line_does_not_match_and_no_line_is_registered(): void
    {
        ShippingLine::factory()->create(['code' => 'SEAT', 'name' => 'Seatrade', 'sigla1_name' => 'SEATRADE'])->delete();
        $registry = app(ShippingLineRegistry::class);

        $this->assertNull($registry->match('SEATRADE'));
        $this->assertNull($registry->match('HAPAG LLOYD AG'));
        $this->assertSame(1, ShippingLine::withTrashed()->count());
    }

    public function test_the_line_chosen_remembers_the_sigla1_name_even_if_a_deactivated_line_had_it(): void
    {
        $old = ShippingLine::factory()->create(['code' => 'HAPA', 'name' => 'Hapag Antigua', 'sigla1_name' => 'HAPAG LLOYD AG']);
        $old->delete();
        $hapag = ShippingLine::factory()->create(['code' => 'HLCU', 'name' => 'Hapag-Lloyd']);
        $registry = app(ShippingLineRegistry::class);

        $registry->remember($hapag, ' hapag  lloyd ag ');

        $this->assertSame('HAPAG LLOYD AG', $hapag->fresh()?->sigla1_name);
        $this->assertNull($old->fresh()?->sigla1_name);
        $this->assertTrue($hapag->is($registry->match('HAPAG LLOYD AG')));
    }

    public function test_every_line_of_sigla1_squares_with_its_seeded_line(): void
    {
        $this->seed(ShippingLineSeeder::class);
        $registry = app(ShippingLineRegistry::class);

        $sigla1Lines = [
            'SEATRADE' => 'SEAT', 'MAERSK LINE' => 'MAEU', 'CHIQUITA' => 'CHQT', 'SEABOARD' => 'SEAB', 'COSCO' => 'COSU',
            'ZIM' => 'ZIMU', 'ONE SHIPPING' => 'ONEY', 'MEDITERRANEAN SHIPPING MSC' => 'MSCU',
            'CONTAINER SUDAMERICA PERU' => 'CSP', 'SERVICONTAINER' => 'SVC', 'MACROMAR LOGISTICS SAC' => 'MLS',
        ];

        foreach ($sigla1Lines as $sigla1Name => $code) {
            $this->assertSame($code, $registry->match($sigla1Name)?->code, $sigla1Name);
        }
    }
}
