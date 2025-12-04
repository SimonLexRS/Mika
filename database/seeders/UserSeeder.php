<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario de demostración
        User::updateOrCreate(
            ['email' => 'demo@mika.app'],
            [
                'name' => 'Usuario Demo',
                'password' => Hash::make('password123'),
                'business_name' => 'Mi Negocio Demo',
                'business_type' => 'freelance',
                'currency' => 'MXN',
                'email_verified_at' => now(),
                'preferences' => [
                    'notifications' => true,
                    'dark_mode' => true,
                ],
                'categories' => [
                    'expense' => [
                        'comida' => 'Comida y restaurantes',
                        'transporte' => 'Transporte y gasolina',
                        'servicios' => 'Servicios y suscripciones',
                        'compras' => 'Compras generales',
                        'salud' => 'Salud y médicos',
                        'entretenimiento' => 'Entretenimiento',
                        'educacion' => 'Educación',
                        'otros' => 'Otros gastos',
                    ],
                    'income' => [
                        'ventas' => 'Ventas',
                        'servicios' => 'Servicios profesionales',
                        'freelance' => 'Trabajo freelance',
                        'otros' => 'Otros ingresos',
                    ],
                ],
            ]
        );

        // Usuario de pruebas
        User::updateOrCreate(
            ['email' => 'test@mika.app'],
            [
                'name' => 'Test User',
                'password' => Hash::make('test1234'),
                'currency' => 'MXN',
                'email_verified_at' => now(),
            ]
        );
    }
}
