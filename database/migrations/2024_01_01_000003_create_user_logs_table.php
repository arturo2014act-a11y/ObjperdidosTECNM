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
        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            
            // Cuenta afectada
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Administrador responsable que ejecutó la acción
            $table->foreignId('admin_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Tipo de acción: 'activacion' o 'desactivacion' (RF-89)
            $table->string('action'); // 'activacion', 'desactivacion'

            // Motivo justificado obligatorio (máx 255 caracteres, RF-91)
            $table->string('reason', 255);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_logs');
    }
};

