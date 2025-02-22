<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Billing extends Model
{
    use HasFactory;
    protected $fillable = ['amount', 'appointment_id', 'payment_status'];
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointments::class);
    }
}
