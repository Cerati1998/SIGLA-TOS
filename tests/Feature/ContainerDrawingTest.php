<?php

namespace Tests\Feature;

use App\Enums\CertificationSectionType;
use App\Enums\ContainerDrawing;
use Database\Seeders\CertificationSectionCatalogSeeder;
use Tests\TestCase;

class ContainerDrawingTest extends TestCase
{
    /**
     * The internal sections of the catalog that are not a place of the container, so they go only in the list.
     */
    private const array INTERNAL_WITHOUT_SPOT = ['EIR', 'DNI del conductor', 'Precinto de vacío provisional', 'Número de contenedor interno', 'Panorámica del contenedor'];

    public function test_every_section_of_the_catalog_has_a_spot_on_a_drawing_except_the_ones_that_are_not_a_place(): void
    {
        foreach (CertificationSectionCatalogSeeder::INTERNAL as $name) {
            $spot = ContainerDrawing::spotOf(CertificationSectionType::Internal, $name);

            if (in_array($name, self::INTERNAL_WITHOUT_SPOT, true)) {
                $this->assertNull($spot, $name);
            } else {
                $this->assertSame(ContainerDrawing::Inside, $spot[0] ?? null, $name);
            }
        }

        foreach (CertificationSectionCatalogSeeder::EXTERNAL as $name) {
            $this->assertContains(ContainerDrawing::spotOf(CertificationSectionType::External, $name)[0] ?? null, [ContainerDrawing::Doors, ContainerDrawing::Machine], $name);
        }

        $this->assertNull(ContainerDrawing::spotOf(CertificationSectionType::Internal, 'Sección nueva del administrador'));
    }

    public function test_the_devices_of_a_section_are_marked_side_by_side_on_its_spot_with_their_numbers(): void
    {
        $marks = ContainerDrawing::Inside->marks([
            ['number' => 1, 'section' => 'Techo'],
            ['number' => 2, 'section' => 'Techo'],
            ['number' => 3, 'section' => 'EIR'],
            ['number' => 4, 'section' => 'Piso'],
        ], 200);
        $height = ContainerDrawing::Inside->heightAt(200);

        $this->assertSame([1, 2, 4], array_column($marks, 'number'));
        $this->assertEqualsWithDelta([93.0, 107.0, 100.0], array_column($marks, 'left'), 0.01);
        $this->assertEqualsWithDelta([0.18 * $height, 0.18 * $height, 0.78 * $height], array_column($marks, 'top'), 0.01);
    }
}
