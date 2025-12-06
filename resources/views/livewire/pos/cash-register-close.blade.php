<div class="min-h-screen p-4">
    <div class="max-w-2xl mx-auto">
        <div class="bg-mika-dark rounded-2xl overflow-hidden">
            {{-- Header --}}
            <div class="p-6 border-b border-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">Cerrar Caja</h1>
                        <p class="text-gray-400">{{ $this->branch?->name }}</p>
                    </div>
                    <a href="{{ route('pos.terminal') }}" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                </div>
            </div>

            @if(!$this->cashRegister)
                <div class="p-8 text-center">
                    <p class="text-gray-400">No hay caja abierta para cerrar.</p>
                    <a href="{{ route('dashboard') }}" class="inline-block mt-4 px-6 py-2 bg-blue-600 rounded-lg">
                        Volver al dashboard
                    </a>
                </div>
            @else
                <form wire:submit="closeRegister">
                    {{-- Resumen de la caja --}}
                    <div class="p-6 border-b border-gray-800">
                        <h2 class="font-semibold mb-4">Resumen del turno</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-800 p-4 rounded-xl">
                                <p class="text-sm text-gray-400">Apertura</p>
                                <p class="text-xl font-bold">${{ number_format($this->cashRegister->opening_amount, 2) }}</p>
                            </div>
                            <div class="bg-gray-800 p-4 rounded-xl">
                                <p class="text-sm text-gray-400">Ventas efectivo</p>
                                <p class="text-xl font-bold text-green-400">${{ number_format($this->cashRegister->cash_sales, 2) }}</p>
                            </div>
                            <div class="bg-gray-800 p-4 rounded-xl">
                                <p class="text-sm text-gray-400">Ventas tarjeta</p>
                                <p class="text-xl font-bold text-blue-400">${{ number_format($this->cashRegister->card_sales, 2) }}</p>
                            </div>
                            <div class="bg-gray-800 p-4 rounded-xl">
                                <p class="text-sm text-gray-400">Transferencias</p>
                                <p class="text-xl font-bold text-purple-400">${{ number_format($this->cashRegister->transfer_sales, 2) }}</p>
                            </div>
                            <div class="bg-gray-800 p-4 rounded-xl">
                                <p class="text-sm text-gray-400">Retiros</p>
                                <p class="text-xl font-bold text-red-400">-${{ number_format($this->cashRegister->withdrawals, 2) }}</p>
                            </div>
                            <div class="bg-gray-800 p-4 rounded-xl">
                                <p class="text-sm text-gray-400">Depósitos</p>
                                <p class="text-xl font-bold text-green-400">${{ number_format($this->cashRegister->deposits, 2) }}</p>
                            </div>
                        </div>

                        <div class="mt-4 p-4 bg-blue-600/20 border border-blue-600 rounded-xl">
                            <div class="flex justify-between items-center">
                                <span class="text-blue-400">Efectivo esperado en caja</span>
                                <span class="text-2xl font-bold">${{ number_format($this->cashRegister->calculateExpectedAmount(), 2) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Conteo de efectivo --}}
                    <div class="p-6 border-b border-gray-800">
                        <h2 class="font-semibold mb-4">Conteo de efectivo</h2>

                        {{-- Billetes --}}
                        <div class="mb-4">
                            <h3 class="text-sm text-gray-400 mb-2">Billetes</h3>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach([1000, 500, 200, 100, 50, 20] as $value)
                                    <div class="flex items-center gap-2 bg-gray-800 p-2 rounded-lg">
                                        <span class="text-sm text-gray-400 w-12">${{ $value }}</span>
                                        <input
                                            type="number"
                                            wire:model.live="bills{{ $value }}"
                                            class="flex-1 px-2 py-1 bg-gray-700 border border-gray-600 rounded text-white text-center w-16"
                                            min="0"
                                        >
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Monedas --}}
                        <div>
                            <h3 class="text-sm text-gray-400 mb-2">Monedas</h3>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach([20, 10, 5, 2, 1] as $value)
                                    <div class="flex items-center gap-2 bg-gray-800 p-2 rounded-lg">
                                        <span class="text-sm text-gray-400 w-12">${{ $value }}</span>
                                        <input
                                            type="number"
                                            wire:model.live="coins{{ $value }}"
                                            class="flex-1 px-2 py-1 bg-gray-700 border border-gray-600 rounded text-white text-center w-16"
                                            min="0"
                                        >
                                    </div>
                                @endforeach
                                <div class="flex items-center gap-2 bg-gray-800 p-2 rounded-lg">
                                    <span class="text-sm text-gray-400 w-12">$0.50</span>
                                    <input
                                        type="number"
                                        wire:model.live="coins50c"
                                        class="flex-1 px-2 py-1 bg-gray-700 border border-gray-600 rounded text-white text-center w-16"
                                        min="0"
                                    >
                                </div>
                            </div>
                        </div>

                        {{-- Total contado --}}
                        <div class="mt-4 p-4 bg-gray-800 rounded-xl">
                            <div class="flex justify-between items-center">
                                <span>Total contado</span>
                                <input
                                    type="number"
                                    wire:model="actualAmount"
                                    class="w-40 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white text-xl text-right"
                                    step="0.01"
                                >
                            </div>
                        </div>

                        {{-- Diferencia --}}
                        @php
                            $expected = $this->cashRegister->calculateExpectedAmount();
                            $difference = $this->actualAmount - $expected;
                        @endphp
                        <div class="mt-4 p-4 rounded-xl {{ $difference >= 0 ? 'bg-green-600/20 border border-green-600' : 'bg-red-600/20 border border-red-600' }}">
                            <div class="flex justify-between items-center">
                                <span class="{{ $difference >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                    {{ $difference >= 0 ? 'Sobrante' : 'Faltante' }}
                                </span>
                                <span class="text-2xl font-bold {{ $difference >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                    ${{ number_format(abs($difference), 2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Notas --}}
                    <div class="p-6 border-b border-gray-800">
                        <label class="block text-sm text-gray-400 mb-2">Notas de cierre (opcional)</label>
                        <textarea
                            wire:model="notes"
                            rows="2"
                            class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white resize-none"
                            placeholder="Observaciones del cierre..."
                        ></textarea>
                    </div>

                    {{-- Botones --}}
                    <div class="p-6 flex gap-3">
                        <a href="{{ route('pos.terminal') }}" class="flex-1 py-4 bg-gray-700 hover:bg-gray-600 rounded-xl font-semibold text-center">
                            Cancelar
                        </a>
                        <button type="submit" class="flex-1 py-4 bg-red-600 hover:bg-red-700 rounded-xl font-semibold">
                            Cerrar Caja
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
