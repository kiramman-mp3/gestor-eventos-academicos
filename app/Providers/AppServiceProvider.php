<?php

namespace App\Providers;

use App\Policies\CertificadoPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Detecta en desarrollo: lazy loading (N+1), atributos inexistentes y asignación masiva descartada
        Model::shouldBeStrict(! $this->app->isProduction());

        Date::use(\Carbon\CarbonImmutable::class);

        // El certificado no es un modelo: su política se registra como Gate
        Gate::define('descargar-certificado', [CertificadoPolicy::class, 'descargar']);

        Password::defaults(fn () => $this->app->isProduction()
            ? Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised()
            : Password::min(8)->mixedCase()->numbers()->symbols());
    }
}
