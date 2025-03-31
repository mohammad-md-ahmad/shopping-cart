<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'value',
        'min_quantity',
        'applicable_model_type',
        'applicable_model_id',
        'valid_from',
        'valid_until',
    ];
}
