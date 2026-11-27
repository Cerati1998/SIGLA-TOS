<?php

namespace Database\Seeders;

use App\Enums\CertificationSectionType;
use App\Enums\CheckpointSectionKind;
use App\Models\CertificationSection;
use App\Services\CertificationSectionOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * The catalog of sections agreed on 2026-11-23 (H33): the internal and external ones of the document «Proceso de
 * certificación de contenedores» and the checkpoint ones of the video of the current checkpoint, with the sections each
 * verification shows. Only the sections, without their devices: the admin sets what each one carries (H12).
 *
 * A section that already exists with the same name and type is kept as it is, even if it was deactivated: running
 * it again adds nothing. The new ones go to the end of the order. The migration that loads the catalog runs it.
 */
class CertificationSectionCatalogSeeder extends Seeder
{
    /**
     * The internal sections, in the order of the document. Its item 1 (the container number) is not a section.
     */
    public const array INTERNAL = [
        'EIR',
        'DNI del conductor',
        'Precinto de vacío provisional',
        'Número de contenedor interno',
        'Puerta lado derecho',
        'Puerta lado izquierdo',
        'Kit de precintos en piso',
        'Pared interna',
        'Cintas de pared interna',
        'Panel lado derecho',
        'Panel lado izquierdo',
        'Techo',
        'Piso',
        'Puerta cerrada y panorámica',
        'Candado GPS',
        'Cinta unión vacío',
        'Panorámica del contenedor',
    ];

    /**
     * The external sections, in the order of the document. Its item 1 (the container number) is not a section.
     */
    public const array EXTERNAL = [
        'Panorámica frontal',
        'Pared exterior izquierda',
        'Piso exterior lado izquierdo',
        'Pared exterior derecha',
        'Piso exterior lado derecho',
        'Cable de alimentación o manga',
        'Proceso PTI',
        'Techo',
        'Evaporadores abiertos',
        'Evaporador lado derecho',
        'Evaporador lado izquierdo',
        'Evaporadores cerrados',
        'Ventilador',
        'Panorámica con cintas de seguridad',
        'Dibujo parte externa derecha',
        'Dibujo parte externa izquierda',
    ];

    /**
     * The checkpoint sections, in the order of the video, each with the internal or external sections it verifies.
     * Without sections to verify, it asks for photos.
     *
     * @var array<string, list<array{string, CertificationSectionType}>>
     */
    public const array CHECKPOINT = [
        'Placa, cédula y transporte' => [],
        'Panorámica del panel frontal' => [],
        'Cintas de evaporadores y máquina' => [],
        'Verificación de cintas de evaporadores' => [['Evaporadores cerrados', CertificationSectionType::External], ['Ventilador', CertificationSectionType::External]],
        'Paredes, pisos externos y puertas' => [],
        'Cinta de puerta' => [],
        'Verificación de cinta de puerta' => [['Kit de precintos en piso', CertificationSectionType::Internal]],
        'Sellos de naviera' => [],
        'Sello cable' => [],
        'Verificación de sello cable' => [['Puerta cerrada y panorámica', CertificationSectionType::Internal]],
        'Cintas de puerta y checkpoint' => [],
        'Panorámica de puertas' => [],
    ];

    /**
     * The verification sections where an incorrect series does not ask for a reinspection (T91, team decision of
     * 2026-11-27).
     *
     * @var list<string>
     */
    public const array SKIPS_REINSPECTION = ['Verificación de cinta de puerta'];

    public function __construct(private CertificationSectionOrder $order) {}

    /**
     * Load the catalog, all or nothing.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            foreach (self::INTERNAL as $name) {
                $this->section($name, CertificationSectionType::Internal);
            }

            foreach (self::EXTERNAL as $name) {
                $this->section($name, CertificationSectionType::External);
            }

            foreach (self::CHECKPOINT as $name => $verified) {
                $kind = $verified === [] ? CheckpointSectionKind::Photos : CheckpointSectionKind::Verification;
                $section = $this->section($name, CertificationSectionType::Checkpoint, $kind);

                if ($section->wasRecentlyCreated) {
                    $section->update(['skips_reinspection' => in_array($name, self::SKIPS_REINSPECTION, true)]);
                    $section->verifiedSections()->sync(array_map(
                        fn (array $link): int => $this->find($link[0], $link[1])->id,
                        $verified,
                    ));
                }
            }
        });
    }

    /**
     * Get the section with the given name and type, creating it at the end of the order when it does not exist.
     */
    private function section(string $name, CertificationSectionType $type, ?CheckpointSectionKind $kind = null): CertificationSection
    {
        return CertificationSection::withTrashed()->firstOrCreate(
            ['name' => $name, 'type' => $type],
            ['checkpoint_kind' => $kind, 'sort_order' => $this->order->nextPosition()],
        );
    }

    /**
     * Get the section with the given name and type, which the catalog has just made sure exists.
     */
    private function find(string $name, CertificationSectionType $type): CertificationSection
    {
        return CertificationSection::withTrashed()->where('name', $name)->where('type', $type)->firstOrFail();
    }
}
