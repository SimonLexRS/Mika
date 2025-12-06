@props(['collapsed' => false])

<aside
    x-data="{ collapsed: {{ $collapsed ? 'true' : 'false' }} }"
    :class="collapsed ? 'w-20' : 'w-64'"
    class="hidden lg:flex flex-col bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 transition-all duration-300"
>
    {{-- Logo --}}
    <div class="h-16 flex items-center px-6 border-b border-gray-200 dark:border-gray-800">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-pink-600 rounded-xl flex items-center justify-center">
                <span class="text-white font-bold text-xl">M</span>
            </div>
            <span x-show="!collapsed" x-transition class="text-xl font-bold bg-gradient-to-r from-orange-500 to-pink-600 bg-clip-text text-transparent">
                Mika
            </span>
        </a>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 py-6 px-3 space-y-1 overflow-y-auto">
        {{-- Dashboard --}}
        <a
            href="{{ route('dashboard') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
        >
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
            </svg>
            <span x-show="!collapsed" x-transition class="font-medium">Dashboard</span>
        </a>

        {{-- POS --}}
        <a
            href="{{ route('pos.terminal') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('pos.*') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
        >
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span x-show="!collapsed" x-transition class="font-medium">Punto de Venta</span>
        </a>

        {{-- Inventario --}}
        <a
            href="{{ route('inventory.products') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('inventory.*') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
        >
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <span x-show="!collapsed" x-transition class="font-medium">Inventario</span>
        </a>

        {{-- Reportes --}}
        <a
            href="{{ route('reports.sales') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('reports.*') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
        >
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span x-show="!collapsed" x-transition class="font-medium">Reportes</span>
        </a>

        {{-- Clientes --}}
        <a
            href="#"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800"
        >
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span x-show="!collapsed" x-transition class="font-medium">Clientes</span>
        </a>

        {{-- Asistente IA --}}
        <a
            href="{{ route('chat') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('chat') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
        >
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <span x-show="!collapsed" x-transition class="font-medium">Asistente IA</span>
        </a>

        <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-800">
            <p x-show="!collapsed" x-transition class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Configuración</p>

            {{-- Caja --}}
            <a
                href="{{ route('pos.cash-register.open') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800"
            >
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span x-show="!collapsed" x-transition class="font-medium">Caja</span>
            </a>

            {{-- Perfil --}}
            <a
                href="{{ route('profile') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all {{ request()->routeIs('profile') ? 'bg-orange-500/10 text-orange-500' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
            >
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span x-show="!collapsed" x-transition class="font-medium">Mi Perfil</span>
            </a>
        </div>
    </nav>

    {{-- User section --}}
    <div class="p-4 border-t border-gray-200 dark:border-gray-800">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
            </div>
            <div x-show="!collapsed" x-transition class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ auth()->user()->name ?? 'Usuario' }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->business_name ?? 'Mi Negocio' }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" x-show="!collapsed" x-transition>
                @csrf
                <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
