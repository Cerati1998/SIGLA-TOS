<?php

namespace App\Livewire\Forms;

use App\Enums\GpsDeviceCategory;
use App\Enums\GpsOpeningType;
use App\Models\GpsDeviceModel;
use App\Models\Provider;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

class GpsDeviceModelForm extends Form
{
    public bool $openModal = false;

    #[Locked]
    public ?int $gpsDeviceModelId = null;

    /**
     * The brand the model already had, kept as a choice even if it was deactivated afterwards.
     */
    #[Locked]
    public ?int $savedBrandId = null;

    public ?int $brandId = null;

    public string $name = '';

    public string $category = '';

    public string $openingType = '';

    public string $monitoringProductCode = '';

    public string $monitoringDeviceTypeCode = '';

    public string $description = '';

    /**
     * Fill the form with the model being edited.
     */
    public function setGpsDeviceModel(GpsDeviceModel $gpsDeviceModel): void
    {
        $this->gpsDeviceModelId = $gpsDeviceModel->id;
        $this->savedBrandId = $gpsDeviceModel->brand_id;
        $this->brandId = $gpsDeviceModel->brand_id;
        $this->name = $gpsDeviceModel->name;
        $this->category = $gpsDeviceModel->category->value;
        $this->openingType = $gpsDeviceModel->opening_type->value;
        $this->monitoringProductCode = (string) $gpsDeviceModel->monitoring_product_code;
        $this->monitoringDeviceTypeCode = (string) $gpsDeviceModel->monitoring_device_type_code;
        $this->description = (string) $gpsDeviceModel->description;
    }

    /**
     * Get the validation rules of the form. The two monitoring codes go together or not at all, and their pair is not
     * repeated in another model, even a deactivated one.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'brandId' => [
                'required',
                'integer',
                Rule::exists(Provider::class, 'id')->where(fn (Builder $query) => $query
                    ->whereNull('deleted_at')
                    ->orWhere('id', $this->savedBrandId)),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique(GpsDeviceModel::class, 'name')->where('brand_id', $this->brandId)->ignore($this->gpsDeviceModelId),
            ],
            'category' => ['required', Rule::enum(GpsDeviceCategory::class)],
            'openingType' => ['required', Rule::enum(GpsOpeningType::class)],
            'monitoringProductCode' => ['nullable', 'string', 'max:20', 'required_with:monitoringDeviceTypeCode'],
            'monitoringDeviceTypeCode' => [
                'nullable',
                'string',
                'max:20',
                'required_with:monitoringProductCode',
                Rule::unique(GpsDeviceModel::class, 'monitoring_device_type_code')
                    ->where('monitoring_product_code', $this->monitoringProductCode)
                    ->ignore($this->gpsDeviceModelId),
            ],
            'description' => ['nullable', 'string', 'max:255'],
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
            'brandId.required' => 'Elige la marca del modelo.',
            'brandId.exists' => 'Elige una marca activa.',
            'name.required' => 'Escribe el nombre del modelo.',
            'name.unique' => 'Ya existe un modelo con este nombre en la marca, aunque esté desactivado.',
            'category.required' => 'Elige la categoría.',
            'openingType.required' => 'Elige la apertura.',
            'monitoringProductCode.required_with' => 'Escribe también el código de producto, o deja los dos vacíos.',
            'monitoringDeviceTypeCode.required_with' => 'Escribe también el código de tipo, o deja los dos vacíos.',
            'monitoringDeviceTypeCode.unique' => $this->repeatedCodesMessage(),
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
            'brandId' => 'marca',
            'name' => 'nombre',
            'category' => 'categoría',
            'openingType' => 'apertura',
            'monitoringProductCode' => 'código de producto',
            'monitoringDeviceTypeCode' => 'código de tipo',
            'description' => 'descripción',
        ];
    }

    /**
     * Create the model.
     */
    public function store(): void
    {
        $this->validateNormalized();

        GpsDeviceModel::query()->create($this->gpsDeviceModelAttributes());
    }

    /**
     * Save the changes to the given model.
     */
    public function update(GpsDeviceModel $gpsDeviceModel): void
    {
        $this->validateNormalized();

        $gpsDeviceModel->update($this->gpsDeviceModelAttributes());
    }

    /**
     * Get the message for codes already used, naming the model that uses them.
     */
    private function repeatedCodesMessage(): string
    {
        $name = GpsDeviceModel::withTrashed()
            ->where('monitoring_product_code', $this->monitoringProductCode)
            ->where('monitoring_device_type_code', $this->monitoringDeviceTypeCode)
            ->value('name');

        return $name === null
            ? 'Otro modelo ya usa estos códigos, aunque esté desactivado.'
            : "El modelo {$name} ya usa estos códigos, aunque esté desactivado.";
    }

    /**
     * Validate the data as it will be stored: without spaces around it, and the product code in capitals.
     */
    private function validateNormalized(): void
    {
        $this->name = trim($this->name);
        $this->monitoringProductCode = mb_strtoupper(trim($this->monitoringProductCode));
        $this->monitoringDeviceTypeCode = mb_strtoupper(trim($this->monitoringDeviceTypeCode));
        $this->description = trim($this->description);

        $this->validate();
    }

    /**
     * Get the columns of the model that this form writes.
     *
     * @return array{brand_id: int|null, name: string, category: string, opening_type: string, monitoring_product_code: string|null, monitoring_device_type_code: string|null, description: string|null}
     */
    private function gpsDeviceModelAttributes(): array
    {
        return [
            'brand_id' => $this->brandId,
            'name' => $this->name,
            'category' => $this->category,
            'opening_type' => $this->openingType,
            'monitoring_product_code' => $this->monitoringProductCode === '' ? null : $this->monitoringProductCode,
            'monitoring_device_type_code' => $this->monitoringDeviceTypeCode === '' ? null : $this->monitoringDeviceTypeCode,
            'description' => $this->description === '' ? null : $this->description,
        ];
    }
}
