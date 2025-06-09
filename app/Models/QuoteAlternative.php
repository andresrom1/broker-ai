<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteAlternative extends Model {
    protected $fillable = [
        'quote_request_id', 'company', 'price', 'coverage', 'observations'
    ];

    public function request() {
        return $this->belongsTo(QuoteRequest::class);
    }
}
