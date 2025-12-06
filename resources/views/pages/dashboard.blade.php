<x-layouts.app>
    <div class="p-4 max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold">Dashboard</h1>
            <p class="text-gray-400">Bienvenido, {{ auth()->user()->name }}</p>
        </div>

        {{-- Accesos rápidos --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <a href="{{ route('pos.terminal') }}" class="bg-gradient-to-br from-green-600 to-emerald-700 p-6 rounded-2xl hover:scale-105 transition-transform">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-lg">Punto de Venta</h3>
                <p class="text-white/70 text-sm">Abrir terminal POS</p>
            </a>

            <a href="{{ route('inventory.products') }}" class="bg-gradient-to-br from-blue-600 to-indigo-700 p-6 rounded-2xl hover:scale-105 transition-transform">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-lg">Inventario</h3>
                <p class="text-white/70 text-sm">Gestionar productos</p>
            </a>

            <a href="{{ route('reports.sales') }}" class="bg-gradient-to-br from-purple-600 to-pink-700 p-6 rounded-2xl hover:scale-105 transition-transform">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-lg">Reportes</h3>
                <p class="text-white/70 text-sm">Ver estadísticas</p>
            </a>

            <a href="{{ route('chat') }}" class="bg-gradient-to-br from-orange-600 to-red-700 p-6 rounded-2xl hover:scale-105 transition-transform">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-lg">Asistente Mika</h3>
                <p class="text-white/70 text-sm">Chat con IA</p>
            </a>
        </div>

        {{-- Resumen del día --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Ventas del día --}}
            <div class="lg:col-span-2 bg-mika-dark rounded-2xl p-6">
                <h2 class="font-semibold mb-4">Resumen de hoy</h2>

                @php
                    $todaySales = \App\Models\Sale::today()->completed();
                    $totalSales = $todaySales->sum('total');
                    $salesCount = $todaySales->count();
                @endphp

                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="text-center p-4 bg-gray-800 rounded-xl">
                        <p class="text-sm text-gray-400">Ventas</p>
                        <p class="text-2xl font-bold text-green-400">${{ number_format($totalSales, 2) }}</p>
                    </div>
                    <div class="text-center p-4 bg-gray-800 rounded-xl">
                        <p class="text-sm text-gray-400">Transacciones</p>
                        <p class="text-2xl font-bold">{{ $salesCount }}</p>
                    </div>
                    <div class="text-center p-4 bg-gray-800 rounded-xl">
                        <p class="text-sm text-gray-400">Ticket promedio</p>
                        <p class="text-2xl font-bold text-blue-400">
                            ${{ $salesCount > 0 ? number_format($totalSales / $salesCount, 2) : '0.00' }}
                        </p>
                    </div>
                </div>

                {{-- Últimas transacciones --}}
                <h3 class="text-sm text-gray-400 mb-3">Últimas transacciones</h3>
                <div class="space-y-2">
                    @forelse(\App\Models\Sale::today()->completed()->latest()->limit(5)->get() as $sale)
                        <div class="flex items-center justify-between p-3 bg-gray-800 rounded-lg">
                            <div>
                                <span class="font-mono text-blue-400">{{ $sale->ticket_number }}</span>
                                <span class="text-gray-500 text-sm ml-2">{{ $sale->created_at->format('H:i') }}</span>
                            </div>
                            <span class="font-semibold text-green-400">${{ number_format($sale->total, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 py-4">No hay ventas hoy</p>
                    @endforelse
                </div>
            </div>

            {{-- Alertas y estado --}}
            <div class="space-y-6">
                {{-- Estado de caja --}}
                <div class="bg-mika-dark rounded-2xl p-6">
                    <h2 class="font-semibold mb-4">Estado de Caja</h2>

                    @php
                        $cashRegister = auth()->user()->branch_id
                            ? \App\Models\CashRegister::where('branch_id', auth()->user()->branch_id)
                                ->where('status', 'open')
                                ->first()
                            : null;
                    @endphp

                    @if($cashRegister)
                        <div class="text-center mb-4">
                            <div class="w-16 h-16 bg-green-600/20 rounded-full flex items-center justify-center mx-auto mb-2">
                                <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-green-400 font-medium">Caja Abierta</p>
                            <p class="text-sm text-gray-500">Desde {{ $cashRegister->opened_at->format('H:i') }}</p>
                        </div>
                        <div class="text-center p-3 bg-gray-800 rounded-lg mb-4">
                            <p class="text-sm text-gray-400">En caja (efectivo)</p>
                            <p class="text-xl font-bold">${{ number_format($cashRegister->calculateExpectedAmount(), 2) }}</p>
                        </div>
                        <a href="{{ route('pos.cash-register.close') }}" class="block w-full py-2 text-center bg-red-600 hover:bg-red-700 rounded-lg">
                            Cerrar Caja
                        </a>
                    @else
                        <div class="text-center mb-4">
                            <div class="w-16 h-16 bg-yellow-600/20 rounded-full flex items-center justify-center mx-auto mb-2">
                                <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <p class="text-yellow-400 font-medium">Caja Cerrada</p>
                            <p class="text-sm text-gray-500">Abre la caja para vender</p>
                        </div>
                        <a href="{{ route('pos.cash-register.open') }}" class="block w-full py-2 text-center bg-green-600 hover:bg-green-700 rounded-lg">
                            Abrir Caja
                        </a>
                    @endif
                </div>

                {{-- Alertas de stock --}}
                <div class="bg-mika-dark rounded-2xl p-6">
                    <h2 class="font-semibold mb-4">Alertas de Stock</h2>

                    @php
                        $lowStockProducts = \App\Models\Product::where('track_inventory', true)
                            ->whereNotNull('low_stock_threshold')
                            ->whereHas('inventoryStocks', function($q) {
                                $q->whereRaw('quantity <= (SELECT low_stock_threshold FROM products WHERE products.id = inventory_stocks.product_id)');
                            })
                            ->limit(5)
                            ->get();
                    @endphp

                    @if($lowStockProducts->isEmpty())
                        <div class="text-center py-4">
                            <div class="w-12 h-12 bg-green-600/20 rounded-full flex items-center justify-center mx-auto mb-2">
                                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <p class="text-gray-400">Todo el inventario está bien</p>
                        </div>
                    @else
                        <div class="space-y-2">
                            @foreach($lowStockProducts as $product)
                                <div class="flex items-center justify-between p-3 bg-red-600/10 border border-red-600/20 rounded-lg">
                                    <span class="truncate">{{ $product->name }}</span>
                                    <span class="text-red-400 font-medium">
                                        {{ $product->inventoryStocks->first()?->quantity ?? 0 }} {{ $product->unit }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
