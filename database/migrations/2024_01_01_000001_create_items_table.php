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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            
            // Relación con la tabla users
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Tipo de publicación
            $table->enum('type', ['perdido', 'encontrado'])->index();

            // Detalles del objeto
            $table->string('name', 100);
            $table->text('description'); // Validado a 500 caracteres a nivel de aplicación
            $table->string('category')->index();
            $table->string('category_specification')->nullable();
            
            // Ubicación y contexto institucional
            $table->string('area')->index();
            $table->string('academic_class')->nullable();
            
            // Multimedia y fecha del suceso
            $table->string('image_path')->nullable();
            $table->date('event_date')->nullable();

            // Estado del flujo del objeto
            $table->enum('status', [
                'publicado',
                'pendiente',
                'rechazado',
                'cerrado',
                'devuelto',
                'cierre_pendiente'
            ])->default('pendiente')->index();

            $table->text('rejection_reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};

