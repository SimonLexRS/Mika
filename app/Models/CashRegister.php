<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashRegister extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'opened_by',
        'closed_by',
        'opening_amount',
        'expected_amount',
        'actual_amount',
        'difference',
        'cash_sales',
        'card_sales',
        'transfer_sales',
        'other_sales',
        'withdrawals',
        'deposits',
        'total_transactions',
        'notes',
        'closing_notes',
        'status',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'opening_amount' => 'decimal:2',
            'expected_amount' => 'decimal:2',
            'actual_amount' => 'decimal:2',
            'difference' => 'decimal:2',
            'cash_sales' => 'decimal:2',
            'card_sales' => 'decimal:2',
            'transfer_sales' => 'decimal:2',
            'other_sales' => 'decimal:2',
            'withdrawals' => 'decimal:2',
            'deposits' => 'decimal:2',
            'total_transactions' => 'integer',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    const STATUS_OPEN = 'open';
    const STATUS_CLOSED = 'closed';

    /**
     * Relación con sucursal.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relación con usuario que abrió.
     */
    public function openedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    /**
     * Relación con usuario que cerró.
     */
    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Relación con movimientos de caja.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    /**
     * Relación con ventas.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Verificar si está abierta.
     */
    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    /**
     * Calcular el monto esperado.
     */
    public function calculateExpectedAmount(): float
    {
        return $this->opening_amount
            + $this->cash_sales
            + $this->deposits
            - $this->withdrawals;
    }

    /**
     * Registrar venta.
     */
    public function recordSale(Sale $sale): void
    {
        $this->increment('total_transactions');

        match ($sale->payment_method) {
            'cash' => $this->increment('cash_sales', $sale->total),
            'card' => $this->increment('card_sales', $sale->total),
            'transfer' => $this->increment('transfer_sales', $sale->total),
            default => $this->increment('other_sales', $sale->total),
        };

        $this->update(['expected_amount' => $this->calculateExpectedAmount()]);
    }

    /**
     * Registrar retiro.
     */
    public function recordWithdrawal(float $amount, User $user, string $description = null): CashMovement
    {
        $this->increment('withdrawals', $amount);
        $this->update(['expected_amount' => $this->calculateExpectedAmount()]);

        return $this->movements()->create([
            'tenant_id' => $this->tenant_id,
            'user_id' => $user->id,
            'type' => 'withdrawal',
            'payment_method' => 'cash',
            'amount' => -$amount,
            'description' => $description,
        ]);
    }

    /**
     * Registrar depósito.
     */
    public function recordDeposit(float $amount, User $user, string $description = null): CashMovement
    {
        $this->increment('deposits', $amount);
        $this->update(['expected_amount' => $this->calculateExpectedAmount()]);

        return $this->movements()->create([
            'tenant_id' => $this->tenant_id,
            'user_id' => $user->id,
            'type' => 'deposit',
            'payment_method' => 'cash',
            'amount' => $amount,
            'description' => $description,
        ]);
    }

    /**
     * Cerrar caja.
     */
    public function close(User $user, float $actualAmount, string $notes = null): bool
    {
        $expectedAmount = $this->calculateExpectedAmount();

        $this->update([
            'closed_by' => $user->id,
            'expected_amount' => $expectedAmount,
            'actual_amount' => $actualAmount,
            'difference' => $actualAmount - $expectedAmount,
            'closing_notes' => $notes,
            'status' => self::STATUS_CLOSED,
            'closed_at' => now(),
        ]);

        return true;
    }

    /**
     * Obtener total de ventas.
     */
    public function getTotalSalesAttribute(): float
    {
        return $this->cash_sales
            + $this->card_sales
            + $this->transfer_sales
            + $this->other_sales;
    }
}
