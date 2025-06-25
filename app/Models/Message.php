<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lead_id',             // Clave foránea que vincula el mensaje a un Lead
        'role',                // Rol del remitente: 'user', 'assistant', 'admin'
        'content',             // Contenido textual del mensaje
        'openai_message_id',   // ID del mensaje en OpenAI (opcional, para referencia)
        'meta_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta_data' => 'array',
        ];
    }

    /**
     * Define la relación: un mensaje pertenece a un Lead.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    // Si en el futuro decides implementar adjuntos (MessageAttachment),
    // la relación 'hasMany' para ellos se definiría aquí:
    /*
    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }
    */
}

