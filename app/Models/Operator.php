<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operator extends Model
{
    protected $fillable = ['name', 'rate_weekday', 'rate_saturday', 'rate_sunday', 'company', 'zone'];

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function externalOperations(): HasMany
    {
        return $this->hasMany(ExternalOperation::class);
    }
}
