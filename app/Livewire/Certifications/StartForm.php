<?php

namespace App\Livewire\Certifications;

use App\Actions\Certifications\FindGateOutRequestToCertify;
use App\Actions\Certifications\FindKitToCertify;
use App\Actions\Certifications\StartCertification;
use App\Actions\Kits\BuildKitContents;
use App\Enums\KitAssignment;
use App\Livewire\Forms\CertificationForm;
use App\Models\Certification;
use App\Models\Kit;
use App\Models\User;
use App\Models\YardZone;
use App\Services\Sigla1LookupResult;
use App\Services\Sigla1LookupStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

/**
 * Start a certification in three steps: the gate out request, the kit and the yard zone (H17). On the phone each
 * step has its own screen; on the desktop the three are seen together.
 *
 * @phpstan-import-type GateOutRequest from Sigla1LookupResult
 *
 * @property-read Kit|null $kit
 * @property-read array{slots: list<array{key: string, name: string, assignment: KitAssignment|null, quantity: int}>, devices: list<array{serialNumber: string, type: string, assignment: string}>}|null $kitContents
 * @property-read Collection<int, YardZone> $yardZones
 */
class StartForm extends Component
{
    public CertificationForm $form;

    /**
     * How the last lookup in SIGLA 1.0 ended, or null before the first one.
     */
    #[Locked]
    public ?Sigla1LookupStatus $status = null;

    /**
     * The request found, the one dispatched last when SIGLA 1.0 sends more than one.
     *
     * @var GateOutRequest|null
     */
    #[Locked]
    public ?array $gateOutRequest = null;

    /**
     * The certification that the request already has, to open it instead of starting another (criterion 6).
     */
    #[Locked]
    public ?int $existingCertificationId = null;

    /**
     * The kit found with the pouch, once it can be used.
     */
    #[Locked]
    public ?int $kitId = null;

    /**
     * Whether saving failed for a reason that is not a validation, so nothing was saved (criterion 4).
     */
    #[Locked]
    public bool $startFailed = false;

    /**
     * Get the kit found, with what the screen shows of it.
     */
    #[Computed]
    public function kit(): ?Kit
    {
        return $this->kitId === null
            ? null
            : Kit::query()->with(['kitType', 'shippingLine', 'dispatches.receiver'])->find($this->kitId);
    }

    /**
     * Get what the kit carries by type and its devices, shown only as information (criterion 7).
     *
     * @return array{slots: list<array{key: string, name: string, assignment: KitAssignment|null, quantity: int}>, devices: list<array{serialNumber: string, type: string, assignment: string}>}|null
     */
    #[Computed]
    public function kitContents(): ?array
    {
        if ($this->kit === null) {
            return null;
        }

        $contents = app(BuildKitContents::class)->handle($this->kit);

        return ['slots' => $contents['slots'], 'devices' => $contents['devices']];
    }

    /**
     * Get the active yard zones of the branch of the user (criterion 13).
     *
     * @return Collection<int, YardZone>
     */
    #[Computed]
    public function yardZones(): Collection
    {
        /** @var User $user */
        $user = Auth::user();

        return YardZone::query()->where('branch_id', $user->branch_id)->orderBy('name')->get(['id', 'name']);
    }

    /**
     * Look up the request in SIGLA 1.0 with the container and the booking. A new lookup forgets the kit chosen
     * before, because it was checked against the shipping line of the previous request.
     */
    public function search(FindGateOutRequestToCertify $findGateOutRequest): void
    {
        $this->reset('status', 'gateOutRequest', 'existingCertificationId', 'kitId', 'startFailed');

        $this->form->validateRequest();

        $result = $findGateOutRequest->handle($this->form->booking, $this->form->container());

        $this->status = $result['status'];
        $this->gateOutRequest = $result['gateOutRequest'];
        $this->existingCertificationId = $result['certification']?->id;
    }

    /**
     * Find the kit with the scanned or written pouch, once the request is chosen.
     */
    public function findKit(FindKitToCertify $findKit): void
    {
        $this->reset('kitId', 'startFailed');

        if (! $this->hasRequestToCertify()) {
            return;
        }

        $this->form->validatePouch();

        $result = $findKit->handle($this->form->pouchCode, $this->gateOutRequest['shipping_line'] ?? null);

        if (is_string($result)) {
            $this->addError('form.pouchCode', $result);

            return;
        }

        $this->kitId = $result->id;
    }

    /**
     * Start the certification and open it. Everything is saved or nothing is (criterion 4).
     */
    public function start(StartCertification $startCertification): void
    {
        $this->startFailed = false;

        if (! $this->hasRequestToCertify() || $this->kit === null) {
            return;
        }

        /** @var User $user */
        $user = Auth::user();

        try {
            $certification = $this->form->store($startCertification, $this->gateOutRequest, $this->kit, $user);
        } catch (ValidationException $exception) {
            $this->dispatch('certification-step', step: $this->stepOf((string) array_key_first($exception->errors())));

            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            $this->startFailed = true;

            return;
        }

        session()->flash('toast', [
            'icon' => 'success',
            'title' => 'Certificación iniciada',
            'text' => sprintf('Se asignó el kit al contenedor %s. Sus dos inspecciones quedan pendientes.', $certification->container),
        ]);

        $this->redirectRoute('intranet.certifications.list', navigate: true);
    }

    /**
     * Whether there is a request found that has no certification yet.
     *
     * @phpstan-assert-if-true GateOutRequest $this->gateOutRequest
     */
    private function hasRequestToCertify(): bool
    {
        return $this->gateOutRequest !== null && $this->existingCertificationId === null;
    }

    /**
     * Get the step of the screen that has a field, to go back to it when saving finds an error there.
     */
    private function stepOf(string $field): int
    {
        return match ($field) {
            'form.booking', 'form.containerOwner', 'form.containerNumber' => 1,
            'form.pouchCode', 'form.inspectionSheetCode' => 2,
            default => 3,
        };
    }

    /**
     * Get the certification the request already has.
     */
    public function existingCertification(): ?Certification
    {
        return $this->existingCertificationId === null
            ? null
            : Certification::query()->with('certifier')->find($this->existingCertificationId);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.certifications.start-form', [
            'existingCertification' => $this->existingCertification(),
        ]);
    }
}
