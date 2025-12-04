<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([TransactionType::Income, TransactionType::Expense]);

        $expenseCategories = ['comida', 'transporte', 'servicios', 'compras', 'salud', 'entretenimiento', 'educacion', 'otros'];
        $incomeCategories = ['ventas', 'servicios', 'freelance', 'otros'];

        return [
            'user_id' => User::factory(),
            'amount' => fake()->randomFloat(2, 50, 10000),
            'type' => $type,
            'category' => $type === TransactionType::Expense
                ? fake()->randomElement($expenseCategories)
                : fake()->randomElement($incomeCategories),
            'transaction_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'description' => fake('es_MX')->sentence(3),
            'receipt_image_path' => null,
            'status' => fake()->randomElement([TransactionStatus::Approved, TransactionStatus::Pending]),
            'meta_data' => null,
        ];
    }

    /**
     * Indicate that the transaction is an expense.
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionType::Expense,
            'category' => fake()->randomElement(['comida', 'transporte', 'servicios', 'compras', 'salud']),
        ]);
    }

    /**
     * Indicate that the transaction is an income.
     */
    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionType::Income,
            'category' => fake()->randomElement(['ventas', 'servicios', 'freelance']),
        ]);
    }

    /**
     * Indicate that the transaction is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Approved,
        ]);
    }

    /**
     * Indicate that the transaction is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Pending,
        ]);
    }
}
