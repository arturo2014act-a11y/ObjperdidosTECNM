<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'user_id',
        'action',
        'reason',
        'ip_address',
    ];

    /**
     * Objeto al que pertenece este registro de auditoría.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Cuenta institucional (Prefectura/Admin) responsable de la acción.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Etiqueta amigable de la acción auditada para la interfaz de usuario.
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'aprobacion_recepcion' => 'Aprobación y Recepción Física',
            'rechazo_encontrado' => 'Rechazo de Publicación Encontrada',
            'cierre_aprobado' => 'Aprobación de Solicitud de Cierre',
            'cierre_rechazado' => 'Rechazo de Solicitud de Cierre',
            'objeto_devuelto' => 'Entrega Presencial y Devolución',
            'reversion_devolucion' => 'Reversión de Devolución a Activo',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }
}

