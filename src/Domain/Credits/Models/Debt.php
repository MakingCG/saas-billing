<?php
namespace VueFileManager\Subscription\Domain\Credits\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use VueFileManager\Subscription\Database\Factories\DebtFactory;
use VueFileManager\Subscription\Domain\Transactions\Models\Transaction;

/**
 * @method static create(array $array)
 * @property string id
 * @property string user_id
 * @property string transaction_id
 * @property float amount
 * @property string currency
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Debt extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id'     => 'string',
        'amount' => 'float',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public function user(): HasOne
    {
        return $this->hasOne(config('auth.providers.users.model'), 'id', 'user_id');
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class, 'id', 'transaction_id');
    }

    protected static function newFactory(): DebtFactory
    {
        return DebtFactory::new();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(fn ($debt) => $debt->id = Str::uuid());
    }
}
