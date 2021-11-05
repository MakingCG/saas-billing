<?php

namespace Domain\Customers\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @property string user_id
 * @property string driver
 * @property string driver_user_id
 */
class Customer extends Model
{
    public $primaryKey = null;

    public $incrementing = false;

    protected $guarded = [];

    public function user(): HasOne
    {
        return $this->hasOne(config('auth.providers.users.model'), 'id', 'user_id');
    }
}
