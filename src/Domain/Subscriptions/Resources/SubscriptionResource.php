<?php
namespace VueFileManager\Subscription\Domain\Subscriptions\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
                'id'         => $this->id,
                'type'       => 'subscription',
                'attributes' => [
                    'name'          => $this->name,
                    'status'        => $this->status,
                    'trial_ends_at' => $this->trial_ends_at,
                    'ends_at'       => $this->ends_at,
                    'created_at'    => $this->created_at,
                ],
            ],
        ];
    }
}
