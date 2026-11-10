<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationExecutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_ciclo_completo_verificacion_correo(): void
    {
        // 1. Crear un usuario con correo NO verificado
        $user = User::create([
            'name' => 'Prueba Verificación TecNM',
            'email' => 'alumno.verif@merida.tecnm.mx',
            'password' => bcrypt('TecNM#2026'),
            'role' => 'usuario_general',
            'is_active' => true,
            'email_verified_at' => null,
        ]);

        $this->assertFalse($user->hasVerifiedEmail());

        // 2. Si intenta acceder a una ruta protegida con middleware 'verified', es redirigido al aviso
        $responseNotice = $this->actingAs($user)->get(route('items.my-items'));
        $responseNotice->assertRedirect(route('verification.notice'));

        // 3. Generar la URL firmada oficial de la API de verificación
        $expireMinutes = (int) Config::get('auth.verification.expire', 60);
        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes($expireMinutes),
            [
                'id'   => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $this->assertNotEmpty($signedUrl);

        // 4. Ejecutar la llamada a la ruta firmada (redirige a la URL que el usuario intentó acceder previamente o al muro)
        $responseVerify = $this->actingAs($user)->get($signedUrl);
        $responseVerify->assertRedirect();
        $responseVerify->assertSessionHas('success');

        // 5. Comprobar que en la base de datos ya está marcado como verificado
        $user->refresh();
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertNotNull($user->email_verified_at);

        // 6. Ahora puede acceder a las rutas protegidas sin ser redirigido
        $responseAllowed = $this->actingAs($user)->get(route('items.my-items'));
        $responseAllowed->assertStatus(200);
        $responseAllowed->assertSee('Mis Publicaciones');
    }
}
