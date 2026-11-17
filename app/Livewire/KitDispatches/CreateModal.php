<?php

namespace App\Livewire\KitDispatches;

use App\Actions\KitDispatches\DispatchKits;
use App\Actions\KitDispatches\FindKitToDispatch;
use App\Enums\KitStatus;
use App\Livewire\Forms\KitDispatchForm;
use App\Models\Kit;
use App\Models\KitDispatch;
use App\Models\KitType;
use App\Models\ShippingLine;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * The window «Despacho masivo de kits» (H15): the worker who receives, the kits marked by scanning their pouch or with
 * their checkbox in the table of kits Armados, and the dispatch in a single step.
 */
class CreateModal extends Component
{
    /**
     * How many kits the table of kits Armados shows per page.
     */
    public const int PER_PAGE = 10;

    public KitDispatchForm $form;

    public string $kitTypeFilter = '';

    public string $shippingLineFilter = '';

    public string $pouchSearch = '';

    public bool $onlyMarked = false;

    public int $page = 1;

    /**
     * Mark the kit with the scanned or written pouch code. The answer feeds the list of the last scans and, for a kit
     * just marked, the summary of the marked kits by kit type.
     *
     * @return array{ok: bool, message: string, kitId?: int, kitType?: string}
     */
    public function mark(FindKitToDispatch $findKitToDispatch, string $pouchCode): array
    {
        $pouchCode = mb_strtoupper(trim($pouchCode));

        if ($pouchCode === '' || mb_strlen($pouchCode) > 50) {
            return ['ok' => false, 'message' => 'Escanea o escribe una funda de hasta 50 caracteres.'];
        }

        $kit = $findKitToDispatch->handle($pouchCode);

        if (is_string($kit)) {
            return ['ok' => false, 'message' => $kit];
        }

        if (! $this->form->mark($kit->id)) {
            return ['ok' => true, 'message' => 'Ya estaba marcado en este despacho.'];
        }

        $this->form->resetValidation('kitIds');

        return ['ok' => true, 'message' => $kit->kitType->name.' · marcado', 'kitId' => $kit->id, 'kitType' => $kit->kitType->name];
    }

    /**
     * Check the window and, when it is right, ask the user to confirm the dispatch.
     */
    public function confirmDispatch(): void
    {
        $this->form->validate();

        $count = count($this->form->markedKitIds());
        $receiverName = (string) Worker::query()->whereKey($this->form->receiverId)->value('full_name');

        $this->dispatch(
            'confirm-kit-dispatch',
            title: sprintf('¿Despachar %d %s a %s?', $count, $count === 1 ? 'kit' : 'kits', $receiverName),
            text: 'Después no podrás cambiar su contenido.',
        );
    }

    /**
     * Dispatch the marked kits, close the window and refresh the list of dispatches.
     */
    public function save(DispatchKits $dispatchKits): void
    {
        /** @var User $user */
        $user = Auth::user();
        $dispatch = $this->form->store($dispatchKits, $user)->load('receiver');
        $count = $dispatch->kits()->count();

        $this->discard();

        $this->dispatch(
            'toast',
            icon: 'success',
            title: sprintf('Despacho %s registrado', $dispatch->number()),
            text: sprintf('%d %s a %s.', $count, $count === 1 ? 'kit despachado' : 'kits despachados', $dispatch->receiver->full_name),
        );
        $this->dispatch('refreshDatatable');
    }

    /**
     * Forget what was chosen in a window that was closed without dispatching: nothing stays saved.
     */
    public function discard(): void
    {
        $this->form->reset();
        $this->form->resetValidation();
        $this->reset('kitTypeFilter', 'shippingLineFilter', 'pouchSearch', 'onlyMarked', 'page');
    }

    /**
     * Go back to the first page when a filter changes.
     */
    public function updated(string $property): void
    {
        if (in_array($property, ['kitTypeFilter', 'shippingLineFilter', 'pouchSearch', 'onlyMarked'], true)) {
            $this->page = 1;
        }
    }

    /**
     * Show the previous page of kits Armados.
     */
    public function previousPage(): void
    {
        $this->page = max(1, $this->page - 1);
    }

    /**
     * Show the next page of kits Armados.
     */
    public function nextPage(): void
    {
        $this->page++;
    }

    /**
     * Get the kits that can be dispatched, with the filters of the window.
     *
     * @return Builder<Kit>
     */
    private function availableKits(): Builder
    {
        $pouchSearch = mb_strtoupper(trim($this->pouchSearch));

        return Kit::query()
            ->where('status', KitStatus::Built)
            ->whereDoesntHave('dispatches')
            ->when($this->kitTypeFilter !== '', fn (Builder $query) => $query->where('kit_type_id', (int) $this->kitTypeFilter))
            ->when($this->shippingLineFilter !== '', fn (Builder $query) => $query->where('shipping_line_id', (int) $this->shippingLineFilter))
            ->when($pouchSearch !== '', fn (Builder $query) => $query->where('pouch_code', 'like', '%'.$pouchSearch.'%'))
            ->when($this->onlyMarked, fn (Builder $query) => $query->whereKey($this->form->markedKitIds()));
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        $total = $this->availableKits()->count();
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $this->page = min($this->page, $lastPage);

        $markedKits = Kit::query()->with('kitType')->whereKey($this->form->markedKitIds())->get();
        /** @var User $user */
        $user = Auth::user();

        return view('pages.kit-dispatches.create-modal', [
            'kits' => $this->availableKits()
                ->with(['kitType', 'shippingLine'])
                ->withCount('devices')
                ->orderByDesc('kits.id')
                ->forPage($this->page, self::PER_PAGE)
                ->get(),
            'total' => $total,
            'lastPage' => $lastPage,
            'availableCount' => Kit::query()->where('status', KitStatus::Built)->whereDoesntHave('dispatches')->count(),
            'markedCount' => $markedKits->count(),
            'markedSummary' => KitDispatch::summarize($markedKits),
            'kitTypes' => KitType::query()->orderBy('name')->pluck('name', 'id'),
            'shippingLines' => ShippingLine::query()->orderBy('code')->pluck('code', 'id'),
            'delivererName' => $user->name,
        ]);
    }
}
