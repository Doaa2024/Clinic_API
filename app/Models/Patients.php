<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Patients extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'phone', 'dob', 'medical_history'];

    // Relationships
    public function appointments()
    {
        return $this->hasMany(Appointments::class);
    }
}
