<?php
namespace VueFileManager\Subscription\Domain\Balances\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use VueFileManager\Subscription\Database\Factories\BalanceFactory;

/**
 * @method static create(array $array)
 * @property string id
 * @property string user_id
 * @property float balance
 * @property string currency
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Balance extends Model
{
    use HasFactory;
    use Sortable;

    protected $guarded = [];

    protected $casts = [
        'id'      => 'string',
        'balance' => 'float',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function newFactory(): BalanceFactory
    {
        return BalanceFactory::new();
    }

    public function user(): HasOne
    {
        return $this->hasOne(config('auth.providers.users.model'), 'id', 'user_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(fn ($balance) => $balance->id = Str::uuid());
    }
}
