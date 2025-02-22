<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prescriptions extends Model
{
    use HasFactory;
    protected $fillable = ['appointment_id', 'doctor_id', 'notes', 'medication'];
    protected $casts = [
        'medication' => 'array',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointments::class);
    }
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctors::class);
    }
}
