<?php
namespace VueFileManager\Subscription\Domain\Plans\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use VueFileManager\Subscription\Database\Factories\PlanFactory;

/**
 * @method static create(array $array)
 * @property string id
 * @property string name
 * @property string description
 * @property string currency
 * @property int amount
 * @property string interval
 * @property bool visible
 */
class Plan extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id'      => 'string',
        'visible' => 'bool',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public function drivers(): HasMany
    {
        return $this->hasMany(PlanDriver::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    protected static function newFactory(): PlanFactory
    {
        return PlanFactory::new();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($plan) {
            $plan->id = Str::uuid();
        });

        static::updated(function () {
            cache()->add('action.synchronize-plans', now()->toString());
        });
    }
}
