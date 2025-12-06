<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'business_name',
        'business_type',
        'currency',
        'preferences',
        'categories',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
            'categories' => 'array',
            'is_active' => 'boolean',
        ];
    }

    const ROLE_OWNER = 'owner';
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_CASHIER = 'cashier';
    const ROLE_USER = 'user';

    /**
     * Relación con tenant.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Relación con sucursal.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relación con transacciones.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Relación con conversaciones.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Obtener la conversación activa del usuario.
     */
    public function activeConversation(): ?Conversation
    {
        return $this->conversations()
            ->where('is_active', true)
            ->latest('last_activity_at')
            ->first();
    }

    /**
     * Obtener o crear una conversación activa.
     */
    public function getOrCreateActiveConversation(): Conversation
    {
        $conversation = $this->activeConversation();

        if (!$conversation) {
            $conversation = $this->conversations()->create([
                'is_active' => true,
                'last_activity_at' => now(),
            ]);
        }

        return $conversation;
    }

    /**
     * Obtener categorías del usuario o las predeterminadas.
     */
    public function getCategories(string $type = 'expense'): array
    {
        if ($this->categories && isset($this->categories[$type])) {
            return $this->categories[$type];
        }

        return config("mika.default_categories.{$type}", []);
    }

    /**
     * Obtener el nombre de visualización.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->business_name ?? $this->name;
    }

    /**
     * Verificar si es propietario.
     */
    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    /**
     * Verificar si es administrador.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_ADMIN]);
    }

    /**
     * Verificar si es gerente o superior.
     */
    public function isManager(): bool
    {
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_ADMIN, self::ROLE_MANAGER]);
    }

    /**
     * Verificar si es cajero o superior.
     */
    public function isCashier(): bool
    {
        return in_array($this->role, [
            self::ROLE_OWNER,
            self::ROLE_ADMIN,
            self::ROLE_MANAGER,
            self::ROLE_CASHIER,
        ]);
    }

    /**
     * Verificar si puede acceder al POS.
     */
    public function canAccessPos(): bool
    {
        return $this->isCashier() && $this->is_active;
    }

    /**
     * Verificar si puede gestionar inventario.
     */
    public function canManageInventory(): bool
    {
        return $this->isManager() && $this->is_active;
    }

    /**
     * Verificar si puede ver reportes.
     */
    public function canViewReports(): bool
    {
        return $this->isManager() && $this->is_active;
    }

    /**
     * Verificar si puede gestionar usuarios.
     */
    public function canManageUsers(): bool
    {
        return $this->isAdmin() && $this->is_active;
    }

    /**
     * Obtener nombre del rol.
     */
    public function getRoleNameAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_OWNER => 'Propietario',
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_MANAGER => 'Gerente',
            self::ROLE_CASHIER => 'Cajero',
            self::ROLE_USER => 'Usuario',
            default => $this->role,
        };
    }
}
