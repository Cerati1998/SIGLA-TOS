<?php

namespace App\Livewire\Forms;

use App\Actions\BulkDispatches\DispatchBulkDevices;
use App\Actions\Kits\SearchDispatchedKitOptions;
use App\Enums\BulkDispatchReason;
use App\Models\BulkDispatch;
use App\Models\Client;
use App\Models\User;
use App\Models\Worker;
use App\Models\YardZone;
use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

/**
 * The window «Despacho a granel» (H30): who receives, why the devices leave, where they go and which devices.
 */
class BulkDispatchForm extends Form
{
    /**
     * The longest remark.
     */
    public const int NOTES_MAX = 500;

    public bool $openModal = false;

    public ?int $receiverId = null;

    public string $reason = '';

    public ?int $clientId = null;

    public ?int $kitId = null;

    public ?int $yardZoneId = null;

    public string $notes = '';

    /**
     * The ids of the devices to dispatch, chosen by scanning, by range or by quantity.
     *
     * @var list<int>
     */
    public array $deviceIds = [];

    /**
     * Add devices to the dispatch and return how many were not in it yet.
     *
     * @param  list<int>  $deviceIds
     */
    public function addDevices(array $deviceIds): int
    {
        $current = $this->chosenDeviceIds();
        $new = array_values(array_diff($deviceIds, $current));

        $this->deviceIds = [...$current, ...$new];

        return count($new);
    }

    /**
     * Take devices out of the dispatch.
     *
     * @param  list<int>  $deviceIds
     */
    public function removeDevices(array $deviceIds): void
    {
        $this->deviceIds = array_values(array_diff($this->chosenDeviceIds(), $deviceIds));
    }

    /**
     * Get the ids of the chosen devices as integers, once each.
     *
     * @return list<int>
     */
    public function chosenDeviceIds(): array
    {
        return array_values(array_unique(array_map('intval', $this->deviceIds)));
    }

    /**
     * Validate the window and dispatch the devices. A device that can no longer be dispatched stops it, with the reason
     * under the list of devices.
     */
    public function store(DispatchBulkDevices $dispatchBulkDevices, User $user): BulkDispatch
    {
        $this->notes = trim($this->notes);
        $this->validate();

        $reason = BulkDispatchReason::from($this->reason);

        try {
            return $dispatchBulkDevices->handle([
                'receiver_id' => (int) $this->receiverId,
                'reason' => $reason->value,
                'client_id' => $reason === BulkDispatchReason::Sale ? $this->clientId : null,
                'kit_id' => $reason === BulkDispatchReason::Operation ? $this->kitId : null,
                'yard_zone_id' => (int) $this->yardZoneId,
                'notes' => $this->notes === '' ? null : $this->notes,
            ], $this->chosenDeviceIds(), $user);
        } catch (ValidationException $exception) {
            throw ValidationException::withMessages(['form.deviceIds' => $exception->errors()['deviceIds'] ?? []]);
        }
    }

    /**
     * Get the validation rules of the window. The yard zone must be an active zone of the branch of the user.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        /** @var User $user */
        $user = auth()->user();
        $active = fn (Builder $query) => $query->whereNull('deleted_at');

        return [
            'receiverId' => ['required', 'integer', Rule::exists(Worker::class, 'id')->where($active)],
            'reason' => ['required', Rule::enum(BulkDispatchReason::class)],
            'clientId' => [
                Rule::requiredIf($this->reason === BulkDispatchReason::Sale->value),
                'nullable',
                'integer',
                Rule::exists(Client::class, 'id')->where($active),
            ],
            'kitId' => [
                Rule::requiredIf($this->reason === BulkDispatchReason::Operation->value),
                'nullable',
                'integer',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($this->reason === BulkDispatchReason::Operation->value && ! SearchDispatchedKitOptions::query()->whereKey((int) $value)->exists()) {
                        $fail('Elige el contenedor de un kit usado en su certificación.');
                    }
                },
            ],
            'yardZoneId' => [
                'required',
                'integer',
                Rule::exists(YardZone::class, 'id')->where($active)->where('branch_id', $user->branch_id),
            ],
            'notes' => ['nullable', 'string', 'max:'.self::NOTES_MAX],
            'deviceIds' => ['required', 'array', 'min:1'],
            'deviceIds.*' => ['integer'],
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
            'receiverId.required' => 'Elige quién recibe los dispositivos.',
            'receiverId.exists' => 'Elige un trabajador activo.',
            'reason.required' => 'Elige el motivo de salida.',
            'clientId.required' => 'Elige el cliente de la venta.',
            'clientId.exists' => 'Elige un cliente activo.',
            'kitId.required' => 'Elige el kit despachado al que van los dispositivos.',
            'yardZoneId.required' => 'Elige el patio de destino.',
            'yardZoneId.exists' => 'Elige una zona de patio activa de tu sede.',
            'notes.max' => 'La observación puede tener como máximo :max caracteres.',
            'deviceIds.required' => 'Agrega al menos un dispositivo para despachar.',
            'deviceIds.min' => 'Agrega al menos un dispositivo para despachar.',
        ];
    }
}
