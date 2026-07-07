/**
 * Portafolio del docente: tarjetas de Sílabo/Evidencias (subida rápida), Sesión de
 * Aprendizaje (gestor de 3 columnas) y accesos a Asistencia/Notas (gestores completos,
 * los mismos que /docente/asistencia y /docente/notas, embebidos aquí con sus propios
 * scripts ya existentes).
 */
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const TIPOS_PORTAFOLIO = [
    { valor: 'SILABO', etiqueta: 'Sílabo', icono: 'bi-file-earmark-text', color: 'red' },
    { valor: 'EVIDENCIA', etiqueta: 'Evidencias de Aprendizaje', icono: 'bi-folder2', color: 'navy' },
];

const COLOR_VAR = { red: 'var(--red-alert)', teal: 'var(--teal)', gold: 'var(--gold)', navy: 'var(--navy)' };
const PALETA_CURSOS = ['teal', 'navy', 'gold', 'red'];

let cursosCache = [];
let documentosCache = [];

function formatearFechaDoc(fecha) {
    return new Date(fecha).toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function cursoActivo() {
    return document.getElementById('doc-portafolio-curso')?.value ?? '';
}

function renderCardTipo(tipo, idCurso, curso) {
    const documentosTipo = documentosCache
        .filter((d) => d.tipo === tipo.valor)
        .sort((a, b) => new Date(b.fecha_subida) - new Date(a.fecha_subida));
    const ultimo = documentosTipo[0];

    let detalle;
    let badgeClase;
    let badgeIcono;
    let badgeTexto;

    if (!ultimo) {
        detalle = `Pendiente de subir · Requiere ${tipo.etiqueta.toLowerCase()}`;
        badgeClase = 'c-badge-red';
        badgeIcono = 'bi-exclamation-triangle-fill';
        badgeTexto = 'Pendiente';
    } else if (ultimo.estado === 'APROBADO') {
        detalle = `Subido ${formatearFechaDoc(ultimo.fecha_subida)}`;
        badgeClase = 'c-badge-green';
        badgeIcono = 'bi-check-circle-fill';
        badgeTexto = 'Aprobado';
    } else if (ultimo.estado === 'OBSERVADO') {
        detalle = `Subido ${formatearFechaDoc(ultimo.fecha_subida)}`;
        badgeClase = 'c-badge-red';
        badgeIcono = 'bi-pencil-fill';
        badgeTexto = 'Observado';
    } else {
        detalle = `Subido ${formatearFechaDoc(ultimo.fecha_subida)}`;
        badgeClase = 'c-badge-gold';
        badgeIcono = 'bi-clock-history';
        badgeTexto = 'En revisión';
    }

    const listaArchivos = documentosTipo.length
        ? documentosTipo.map((d) => `
            <div class="coord-mi-portafolio-card-lista-item">
                <span>${d.titulo}</span>
                <span>${formatearFechaDoc(d.fecha_subida)}</span>
            </div>
        `).join('')
        : '<div class="coord-mi-portafolio-card-lista-item">Todavía no subiste archivos.</div>';

    return `
        <div class="coord-mi-portafolio-card" style="--card-color:${COLOR_VAR[tipo.color]}">
            <div class="coord-mi-portafolio-card-head">
                <div class="coord-mi-portafolio-card-icon"><i class="bi ${tipo.icono}"></i></div>
                <div>
                    <div class="coord-mi-portafolio-card-titulo">${tipo.etiqueta}</div>
                    <div class="coord-mi-portafolio-card-subtitulo">${curso?.nombre_curso ?? ''}</div>
                </div>
            </div>
            <div class="coord-mi-portafolio-card-detalle">${detalle}</div>
            <div class="coord-mi-portafolio-card-badge"><span class="c-badge ${badgeClase}"><i class="bi ${badgeIcono}"></i> ${badgeTexto}</span></div>
            <button type="button" class="c-btn c-btn-primary" data-subir-tipo="${tipo.valor}" data-id-curso="${idCurso}">
                <i class="bi bi-cloud-upload"></i> Subir
            </button>
            <button type="button" class="coord-mi-portafolio-card-archivos" data-ver-archivos="${tipo.valor}">
                <span><i class="bi bi-folder2"></i> Archivos Subidos</span>
                <i class="bi bi-chevron-down"></i>
            </button>
            <div class="coord-mi-portafolio-card-lista" id="doc-portafolio-lista-${tipo.valor}">${listaArchivos}</div>
        </div>
    `;
}

function renderCardSesiones(idCurso, curso) {
    return `
        <div class="coord-mi-portafolio-card" style="--card-color:${COLOR_VAR.teal}">
            <div class="coord-mi-portafolio-card-head">
                <div class="coord-mi-portafolio-card-icon"><i class="bi bi-journal-code"></i></div>
                <div>
                    <div class="coord-mi-portafolio-card-titulo">Sesión de Aprendizaje</div>
                    <div class="coord-mi-portafolio-card-subtitulo">${curso?.nombre_curso ?? ''}</div>
                </div>
            </div>
            <div class="coord-mi-portafolio-card-detalle" id="doc-portafolio-sesiones-detalle">Cargando…</div>
            <div class="coord-mi-portafolio-card-badge" id="doc-portafolio-sesiones-badge"></div>
            <button type="button" class="c-btn c-btn-primary" data-abrir-sesiones="1">
                <i class="bi bi-cloud-upload"></i> Subir
            </button>
            <button type="button" class="coord-mi-portafolio-card-archivos" data-abrir-sesiones="1">
                <span><i class="bi bi-folder2"></i> Archivos Subidos</span>
                <i class="bi bi-chevron-down"></i>
            </button>
        </div>
    `;
}

function renderCardAsistencia(idCurso, curso) {
    return `
        <div class="coord-mi-portafolio-card" style="--card-color:${COLOR_VAR.gold}">
            <div class="coord-mi-portafolio-card-head">
                <div class="coord-mi-portafolio-card-icon"><i class="bi bi-calendar-check"></i></div>
                <div>
                    <div class="coord-mi-portafolio-card-titulo">Registro de Asistencia</div>
                    <div class="coord-mi-portafolio-card-subtitulo">${curso?.nombre_curso ?? ''}</div>
                </div>
            </div>
            <div class="coord-mi-portafolio-card-detalle" id="doc-portafolio-asistencia-detalle">Cargando…</div>
            <div class="coord-mi-portafolio-card-badge" id="doc-portafolio-asistencia-badge"></div>
            <button type="button" class="c-btn c-btn-primary" data-abrir-asistencia="1">
                <i class="bi bi-pencil-square"></i> Ingresar
            </button>
        </div>
    `;
}

function renderCardNotas(idCurso, curso) {
    return `
        <div class="coord-mi-portafolio-card" style="--card-color:${COLOR_VAR.navy}">
            <div class="coord-mi-portafolio-card-head">
                <div class="coord-mi-portafolio-card-icon"><i class="bi bi-clipboard-data"></i></div>
                <div>
                    <div class="coord-mi-portafolio-card-titulo">Registro de Notas</div>
                    <div class="coord-mi-portafolio-card-subtitulo">${curso?.nombre_curso ?? ''}</div>
                </div>
            </div>
            <div class="coord-mi-portafolio-card-detalle" id="doc-portafolio-notas-detalle">Cargando…</div>
            <div class="coord-mi-portafolio-card-badge" id="doc-portafolio-notas-badge"></div>
            <button type="button" class="c-btn c-btn-primary" data-abrir-notas="1">
                <i class="bi bi-pencil-square"></i> Ingresar
            </button>
        </div>
    `;
}

function actualizarResumenSesiones(idCurso) {
    fetch(`/api/docente/sesiones?id_curso=${idCurso}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const sesiones = data.sesiones ?? [];
            const detalle = document.getElementById('doc-portafolio-sesiones-detalle');
            const badge = document.getElementById('doc-portafolio-sesiones-badge');
            if (!detalle || !badge) return;

            if (!sesiones.length) {
                detalle.textContent = 'Pendiente de subir · Requiere sesión de aprendizaje';
                badge.innerHTML = '<span class="c-badge c-badge-red"><i class="bi bi-exclamation-triangle-fill"></i> Pendiente</span>';
            } else {
                const ultima = [...sesiones].sort((a, b) => new Date(b.fecha_subida) - new Date(a.fecha_subida))[0];
                detalle.textContent = `Subido ${formatearFechaDoc(ultima.fecha_subida)}`;
                badge.innerHTML = `<span class="c-badge c-badge-green"><i class="bi bi-check-circle-fill"></i> ${sesiones.length} ${sesiones.length === 1 ? 'sesión' : 'sesiones'}</span>`;
            }
        })
        .catch(() => {
            const detalle = document.getElementById('doc-portafolio-sesiones-detalle');
            if (detalle) detalle.textContent = 'No se pudo cargar.';
        });
}

function actualizarResumenAsistencia(idCurso) {
    const hoy = new Date().toISOString().slice(0, 10);

    fetch(`/api/docente/asistencia?id_curso=${idCurso}&fecha=${hoy}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const estudiantes = data.estudiantes ?? [];
            const detalle = document.getElementById('doc-portafolio-asistencia-detalle');
            const badge = document.getElementById('doc-portafolio-asistencia-badge');
            if (!detalle || !badge) return;

            const tomada = estudiantes.some((e) => e.estado !== null);

            if (!estudiantes.length) {
                detalle.textContent = 'Aún no hay estudiantes matriculados en este curso.';
                badge.innerHTML = '<span class="c-badge c-badge-red"><i class="bi bi-exclamation-triangle-fill"></i> Pendiente</span>';
            } else if (!tomada) {
                detalle.textContent = 'Pendiente de ingresar · Requiere asistencia hoy';
                badge.innerHTML = '<span class="c-badge c-badge-red"><i class="bi bi-exclamation-triangle-fill"></i> Pendiente</span>';
            } else {
                detalle.textContent = `${data.resumen.presentes} presente(s) hoy`;
                badge.innerHTML = '<span class="c-badge c-badge-green"><i class="bi bi-check-circle-fill"></i> Tomada</span>';
            }
        })
        .catch(() => {
            const detalle = document.getElementById('doc-portafolio-asistencia-detalle');
            if (detalle) detalle.textContent = 'No se pudo cargar.';
        });
}

function actualizarResumenNotas(idCurso) {
    fetch(`/api/docente/notas?id_curso=${idCurso}&unidad=I`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const estudiantes = data.estudiantes ?? [];
            const conNota = estudiantes.filter((e) => e.nota?.practica != null || e.nota?.teoria != null || e.nota?.examen != null);
            const detalle = document.getElementById('doc-portafolio-notas-detalle');
            const badge = document.getElementById('doc-portafolio-notas-badge');
            if (!detalle || !badge) return;

            if (!estudiantes.length) {
                detalle.textContent = 'Aún no hay estudiantes matriculados en este curso.';
                badge.innerHTML = '<span class="c-badge c-badge-red"><i class="bi bi-exclamation-triangle-fill"></i> Pendiente</span>';
            } else if (!conNota.length) {
                detalle.textContent = `Pendiente de ingresar · ${estudiantes.length} estudiante(s)`;
                badge.innerHTML = '<span class="c-badge c-badge-red"><i class="bi bi-exclamation-triangle-fill"></i> Pendiente</span>';
            } else {
                detalle.textContent = `${conNota.length} de ${estudiantes.length} estudiantes con nota (Unidad I)`;
                badge.innerHTML = `<span class="c-badge c-badge-green"><i class="bi bi-check-circle-fill"></i> ${conNota.length}/${estudiantes.length}</span>`;
            }
        })
        .catch(() => {
            const detalle = document.getElementById('doc-portafolio-notas-detalle');
            if (detalle) detalle.textContent = 'No se pudo cargar.';
        });
}

function renderGridPortafolio() {
    const grid = document.getElementById('doc-portafolio-grid');
    const select = document.getElementById('doc-portafolio-curso');
    if (!grid || !select) return;

    const idCurso = select.value;
    const curso = { nombre_curso: select.selectedOptions[0]?.textContent ?? '' };

    if (!idCurso) {
        grid.innerHTML = '<p class="coord-portafolio-empty">Aún no tienes cursos asignados.</p>';

        return;
    }

    grid.innerHTML = renderCardTipo(TIPOS_PORTAFOLIO[0], idCurso, curso)
        + renderCardSesiones(idCurso, curso)
        + renderCardAsistencia(idCurso, curso)
        + renderCardNotas(idCurso, curso)
        + renderCardTipo(TIPOS_PORTAFOLIO[1], idCurso, curso);

    actualizarResumenSesiones(idCurso);
    actualizarResumenAsistencia(idCurso);
    actualizarResumenNotas(idCurso);

    grid.querySelectorAll('[data-subir-tipo]').forEach((btn) => {
        btn.addEventListener('click', () => iniciarSubida(btn.dataset.subirTipo, btn.dataset.idCurso));
    });
    grid.querySelectorAll('[data-abrir-sesiones]').forEach((btn) => btn.addEventListener('click', abrirGestorSesiones));
    grid.querySelectorAll('[data-abrir-asistencia]').forEach((btn) => btn.addEventListener('click', abrirGestorAsistenciaEmbebido));
    grid.querySelectorAll('[data-abrir-notas]').forEach((btn) => btn.addEventListener('click', abrirGestorNotasEmbebido));

    grid.querySelectorAll('[data-ver-archivos]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const lista = document.getElementById(`doc-portafolio-lista-${btn.dataset.verArchivos}`);
            lista.classList.toggle('show');
            btn.querySelector('.bi-chevron-down, .bi-chevron-up')?.classList.toggle('bi-chevron-up');
            btn.querySelector('.bi-chevron-down, .bi-chevron-up')?.classList.toggle('bi-chevron-down');
        });
    });
}

function cargarPortafolio() {
    const idCurso = cursoActivo();
    const errorBox = document.getElementById('doc-portafolio-error');
    errorBox.textContent = '';

    if (!idCurso) {
        renderGridPortafolio();

        return;
    }

    fetch(`/api/docente/portafolio?id_curso=${idCurso}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            documentosCache = data.documentos ?? [];
            renderGridPortafolio();
        })
        .catch(() => {
            errorBox.textContent = 'No se pudo cargar tu portafolio.';
        });
}

/* --- Subida rapida de Sílabo/Evidencias (modal), igual al de Coordinador > Mi Portafolio. --- */
let subidaActiva = null;

function iniciarSubida(tipo, idCurso) {
    subidaActiva = { tipo, idCurso };
    abrirModalSubida();
}

function actualizarSemestreModalSubida() {
    const cursoSelect = document.getElementById('doc-portafolio-curso');
    const modalSemestre = document.getElementById('doc-portafolio-upload-semestre');
    const opcion = Array.from(cursoSelect.options).find((o) => o.value === subidaActiva.idCurso);
    const semestre = opcion?.dataset.semestre ?? '';

    modalSemestre.innerHTML = semestre ? `<option value="${semestre}">${semestre}</option>` : '<option value="">—</option>';
}

function abrirModalSubida() {
    const cursoSelect = document.getElementById('doc-portafolio-curso');
    const modalCurso = document.getElementById('doc-portafolio-upload-curso');

    modalCurso.innerHTML = cursoSelect.innerHTML;
    modalCurso.value = subidaActiva.idCurso;
    modalCurso.onchange = () => {
        subidaActiva.idCurso = modalCurso.value;
        actualizarSemestreModalSubida();
    };
    actualizarSemestreModalSubida();

    document.getElementById('doc-portafolio-upload-archivo').value = '';
    document.getElementById('doc-portafolio-upload-error').textContent = '';
    document.getElementById('doc-portafolio-upload-modal').classList.add('show');
}

function cerrarModalSubida() {
    document.getElementById('doc-portafolio-upload-modal').classList.remove('show');
    subidaActiva = null;
}

function confirmarSubidaArchivo() {
    const errorBox = document.getElementById('doc-portafolio-upload-error');
    errorBox.textContent = '';

    const archivo = document.getElementById('doc-portafolio-upload-archivo').files[0];
    if (!archivo) {
        errorBox.textContent = 'Selecciona un archivo.';

        return;
    }
    if (!subidaActiva) return;

    const idPeriodo = document.getElementById('doc-portafolio-curso').dataset.idPeriodo;
    const tipoInfo = TIPOS_PORTAFOLIO.find((t) => t.valor === subidaActiva.tipo);
    const cursoTexto = document.getElementById('doc-portafolio-upload-curso').selectedOptions[0]?.textContent ?? '';

    const formData = new FormData();
    formData.append('documento', archivo);
    formData.append('id_curso', subidaActiva.idCurso);
    formData.append('id_periodo', idPeriodo);
    formData.append('tipo', subidaActiva.tipo);
    formData.append('titulo', `${tipoInfo?.etiqueta ?? subidaActiva.tipo} - ${cursoTexto}`);

    fetch('/api/docente/portafolio', {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: formData,
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then(() => {
            cerrarModalSubida();
            cargarPortafolio();
        })
        .catch((error) => {
            errorBox.textContent = error?.mensaje ?? (error?.errors ? Object.values(error.errors).flat().join(' ') : 'No se pudo subir el documento.');
        });
}

/* --- Gestor de Sesiones de Aprendizaje (3 columnas): Acciones / Cursos / Sesiones. --- */
let sesionesCursos = [];
let sesionesCursoActivo = null;
let sesionSeleccionada = null;

function mostrarSoloPanel(idPanel) {
    document.getElementById('doc-portafolio-grid-wrap').style.display = idPanel === null ? 'block' : 'none';
    ['doc-sesiones-manager', 'doc-portafolio-asistencia-manager', 'doc-portafolio-notas-manager'].forEach((id) => {
        document.getElementById(id).style.display = id === idPanel ? 'block' : 'none';
    });
}

function abrirGestorSesiones() {
    mostrarSoloPanel('doc-sesiones-manager');
    cargarCursosParaSesiones();
}

function cerrarGestorSesiones() {
    mostrarSoloPanel(null);
    cargarPortafolio();
}

function cargarCursosParaSesiones() {
    sesionesCursos = Array.from(document.getElementById('doc-portafolio-curso').options)
        .filter((o) => o.value)
        .map((o) => ({ id_curso: o.value, nombre_curso: o.textContent }));

    sesionesCursoActivo = null;
    sesionSeleccionada = null;
    renderListaCursosSesiones();
    renderListaSesiones();

    if (sesionesCursos.length) seleccionarCursoSesiones(sesionesCursos[0].id_curso);
}

function renderListaCursosSesiones() {
    const root = document.getElementById('doc-sesiones-lista-cursos');

    root.innerHTML = sesionesCursos.length
        ? sesionesCursos.map((c, i) => `
            <div class="coord-sesiones-curso-item ${c.id_curso === sesionesCursoActivo ? 'active' : ''}" data-id-curso="${c.id_curso}" style="--item-color:${COLOR_VAR[PALETA_CURSOS[i % PALETA_CURSOS.length]]}">
                <div class="coord-sesiones-curso-icono"><i class="bi bi-code-slash"></i></div>
                <div class="coord-sesiones-curso-nombre">${c.nombre_curso}</div>
            </div>
        `).join('')
        : '<p class="coord-portafolio-empty">Aún no tienes cursos asignados.</p>';

    root.querySelectorAll('[data-id-curso]').forEach((el) => {
        el.addEventListener('click', () => seleccionarCursoSesiones(el.dataset.idCurso));
    });
}

function seleccionarCursoSesiones(idCurso) {
    sesionesCursoActivo = idCurso;
    sesionSeleccionada = null;
    const curso = sesionesCursos.find((c) => c.id_curso === idCurso);

    document.getElementById('doc-sesiones-seleccionado').style.display = 'block';
    document.getElementById('doc-sesiones-curso-actual').textContent = curso?.nombre_curso ?? '';
    document.getElementById('doc-sesiones-subir').disabled = false;
    document.getElementById('doc-sesiones-eliminar').disabled = true;

    renderListaCursosSesiones();
    recargarSesionesCursoActivo();
}

let sesionesCacheActual = [];

function recargarSesionesCursoActivo() {
    if (!sesionesCursoActivo) return;

    fetch(`/api/docente/sesiones?id_curso=${sesionesCursoActivo}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            sesionesCacheActual = data.sesiones ?? [];
            renderListaSesiones();
        })
        .catch(() => {
            document.getElementById('doc-sesiones-lista-items').innerHTML = '<div class="coord-sesiones-lista-vacia">No se pudo cargar.</div>';
        });
}

function renderListaSesiones() {
    const titulo = document.getElementById('doc-sesiones-lista-titulo');
    const root = document.getElementById('doc-sesiones-lista-items');
    const curso = sesionesCursos.find((c) => c.id_curso === sesionesCursoActivo);
    const busqueda = document.getElementById('doc-sesiones-buscar').value.trim().toLowerCase();

    if (!curso) {
        titulo.innerHTML = '<i class="bi bi-list"></i> Seleccione un curso';
        root.innerHTML = '';

        return;
    }

    titulo.innerHTML = `<i class="bi bi-list"></i> ${curso.nombre_curso}`;

    const filtradas = sesionesCacheActual.filter((s) => !busqueda || s.titulo.toLowerCase().includes(busqueda));

    root.innerHTML = filtradas.length
        ? filtradas.map((s) => `
            <div class="coord-sesiones-item ${s.id_sesion === sesionSeleccionada ? 'selected' : ''}" data-id-sesion="${s.id_sesion}">
                <div>
                    <strong>${s.titulo}</strong>
                    <div class="coord-sesiones-item-fecha">${s.numero_sesion ? `Sesión ${s.numero_sesion} · ` : ''}${formatearFechaDoc(s.fecha_subida)}</div>
                </div>
                <span class="c-badge ${s.estado === 'APROBADO' ? 'c-badge-green' : s.estado === 'RECHAZADO' ? 'c-badge-red' : 'c-badge-gold'}">${s.estado}</span>
            </div>
        `).join('')
        : '<div class="coord-sesiones-lista-vacia">No hay sesiones de aprendizaje subidas para este curso.</div>';

    root.querySelectorAll('[data-id-sesion]').forEach((el) => {
        el.addEventListener('click', () => {
            sesionSeleccionada = sesionSeleccionada === Number(el.dataset.idSesion) ? null : Number(el.dataset.idSesion);
            document.getElementById('doc-sesiones-eliminar').disabled = !sesionSeleccionada;
            renderListaSesiones();
        });
    });
}

function iniciarSubidaSesion() {
    if (!sesionesCursoActivo) return;
    document.getElementById('doc-sesiones-input-archivo').click();
}

function subirArchivoSesion(event) {
    const archivo = event.target.files[0];
    event.target.value = '';
    if (!archivo || !sesionesCursoActivo) return;

    const errorBox = document.getElementById('doc-sesiones-error');
    errorBox.textContent = '';

    const numero = sesionesCacheActual.length + 1;
    const cursoTexto = sesionesCursos.find((c) => c.id_curso === sesionesCursoActivo)?.nombre_curso ?? '';

    const formData = new FormData();
    formData.append('archivo', archivo);
    formData.append('id_curso', sesionesCursoActivo);
    formData.append('titulo', `Sesión ${numero} - ${cursoTexto}`);
    formData.append('numero_sesion', numero);

    fetch('/api/docente/sesiones', {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: formData,
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then(() => recargarSesionesCursoActivo())
        .catch((error) => {
            errorBox.textContent = error?.mensaje ?? (error?.errors ? Object.values(error.errors).flat().join(' ') : 'No se pudo subir la sesión.');
        });
}

function eliminarSesionSeleccionada() {
    if (!sesionSeleccionada) return;
    if (!confirm('¿Eliminar esta sesión de aprendizaje?')) return;

    fetch(`/api/docente/sesiones/${sesionSeleccionada}`, {
        method: 'DELETE',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then(() => {
            sesionSeleccionada = null;
            document.getElementById('doc-sesiones-eliminar').disabled = true;
            recargarSesionesCursoActivo();
        })
        .catch((error) => {
            document.getElementById('doc-sesiones-error').textContent = error?.mensaje ?? 'No se pudo eliminar la sesión.';
        });
}

/* --- Mostrar/ocultar los gestores de Asistencia y Notas ya embebidos (mismo HTML/JS que sus paginas propias). --- */
function abrirGestorAsistenciaEmbebido() {
    mostrarSoloPanel('doc-portafolio-asistencia-manager');
}

function abrirGestorNotasEmbebido() {
    mostrarSoloPanel('doc-portafolio-notas-manager');
}

export function initDocentePortafolio() {
    const select = document.getElementById('doc-portafolio-curso');
    if (!select) return;

    cargarPortafolio();

    select.addEventListener('change', cargarPortafolio);

    document.getElementById('doc-portafolio-upload-cancelar')?.addEventListener('click', cerrarModalSubida);
    document.getElementById('doc-portafolio-upload-confirmar')?.addEventListener('click', confirmarSubidaArchivo);

    document.getElementById('doc-sesiones-volver')?.addEventListener('click', cerrarGestorSesiones);
    document.getElementById('doc-sesiones-subir')?.addEventListener('click', iniciarSubidaSesion);
    document.getElementById('doc-sesiones-eliminar')?.addEventListener('click', eliminarSesionSeleccionada);
    document.getElementById('doc-sesiones-input-archivo')?.addEventListener('change', subirArchivoSesion);

    let debounceSesiones;
    document.getElementById('doc-sesiones-buscar')?.addEventListener('input', () => {
        clearTimeout(debounceSesiones);
        debounceSesiones = setTimeout(renderListaSesiones, 250);
    });

    document.getElementById('doc-portafolio-asistencia-volver')?.addEventListener('click', () => mostrarSoloPanel(null));
    document.getElementById('doc-portafolio-notas-volver')?.addEventListener('click', () => mostrarSoloPanel(null));
}

document.addEventListener('DOMContentLoaded', initDocentePortafolio);
