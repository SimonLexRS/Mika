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

class RegisterExpenseIntent implements IntentHandlerInterface
{
    public function canHandle(string $intent): bool
    {
        return $intent === 'register_expense';
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
        $category = $entities['category'] ?? 'otros';
        $date = $entities['date'] ?? now();
        $description = $this->extractDescription($message, $category);

        // Crear la transacción
        $transaction = $user->transactions()->create([
            'amount' => $amount,
            'type' => TransactionType::Expense,
            'category' => $category,
            'transaction_date' => $date,
            'description' => $description,
            'status' => TransactionStatus::Approved,
        ]);

        // Limpiar cualquier flujo pendiente
        $context->clearPendingFlow();

        return new CardResponse(
            content: "Gasto registrado correctamente",
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
                "¿Cuánto fue el gasto? Puedes decirme algo como '\$500' o '500 pesos'",
                followUp: [
                    'intent' => 'register_expense',
                    'expecting' => 'amount',
                    'entities' => [],
                ]
            ),
            'category' => new QuickRepliesResponse(
                "¿En qué categoría lo clasificamos?",
                options: ['Comida', 'Transporte', 'Servicios', 'Compras', 'Salud', 'Otros'],
                followUp: [
                    'intent' => 'register_expense',
                    'expecting' => 'category',
                    'entities' => $context->get('pending_flow')['entities'] ?? [],
                ]
            ),
            default => new TextResponse("Necesito más información para registrar el gasto"),
        };
    }

    /**
     * Extraer descripción del mensaje.
     */
    protected function extractDescription(string $message, string $category): string
    {
        // Eliminar cantidades y palabras comunes
        $description = preg_replace('/\$?\d+(?:[.,]\d+)?/', '', $message);
        $description = preg_replace('/\b(gast[eéoó]|pagu[eé]|en|de|por|pesos?|mil|k)\b/iu', '', $description);
        $description = trim(preg_replace('/\s+/', ' ', $description));

        return $description ?: ucfirst($category);
    }
}
