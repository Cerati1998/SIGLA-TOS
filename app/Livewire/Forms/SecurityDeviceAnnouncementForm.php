<?php

namespace App\Livewire\Forms;

use App\Actions\SecurityDevices\ReceiveSecurityDeviceLots;
use App\Models\SecurityDeviceAnnouncement;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceType;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Livewire\Attributes\Locked;
use Livewire\Form;

/**
 * An announcement of the entry of lots (H37): its header and the new lots it brings. The lots are added to the list in
 * the browser, and every rule is checked again here, on save.
 */
class SecurityDeviceAnnouncementForm extends Form
{
    public string $shippingLineId = '';

    public string $providerId = '';

    public string $receivedAt = '';

    public string $dispatchNumber = '';

    /**
     * The new lots of the announcement, as the browser listed them.
     *
     * @var array<int, array{securityDeviceTypeId: string, seriesPrefix: string, minDigits: string, seriesStart: string, seriesEnd: string, seriesSuffix: string, notes: string}>
     */
    public array $lots = [];

    /**
     * The positions of the lots whose serial numbers collide, to mark them in the list.
     *
     * @var list<int>
     */
    #[Locked]
    public array $collidingLots = [];

    /**
     * How many devices the lots already saved in the announcement bring, which count for its maximum.
     */
    #[Locked]
    public int $savedDevices = 0;

    /**
     * The shipping line and the provider of the announcement being corrected. They stay a valid choice even if they
     * were deactivated later.
     *
     * @var array{shippingLineId: int|null, providerId: int|null}
     */
    #[Locked]
    public array $currentOriginIds = ['shippingLineId' => null, 'providerId' => null];

    /**
     * Start the form with today as the reception date.
     */
    public function setDefaults(): void
    {
        $this->receivedAt = now('America/Lima')->toDateString();
    }

    /**
     * Fill the header with an announcement already saved, to correct it or to add lots to it.
     */
    public function setAnnouncement(SecurityDeviceAnnouncement $announcement): void
    {
        $this->shippingLineId = (string) $announcement->shipping_line_id;
        $this->providerId = (string) $announcement->provider_id;
        $this->receivedAt = $announcement->received_at->toDateString();
        $this->dispatchNumber = $announcement->dispatch_number;
        $this->currentOriginIds = ['shippingLineId' => $announcement->shipping_line_id, 'providerId' => $announcement->provider_id];
        $this->savedDevices = (int) $announcement->lots()->sum('quantity');
    }

    /**
     * Get the validation rules of the header.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'shippingLineId' => ['required', $this->activeOrCurrent('shipping_lines', 'shippingLineId')],
            'providerId' => ['required', $this->activeOrCurrent('providers', 'providerId')],
            'receivedAt' => ['required', 'date_format:Y-m-d'],
            'dispatchNumber' => ['required', 'string', 'max:50'],
        ];
    }

    /**
     * Get the messages that change from the default ones.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'shippingLineId.exists' => 'Elige una línea naviera activa.',
            'providerId.exists' => 'Elige un proveedor activo.',
        ];
    }

    /**
     * Get the names of the fields in the validation messages.
     *
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'shippingLineId' => 'línea naviera',
            'providerId' => 'proveedor',
            'receivedAt' => 'fecha de recepción',
            'dispatchNumber' => 'número de guía',
        ];
    }

    /**
     * Accept an active record, or the one the announcement being corrected already has.
     *
     * @param  'shippingLineId'|'providerId'  $field
     */
    private function activeOrCurrent(string $table, string $field): Exists
    {
        $currentId = $this->currentOriginIds[$field];

        return Rule::exists($table, 'id')->where(fn (Builder $query) => $query->where(
            fn (Builder $active) => $active->whereNull('deleted_at')->when($currentId, fn (Builder $current, int $id) => $current->orWhere('id', $id)),
        ));
    }

    /**
     * Save a new announcement with its lots and their devices, all or nothing (H37, criteria 1 to 8). Return null, with
     * nothing saved, when a field is wrong or a serial number collides.
     */
    public function store(ReceiveSecurityDeviceLots $receiveLots, User $user): ?SecurityDeviceAnnouncement
    {
        $this->dispatchNumber = trim($this->dispatchNumber);
        $this->validate();

        $announcement = new SecurityDeviceAnnouncement([
            'shipping_line_id' => (int) $this->shippingLineId,
            'provider_id' => (int) $this->providerId,
            'received_at' => $this->receivedAt,
            'dispatch_number' => $this->dispatchNumber,
        ]);

        return $this->saveLots($announcement, $receiveLots, $user) ? $announcement : null;
    }

    /**
     * Add new lots to an announcement already saved, all or nothing (H37, criterion 10).
     */
    public function addLots(SecurityDeviceAnnouncement $announcement, ReceiveSecurityDeviceLots $receiveLots, User $user): bool
    {
        $this->savedDevices = (int) $announcement->lots()->sum('quantity');

        return $this->saveLots($announcement, $receiveLots, $user);
    }

    /**
     * Save the corrected header of the announcement (H37, criterion 12). Its lots are corrected apart.
     */
    public function update(SecurityDeviceAnnouncement $announcement): void
    {
        $this->dispatchNumber = trim($this->dispatchNumber);
        $this->validate();

        $announcement->update([
            'shipping_line_id' => (int) $this->shippingLineId,
            'provider_id' => (int) $this->providerId,
            'received_at' => $this->receivedAt,
            'dispatch_number' => $this->dispatchNumber,
        ]);
    }

    /**
     * Check the listed lots and save them in the announcement.
     */
    private function saveLots(SecurityDeviceAnnouncement $announcement, ReceiveSecurityDeviceLots $receiveLots, User $user): bool
    {
        $this->collidingLots = [];
        $this->resetErrorBag('lots');
        $lots = $this->validatedLots();

        if ($lots === null) {
            return false;
        }

        if ($receiveLots->handle($announcement, $lots, $user)) {
            return true;
        }

        foreach ($receiveLots->takenSerialNumbersByLot($lots) as $index => $taken) {
            if ($taken !== []) {
                $this->collidingLots[] = $index;
                $this->addError('lots', sprintf(
                    'Lote %d · %s: %s',
                    $index + 1,
                    $lots[$index]->securityDeviceType->name,
                    SecurityDeviceLotForm::takenSerialNumbersMessage($taken, $lots[$index]->securityDeviceType),
                ));
            }
        }

        return false;
    }

    /**
     * Check every listed lot with the rules of H07 and build it, not saved yet. Return null, with the errors in the form,
     * when there are no lots, one is wrong, two of them bring the same serial number or they pass the maximum of the
     * announcement.
     *
     * @return list<SecurityDeviceLot>|null
     */
    private function validatedLots(): ?array
    {
        if ($this->lots === []) {
            $this->addError('lots', 'Agrega al menos un lote para guardar el anuncio.');

            return null;
        }

        $lots = [];

        foreach (array_values($this->lots) as $index => $data) {
            $lot = $this->validatedLot($index, $data);

            if ($lot === null) {
                return null;
            }

            $lots[] = $lot;
        }

        return $this->lotsFitTheAnnouncement($lots) ? $lots : null;
    }

    /**
     * Check a listed lot and build it, or add its first error and return null.
     *
     * @param  array<string, mixed>  $data
     */
    private function validatedLot(int $index, array $data): ?SecurityDeviceLot
    {
        $typeId = (string) ($data['securityDeviceTypeId'] ?? '');
        $type = ctype_digit($typeId) ? SecurityDeviceType::find((int) $typeId) : null;
        $usesSeries = $type->uses_series ?? true;
        $fields = [
            'securityDeviceTypeId' => $typeId,
            'seriesPrefix' => $usesSeries ? mb_strtoupper(trim((string) ($data['seriesPrefix'] ?? ''))) : '',
            'minDigits' => (string) ($data['minDigits'] ?? ''),
            'seriesStart' => (string) ($data['seriesStart'] ?? ''),
            'seriesEnd' => (string) ($data['seriesEnd'] ?? ''),
            'seriesSuffix' => $usesSeries ? mb_strtoupper(trim((string) ($data['seriesSuffix'] ?? ''))) : '',
            'notes' => trim((string) ($data['notes'] ?? '')),
        ];

        $validator = Validator::make(
            $fields,
            [
                'securityDeviceTypeId' => ['required', SecurityDeviceLotForm::activeOrCurrentType(null)],
                ...SecurityDeviceLotForm::seriesRules($type),
                'notes' => ['nullable', 'string', 'max:1000'],
            ],
            ['securityDeviceTypeId.exists' => 'Elige un tipo de dispositivo activo.', ...SecurityDeviceLotForm::seriesMessages()],
            ['securityDeviceTypeId' => 'tipo de dispositivo', ...SecurityDeviceLotForm::seriesAttributes(), 'notes' => 'observaciones'],
        );

        $error = $validator->fails()
            ? $validator->errors()->first()
            : SecurityDeviceLotForm::rangeError((int) $fields['seriesStart'], (int) $fields['seriesEnd']);

        if ($error !== null || $type === null) {
            $this->collidingLots[] = $index;
            $this->addError('lots', sprintf('Lote %d: %s', $index + 1, $error));

            return null;
        }

        $lot = new SecurityDeviceLot([
            'security_device_type_id' => $type->id,
            'series_prefix' => $fields['seriesPrefix'],
            'min_digits' => (int) $fields['minDigits'],
            'series_start' => (int) $fields['seriesStart'],
            'series_end' => (int) $fields['seriesEnd'],
            'series_suffix' => $fields['seriesSuffix'] ?: null,
            'notes' => $fields['notes'] ?: null,
        ]);
        $lot->setRelation('securityDeviceType', $type);

        return $lot;
    }

    /**
     * Check that the lots do not pass the maximum of the announcement, counting the ones already saved, and that no two
     * of them bring the same serial number (H37, criteria 5 and 6). Two types that do not use a series may share one.
     *
     * @param  list<SecurityDeviceLot>  $lots
     */
    private function lotsFitTheAnnouncement(array $lots): bool
    {
        $total = $this->savedDevices + array_sum(array_map(fn (SecurityDeviceLot $lot): int => $lot->series_end - $lot->series_start + 1, $lots));

        if ($total > SecurityDeviceAnnouncement::MAX_DEVICES) {
            $this->addError('lots', sprintf(
                'Un anuncio trae como máximo %s series y estos lotes suman %s.',
                SecurityDeviceLotForm::formatNumber(SecurityDeviceAnnouncement::MAX_DEVICES),
                SecurityDeviceLotForm::formatNumber($total),
            ));

            return false;
        }

        /** @var array<string, list<int>> $owners */
        $owners = [];

        foreach ($lots as $index => $lot) {
            foreach ($lot->serialNumbers() as $serialNumber) {
                $key = mb_strtoupper($serialNumber);

                foreach ($owners[$key] ?? [] as $otherIndex) {
                    $other = $lots[$otherIndex];

                    if ($other->securityDeviceType->uses_series || $lot->securityDeviceType->uses_series || $other->security_device_type_id === $lot->security_device_type_id) {
                        $this->collidingLots = [$otherIndex, $index];
                        $this->addError('lots', sprintf('El lote %d choca con el lote %d del anuncio: los dos traen la serie %s.', $index + 1, $otherIndex + 1, $serialNumber));

                        return false;
                    }
                }

                $owners[$key][] = $index;
            }
        }

        return true;
    }
}
