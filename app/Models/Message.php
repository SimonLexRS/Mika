<?php

namespace App\Models;

use App\Enums\MessageSender;
use App\Enums\MessageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id',
        'content',
        'sender',
        'type',
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
            'sender' => MessageSender::class,
            'type' => MessageType::class,
            'meta_data' => 'array',
        ];
    }

    /**
     * Relación con conversación.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Verificar si el mensaje es del usuario.
     */
    public function isFromUser(): bool
    {
        return $this->sender === MessageSender::User;
    }

    /**
     * Verificar si el mensaje es del bot.
     */
    public function isFromBot(): bool
    {
        return $this->sender === MessageSender::Bot;
    }

    /**
     * Verificar si es un mensaje de texto.
     */
    public function isText(): bool
    {
        return $this->type === MessageType::Text;
    }

    /**
     * Verificar si es una tarjeta.
     */
    public function isCard(): bool
    {
        return $this->type === MessageType::Card;
    }

    /**
     * Verificar si tiene respuestas rápidas.
     */
    public function hasQuickReplies(): bool
    {
        return $this->type === MessageType::QuickReplies;
    }

    /**
     * Obtener las opciones de respuesta rápida.
     */
    public function getQuickRepliesAttribute(): array
    {
        if (!$this->hasQuickReplies() || !$this->meta_data) {
            return [];
        }

        return $this->meta_data['options'] ?? [];
    }

    /**
     * Obtener datos de la tarjeta.
     */
    public function getCardDataAttribute(): ?array
    {
        if (!$this->isCard() || !$this->meta_data) {
            return null;
        }

        return $this->meta_data;
    }
}
