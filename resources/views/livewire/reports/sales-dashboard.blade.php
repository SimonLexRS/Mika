<div class="p-4 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Reporte de Ventas</h1>
            <p class="text-gray-400">Análisis de ventas y rendimiento</p>
        </div>

        {{-- Filtro de período --}}
        <div class="flex items-center gap-2">
            @foreach(['today' => 'Hoy', 'yesterday' => 'Ayer', 'week' => 'Semana', 'month' => 'Mes'] as $value => $label)
                <button
                    wire:click="$set('period', '{{ $value }}')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $period === $value ? 'bg-blue-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Tarjetas de resumen --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-mika-dark p-6 rounded-xl">
            <p class="text-sm text-gray-400">Total Ventas</p>
            <p class="text-3xl font-bold text-green-400">${{ number_format($this->salesSummary['total'], 2) }}</p>
            <p class="text-sm text-gray-500 mt-1">{{ $this->salesSummary['count'] }} transacciones</p>
        </div>

        <div class="bg-mika-dark p-6 rounded-xl">
            <p class="text-sm text-gray-400">Ticket Promedio</p>
            <p class="text-3xl font-bold text-blue-400">${{ number_format($this->salesSummary['average'], 2) }}</p>
        </div>

        <div class="bg-mika-dark p-6 rounded-xl">
            <p class="text-sm text-gray-400">Impuestos</p>
            <p class="text-3xl font-bold text-purple-400">${{ number_format($this->salesSummary['tax'], 2) }}</p>
        </div>

        <div class="bg-mika-dark p-6 rounded-xl">
            <p class="text-sm text-gray-400">Descuentos</p>
            <p class="text-3xl font-bold text-red-400">${{ number_format($this->salesSummary['discount'], 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Ventas por método de pago --}}
        <div class="bg-mika-dark rounded-xl p-6">
            <h2 class="font-semibold mb-4">Por método de pago</h2>
            <div class="space-y-3">
                @php
                    $paymentMethods = [
                        'cash' => ['label' => 'Efectivo', 'color' => 'green', 'icon' => '💵'],
                        'card' => ['label' => 'Tarjeta', 'color' => 'blue', 'icon' => '💳'],
                        'transfer' => ['label' => 'Transferencia', 'color' => 'purple', 'icon' => '📱'],
                        'mixed' => ['label' => 'Mixto', 'color' => 'yellow', 'icon' => '🔄'],
                    ];
                @endphp

                @foreach($paymentMethods as $method => $info)
                    @php
                        $data = $this->salesByPaymentMethod[$method] ?? ['count' => 0, 'total' => 0];
                        $percentage = $this->salesSummary['total'] > 0 ? ($data['total'] / $this->salesSummary['total']) * 100 : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="flex items-center gap-2">
                                <span>{{ $info['icon'] }}</span>
                                <span>{{ $info['label'] }}</span>
                            </span>
                            <span class="font-semibold">${{ number_format($data['total'], 2) }}</span>
                        </div>
                        <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full bg-{{ $info['color'] }}-500 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ $data['count'] }} transacciones</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Productos más vendidos --}}
        <div class="bg-mika-dark rounded-xl p-6">
            <h2 class="font-semibold mb-4">Productos más vendidos</h2>
            <div class="space-y-3">
                @forelse($this->topProducts as $index => $product)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 flex items-center justify-center rounded-full bg-gray-800 text-sm">
                                {{ $index + 1 }}
                            </span>
                            <div>
                                <p class="font-medium">{{ $product->product_name }}</p>
                                <p class="text-xs text-gray-500">{{ number_format($product->quantity) }} unidades</p>
                            </div>
                        </div>
                        <span class="font-semibold text-green-400">${{ number_format($product->total, 2) }}</span>
                    </div>
                @empty
                    <p class="text-center text-gray-500 py-4">No hay ventas en este período</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Ventas por hora --}}
    <div class="bg-mika-dark rounded-xl p-6 mb-6">
        <h2 class="font-semibold mb-4">Ventas por hora</h2>
        <div class="flex items-end gap-1 h-32">
            @php
                $maxSales = max(array_column($this->salesByHour, 'total')) ?: 1;
            @endphp
            @foreach($this->salesByHour as $hour => $data)
                @php
                    $height = ($data['total'] / $maxSales) * 100;
                @endphp
                <div class="flex-1 flex flex-col items-center group relative">
                    <div
                        class="w-full bg-blue-600 rounded-t transition-all hover:bg-blue-500"
                        style="height: {{ max($height, 2) }}%"
                    ></div>
                    @if($hour % 3 === 0)
                        <span class="text-xs text-gray-500 mt-1">{{ sprintf('%02d', $hour) }}</span>
                    @endif

                    {{-- Tooltip --}}
                    <div class="absolute bottom-full mb-2 hidden group-hover:block z-10">
                        <div class="bg-gray-900 text-white text-xs rounded px-2 py-1 whitespace-nowrap">
                            {{ sprintf('%02d:00', $hour) }} - ${{ number_format($data['total'], 2) }} ({{ $data['count'] }})
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Últimas ventas --}}
    <div class="bg-mika-dark rounded-xl overflow-hidden">
        <div class="p-6 border-b border-gray-800">
            <h2 class="font-semibold">Últimas ventas</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-400">Ticket</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-400">Fecha</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-400">Cliente</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-400">Cajero</th>
                        <th class="px-4 py-3 text-center text-sm font-medium text-gray-400">Pago</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-400">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($this->recentSales as $sale)
                        <tr class="hover:bg-gray-800/50">
                            <td class="px-4 py-3 font-mono text-blue-400">{{ $sale->ticket_number }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ $sale->created_at->format('d/m H:i') }}</td>
                            <td class="px-4 py-3">{{ $sale->customer?->name ?? 'Cliente general' }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ $sale->user?->name }}</td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $methodIcon = match($sale->payment_method) {
                                        'cash' => '💵',
                                        'card' => '💳',
                                        'transfer' => '📱',
                                        default => '🔄'
                                    };
                                @endphp
                                {{ $methodIcon }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-green-400">
                                ${{ number_format($sale->total, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                No hay ventas en este período
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
