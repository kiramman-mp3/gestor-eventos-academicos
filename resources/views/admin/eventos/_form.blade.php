@php
    $requisitosIniciales = old('requisitos', $evento->relationLoaded('requisitos')
        ? $evento->requisitos->map(fn ($r) => ['tipo' => $r->tipo->value, 'tipo_documento' => $r->tipo_documento?->value, 'descripcion' => $r->descripcion])->values()->all()
        : []);
    $categoriasSeleccionadas = array_map('intval', old('categorias', $evento->relationLoaded('categorias') ? $evento->categorias->pluck('id')->all() : []));
    $requisitosBloqueados = ($evento->inscripciones_count ?? 0) > 0;
@endphp

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <section class="card space-y-4 p-6">
            <h2 class="text-lg font-semibold text-zinc-900">Información general</h2>

            <x-field name="nombre" label="Nombre del evento" :value="$evento->nombre" required maxlength="255" />

            <div class="grid gap-4 sm:grid-cols-2">
                <x-field name="tipo_evento_id" label="Tipo de evento" type="select" :options="$tipos" :value="$evento->tipo_evento_id" placeholder="Selecciona" required />
                <x-field name="modalidad" label="Modalidad" type="select" :options="\App\Enums\Modalidad::options()" :value="$evento->modalidad" required />
            </div>

            <x-field name="ponentes" label="Ponentes" :value="$evento->ponentes" required maxlength="1000" />
            <x-field name="descripcion" label="Descripción" type="textarea" :value="$evento->descripcion" rows="5" maxlength="5000" />
            <x-field name="docente_id" label="Docente responsable de calificar" type="select" :options="$docentes" :value="$evento->docente_id"
                     placeholder="Sin docente asignado" help="El docente podrá registrar notas y asistencia de este evento." />
        </section>

        <section class="card space-y-4 p-6">
            <h2 class="text-lg font-semibold text-zinc-900">Fechas y cupos</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-field name="fecha_inicio_inscripciones" label="Inicio de inscripciones" type="date" :value="$evento->fecha_inicio_inscripciones" required />
                <x-field name="fecha_fin_inscripciones" label="Fin de inscripciones" type="date" :value="$evento->fecha_fin_inscripciones" required />
                <x-field name="fecha_inicio" label="Inicio del evento" type="date" :value="$evento->fecha_inicio" required />
                <x-field name="fecha_fin" label="Fin del evento" type="date" :value="$evento->fecha_fin" required />
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <x-field name="horas" label="Horas académicas" type="number" min="1" :value="$evento->horas" required />
                <x-field name="cupos" label="Cupos" type="number" min="1" :value="$evento->cupos" required />
                <x-field name="costo" label="Costo (USD)" type="number" min="0" step="0.01" :value="$evento->costo" required help="0 = gratuito" />
            </div>
        </section>

        <section class="card p-6" x-data="{ requisitos: @js($requisitosIniciales) }">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-zinc-900">Requisitos de inscripción</h2>
                @unless ($requisitosBloqueados)
                    <x-button type="button" size="sm" variant="secondary" @click="requisitos.push({ tipo: 'documento', tipo_documento: 'cedula', descripcion: '' })">
                        Agregar requisito
                    </x-button>
                @endunless
            </div>

            @if ($requisitosBloqueados)
                <p class="mt-2 rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-800">
                    El evento ya tiene inscripciones: los requisitos no se pueden modificar.
                </p>
                <ul class="mt-3 list-inside list-disc text-sm text-zinc-700">
                    @foreach ($evento->requisitos as $requisito)
                        <li>{{ $requisito->descripcion }} ({{ $requisito->tipo_documento?->label() ?? $requisito->tipo->label() }})</li>
                    @endforeach
                </ul>
            @else
                <p class="mt-1 text-sm text-zinc-500">Documentos del perfil que se exigen o preguntas que el estudiante responde al inscribirse.</p>

                <template x-if="requisitos.length === 0">
                    <p class="mt-4 text-sm text-zinc-400">Sin requisitos.</p>
                </template>

                <div class="mt-4 space-y-3">
                    <template x-for="(requisito, i) in requisitos" :key="i">
                        <div class="grid gap-2 rounded-lg border border-zinc-200 p-3 sm:grid-cols-[10rem_12rem_1fr_auto]">
                            <select class="form-input" :name="`requisitos[${i}][tipo]`" x-model="requisito.tipo" aria-label="Tipo">
                                @foreach ($tiposRequisito as $valor => $etiqueta)
                                    <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                            <select class="form-input" :name="`requisitos[${i}][tipo_documento]`" x-model="requisito.tipo_documento"
                                    :disabled="requisito.tipo !== 'documento'" aria-label="Documento">
                                @foreach ($tiposDocumento as $valor => $etiqueta)
                                    <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                            <input class="form-input" :name="`requisitos[${i}][descripcion]`" x-model="requisito.descripcion"
                                   placeholder="Descripción visible para el estudiante" required minlength="3" maxlength="500" aria-label="Descripción">
                            <x-button type="button" size="sm" variant="ghost" @click="requisitos.splice(i, 1)">Quitar</x-button>
                        </div>
                    </template>
                </div>
                <x-input-error :messages="collect($errors->get('requisitos*'))->flatten()->unique()->all()" />
            @endif
        </section>
    </div>

    <div class="space-y-6">
        <section class="card space-y-4 p-6">
            <h2 class="text-lg font-semibold text-zinc-900">Publicación</h2>
            <x-field name="estado" label="Estado" type="select" required
                     :options="collect([\App\Enums\EstadoEvento::Borrador, \App\Enums\EstadoEvento::Abierto])->mapWithKeys(fn ($e) => [$e->value => $e->label()])"
                     :value="in_array($evento->estado, [\App\Enums\EstadoEvento::Borrador, \App\Enums\EstadoEvento::Abierto], true) ? $evento->estado : \App\Enums\EstadoEvento::Borrador"
                     help="Los eventos en borrador no aparecen en el catálogo." />

            <div>
                <x-input-label value="Imagen / banner" />
                @if ($evento->imagen_url)
                    <img src="{{ $evento->imagen_url }}" alt="" class="mb-2 h-32 w-full rounded-lg object-cover">
                @endif
                <input type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp"
                       class="block w-full text-sm text-zinc-600 file:mr-2 file:rounded-md file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-sm file:font-semibold">
                <x-input-error :messages="$errors->get('imagen')" />
            </div>
        </section>

        <section class="card space-y-3 p-6">
            <h2 class="text-lg font-semibold text-zinc-900">Destinatarios</h2>
            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="es_publico" value="0">
                <input type="checkbox" name="es_publico" value="1" @checked(old('es_publico', $evento->es_publico)) class="rounded border-zinc-300 text-uta-600">
                Abierto al público general
            </label>
            <p class="text-xs text-zinc-500">Además, visible para estudiantes de las carreras seleccionadas:</p>
            @foreach ($categorias as $id => $nombre)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="categorias[]" value="{{ $id }}" @checked(in_array($id, $categoriasSeleccionadas, true)) class="rounded border-zinc-300 text-uta-600">
                    {{ $nombre }}
                </label>
            @endforeach
            <x-input-error :messages="$errors->get('categorias')" />
        </section>

        <section class="card space-y-4 p-6" x-data="{ nota: @js((bool) old('requiere_nota', $evento->requiere_nota)), asistencia: @js((bool) old('requiere_asistencia', $evento->requiere_asistencia)) }">
            <h2 class="text-lg font-semibold text-zinc-900">Aprobación y certificado</h2>

            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="requiere_nota" value="0">
                <input type="checkbox" name="requiere_nota" value="1" x-model="nota" class="rounded border-zinc-300 text-uta-600">
                Requiere calificación
            </label>
            <div x-show="nota" x-cloak>
                <x-field name="nota_minima" label="Nota mínima (0–10)" type="number" step="0.01" min="0" max="10"
                         :value="$evento->nota_minima ?? config('fisei.nota_minima_defecto')" />
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="requiere_asistencia" value="0">
                <input type="checkbox" name="requiere_asistencia" value="1" x-model="asistencia" class="rounded border-zinc-300 text-uta-600">
                Requiere asistencia
            </label>
            <div x-show="asistencia" x-cloak>
                <x-field name="asistencia_minima" label="Asistencia mínima (%)" type="number" step="0.01" min="0" max="100"
                         :value="$evento->asistencia_minima ?? config('fisei.asistencia_minima_defecto')" />
            </div>
        </section>
    </div>
</div>
