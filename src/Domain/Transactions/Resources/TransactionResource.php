<?php
namespace VueFileManager\Subscription\Domain\Transactions\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
                'id'            => $this->id,
                'type'          => 'transactions',
                'attributes'    => [
                    'status'     => $this->status,
                    'plan_name'  => $this->plan_name,
                    'price'      => format_currency($this),
                    'currency'   => $this->currency,
                    'amount'     => $this->amount,
                    'driver'     => $this->driver,
                    'reference'  => $this->reference,
                    'created_at' => $this->created_at->formatLocalized('%d. %b. %Y'),
                    'updated_at' => $this->updated_at,
                ],
                'relationships' => [
                    $this->mergeWhen($this->user && $this->user->settings, fn () => [
                        'user' => [
                            'data' => [
                                'id'         => $this->user->id,
                                'type'       => 'users',
                                'attributes' => [
                                    'avatar' => $this->user->settings->avatar,
                                    'name'   => $this->user->settings->name,
                                    'color'  => $this->user->settings->color,
                                    'email'  => $this->user->email,
                                ],
                            ],
                        ],
                    ]),
                ],
            ],
        ];
    }
}
