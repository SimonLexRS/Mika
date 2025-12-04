<div class="bg-mika-surface rounded-2xl rounded-tl-none overflow-hidden">
    {{-- Card Header --}}
    <div class="px-4 py-3 border-b border-mika-surface-light">
        <p class="text-white text-sm font-medium">{{ $title }}</p>
    </div>

    {{-- Card Content --}}
    <div class="px-4 py-3">
        @if(($data['type'] ?? '') === 'transaction_confirmation')
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-mika-text-secondary text-sm">Monto</span>
                    <span class="font-semibold {{ str_starts_with($data['amount'] ?? '', '-') ? 'text-mika-danger' : 'text-mika-success' }}">
                        {{ $data['amount'] ?? '' }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-mika-text-secondary text-sm">Categoría</span>
                    <span class="text-white text-sm">{{ $data['category'] ?? '' }}</span>
                </div>
                @if(!empty($data['description']))
                <div class="flex justify-between items-center">
                    <span class="text-mika-text-secondary text-sm">Descripción</span>
                    <span class="text-white text-sm truncate max-w-[150px]">{{ $data['description'] }}</span>
                </div>
                @endif
                <div class="flex justify-between items-center">
                    <span class="text-mika-text-secondary text-sm">Fecha</span>
                    <span class="text-white text-sm">{{ $data['date'] ?? '' }}</span>
                </div>
            </div>
        @endif

        @if(($data['type'] ?? '') === 'balance_summary')
            <div class="text-center py-2">
                <p class="text-mika-text-secondary text-sm">Saldo de {{ $data['month'] ?? '' }}</p>
                <p class="text-3xl font-bold {{ ($data['balance'] ?? 0) >= 0 ? 'text-mika-success' : 'text-mika-danger' }}">
                    ${{ number_format($data['balance'] ?? 0, 2) }}
                </p>
            </div>
            <div class="flex justify-around mt-3 pt-3 border-t border-mika-surface-light">
                <div class="text-center">
                    <p class="text-mika-success text-sm font-semibold">+${{ number_format($data['income'] ?? 0, 2) }}</p>
                    <p class="text-mika-text-secondary text-xs">Ingresos</p>
                </div>
                <div class="text-center">
                    <p class="text-mika-danger text-sm font-semibold">-${{ number_format($data['expenses'] ?? 0, 2) }}</p>
                    <p class="text-mika-text-secondary text-xs">Gastos</p>
                </div>
            </div>
            @if(!empty($data['top_expense_category']))
            <div class="mt-3 pt-3 border-t border-mika-surface-light text-center">
                <p class="text-mika-text-secondary text-xs">Mayor gasto en: <span class="text-white">{{ $data['top_expense_category'] }}</span></p>
            </div>
            @endif
        @endif

        @if(($data['type'] ?? '') === 'transaction_list')
            <div class="space-y-2">
                @foreach(($data['transactions'] ?? []) as $transaction)
                    <div class="flex justify-between items-center py-2 border-b border-mika-surface-light last:border-0">
                        <div>
                            <p class="text-white text-sm">{{ $transaction['description'] ?? $transaction['category'] }}</p>
                            <p class="text-mika-text-secondary text-xs">{{ $transaction['date'] }} - {{ $transaction['category'] }}</p>
                        </div>
                        <span class="{{ str_starts_with($transaction['amount'] ?? '', '-') ? 'text-mika-danger' : 'text-mika-success' }} font-semibold">
                            {{ $transaction['amount'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Card Actions --}}
    @if(!empty($data['actions']))
        <div class="px-4 py-2 border-t border-mika-surface-light flex justify-end space-x-2">
            @foreach($data['actions'] as $action)
                <button
                    wire:click="performAction('{{ $action['action'] }}', {{ $action['id'] ?? 'null' }})"
                    class="px-3 py-1 text-sm text-mika-primary hover:text-mika-primary-light transition-colors"
                >
                    {{ $action['label'] }}
                </button>
            @endforeach
        </div>
    @endif
</div>
