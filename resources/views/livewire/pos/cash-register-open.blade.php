<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-mika-dark rounded-2xl p-8">
            {{-- Header --}}
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-green-600/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold">Abrir Caja</h1>
                <p class="text-gray-400 mt-1">{{ $this->branch?->name ?? 'Sin sucursal' }}</p>
            </div>

            @if($this->existingOpenRegister)
                <div class="bg-yellow-600/20 border border-yellow-600 rounded-xl p-4 mb-6">
                    <p class="text-yellow-400 text-center">
                        Ya hay una caja abierta en esta sucursal.
                    </p>
                </div>

                <a href="{{ route('pos.terminal') }}" class="block w-full py-4 bg-blue-600 hover:bg-blue-700 rounded-xl font-semibold text-center">
                    Ir al POS
                </a>
            @else
                <form wire:submit="openRegister" class="space-y-6">
                    {{-- Monto inicial --}}
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Monto inicial en caja</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl">$</span>
                            <input
                                type="number"
                                wire:model="openingAmount"
                                class="w-full px-4 py-4 pl-10 bg-gray-800 border border-gray-700 rounded-xl text-white text-2xl text-center focus:outline-none focus:border-green-500"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                            >
                        </div>
                        @error('openingAmount')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Sugerencias rápidas --}}
                    <div class="grid grid-cols-4 gap-2">
                        @foreach([500, 1000, 2000, 5000] as $amount)
                            <button
                                type="button"
                                wire:click="$set('openingAmount', {{ $amount }})"
                                class="py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm"
                            >
                                ${{ number_format($amount, 0) }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Notas --}}
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Notas (opcional)</label>
                        <textarea
                            wire:model="notes"
                            rows="2"
                            class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white resize-none focus:outline-none focus:border-green-500"
                            placeholder="Notas de apertura..."
                        ></textarea>
                    </div>

                    {{-- Botones --}}
                    <div class="flex gap-3">
                        <a href="{{ route('dashboard') }}" class="flex-1 py-4 bg-gray-700 hover:bg-gray-600 rounded-xl font-semibold text-center">
                            Cancelar
                        </a>
                        <button type="submit" class="flex-1 py-4 bg-green-600 hover:bg-green-700 rounded-xl font-semibold">
                            Abrir Caja
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
