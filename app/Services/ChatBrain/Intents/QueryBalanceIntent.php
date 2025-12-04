<?php

namespace App\Services\ChatBrain\Intents;

use App\Enums\TransactionType;
use App\Models\User;
use App\Services\ChatBrain\Context\ConversationContext;
use App\Services\ChatBrain\Contracts\IntentHandlerInterface;
use App\Services\ChatBrain\Contracts\ResponseBuilderInterface;
use App\Services\ChatBrain\Responses\CardResponse;

class QueryBalanceIntent implements IntentHandlerInterface
{
    public function canHandle(string $intent): bool
    {
        return $intent === 'query_balance';
    }

    public function requiredEntities(): array
    {
        return []; // No requiere entidades
    }

    public function handle(
        User $user,
        string $message,
        array $entities,
        ConversationContext $context
    ): ResponseBuilderInterface {
        // Obtener transacciones del mes actual
        $transactions = $user->transactions()
            ->approved()
            ->currentMonth()
            ->get();

        $income = $transactions
            ->where('type', TransactionType::Income)
            ->sum('amount');

        $expenses = $transactions
            ->where('type', TransactionType::Expense)
            ->sum('amount');

        $balance = $income - $expenses;

        // Obtener categoría con más gastos
        $topExpenseCategory = $transactions
            ->where('type', TransactionType::Expense)
            ->groupBy('category')
            ->map(fn($items) => $items->sum('amount'))
            ->sortDesc()
            ->keys()
            ->first();

        $month = now()->locale('es')->monthName;

        return new CardResponse(
            content: "Aquí está tu resumen de {$month}",
            cardData: [
                'type' => 'balance_summary',
                'balance' => $balance,
                'income' => $income,
                'expenses' => $expenses,
                'month' => ucfirst($month),
                'transaction_count' => $transactions->count(),
                'top_expense_category' => $topExpenseCategory ? ucfirst($topExpenseCategory) : null,
                'period' => [
                    'start' => now()->startOfMonth()->format('d/m/Y'),
                    'end' => now()->endOfMonth()->format('d/m/Y'),
                ],
            ]
        );
    }

    public function askForMissingEntity(
        string $entity,
        ConversationContext $context
    ): ResponseBuilderInterface {
        // Este intent no requiere entidades
        return $this->handle(
            auth()->user(),
            '',
            [],
            $context
        );
    }
}
