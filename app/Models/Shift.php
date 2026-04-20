<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shift extends Model
{
    protected $fillable = ['operator_id', 'date', 'hours', 'color'];

    protected $casts = [
        'date' => 'date',
        'hours' => 'float',
    ];

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }
}
