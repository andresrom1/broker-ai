<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteAlternativeAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_alternative_id',
        'file_name',
        'file_path',
        'file_url',
        'mime_type',
        'file_size',
    ];

    /**
     * Define la relación: un adjunto pertenece a una QuoteAlternative.
     */
    public function quoteAlternative()
    {
        return $this->belongsTo(QuoteAlternative::class);
    }
}
