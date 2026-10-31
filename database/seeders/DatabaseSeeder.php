<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\ItemLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with realistic test data.
     */
    public function run(): void
    {
        // 1. Cuenta de Prefectura
        $prefecto = User::create([
            'name' => 'Lic. Carlos Canché Pech',
            'email' => 'prefectura@merida.tecnm.mx',
            'password' => Hash::make('TecNM#2026'),
            'role' => 'prefectura',
            'is_active' => true,
        ]);

        // 2. Usuarios de la comunidad académica
        $estudiante1 = User::create([
            'name' => 'Rodrigo Alonzo Medina',
            'email' => 'rodrigo.alonzo@merida.tecnm.mx',
            'password' => Hash::make('TecNM#2026'),
            'role' => 'usuario_general',
            'is_active' => true,
        ]);

        $estudiante2 = User::create([
            'name' => 'Mariana Yam Puc',
            'email' => 'mariana.yam@itmerida.edu.mx',
            'password' => Hash::make('TecNM#2026'),
            'role' => 'usuario_general',
            'is_active' => true,
        ]);

        // 3. Sección 1: Encontrados pendientes de aprobación (RF-50, RF-53)
        $itemPendiente1 = Item::create([
            'user_id' => $estudiante1->id,
            'type' => 'encontrado',
            'name' => 'Calculadora Científica Casio fx-991LAX',
            'description' => 'Calculadora científica color negro con tapa protectora con una calcomanía del logo de Sistemas.',
            'category' => 'Electrónica',
            'category_specification' => 'Calculadoras',
            'area' => 'Campus Poniente',
            'academic_class' => 'Edificio H - Laboratorio de Cómputo 2',
            'image_path' => null,
            'event_date' => now()->subDay(),
            'status' => 'pendiente',
        ]);

        $itemPendiente2 = Item::create([
            'user_id' => $estudiante2->id,
            'type' => 'encontrado',
            'name' => 'Memoria USB Kingston 64GB',
            'description' => 'Memoria USB metálica plateada con un llavero azul con forma de pulpo.',
            'category' => 'Dispositivos de Almacenamiento',
            'category_specification' => 'Memorias Flash',
            'area' => 'Campus Central',
            'academic_class' => 'Biblioteca Central - Sala 3',
            'image_path' => null,
            'event_date' => now()->subHours(6),
            'status' => 'pendiente',
        ]);

        // 4. Sección 2: Solicitudes de cierre pendientes (RF-50)
        $itemCierre = Item::create([
            'user_id' => $estudiante1->id,
            'type' => 'perdido',
            'name' => 'Credencial Institucional TecNM',
            'description' => 'Credencial de estudiante a nombre de Rodrigo Alonzo de Ing. en Sistemas.',
            'category' => 'Documentos Oficiales',
            'category_specification' => 'Credencial escolar',
            'area' => 'Campus Poniente',
            'academic_class' => 'Cafetería Principal',
            'image_path' => null,
            'event_date' => now()->subDays(3),
            'status' => 'cierre_pendiente',
        ]);

        // 5. Sección 3: Publicaciones bajo gestión (RF-51)
        $itemPublicado = Item::create([
            'user_id' => $estudiante2->id,
            'type' => 'perdido',
            'name' => 'Mochila negra SwissGear con laptop',
            'description' => 'Mochila negra con cierre reforzado, contiene libretas de cálculo y estuche de lápices.',
            'category' => 'Mochilas y Bolsos',
            'category_specification' => 'Mochila escolar',
            'area' => 'Campus Central',
            'academic_class' => 'Edificio C - Aula C4',
            'image_path' => null,
            'event_date' => now()->subDays(2),
            'status' => 'publicado',
        ]);

        $itemDevuelto = Item::create([
            'user_id' => $estudiante1->id,
            'type' => 'encontrado',
            'name' => 'Lentes de sol con armazón café',
            'description' => 'Lentes graduados con funda protectora color azul marino.',
            'category' => 'Accesorios Personales',
            'category_specification' => 'Óptica',
            'area' => 'Campus Poniente',
            'academic_class' => 'Canchas de Basquetbol',
            'image_path' => null,
            'event_date' => now()->subDays(5),
            'status' => 'devuelto',
        ]);

        // 6. Registro de Auditoría Inicial (RF-56)
        ItemLog::create([
            'item_id' => $itemDevuelto->id,
            'user_id' => $prefecto->id,
            'action' => 'objeto_devuelto',
            'reason' => 'Entrega presencial confirmada con el propietario. Acreditó propiedad mediante descripción exacta de la graduación y estuche.',
            'ip_address' => '127.0.0.1',
        ]);
    }
}

