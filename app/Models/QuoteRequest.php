<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteRequest extends Model
{
    protected $fillable = [
        'lead_id',
        'vehicle_brand',
        'vehicle_model',
        'vehicle_version',
        'vehicle_fuel',
        'vehicle_year',
        'vehicle_postal_code',
        'dni',
        'coverage_type',
        'phone',
        'quoted',
    ];

    /**
     * Define la relación: una QuoteRequest puede tener muchas QuoteAlternatives.
     */
    public function alternatives()
    {
        return $this->hasMany(QuoteAlternative::class);
    }

    /**
     * Define la relación: una QuoteRequest pertenece a un Lead.
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Define la relación: una QuoteRequest puede tener muchos Attachments a traves de QuoteAlternative.
     */
    public function attachments()
    {
        return $this->hasManyThrough(
            \App\Models\QuoteAlternativeAttachment::class, // Modelo destino (adjuntos)
            \App\Models\QuoteAlternative::class,           // Modelo intermedio (alternativas)
            'quote_request_id',                            // Foreign key en quote_alternatives → quote_requests
            'quote_alternative_id',                        // Foreign key en attachments → quote_alternatives
            'id',                                           // Local key en quote_requests
            'id'                                            // Local key en quote_alternatives
        );
    }

}
