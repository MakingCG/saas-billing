<?php

namespace VueFileManager\Subscription\Domain\Plans\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => [
                'id'         => $this->id,
                'type'       => 'plans',
                'attributes' => [
                    'name'        => $this->name,
                    'amount'      => $this->amount,
                    'visible'     => $this->visible,
                    'interval'    => $this->interval,
                    'description' => $this->description,
                    'features'    => $this->features->pluck('value', 'key'),
                ],
                'meta' => [
                    'driver_plan_id' => $this->drivers->pluck('driver_plan_id', 'driver'),
                ],
            ],
        ];
    }
}