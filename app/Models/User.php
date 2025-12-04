<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'name',
        'email',
        'password',
        'business_name',
        'business_type',
        'currency',
        'preferences',
        'categories',
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
        ];
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
}
