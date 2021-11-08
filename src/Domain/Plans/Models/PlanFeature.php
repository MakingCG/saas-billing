<?php

namespace VueFileManager\Subscription\Domain\Plans\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @method static create(array $array)
 * @property string plan_id
 * @property string key
 * @property string value
 */
class PlanFeature extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id'    => 'string',
        'value' => 'integer',
    ];

    public $incrementing = false;

    public $timestamps = false;
}
