<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use App\Notifications\TelegramNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = ['first_name', 'last_name', 'phone', 'email', 'channel_id', 'campaign_id', 'entry_point', 'status', 'telegram_id', 'instagram_id', 'whatsapp', 'comment'];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    protected static function booted() 
    {
        static::created(function (Customer $customer) {
            $customer->notify(new TelegramNotification($customer));
        });
    }
}
