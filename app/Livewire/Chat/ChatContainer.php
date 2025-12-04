<?php

namespace App\Livewire\Chat;

use App\Services\ChatBrain\ChatBrainService;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class ChatContainer extends Component
{
    public Collection $messages;
    public bool $isTyping = false;
    public ?int $conversationId = null;

    public function mount(): void
    {
        $user = auth()->user();
        $conversation = $user->activeConversation();

        if ($conversation) {
            $this->conversationId = $conversation->id;
            $this->messages = $conversation->latestMessages(50);
        } else {
            $this->messages = collect();
            $this->addWelcomeMessage();
        }
    }

    #[On('message-sent')]
    public function handleUserMessage(string $message): void
    {
        // Agregar mensaje del usuario inmediatamente (optimistic UI)
        $this->messages->push([
            'id' => 'temp-' . time(),
            'content' => $message,
            'sender' => 'user',
            'type' => 'text',
            'meta_data' => null,
            'created_at' => now(),
        ]);

        // Mostrar indicador de escritura
        $this->isTyping = true;

        // Procesar con ChatBrain (despachamos para async)
        $this->processMessage($message);
    }

    public function processMessage(string $message): void
    {
        $chatBrain = app(ChatBrainService::class);

        try {
            $result = $chatBrain->process(auth()->user(), $message);

            // Actualizar ID de conversación si es nueva
            if (!$this->conversationId) {
                $this->conversationId = $result['user_message']->conversation_id;
            }

            // Reemplazar mensaje temporal con el real
            $this->messages = $this->messages->map(function ($msg) use ($result) {
                if (is_array($msg) && str_starts_with($msg['id'] ?? '', 'temp-')) {
                    return $result['user_message'];
                }
                return $msg;
            });

            // Agregar respuesta del bot
            $this->messages->push($result['bot_response']);

        } catch (\Exception $e) {
            report($e);

            $this->messages->push([
                'id' => 'error-' . time(),
                'content' => 'Lo siento, ocurrió un error. ¿Podrías intentarlo de nuevo?',
                'sender' => 'bot',
                'type' => 'text',
                'meta_data' => null,
                'created_at' => now(),
            ]);
        }

        $this->isTyping = false;
        $this->dispatch('scroll-to-bottom');
    }

    #[On('quick-reply-selected')]
    public function handleQuickReply(string $value): void
    {
        $this->handleUserMessage($value);
    }

    #[On('card-action')]
    public function handleCardAction(string $action, ?int $id = null): void
    {
        match($action) {
            'edit_transaction' => $this->dispatch('open-edit-modal', transactionId: $id),
            'delete_transaction' => $this->deleteTransaction($id),
            default => null,
        };
    }

    protected function addWelcomeMessage(): void
    {
        $welcomeMessage = config('mika.chat.welcome_message');

        $this->messages->push([
            'id' => 'welcome',
            'content' => $welcomeMessage,
            'sender' => 'bot',
            'type' => 'quick_replies',
            'meta_data' => [
                'options' => [
                    'Registrar un gasto',
                    'Registrar un ingreso',
                    'Ver mi saldo',
                    '¿Qué puedes hacer?',
                ],
            ],
            'created_at' => now(),
        ]);
    }

    protected function deleteTransaction(int $id): void
    {
        $transaction = auth()->user()->transactions()->find($id);

        if ($transaction) {
            $amount = $transaction->formatted_amount;
            $transaction->delete();

            $this->messages->push([
                'id' => 'deleted-' . time(),
                'content' => "Listo, eliminé la transacción de {$amount}.",
                'sender' => 'bot',
                'type' => 'text',
                'meta_data' => null,
                'created_at' => now(),
            ]);

            $this->dispatch('scroll-to-bottom');
        }
    }

    public function render()
    {
        return view('livewire.chat.chat-container');
    }
}
