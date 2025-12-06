<div class="h-full flex flex-col lg:flex-row">
    {{-- Panel de Productos (Izquierda) --}}
    <div class="lg:w-2/3 flex flex-col bg-mika-bg">
        {{-- Header --}}
        <header class="h-16 flex items-center justify-between px-4 bg-mika-dark border-b border-gray-800">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-xl font-bold">Punto de Venta</h1>
            </div>

            <div class="flex items-center gap-4">
                @if($this->cashRegister)
                    <span class="px-3 py-1 bg-green-600/20 text-green-400 rounded-full text-sm">
                        Caja abierta
                    </span>
                @else
                    <a href="{{ route('pos.cash-register.open') }}" class="px-3 py-1 bg-yellow-600 text-white rounded-full text-sm hover:bg-yellow-700">
                        Abrir caja
                    </a>
                @endif

                <span class="text-gray-400 text-sm">
                    {{ auth()->user()->name }}
                </span>
            </div>
        </header>

        {{-- Búsqueda --}}
        <div class="p-4">
            <div class="relative">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    wire:keydown.enter="searchBarcode($event.target.value)"
                    placeholder="Buscar producto o escanear código..."
                    class="w-full px-4 py-3 pl-12 bg-mika-dark border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 text-lg"
                    autofocus
                >
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        {{-- Lista de Productos --}}
        <div class="flex-1 overflow-y-auto px-4 pb-4">
            @if(strlen($this->search) >= 2)
                @if($this->products->isEmpty())
                    <div class="text-center text-gray-500 py-8">
                        No se encontraron productos
                    </div>
                @else
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3">
                        @foreach($this->products as $product)
                            <button
                                wire:click="addToCart({{ $product->id }})"
                                class="bg-mika-dark p-4 rounded-xl text-left hover:bg-gray-800 transition-colors group"
                            >
                                @if($product->image)
                                    <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-full h-24 object-cover rounded-lg mb-2">
                                @else
                                    <div class="w-full h-24 bg-gray-700 rounded-lg mb-2 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                @endif

                                <h3 class="font-medium text-white truncate group-hover:text-blue-400">
                                    {{ $product->name }}
                                </h3>

                                @if($product->category)
                                    <p class="text-xs text-gray-500 truncate">{{ $product->category->name }}</p>
                                @endif

                                <p class="text-lg font-bold text-green-400 mt-1">
                                    ${{ number_format($product->price, 2) }}
                                </p>

                                @if($product->sku)
                                    <p class="text-xs text-gray-600">{{ $product->sku }}</p>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endif
            @else
                <div class="text-center text-gray-500 py-16">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <p>Escribe para buscar productos</p>
                    <p class="text-sm">o escanea un código de barras</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Panel del Carrito (Derecha) --}}
    <div class="lg:w-1/3 flex flex-col bg-mika-dark border-l border-gray-800">
        {{-- Header del Carrito --}}
        <div class="h-16 flex items-center justify-between px-4 border-b border-gray-800">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="font-semibold">Carrito</span>
                @if(count($cart) > 0)
                    <span class="px-2 py-0.5 bg-blue-600 rounded-full text-xs">{{ $this->itemCount }}</span>
                @endif
            </div>

            @if(count($cart) > 0)
                <button wire:click="clearCart" class="text-red-400 hover:text-red-300 text-sm">
                    Vaciar
                </button>
            @endif
        </div>

        {{-- Cliente --}}
        <div class="px-4 py-3 border-b border-gray-800">
            @if($this->customer)
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium">{{ $this->customer->name }}</p>
                        <p class="text-sm text-gray-400">{{ $this->customer->phone ?? $this->customer->email }}</p>
                    </div>
                    <button wire:click="removeCustomer" class="text-gray-400 hover:text-red-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @else
                <button
                    wire:click="$set('showCustomerModal', true)"
                    class="w-full py-2 border border-dashed border-gray-600 rounded-lg text-gray-400 hover:border-blue-500 hover:text-blue-400 transition-colors"
                >
                    + Agregar cliente
                </button>
            @endif
        </div>

        {{-- Items del Carrito --}}
        <div class="flex-1 overflow-y-auto px-4 py-2">
            @forelse($cart as $key => $item)
                <div class="py-3 border-b border-gray-800 cart-item-enter" wire:key="{{ $key }}">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1 pr-2">
                            <h4 class="font-medium text-white">{{ $item['name'] }}</h4>
                            <p class="text-sm text-gray-400">${{ number_format($item['price'], 2) }} / {{ $item['unit'] }}</p>
                        </div>
                        <button
                            wire:click="removeFromCart('{{ $key }}')"
                            class="text-gray-500 hover:text-red-400 p-1"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <button
                                wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})"
                                class="w-8 h-8 flex items-center justify-center bg-gray-700 rounded-lg hover:bg-gray-600"
                            >
                                -
                            </button>
                            <input
                                type="number"
                                value="{{ $item['quantity'] }}"
                                wire:change="updateQuantity('{{ $key }}', $event.target.value)"
                                class="w-16 h-8 text-center bg-gray-800 border border-gray-700 rounded-lg text-white"
                                min="0"
                                step="0.01"
                            >
                            <button
                                wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})"
                                class="w-8 h-8 flex items-center justify-center bg-gray-700 rounded-lg hover:bg-gray-600"
                            >
                                +
                            </button>
                        </div>

                        <span class="font-bold text-green-400">
                            ${{ number_format(($item['price'] * $item['quantity']) - ($item['discount'] ?? 0), 2) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-500 py-12">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p>El carrito está vacío</p>
                </div>
            @endforelse
        </div>

        {{-- Totales y Pago --}}
        @if(count($cart) > 0)
            <div class="border-t border-gray-800 p-4 space-y-3 bg-gray-900/50">
                {{-- Subtotal --}}
                <div class="flex justify-between text-gray-400">
                    <span>Subtotal</span>
                    <span>${{ number_format($this->subtotal, 2) }}</span>
                </div>

                {{-- Impuestos --}}
                <div class="flex justify-between text-gray-400">
                    <span>Impuestos</span>
                    <span>${{ number_format($this->taxTotal, 2) }}</span>
                </div>

                {{-- Descuento --}}
                @if($this->discount > 0)
                    <div class="flex justify-between text-red-400">
                        <span>Descuento</span>
                        <span>-${{ number_format($this->discount, 2) }}</span>
                    </div>
                @endif

                {{-- Total --}}
                <div class="flex justify-between text-xl font-bold pt-2 border-t border-gray-700">
                    <span>Total</span>
                    <span class="text-green-400">${{ number_format($this->total, 2) }}</span>
                </div>

                {{-- Botones de Pago Rápido --}}
                <div class="grid grid-cols-3 gap-2 pt-2">
                    <button
                        wire:click="quickPay('cash')"
                        @if(!$this->cashRegister) disabled @endif
                        class="py-3 bg-green-600 hover:bg-green-700 rounded-xl font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        💵 Efectivo
                    </button>
                    <button
                        wire:click="quickPay('card')"
                        @if(!$this->cashRegister) disabled @endif
                        class="py-3 bg-blue-600 hover:bg-blue-700 rounded-xl font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        💳 Tarjeta
                    </button>
                    <button
                        wire:click="quickPay('transfer')"
                        @if(!$this->cashRegister) disabled @endif
                        class="py-3 bg-purple-600 hover:bg-purple-700 rounded-xl font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        📱 Trans.
                    </button>
                </div>

                {{-- Botón Cobrar --}}
                <button
                    wire:click="openPaymentModal"
                    @if(!$this->cashRegister) disabled @endif
                    class="w-full py-4 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 rounded-xl font-bold text-lg disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Cobrar ${{ number_format($this->total, 2) }}
                </button>
            </div>
        @endif
    </div>

    {{-- Modal de Pago --}}
    @if($showPaymentModal)
        <div class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
            <div class="bg-mika-dark rounded-2xl w-full max-w-md" wire:click.outside="$set('showPaymentModal', false)">
                <div class="p-6 border-b border-gray-800">
                    <h2 class="text-xl font-bold">Confirmar Pago</h2>
                </div>

                <div class="p-6 space-y-4">
                    {{-- Total --}}
                    <div class="text-center py-4 bg-gray-800 rounded-xl">
                        <p class="text-gray-400 text-sm">Total a pagar</p>
                        <p class="text-3xl font-bold text-green-400">${{ number_format($this->total, 2) }}</p>
                    </div>

                    {{-- Método de Pago --}}
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Método de pago</label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach(['cash' => '💵', 'card' => '💳', 'transfer' => '📱', 'mixed' => '🔄'] as $method => $icon)
                                <button
                                    wire:click="$set('paymentMethod', '{{ $method }}')"
                                    class="py-3 rounded-xl border-2 transition-all {{ $paymentMethod === $method ? 'border-green-500 bg-green-500/20' : 'border-gray-700 hover:border-gray-600' }}"
                                >
                                    <span class="text-2xl">{{ $icon }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Monto Recibido --}}
                    @if($paymentMethod === 'cash' || $paymentMethod === 'mixed')
                        <div>
                            <label class="block text-sm text-gray-400 mb-2">Monto recibido</label>
                            <input
                                type="number"
                                wire:model.live="amountPaid"
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white text-xl text-center"
                                step="0.01"
                                min="0"
                            >

                            {{-- Sugerencias de monto --}}
                            <div class="grid grid-cols-4 gap-2 mt-2">
                                @php
                                    $suggestions = [
                                        ceil($this->total / 10) * 10,
                                        ceil($this->total / 50) * 50,
                                        ceil($this->total / 100) * 100,
                                        ceil($this->total / 500) * 500,
                                    ];
                                    $suggestions = array_unique(array_filter($suggestions, fn($s) => $s >= $this->total));
                                @endphp
                                @foreach(array_slice($suggestions, 0, 4) as $amount)
                                    <button
                                        wire:click="$set('amountPaid', {{ $amount }})"
                                        class="py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm"
                                    >
                                        ${{ number_format($amount, 0) }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Cambio --}}
                        @if($this->change > 0)
                            <div class="text-center py-3 bg-yellow-500/20 rounded-xl">
                                <p class="text-sm text-gray-400">Cambio</p>
                                <p class="text-2xl font-bold text-yellow-400">${{ number_format($this->change, 2) }}</p>
                            </div>
                        @endif
                    @endif

                    {{-- Notas --}}
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Notas (opcional)</label>
                        <textarea
                            wire:model="notes"
                            rows="2"
                            class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-xl text-white resize-none"
                            placeholder="Notas adicionales..."
                        ></textarea>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-800 flex gap-3">
                    <button
                        wire:click="$set('showPaymentModal', false)"
                        class="flex-1 py-3 bg-gray-700 hover:bg-gray-600 rounded-xl font-semibold"
                    >
                        Cancelar
                    </button>
                    <button
                        wire:click="processSale"
                        class="flex-1 py-3 bg-green-600 hover:bg-green-700 rounded-xl font-semibold"
                    >
                        Confirmar Pago
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Cliente --}}
    @if($showCustomerModal)
        <div class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
            <div class="bg-mika-dark rounded-2xl w-full max-w-md" wire:click.outside="$set('showCustomerModal', false)">
                <div class="p-6 border-b border-gray-800">
                    <h2 class="text-xl font-bold">Seleccionar Cliente</h2>
                </div>

                <div class="p-6">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="customerSearch"
                        placeholder="Buscar por nombre, teléfono o email..."
                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white mb-4"
                        autofocus
                    >

                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @forelse($this->customers as $customer)
                            <button
                                wire:click="selectCustomer({{ $customer->id }})"
                                class="w-full p-3 bg-gray-800 hover:bg-gray-700 rounded-xl text-left"
                            >
                                <p class="font-medium">{{ $customer->name }}</p>
                                <p class="text-sm text-gray-400">
                                    {{ $customer->phone ?? $customer->email ?? 'Sin contacto' }}
                                </p>
                            </button>
                        @empty
                            @if(strlen($customerSearch) >= 2)
                                <p class="text-center text-gray-500 py-4">No se encontraron clientes</p>
                            @else
                                <p class="text-center text-gray-500 py-4">Escribe para buscar</p>
                            @endif
                        @endforelse
                    </div>
                </div>

                <div class="p-6 border-t border-gray-800">
                    <button
                        wire:click="$set('showCustomerModal', false)"
                        class="w-full py-3 bg-gray-700 hover:bg-gray-600 rounded-xl font-semibold"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
