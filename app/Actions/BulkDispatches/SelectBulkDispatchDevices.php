<?php

namespace App\Actions\BulkDispatches;

use App\Enums\SecurityDeviceStatus;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use Illuminate\Support\Collection;

/**
 * Choose the devices of a bulk dispatch by range or by quantity (H30, criteria 6 to 8). Both are all or nothing: the
 * answer has the devices to add, or the reason none was added.
 */
class SelectBulkDispatchDevices
{
    /**
     * The most devices a range or a quantity can bring, as in H29.
     */
    public const int MAX_DEVICES = 10000;

    /**
     * How many rejected devices the message names at most.
     */
    private const int NAMED_REJECTIONS = 5;

    /**
     * The rows looked up per query.
     */
    private const int CHUNK_SIZE = 1000;

    public function __construct(private FindDeviceToBulkDispatch $findDeviceToBulkDispatch) {}

    /**
     * Choose the devices from the first serial number to the last one, both of the same lot. The correlatives without
     * a device are skipped.
     *
     * @return array{deviceIds: list<int>, error: string|null, summary: string|null}
     */
    public function byRange(string $firstSerialNumber, string $lastSerialNumber): array
    {
        $first = $this->device($firstSerialNumber);
        $last = $this->device($lastSerialNumber);

        if (is_string($first)) {
            return $this->rejected($first);
        }

        if (is_string($last)) {
            return $this->rejected($last);
        }

        if ($first->security_device_lot_id !== $last->security_device_lot_id) {
            return $this->rejected('La serie inicial y la final deben ser del mismo lote.');
        }

        $lot = $first->lot;
        $start = (int) $lot->announcement->correlativeOf($first->serial_number);
        $end = (int) $lot->announcement->correlativeOf($last->serial_number);

        if ($start > $end) {
            return $this->rejected('La serie inicial no puede ser mayor que la final.');
        }

        if ($end - $start + 1 > self::MAX_DEVICES) {
            return $this->rejected(sprintf('Un rango puede tener como máximo %s dispositivos. Este tiene %s.', self::format(self::MAX_DEVICES), self::format($end - $start + 1)));
        }

        $devices = $this->devicesOfStretch($lot, $start, $end);
        $rejections = [];

        foreach ($devices as $device) {
            $reason = $this->findDeviceToBulkDispatch->rejectionReason($device);

            if ($reason !== null) {
                $rejections[] = "{$device->serial_number}: {$reason}";
            }
        }

        if ($rejections !== []) {
            return $this->rejected(sprintf(
                'No se agregó ninguno: %s de los %s dispositivos no se pueden despachar. %s',
                self::format(count($rejections)),
                self::format($devices->count()),
                $this->describe($rejections),
            ));
        }

        return $this->chosen($devices, $lot->announcement->securityDeviceType->name);
    }

    /**
     * Choose the given quantity of Available devices of the type, in the order of the correlatives. The start is a whole
     * serial number, as HLS0000009, from which its lot is taken, or only the prefix of a series, as HLS, from its first
     * device. The devices that cannot be dispatched and the ones already chosen are skipped.
     *
     * @param  list<int>  $chosenIds  The devices already in the dispatch, which are not chosen again.
     * @return array{deviceIds: list<int>, error: string|null, summary: string|null}
     */
    public function byQuantity(SecurityDeviceType $type, string $start, int $quantity, array $chosenIds = []): array
    {
        if ($quantity < 1 || $quantity > self::MAX_DEVICES) {
            return $this->rejected(sprintf('La cantidad debe estar entre 1 y %s.', self::format(self::MAX_DEVICES)));
        }

        $first = SecurityDevice::query()->with('lot.announcement.securityDeviceType')->where('serial_number', $start)->first();
        $lots = $first === null ? $this->lotsOfPrefix($start) : collect([$first->lot]);
        $lotOfAnotherType = $lots->first(fn (SecurityDeviceLot $lot): bool => $lot->announcement->security_device_type_id !== $type->id);

        if ($lots->isEmpty()) {
            return $this->rejected(sprintf('No encontramos un dispositivo ni una serie %s. Escribe la serie completa (por ejemplo HLS0000009) o solo su prefijo (HLS).', $start));
        }

        if ($lotOfAnotherType !== null) {
            return $this->rejected(sprintf('La serie %s es de %s, no de %s.', $start, $lotOfAnotherType->announcement->securityDeviceType->name, $type->name));
        }

        $from = $first === null ? null : $first->lot->announcement->correlativeOf($first->serial_number);
        $devices = $lots
            ->flatMap(fn (SecurityDeviceLot $lot): array => $this->dispatchableDevices($lot)
                ->reject(fn (SecurityDevice $device): bool => in_array($device->id, $chosenIds, true))
                ->map(fn (SecurityDevice $device): array => ['device' => $device, 'lot' => $lot->id, 'correlative' => $lot->announcement->correlativeOf($device->serial_number)])
                ->all())
            ->filter(fn (array $item): bool => $item['correlative'] !== null && ($from === null || $item['correlative'] >= $from))
            ->sortBy([['correlative', 'asc'], ['lot', 'asc']])
            ->map(fn (array $item): SecurityDevice => $item['device'])
            ->values();

        if ($devices->count() < $quantity) {
            return $this->rejected(sprintf(
                '%s solo quedan %s %s disponibles: no alcanza para %s. Baja la cantidad o elige otra serie inicial.',
                $first === null ? 'De la serie '.$start : 'Desde '.$start,
                self::format($devices->count()),
                $type->name,
                self::format($quantity),
            ));
        }

        return $this->chosen($devices->take($quantity)->values(), $type->name);
    }

    /**
     * Get the lots whose series has the given prefix, as HLS.
     *
     * @return Collection<int, SecurityDeviceLot>
     */
    private function lotsOfPrefix(string $prefix): Collection
    {
        return SecurityDeviceLot::query()
            ->with('announcement.securityDeviceType')
            ->whereHas('announcement', fn ($query) => $query->where('series_prefix', $prefix))
            ->orderBy('id')
            ->get()
            ->collect();
    }

    /**
     * Find a device by its serial number, with its lot, or say that it does not exist.
     */
    private function device(string $serialNumber): SecurityDevice|string
    {
        return SecurityDevice::query()->with('lot.announcement.securityDeviceType')->where('serial_number', $serialNumber)->first()
            ?? sprintf('No encontramos un dispositivo con la serie %s.', $serialNumber);
    }

    /**
     * Get the devices of the lot from the first correlative to the last one, in that order, with what tells whether
     * they can be dispatched.
     *
     * @return Collection<int, SecurityDevice>
     */
    private function devicesOfStretch(SecurityDeviceLot $lot, int $start, int $end): Collection
    {
        $devices = collect();

        foreach (array_chunk(array_map($lot->announcement->serialNumber(...), range($start, $end)), self::CHUNK_SIZE) as $chunk) {
            $found = $lot->devices()->with(['kits', 'bulkDispatches'])->whereIn('serial_number', $chunk)->get()->keyBy('serial_number');

            foreach ($chunk as $serialNumber) {
                if ($found->has($serialNumber)) {
                    $devices->push($found->get($serialNumber));
                }
            }
        }

        return $devices;
    }

    /**
     * Get the devices of the lot that can be dispatched in bulk: Available, out of a kit and out of a bulk dispatch.
     *
     * @return Collection<int, SecurityDevice>
     */
    private function dispatchableDevices(SecurityDeviceLot $lot): Collection
    {
        return $lot->devices()
            ->where('status', SecurityDeviceStatus::Available->value)
            ->whereDoesntHave('kits')
            ->whereDoesntHave('bulkDispatches')
            ->get(['security_devices.id', 'security_devices.serial_number']);
    }

    /**
     * Get the answer with the devices chosen and a summary for the preview, as in "Sello botella · SB001061 a SB001062
     * · 2 dispositivos".
     *
     * @param  Collection<int, SecurityDevice>  $devices
     * @return array{deviceIds: list<int>, error: null, summary: string|null}
     */
    private function chosen(Collection $devices, string $typeName): array
    {
        $first = $devices->first();
        $last = $devices->last();

        if ($first === null || $last === null) {
            return ['deviceIds' => [], 'error' => null, 'summary' => null];
        }

        $count = $devices->count();
        $range = $count === 1 ? $first->serial_number : $first->serial_number.' a '.$last->serial_number;

        return [
            'deviceIds' => array_values($devices->map(fn (SecurityDevice $device): int => $device->id)->all()),
            'error' => null,
            'summary' => sprintf('%s · %s · %s %s', $typeName, $range, self::format($count), $count === 1 ? 'dispositivo' : 'dispositivos'),
        ];
    }

    /**
     * Get the answer of a range or a quantity that adds nothing.
     *
     * @return array{deviceIds: list<int>, error: string, summary: null}
     */
    private function rejected(string $error): array
    {
        return ['deviceIds' => [], 'error' => $error, 'summary' => null];
    }

    /**
     * Name the first rejected devices with their reason, and say how many more there are.
     *
     * @param  list<string>  $rejections
     */
    private function describe(array $rejections): string
    {
        $named = implode(' ', array_slice($rejections, 0, self::NAMED_REJECTIONS));
        $more = count($rejections) - self::NAMED_REJECTIONS;

        return $more > 0 ? sprintf('%s Y %s más.', $named, self::format($more)) : $named;
    }

    /**
     * Format a whole number with a space between thousands, as in 10 000.
     */
    private static function format(int $number): string
    {
        return number_format($number, 0, ',', ' ');
    }
}
