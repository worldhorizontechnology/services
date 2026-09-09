<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = ['customer_id', 'campaign_id', 'status', 'payment_status', 'discount_amount', 'total_amount'];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'order_service')
                    ->withPivot('id', 'quantity', 'price');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }
}
