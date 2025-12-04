<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'category',
        'transaction_date',
        'description',
        'receipt_image_path',
        'status',
        'meta_data',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'type' => TransactionType::class,
            'status' => TransactionStatus::class,
            'transaction_date' => 'date',
            'meta_data' => 'array',
        ];
    }

    /**
     * Relación con usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para filtrar solo ingresos.
     */
    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', TransactionType::Income);
    }

    /**
     * Scope para filtrar solo gastos.
     */
    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', TransactionType::Expense);
    }

    /**
     * Scope para filtrar solo aprobados.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', TransactionStatus::Approved);
    }

    /**
     * Scope para filtrar por período.
     */
    public function scopeInPeriod(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope para filtrar por mes actual.
     */
    public function scopeCurrentMonth(Builder $query): Builder
    {
        return $query->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year);
    }

    /**
     * Scope para filtrar por categoría.
     */
    public function scopeInCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Obtener el monto formateado.
     */
    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->type === TransactionType::Expense ? '-' : '+';
        return $prefix . '$' . number_format($this->amount, 2);
    }

    /**
     * Obtener la etiqueta de categoría.
     */
    public function getCategoryLabelAttribute(): string
    {
        $categories = config('mika.default_categories.' . $this->type->value, []);
        return $categories[$this->category] ?? ucfirst($this->category);
    }

    /**
     * Verificar si es un gasto.
     */
    public function isExpense(): bool
    {
        return $this->type === TransactionType::Expense;
    }

    /**
     * Verificar si es un ingreso.
     */
    public function isIncome(): bool
    {
        return $this->type === TransactionType::Income;
    }
}
