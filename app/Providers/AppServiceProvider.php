<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
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

        Password::defaults(fn () => $this->app->isProduction()
            ? Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised()
            : Password::min(8)->mixedCase()->numbers()->symbols());
    }
}
