<header class="h-16 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between px-4 lg:px-6">
    {{-- Left side - Mobile menu & Search --}}
    <div class="flex items-center gap-4">
        {{-- Mobile menu button --}}
        <button
            x-data
            @click="$dispatch('toggle-mobile-menu')"
            class="lg:hidden p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        {{-- Search --}}
        <div class="hidden sm:flex items-center">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    type="text"
                    placeholder="Buscar productos, ventas..."
                    class="w-64 pl-10 pr-4 py-2 bg-gray-100 dark:bg-gray-800 border-0 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:bg-white dark:focus:bg-gray-700"
                >
            </div>
        </div>
    </div>

    {{-- Right side - Branch selector, notifications, user --}}
    <div class="flex items-center gap-2 sm:gap-4">
        {{-- Branch Selector --}}
        @php
            $branches = auth()->user()->tenant?->branches ?? collect();
            $currentBranch = $branches->firstWhere('id', auth()->user()->branch_id);
        @endphp

        @if($branches->count() > 0)
            <div x-data="{ open: false }" class="relative">
                <button
                    @click="open = !open"
                    class="flex items-center gap-2 px-3 py-2 text-sm bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                >
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="hidden sm:inline font-medium truncate max-w-[120px]">
                        {{ $currentBranch?->name ?? 'Seleccionar sucursal' }}
                    </span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div
                    x-show="open"
                    @click.away="open = false"
                    x-transition
                    class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50"
                >
                    @foreach($branches as $branch)
                        <a
                            href="{{ route('switch.branch', $branch->id) }}"
                            class="flex items-center gap-3 px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 {{ $branch->id === auth()->user()->branch_id ? 'text-orange-500 bg-orange-50 dark:bg-orange-500/10' : 'text-gray-700 dark:text-gray-300' }}"
                        >
                            @if($branch->id === auth()->user()->branch_id)
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            @else
                                <span class="w-4"></span>
                            @endif
                            {{ $branch->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Notifications --}}
        <div x-data="{ open: false }" class="relative">
            <button
                @click="open = !open"
                class="relative p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                {{-- Notification badge --}}
                @php
                    $lowStockCount = \App\Models\Product::where('track_inventory', true)
                        ->whereNotNull('low_stock_threshold')
                        ->whereHas('inventoryStocks', function($q) {
                            $q->whereRaw('quantity <= (SELECT low_stock_threshold FROM products WHERE products.id = inventory_stocks.product_id)');
                        })->count();
                @endphp
                @if($lowStockCount > 0)
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                @endif
            </button>

            <div
                x-show="open"
                @click.away="open = false"
                x-transition
                class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-50"
            >
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Notificaciones</h3>
                </div>
                <div class="max-h-96 overflow-y-auto">
                    @if($lowStockCount > 0)
                        <a href="{{ route('inventory.products') }}" class="flex items-start gap-3 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <div class="w-10 h-10 bg-red-100 dark:bg-red-500/20 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Stock bajo</p>
                                <p class="text-xs text-gray-500">{{ $lowStockCount }} productos con inventario bajo</p>
                            </div>
                        </a>
                    @else
                        <div class="p-8 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p class="text-sm">No hay notificaciones</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Date --}}
        <div class="hidden md:flex items-center gap-2 px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                {{ now()->locale('es')->isoFormat('ddd, D MMM') }}
            </span>
        </div>

        {{-- User avatar (mobile) --}}
        <a href="{{ route('profile') }}" class="lg:hidden">
            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-orange-500 to-pink-600 flex items-center justify-center text-white font-semibold text-sm">
                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
            </div>
        </a>
    </div>
</header>
