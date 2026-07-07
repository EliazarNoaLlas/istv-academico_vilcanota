/**
 * Interaccion visual + fetch de coordinador/portafolio. La decision de
 * aprobar/observar la valida el backend (PortafolioReviewService); este
 * archivo solo lista, filtra y muestra el resultado del analisis IA (stub
 * de la Fase 6, sin llamar todavia a Groq de verdad).
 */
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const BADGE_ESTADO = {
    PENDIENTE: 'c-badge-navy',
    SUBIDO: 'c-badge-gold',
    APROBADO: 'c-badge-green',
    OBSERVADO: 'c-badge-red',
};

let documentosCache = [];

function badgeEstado(doc) {
    return `<span class="c-badge ${BADGE_ESTADO[doc.estado] ?? 'c-badge-navy'}">${doc.estado}</span>`;
}

function renderRow(doc) {
    const docente = doc.portafolio?.docente ? `${doc.portafolio.docente.usuario?.nombres ?? ''} ${doc.portafolio.docente.usuario?.apellidos ?? ''}` : '—';
    const curso = doc.portafolio?.curso?.nombre_curso ?? '—';

    return `
        <tr>
            <td>${doc.titulo}</td>
            <td>${docente}</td>
            <td>${curso}</td>
            <td>${doc.tipo}</td>
            <td>${badgeEstado(doc)}</td>
            <td><button type="button" class="c-btn c-btn-outline c-btn-sm" data-revisar="${doc.id_documento}">Revisar</button></td>
        </tr>
    `;
}

let cursosDisponibles = [];

/** Captura una sola vez las opciones reales del select (antes de reescribirlo). */
function capturarCursosDisponibles() {
    const select = document.getElementById('coord-portafolio-filtro-curso');

    cursosDisponibles = Array.from(select.querySelectorAll('option[value]:not([value=""])')).map((opt) => ({
        value: opt.value,
        texto: opt.textContent,
        idDocente: opt.dataset.idDocente,
        semestre: opt.dataset.semestre,
    }));
}

/** Al elegir un docente, el select de curso se limita a lo que ese docente tiene asignado, agrupado por semestre. */
function actualizarCursosPorDocente() {
    const idDocente = document.getElementById('coord-portafolio-filtro-docente')?.value;
    const select = document.getElementById('coord-portafolio-filtro-curso');
    const cursos = idDocente ? cursosDisponibles.filter((c) => c.idDocente === idDocente) : cursosDisponibles;

    const porSemestre = cursos.reduce((grupos, curso) => {
        const clave = curso.semestre || 'Sin semestre';
        (grupos[clave] ??= []).push(curso);

        return grupos;
    }, {});

    const grupos = Object.keys(porSemestre)
        .sort()
        .map((semestre) => `
            <optgroup label="Semestre ${semestre}">
                ${porSemestre[semestre].map((c) => `<option value="${c.value}">${c.texto}</option>`).join('')}
            </optgroup>
        `)
        .join('');

    select.innerHTML = `<option value="">Todos los cursos</option>${grupos}`;
}

function filtrosActuales() {
    const params = {};
    const docente = document.getElementById('coord-portafolio-filtro-docente')?.value;
    const curso = document.getElementById('coord-portafolio-filtro-curso')?.value;
    const estado = document.getElementById('coord-portafolio-filtro-estado')?.value;
    if (docente) params.id_docente = docente;
    if (curso) params.id_curso = curso;
    if (estado) params.estado = estado;

    return params;
}

function cargarKpis() {
    const root = document.getElementById('coord-portafolio-kpis');
    if (!root) return;

    fetch('/api/coordinador/portafolios', { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const documentos = data.documentos ?? [];
            root.querySelector('[data-kpi="total"]').textContent = documentos.length;
            root.querySelector('[data-kpi="aprobados"]').textContent = documentos.filter((d) => d.estado === 'APROBADO').length;
            root.querySelector('[data-kpi="observados"]').textContent = documentos.filter((d) => d.estado === 'OBSERVADO').length;
            root.querySelector('[data-kpi="pendientes"]').textContent = documentos.filter((d) => ['PENDIENTE', 'SUBIDO'].includes(d.estado)).length;
        })
        .catch((error) => console.error(error));
}

function cargarDocumentos() {
    const tbody = document.getElementById('coord-portafolio-tbody');
    const query = new URLSearchParams(filtrosActuales()).toString();

    fetch(`/api/coordinador/portafolios${query ? `?${query}` : ''}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            documentosCache = data.documentos ?? [];

            tbody.innerHTML = documentosCache.length
                ? documentosCache.map(renderRow).join('')
                : '<tr><td colspan="6" class="coord-portafolio-empty">No hay documentos para este filtro.</td></tr>';

            wireRevisarButtons();
        })
        .catch((error) => {
            tbody.innerHTML = '<tr><td colspan="6" class="coord-portafolio-empty">No se pudo cargar el portafolio.</td></tr>';
            console.error(error);
        });
}

function wireRevisarButtons() {
    document.querySelectorAll('[data-revisar]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const doc = documentosCache.find((d) => String(d.id_documento) === btn.dataset.revisar);
            if (doc) abrirModal(doc);
        });
    });
}

let documentoActivo = null;

function abrirModal(doc) {
    documentoActivo = doc;
    document.getElementById('coord-portafolio-modal-title').textContent = doc.titulo;
    document.getElementById('coord-portafolio-modal-sub').textContent = `${doc.tipo} — estado actual: ${doc.estado}`;
    document.getElementById('coord-portafolio-observacion').value = doc.observacion ?? '';
    document.getElementById('coord-portafolio-ia-resultado').textContent = '';
    document.getElementById('coord-portafolio-modal').classList.add('show');
}

function cerrarModal() {
    document.getElementById('coord-portafolio-modal').classList.remove('show');
    documentoActivo = null;
}

function analizarIA() {
    if (!documentoActivo) return;
    const nota = document.getElementById('coord-portafolio-ia-resultado');
    nota.textContent = 'Analizando…';

    fetch(`/api/portafolios/${documentoActivo.id_documento}/analizar`, {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
        .then((res) => res.json())
        .then((data) => { nota.textContent = data.mensaje; })
        .catch(() => { nota.textContent = 'No se pudo analizar el documento.'; });
}

function validar(estado) {
    if (!documentoActivo) return;
    const observacion = document.getElementById('coord-portafolio-observacion').value;

    fetch(`/api/portafolios/${documentoActivo.id_documento}/validar`, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ estado, observacion }),
    })
        .then((res) => res.json())
        .then(() => {
            cerrarModal();
            cargarDocumentos();
            cargarKpis();
        })
        .catch((error) => console.error(error));
}

function cambiarTab(nombre) {
    document.querySelectorAll('.c-tab-btn').forEach((btn) => btn.classList.toggle('active', btn.dataset.tab === nombre));
    document.querySelectorAll('.c-tab-pane').forEach((pane) => pane.classList.toggle('active', pane.id === `tab-${nombre}`));

    if (nombre === 'mio') cargarMiPortafolio();
}

const TIPOS_PORTAFOLIO = [
    { valor: 'SILABO', etiqueta: 'Sílabos', icono: 'bi-file-earmark-text', color: 'red' },
    { valor: 'EVIDENCIA', etiqueta: 'Evidencias de Aprendizaje', icono: 'bi-folder2', color: 'navy' },
];

const COLOR_VAR = { red: 'var(--red-alert)', teal: 'var(--teal)', gold: 'var(--gold)', navy: 'var(--navy)' };
const PALETA_CURSOS = ['teal', 'navy', 'gold', 'red'];

let misDocumentosCache = [];

function formatearFecha(fecha) {
    return new Date(fecha).toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

/** La tarjeta de sesiones no usa el sistema generico de portafolio_documentos:
 *  las sesiones tienen su propia tabla/gestor (ver mas abajo), por eso abre
 *  el panel de 3 columnas en vez de subir un archivo suelto. */
function renderCardSesiones(idCurso, curso, periodo) {
    return `
        <div class="coord-mi-portafolio-card" style="--card-color:${COLOR_VAR.teal}">
            <div class="coord-mi-portafolio-card-head">
                <div class="coord-mi-portafolio-card-icon"><i class="bi bi-journal-code"></i></div>
                <div>
                    <div class="coord-mi-portafolio-card-titulo">Sesión de Aprendizaje</div>
                    <div class="coord-mi-portafolio-card-subtitulo">${curso?.nombre_curso ?? ''}${periodo ? ' · ' + periodo : ''}</div>
                </div>
            </div>
            <div class="coord-mi-portafolio-card-detalle" id="sesiones-card-detalle">Cargando…</div>
            <div class="coord-mi-portafolio-card-badge" id="sesiones-card-badge"></div>
            <button type="button" class="c-btn c-btn-primary" data-abrir-gestor-sesiones="1">
                <i class="bi bi-cloud-upload"></i> Subir
            </button>
            <button type="button" class="coord-mi-portafolio-card-archivos" data-abrir-gestor-sesiones="1">
                <span><i class="bi bi-folder2"></i> Archivos Subidos</span>
                <i class="bi bi-chevron-down"></i>
            </button>
        </div>
    `;
}

function actualizarConteoSesiones(idCurso) {
    fetch(`/api/coordinador/sesiones?id_curso=${idCurso}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const sesiones = data.sesiones ?? [];
            const detalle = document.getElementById('sesiones-card-detalle');
            const badge = document.getElementById('sesiones-card-badge');
            if (!detalle || !badge) return;

            if (!sesiones.length) {
                detalle.textContent = 'Pendiente de subir · Requiere sesión de aprendizaje';
                badge.innerHTML = '<span class="c-badge c-badge-red"><i class="bi bi-exclamation-triangle-fill"></i> Pendiente</span>';
            } else {
                const ultima = [...sesiones].sort((a, b) => new Date(b.fecha_subida) - new Date(a.fecha_subida))[0];
                detalle.textContent = `Subido ${formatearFecha(ultima.fecha_subida)}`;
                badge.innerHTML = `<span class="c-badge c-badge-green"><i class="bi bi-check-circle-fill"></i> ${sesiones.length} ${sesiones.length === 1 ? 'sesión' : 'sesiones'}</span>`;
            }
        })
        .catch(() => {
            const detalle = document.getElementById('sesiones-card-detalle');
            if (detalle) detalle.textContent = 'No se pudo cargar.';
        });
}

/** Notas y Asistencia no son documentos sueltos: se ingresan por estudiante,
 *  por eso abren un gestor de 3 columnas (como Sesiones) en vez de subir un
 *  archivo. El boton dice "Ingresar" en lugar de "Subir". */
function renderCardNotas(idCurso, curso, periodo) {
    return `
        <div class="coord-mi-portafolio-card" style="--card-color:${COLOR_VAR.gold}">
            <div class="coord-mi-portafolio-card-head">
                <div class="coord-mi-portafolio-card-icon"><i class="bi bi-clipboard-data"></i></div>
                <div>
                    <div class="coord-mi-portafolio-card-titulo">Notas</div>
                    <div class="coord-mi-portafolio-card-subtitulo">${curso?.nombre_curso ?? ''}${periodo ? ' · ' + periodo : ''}</div>
                </div>
            </div>
            <div class="coord-mi-portafolio-card-detalle" id="notas-card-detalle">Cargando…</div>
            <div class="coord-mi-portafolio-card-badge" id="notas-card-badge"></div>
            <button type="button" class="c-btn c-btn-primary" data-abrir-gestor-notas="1">
                <i class="bi bi-pencil-square"></i> Ingresar
            </button>
        </div>
    `;
}

function actualizarConteoNotas(idCurso) {
    fetch(`/api/coordinador/portafolios/notas?id_curso=${idCurso}&unidad=I`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const estudiantes = data.estudiantes ?? [];
            const conNota = estudiantes.filter((e) => e.practica !== null || e.teoria !== null || e.examen !== null);
            const detalle = document.getElementById('notas-card-detalle');
            const badge = document.getElementById('notas-card-badge');
            if (!detalle || !badge) return;

            if (!estudiantes.length) {
                detalle.textContent = 'Aún no hay estudiantes matriculados en este curso.';
                badge.innerHTML = '<span class="c-badge c-badge-red"><i class="bi bi-exclamation-triangle-fill"></i> Pendiente</span>';
            } else if (!conNota.length) {
                detalle.textContent = `Pendiente de ingresar · ${estudiantes.length} estudiante(s)`;
                badge.innerHTML = '<span class="c-badge c-badge-red"><i class="bi bi-exclamation-triangle-fill"></i> Pendiente</span>';
            } else {
                detalle.textContent = `${conNota.length} de ${estudiantes.length} estudiantes con nota (unidad I)`;
                badge.innerHTML = `<span class="c-badge c-badge-green"><i class="bi bi-check-circle-fill"></i> ${conNota.length}/${estudiantes.length}</span>`;
            }
        })
        .catch(() => {
            const detalle = document.getElementById('notas-card-detalle');
            if (detalle) detalle.textContent = 'No se pudo cargar.';
        });
}

function renderCardAsistencia(idCurso, curso, periodo) {
    return `
        <div class="coord-mi-portafolio-card" style="--card-color:${COLOR_VAR.teal}">
            <div class="coord-mi-portafolio-card-head">
                <div class="coord-mi-portafolio-card-icon"><i class="bi bi-calendar-check"></i></div>
                <div>
                    <div class="coord-mi-portafolio-card-titulo">Asistencia</div>
                    <div class="coord-mi-portafolio-card-subtitulo">${curso?.nombre_curso ?? ''}${periodo ? ' · ' + periodo : ''}</div>
                </div>
            </div>
            <div class="coord-mi-portafolio-card-detalle" id="asistencia-card-detalle">Cargando…</div>
            <div class="coord-mi-portafolio-card-badge" id="asistencia-card-badge"></div>
            <button type="button" class="c-btn c-btn-primary" data-abrir-gestor-asistencia="1">
                <i class="bi bi-pencil-square"></i> Ingresar
            </button>
        </div>
    `;
}

function actualizarConteoAsistencia(idCurso) {
    fetch(`/api/coordinador/portafolios/asistencia/sesiones?id_curso=${idCurso}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const sesiones = data.sesiones ?? [];
            const detalle = document.getElementById('asistencia-card-detalle');
            const badge = document.getElementById('asistencia-card-badge');
            if (!detalle || !badge) return;

            if (!sesiones.length) {
                detalle.textContent = 'Pendiente de ingresar · Requiere asistencia';
                badge.innerHTML = '<span class="c-badge c-badge-red"><i class="bi bi-exclamation-triangle-fill"></i> Pendiente</span>';
            } else {
                detalle.textContent = `Última sesión ${formatearFecha(sesiones[0].fecha_sesion)}`;
                badge.innerHTML = `<span class="c-badge c-badge-green"><i class="bi bi-check-circle-fill"></i> ${sesiones.length} ${sesiones.length === 1 ? 'sesión' : 'sesiones'}</span>`;
            }
        })
        .catch(() => {
            const detalle = document.getElementById('asistencia-card-detalle');
            if (detalle) detalle.textContent = 'No se pudo cargar.';
        });
}

function renderCardTipo(tipo, idCurso, curso, periodo) {
    const documentosTipo = misDocumentosCache
        .filter((d) => d.tipo === tipo.valor)
        .sort((a, b) => new Date(b.fecha_subida) - new Date(a.fecha_subida));
    const ultimo = documentosTipo[0];

    const cursoPeriodo = `${curso?.nombre_curso ?? ''}${periodo ? ' · ' + periodo : ''}`;

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
        detalle = `Subido ${formatearFecha(ultimo.fecha_subida)}`;
        badgeClase = 'c-badge-green';
        badgeIcono = 'bi-check-circle-fill';
        badgeTexto = 'Aprobado';
    } else if (ultimo.estado === 'OBSERVADO') {
        detalle = `Subido ${formatearFecha(ultimo.fecha_subida)}`;
        badgeClase = 'c-badge-red';
        badgeIcono = 'bi-pencil-fill';
        badgeTexto = 'Observado';
    } else {
        detalle = `Subido ${formatearFecha(ultimo.fecha_subida)}`;
        badgeClase = 'c-badge-gold';
        badgeIcono = 'bi-clock-history';
        badgeTexto = 'En revisión';
    }

    const listaArchivos = documentosTipo.length
        ? documentosTipo.map((d) => `
            <div class="coord-mi-portafolio-card-lista-item">
                <span>${d.titulo}</span>
                <span>${formatearFecha(d.fecha_subida)}</span>
            </div>
        `).join('')
        : '<div class="coord-mi-portafolio-card-lista-item">Todavía no subiste archivos.</div>';

    return `
        <div class="coord-mi-portafolio-card" style="--card-color:${COLOR_VAR[tipo.color]}">
            <div class="coord-mi-portafolio-card-head">
                <div class="coord-mi-portafolio-card-icon"><i class="bi ${tipo.icono}"></i></div>
                <div>
                    <div class="coord-mi-portafolio-card-titulo">${tipo.etiqueta}</div>
                    <div class="coord-mi-portafolio-card-subtitulo">${cursoPeriodo}</div>
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
            <div class="coord-mi-portafolio-card-lista" id="lista-${tipo.valor}">${listaArchivos}</div>
        </div>
    `;
}

function renderGridMiPortafolio() {
    const grid = document.getElementById('coord-mi-portafolio-grid');
    const select = document.getElementById('coord-mi-portafolio-curso');
    if (!grid || !select) return;

    const idCurso = select.value;
    const curso = { nombre_curso: select.selectedOptions[0]?.textContent ?? '' };
    const periodo = misDocumentosCache[0]?.portafolio?.periodo?.codigo ?? '';

    if (!idCurso) {
        grid.innerHTML = '<p class="coord-portafolio-empty">Aún no tienes cursos asignados como docente.</p>';
        return;
    }

    grid.innerHTML = renderCardTipo(TIPOS_PORTAFOLIO[0], idCurso, curso, periodo)
        + renderCardSesiones(idCurso, curso, periodo)
        + renderCardAsistencia(idCurso, curso, periodo)
        + renderCardNotas(idCurso, curso, periodo)
        + TIPOS_PORTAFOLIO.slice(1).map((tipo) => renderCardTipo(tipo, idCurso, curso, periodo)).join('');

    actualizarConteoSesiones(idCurso);
    actualizarConteoAsistencia(idCurso);
    actualizarConteoNotas(idCurso);

    grid.querySelectorAll('[data-subir-tipo]').forEach((btn) => {
        btn.addEventListener('click', () => iniciarSubida(btn.dataset.subirTipo, btn.dataset.idCurso));
    });

    grid.querySelectorAll('[data-abrir-gestor-sesiones]').forEach((btn) => {
        btn.addEventListener('click', abrirGestorSesiones);
    });

    grid.querySelectorAll('[data-abrir-gestor-notas]').forEach((btn) => {
        btn.addEventListener('click', abrirGestorNotas);
    });

    grid.querySelectorAll('[data-abrir-gestor-asistencia]').forEach((btn) => {
        btn.addEventListener('click', abrirGestorAsistencia);
    });

    grid.querySelectorAll('[data-ver-archivos]').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.getElementById(`lista-${btn.dataset.verArchivos}`)?.classList.toggle('show');
        });
    });
}

function cargarMiPortafolio() {
    const select = document.getElementById('coord-mi-portafolio-curso');
    if (!select) return;

    const idDocente = select.dataset.idDocente;
    const idCurso = select.value;
    const params = new URLSearchParams({ id_docente: idDocente });
    if (idCurso) params.set('id_curso', idCurso);

    fetch(`/api/coordinador/portafolios?${params}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            misDocumentosCache = data.documentos ?? [];
            renderGridMiPortafolio();
        })
        .catch((error) => {
            console.error(error);
            document.getElementById('coord-mi-portafolio-grid').innerHTML = '<p class="coord-portafolio-empty">No se pudo cargar tu portafolio.</p>';
        });
}

// --- Gestor de "Sesiones de aprendizaje" (3 columnas: acciones / cursos / sesiones) ---
let sesionesCursos = [];
let sesionesCursoActivo = null;
let sesionSeleccionada = null;

function abrirGestorSesiones() {
    document.getElementById('coord-mi-portafolio-grid').style.display = 'none';
    document.getElementById('coord-sesiones-manager').style.display = 'block';
    cargarCursosConSesiones();
}

function cerrarGestorSesiones() {
    document.getElementById('coord-sesiones-manager').style.display = 'none';
    document.getElementById('coord-mi-portafolio-grid').style.display = 'grid';
}

function cargarCursosConSesiones() {
    const cursos = Array.from(document.getElementById('coord-mi-portafolio-curso').options)
        .filter((o) => o.value)
        .map((o) => ({ id_curso: o.value, nombre_curso: o.textContent, sesiones: [] }));

    sesionesCursos = cursos;
    sesionesCursoActivo = null;
    sesionSeleccionada = null;
    renderListaCursosSesiones();
    renderListaSesiones();

    Promise.all(cursos.map((c) => fetch(`/api/coordinador/sesiones?id_curso=${c.id_curso}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => { c.sesiones = data.sesiones ?? []; })))
        .then(() => {
            renderListaCursosSesiones();
            if (cursos.length) seleccionarCursoSesiones(cursos[0].id_curso);
        });
}

function renderListaCursosSesiones() {
    const root = document.getElementById('coord-sesiones-lista-cursos');

    root.innerHTML = sesionesCursos.length
        ? sesionesCursos.map((c, i) => `
            <div class="coord-sesiones-curso-item ${c.id_curso === sesionesCursoActivo ? 'active' : ''}" data-id-curso="${c.id_curso}" style="--item-color:${COLOR_VAR[PALETA_CURSOS[i % PALETA_CURSOS.length]]}">
                <div class="coord-sesiones-curso-icono"><i class="bi bi-code-slash"></i></div>
                <div>
                    <div class="coord-sesiones-curso-nombre">${c.nombre_curso}</div>
                    <div class="coord-sesiones-curso-conteo">${c.sesiones.length} ${c.sesiones.length === 1 ? 'sesión' : 'sesiones'}</div>
                </div>
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

    document.getElementById('coord-sesiones-seleccionado').style.display = 'block';
    document.getElementById('coord-sesiones-curso-actual').textContent = curso?.nombre_curso ?? '';
    document.getElementById('coord-sesiones-subir').disabled = false;
    document.getElementById('coord-sesiones-eliminar').disabled = true;

    renderListaCursosSesiones();
    renderListaSesiones();
}

function renderListaSesiones() {
    const filtro = document.getElementById('coord-sesiones-buscar')?.value.toLowerCase() ?? '';
    const curso = sesionesCursos.find((c) => c.id_curso === sesionesCursoActivo);
    const titulo = document.getElementById('coord-sesiones-lista-titulo');
    const root = document.getElementById('coord-sesiones-lista-items');

    if (!curso) {
        titulo.innerHTML = '<i class="bi bi-list"></i> Seleccione un curso';
        root.innerHTML = '';

        return;
    }

    titulo.innerHTML = `${curso.nombre_curso}<br><small style="font-weight:400;color:var(--text-muted)">${curso.sesiones.length} sesiones subidas</small>`;

    const filtradas = filtro ? curso.sesiones.filter((s) => s.titulo.toLowerCase().includes(filtro)) : curso.sesiones;

    root.innerHTML = filtradas.length
        ? filtradas.map((s) => `
            <div class="coord-sesiones-item ${String(s.id_sesion) === sesionSeleccionada ? 'selected' : ''}" data-id-sesion="${s.id_sesion}">
                <span>${s.titulo}</span>
                <span class="coord-sesiones-item-fecha">${s.fecha_subida ? formatearFecha(s.fecha_subida) : ''}</span>
            </div>
        `).join('')
        : '<div class="coord-sesiones-lista-vacia">No existen sesiones de aprendizaje para este curso.</div>';

    root.querySelectorAll('[data-id-sesion]').forEach((el) => {
        el.addEventListener('click', () => {
            sesionSeleccionada = el.dataset.idSesion;
            document.getElementById('coord-sesiones-eliminar').disabled = false;
            renderListaSesiones();
        });
    });
}

function iniciarSubidaSesion() {
    if (!sesionesCursoActivo) return;
    document.getElementById('coord-sesiones-input-archivo').click();
}

function subirArchivoSesion(event) {
    const archivo = event.target.files[0];
    event.target.value = '';
    if (!archivo || !sesionesCursoActivo) return;

    const curso = sesionesCursos.find((c) => c.id_curso === sesionesCursoActivo);
    const numeroSesion = (curso?.sesiones.length ?? 0) + 1;
    const errorBox = document.getElementById('coord-sesiones-error');
    errorBox.textContent = '';

    const formData = new FormData();
    formData.append('archivo', archivo);
    formData.append('id_curso', sesionesCursoActivo);
    formData.append('numero_sesion', numeroSesion);
    formData.append('titulo', `Sesión ${numeroSesion} - ${curso?.nombre_curso ?? ''}`);

    fetch('/api/coordinador/sesiones', {
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

    fetch(`/api/coordinador/sesiones/${sesionSeleccionada}`, {
        method: 'DELETE',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
        .then((res) => res.json())
        .then(() => {
            sesionSeleccionada = null;
            document.getElementById('coord-sesiones-eliminar').disabled = true;
            recargarSesionesCursoActivo();
        })
        .catch((error) => console.error(error));
}

function recargarSesionesCursoActivo() {
    fetch(`/api/coordinador/sesiones?id_curso=${sesionesCursoActivo}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const curso = sesionesCursos.find((c) => c.id_curso === sesionesCursoActivo);
            if (curso) curso.sesiones = data.sesiones ?? [];
            renderListaCursosSesiones();
            renderListaSesiones();
        });
}

// --- Gestor de "Notas" (3 columnas: acciones / cursos / estudiantes) ---
let notasCursos = [];
let notasCursoActivo = null;

function abrirGestorNotas() {
    document.getElementById('coord-mi-portafolio-grid').style.display = 'none';
    document.getElementById('coord-notas-manager').style.display = 'block';
    cargarCursosParaNotas();
}

function cerrarGestorNotas() {
    document.getElementById('coord-notas-manager').style.display = 'none';
    document.getElementById('coord-mi-portafolio-grid').style.display = 'grid';
}

function cargarCursosParaNotas() {
    notasCursos = Array.from(document.getElementById('coord-mi-portafolio-curso').options)
        .filter((o) => o.value)
        .map((o) => ({ id_curso: o.value, nombre_curso: o.textContent }));

    notasCursoActivo = null;
    renderListaCursosNotas();
    renderListaEstudiantesNotas();

    if (notasCursos.length) seleccionarCursoNotas(notasCursos[0].id_curso);
}

function renderListaCursosNotas() {
    const root = document.getElementById('coord-notas-lista-cursos');

    root.innerHTML = notasCursos.length
        ? notasCursos.map((c, i) => `
            <div class="coord-sesiones-curso-item ${c.id_curso === notasCursoActivo ? 'active' : ''}" data-id-curso="${c.id_curso}" style="--item-color:${COLOR_VAR[PALETA_CURSOS[i % PALETA_CURSOS.length]]}">
                <div class="coord-sesiones-curso-icono"><i class="bi bi-code-slash"></i></div>
                <div class="coord-sesiones-curso-nombre">${c.nombre_curso}</div>
            </div>
        `).join('')
        : '<p class="coord-portafolio-empty">Aún no tienes cursos asignados.</p>';

    root.querySelectorAll('[data-id-curso]').forEach((el) => {
        el.addEventListener('click', () => seleccionarCursoNotas(el.dataset.idCurso));
    });
}

function seleccionarCursoNotas(idCurso) {
    notasCursoActivo = idCurso;
    const curso = notasCursos.find((c) => c.id_curso === idCurso);

    document.getElementById('coord-notas-seleccionado').style.display = 'block';
    document.getElementById('coord-notas-curso-actual').textContent = curso?.nombre_curso ?? '';

    renderListaCursosNotas();
    renderListaEstudiantesNotas();
}

function renderListaEstudiantesNotas() {
    const titulo = document.getElementById('coord-notas-lista-titulo');
    const root = document.getElementById('coord-notas-lista-estudiantes');
    const curso = notasCursos.find((c) => c.id_curso === notasCursoActivo);

    if (!curso) {
        titulo.innerHTML = '<i class="bi bi-people"></i> Seleccione un curso';
        root.innerHTML = '';

        return;
    }

    const unidad = document.getElementById('coord-notas-unidad').value;
    titulo.innerHTML = `${curso.nombre_curso}<br><small style="font-weight:400;color:var(--text-muted)">Unidad ${unidad}</small>`;
    root.innerHTML = '<div class="coord-sesiones-lista-vacia">Cargando…</div>';

    fetch(`/api/coordinador/portafolios/notas?id_curso=${notasCursoActivo}&unidad=${unidad}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const estudiantes = data.estudiantes ?? [];

            root.innerHTML = estudiantes.length
                ? `
                    <table class="c-table">
                        <thead>
                            <tr><th>Estudiante</th><th>Práctica</th><th>Teoría</th><th>Examen</th><th>Promedio</th><th></th></tr>
                        </thead>
                        <tbody>
                            ${estudiantes.map((e) => `
                                <tr data-id-matricula-curso="${e.id_matricula_curso}">
                                    <td>${e.estudiante.nombres} ${e.estudiante.apellido_paterno ?? ''}</td>
                                    <td><input type="number" min="0" max="20" step="0.01" class="input-inline nota-practica" style="width:70px" value="${e.practica ?? ''}"></td>
                                    <td><input type="number" min="0" max="20" step="0.01" class="input-inline nota-teoria" style="width:70px" value="${e.teoria ?? ''}"></td>
                                    <td><input type="number" min="0" max="20" step="0.01" class="input-inline nota-examen" style="width:70px" value="${e.examen ?? ''}"></td>
                                    <td>${e.promedio ?? '—'}</td>
                                    <td><button type="button" class="c-btn c-btn-outline c-btn-sm" data-guardar-nota="${e.id_matricula_curso}">Guardar</button></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `
                : '<div class="coord-sesiones-lista-vacia">Aún no hay estudiantes matriculados en este curso.</div>';

            root.querySelectorAll('[data-guardar-nota]').forEach((btn) => {
                btn.addEventListener('click', () => guardarNotaFila(btn.dataset.guardarNota));
            });
        })
        .catch(() => {
            root.innerHTML = '<div class="coord-sesiones-lista-vacia">No se pudo cargar la lista de estudiantes.</div>';
        });
}

function guardarNotaFila(idMatriculaCurso) {
    const fila = document.querySelector(`tr[data-id-matricula-curso="${idMatriculaCurso}"]`);
    const unidad = document.getElementById('coord-notas-unidad').value;
    const errorBox = document.getElementById('coord-notas-error');
    errorBox.textContent = '';

    const valor = (selector) => {
        const raw = fila.querySelector(selector).value;

        return raw === '' ? null : Number(raw);
    };

    fetch('/api/coordinador/portafolios/notas', {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({
            id_matricula_curso: idMatriculaCurso,
            unidad,
            practica: valor('.nota-practica'),
            teoria: valor('.nota-teoria'),
            examen: valor('.nota-examen'),
        }),
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then(() => {
            renderListaEstudiantesNotas();
            actualizarConteoNotas(notasCursoActivo);
        })
        .catch((error) => {
            errorBox.textContent = error?.errors ? Object.values(error.errors).flat().join(' ') : 'No se pudo guardar la nota.';
        });
}

// --- Gestor de "Asistencia" (3 columnas: acciones / cursos / estudiantes) ---
let asistenciaCursos = [];
let asistenciaCursoActivo = null;
let asistenciaSesionActiva = null;

function abrirGestorAsistencia() {
    document.getElementById('coord-mi-portafolio-grid').style.display = 'none';
    document.getElementById('coord-asistencia-manager').style.display = 'block';
    document.getElementById('coord-asistencia-fecha').value = new Date().toISOString().slice(0, 10);
    cargarCursosParaAsistencia();
}

function cerrarGestorAsistencia() {
    document.getElementById('coord-asistencia-manager').style.display = 'none';
    document.getElementById('coord-mi-portafolio-grid').style.display = 'grid';
}

function cargarCursosParaAsistencia() {
    asistenciaCursos = Array.from(document.getElementById('coord-mi-portafolio-curso').options)
        .filter((o) => o.value)
        .map((o) => ({ id_curso: o.value, nombre_curso: o.textContent }));

    asistenciaCursoActivo = null;
    asistenciaSesionActiva = null;
    renderListaCursosAsistencia();
    renderListaEstudiantesAsistencia();

    if (asistenciaCursos.length) seleccionarCursoAsistencia(asistenciaCursos[0].id_curso);
}

function renderListaCursosAsistencia() {
    const root = document.getElementById('coord-asistencia-lista-cursos');

    root.innerHTML = asistenciaCursos.length
        ? asistenciaCursos.map((c, i) => `
            <div class="coord-sesiones-curso-item ${c.id_curso === asistenciaCursoActivo ? 'active' : ''}" data-id-curso="${c.id_curso}" style="--item-color:${COLOR_VAR[PALETA_CURSOS[i % PALETA_CURSOS.length]]}">
                <div class="coord-sesiones-curso-icono"><i class="bi bi-code-slash"></i></div>
                <div class="coord-sesiones-curso-nombre">${c.nombre_curso}</div>
            </div>
        `).join('')
        : '<p class="coord-portafolio-empty">Aún no tienes cursos asignados.</p>';

    root.querySelectorAll('[data-id-curso]').forEach((el) => {
        el.addEventListener('click', () => seleccionarCursoAsistencia(el.dataset.idCurso));
    });
}

function seleccionarCursoAsistencia(idCurso) {
    asistenciaCursoActivo = idCurso;
    asistenciaSesionActiva = null;
    const curso = asistenciaCursos.find((c) => c.id_curso === idCurso);

    document.getElementById('coord-asistencia-seleccionado').style.display = 'block';
    document.getElementById('coord-asistencia-curso-actual').textContent = curso?.nombre_curso ?? '';
    document.getElementById('coord-asistencia-cargar-sesion').disabled = false;
    document.getElementById('coord-asistencia-guardar').disabled = true;

    renderListaCursosAsistencia();
    renderListaEstudiantesAsistencia();
}

function cargarOCrearSesionAsistencia() {
    const fecha = document.getElementById('coord-asistencia-fecha').value;
    const errorBox = document.getElementById('coord-asistencia-error');
    errorBox.textContent = '';

    if (!asistenciaCursoActivo || !fecha) return;

    fetch('/api/coordinador/portafolios/asistencia/sesiones', {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ id_curso: asistenciaCursoActivo, fecha_sesion: fecha }),
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then((body) => {
            asistenciaSesionActiva = body.sesion.id_sesion;
            document.getElementById('coord-asistencia-guardar').disabled = false;
            renderListaEstudiantesAsistencia();
        })
        .catch((error) => {
            errorBox.textContent = error?.mensaje ?? 'No se pudo cargar la sesión.';
        });
}

const ESTADOS_ASISTENCIA = ['PRESENTE', 'TARDANZA', 'AUSENTE', 'JUSTIFICADO'];

function renderListaEstudiantesAsistencia() {
    const titulo = document.getElementById('coord-asistencia-lista-titulo');
    const root = document.getElementById('coord-asistencia-lista-estudiantes');
    const curso = asistenciaCursos.find((c) => c.id_curso === asistenciaCursoActivo);

    if (!curso || !asistenciaSesionActiva) {
        titulo.innerHTML = '<i class="bi bi-people"></i> Seleccione un curso y cargue la sesión';
        root.innerHTML = '';

        return;
    }

    titulo.innerHTML = `${curso.nombre_curso}<br><small style="font-weight:400;color:var(--text-muted)">${document.getElementById('coord-asistencia-fecha').value}</small>`;
    root.innerHTML = '<div class="coord-sesiones-lista-vacia">Cargando…</div>';

    fetch(`/api/coordinador/portafolios/asistencia?id_curso=${asistenciaCursoActivo}&id_sesion=${asistenciaSesionActiva}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const estudiantes = data.estudiantes ?? [];

            root.innerHTML = estudiantes.length
                ? estudiantes.map((e) => `
                    <div class="coord-sesiones-item" data-id-estudiante="${e.id_estudiante}" style="cursor:default">
                        <span>${e.estudiante.nombres} ${e.estudiante.apellido_paterno ?? ''}</span>
                        <select class="input-inline asistencia-estado" style="width:auto">
                            ${ESTADOS_ASISTENCIA.map((estado) => `<option value="${estado}" ${estado === e.estado ? 'selected' : ''}>${estado}</option>`).join('')}
                        </select>
                    </div>
                `).join('')
                : '<div class="coord-sesiones-lista-vacia">Aún no hay estudiantes matriculados en este curso.</div>';
        })
        .catch(() => {
            root.innerHTML = '<div class="coord-sesiones-lista-vacia">No se pudo cargar la lista de estudiantes.</div>';
        });
}

function guardarAsistenciaCompleta() {
    if (!asistenciaSesionActiva) return;
    const errorBox = document.getElementById('coord-asistencia-error');
    errorBox.textContent = '';

    const registros = Array.from(document.querySelectorAll('#coord-asistencia-lista-estudiantes [data-id-estudiante]')).map((fila) => ({
        id_estudiante: Number(fila.dataset.idEstudiante),
        estado: fila.querySelector('.asistencia-estado').value,
    }));

    fetch('/api/coordinador/portafolios/asistencia', {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ id_sesion: asistenciaSesionActiva, registros }),
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then(() => {
            alert('Asistencia guardada.');
            actualizarConteoAsistencia(asistenciaCursoActivo);
        })
        .catch((error) => {
            errorBox.textContent = error?.errors ? Object.values(error.errors).flat().join(' ') : 'No se pudo guardar la asistencia.';
        });
}

let subidaActiva = null;

/** Subida rapida: un clic abre el explorador de archivos y sube apenas se elige uno, con titulo autogenerado. */
function iniciarSubida(tipo, idCurso) {
    const select = document.getElementById('coord-mi-portafolio-curso');
    subidaActiva = { tipo, idCurso, idPeriodo: select.dataset.idPeriodo };
    document.getElementById('coord-mi-portafolio-input-archivo').click();
}

function subirArchivoSeleccionado(event) {
    const archivo = event.target.files[0];
    event.target.value = '';
    if (!archivo || !subidaActiva) return;

    const tipoInfo = TIPOS_PORTAFOLIO.find((t) => t.valor === subidaActiva.tipo);
    const cursoTexto = document.getElementById('coord-mi-portafolio-curso').selectedOptions[0]?.textContent ?? '';
    const errorBox = document.getElementById('coord-mi-portafolio-error');
    errorBox.textContent = '';

    const formData = new FormData();
    formData.append('documento', archivo);
    formData.append('id_curso', subidaActiva.idCurso);
    formData.append('id_periodo', subidaActiva.idPeriodo);
    formData.append('tipo', subidaActiva.tipo);
    formData.append('titulo', `${tipoInfo?.etiqueta ?? subidaActiva.tipo} - ${cursoTexto}`);

    fetch('/api/portafolios/documentos', {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: formData,
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then(() => cargarMiPortafolio())
        .catch((error) => {
            errorBox.textContent = error?.mensaje ?? (error?.errors ? Object.values(error.errors).flat().join(' ') : 'No se pudo subir el documento.');
        })
        .finally(() => { subidaActiva = null; });
}

export function initCoordinadorPortafolio() {
    const tbody = document.getElementById('coord-portafolio-tbody');
    if (!tbody) return;

    capturarCursosDisponibles();
    cargarDocumentos();
    cargarKpis();

    document.getElementById('coord-portafolio-filtro-docente')?.addEventListener('change', () => {
        actualizarCursosPorDocente();
        cargarDocumentos();
    });

    ['coord-portafolio-filtro-curso', 'coord-portafolio-filtro-estado'].forEach((id) => {
        document.getElementById(id)?.addEventListener('change', cargarDocumentos);
    });

    document.getElementById('coord-portafolio-cerrar')?.addEventListener('click', cerrarModal);
    document.getElementById('coord-portafolio-analizar-ia')?.addEventListener('click', analizarIA);
    document.getElementById('coord-portafolio-aprobar')?.addEventListener('click', () => validar('APROBADO'));
    document.getElementById('coord-portafolio-observar')?.addEventListener('click', () => validar('OBSERVADO'));

    document.querySelectorAll('.c-tab-btn').forEach((btn) => {
        btn.addEventListener('click', () => cambiarTab(btn.dataset.tab));
    });
    document.getElementById('coord-mi-portafolio-curso')?.addEventListener('change', cargarMiPortafolio);
    document.getElementById('coord-mi-portafolio-input-archivo')?.addEventListener('change', subirArchivoSeleccionado);

    document.getElementById('coord-sesiones-volver')?.addEventListener('click', cerrarGestorSesiones);
    document.getElementById('coord-sesiones-subir')?.addEventListener('click', iniciarSubidaSesion);
    document.getElementById('coord-sesiones-eliminar')?.addEventListener('click', eliminarSesionSeleccionada);
    document.getElementById('coord-sesiones-input-archivo')?.addEventListener('change', subirArchivoSesion);

    let debounceSesiones;
    document.getElementById('coord-sesiones-buscar')?.addEventListener('input', () => {
        clearTimeout(debounceSesiones);
        debounceSesiones = setTimeout(renderListaSesiones, 250);
    });

    document.getElementById('coord-notas-volver')?.addEventListener('click', cerrarGestorNotas);
    document.getElementById('coord-notas-unidad')?.addEventListener('change', renderListaEstudiantesNotas);

    document.getElementById('coord-asistencia-volver')?.addEventListener('click', cerrarGestorAsistencia);
    document.getElementById('coord-asistencia-cargar-sesion')?.addEventListener('click', cargarOCrearSesionAsistencia);
    document.getElementById('coord-asistencia-guardar')?.addEventListener('click', guardarAsistenciaCompleta);
}

document.addEventListener('DOMContentLoaded', initCoordinadorPortafolio);
