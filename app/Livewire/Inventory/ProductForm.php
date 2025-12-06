<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductForm extends Component
{
    use WithFileUploads;

    public ?Product $product = null;

    public string $name = '';
    public string $description = '';
    public ?int $categoryId = null;
    public string $sku = '';
    public string $barcode = '';
    public string $unit = 'pza';
    public float $cost = 0;
    public float $price = 0;
    public float $taxRate = 16.00;
    public bool $taxIncluded = true;
    public bool $trackInventory = true;
    public ?int $lowStockThreshold = null;
    public bool $isActive = true;
    public bool $isForSale = true;
    public bool $isIngredient = false;

    public $image = null;

    protected function rules()
    {
        $uniqueSkuRule = $this->product
            ? "unique:products,sku,{$this->product->id}"
            : 'unique:products,sku';

        $uniqueBarcodeRule = $this->product
            ? "unique:products,barcode,{$this->product->id}"
            : 'unique:products,barcode';

        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'categoryId' => 'nullable|exists:categories,id',
            'sku' => "nullable|string|max:50|{$uniqueSkuRule}",
            'barcode' => "nullable|string|max:50|{$uniqueBarcodeRule}",
            'unit' => 'required|string|max:20',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'taxRate' => 'required|numeric|min:0|max:100',
            'taxIncluded' => 'boolean',
            'trackInventory' => 'boolean',
            'lowStockThreshold' => 'nullable|integer|min:0',
            'isActive' => 'boolean',
            'isForSale' => 'boolean',
            'isIngredient' => 'boolean',
            'image' => 'nullable|image|max:2048',
        ];
    }

    public function mount(?Product $product = null)
    {
        if ($product && $product->exists) {
            $this->product = $product;
            $this->name = $product->name;
            $this->description = $product->description ?? '';
            $this->categoryId = $product->category_id;
            $this->sku = $product->sku ?? '';
            $this->barcode = $product->barcode ?? '';
            $this->unit = $product->unit;
            $this->cost = $product->cost;
            $this->price = $product->price;
            $this->taxRate = $product->tax_rate;
            $this->taxIncluded = $product->tax_included;
            $this->trackInventory = $product->track_inventory;
            $this->lowStockThreshold = $product->low_stock_threshold;
            $this->isActive = $product->is_active;
            $this->isForSale = $product->is_for_sale;
            $this->isIngredient = $product->is_ingredient;
        }
    }

    #[Computed]
    public function categories()
    {
        return Category::query()
            ->active()
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function profitMargin(): float
    {
        if ($this->cost <= 0) {
            return 100;
        }

        $netPrice = $this->taxIncluded
            ? $this->price / (1 + $this->taxRate / 100)
            : $this->price;

        return (($netPrice - $this->cost) / $this->cost) * 100;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'category_id' => $this->categoryId,
            'sku' => $this->sku ?: null,
            'barcode' => $this->barcode ?: null,
            'unit' => $this->unit,
            'cost' => $this->cost,
            'price' => $this->price,
            'tax_rate' => $this->taxRate,
            'tax_included' => $this->taxIncluded,
            'track_inventory' => $this->trackInventory,
            'low_stock_threshold' => $this->trackInventory ? $this->lowStockThreshold : null,
            'is_active' => $this->isActive,
            'is_for_sale' => $this->isForSale,
            'is_ingredient' => $this->isIngredient,
        ];

        if ($this->image) {
            $data['image'] = $this->image->store('products', 'public');
        }

        if ($this->product) {
            $this->product->update($data);
            $this->dispatch('notify', type: 'success', message: 'Producto actualizado');
        } else {
            $data['tenant_id'] = auth()->user()->tenant_id;
            Product::create($data);
            $this->dispatch('notify', type: 'success', message: 'Producto creado');
        }

        return redirect()->route('inventory.products');
    }

    public function render()
    {
        return view('livewire.inventory.product-form')
            ->layout('layouts.app');
    }
}
