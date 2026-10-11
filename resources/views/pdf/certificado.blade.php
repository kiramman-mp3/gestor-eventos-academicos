<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Certificado {{ $inscripcion->codigo_certificado }}</title>
    <style>
        @page { margin: 0; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; color: #1f2937; }
        .marco { position: absolute; top: 18px; left: 18px; right: 18px; bottom: 18px; border: 3px solid #1b2a53; }
        .franja { position: absolute; top: 18px; left: 18px; right: 18px; height: 14px; background: #b02a37; }
        .contenido { padding: 80px 80px 0; text-align: center; }
        .logos { width: 100%; margin-bottom: 10px; }
        .logos td { vertical-align: middle; }
        .titulo { font-size: 34px; letter-spacing: 6px; color: #1b2a53; margin: 18px 0 4px; font-weight: bold; }
        .subtitulo { font-size: 14px; color: #6b7280; margin: 0 0 26px; text-transform: uppercase; letter-spacing: 2px; }
        .nombre { font-size: 30px; font-weight: bold; color: #b02a37; margin: 8px 0; }
        .texto { font-size: 15px; line-height: 1.7; margin: 12px 60px; }
        .firmas { width: 100%; margin-top: 90px; }
        .firmas td { width: 50%; text-align: center; font-size: 12px; }
        .linea { width: 220px; border-top: 1px solid #1f2937; margin: 0 auto 6px; }
        .pie { position: absolute; bottom: 34px; left: 40px; right: 40px; font-size: 10px; color: #6b7280; }
        .codigo { background: #1b2a53; color: #fff; padding: 4px 10px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="marco"></div>
    <div class="franja"></div>

    <div class="contenido">
        <table class="logos">
            <tr>
                <td style="text-align: left;"><img src="{{ public_path('images/logo-uta-escudo.png') }}" alt="" style="height: 70px;"></td>
                <td style="text-align: right;"><img src="{{ public_path('images/logo-fisei.png') }}" alt="" style="height: 60px;"></td>
            </tr>
        </table>

        <p class="titulo">CERTIFICADO</p>
        <p class="subtitulo">de {{ $evento->requiere_nota ? 'aprobación' : 'participación' }}</p>

        <p style="margin: 0; font-size: 14px;">La Facultad de Ingeniería en Sistemas, Electrónica e Industrial certifica que</p>
        <p class="nombre">{{ mb_strtoupper($participante->nombre_completo) }}</p>
        <p style="margin: 0; font-size: 13px;">C.I. {{ $participante->cedula }}</p>

        <p class="texto">
            {{ $evento->requiere_nota ? 'aprobó' : 'participó en' }} el {{ mb_strtolower($evento->tipoEvento->nombre) }}
            <strong>«{{ $evento->nombre }}»</strong>, en modalidad {{ mb_strtolower($evento->modalidad->label()) }},
            con una duración de <strong>{{ $evento->horas }} horas académicas</strong>,
            realizado del {{ $evento->fecha_inicio->isoFormat('D [de] MMMM') }} al {{ $evento->fecha_fin->isoFormat('D [de] MMMM [de] YYYY') }}@if ($evento->requiere_nota), con una calificación de <strong>{{ number_format((float) $inscripcion->nota, 2) }}/10</strong>@endif.
        </p>

        <table class="firmas">
            <tr>
                <td><div class="linea"></div>Decano(a) FISEI</td>
                <td><div class="linea"></div>Coordinador(a) académico(a)</td>
            </tr>
        </table>
    </div>

    <div class="pie">
        <span class="codigo">{{ $inscripcion->codigo_certificado }}</span>
        &nbsp; Emitido el {{ ($inscripcion->certificado_emitido_at ?? now())->isoFormat('D [de] MMMM [de] YYYY') }} ·
        Verifique su autenticidad en {{ $urlVerificacion }}
    </div>
</body>
</html>
