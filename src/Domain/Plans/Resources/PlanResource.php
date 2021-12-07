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
                    'type'        => $this->type,
                    'visible'     => $this->visible,
                    'currency'    => $this->currency,
                    'description' => $this->description,
                    'subscribers' => $this->subscriptions->count(),
                ],
                'meta'       => [
                    // Get gateway driver ids
                    'driver_plan_id' => $this->drivers->pluck('driver_plan_id', 'driver'),

                    // Get fixed plan attributes
                    'fixed'          => $this->when($this->type === 'fixed', fn () => [
                        'price'    => format_currency($this->amount, $this->currency),
                        'amount'   => $this->amount,
                        'features' => $this->fixedItems->pluck('value', 'key'),
                        'interval' => $this->interval,
                    ]),

                    // Get metered plan attributes
                    'metered'        => $this->when($this->type === 'metered', fn () => [
                        'prices' => $this->meteredItems->map(fn ($price) => [
                            'key'       => $price['key'],
                            'charge_by' => $price['charge_by'],
                            'tiers'     => $price['tiers'],
                        ]),
                    ]),
                ],
            ],
        ];
    }
}
