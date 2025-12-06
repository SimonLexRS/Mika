<?php

namespace App\Livewire\Pos;

use App\Models\CashRegister;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CashRegisterClose extends Component
{
    public float $actualAmount = 0;
    public string $notes = '';

    // Desglose de efectivo
    public int $bills1000 = 0;
    public int $bills500 = 0;
    public int $bills200 = 0;
    public int $bills100 = 0;
    public int $bills50 = 0;
    public int $bills20 = 0;
    public int $coins20 = 0;
    public int $coins10 = 0;
    public int $coins5 = 0;
    public int $coins2 = 0;
    public int $coins1 = 0;
    public int $coins50c = 0;

    protected $rules = [
        'actualAmount' => 'required|numeric|min:0',
        'notes' => 'nullable|string|max:500',
    ];

    #[Computed]
    public function branch()
    {
        return auth()->user()->branch;
    }

    #[Computed]
    public function cashRegister()
    {
        return CashRegister::where('branch_id', $this->branch?->id)
            ->where('status', 'open')
            ->first();
    }

    #[Computed]
    public function calculatedTotal(): float
    {
        return ($this->bills1000 * 1000)
            + ($this->bills500 * 500)
            + ($this->bills200 * 200)
            + ($this->bills100 * 100)
            + ($this->bills50 * 50)
            + ($this->bills20 * 20)
            + ($this->coins20 * 20)
            + ($this->coins10 * 10)
            + ($this->coins5 * 5)
            + ($this->coins2 * 2)
            + ($this->coins1 * 1)
            + ($this->coins50c * 0.5);
    }

    public function updatedBills1000() { $this->updateActualAmount(); }
    public function updatedBills500() { $this->updateActualAmount(); }
    public function updatedBills200() { $this->updateActualAmount(); }
    public function updatedBills100() { $this->updateActualAmount(); }
    public function updatedBills50() { $this->updateActualAmount(); }
    public function updatedBills20() { $this->updateActualAmount(); }
    public function updatedCoins20() { $this->updateActualAmount(); }
    public function updatedCoins10() { $this->updateActualAmount(); }
    public function updatedCoins5() { $this->updateActualAmount(); }
    public function updatedCoins2() { $this->updateActualAmount(); }
    public function updatedCoins1() { $this->updateActualAmount(); }
    public function updatedCoins50c() { $this->updateActualAmount(); }

    protected function updateActualAmount()
    {
        $this->actualAmount = $this->calculatedTotal;
    }

    public function closeRegister()
    {
        if (!$this->cashRegister) {
            session()->flash('error', 'No hay caja abierta para cerrar.');
            return redirect()->route('pos.terminal');
        }

        $this->validate();

        $this->cashRegister->close(
            auth()->user(),
            $this->actualAmount,
            $this->notes
        );

        session()->flash('success', 'Caja cerrada exitosamente.');
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.pos.cash-register-close')
            ->layout('layouts.app');
    }
}
