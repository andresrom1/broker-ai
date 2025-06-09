<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteRequest extends Model
{
    protected $fillable = [
        'vehicle_brand',
        'vehicle_model',
        'vehicle_version',
        'vehicle_fuel',
        'vehicle_year',
        'vehicle_postal_code',
        'dni',
        'coverage_type',
        'phone',
        'session_id',
        'quoted',
    ];

    public function alternatives() {
        return $this->hasMany(QuoteAlternative::class);
    }
}
