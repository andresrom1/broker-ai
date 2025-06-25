<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteAlternative extends Model {
    protected $fillable = [
        'quote_request_id', 'company', 'price', 'coverage', 'observations'
    ];

    public function quoteRequest() {
        return $this->belongsTo(QuoteRequest::class);
    }
    
    /**
     * Define la relación: una alternativa puede tener muchos adjuntos.
     */

    public function attachments()
    {
        return $this->hasMany(QuoteAlternativeAttachment::class, 'quote_alternative_id');
    }

}
