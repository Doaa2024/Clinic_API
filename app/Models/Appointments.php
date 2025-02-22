<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointments extends Model
{
    use HasFactory;
    protected $fillable = ['patient_id', 'doctor_id', 'date', 'time', 'status', 'created_by'];

    // Relationships
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patients::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctors::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function prescription(): HasOne
    {
        return $this->hasOne(Prescriptions::class, 'appointment_id', 'id');
    }
    public function billing(): HasOne
    {
        return $this->hasOne(Billing::class, 'appointment_id', 'id');
    }
}
