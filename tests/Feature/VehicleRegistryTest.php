<?php

namespace Tests\Feature;

use App\Enums\VehicleCategory;
use App\Enums\VehicleKind;
use App\Models\Vehicle;
use App\Models\VehicleBody;
use App\Services\VehicleRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class VehicleRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_vehicle_not_in_the_catalog_is_registered_with_the_data_of_sigla_1(): void
    {
        $vehicle = app(VehicleRegistry::class)->findOrRegister(
            ['plate' => 'abc-123', 'brand' => ' VOLVO ', 'category' => 'n3', 'body' => ' remolcador ', 'color' => ' blanco '],
            VehicleKind::Tractor,
        );

        $this->assertSame('ABC123', $vehicle->plate);
        $this->assertSame('VOLVO', $vehicle->brand);
        $this->assertSame(VehicleKind::Tractor, $vehicle->kind);
        $this->assertSame(VehicleCategory::N3, $vehicle->category);
        $this->assertSame('REMOLCADOR', $vehicle->body->name);
        $this->assertSame('BLANCO', $vehicle->color);
    }

    public function test_an_existing_vehicle_is_returned_without_changes_even_if_it_is_deactivated(): void
    {
        $existing = Vehicle::factory()->trashed()->create(['plate' => 'ABC123', 'brand' => 'MARCA GUARDADA']);

        $vehicle = app(VehicleRegistry::class)->findOrRegister(
            ['plate' => 'ABC123', 'brand' => 'OTRA MARCA', 'category' => 'O4', 'body' => 'PLATAFORMA', 'color' => null],
            VehicleKind::SemiTrailer,
        );

        $this->assertTrue($vehicle->is($existing));
        $this->assertSame('MARCA GUARDADA', $vehicle->brand);
        $this->assertDatabaseCount('vehicles', 1);
        $this->assertDatabaseMissing('vehicle_bodies', ['name' => 'PLATAFORMA']);
    }

    public function test_an_existing_body_is_reused_even_if_it_is_deactivated(): void
    {
        $body = VehicleBody::factory()->trashed()->create(['name' => 'PLATAFORMA']);

        $vehicle = app(VehicleRegistry::class)->findOrRegister(
            ['plate' => 'XYZ789', 'brand' => 'RANDON', 'category' => 'O4', 'body' => 'plataforma', 'color' => null],
            VehicleKind::SemiTrailer,
        );

        $this->assertSame($body->id, $vehicle->vehicle_body_id);
        $this->assertDatabaseCount('vehicle_bodies', 1);
    }

    public function test_a_category_out_of_the_list_is_stored_as_other(): void
    {
        $vehicle = app(VehicleRegistry::class)->findOrRegister(
            ['plate' => 'XYZ789', 'brand' => 'RANDON', 'category' => 'OTRO', 'body' => 'PLATAFORMA', 'color' => null],
            VehicleKind::SemiTrailer,
        );

        $this->assertSame(VehicleCategory::Other, $vehicle->category);
    }

    public function test_a_vehicle_without_plate_brand_or_body_is_not_registered(): void
    {
        $this->expectException(InvalidArgumentException::class);

        try {
            app(VehicleRegistry::class)->findOrRegister(
                ['plate' => 'XYZ789', 'brand' => 'RANDON', 'category' => 'O4', 'body' => null, 'color' => null],
                VehicleKind::SemiTrailer,
            );
        } finally {
            $this->assertDatabaseCount('vehicles', 0);
        }
    }
}
