<?php

namespace App\Livewire\Pos;

use App\Models\CashMovement;
use App\Models\CashRegister;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CashRegisterOpen extends Component
{
    public float $openingAmount = 0;
    public string $notes = '';

    protected $rules = [
        'openingAmount' => 'required|numeric|min:0',
        'notes' => 'nullable|string|max:500',
    ];

    #[Computed]
    public function branch()
    {
        return auth()->user()->branch;
    }

    #[Computed]
    public function existingOpenRegister()
    {
        return CashRegister::where('branch_id', $this->branch?->id)
            ->where('status', 'open')
            ->first();
    }

    public function openRegister()
    {
        if (!$this->branch) {
            session()->flash('error', 'No tienes una sucursal asignada.');
            return;
        }

        if ($this->existingOpenRegister) {
            session()->flash('error', 'Ya hay una caja abierta en esta sucursal.');
            return redirect()->route('pos.terminal');
        }

        $this->validate();

        $register = CashRegister::create([
            'tenant_id' => auth()->user()->tenant_id,
            'branch_id' => $this->branch->id,
            'opened_by' => auth()->id(),
            'opening_amount' => $this->openingAmount,
            'expected_amount' => $this->openingAmount,
            'status' => CashRegister::STATUS_OPEN,
            'opened_at' => now(),
            'notes' => $this->notes,
        ]);

        // Registrar movimiento de apertura
        CashMovement::create([
            'tenant_id' => auth()->user()->tenant_id,
            'cash_register_id' => $register->id,
            'user_id' => auth()->id(),
            'type' => CashMovement::TYPE_OPENING,
            'payment_method' => 'cash',
            'amount' => $this->openingAmount,
            'description' => 'Apertura de caja',
        ]);

        session()->flash('success', 'Caja abierta exitosamente.');
        return redirect()->route('pos.terminal');
    }

    public function render()
    {
        return view('livewire.pos.cash-register-open')
            ->layout('layouts.app');
    }
}
