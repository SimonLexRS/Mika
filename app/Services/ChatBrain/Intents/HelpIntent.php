<?php

namespace App\Services\ChatBrain\Intents;

use App\Models\User;
use App\Services\ChatBrain\Context\ConversationContext;
use App\Services\ChatBrain\Contracts\IntentHandlerInterface;
use App\Services\ChatBrain\Contracts\ResponseBuilderInterface;
use App\Services\ChatBrain\Responses\QuickRepliesResponse;

class HelpIntent implements IntentHandlerInterface
{
    public function canHandle(string $intent): bool
    {
        return $intent === 'help' || $intent === 'unknown';
    }

    public function requiredEntities(): array
    {
        return [];
    }

    public function handle(
        User $user,
        string $message,
        array $entities,
        ConversationContext $context
    ): ResponseBuilderInterface {
        $helpText = "Soy Mika, tu asistente financiero. Puedo ayudarte con:\n\n" .
            "💰 **Registrar gastos**: \"Gasté \$500 en comida\"\n" .
            "💵 **Registrar ingresos**: \"Me pagaron \$5000\"\n" .
            "📊 **Ver tu saldo**: \"¿Cómo voy este mes?\"\n" .
            "📋 **Ver movimientos**: \"Muéstrame mis últimos gastos\"\n\n" .
            "Solo cuéntame qué necesitas y yo me encargo del resto.";

        return new QuickRepliesResponse(
            content: $helpText,
            options: [
                'Registrar un gasto',
                'Registrar un ingreso',
                'Ver mi saldo',
            ]
        );
    }

    public function askForMissingEntity(
        string $entity,
        ConversationContext $context
    ): ResponseBuilderInterface {
        return $this->handle(auth()->user(), '', [], $context);
    }
}
