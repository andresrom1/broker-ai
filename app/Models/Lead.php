<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'thread_id',
        'dni',
        'name',
        'email',
        'phone',
        'status',
    ];

    /**
     * Define la relación: un Lead puede tener muchas QuoteRequests.
     */
    public function quoteRequests()
    {
        return $this->hasMany(QuoteRequest::class);
    }

    /**
     * Define la relación: un Lead puede tener muchos Messages.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}

