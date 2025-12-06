<x-layouts.app>
    @php
        // Today's data
        $todaySales = \App\Models\Sale::today()->completed();
        $totalSales = $todaySales->sum('total');
        $salesCount = $todaySales->count();
        $avgTicket = $salesCount > 0 ? $totalSales / $salesCount : 0;

        // Yesterday's data for comparison
        $yesterdaySales = \App\Models\Sale::whereDate('created_at', now()->subDay())
            ->where('status', 'completed');
        $yesterdayTotal = $yesterdaySales->sum('total');
        $yesterdayCount = $yesterdaySales->count();

        // Calculate changes
        $salesChange = $yesterdayTotal > 0 ? (($totalSales - $yesterdayTotal) / $yesterdayTotal) * 100 : 0;
        $countChange = $yesterdayCount > 0 ? (($salesCount - $yesterdayCount) / $yesterdayCount) * 100 : 0;

        // Cash register
        $cashRegister = auth()->user()->branch_id
            ? \App\Models\CashRegister::where('branch_id', auth()->user()->branch_id)
                ->where('status', 'open')
                ->first()
            : null;

        // Products count
        $productsCount = \App\Models\Product::count();

        // Low stock
        $lowStockCount = \App\Models\Product::where('track_inventory', true)
            ->whereNotNull('low_stock_threshold')
            ->whereHas('inventoryStocks', function($q) {
                $q->whereRaw('quantity <= (SELECT low_stock_threshold FROM products WHERE products.id = inventory_stocks.product_id)');
            })->count();

        // Recent sales
        $recentSales = \App\Models\Sale::today()->completed()->latest()->limit(5)->get();

        // Weekly data for chart
        $weeklyData = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayTotal = \App\Models\Sale::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('total');
            $weeklyData->push([
                'day' => $date->locale('es')->isoFormat('ddd'),
                'date' => $date->format('d M'),
                'total' => $dayTotal
            ]);
        }
    @endphp

    <div class="p-4 lg:p-6 max-w-7xl mx-auto space-y-6">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Bienvenido, {{ auth()->user()->name }}</p>
            </div>

            <div class="flex items-center gap-3">
                @if($cashRegister)
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-400 text-sm font-medium rounded-full">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        Caja Abierta
                    </span>
                @else
                    <a href="{{ route('pos.cash-register.open') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Abrir Caja
                    </a>
                @endif
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Ventas del dia --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    @if($salesChange != 0)
                        <span class="inline-flex items-center text-xs font-medium {{ $salesChange >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            <svg class="w-3 h-3 mr-0.5 {{ $salesChange < 0 ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            {{ abs(round($salesChange, 1)) }}%
                        </span>
                    @endif
                </div>
                <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($totalSales, 0) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ventas de hoy</p>
            </div>

            {{-- Transacciones --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    @if($countChange != 0)
                        <span class="inline-flex items-center text-xs font-medium {{ $countChange >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            <svg class="w-3 h-3 mr-0.5 {{ $countChange < 0 ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            {{ abs(round($countChange, 1)) }}%
                        </span>
                    @endif
                </div>
                <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">{{ $salesCount }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Transacciones</p>
            </div>

            {{-- Ticket promedio --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($avgTicket, 0) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ticket promedio</p>
            </div>

            {{-- Productos --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 bg-orange-100 dark:bg-orange-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    @if($lowStockCount > 0)
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-600 dark:text-amber-400">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ $lowStockCount }} bajo
                        </span>
                    @endif
                </div>
                <p class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">{{ $productsCount }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Productos</p>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Revenue Chart --}}
            <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Ventas de la Semana</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Ultimos 7 dias</p>
                    </div>
                    <a href="{{ route('reports.sales') }}" class="text-sm text-orange-500 hover:text-orange-600 font-medium">
                        Ver reporte
                    </a>
                </div>
                <div id="revenueChart" class="h-64"></div>
            </div>

            {{-- Cash Register Status --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Estado de Caja</h2>

                @if($cashRegister)
                    <div class="text-center py-4">
                        <div class="w-20 h-20 bg-green-100 dark:bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-green-600 dark:text-green-400 font-semibold text-lg">Caja Abierta</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Desde {{ $cashRegister->opened_at->format('H:i') }}</p>
                    </div>

                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Efectivo esperado</span>
                            <span class="font-semibold text-gray-900 dark:text-white">${{ number_format($cashRegister->calculateExpectedAmount(), 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Apertura</span>
                            <span class="font-semibold text-gray-900 dark:text-white">${{ number_format($cashRegister->opening_amount, 2) }}</span>
                        </div>
                    </div>

                    <a href="{{ route('pos.cash-register.close') }}" class="mt-4 w-full flex items-center justify-center gap-2 py-3 bg-red-500 hover:bg-red-600 text-white font-medium rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Cerrar Caja
                    </a>
                @else
                    <div class="text-center py-4">
                        <div class="w-20 h-20 bg-amber-100 dark:bg-amber-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <p class="text-amber-600 dark:text-amber-400 font-semibold text-lg">Caja Cerrada</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Abre la caja para empezar a vender</p>
                    </div>

                    <a href="{{ route('pos.cash-register.open') }}" class="mt-4 w-full flex items-center justify-center gap-2 py-3 bg-green-500 hover:bg-green-600 text-white font-medium rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Abrir Caja
                    </a>
                @endif
            </div>
        </div>

        {{-- Quick Access & Recent Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Quick Access --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Acceso Rapido</h2>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('pos.terminal') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl hover:scale-105 transition-transform">
                        <svg class="w-6 h-6 text-white mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="text-white text-sm font-medium">POS</span>
                    </a>

                    <a href="{{ route('inventory.products') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl hover:scale-105 transition-transform">
                        <svg class="w-6 h-6 text-white mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span class="text-white text-sm font-medium">Inventario</span>
                    </a>

                    <a href="{{ route('reports.sales') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl hover:scale-105 transition-transform">
                        <svg class="w-6 h-6 text-white mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span class="text-white text-sm font-medium">Reportes</span>
                    </a>

                    <a href="{{ route('chat') }}" class="flex flex-col items-center p-4 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl hover:scale-105 transition-transform">
                        <svg class="w-6 h-6 text-white mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <span class="text-white text-sm font-medium">Mika IA</span>
                    </a>
                </div>
            </div>

            {{-- Recent Transactions with Tabs --}}
            <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800" x-data="{ tab: 'sales' }">
                <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Actividad Reciente</h2>
                    <div class="flex bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
                        <button
                            @click="tab = 'sales'"
                            :class="tab === 'sales' ? 'bg-white dark:bg-gray-700 shadow-sm' : ''"
                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors"
                        >
                            Ventas
                        </button>
                        <button
                            @click="tab = 'stock'"
                            :class="tab === 'stock' ? 'bg-white dark:bg-gray-700 shadow-sm' : ''"
                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors"
                        >
                            Stock Bajo
                        </button>
                    </div>
                </div>

                {{-- Sales Tab --}}
                <div x-show="tab === 'sales'" class="p-5">
                    <div class="space-y-3">
                        @forelse($recentSales as $sale)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-green-100 dark:bg-green-500/20 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $sale->ticket_number }}</p>
                                        <p class="text-sm text-gray-500">{{ $sale->created_at->format('H:i') }} - {{ ucfirst($sale->payment_method) }}</p>
                                    </div>
                                </div>
                                <span class="font-semibold text-green-600 dark:text-green-400">${{ number_format($sale->total, 2) }}</span>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p class="text-gray-500 dark:text-gray-400">No hay ventas hoy</p>
                                <a href="{{ route('pos.terminal') }}" class="inline-block mt-3 text-sm text-orange-500 hover:text-orange-600 font-medium">
                                    Ir al POS
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Stock Tab --}}
                <div x-show="tab === 'stock'" x-cloak class="p-5">
                    @php
                        $lowStockProducts = \App\Models\Product::where('track_inventory', true)
                            ->whereNotNull('low_stock_threshold')
                            ->whereHas('inventoryStocks', function($q) {
                                $q->whereRaw('quantity <= (SELECT low_stock_threshold FROM products WHERE products.id = inventory_stocks.product_id)');
                            })
                            ->limit(5)
                            ->get();
                    @endphp

                    <div class="space-y-3">
                        @forelse($lowStockProducts as $product)
                            <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-red-100 dark:bg-red-500/20 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white truncate max-w-[200px]">{{ $product->name }}</p>
                                        <p class="text-sm text-gray-500">Min: {{ $product->low_stock_threshold }} {{ $product->unit }}</p>
                                    </div>
                                </div>
                                <span class="font-semibold text-red-600 dark:text-red-400">
                                    {{ $product->inventoryStocks->first()?->quantity ?? 0 }} {{ $product->unit }}
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 mx-auto text-green-300 dark:text-green-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-gray-500 dark:text-gray-400">Todo el inventario esta bien</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const weeklyData = @json($weeklyData);

            const options = {
                chart: {
                    type: 'area',
                    height: 256,
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif',
                },
                series: [{
                    name: 'Ventas',
                    data: weeklyData.map(d => d.total)
                }],
                xaxis: {
                    categories: weeklyData.map(d => d.day),
                    labels: {
                        style: {
                            colors: '#9ca3af',
                            fontSize: '12px'
                        }
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#9ca3af',
                            fontSize: '12px'
                        },
                        formatter: (val) => '$' + val.toLocaleString()
                    }
                },
                colors: ['#f97316'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.1,
                        stops: [0, 100]
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                dataLabels: { enabled: false },
                grid: {
                    borderColor: '#e5e7eb',
                    strokeDashArray: 4,
                    xaxis: { lines: { show: false } }
                },
                tooltip: {
                    y: {
                        formatter: (val) => '$' + val.toLocaleString()
                    }
                }
            };

            // Check dark mode
            if (document.documentElement.classList.contains('dark') ||
                window.matchMedia('(prefers-color-scheme: dark)').matches) {
                options.grid.borderColor = '#374151';
                options.chart.foreColor = '#9ca3af';
            }

            const chart = new ApexCharts(document.querySelector('#revenueChart'), options);
            chart.render();
        });
    </script>
    @endpush
</x-layouts.app>
