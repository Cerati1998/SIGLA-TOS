<?php

namespace App\Livewire\Forms;

use App\Actions\GpsDevices\SaveGpsDevice;
use App\Enums\GpsDeviceOwnership;
use App\Models\Branch;
use App\Models\GpsDevice;
use App\Models\GpsDeviceModel;
use App\Models\Provider;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class GpsDeviceForm extends Form
{
    public bool $openModal = false;

    #[Locked]
    public ?int $gpsDeviceId = null;

    /**
     * The model, rental provider and branch the device already had, kept as choices even if they were deactivated.
     */
    #[Locked]
    public ?int $savedModelId = null;

    #[Locked]
    public ?int $savedRentalProviderId = null;

    #[Locked]
    public ?int $savedBranchId = null;

    /**
     * The addresses of the photo the device already has, to show it when editing.
     */
    #[Locked]
    public ?string $savedPhotoUrl = null;

    #[Locked]
    public ?string $savedThumbnailUrl = null;

    public string $code = '';

    public string $monitoringAssetId = '';

    public ?int $gpsDeviceModelId = null;

    public string $ownership = 'own';

    public ?int $rentalProviderId = null;

    public string $branchId = '';

    public string $notes = '';

    /**
     * The reference photo, already compressed in the browser, and its thumbnail (H40, criterion 1b).
     */
    public ?TemporaryUploadedFile $photo = null;

    public ?TemporaryUploadedFile $thumbnail = null;

    /**
     * Whether the photo the device had was removed in the window.
     */
    public bool $removePhoto = false;

    /**
     * Fill the form with the device being edited.
     */
    public function setGpsDevice(GpsDevice $gpsDevice): void
    {
        $this->gpsDeviceId = $gpsDevice->id;
        $this->savedModelId = $gpsDevice->gps_device_model_id;
        $this->savedRentalProviderId = $gpsDevice->rental_provider_id;
        $this->savedBranchId = $gpsDevice->branch_id;
        $this->code = $gpsDevice->code;
        $this->monitoringAssetId = (string) $gpsDevice->monitoring_asset_id;
        $this->gpsDeviceModelId = $gpsDevice->gps_device_model_id;
        $this->ownership = $gpsDevice->ownership->value;
        $this->rentalProviderId = $gpsDevice->rental_provider_id;
        $this->branchId = (string) $gpsDevice->branch_id;
        $this->notes = (string) $gpsDevice->notes;

        if ($gpsDevice->photo_path !== null && $gpsDevice->thumbnail_path !== null) {
            $version = basename($gpsDevice->photo_path, '.jpg');
            $this->savedPhotoUrl = route('intranet.gps-devices.photo', ['gpsDevice' => $gpsDevice->id, 'v' => $version]);
            $this->savedThumbnailUrl = route('intranet.gps-devices.photo.thumbnail', ['gpsDevice' => $gpsDevice->id, 'v' => $version]);
        }
    }

    /**
     * Get the validation rules of the form. The model, the rental provider and the branch must be active, unless the
     * device already had them. A rented device needs its provider; an own one is saved without it.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', Rule::unique(GpsDevice::class, 'code')->ignore($this->gpsDeviceId)],
            'monitoringAssetId' => [
                'nullable',
                'digits_between:1,12',
                Rule::unique(GpsDevice::class, 'monitoring_asset_id')->ignore($this->gpsDeviceId),
            ],
            'gpsDeviceModelId' => ['required', 'integer', Rule::exists(GpsDeviceModel::class, 'id')->where(fn (Builder $query) => $query
                ->whereNull('deleted_at')
                ->orWhere('id', $this->savedModelId))],
            'ownership' => ['required', Rule::enum(GpsDeviceOwnership::class)],
            'rentalProviderId' => [
                'exclude_unless:ownership,'.GpsDeviceOwnership::Rented->value,
                'required',
                'integer',
                Rule::exists(Provider::class, 'id')->where(fn (Builder $query) => $query
                    ->whereNull('deleted_at')
                    ->orWhere('id', $this->savedRentalProviderId)),
            ],
            'branchId' => ['required', 'integer', Rule::exists(Branch::class, 'id')->where(fn (Builder $query) => $query
                ->whereNull('deleted_at')
                ->orWhere('id', $this->savedBranchId))],
            'notes' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg', 'max:3072'],
            'thumbnail' => ['nullable', 'required_with:photo', 'image', 'mimes:jpg,jpeg', 'max:1024'],
        ];
    }

    /**
     * Get the messages of the rules that need their own wording.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'code.required' => 'Escribe el código del dispositivo.',
            'code.unique' => 'Ya existe un dispositivo con este código, aunque esté desactivado.',
            'monitoringAssetId.digits_between' => 'El ID de 24HM solo lleva números.',
            'monitoringAssetId.unique' => $this->repeatedAssetIdMessage(),
            'gpsDeviceModelId.required' => 'Elige el modelo del dispositivo.',
            'gpsDeviceModelId.integer' => 'Elige el modelo del dispositivo.',
            'gpsDeviceModelId.exists' => 'Elige un modelo activo.',
            'rentalProviderId.required' => 'Elige el proveedor del alquiler.',
            'rentalProviderId.integer' => 'Elige el proveedor del alquiler.',
            'rentalProviderId.exists' => 'Elige un proveedor activo.',
            'branchId.required' => 'Elige la sucursal del dispositivo.',
            'branchId.integer' => 'Elige la sucursal del dispositivo.',
            'branchId.exists' => 'Elige una sucursal activa.',
            'photo.*' => 'La foto no es válida. Tómala o elígela otra vez.',
            'thumbnail.*' => 'La foto no es válida. Tómala o elígela otra vez.',
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
            'code' => 'código',
            'monitoringAssetId' => 'ID de 24HM',
            'gpsDeviceModelId' => 'modelo',
            'ownership' => 'propiedad',
            'rentalProviderId' => 'proveedor del alquiler',
            'branchId' => 'sucursal',
            'notes' => 'observaciones',
            'photo' => 'foto',
        ];
    }

    /**
     * Get the branches to choose from: the active ones and, when editing, the one the device already had.
     *
     * @return Collection<int, string>
     */
    public function branches(): Collection
    {
        return Branch::withTrashed()
            ->where(fn (Builder $query) => $query->whereNull('deleted_at')->orWhere('id', $this->savedBranchId))
            ->orderBy('name')
            ->get(['id', 'name', 'deleted_at'])
            ->mapWithKeys(fn (Branch $branch): array => [$branch->id => $branch->trashed() ? "{$branch->name} (desactivada)" : $branch->name]);
    }

    /**
     * Create the device, or save the changes to the one being edited, with its photo.
     */
    public function save(SaveGpsDevice $saveGpsDevice): GpsDevice
    {
        $this->code = mb_strtoupper(trim($this->code));
        $this->monitoringAssetId = trim($this->monitoringAssetId);
        $this->notes = trim($this->notes);

        $this->validate();

        $gpsDevice = $this->gpsDeviceId === null ? null : GpsDevice::withTrashed()->findOrFail($this->gpsDeviceId);
        $isRented = $this->ownership === GpsDeviceOwnership::Rented->value;

        return $saveGpsDevice->handle($gpsDevice, [
            'code' => $this->code,
            'monitoring_asset_id' => $this->monitoringAssetId === '' ? null : (int) $this->monitoringAssetId,
            'gps_device_model_id' => $this->gpsDeviceModelId,
            'ownership' => $this->ownership,
            'rental_provider_id' => $isRented ? $this->rentalProviderId : null,
            'branch_id' => (int) $this->branchId,
            'notes' => $this->notes === '' ? null : $this->notes,
        ], $this->photo, $this->thumbnail, $this->removePhoto);
    }

    /**
     * Get the message for an id of the monitoring platform already used, naming the device that has it.
     */
    private function repeatedAssetIdMessage(): string
    {
        $code = ctype_digit($this->monitoringAssetId)
            ? GpsDevice::withTrashed()->where('monitoring_asset_id', (int) $this->monitoringAssetId)->value('code')
            : null;

        return $code === null
            ? 'Otro dispositivo ya tiene este ID de 24HM, aunque esté desactivado.'
            : "El dispositivo {$code} ya tiene este ID de 24HM, aunque esté desactivado.";
    }
}
