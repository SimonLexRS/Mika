<?php

namespace App\Services\ChatBrain\Intents;

use App\Enums\TransactionType;
use App\Models\User;
use App\Services\ChatBrain\Context\ConversationContext;
use App\Services\ChatBrain\Contracts\IntentHandlerInterface;
use App\Services\ChatBrain\Contracts\ResponseBuilderInterface;
use App\Services\ChatBrain\Responses\CardResponse;
use App\Services\ChatBrain\Responses\TextResponse;

class QueryTransactionsIntent implements IntentHandlerInterface
{
    public function canHandle(string $intent): bool
    {
        return $intent === 'query_transactions';
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
        // Determinar si busca gastos o ingresos
        $type = $this->detectTransactionType($message);

        $query = $user->transactions()->approved();

        if ($type) {
            $query->where('type', $type);
        }

        $transactions = $query
            ->orderBy('transaction_date', 'desc')
            ->limit(5)
            ->get();

        if ($transactions->isEmpty()) {
            return new TextResponse(
                "No encontré transacciones recientes. ¿Quieres registrar algo?"
            );
        }

        $transactionList = $transactions->map(fn($t) => [
            'id' => $t->id,
            'amount' => $t->formatted_amount,
            'category' => ucfirst($t->category),
            'description' => $t->description,
            'date' => $t->transaction_date->format('d/m'),
            'type' => $t->type->value,
        ])->toArray();

        $typeLabel = match($type) {
            TransactionType::Expense => 'gastos',
            TransactionType::Income => 'ingresos',
            default => 'movimientos',
        };

        return new CardResponse(
            content: "Aquí están tus últimos {$typeLabel}",
            cardData: [
                'type' => 'transaction_list',
                'transactions' => $transactionList,
                'total_shown' => count($transactionList),
            ]
        );
    }

    public function askForMissingEntity(
        string $entity,
        ConversationContext $context
    ): ResponseBuilderInterface {
        return $this->handle(auth()->user(), '', [], $context);
    }

    /**
     * Detectar si el usuario busca gastos o ingresos.
     */
    protected function detectTransactionType(string $message): ?TransactionType
    {
        $normalized = mb_strtolower($message);

        if (preg_match('/gasto|gasté|pagué/u', $normalized)) {
            return TransactionType::Expense;
        }

        if (preg_match('/ingreso|cobré|recibí/u', $normalized)) {
            return TransactionType::Income;
        }

        return null;
    }
}
