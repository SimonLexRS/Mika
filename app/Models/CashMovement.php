<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'cash_register_id',
        'user_id',
        'type',
        'payment_method',
        'amount',
        'reference_type',
        'reference_id',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    const TYPE_OPENING = 'opening';
    const TYPE_SALE = 'sale';
    const TYPE_REFUND = 'refund';
    const TYPE_WITHDRAWAL = 'withdrawal';
    const TYPE_DEPOSIT = 'deposit';
    const TYPE_CLOSING = 'closing';

    /**
     * Relación con caja registradora.
     */
    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
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
            default => null,
        };

        if (!$modelClass) {
            return null;
        }

        return $modelClass::find($this->reference_id);
    }

    /**
     * Obtener descripción del tipo.
     */
    public function getTypeDescriptionAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_OPENING => 'Apertura',
            self::TYPE_SALE => 'Venta',
            self::TYPE_REFUND => 'Devolución',
            self::TYPE_WITHDRAWAL => 'Retiro',
            self::TYPE_DEPOSIT => 'Depósito',
            self::TYPE_CLOSING => 'Cierre',
            default => $this->type,
        };
    }

    /**
     * Verificar si es entrada de dinero.
     */
    public function isIncoming(): bool
    {
        return $this->amount > 0;
    }

    /**
     * Verificar si es salida de dinero.
     */
    public function isOutgoing(): bool
    {
        return $this->amount < 0;
    }
}
