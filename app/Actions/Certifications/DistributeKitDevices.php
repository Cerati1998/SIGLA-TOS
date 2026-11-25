<?php

namespace App\Actions\Certifications;

use App\Enums\CertificationSectionType;
use App\Enums\KitAssignment;
use App\Enums\SecurityDeviceKind;
use App\Models\CertificationSection;
use App\Models\Kit;
use Illuminate\Support\Facades\DB;

/**
 * Places the devices of a kit in the active internal and external sections of the catalog (the checkpoint ones are
 * not part of the certification, H33), as many of each type as each section asks for
 * (H12), so the inspector does not choose them (H18, criterion 3). A type split between the internal and the
 * external assignment, as the inspection tape, goes to the sections of its side (T55). Each section takes the devices
 * with the lowest correlatives first. The kit fits when it has at least what the sections ask for (H17, criterion 16):
 * the devices left over stay in the kit without a section, because the driver takes them (T83). The pouch is left
 * out: it only holds the kit and is not used in the inspection (T76).
 */
class DistributeKitDevices
{
    /**
     * Get what is missing in the kit, as lines to show on screen; empty when the kit fits.
     *
     * @return list<string>
     */
    public function problems(Kit $kit): array
    {
        return $this->handle($kit)['problems'];
    }

    /**
     * Distribute the devices of the kit, in the order of the sections and of the correlatives. When the kit does not
     * fit, no section gets devices and the problems say why.
     *
     * @return array{steps: list<array{section: CertificationSection, deviceIds: list<int>}>, problems: list<string>}
     */
    public function handle(Kit $kit): array
    {
        $sections = CertificationSection::query()
            ->whereIn('type', CertificationSectionType::inspectionTypes())
            ->whereNotNull('sort_order')
            ->with('securityDeviceTypes')
            ->orderBy('sort_order')
            ->get();
        $pool = $this->kitDevices($kit);
        $required = [];
        $names = [];

        foreach ($sections as $section) {
            foreach ($section->securityDeviceTypes as $securityDeviceType) {
                $assignment = $securityDeviceType->splits_internal_external ? $this->assignmentOf($section->type) : null;
                $key = Kit::slotKey($securityDeviceType->id, $assignment);
                /** @var object{quantity: int} $pivot */
                $pivot = $securityDeviceType->getRelationValue('pivot');

                $required[$key] = ($required[$key] ?? 0) + (int) $pivot->quantity;
                $names[$key] = $this->slotName($securityDeviceType->name, $assignment);
            }
        }

        $problems = [];

        foreach ($required as $key => $quantity) {
            $missing = $quantity - count($pool[$key] ?? []);

            if ($missing > 0) {
                $problems[] = sprintf('%s %d · %s', $missing === 1 ? 'Falta' : 'Faltan', $missing, $names[$key]);
            }
        }

        if ($problems !== []) {
            return ['steps' => [], 'problems' => $problems];
        }

        $devices = $pool;
        $steps = [];

        foreach ($sections as $section) {
            $deviceIds = [];

            foreach ($section->securityDeviceTypes as $securityDeviceType) {
                $assignment = $securityDeviceType->splits_internal_external ? $this->assignmentOf($section->type) : null;
                $key = Kit::slotKey($securityDeviceType->id, $assignment);
                /** @var object{quantity: int} $pivot */
                $pivot = $securityDeviceType->getRelationValue('pivot');

                array_push($deviceIds, ...array_splice($devices[$key], 0, (int) $pivot->quantity));
            }

            $steps[] = ['section' => $section, 'deviceIds' => $deviceIds];
        }

        return ['steps' => $steps, 'problems' => []];
    }

    /**
     * Get the devices of the kit grouped by slot, each slot from the lowest correlative to the highest one: by series
     * (prefix and suffix) and, within it, by the number of the correlative, so that 9 goes before 10.
     *
     * @return array<string, list<int>>
     */
    private function kitDevices(Kit $kit): array
    {
        $rows = DB::table('kit_security_device')
            ->join('security_devices', 'security_devices.id', '=', 'kit_security_device.security_device_id')
            ->join('security_device_lots', 'security_device_lots.id', '=', 'security_devices.security_device_lot_id')
            ->join('security_device_types', 'security_device_types.id', '=', 'security_device_lots.security_device_type_id')
            ->where('kit_security_device.kit_id', $kit->id)
            ->where('security_device_types.kind', '!=', SecurityDeviceKind::Pouch->value)
            ->get([
                'security_devices.id',
                'security_devices.serial_number',
                'security_device_lots.series_prefix',
                'security_device_lots.series_suffix',
                'security_device_types.id as type_id',
                'kit_security_device.assignment',
            ])
            ->map(fn (object $row): array => [
                'id' => (int) $row->id,
                'key' => Kit::slotKey((int) $row->type_id, KitAssignment::tryFrom((string) $row->assignment)),
                'order' => [
                    (string) $row->series_prefix,
                    (string) $row->series_suffix,
                    $this->correlativeOf((string) $row->serial_number, (string) $row->series_prefix, (string) $row->series_suffix),
                    (string) $row->serial_number,
                ],
            ])
            ->sortBy(fn (array $device): array => $device['order'])
            ->all();
        $devices = [];

        foreach ($rows as $row) {
            $devices[$row['key']][] = $row['id'];
        }

        return $devices;
    }

    /**
     * Get the correlative of a device: its serial number without the prefix and the suffix of its lot.
     */
    private function correlativeOf(string $serialNumber, string $prefix, string $suffix): int
    {
        return (int) substr($serialNumber, strlen($prefix), strlen($serialNumber) - strlen($prefix) - strlen($suffix));
    }

    /**
     * Get the side of a split type that the sections of a type take.
     */
    private function assignmentOf(CertificationSectionType $type): KitAssignment
    {
        return $type === CertificationSectionType::Internal ? KitAssignment::Internal : KitAssignment::External;
    }

    /**
     * Get the name of a slot, with its side when the type is split.
     */
    private function slotName(string $typeName, ?KitAssignment $assignment): string
    {
        return $assignment === null ? $typeName : "{$typeName} · {$assignment->label()}";
    }
}
