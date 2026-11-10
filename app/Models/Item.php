<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'name',
        'description',
        'category',
        'category_specification',
        'area',
        'academic_class',
        'image_path',
        'event_date',
        'status',
        'rejection_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_date' => 'date',
        ];
    }

    /**
     * Relación: Una publicación pertenece a un usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: Conversaciones privadas iniciadas por usuarios interesados.
     */
    public function conversations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Conversation::class)->latest();
    }

    /**
     * Relación: Mensajes y consultas de orientación sobre la pertenencia.
     */
    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ItemMessage::class)->oldest();
    }

    /**
     * Relación: Una publicación tiene un historial de auditoría de acciones (RF-56).
     */
    public function logs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ItemLog::class)->latest();
    }

    /**
     * Scopes de consulta útiles para el prototipo
     */
    public function scopePublicados($query)
    {
        return $query->where('status', 'publicado');
    }

    public function scopePerdidos($query)
    {
        return $query->where('type', 'perdido');
    }

    public function scopeEncontrados($query)
    {
        return $query->where('type', 'encontrado');
    }
}

