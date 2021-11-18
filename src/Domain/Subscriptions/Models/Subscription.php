<?php
namespace VueFileManager\Subscription\Domain\Subscriptions\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Database\Factories\SubscriptionFactory;
use VueFileManager\Subscription\Domain\Subscriptions\Traits\SubscriptionHelpers;

/**
 * @method static create(array $array)
 * @property string id
 * @property string user_id
 * @property string name
 * @property string subscription_id
 * @property string plan_id
 * @property DateTime trial_ends_at
 * @property DateTime ends_at
 */
class Subscription extends Model
{
    use HasFactory;
    use SubscriptionHelpers;

    protected $guarded = [];

    protected $casts = [
        'id'            => 'string',
        'ends_at'       => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function newFactory(): SubscriptionFactory
    {
        return SubscriptionFactory::new();
    }

    public function user(): HasOne
    {
        return $this->hasOne(config('auth.providers.users.model'), 'id', 'user_id');
    }

    public function plan(): HasOne
    {
        return $this->hasOne(Plan::class, 'id', 'plan_id');
    }

    public function driver(): hasOne
    {
        return $this->hasOne(SubscriptionDriver::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscription) {
            $subscription->id = Str::uuid();
        });
    }
}
