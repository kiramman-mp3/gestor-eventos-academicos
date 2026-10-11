<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte · {{ $evento->nombre }}</title>
    <style>
        @page { margin: 28px 32px 40px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 18px; color: #1b2a53; margin: 0; }
        .muted { color: #6b7280; }
        .encabezado { width: 100%; border-bottom: 3px solid #b02a37; padding-bottom: 8px; margin-bottom: 12px; }
        .ficha td { padding: 2px 12px 2px 0; }
        .resumen td { border: 1px solid #e5e7eb; padding: 4px 8px; text-align: center; }
        table.datos { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.datos th { background: #1b2a53; color: #fff; padding: 5px; text-align: left; font-size: 9px; }
        table.datos td { border-bottom: 1px solid #e5e7eb; padding: 4px 5px; }
        table.datos tr:nth-child(even) td { background: #f9fafb; }
        .ok { color: #047857; font-weight: bold; }
        .no { color: #b91c1c; }
        footer { position: fixed; bottom: -26px; left: 0; right: 0; font-size: 8px; color: #9ca3af; }
    </style>
</head>
<body>
    <table class="encabezado">
        <tr>
            <td>
                <h1>{{ $evento->nombre }}</h1>
                <span class="muted">{{ $evento->tipoEvento->nombre }} · {{ $evento->modalidad->label() }} · {{ $evento->estado->label() }}</span>
            </td>
            <td style="text-align: right;"><img src="{{ public_path('images/logo-fisei.png') }}" alt="" style="height: 40px;"></td>
        </tr>
    </table>

    <table class="ficha">
        <tr>
            <td><strong>Fechas:</strong> {{ $evento->fecha_inicio->isoFormat('LL') }} al {{ $evento->fecha_fin->isoFormat('LL') }}</td>
            <td><strong>Horas:</strong> {{ $evento->horas }}</td>
            <td><strong>Cupos:</strong> {{ $evento->cupos }}</td>
            <td><strong>Costo:</strong> {{ $evento->esGratuito() ? 'Gratuito' : '$'.number_format((float) $evento->costo, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Docente:</strong> {{ $evento->docente?->nombre_completo ?? '—' }}</td>
            <td colspan="2"><strong>Destinatarios:</strong> {{ $evento->es_publico ? 'Público general' : '' }} {{ $evento->categorias->pluck('nombre')->join(', ') }}</td>
            <td>
                @if ($evento->requiere_nota)<strong>Nota mín.:</strong> {{ $evento->nota_minima }} @endif
                @if ($evento->requiere_asistencia)<strong>Asist. mín.:</strong> {{ $evento->asistencia_minima }}% @endif
            </td>
        </tr>
    </table>

    <table class="resumen" style="margin-top: 10px; border-collapse: collapse;">
        <tr>
            @foreach ($resumen as $estado => $total)
                <td><strong>{{ $total }}</strong><br><span class="muted">{{ $estado }}</span></td>
            @endforeach
            <td><strong>{{ $aprobados }}</strong><br><span class="muted">Aptos para certificado</span></td>
        </tr>
    </table>

    <table class="datos">
        <thead>
            <tr>
                <th>#</th><th>Apellidos y nombres</th><th>Cédula</th><th>Correo</th><th>Estado</th>
                @if ($evento->requiere_nota)<th>Nota</th>@endif
                @if ($evento->requiere_asistencia)<th>Asistencia</th>@endif
                <th>Legalizado</th><th>Resultado</th><th>Certificado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($inscripciones as $inscripcion)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $inscripcion->user->apellido }} {{ $inscripcion->user->nombre }}</td>
                    <td>{{ $inscripcion->user->cedula }}</td>
                    <td>{{ $inscripcion->user->email }}</td>
                    <td>{{ $inscripcion->estado->label() }}</td>
                    @if ($evento->requiere_nota)<td>{{ $inscripcion->nota ?? '—' }}</td>@endif
                    @if ($evento->requiere_asistencia)<td>{{ $inscripcion->asistencia !== null ? $inscripcion->asistencia.'%' : '—' }}</td>@endif
                    <td>{{ $inscripcion->legalizado ? 'Sí' : 'No' }}</td>
                    <td class="{{ $inscripcion->aptaParaCertificado() ? 'ok' : 'no' }}">{{ $inscripcion->aptaParaCertificado() ? 'Aprobado' : 'No aprobado' }}</td>
                    <td>{{ $inscripcion->codigo_certificado ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="muted">Sin inscripciones.</td></tr>
            @endforelse
        </tbody>
    </table>

    <footer>Generado el {{ now()->isoFormat('LLL') }} · {{ config('app.name') }}</footer>
</body>
</html>
