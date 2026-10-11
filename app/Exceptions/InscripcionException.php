<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Regla de negocio incumplida al inscribirse o al gestionar un pago.
 * Se renderiza como un mensaje para el usuario, no como un error 500.
 */
class InscripcionException extends RuntimeException
{
    public static function inscripcionesCerradas(): self
    {
        return new self('Las inscripciones para este evento no están abiertas.');
    }

    public static function noDirigido(): self
    {
        return new self('Este evento no está dirigido a tu carrera.');
    }

    public static function yaInscrito(): self
    {
        return new self('Ya estás inscrito en este evento.');
    }

    public static function sinCupos(): self
    {
        return new self('No quedan cupos disponibles para este evento.');
    }

    public static function limiteAlcanzado(int $maximo): self
    {
        return new self("Alcanzaste el máximo de {$maximo} inscripciones activas permitidas por estudiante.");
    }

    public static function requisitoPendiente(string $descripcion): self
    {
        return new self("Falta cumplir el requisito: {$descripcion}. Sube el documento desde tu perfil.");
    }

    public static function estadoInvalido(string $accion): self
    {
        return new self("No es posible {$accion} en el estado actual de la inscripción.");
    }

    public function render(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 422);
        }

        return back()->with('error', $this->getMessage());
    }
}
