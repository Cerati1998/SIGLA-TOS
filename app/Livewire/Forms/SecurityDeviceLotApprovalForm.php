<?php

namespace App\Livewire\Forms;

use App\Actions\SecurityDevices\ApproveSecurityDeviceLotRange;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceLotApproval;
use App\Models\User;
use Livewire\Attributes\Locked;
use Livewire\Form;

class SecurityDeviceLotApprovalForm extends Form
{
    public bool $openModal = false;

    /**
     * The lot whose stretch is approved.
     */
    #[Locked]
    public int $lotId;

    /**
     * The first correlative of the lot.
     */
    #[Locked]
    public int $seriesStart;

    /**
     * The last correlative of the lot.
     */
    #[Locked]
    public int $seriesEnd;

    public string $correlativeStart = '';

    public string $correlativeEnd = '';

    /**
     * Fix the lot whose stretches are approved and the range its correlatives can take.
     */
    public function setLot(SecurityDeviceLot $lot): void
    {
        $this->lotId = $lot->id;
        $this->seriesStart = $lot->series_start;
        $this->seriesEnd = $lot->series_end;
    }

    /**
     * Check that both correlatives were written, so the stretch can be checked and previewed.
     */
    public function isComplete(): bool
    {
        return trim($this->correlativeStart) !== '' && trim($this->correlativeEnd) !== '';
    }

    /**
     * Get the validation rules of the form: two whole correlatives inside the lot, the first one not greater than the
     * last one.
     *
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        $insideTheLot = "between:{$this->seriesStart},{$this->seriesEnd}";

        return [
            'correlativeStart' => ['required', 'integer', $insideTheLot, 'lte:correlativeEnd'],
            'correlativeEnd' => ['required', 'integer', $insideTheLot],
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        $insideTheLot = "El tramo debe estar dentro del lote: del {$this->seriesStart} al {$this->seriesEnd}.";

        return [
            'correlativeStart.required' => 'Escribe el correlativo inicial.',
            'correlativeEnd.required' => 'Escribe el correlativo final.',
            'correlativeStart.integer' => 'Escribe solo números.',
            'correlativeEnd.integer' => 'Escribe solo números.',
            'correlativeStart.between' => $insideTheLot,
            'correlativeEnd.between' => $insideTheLot,
            'correlativeStart.lte' => 'El correlativo inicial no puede ser mayor que el final.',
        ];
    }

    /**
     * Register the approval of the stretch. Return null, with nothing saved, when the stretch has no inactive device.
     */
    public function store(ApproveSecurityDeviceLotRange $approveRange, User $user): ?SecurityDeviceLotApproval
    {
        $this->correlativeStart = trim($this->correlativeStart);
        $this->correlativeEnd = trim($this->correlativeEnd);

        $this->validate();

        return $approveRange->handle($this->lot(), (int) $this->correlativeStart, (int) $this->correlativeEnd, $user);
    }

    /**
     * Get the lot whose stretch is approved.
     */
    public function lot(): SecurityDeviceLot
    {
        return SecurityDeviceLot::query()->findOrFail($this->lotId);
    }
}
