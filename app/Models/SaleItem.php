<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'product_sku',
        'quantity',
        'unit',
        'unit_price',
        'cost',
        'discount',
        'tax_rate',
        'tax',
        'subtotal',
        'total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'cost' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /**
     * Boot del modelo.
     */
    protected static function booted(): void
    {
        static::creating(function ($item) {
            $item->calculateTotals();
        });

        static::updating(function ($item) {
            $item->calculateTotals();
        });
    }

    /**
     * Calcular totales del item.
     */
    public function calculateTotals(): self
    {
        $subtotal = ($this->unit_price * $this->quantity) - $this->discount;
        $tax = $subtotal * ($this->tax_rate / 100);

        $this->subtotal = max(0, $subtotal);
        $this->tax = max(0, $tax);
        $this->total = $this->subtotal + $this->tax;

        return $this;
    }

    /**
     * Relación con venta.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Relación con producto.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Obtener ganancia del item.
     */
    public function getProfitAttribute(): float
    {
        return ($this->unit_price - $this->cost) * $this->quantity - $this->discount;
    }

    /**
     * Obtener margen del item.
     */
    public function getMarginAttribute(): float
    {
        if ($this->cost <= 0) {
            return 100;
        }

        return (($this->unit_price - $this->cost) / $this->cost) * 100;
    }
}
