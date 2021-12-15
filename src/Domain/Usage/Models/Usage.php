<?php
namespace VueFileManager\Subscription\Domain\Usage\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use VueFileManager\Subscription\Database\Factories\UsageFactory;
use VueFileManager\Subscription\Domain\Plans\Models\PlanMeteredFeature;
use VueFileManager\Subscription\Domain\Subscriptions\Models\Subscription;
use VueFileManager\Subscription\Support\EngineManager;

/**
 * @method static create(array $array)
 * @property string metered_feature_id
 * @property string subscription_id
 * @property float quantity
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Usage extends Model
{
    use HasFactory;

    protected $casts = [
        'quantity' => 'float',
    ];

    protected $guarded = [];

    public $incrementing = false;

    protected $keyType = null;

    protected static function newFactory(): UsageFactory
    {
        return UsageFactory::new();
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'id', 'subscription_id');
    }

    public function feature(): HasOne
    {
        return $this->hasOne(PlanMeteredFeature::class, 'id', 'metered_feature_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($usage) {

            $driver = $usage->subscription->driver->driver ?? null;

            if (in_array($driver, config('subscription.metered_billing.native_support'))) {
                resolve(EngineManager::class)
                    ->driver($driver)
                    ->reportUsage($usage);
            }
        });
    }
}
