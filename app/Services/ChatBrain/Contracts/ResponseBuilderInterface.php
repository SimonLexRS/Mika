<?php

namespace App\Services\ChatBrain\Contracts;

interface ResponseBuilderInterface
{
    /**
     * Obtener el tipo de respuesta (text, card, quick_replies, etc.)
     */
    public function getType(): string;

    /**
     * Obtener el contenido textual de la respuesta.
     */
    public function getContent(): string;

    /**
     * Obtener metadata adicional para renderizado.
     */
    public function getMetaData(): ?array;

    /**
     * Convertir la respuesta a array.
     */
    public function toArray(): array;

    /**
     * Verificar si la respuesta requiere seguimiento.
     */
    public function requiresFollowUp(): bool;

    /**
     * Obtener el contexto de seguimiento.
     */
    public function getFollowUpContext(): ?array;
}
