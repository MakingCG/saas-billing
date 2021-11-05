<?php
namespace Domain\Plans\Models;

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
 * @property string price
 * @property string currency
 * @property int amount
 * @property string interval
 * @property boolean visible
 */
class Plan extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public function drivers(): HasMany
    {
        return $this->hasMany(PlanDriver::class, 'plan_id', 'id');
    }

    protected static function newFactory(): PlanFactory
    {
        return PlanFactory::new();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->id = Str::uuid();
        });
    }
}
