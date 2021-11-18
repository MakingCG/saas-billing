<?php
namespace VueFileManager\Subscription\Domain\Transactions\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use VueFileManager\Subscription\Database\Factories\TransactionFactory;

/**
 * @method static create(array $array)
 * @property string id
 * @property string user_id
 * @property string plan_name
 * @property string status
 * @property string currency
 * @property int amount
 * @property string driver
 * @property string reference
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Transaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id'      => 'string',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function newFactory(): TransactionFactory
    {
        return TransactionFactory::new();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($plan) {
            $plan->id = Str::uuid();
        });
    }
}
