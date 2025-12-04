<?php

namespace App\Services\ChatBrain\Intents;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\User;
use App\Services\ChatBrain\Context\ConversationContext;
use App\Services\ChatBrain\Contracts\IntentHandlerInterface;
use App\Services\ChatBrain\Contracts\ResponseBuilderInterface;
use App\Services\ChatBrain\Responses\CardResponse;
use App\Services\ChatBrain\Responses\QuickRepliesResponse;
use App\Services\ChatBrain\Responses\TextResponse;

class RegisterIncomeIntent implements IntentHandlerInterface
{
    public function canHandle(string $intent): bool
    {
        return $intent === 'register_income';
    }

    public function requiredEntities(): array
    {
        return ['amount'];
    }

    public function handle(
        User $user,
        string $message,
        array $entities,
        ConversationContext $context
    ): ResponseBuilderInterface {
        $amount = $entities['amount'];
        $category = $entities['category'] ?? 'ventas';
        $date = $entities['date'] ?? now();
        $description = $this->extractDescription($message, $category);

        // Crear la transacción
        $transaction = $user->transactions()->create([
            'amount' => $amount,
            'type' => TransactionType::Income,
            'category' => $category,
            'transaction_date' => $date,
            'description' => $description,
            'status' => TransactionStatus::Approved,
        ]);

        // Limpiar cualquier flujo pendiente
        $context->clearPendingFlow();

        return new CardResponse(
            content: "Ingreso registrado correctamente",
            cardData: [
                'type' => 'transaction_confirmation',
                'transaction_id' => $transaction->id,
                'amount' => $transaction->formatted_amount,
                'category' => ucfirst($category),
                'description' => $description,
                'date' => $transaction->transaction_date->format('d/m/Y'),
                'actions' => [
                    ['label' => 'Editar', 'action' => 'edit_transaction', 'id' => $transaction->id],
                    ['label' => 'Eliminar', 'action' => 'delete_transaction', 'id' => $transaction->id],
                ],
            ]
        );
    }

    public function askForMissingEntity(
        string $entity,
        ConversationContext $context
    ): ResponseBuilderInterface {
        return match($entity) {
            'amount' => new TextResponse(
                "¿Cuánto fue el ingreso? Puedes decirme algo como '\$5000' o '5000 pesos'",
                followUp: [
                    'intent' => 'register_income',
                    'expecting' => 'amount',
                    'entities' => [],
                ]
            ),
            'category' => new QuickRepliesResponse(
                "¿De qué tipo de ingreso se trata?",
                options: ['Ventas', 'Servicios', 'Freelance', 'Otros'],
                followUp: [
                    'intent' => 'register_income',
                    'expecting' => 'category',
                    'entities' => $context->get('pending_flow')['entities'] ?? [],
                ]
            ),
            default => new TextResponse("Necesito más información para registrar el ingreso"),
        };
    }

    /**
     * Extraer descripción del mensaje.
     */
    protected function extractDescription(string $message, string $category): string
    {
        $description = preg_replace('/\$?\d+(?:[.,]\d+)?/', '', $message);
        $description = preg_replace('/\b(ingres[eo]|cobr[eéoó]|recib[íi]|me pagaron|pesos?|mil|k)\b/iu', '', $description);
        $description = trim(preg_replace('/\s+/', ' ', $description));

        return $description ?: ucfirst($category);
    }
}
