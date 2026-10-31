<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admin_id',
        'action',
        'reason',
    ];

    /**
     * Cuenta institucional a la que corresponde este movimiento.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Administrador responsable que ejecutó la activación/desactivación.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Etiqueta amigable de la acción.
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'desactivacion' => 'Desactivación de Cuenta',
            'activacion' => 'Reactivación de Cuenta',
            default => ucfirst($this->action),
        };
    }
}

