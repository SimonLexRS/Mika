<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'cash_register_id',
        'customer_id',
        'user_id',
        'ticket_number',
        'type',
        'status',
        'subtotal',
        'tax',
        'discount',
        'discount_type',
        'total',
        'paid',
        'change',
        'payment_method',
        'payment_details',
        'notes',
        'is_invoiced',
        'invoice_uuid',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid' => 'decimal:2',
            'change' => 'decimal:2',
            'payment_details' => 'array',
            'is_invoiced' => 'boolean',
        ];
    }

    const TYPE_SALE = 'sale';
    const TYPE_QUOTE = 'quote';
    const TYPE_ORDER = 'order';

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    const PAYMENT_CASH = 'cash';
    const PAYMENT_CARD = 'card';
    const PAYMENT_TRANSFER = 'transfer';
    const PAYMENT_MIXED = 'mixed';

    /**
     * Boot del modelo.
     */
    protected static function booted(): void
    {
        static::creating(function ($sale) {
            if (empty($sale->ticket_number)) {
                $sale->ticket_number = $sale->generateTicketNumber();
            }
        });
    }

    /**
     * Generar número de ticket.
     */
    public function generateTicketNumber(): string
    {
        $prefix = 'T';
        $date = now()->format('ymd');
        $branchCode = str_pad($this->branch_id ?? 1, 2, '0', STR_PAD_LEFT);

        $lastTicket = static::where('tenant_id', $this->tenant_id)
            ->where('ticket_number', 'like', "{$prefix}{$date}{$branchCode}%")
            ->orderBy('ticket_number', 'desc')
            ->first();

        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket->ticket_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . $branchCode . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relación con sucursal.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relación con caja registradora.
     */
    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    /**
     * Relación con cliente.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relación con usuario (cajero).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con items de la venta.
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Verificar si está completada.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Verificar si está pendiente.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verificar si fue cancelada.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Recalcular totales.
     */
    public function recalculateTotals(): self
    {
        $subtotal = $this->items->sum('subtotal');
        $tax = $this->items->sum('tax');
        $total = $subtotal + $tax - $this->discount;

        $this->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => max(0, $total),
        ]);

        return $this;
    }

    /**
     * Completar venta.
     */
    public function complete(): self
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
        ]);

        // Registrar en caja si existe
        if ($this->cashRegister) {
            $this->cashRegister->recordSale($this);
        }

        return $this;
    }

    /**
     * Cancelar venta.
     */
    public function cancel(): self
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);

        return $this;
    }

    /**
     * Obtener cantidad de items.
     */
    public function getItemsCountAttribute(): int
    {
        return $this->items->count();
    }

    /**
     * Obtener cantidad total de productos.
     */
    public function getTotalQuantityAttribute(): float
    {
        return $this->items->sum('quantity');
    }

    /**
     * Obtener costo total.
     */
    public function getTotalCostAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->cost * $item->quantity;
        });
    }

    /**
     * Obtener ganancia.
     */
    public function getProfitAttribute(): float
    {
        return $this->total - $this->total_cost;
    }

    /**
     * Scope para ventas completadas.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope para ventas del día.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope para ventas de esta semana.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    /**
     * Scope para ventas de este mes.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }
}
