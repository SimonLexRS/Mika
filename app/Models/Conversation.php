<?php

namespace App\Models;

use App\Enums\MessageSender;
use App\Enums\MessageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Conversation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'context',
        'is_active',
        'last_activity_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'context' => 'array',
            'is_active' => 'boolean',
            'last_activity_at' => 'datetime',
        ];
    }

    /**
     * Relación con usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con mensajes.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Obtener los últimos mensajes.
     */
    public function latestMessages(int $limit = 50): Collection
    {
        return $this->messages()
            ->latest()
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * Agregar un mensaje a la conversación.
     */
    public function addMessage(
        string $content,
        string|MessageSender $sender,
        string|MessageType $type = MessageType::Text,
        ?array $metaData = null
    ): Message {
        // Actualizar última actividad
        $this->update(['last_activity_at' => now()]);

        // Normalizar enums
        $senderValue = $sender instanceof MessageSender ? $sender->value : $sender;
        $typeValue = $type instanceof MessageType ? $type->value : $type;

        return $this->messages()->create([
            'content' => $content,
            'sender' => $senderValue,
            'type' => $typeValue,
            'meta_data' => $metaData,
        ]);
    }

    /**
     * Agregar mensaje del usuario.
     */
    public function addUserMessage(string $content): Message
    {
        return $this->addMessage($content, MessageSender::User, MessageType::Text);
    }

    /**
     * Agregar mensaje del bot.
     */
    public function addBotMessage(
        string $content,
        string|MessageType $type = MessageType::Text,
        ?array $metaData = null
    ): Message {
        return $this->addMessage($content, MessageSender::Bot, $type, $metaData);
    }

    /**
     * Actualizar el contexto de la conversación.
     */
    public function updateContext(array $newContext): void
    {
        $currentContext = $this->context ?? [];
        $this->update([
            'context' => array_merge($currentContext, $newContext),
        ]);
    }

    /**
     * Limpiar el contexto de la conversación.
     */
    public function clearContext(): void
    {
        $this->update(['context' => null]);
    }

    /**
     * Cerrar la conversación.
     */
    public function close(): void
    {
        $this->update(['is_active' => false]);
    }
}
