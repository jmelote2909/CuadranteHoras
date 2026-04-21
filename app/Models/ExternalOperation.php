<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalOperation extends Model
{
    protected $fillable = ['operator_id', 'month', 'year', 'amount'];

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }
}
