<?php

namespace App\Services\ChatBrain\Contracts;

use App\Models\User;
use App\Services\ChatBrain\Context\ConversationContext;

interface IntentHandlerInterface
{
    /**
     * Verificar si este handler puede manejar la intención dada.
     */
    public function canHandle(string $intent): bool;

    /**
     * Manejar la intención y generar una respuesta.
     */
    public function handle(
        User $user,
        string $message,
        array $entities,
        ConversationContext $context
    ): ResponseBuilderInterface;

    /**
     * Obtener las entidades requeridas para procesar esta intención.
     */
    public function requiredEntities(): array;

    /**
     * Generar respuesta para solicitar una entidad faltante.
     */
    public function askForMissingEntity(
        string $entity,
        ConversationContext $context
    ): ResponseBuilderInterface;
}
