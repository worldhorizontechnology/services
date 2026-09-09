<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Channel extends Model
{
    protected $fillable = ['name', 'type', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
