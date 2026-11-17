<?php

namespace App\Actions\KitDispatches;

use App\Enums\KitStatus;
use App\Models\Kit;
use App\Models\KitDispatch;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DispatchKits
{
    public function __construct(private FindKitToDispatch $findKitToDispatch) {}

    /**
     * Dispatch the kits to the worker, all or none (H15). Each kit is checked again under a lock, in case another user
     * dispatched it or changed it after it was marked. The kits become Despachado and their devices stay En un kit.
     *
     * @param  list<int>  $kitIds
     *
     * @throws ValidationException With the key "kitIds", when a kit can no longer be dispatched.
     */
    public function handle(Worker $receiver, array $kitIds, User $user): KitDispatch
    {
        return DB::transaction(function () use ($receiver, $kitIds, $user): KitDispatch {
            $kits = Kit::query()
                ->with(['kitType', 'dispatches'])
                ->whereKey($kitIds)
                ->lockForUpdate()
                ->orderBy('pouch_code')
                ->get();

            $rejections = $kits
                ->map(fn (Kit $kit): ?string => ($reason = $this->findKitToDispatch->rejectionReason($kit)) === null ? null : "{$kit->pouch_code}: {$reason}")
                ->filter()
                ->values();

            if ($kits->count() !== count(array_unique($kitIds))) {
                $rejections->push('Uno de los kits marcados ya no existe.');
            }

            if ($rejections->isNotEmpty()) {
                throw ValidationException::withMessages(['kitIds' => $rejections->all()]);
            }

            $dispatch = KitDispatch::query()->create(['receiver_id' => $receiver->id, 'created_by' => $user->id]);
            $dispatch->kits()->attach($kits->modelKeys());
            Kit::query()->whereKey($kits->modelKeys())->update(['status' => KitStatus::Dispatched]);

            return $dispatch;
        });
    }
}
