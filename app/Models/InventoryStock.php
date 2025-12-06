<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStock extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'branch_id',
        'quantity',
        'reserved',
        'min_stock',
        'max_stock',
        'location',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'reserved' => 'decimal:3',
            'min_stock' => 'decimal:3',
            'max_stock' => 'decimal:3',
        ];
    }

    /**
     * Relación con producto.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relación con sucursal.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Obtener cantidad disponible.
     */
    public function getAvailableAttribute(): float
    {
        return max(0, $this->quantity - $this->reserved);
    }

    /**
     * Verificar si tiene stock bajo.
     */
    public function hasLowStock(): bool
    {
        if (!$this->min_stock) {
            return false;
        }

        return $this->available <= $this->min_stock;
    }

    /**
     * Verificar si tiene sobrestock.
     */
    public function hasOverstock(): bool
    {
        if (!$this->max_stock) {
            return false;
        }

        return $this->quantity > $this->max_stock;
    }

    /**
     * Agregar stock.
     */
    public function addStock(float $quantity): self
    {
        $this->increment('quantity', $quantity);
        return $this;
    }

    /**
     * Reducir stock.
     */
    public function reduceStock(float $quantity): bool
    {
        if ($this->available < $quantity) {
            return false;
        }

        $this->decrement('quantity', $quantity);
        return true;
    }

    /**
     * Reservar stock.
     */
    public function reserveStock(float $quantity): bool
    {
        if ($this->available < $quantity) {
            return false;
        }

        $this->increment('reserved', $quantity);
        return true;
    }

    /**
     * Liberar stock reservado.
     */
    public function releaseReserved(float $quantity): self
    {
        $this->decrement('reserved', min($quantity, $this->reserved));
        return $this;
    }

    /**
     * Confirmar stock reservado (convierte reserva en venta).
     */
    public function confirmReserved(float $quantity): bool
    {
        $actualQuantity = min($quantity, $this->reserved);

        $this->decrement('quantity', $actualQuantity);
        $this->decrement('reserved', $actualQuantity);

        return true;
    }
}
