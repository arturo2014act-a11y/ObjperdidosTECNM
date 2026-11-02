<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
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
        'role',
        'is_active',
        'deactivation_reason',
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
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relación: Un usuario tiene muchas publicaciones de objetos perdidos/encontrados.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Relación: Un usuario (Prefectura/Admin) registra múltiples acciones de auditoría sobre objetos (RF-56).
     */
    public function itemLogs(): HasMany
    {
        return $this->hasMany(ItemLog::class);
    }

    /**
     * Relación: Historial administrativo de activaciones/desactivaciones sobre esta cuenta (RF-89, RF-90).
     */
    public function userLogs(): HasMany
    {
        return $this->hasMany(UserLog::class)->latest();
    }

    /**
     * Helpers para validación de roles institucionales
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPrefectura(): bool
    {
        return $this->role === 'prefectura';
    }

    public function isUsuarioGeneral(): bool
    {
        return $this->role === 'usuario_general';
    }
}

