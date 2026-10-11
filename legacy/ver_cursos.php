<?php
require_once 'session.php';
$nombre = getUserName();
$apellido = getUserLastname();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Inicio - Gestión de Eventos FISEI</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous">
</head>

<body>
    <header class="ctt-header">
        <div class="top-bar">
            <div class="logo">
                <a href="index.php">
                    <img src="uploads/logo.png" alt="Logo FISEI">
                </a>
            </div>
            <div class="top-links">
                <?php if (isLoggedIn() && getUserRole() === 'administrador'): ?>
                    <div class="link-box">
                        <i class="fa-solid fa-arrow-left"></i>
                        <div>
                            <span class="title">Dashboard</span><br>
                            <a href="admin/panel_admin.php">Ir al Dashboard</a>
                        </div>
                    </div>
                <?php elseif (isLoggedIn() && getUserRole() === 'estudiante'): ?>
                    <div class="link-box">
                        <i class="fa-solid fa-graduation-cap"></i>
                        <div>
                            <span class="title">Mis Cursos</span><br>
                            <a href="estudiantes/mis_cursos.php">Ver Cursos</a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isLoggedIn()): ?>
                    <div class="link-box">
                        <i class="fa-solid fa-user"></i>
                        <div>
                            <span class="title">Hola, <?= htmlspecialchars($nombre) ?>
                                <?= htmlspecialchars($apellido) ?></span><br>
                            <a href="perfil.php">Ver Perfil</a>
                        </div>
                    </div>
                    <div class="link-box">
                        <i class="fas fa-sign-out-alt"></i>
                        <div>
                            <span class="title">Sesión Activa</span><br>
                            <a href="logout.php">Cerrar Sesión</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="link-box">
                        <i class="fas fa-sign-in-alt"></i>
                        <div>
                            <span class="title">Acceso</span><br>
                            <a href="login.php">Iniciar Sesión</a>
                        </div>
                    </div>
                    <div class="link-box">
                        <i class="fas fa-user-plus"></i>
                        <div>
                            <span class="title">Únete</span><br>
                            <a href="registro.php">Registrarse</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <div class="hero-section-full-width">
            <h1>Gestión de Eventos Académicos - FISEI</h1>
            <p>Bienvenido al sistema de gestión de cursos y eventos académicos de la Facultad de Ingeniería en
                Sistemas, Electrónica e Industrial.</p>
        </div>

        <section class="section-courses-grid">
            <div id="lista-cursos" class="cards-grid"></div>
        </section>
    </main>

    <div class="modal" id="modalInscripcion">
        <div class="modal-dialog">
            <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column;">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTituloCurso">Curso</h5>
                    <button type="button" class="close-button" data-dismiss="modal" aria-label="Cerrar">&times;</button>
                </div>
                <div class="modal-body-custom" style="overflow-y: auto; max-height: 65vh; padding-right: 10px;">
                    <div class="modal-image-col">
                        <img id="modalImagenCurso" src="resource/placeholder.svg" alt="Imagen del curso">
                        <div id="modalImagePlaceholder" class="missing-image-placeholder" style="display: none;">
                            Imagen no disponible
                        </div>
                    </div>
                    <div class="modal-details-col">
                        <p><strong>Ponente:</strong> <span id="modalPonente"></span></p>
                        <p><strong>Fechas:</strong> <span id="modalFechas"></span></p>
                        <p><strong>Horas:</strong> <span id="modalHoras"></span></p>
                        <p><strong>Cupos:</strong> <span id="modalCupos"></span></p>
                        <h6 class="modal-subtitle">Requisitos:</h6>
                        <ul id="lista-requisitos" class="requirements-list"></ul>
                        <div id="camposTextoDinamicos" class="motivation-field" style="display: none;">
                            <!-- Aquí se añadirán inputs de texto dinámicamente -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer-custom">
                    <button type="button" class="btn-cancel" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn-primary-custom" id="btnConfirmarInscripcion">Confirmar
                        inscripción</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const contenedor = document.getElementById('lista-cursos');
            const modalInscripcion = document.getElementById('modalInscripcion');
            const modalImagenCurso = document.getElementById('modalImagenCurso');
            const modalImagePlaceholder = document.getElementById('modalImagePlaceholder');
            const modalTituloCurso = document.getElementById('modalTituloCurso');
            const modalPonente = document.getElementById('modalPonente');
            const modalFechas = document.getElementById('modalFechas');
            const modalHoras = document.getElementById('modalHoras');
            const modalCupos = document.getElementById('modalCupos');
            const listaRequisitos = document.getElementById('lista-requisitos');
            const camposTextoDinamicos = document.getElementById('camposTextoDinamicos');
            const btnConfirmarInscripcion = document.getElementById('btnConfirmarInscripcion');

            let requisitosGlobales = [];

            fetch('service/CursosPorCarrera.php')
                .then(res => res.json())
                .then(data => {
                    const { rol, cursos } = data;
                    contenedor.innerHTML = '';

                    cursos.forEach(curso => {
                        const ruta = curso.ruta_imagen?.replace(/^(\.\.\/)+/, '') || 'resource/placeholder.svg';
                        let btn = '';
                        let cuposInfo = '';

                        if (curso.cupos_disponibles <= 0) {
                            cuposInfo = `<p class="text-danger"><strong>Cupos:</strong> ${curso.cupos} (LLENO)</p>`;
                        } else if (curso.cupos_disponibles <= 5) {
                            cuposInfo = `<p class="text-warning"><strong>Cupos:</strong> ${curso.cupos_disponibles}/${curso.cupos} (Pocos cupos)</p>`;
                        } else {
                            cuposInfo = `<p><strong>Cupos:</strong> ${curso.cupos_disponibles}/${curso.cupos}</p>`;
                        }

                        if (rol === 'estudiante' && !curso.inscrito) {
                            btn = curso.cupos_disponibles > 0
                                ? `<button class="boton-inscribirse btn btn-primary mt-3" data-id="${curso.id}">Inscribirse</button>`
                                : `<button class="btn btn-secondary mt-3" disabled>Curso Lleno</button>`;
                        } else if (rol === 'administrador') {
                            btn = `<a href="admin/administrar_evento.php?id=${curso.id}" class="btn btn-outline-secondary mt-3">Administrar</a>`;
                        }

                        const tarjeta = document.createElement('div');
                        tarjeta.className = 'card-curso';
                        tarjeta.innerHTML = `
                        <img src="${ruta}" alt="Imagen del evento">
                        <div class="card-body">
                            <h5>${curso.nombre_evento}</h5>
                            <p><strong>Fechas:</strong> ${curso.fecha_inicio} al ${curso.fecha_fin}</p>
                            <p><strong>Ponente:</strong> ${curso.ponentes}</p>
                            <p><strong>Horas:</strong> ${curso.horas}</p>
                            ${cuposInfo}
                            <div class="card-button-wrapper">${btn}</div>
                        </div>`;
                        contenedor.appendChild(tarjeta);
                    });
                })
                .catch(error => console.error('Error al cargar cursos:', error));

            document.addEventListener('click', async e => {
                if (e.target.classList.contains('boton-inscribirse')) {
                    const id = e.target.getAttribute('data-id');
                    try {
                        const res = await fetch('service/requisitos.php?evento_id=' + id);
                        const data = await res.json();

                        if (data.success) {
                            const { curso, requisitos } = data;
                            modalTituloCurso.textContent = curso.nombre_evento;
                            modalPonente.textContent = curso.ponentes;
                            modalFechas.textContent = `${curso.fecha_inicio} al ${curso.fecha_fin}`;
                            modalHoras.textContent = curso.horas;

                            if (curso.cupos_disponibles <= 0) {
                                modalCupos.innerHTML = `<span class="text-danger">${curso.cupos} (CURSO LLENO)</span>`;
                            } else if (curso.cupos_disponibles <= 5) {
                                modalCupos.innerHTML = `<span class="text-warning">${curso.cupos_disponibles}/${curso.cupos} (Pocos cupos)</span>`;
                            } else {
                                modalCupos.textContent = `${curso.cupos_disponibles}/${curso.cupos}`;
                            }

                            const imageUrl = curso.ruta_imagen?.replace(/^(\.\.\/)+/, '') || 'resource/placeholder.svg';
                            if (imageUrl !== 'resource/placeholder.svg') {
                                modalImagenCurso.src = imageUrl;
                                modalImagenCurso.style.display = 'block';
                                modalImagePlaceholder.style.display = 'none';
                            } else {
                                modalImagenCurso.src = '';
                                modalImagenCurso.style.display = 'none';
                                modalImagePlaceholder.style.display = 'flex';
                            }

                            let html = '';
                            let requiereTexto = false;
                            camposTextoDinamicos.innerHTML = '';

                            requisitos.forEach(r => {
                                if (r.tipo === 'texto' && !r.cumplido) {
                                    requiereTexto = true;
                                    camposTextoDinamicos.innerHTML += `
                                    <div class="mb-3">
                                        <label for="req_texto_${r.id}" class="form-label">${r.descripcion}</label>
                                        <textarea class="form-control-custom" id="req_texto_${r.id}" rows="3" data-requisito-id="${r.id}"></textarea>
                                    </div>`;
                                }

                                let detalle = '';
                                if (r.tipo === 'archivo') {
                                    detalle = r.cumplido
                                        ? ' (documento cargado en tu perfil)'
                                        : ' (debes cargar este documento en tu perfil)';
                                } else if (r.tipo === 'texto') {
                                    detalle = r.cumplido
                                        ? ' (ya entregado)'
                                        : ' (debes ingresar este texto)';
                                }

                                html += `<li class="list-item-custom">${r.cumplido ? '✅' : '❌'} ${r.descripcion}${detalle}</li>`;
                            });

                            listaRequisitos.innerHTML = html || '<li class="list-item-custom text-muted">Sin requisitos</li>';
                            camposTextoDinamicos.style.display = requiereTexto ? 'block' : 'none';
                            btnConfirmarInscripcion.setAttribute('data-id', id);
                            requisitosGlobales = requisitos;
                            modalInscripcion.classList.add('show-modal');
                        } else {
                            alert(data.message || 'Error al cargar requisitos del curso.');
                        }
                    } catch (error) {
                        console.error('Error al obtener requisitos:', error);
                        alert('No se pudo cargar la información del curso.');
                    }
                }

                if (e.target.classList.contains('close-button') || e.target.classList.contains('btn-cancel') || e.target === modalInscripcion) {
                    modalInscripcion.classList.remove('show-modal');
                }
            });

            btnConfirmarInscripcion.addEventListener('click', async () => {
                const eventoId = btnConfirmarInscripcion.getAttribute('data-id');
                const textosRequisitos = {};

                // Validar y recolectar requisitos tipo texto no cumplidos
                let incompletos = 0;
                requisitosGlobales.forEach(r => {
                    if (r.tipo === 'texto' && !r.cumplido) {
                        const campo = document.querySelector(`#req_texto_${r.id}`);
                        const valor = campo?.value.trim();
                        if (!valor) incompletos++;
                        else textosRequisitos[r.id] = valor;
                    }
                });

                if (incompletos > 0) {
                    alert('Debes completar todos los requisitos de texto antes de inscribirte.');
                    return;
                }

                try {
                    const res = await fetch('estudiantes/inscribirse_evento.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            evento_id: eventoId,
                            textos_requisitos: textosRequisitos
                        })
                    });

                    const result = await res.json();
                    alert(result.message);
                    if (result.success) location.reload();
                } catch (error) {
                    console.error('Error al confirmar inscripción:', error);
                    alert('Error al procesar la inscripción.');
                } finally {
                    modalInscripcion.classList.remove('show-modal');
                }
            });
        });
    </script>


</body>

</html>