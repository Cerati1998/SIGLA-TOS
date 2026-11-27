<?php

namespace App\Enums;

/**
 * The three drawings of the container in the PDF of a certification (H20, criterion 6), each with the spot of the
 * catalog sections it shows, so the devices of a section are marked there with their number (T73). Left and right
 * are seen as in the internal inspection: standing at the doors, looking inside. A section without a spot, or one the
 * administrator added later, goes only in the list.
 */
enum ContainerDrawing: string
{
    case Inside = 'inside';
    case Doors = 'doors';
    case Machine = 'machine';

    /**
     * Get the drawings of the sections of a type, in the order the PDF shows them.
     *
     * @return list<self>
     */
    public static function forType(CertificationSectionType $type): array
    {
        return match ($type) {
            CertificationSectionType::Internal => [self::Inside],
            CertificationSectionType::External => [self::Doors, self::Machine],
            CertificationSectionType::Checkpoint => [],
        };
    }

    /**
     * Get the type of the sections this drawing shows.
     */
    public function sectionType(): CertificationSectionType
    {
        return $this === self::Inside ? CertificationSectionType::Internal : CertificationSectionType::External;
    }

    /**
     * Get the caption shown under the drawing.
     */
    public function caption(): string
    {
        return match ($this) {
            self::Inside => 'Interior · puertas abiertas',
            self::Doors => 'Exterior · lado de las puertas',
            self::Machine => 'Exterior · lado de la máquina',
        };
    }

    /**
     * Get the path of the image of the drawing.
     */
    public function path(): string
    {
        return public_path(match ($this) {
            self::Inside => 'images/container-eir/contenedor-interior.png',
            self::Doors => 'images/container-eir/contenedor-puertas.png',
            self::Machine => 'images/container-eir/contenedor-maquina.png',
        });
    }

    /**
     * Get the spot of each section on the drawing, by the name of the section, as percentages of the width and the
     * height of the drawing (design «T73 · Lugar de cada sección en los dibujos», approved on 2026-11-27).
     *
     * @return array<string, array{float, float}>
     */
    public function spots(): array
    {
        return match ($this) {
            self::Inside => [
                'Puerta lado derecho' => [89, 45],
                'Puerta lado izquierdo' => [11, 45],
                'Kit de precintos en piso' => [36, 90],
                'Pared interna' => [50, 40],
                'Cintas de pared interna' => [50, 58],
                'Panel lado derecho' => [69, 50],
                'Panel lado izquierdo' => [31, 50],
                'Techo' => [50, 18],
                'Piso' => [50, 78],
                'Puerta cerrada y panorámica' => [11, 75],
                'Candado GPS' => [89, 75],
                'Cinta unión vacío' => [64, 90],
            ],
            self::Doors => [
                'Pared exterior derecha' => [75, 46],
                'Techo' => [52, 22],
                'Panorámica con cintas de seguridad' => [26, 72],
                'Dibujo parte externa derecha' => [88, 33],
            ],
            self::Machine => [
                'Panorámica frontal' => [26, 16],
                'Pared exterior izquierda' => [75, 54],
                'Piso exterior lado izquierdo' => [60, 80],
                'Piso exterior lado derecho' => [40, 66],
                'Cable de alimentación o manga' => [39, 42],
                'Proceso PTI' => [13, 42],
                'Evaporadores abiertos' => [13, 16],
                'Evaporador lado derecho' => [39, 29],
                'Evaporador lado izquierdo' => [13, 29],
                'Evaporadores cerrados' => [39, 16],
                'Ventilador' => [26, 29],
                'Dibujo parte externa izquierda' => [89, 68],
            ],
        };
    }

    /**
     * Get the image of the drawing as a data URI, to put it inside a PDF.
     */
    public function dataUri(): string
    {
        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($this->path()));
    }

    /**
     * Get the height of the drawing shown at a width, keeping its proportions.
     */
    public function heightAt(float $width): float
    {
        $size = getimagesize($this->path());

        return $size === false ? 0.0 : $width * $size[1] / $size[0];
    }

    /**
     * Get the numbered marks of the devices whose section has a spot on this drawing, for the drawing shown at a
     * width. The marks of a section go side by side, centered on its spot, `gap` px apart; `left` and `top` are the
     * center of each mark, in px from the top left corner of the drawing.
     *
     * @param  iterable<array{number: int, section: string}>  $devices
     * @return list<array{number: int, left: float, top: float}>
     */
    public function marks(iterable $devices, float $width, float $gap = 14): array
    {
        $height = $this->heightAt($width);
        $spots = $this->spots();
        $numbersBySection = [];

        foreach ($devices as $device) {
            if (isset($spots[$device['section']])) {
                $numbersBySection[$device['section']][] = $device['number'];
            }
        }

        $marks = [];

        foreach ($numbersBySection as $section => $numbers) {
            [$left, $top] = $spots[$section];

            foreach ($numbers as $index => $number) {
                $marks[] = [
                    'number' => $number,
                    'left' => $left / 100 * $width + ($index - (count($numbers) - 1) / 2) * $gap,
                    'top' => $top / 100 * $height,
                ];
            }
        }

        return $marks;
    }

    /**
     * Get the drawing and the spot of a section, or null when no drawing shows it.
     *
     * @return array{self, float, float}|null
     */
    public static function spotOf(CertificationSectionType $type, string $sectionName): ?array
    {
        foreach (self::forType($type) as $drawing) {
            $spot = $drawing->spots()[$sectionName] ?? null;

            if ($spot !== null) {
                return [$drawing, $spot[0], $spot[1]];
            }
        }

        return null;
    }
}
