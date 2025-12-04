<?php

namespace App\Livewire\Components;

use Livewire\Component;

class ActionCard extends Component
{
    public string $title;
    public array $data;

    public function performAction(string $action, ?int $id = null): void
    {
        $this->dispatch('card-action', action: $action, id: $id);
    }

    public function render()
    {
        return view('livewire.components.action-card');
    }
}
