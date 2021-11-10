<?php
namespace VueFileManager\Subscription\Domain\Subscriptions\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;

/**
 * @method static create(array $array)
 * @property string id
 * @property string user_id
 * @property string name
 * @property string subscription_id
 * @property string plan_id
 * @property timestamp trial_ends_at
 * @property timestamp ends_at
 */
class Subscription extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public function user(): HasOne
    {
        return $this->hasOne(config('auth.providers.users.model'), 'id', 'user_id');
    }

    public function plan(): HasOne
    {
        return $this->hasOne(Plan::class, 'id', 'plan_id');
    }

    public function driver(): HasMany
    {
        return $this->hasMany(SubscriptionDriver::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscription) {
            $subscription->id = Str::uuid();
        });
    }
}
