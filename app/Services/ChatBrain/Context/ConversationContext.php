<?php

namespace App\Services\ChatBrain\Context;

use App\Models\Conversation;
use Illuminate\Support\Collection;

class ConversationContext
{
    protected array $context;

    public function __construct(
        protected Conversation $conversation
    ) {
        $this->context = $conversation->context ?? [];
    }

    /**
     * Verificar si hay un flujo pendiente.
     */
    public function hasPendingFlow(): bool
    {
        return isset($this->context['pending_flow']);
    }

    /**
     * Obtener el flujo pendiente.
     */
    public function getPendingFlow(): ?array
    {
        return $this->context['pending_flow'] ?? null;
    }

    /**
     * Establecer un flujo pendiente.
     */
    public function setPendingFlow(array $flowData): void
    {
        $this->context['pending_flow'] = $flowData;
        $this->save();
    }

    /**
     * Limpiar el flujo pendiente.
     */
    public function clearPendingFlow(): void
    {
        unset($this->context['pending_flow']);
        $this->save();
    }

    /**
     * Establecer datos parciales de una intención.
     */
    public function setPartialData(string $intent, array $entities): void
    {
        $this->context['pending_flow'] = [
            'intent' => $intent,
            'entities' => $entities,
            'expecting' => null,
        ];
        $this->save();
    }

    /**
     * Establecer qué entidad se espera.
     */
    public function setExpectingEntity(string $entity): void
    {
        if (isset($this->context['pending_flow'])) {
            $this->context['pending_flow']['expecting'] = $entity;
            $this->save();
        }
    }

    /**
     * Obtener los últimos mensajes de la conversación.
     */
    public function getRecentMessages(int $limit = 10): Collection
    {
        return $this->conversation->latestMessages($limit);
    }

    /**
     * Obtener un valor del contexto.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->context[$key] ?? $default;
    }

    /**
     * Establecer un valor en el contexto.
     */
    public function set(string $key, mixed $value): void
    {
        $this->context[$key] = $value;
        $this->save();
    }

    /**
     * Eliminar un valor del contexto.
     */
    public function forget(string $key): void
    {
        unset($this->context[$key]);
        $this->save();
    }

    /**
     * Obtener la conversación.
     */
    public function getConversation(): Conversation
    {
        return $this->conversation;
    }

    /**
     * Guardar el contexto en la base de datos.
     */
    protected function save(): void
    {
        $this->conversation->update(['context' => $this->context]);
    }
}
