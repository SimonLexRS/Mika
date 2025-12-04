<?php

namespace App\Livewire\Components;

use Livewire\Component;

class QuickReplies extends Component
{
    public array $options;

    public function selectOption(string $option): void
    {
        $this->dispatch('quick-reply-selected', value: $option);
    }

    public function render()
    {
        return view('livewire.components.quick-replies');
    }
}
