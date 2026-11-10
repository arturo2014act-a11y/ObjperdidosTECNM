<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'user_id',
    ];

    /**
     * Publicación asociada a la conversación.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Usuario interesado que inició el contacto.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mensajes intercambiados dentro de la conversación.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ItemMessage::class)->oldest();
    }

    /**
     * Devuelve el otro participante de la conversación relativo al usuario autenticado.
     */
    public function otherParticipant(User $currentUser): User
    {
        // Si el usuario actual es el autor de la publicación, el otro es quien contactó ($this->user)
        if ($this->item && $this->item->user_id === $currentUser->id) {
            return $this->user;
        }

        // Si el usuario actual es quien contactó, el otro es el autor de la publicación ($this->item->user)
        return $this->item ? $this->item->user : $this->user;
    }
}

