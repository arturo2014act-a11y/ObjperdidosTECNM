<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EncuentraTecSprintTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Prueba 1: Un usuario general puede iniciar sesión y acceder a rutas protegidas.
     */
    public function test_usuario_general_login(): void
    {
        $user = User::where('role', 'usuario_general')->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user)->get(route('items.my-items'));
        $response->assertStatus(200);
        $response->assertSee('Mis Publicaciones');
    }

    /**
     * Prueba 2: Publicar objeto Perdido se publica inmediatamente en el muro.
     */
    public function test_publicacion_objeto_perdido_inmediata(): void
    {
        $user = User::where('role', 'usuario_general')->first();

        $response = $this->actingAs($user)->post(route('items.store'), [
            'type' => 'perdido',
            'name' => 'Cartera café de piel de prueba',
            'description' => 'Cartera café con credencial de elector y tarjetas.',
            'category' => 'Accesorios Personales y Joyería',
            'area' => 'Edificio B',
            'academic_class' => 'Aula B1',
            'event_date' => now()->subDay()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('items.my-items'));
        $response->assertSessionHas('success');

        $item = Item::where('name', 'Cartera café de piel de prueba')->first();
        $this->assertNotNull($item);
        $this->assertEquals('publicado', $item->status);

        // Aparece en el muro
        $muroResponse = $this->get(route('items.index'));
        $muroResponse->assertStatus(200);
        $muroResponse->assertSee('Cartera café de piel de prueba');
    }

    /**
     * Prueba 3: Un Encontrado creado por usuario general permanece pendiente.
     */
    public function test_usuario_general_publica_encontrado_queda_pendiente(): void
    {
        Storage::fake('public');
        $user = User::where('role', 'usuario_general')->first();

        $image = UploadedFile::fake()->create('audifonos.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)->post(route('items.store'), [
            'type' => 'encontrado',
            'name' => 'Audífonos inalámbricos Sony negros',
            'description' => 'Audífonos dentro de estuche negro ovalado.',
            'category' => 'Electrónica',
            'area' => 'Edificio H',
            'event_date' => now()->format('Y-m-d'),
            'image' => $image,
        ]);

        $response->assertRedirect(route('items.my-items'));
        $item = Item::where('name', 'Audífonos inalámbricos Sony negros')->first();
        $this->assertNotNull($item);
        $this->assertEquals('pendiente', $item->status);

        // NO debe figurar en el muro público
        $muroResponse = $this->get(route('items.index'));
        $muroResponse->assertDontSee('Audífonos inalámbricos Sony negros');
    }

    /**
     * Prueba 4 & 5: Prefectura puede consultar y aprobar Encontrados pendientes con recepción física.
     */
    public function test_prefectura_consulta_y_aprueba_encontrado_pendiente(): void
    {
        $prefecto = User::where('role', 'prefectura')->first();
        $this->assertNotNull($prefecto);

        // Consultar el panel
        $dashResponse = $this->actingAs($prefecto)->get(route('prefectura.dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Encontrados por Aprobar');

        $itemPendiente = Item::where('type', 'encontrado')->where('status', 'pendiente')->first();
        $this->assertNotNull($itemPendiente);

        // Aprobar sin checkbox falla
        $failApprove = $this->actingAs($prefecto)->post(route('prefectura.items.approve-found', $itemPendiente), []);
        $failApprove->assertSessionHasErrors('confirm_physical_receipt');

        // Aprobar con checkbox exitoso
        $okApprove = $this->actingAs($prefecto)->post(route('prefectura.items.approve-found', $itemPendiente), [
            'confirm_physical_receipt' => '1',
        ]);
        $okApprove->assertRedirect();
        $okApprove->assertSessionHas('success');

        $itemPendiente->refresh();
        $this->assertEquals('publicado', $itemPendiente->status);

        // Ahora figura en el muro
        $muroResponse = $this->get(route('items.index'));
        $muroResponse->assertSee($itemPendiente->name);
    }

    /**
     * Prueba 6: Prefectura puede publicar directamente un Encontrado sin pasar por aprobación.
     */
    public function test_prefectura_publica_directamente_encontrado(): void
    {
        Storage::fake('public');
        $prefecto = User::where('role', 'prefectura')->first();

        $image = UploadedFile::fake()->create('llaves.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($prefecto)->post(route('items.store'), [
            'type' => 'encontrado',
            'name' => 'Llavero con 3 llaves y figura de metal',
            'description' => 'Llavero entregado directamente en oficina de prefectura.',
            'category' => 'Llaves',
            'area' => 'Edificio B',
            'event_date' => now()->format('Y-m-d'),
            'image' => $image,
        ]);

        $response->assertRedirect(route('items.my-items'));
        $item = Item::where('name', 'Llavero con 3 llaves y figura de metal')->first();
        $this->assertNotNull($item);
        $this->assertEquals('publicado', $item->status);

        // Debe verse de inmediato en el muro
        $muroResponse = $this->get(route('items.index'));
        $muroResponse->assertSee('Llavero con 3 llaves y figura de metal');
    }

    /**
     * Prueba 7: Mis Publicaciones y Privacidad del Autor en el Muro para otros usuarios.
     */
    public function test_mis_publicaciones_y_privacidad(): void
    {
        $author = User::where('role', 'usuario_general')->first();
        $otherUser = User::where('role', 'usuario_general')->where('id', '!=', $author->id)->first();

        // El usuario 2 ve sus publicaciones y la tabla no contiene los items del usuario 1
        $authorItem = Item::where('user_id', $author->id)->first();
        if ($otherUser && $authorItem) {
            $myItemsResponse = $this->actingAs($otherUser)->get(route('items.my-items'));
            $myItemsResponse->assertStatus(200);
            $myItemsResponse->assertDontSee($authorItem->name);
        }

        // En el detalle público visto por otro usuario, no se exhibe el correo del autor
        $itemPublicado = Item::where('user_id', $author->id)->where('status', 'publicado')->first();
        if ($itemPublicado && $otherUser) {
            $showResponse = $this->actingAs($otherUser)->get(route('items.show', $itemPublicado));
            $showResponse->assertStatus(200);
            $showResponse->assertDontSee($author->email);
        }
    }

    /**
     * Prueba 8: Administrador puede consultar y buscar cuentas, y usuario general no tiene acceso.
     */
    public function test_admin_cuentas_y_control_de_acceso(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        // Admin accede al listado
        $adminResponse = $this->actingAs($admin)->get(route('admin.users.index'));
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('Gestión de Cuentas Institucionales');

        // Búsqueda por nombre
        $searchResponse = $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'Rodrigo']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Rodrigo');

        // Usuario general recibe 404 al intentar entrar a /admin/cuentas (RF-62)
        $generalUser = User::where('role', 'usuario_general')->first();
        $forbiddenResponse = $this->actingAs($generalUser)->get(route('admin.users.index'));
        $forbiddenResponse->assertStatus(404);
    }
}
