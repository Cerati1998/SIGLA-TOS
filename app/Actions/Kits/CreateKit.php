<?php

namespace App\Actions\Kits;

use App\Enums\KitStatus;
use App\Models\Kit;
use App\Models\KitType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateKit
{
    /**
     * Create an empty kit, in Armando, with a copy of what its kit type carries, all or nothing (H14).
     *
     * @param  array{pouchCode: string, kitTypeId: int, shippingLineId: int}  $data
     */
    public function handle(array $data, User $user): Kit
    {
        return DB::transaction(function () use ($data, $user): Kit {
            $kitType = KitType::query()->with('securityDeviceTypes')->findOrFail($data['kitTypeId']);

            $kit = Kit::query()->create([
                'pouch_code' => $data['pouchCode'],
                'kit_type_id' => $kitType->id,
                'shipping_line_id' => $data['shippingLineId'],
                'status' => KitStatus::Building,
                'created_by' => $user->id,
            ]);

            $kit->securityDeviceTypes()->attach($kitType->securityDeviceTypes->mapWithKeys(function ($securityDeviceType): array {
                /** @var object{quantity: int, internal_quantity: int|null, external_quantity: int|null} $pivot */
                $pivot = $securityDeviceType->getRelationValue('pivot');

                return [$securityDeviceType->id => [
                    'quantity' => $pivot->quantity,
                    'internal_quantity' => $pivot->internal_quantity,
                    'external_quantity' => $pivot->external_quantity,
                ]];
            })->all());

            return $kit;
        });
    }
}
