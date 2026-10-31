<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_logs', function (Blueprint $table) {
            $table->id();
            
            // Publicación auditada
            $table->foreignId('item_id')
                ->constrained('items')
                ->cascadeOnDelete();

            // Cuenta responsable de Prefectura o Admin que ejecuta la acción
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Tipo de acción auditada (RF-56)
            $table->string('action'); // 'aprobacion_recepcion', 'rechazo_encontrado', 'cierre_aprobado', 'cierre_rechazado', 'objeto_devuelto', 'reversion_devolucion'
            
            // Motivo cuando aplique (obligatorio en rechazo y reversión)
            $table->text('reason')->nullable();
            
            // Metadatos de red
            $table->string('ip_address', 45)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_logs');
    }
};

