<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'sku',
        'barcode',
        'name',
        'description',
        'unit',
        'cost',
        'price',
        'tax_rate',
        'tax_included',
        'track_inventory',
        'low_stock_threshold',
        'image',
        'images',
        'variants',
        'attributes',
        'is_active',
        'is_for_sale',
        'is_ingredient',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_included' => 'boolean',
            'track_inventory' => 'boolean',
            'low_stock_threshold' => 'integer',
            'images' => 'array',
            'variants' => 'array',
            'attributes' => 'array',
            'is_active' => 'boolean',
            'is_for_sale' => 'boolean',
            'is_ingredient' => 'boolean',
        ];
    }

    /**
     * Relación con categoría.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relación con stock de inventario.
     */
    public function inventoryStocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }

    /**
     * Relación con movimientos de inventario.
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Scope para productos activos.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para productos en venta.
     */
    public function scopeForSale($query)
    {
        return $query->where('is_for_sale', true);
    }

    /**
     * Scope para buscar por nombre, SKU o código de barras.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'ilike', "%{$term}%")
              ->orWhere('sku', 'ilike', "%{$term}%")
              ->orWhere('barcode', $term);
        });
    }

    /**
     * Obtener stock en una sucursal específica.
     */
    public function getStockInBranch(int $branchId): float
    {
        $stock = $this->inventoryStocks()
            ->where('branch_id', $branchId)
            ->first();

        return $stock ? $stock->available : 0;
    }

    /**
     * Obtener stock total.
     */
    public function getTotalStockAttribute(): float
    {
        return $this->inventoryStocks->sum('available');
    }

    /**
     * Verificar si tiene stock bajo.
     */
    public function hasLowStock(int $branchId = null): bool
    {
        if (!$this->track_inventory || !$this->low_stock_threshold) {
            return false;
        }

        $stock = $branchId
            ? $this->getStockInBranch($branchId)
            : $this->total_stock;

        return $stock <= $this->low_stock_threshold;
    }

    /**
     * Calcular precio con impuesto.
     */
    public function getPriceWithTaxAttribute(): float
    {
        if ($this->tax_included) {
            return $this->price;
        }

        return $this->price * (1 + $this->tax_rate / 100);
    }

    /**
     * Calcular precio sin impuesto.
     */
    public function getPriceWithoutTaxAttribute(): float
    {
        if (!$this->tax_included) {
            return $this->price;
        }

        return $this->price / (1 + $this->tax_rate / 100);
    }

    /**
     * Calcular margen de ganancia.
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->cost <= 0) {
            return 100;
        }

        $netPrice = $this->price_without_tax;
        return (($netPrice - $this->cost) / $this->cost) * 100;
    }
}
