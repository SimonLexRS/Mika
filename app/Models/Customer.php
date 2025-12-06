<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'tax_id',
        'tax_name',
        'tax_regime',
        'tax_zip_code',
        'address',
        'city',
        'state',
        'postal_code',
        'loyalty_points',
        'credit_limit',
        'balance',
        'birthday',
        'notes',
        'metadata',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'loyalty_points' => 'integer',
            'credit_limit' => 'decimal:2',
            'balance' => 'decimal:2',
            'birthday' => 'date',
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relación con ventas.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Scope para clientes activos.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para buscar por nombre, teléfono o email.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'ilike', "%{$term}%")
              ->orWhere('phone', 'ilike', "%{$term}%")
              ->orWhere('email', 'ilike', "%{$term}%");
        });
    }

    /**
     * Obtener total de compras.
     */
    public function getTotalPurchasesAttribute(): float
    {
        return $this->sales()
            ->where('status', 'completed')
            ->sum('total');
    }

    /**
     * Obtener número de compras.
     */
    public function getPurchaseCountAttribute(): int
    {
        return $this->sales()
            ->where('status', 'completed')
            ->count();
    }

    /**
     * Agregar puntos de lealtad.
     */
    public function addLoyaltyPoints(int $points): self
    {
        $this->increment('loyalty_points', $points);
        return $this;
    }

    /**
     * Usar puntos de lealtad.
     */
    public function useLoyaltyPoints(int $points): bool
    {
        if ($this->loyalty_points < $points) {
            return false;
        }

        $this->decrement('loyalty_points', $points);
        return true;
    }

    /**
     * Verificar si puede facturar.
     */
    public function canInvoice(): bool
    {
        return !empty($this->tax_id) && !empty($this->tax_name);
    }

    /**
     * Obtener nombre para mostrar.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->tax_name ?: $this->name;
    }
}
