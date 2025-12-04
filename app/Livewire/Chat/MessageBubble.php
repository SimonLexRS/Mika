<?php

namespace App\Livewire\Chat;

use Livewire\Component;

class MessageBubble extends Component
{
    public $message;

    public function mount($message): void
    {
        $this->message = is_array($message) ? (object) $message : $message;
    }

    public function render()
    {
        return view('livewire.chat.message-bubble');
    }
}
