<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'branch_id',
        'user_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'cost',
        'reference_type',
        'reference_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'quantity_before' => 'decimal:3',
            'quantity_after' => 'decimal:3',
            'cost' => 'decimal:2',
        ];
    }

    const TYPE_IN = 'in';
    const TYPE_OUT = 'out';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_TRANSFER_IN = 'transfer_in';
    const TYPE_TRANSFER_OUT = 'transfer_out';

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
     * Relación con usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener el modelo de referencia.
     */
    public function reference()
    {
        if (!$this->reference_type || !$this->reference_id) {
            return null;
        }

        $modelClass = match ($this->reference_type) {
            'sale' => Sale::class,
            'purchase' => PurchaseOrder::class,
            default => null,
        };

        if (!$modelClass) {
            return null;
        }

        return $modelClass::find($this->reference_id);
    }

    /**
     * Scope para entradas.
     */
    public function scopeIncoming($query)
    {
        return $query->whereIn('type', [self::TYPE_IN, self::TYPE_TRANSFER_IN]);
    }

    /**
     * Scope para salidas.
     */
    public function scopeOutgoing($query)
    {
        return $query->whereIn('type', [self::TYPE_OUT, self::TYPE_TRANSFER_OUT]);
    }

    /**
     * Obtener descripción del tipo.
     */
    public function getTypeDescriptionAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_IN => 'Entrada',
            self::TYPE_OUT => 'Salida',
            self::TYPE_ADJUSTMENT => 'Ajuste',
            self::TYPE_TRANSFER_IN => 'Transferencia entrada',
            self::TYPE_TRANSFER_OUT => 'Transferencia salida',
            default => $this->type,
        };
    }
}
