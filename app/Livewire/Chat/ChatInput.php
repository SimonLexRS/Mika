<?php

namespace App\Livewire\Chat;

use Livewire\Component;

class ChatInput extends Component
{
    public string $message = '';

    public function sendMessage(): void
    {
        $message = trim($this->message);

        if (empty($message)) {
            return;
        }

        $this->dispatch('message-sent', message: $message);
        $this->message = '';
    }

    public function render()
    {
        return view('livewire.chat.chat-input');
    }
}
