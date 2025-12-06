<div class="p-4 max-w-3xl mx-auto">
    <div class="bg-mika-dark rounded-2xl overflow-hidden">
        {{-- Header --}}
        <div class="p-6 border-b border-gray-800">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">
                    {{ $product ? 'Editar Producto' : 'Nuevo Producto' }}
                </h1>
                <a href="{{ route('inventory.products') }}" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            </div>
        </div>

        <form wire:submit="save" class="p-6 space-y-6">
            {{-- Información básica --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-400 mb-2">Nombre del producto *</label>
                    <input
                        type="text"
                        wire:model="name"
                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white"
                        placeholder="Ej: Hamburguesa clásica"
                    >
                    @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-400 mb-2">Descripción</label>
                    <textarea
                        wire:model="description"
                        rows="2"
                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white resize-none"
                        placeholder="Descripción del producto..."
                    ></textarea>
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-2">Categoría</label>
                    <select
                        wire:model="categoryId"
                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white"
                    >
                        <option value="">Sin categoría</option>
                        @foreach($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-2">Unidad de medida</label>
                    <select
                        wire:model="unit"
                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white"
                    >
                        <option value="pza">Pieza (pza)</option>
                        <option value="kg">Kilogramo (kg)</option>
                        <option value="g">Gramo (g)</option>
                        <option value="lt">Litro (lt)</option>
                        <option value="ml">Mililitro (ml)</option>
                        <option value="mt">Metro (mt)</option>
                        <option value="srv">Servicio (srv)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-2">SKU</label>
                    <input
                        type="text"
                        wire:model="sku"
                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white"
                        placeholder="Código interno"
                    >
                    @error('sku') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-2">Código de barras</label>
                    <input
                        type="text"
                        wire:model="barcode"
                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white"
                        placeholder="UPC / EAN"
                    >
                    @error('barcode') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Precios --}}
            <div class="border-t border-gray-800 pt-6">
                <h2 class="font-semibold mb-4">Precios</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Costo *</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">$</span>
                            <input
                                type="number"
                                wire:model.live="cost"
                                step="0.01"
                                min="0"
                                class="w-full px-4 py-3 pl-8 bg-gray-800 border border-gray-700 rounded-xl text-white"
                            >
                        </div>
                        @error('cost') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Precio de venta *</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">$</span>
                            <input
                                type="number"
                                wire:model.live="price"
                                step="0.01"
                                min="0"
                                class="w-full px-4 py-3 pl-8 bg-gray-800 border border-gray-700 rounded-xl text-white"
                            >
                        </div>
                        @error('price') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Margen</label>
                        <div class="px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl {{ $this->profitMargin >= 20 ? 'text-green-400' : ($this->profitMargin >= 0 ? 'text-yellow-400' : 'text-red-400') }}">
                            {{ number_format($this->profitMargin, 1) }}%
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Tasa de impuesto (%)</label>
                        <input
                            type="number"
                            wire:model="taxRate"
                            step="0.01"
                            min="0"
                            max="100"
                            class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white"
                        >
                    </div>

                    <div class="flex items-center">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="taxIncluded" class="w-5 h-5 rounded bg-gray-800 border-gray-700 text-green-600">
                            <span>IVA incluido en precio</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Inventario --}}
            <div class="border-t border-gray-800 pt-6">
                <h2 class="font-semibold mb-4">Inventario</h2>
                <div class="space-y-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model.live="trackInventory" class="w-5 h-5 rounded bg-gray-800 border-gray-700 text-green-600">
                        <span>Controlar inventario</span>
                    </label>

                    @if($trackInventory)
                        <div class="w-48">
                            <label class="block text-sm text-gray-400 mb-2">Alerta de stock bajo</label>
                            <input
                                type="number"
                                wire:model="lowStockThreshold"
                                min="0"
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white"
                                placeholder="Ej: 10"
                            >
                        </div>
                    @endif
                </div>
            </div>

            {{-- Opciones --}}
            <div class="border-t border-gray-800 pt-6">
                <h2 class="font-semibold mb-4">Opciones</h2>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="isActive" class="w-5 h-5 rounded bg-gray-800 border-gray-700 text-green-600">
                        <span>Producto activo</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="isForSale" class="w-5 h-5 rounded bg-gray-800 border-gray-700 text-green-600">
                        <span>Disponible para venta</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="isIngredient" class="w-5 h-5 rounded bg-gray-800 border-gray-700 text-green-600">
                        <span>Es ingrediente (para recetas)</span>
                    </label>
                </div>
            </div>

            {{-- Imagen --}}
            <div class="border-t border-gray-800 pt-6">
                <h2 class="font-semibold mb-4">Imagen</h2>
                <div class="flex items-center gap-4">
                    @if($product && $product->image)
                        <img src="{{ Storage::url($product->image) }}" class="w-24 h-24 rounded-xl object-cover">
                    @endif

                    <label class="flex-1 border-2 border-dashed border-gray-700 rounded-xl p-6 text-center cursor-pointer hover:border-blue-500 transition-colors">
                        <input type="file" wire:model="image" class="hidden" accept="image/*">
                        <svg class="w-8 h-8 mx-auto text-gray-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-gray-400 text-sm">Haz clic para subir imagen</p>
                    </label>
                </div>
                @error('image') <p class="text-red-400 text-sm mt-2">{{ $message }}</p> @enderror
            </div>

            {{-- Botones --}}
            <div class="flex gap-3 pt-4">
                <a href="{{ route('inventory.products') }}" class="flex-1 py-4 bg-gray-700 hover:bg-gray-600 rounded-xl font-semibold text-center">
                    Cancelar
                </a>
                <button type="submit" class="flex-1 py-4 bg-green-600 hover:bg-green-700 rounded-xl font-semibold">
                    {{ $product ? 'Guardar Cambios' : 'Crear Producto' }}
                </button>
            </div>
        </form>
    </div>
</div>
