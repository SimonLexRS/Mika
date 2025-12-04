<?php

namespace App\Services\ChatBrain\Intents;

use App\Models\User;
use App\Services\ChatBrain\Context\ConversationContext;
use App\Services\ChatBrain\Contracts\IntentHandlerInterface;
use App\Services\ChatBrain\Contracts\ResponseBuilderInterface;
use App\Services\ChatBrain\Responses\QuickRepliesResponse;

class GreetingIntent implements IntentHandlerInterface
{
    protected array $greetings = [
        'morning' => [
            '¡Buenos días, %s! ¿En qué puedo ayudarte hoy?',
            '¡Hola, %s! Espero que tengas un excelente día. ¿Qué necesitas?',
        ],
        'afternoon' => [
            '¡Buenas tardes, %s! ¿Cómo puedo ayudarte?',
            '¡Hola, %s! ¿En qué te puedo asistir esta tarde?',
        ],
        'evening' => [
            '¡Buenas noches, %s! ¿Qué puedo hacer por ti?',
            '¡Hola, %s! ¿Necesitas registrar algo antes de terminar el día?',
        ],
    ];

    public function canHandle(string $intent): bool
    {
        return $intent === 'greeting';
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
        $hour = now()->hour;
        $timeOfDay = match(true) {
            $hour >= 5 && $hour < 12 => 'morning',
            $hour >= 12 && $hour < 19 => 'afternoon',
            default => 'evening',
        };

        $greetings = $this->greetings[$timeOfDay];
        $greeting = sprintf(
            $greetings[array_rand($greetings)],
            $user->name
        );

        return new QuickRepliesResponse(
            content: $greeting,
            options: [
                'Registrar un gasto',
                'Registrar un ingreso',
                'Ver mi saldo',
                '¿Qué puedes hacer?',
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
