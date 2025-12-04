<x-layouts.app>
    <div class="flex flex-col h-full">
        {{-- Header --}}
        <header class="bg-mika-surface border-b border-mika-surface-light px-4 py-4">
            <h1 class="text-xl font-semibold text-white">Mi Perfil</h1>
        </header>

        <div class="flex-1 overflow-y-auto px-4 py-6">
            {{-- User Info --}}
            <div class="flex items-center mb-8">
                <div class="w-16 h-16 rounded-full bg-mika-primary flex items-center justify-center">
                    <span class="text-2xl font-bold text-white">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </span>
                </div>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-white">{{ auth()->user()->name }}</h2>
                    <p class="text-mika-text-secondary text-sm">{{ auth()->user()->email }}</p>
                </div>
            </div>

            {{-- Business Info --}}
            @if(auth()->user()->business_name)
            <div class="bg-mika-surface rounded-xl p-4 mb-6">
                <h3 class="text-mika-text-secondary text-sm mb-2">Negocio</h3>
                <p class="text-white font-medium">{{ auth()->user()->business_name }}</p>
            </div>
            @endif

            {{-- Quick Stats --}}
            <div class="grid grid-cols-2 gap-4 mb-8">
                @php
                    $currentMonth = now();
                    $income = auth()->user()->transactions()
                        ->income()
                        ->approved()
                        ->whereMonth('transaction_date', $currentMonth->month)
                        ->whereYear('transaction_date', $currentMonth->year)
                        ->sum('amount');
                    $expenses = auth()->user()->transactions()
                        ->expense()
                        ->approved()
                        ->whereMonth('transaction_date', $currentMonth->month)
                        ->whereYear('transaction_date', $currentMonth->year)
                        ->sum('amount');
                @endphp

                <div class="bg-mika-surface rounded-xl p-4">
                    <p class="text-mika-text-secondary text-xs mb-1">Ingresos este mes</p>
                    <p class="text-mika-success font-semibold">${{ number_format($income, 2) }}</p>
                </div>
                <div class="bg-mika-surface rounded-xl p-4">
                    <p class="text-mika-text-secondary text-xs mb-1">Gastos este mes</p>
                    <p class="text-mika-danger font-semibold">${{ number_format($expenses, 2) }}</p>
                </div>
            </div>

            {{-- Menu Items --}}
            <div class="space-y-2">
                <a href="#" class="flex items-center justify-between bg-mika-surface rounded-xl p-4 hover:bg-mika-surface-light transition-colors">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-mika-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-white">Configuración</span>
                    </div>
                    <svg class="w-5 h-5 text-mika-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <a href="#" class="flex items-center justify-between bg-mika-surface rounded-xl p-4 hover:bg-mika-surface-light transition-colors">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-mika-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        <span class="text-white">Categorías</span>
                    </div>
                    <svg class="w-5 h-5 text-mika-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-between bg-mika-surface rounded-xl p-4 hover:bg-mika-surface-light transition-colors">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-mika-danger mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span class="text-mika-danger">Cerrar sesión</span>
                        </div>
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
