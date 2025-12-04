<?php

namespace Database\Seeders;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demoUser = User::where('email', 'demo@mika.app')->first();

        if (!$demoUser) {
            $this->command->warn('Usuario demo no encontrado. Ejecuta UserSeeder primero.');
            return;
        }

        // Limpiar transacciones existentes del usuario demo
        $demoUser->transactions()->forceDelete();

        $transactions = [
            // Ingresos del mes actual
            [
                'amount' => 15000.00,
                'type' => TransactionType::Income,
                'category' => 'freelance',
                'transaction_date' => now()->startOfMonth()->addDays(1),
                'description' => 'Proyecto diseño web',
                'status' => TransactionStatus::Approved,
            ],
            [
                'amount' => 8500.00,
                'type' => TransactionType::Income,
                'category' => 'servicios',
                'transaction_date' => now()->startOfMonth()->addDays(10),
                'description' => 'Consultoría técnica',
                'status' => TransactionStatus::Approved,
            ],
            [
                'amount' => 3200.00,
                'type' => TransactionType::Income,
                'category' => 'ventas',
                'transaction_date' => now()->startOfMonth()->addDays(15),
                'description' => 'Venta de productos',
                'status' => TransactionStatus::Approved,
            ],

            // Gastos del mes actual
            [
                'amount' => 2500.00,
                'type' => TransactionType::Expense,
                'category' => 'servicios',
                'transaction_date' => now()->startOfMonth()->addDays(2),
                'description' => 'Renta oficina',
                'status' => TransactionStatus::Approved,
            ],
            [
                'amount' => 850.00,
                'type' => TransactionType::Expense,
                'category' => 'servicios',
                'transaction_date' => now()->startOfMonth()->addDays(3),
                'description' => 'Internet y teléfono',
                'status' => TransactionStatus::Approved,
            ],
            [
                'amount' => 450.00,
                'type' => TransactionType::Expense,
                'category' => 'comida',
                'transaction_date' => now()->startOfMonth()->addDays(5),
                'description' => 'Despensa semanal',
                'status' => TransactionStatus::Approved,
            ],
            [
                'amount' => 1200.00,
                'type' => TransactionType::Expense,
                'category' => 'transporte',
                'transaction_date' => now()->startOfMonth()->addDays(8),
                'description' => 'Gasolina',
                'status' => TransactionStatus::Approved,
            ],
            [
                'amount' => 350.00,
                'type' => TransactionType::Expense,
                'category' => 'comida',
                'transaction_date' => now()->startOfMonth()->addDays(12),
                'description' => 'Comida con cliente',
                'status' => TransactionStatus::Approved,
            ],
            [
                'amount' => 199.00,
                'type' => TransactionType::Expense,
                'category' => 'servicios',
                'transaction_date' => now()->startOfMonth()->addDays(14),
                'description' => 'Suscripción software',
                'status' => TransactionStatus::Approved,
            ],
            [
                'amount' => 580.00,
                'type' => TransactionType::Expense,
                'category' => 'salud',
                'transaction_date' => now()->startOfMonth()->addDays(18),
                'description' => 'Consulta médica',
                'status' => TransactionStatus::Approved,
            ],

            // Transacción pendiente
            [
                'amount' => 5000.00,
                'type' => TransactionType::Income,
                'category' => 'freelance',
                'transaction_date' => now(),
                'description' => 'Proyecto en curso',
                'status' => TransactionStatus::Pending,
            ],
        ];

        foreach ($transactions as $transaction) {
            $demoUser->transactions()->create($transaction);
        }

        $this->command->info('Se crearon ' . count($transactions) . ' transacciones de ejemplo.');
    }
}
