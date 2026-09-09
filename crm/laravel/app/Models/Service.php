<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    protected $fillable = ['name', 'price', 'is_active'];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_service')
                    ->withPivot('id', 'quantity', 'price');
    }
}
