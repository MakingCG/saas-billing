<?php
namespace VueFileManager\Subscription\Domain\Subscriptions\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use VueFileManager\Subscription\Domain\Plans\Resources\PlanResource;

class SubscriptionResource extends JsonResource
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
                'type'          => 'subscription',
                'attributes'    => [
                    'name'          => $this->name,
                    'status'        => $this->status,
                    'driver'        => $this->driverName(),
                    'trial_ends_at' => $this->trial_ends_at,
                    'created_at'    => $this->created_at->formatLocalized('%d. %b. %Y'),
                    'renews_at'     => $this->created_at->addDays(28)->formatLocalized('%d. %b. %Y'), // TODO: add renew date
                    'ends_at'       => $this->ends_at
                        ? $this->ends_at->formatLocalized('%d. %b. %Y')
                        : null,
                ],
                'relationships' => [
                    'plan' => new PlanResource($this->plan),
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
