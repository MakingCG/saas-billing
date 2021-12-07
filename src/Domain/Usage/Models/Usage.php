<?php
namespace VueFileManager\Subscription\Domain\Usage\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use VueFileManager\Subscription\Database\Factories\UsageFactory;

/**
 * @method static create(array $array)
 * @property string plan_metered_feature_id
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
}
