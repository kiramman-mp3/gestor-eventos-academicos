<?php

namespace App\Services;

use App\Models\Inscripcion;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Genera el certificado PDF de una inscripción aprobada.
 *
 * La primera emisión asigna un código único verificable públicamente
 * (route certificados.verificar). Las siguientes descargas reutilizan el mismo código.
 * Los datos salen siempre de la base de datos; nunca de la petición.
 */
class CertificadoPdfService
{
    public function generar(Inscripcion $inscripcion): DomPdf
    {
        $inscripcion = $this->emitir($inscripcion);
        $inscripcion->loadMissing(['user', 'evento.tipoEvento']);

        return Pdf::loadView('pdf.certificado', [
            'inscripcion' => $inscripcion,
            'evento' => $inscripcion->evento,
            'participante' => $inscripcion->user,
            'urlVerificacion' => route('certificados.verificar', $inscripcion->codigo_certificado),
        ])
            ->setPaper('a4', 'landscape')
            ->setOption('isFontSubsettingEnabled', true);
    }

    public function nombreArchivo(Inscripcion $inscripcion): string
    {
        return 'certificado-'.Str::slug($inscripcion->evento->nombre).'-'.$inscripcion->codigo_certificado.'.pdf';
    }

    /** Asigna el código una sola vez, aun con descargas simultáneas. */
    private function emitir(Inscripcion $inscripcion): Inscripcion
    {
        if ($inscripcion->codigo_certificado) {
            return $inscripcion;
        }

        return DB::transaction(function () use ($inscripcion) {
            $bloqueada = Inscripcion::query()->whereKey($inscripcion->id)->lockForUpdate()->firstOrFail();

            if (! $bloqueada->codigo_certificado) {
                $bloqueada->codigo_certificado = $this->nuevoCodigo();
                $bloqueada->certificado_emitido_at = now();
                $bloqueada->save();
            }

            return $bloqueada->setRelations($inscripcion->getRelations());
        });
    }

    private function nuevoCodigo(): string
    {
        do {
            $codigo = 'FISEI-'.now()->year.'-'.Str::upper(Str::random(8));
        } while (Inscripcion::query()->where('codigo_certificado', $codigo)->exists());

        return $codigo;
    }
}
