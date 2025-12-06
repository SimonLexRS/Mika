<div class="p-4 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Productos</h1>
            <p class="text-gray-400">Gestiona tu inventario de productos</p>
        </div>
        <a href="{{ route('inventory.products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 rounded-xl font-semibold">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo Producto
        </a>
    </div>

    {{-- Filtros --}}
    <div class="bg-mika-dark rounded-xl p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-4">
            {{-- Búsqueda --}}
            <div class="flex-1">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por nombre, SKU o código..."
                    class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white"
                >
            </div>

            {{-- Categoría --}}
            <div class="w-full md:w-48">
                <select
                    wire:model.live="categoryId"
                    class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white"
                >
                    <option value="">Todas las categorías</option>
                    @foreach($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Estado --}}
            <div class="w-full md:w-40">
                <select
                    wire:model.live="status"
                    class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white"
                >
                    <option value="all">Todos</option>
                    <option value="active">Activos</option>
                    <option value="inactive">Inactivos</option>
                    <option value="low_stock">Stock bajo</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Lista de productos --}}
    <div class="bg-mika-dark rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-400">Producto</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-400">SKU</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-400">Categoría</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-400">Costo</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-400">Precio</th>
                        <th class="px-4 py-3 text-center text-sm font-medium text-gray-400">Estado</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-400">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($this->products as $product)
                        <tr class="hover:bg-gray-800/50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if($product->image)
                                        <img src="{{ Storage::url($product->image) }}" class="w-10 h-10 rounded-lg object-cover">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-gray-700 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium">{{ $product->name }}</p>
                                        @if($product->barcode)
                                            <p class="text-xs text-gray-500">{{ $product->barcode }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-400">{{ $product->sku ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ $product->category?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right text-gray-400">${{ number_format($product->cost, 2) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-green-400">${{ number_format($product->price, 2) }}</td>
                            <td class="px-4 py-3 text-center">
                                <button
                                    wire:click="toggleActive({{ $product->id }})"
                                    class="px-2 py-1 rounded-full text-xs {{ $product->is_active ? 'bg-green-600/20 text-green-400' : 'bg-gray-600/20 text-gray-400' }}"
                                >
                                    {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('inventory.products.edit', $product) }}" class="p-2 text-gray-400 hover:text-blue-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <button wire:click="confirmDelete({{ $product->id }})" class="p-2 text-gray-400 hover:text-red-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                No se encontraron productos
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="p-4 border-t border-gray-800">
            {{ $this->products->links() }}
        </div>
    </div>

    {{-- Modal de confirmación de eliminación --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
            <div class="bg-mika-dark rounded-2xl w-full max-w-sm p-6">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-red-600/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold">¿Eliminar producto?</h3>
                    <p class="text-gray-400 mt-2">Esta acción no se puede deshacer.</p>
                </div>

                <div class="flex gap-3">
                    <button wire:click="$set('showDeleteModal', false)" class="flex-1 py-3 bg-gray-700 hover:bg-gray-600 rounded-xl font-semibold">
                        Cancelar
                    </button>
                    <button wire:click="deleteProduct" class="flex-1 py-3 bg-red-600 hover:bg-red-700 rounded-xl font-semibold">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
