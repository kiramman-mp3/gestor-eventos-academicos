<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AutenticacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_correcto_regenera_sesion_y_redirige(): void
    {
        $user = User::factory()->create();

        $this->get(route('login'))->assertOk();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_incorrecto_usa_mensaje_generico(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'incorrecta'])
            ->assertSessionHasErrors(['email' => trans('auth.failed')]);

        $this->post(route('login'), ['email' => 'noexiste@example.com', 'password' => 'incorrecta'])
            ->assertSessionHasErrors(['email' => trans('auth.failed')]);

        $this->assertGuest();
    }

    public function test_login_se_bloquea_tras_varios_intentos(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $_) {
            $this->post(route('login'), ['email' => $user->email, 'password' => 'incorrecta']);
        }

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_usuario_desactivado_no_puede_ingresar(): void
    {
        $user = User::factory()->inactivo()->create();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_usuario_desactivado_con_sesion_abierta_es_expulsado(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $user->forceFill(['activo' => false])->save();

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_logout(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_usuario_no_verificado_es_enviado_a_verificar(): void
    {
        $this->actingAs(User::factory()->unverified()->create())
            ->get(route('dashboard'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_enlace_firmado_verifica_el_correo(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->actingAs($user)->get($url)->assertRedirect();

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_enlace_con_hash_incorrecto_no_verifica(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1('otro@example.com'),
        ]);

        $this->actingAs($user)->get($url)->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_restablecer_contrasena(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $this->post(route('password.store'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'Nueva.Clave1',
                'password_confirmation' => 'Nueva.Clave1',
            ])->assertRedirect(route('login'));

            return true;
        });

        $this->post(route('login'), ['email' => $user->email, 'password' => 'Nueva.Clave1']);
        $this->assertAuthenticatedAs($user);
    }

    public function test_solicitud_de_restablecimiento_no_revela_correos(): void
    {
        $this->post(route('password.email'), ['email' => 'noexiste@example.com'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');
    }
}
