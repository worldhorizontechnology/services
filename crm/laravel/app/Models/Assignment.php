<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $fillable = ['order_id', 'executor_id', 'status', 'start_date', 'due_date'];

    protected $casts = [
        'start_date' => 'datetime',
        'due_date' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executor_id');
    }
}
