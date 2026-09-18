<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Notifications\TelegramNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = ['order_id', 'amount', 'method', 'paid_at'];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

     protected static function booted(): void
    {
        static::created(function (Payment $payment) {
            $payment->load([
                'order.assignments.executor',
            ]);

            $payment->notify(
                new TelegramNotification($payment)
            );
        });
    }
}
