<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $categoryId = null;

    #[Url]
    public string $status = 'all';

    public bool $showDeleteModal = false;
    public ?int $deleteProductId = null;

    #[Computed]
    public function categories()
    {
        return Category::query()
            ->active()
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->with('category')
            ->when($this->search, fn ($q) => $q->search($this->search))
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->when($this->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($this->status === 'low_stock', fn ($q) => $q
                ->where('track_inventory', true)
                ->whereNotNull('low_stock_threshold')
                ->whereHas('inventoryStocks', function ($sub) {
                    $sub->whereRaw('quantity <= low_stock_threshold');
                })
            )
            ->orderBy('name')
            ->paginate(20);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategoryId()
    {
        $this->resetPage();
    }

    public function confirmDelete(int $productId)
    {
        $this->deleteProductId = $productId;
        $this->showDeleteModal = true;
    }

    public function deleteProduct()
    {
        if ($this->deleteProductId) {
            Product::find($this->deleteProductId)?->delete();
            $this->dispatch('notify', type: 'success', message: 'Producto eliminado');
        }

        $this->showDeleteModal = false;
        $this->deleteProductId = null;
    }

    public function toggleActive(int $productId)
    {
        $product = Product::find($productId);
        if ($product) {
            $product->update(['is_active' => !$product->is_active]);
            $this->dispatch('notify', type: 'success', message: 'Estado actualizado');
        }
    }

    public function render()
    {
        return view('livewire.inventory.product-list')
            ->layout('layouts.app');
    }
}
