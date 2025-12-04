<?php

namespace Database\Seeders;

use App\Enums\MessageSender;
use App\Enums\MessageType;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConversationSeeder extends Seeder
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

        // Cerrar conversaciones anteriores
        $demoUser->conversations()->update(['is_active' => false]);

        // Crear conversación activa
        $conversation = $demoUser->conversations()->create([
            'title' => 'Conversación de bienvenida',
            'is_active' => true,
            'last_activity_at' => now(),
            'context' => null,
        ]);

        // Mensaje de bienvenida
        $conversation->messages()->create([
            'content' => config('mika.chat.welcome_message'),
            'sender' => MessageSender::Bot,
            'type' => MessageType::QuickReplies,
            'meta_data' => [
                'options' => [
                    'Registrar un gasto',
                    'Registrar un ingreso',
                    'Ver mi saldo',
                    '¿Qué puedes hacer?',
                ],
            ],
        ]);

        $this->command->info('Conversación de bienvenida creada.');
    }
}
