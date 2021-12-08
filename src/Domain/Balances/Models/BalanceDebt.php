<?php
namespace VueFileManager\Subscription\Domain\Balances\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static create(array $array)
 * @property string id
 * @property string user_id
 * @property float debt
 * @property string currency
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class BalanceDebt extends Model
{
    protected $guarded = [];

    protected $casts = [
        'id'   => 'string',
        'debt' => 'float',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public function user(): HasOne
    {
        return $this->hasOne(config('auth.providers.users.model'), 'id', 'user_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(fn ($balanceDebt) => $balanceDebt->id = Str::uuid());
    }
}
