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

/** Carga TODOS los documentos (sin filtro de servidor): los filtros ahora solo muestran/ocultan tarjetas de grupo ya renderizadas. */
function cargarDocumentos() {
    return fetch('/api/coordinador/portafolios', { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            documentosCache = data.documentos ?? [];
            renderGruposRevision();

            // Si un grupo ya estaba expandido, sus tarjetas de Sílabo/Evidencias tambien se refrescan.
            document.querySelectorAll('.coord-revision-grupo[data-cargado="1"]').forEach(renderSeccionesArchivos);
        })
        .catch((error) => console.error(error));
}

/** Actualiza cabecera (archivos subidos/progreso/pendientes) de cada grupo docente+curso y aplica los filtros de visibilidad. */
function renderGruposRevision() {
    const filtroDocente = document.getElementById('coord-portafolio-filtro-docente')?.value;
    const filtroCurso = document.getElementById('coord-portafolio-filtro-curso')?.value;
    const filtroEstado = document.getElementById('coord-portafolio-filtro-estado')?.value;

    document.querySelectorAll('.coord-revision-grupo').forEach((grupo) => {
        const idCurso = grupo.dataset.idCurso;
        const idDocente = grupo.dataset.idDocente;

        const docsGrupo = documentosCache.filter((d) => String(d.portafolio?.id_curso) === idCurso);
        const silabo = docsGrupo.find((d) => d.tipo === 'SILABO') ?? null;
        const evidencia = docsGrupo.find((d) => d.tipo === 'EVIDENCIA') ?? null;
        grupo._docs = { silabo, evidencia };

        const subidos = [silabo, evidencia].filter(Boolean).length;
        const aprobados = [silabo, evidencia].filter((d) => d?.estado === 'APROBADO').length;
        const pendientes = 2 - aprobados;
        const pct = Math.round((aprobados / 2) * 100);

        const subidosEl = grupo.querySelector('[data-archivos-subidos]');
        if (subidosEl) subidosEl.textContent = `${subidos} archivo(s) subido(s) por el docente`;
        grupo.querySelector('[data-progreso-bar]').style.width = `${pct}%`;
        grupo.querySelector('[data-progreso-pct]').textContent = `${pct}%`;

        const badge = grupo.querySelector('[data-pendientes-badge]');
        badge.hidden = pendientes === 0;
        if (pendientes > 0) badge.textContent = `${pendientes} pend.`;

        const coincideDocente = !filtroDocente || filtroDocente === idDocente;
        const coincideCurso = !filtroCurso || filtroCurso === idCurso;
        const coincideEstado = !filtroEstado || [silabo, evidencia].some((d) => d?.estado === filtroEstado);
        grupo.style.display = coincideDocente && coincideCurso && coincideEstado ? '' : 'none';
    });
}

/** Tarjetas de Sílabo y Evidencias del grupo (archivos reales: Ver/Revisar con IA/Observar, via el modal ya existente). */
function renderSeccionesArchivos(grupo) {
    const { silabo, evidencia } = grupo._docs ?? {};
    const root = grupo.querySelector('[data-secciones-archivos]');

    const tarjeta = (doc, etiqueta, icono) => {
        const estado = doc?.estado ?? null;
        const badgeClase = estado ? (BADGE_ESTADO[estado] ?? 'c-badge-navy') : 'c-badge-red';
        const sub = doc ? `Subido ${formatearFecha(doc.fecha_subida)}` : 'Pendiente de subir por el docente';

        return `
            <div class="coord-revision-doc-card">
                <div class="coord-revision-doc-head">
                    <i class="bi ${icono}"></i>
                    <div>
                        <strong>${etiqueta}</strong>
                        <small>${sub}</small>
                    </div>
                </div>
                <span class="c-badge ${badgeClase}">${estado ? '' : '<i class="bi bi-exclamation-triangle-fill"></i> '}${estado ?? 'Pendiente'}</span>
                <div class="coord-revision-doc-acciones">
                    <button type="button" class="c-btn c-btn-outline c-btn-sm" ${doc ? `data-ver-doc="${doc.id_documento}"` : 'disabled'}>
                        <i class="bi bi-eye"></i> Ver
                    </button>
                </div>
            </div>
        `;
    };

    root.innerHTML = tarjeta(silabo, 'Sílabo', 'bi-file-earmark-text') + tarjeta(evidencia, 'Evidencias de Aprendizaje', 'bi-folder2');

    root.querySelectorAll('[data-ver-doc]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const doc = documentosCache.find((d) => String(d.id_documento) === btn.dataset.verDoc);
            if (doc) abrirModal(doc);
        });
    });
}

/** Sesiones de aprendizaje del curso: lista con Aprobar/Rechazar directo (sin modal, no aplica analisis IA). */
function cargarSesionesRevision(grupo) {
    const idCurso = grupo.dataset.idCurso;
    const root = grupo.querySelector('[data-sesiones-lista]');
    const conteo = grupo.querySelector('[data-sesiones-conteo]');

    fetch(`/api/coordinador/portafolios/revision/sesiones?id_curso=${idCurso}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const sesiones = data.sesiones ?? [];
            conteo.textContent = `${sesiones.length} sesión(es) subida(s)`;

            root.innerHTML = sesiones.length
                ? sesiones.map((s) => `
                    <div class="coord-revision-sesion-item">
                        <div>
                            <strong>${s.titulo}</strong><br>
                            <small>${s.numero_sesion ? `Sesión ${s.numero_sesion} · ` : ''}${formatearFecha(s.fecha_subida)}</small>
                        </div>
                        <span class="c-badge ${s.estado === 'APROBADO' ? 'c-badge-green' : s.estado === 'RECHAZADO' ? 'c-badge-red' : 'c-badge-gold'}">${s.estado}</span>
                        <div class="coord-revision-sesion-acciones">
                            <button type="button" class="c-btn c-btn-outline c-btn-sm" data-aprobar-sesion="${s.id_sesion}">Aprobar</button>
                            <button type="button" class="c-btn c-btn-outline c-btn-sm coord-revision-btn-rechazar" data-rechazar-sesion="${s.id_sesion}">Rechazar</button>
                        </div>
                    </div>
                `).join('')
                : '<div class="coord-sesiones-lista-vacia">No hay sesiones de aprendizaje subidas para este curso.</div>';

            root.querySelectorAll('[data-aprobar-sesion]').forEach((btn) => {
                btn.addEventListener('click', () => validarSesionRevision(btn.dataset.aprobarSesion, 'APROBADO', grupo));
            });
            root.querySelectorAll('[data-rechazar-sesion]').forEach((btn) => {
                btn.addEventListener('click', () => validarSesionRevision(btn.dataset.rechazarSesion, 'RECHAZADO', grupo));
            });
        })
        .catch(() => { root.innerHTML = '<div class="coord-sesiones-lista-vacia">No se pudo cargar las sesiones.</div>'; });
}

function validarSesionRevision(idSesion, estado, grupo) {
    fetch(`/api/coordinador/portafolios/revision/sesiones/${idSesion}/validar`, {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ estado }),
    })
        .then((res) => res.json())
        .then(() => cargarSesionesRevision(grupo))
        .catch(() => alert('No se pudo actualizar la sesión.'));
}

/** Asistencia de revision: solo lectura, filtrable por fecha (las fechas ya tomadas por el docente). */
const BADGE_ASISTENCIA = { PRESENTE: 'c-badge-green', TARDANZA: 'c-badge-gold', AUSENTE: 'c-badge-red', JUSTIFICADO: 'c-badge-navy' };

function cargarAsistenciaRevision(grupo) {
    const idCurso = grupo.dataset.idCurso;
    const selectFecha = grupo.querySelector('[data-fecha-select]');
    const root = grupo.querySelector('[data-asistencia-lista]');

    fetch(`/api/coordinador/portafolios/revision/asistencia/fechas?id_curso=${idCurso}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const fechas = data.fechas ?? [];

            if (!fechas.length) {
                selectFecha.innerHTML = '<option value="">Sin sesiones</option>';
                root.innerHTML = '<div class="coord-sesiones-lista-vacia">Este docente aún no ha registrado asistencia en este curso.</div>';

                return;
            }

            selectFecha.innerHTML = fechas.map((f) => `<option value="${f}">${f}</option>`).join('');
            selectFecha.onchange = () => renderAsistenciaFechaRevision(idCurso, selectFecha.value, root);
            renderAsistenciaFechaRevision(idCurso, fechas[0], root);
        })
        .catch(() => { root.innerHTML = '<div class="coord-sesiones-lista-vacia">No se pudo cargar la asistencia.</div>'; });
}

function renderAsistenciaFechaRevision(idCurso, fecha, root) {
    root.innerHTML = '<div class="coord-sesiones-lista-vacia">Cargando…</div>';

    fetch(`/api/coordinador/portafolios/revision/asistencia?id_curso=${idCurso}&fecha=${fecha}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const estudiantes = data.estudiantes ?? [];

            root.innerHTML = estudiantes.length
                ? estudiantes.map((e) => `
                    <div class="coord-revision-asistencia-item">
                        <span>${e.nombre_completo}</span>
                        <span class="c-badge ${BADGE_ASISTENCIA[e.estado] ?? 'c-badge-navy'}">${e.estado ?? 'Sin registrar'}</span>
                    </div>
                `).join('')
                : '<div class="coord-sesiones-lista-vacia">No hay estudiantes registrados en el semestre de este curso.</div>';
        })
        .catch(() => { root.innerHTML = '<div class="coord-sesiones-lista-vacia">No se pudo cargar la asistencia.</div>'; });
}

/** Notas de revision: el coordinador puede ver, editar directamente y reabrir el acta si el docente ya la cerro. */
function wireFilaNotasInputsEnContenedor(root, notaMinima) {
    root.querySelectorAll('tbody tr').forEach((fila) => {
        fila.querySelectorAll('.coord-notas-input').forEach((input) => {
            input.addEventListener('input', () => {
                input.classList.toggle('baja', input.value !== '' && Number(input.value) < notaMinima);
                recalcularPromedioFila(fila, notaMinima);
            });
        });
    });
}

function cargarNotasRevision(grupo) {
    grupo.querySelector('[data-parcial-select]').onchange = () => renderNotasParcialRevision(grupo);
    grupo.querySelector('[data-guardar-notas-btn]').addEventListener('click', () => guardarNotasRevisionGrupo(grupo));
    grupo.querySelector('[data-reabrir-btn]').addEventListener('click', () => reabrirActaRevisionGrupo(grupo));
    renderNotasParcialRevision(grupo);
}

function renderNotasParcialRevision(grupo) {
    const idCurso = grupo.dataset.idCurso;
    const unidad = grupo.querySelector('[data-parcial-select]').value;
    const root = grupo.querySelector('[data-notas-lista]');
    const botonReabrir = grupo.querySelector('[data-reabrir-btn]');

    root.innerHTML = '<div class="coord-sesiones-lista-vacia">Cargando…</div>';

    fetch(`/api/coordinador/portafolios/revision/notas?id_curso=${idCurso}&unidad=${unidad}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const estudiantes = data.estudiantes ?? [];
            const notaMinima = data.resumen?.nota_minima ?? 10.5;
            botonReabrir.hidden = ! (data.resumen?.acta_cerrada ?? false);

            root.innerHTML = estudiantes.length
                ? `
                    <table class="coord-notas-tabla">
                        <thead><tr><th>Estudiante</th><th>Examen Práctico</th><th>Examen Teórico</th><th>Examen</th><th>Promedio</th></tr></thead>
                        <tbody>${estudiantes.map((e, i) => filaNotaHtml(e, i, notaMinima)).join('')}</tbody>
                    </table>
                `
                : '<div class="coord-sesiones-lista-vacia">No hay estudiantes registrados en el semestre de este curso.</div>';

            wireFilaNotasInputsEnContenedor(root, notaMinima);
        })
        .catch(() => { root.innerHTML = '<div class="coord-sesiones-lista-vacia">No se pudo cargar las notas.</div>'; });
}

function guardarNotasRevisionGrupo(grupo) {
    const idCurso = grupo.dataset.idCurso;
    const unidad = grupo.querySelector('[data-parcial-select]').value;
    const errorBox = grupo.querySelector('[data-notas-error]');
    const boton = grupo.querySelector('[data-guardar-notas-btn]');
    errorBox.textContent = '';

    const filas = Array.from(grupo.querySelectorAll('[data-notas-lista] tbody tr'));
    if (!filas.length) return;

    const valor = (fila, selector) => {
        const raw = fila.querySelector(selector).value;

        return raw === '' ? null : Number(raw);
    };

    boton.disabled = true;
    boton.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando…';

    fetch('/api/coordinador/portafolios/revision/notas', {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({
            id_curso: Number(idCurso),
            unidad,
            filas: filas.map((fila) => ({
                id_matricula_curso: Number(fila.dataset.idMatriculaCurso),
                practica: valor(fila, '.nota-practica'),
                teoria: valor(fila, '.nota-teoria'),
                examen: valor(fila, '.nota-examen'),
            })),
        }),
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then(() => renderNotasParcialRevision(grupo))
        .catch((error) => {
            errorBox.textContent = error?.errors ? Object.values(error.errors).flat().join(' ') : 'No se pudo guardar las notas.';
        })
        .finally(() => {
            boton.disabled = false;
            boton.innerHTML = '<i class="bi bi-save"></i> Guardar';
        });
}

function reabrirActaRevisionGrupo(grupo) {
    const idCurso = grupo.dataset.idCurso;
    const unidad = grupo.querySelector('[data-parcial-select]').value;

    fetch('/api/coordinador/portafolios/revision/notas/reabrir', {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ id_curso: Number(idCurso), unidad }),
    })
        .then((res) => res.json())
        .then(() => renderNotasParcialRevision(grupo))
        .catch(() => alert('No se pudo reabrir el acta.'));
}

/** Expandir/colapsar un grupo docente+curso; la primera vez que se abre, carga sus 5 secciones. */
function wireGruposRevision() {
    document.querySelectorAll('[data-toggle-grupo]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const grupo = btn.closest('.coord-revision-grupo');
            const body = grupo.querySelector('.coord-revision-grupo-body');
            const abrir = body.hidden;
            body.hidden = !abrir;
            btn.querySelector('.bi-chevron-down, .bi-chevron-up')?.classList.toggle('bi-chevron-up', abrir);
            btn.querySelector('.bi-chevron-down, .bi-chevron-up')?.classList.toggle('bi-chevron-down', !abrir);

            if (abrir && !grupo.dataset.cargado) {
                grupo.dataset.cargado = '1';
                renderSeccionesArchivos(grupo);
                cargarSesionesRevision(grupo);
                cargarAsistenciaRevision(grupo);
                cargarNotasRevision(grupo);
            }
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
                detalle.textContent = 'No hay estudiantes registrados en el semestre de este curso.';
                badge.innerHTML = '<span class="c-badge c-badge-red"><i class="bi bi-exclamation-triangle-fill"></i> Pendiente</span>';
            } else if (!conNota.length) {
                detalle.textContent = `Pendiente de ingresar · ${estudiantes.length} estudiante(s)`;
                badge.innerHTML = '<span class="c-badge c-badge-red"><i class="bi bi-exclamation-triangle-fill"></i> Pendiente</span>';
            } else {
                detalle.textContent = `${conNota.length} de ${estudiantes.length} estudiantes con nota (Parcial 1)`;
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
    const hoy = new Date().toISOString().slice(0, 10);

    fetch(`/api/coordinador/portafolios/asistencia?id_curso=${idCurso}&fecha=${hoy}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const estudiantes = data.estudiantes ?? [];
            const detalle = document.getElementById('asistencia-card-detalle');
            const badge = document.getElementById('asistencia-card-badge');
            if (!detalle || !badge) return;

            const tomada = estudiantes.some((e) => e.estado !== null);

            if (!tomada) {
                detalle.textContent = 'Pendiente de ingresar · Requiere asistencia hoy';
                badge.innerHTML = '<span class="c-badge c-badge-red"><i class="bi bi-exclamation-triangle-fill"></i> Pendiente</span>';
            } else {
                detalle.textContent = `${data.resumen.presentes} presente(s) hoy`;
                badge.innerHTML = `<span class="c-badge c-badge-green"><i class="bi bi-check-circle-fill"></i> Tomada</span>`;
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

// --- Gestor de "Notas": tarjeta de curso + resumen de clase + tabla de estudiantes por semestre (igual que Asistencia). ---
let notasCursos = [];
let notasCursoActivo = null;
const ETIQUETA_PARCIAL = { I: 'Parcial 1', II: 'Parcial 2', III: 'Parcial 3' };
const PALETA_AVATAR_NOTAS = ['teal', 'navy', 'gold', 'red', 'purple'];

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
    const selectPrincipal = document.getElementById('coord-mi-portafolio-curso');

    notasCursos = Array.from(selectPrincipal.options)
        .filter((o) => o.value)
        .map((o) => ({ id_curso: o.value, nombre_curso: o.textContent }));

    const select = document.getElementById('coord-notas-curso');
    select.innerHTML = notasCursos.map((c) => `<option value="${c.id_curso}">${c.nombre_curso}</option>`).join('');

    document.getElementById('coord-notas-semestre-valor').textContent = selectPrincipal.dataset.periodoCodigo || '—';

    if (notasCursos.length) {
        seleccionarCursoNotas(notasCursos[0].id_curso);
    } else {
        document.getElementById('coord-notas-curso-nombre').textContent = 'Sin cursos asignados';
        document.getElementById('coord-notas-total-estudiantes').textContent = '0';
    }
}

function seleccionarCursoNotas(idCurso) {
    notasCursoActivo = idCurso;
    document.getElementById('coord-notas-curso').value = idCurso;

    const curso = notasCursos.find((c) => c.id_curso === idCurso);
    document.getElementById('coord-notas-curso-nombre').textContent = curso?.nombre_curso ?? '—';

    cargarNotasDelParcial();
}

function inicialesNotas(nombres, apellidos) {
    return `${(nombres[0] ?? '').toUpperCase()}${(apellidos[0] ?? '').toUpperCase()}`;
}

function claseNotaBaja(valor, notaMinima) {
    return valor !== null && valor !== undefined && Number(valor) < notaMinima ? 'baja' : '';
}

function claseBadgePromedio(promedio, notaMinima) {
    if (promedio === null || promedio === undefined) return '';
    const valor = Number(promedio);
    if (valor < notaMinima) return 'baja';

    return valor >= 14 ? 'alta' : 'media';
}

function filaNotaHtml(est, indice, notaMinima) {
    const color = PALETA_AVATAR_NOTAS[indice % PALETA_AVATAR_NOTAS.length];
    const nombre = `${est.estudiante.nombres} ${est.estudiante.apellido_paterno ?? ''}`;

    return `
        <tr data-id-matricula-curso="${est.id_matricula_curso}" data-nombre="${nombre.toLowerCase()}">
            <td>
                <div class="coord-notas-fila-nombre">
                    <div class="coord-asistencia-avatar ${color}">${inicialesNotas(est.estudiante.nombres, est.estudiante.apellido_paterno ?? '')}</div>
                    <div>
                        <strong>${nombre}</strong><br>
                        <small style="color:var(--text-muted)">${est.estudiante.codigo_estudiante}</small>
                    </div>
                </div>
            </td>
            <td><input type="number" min="0" max="20" step="0.01" class="coord-notas-input nota-practica ${claseNotaBaja(est.practica, notaMinima)}" value="${est.practica ?? ''}"></td>
            <td><input type="number" min="0" max="20" step="0.01" class="coord-notas-input nota-teoria ${claseNotaBaja(est.teoria, notaMinima)}" value="${est.teoria ?? ''}"></td>
            <td><input type="number" min="0" max="20" step="0.01" class="coord-notas-input nota-examen ${claseNotaBaja(est.examen, notaMinima)}" value="${est.examen ?? ''}"></td>
            <td><span class="coord-notas-promedio ${claseBadgePromedio(est.promedio, notaMinima)}" data-promedio>${est.promedio ?? '—'}</span></td>
        </tr>
    `;
}

/** Recalcula el promedio (20% practica + 30% teoria + 50% examen) al vuelo, sin esperar a guardar. */
function recalcularPromedioFila(fila, notaMinima) {
    const leer = (selector) => {
        const raw = fila.querySelector(selector).value;

        return raw === '' ? null : Number(raw);
    };

    const practica = leer('.nota-practica');
    const teoria = leer('.nota-teoria');
    const examen = leer('.nota-examen');

    const badge = fila.querySelector('[data-promedio]');

    if (practica === null && teoria === null && examen === null) {
        badge.textContent = '—';
        badge.className = 'coord-notas-promedio';

        return;
    }

    const promedio = Math.round(((practica ?? 0) * 0.2 + (teoria ?? 0) * 0.3 + (examen ?? 0) * 0.5) * 100) / 100;
    badge.textContent = promedio.toFixed(2);
    badge.className = `coord-notas-promedio ${claseBadgePromedio(promedio, notaMinima)}`;
}

function wireFilaNotasInputs(notaMinima) {
    document.querySelectorAll('#coord-notas-lista-estudiantes tbody tr').forEach((fila) => {
        fila.querySelectorAll('.coord-notas-input').forEach((input) => {
            input.addEventListener('input', () => {
                input.classList.toggle('baja', input.value !== '' && Number(input.value) < notaMinima);
                recalcularPromedioFila(fila, notaMinima);
            });
        });
    });
}

function renderKpisNotas(resumen) {
    document.getElementById('coord-notas-stat-promedio').textContent = resumen.promedio_clase ?? '—';
    document.getElementById('coord-notas-stat-desaprobados').textContent = resumen.desaprobados ?? 0;

    const kpis = document.getElementById('coord-notas-kpis');
    kpis.querySelector('[data-kpi="promedio"]').textContent = resumen.promedio_clase ?? '—';
    kpis.querySelector('[data-kpi="mas-alta"]').textContent = resumen.nota_mas_alta ?? '—';
    kpis.querySelector('[data-kpi="mas-baja"]').textContent = resumen.nota_mas_baja ?? '—';
    kpis.querySelector('[data-kpi="desaprobados"]').textContent = resumen.desaprobados ?? 0;
}

function aplicarFiltroNotas() {
    const busqueda = document.getElementById('coord-notas-buscar').value.trim().toLowerCase();

    document.querySelectorAll('#coord-notas-lista-estudiantes tbody tr').forEach((fila) => {
        fila.style.display = !busqueda || fila.dataset.nombre.includes(busqueda) ? '' : 'none';
    });
}

function cargarNotasDelParcial() {
    const root = document.getElementById('coord-notas-lista-estudiantes');

    if (!notasCursoActivo) {
        root.innerHTML = '';

        return;
    }

    const unidad = document.getElementById('coord-notas-unidad').value;
    root.innerHTML = '<div class="coord-sesiones-lista-vacia">Cargando…</div>';

    fetch(`/api/coordinador/portafolios/notas?id_curso=${notasCursoActivo}&unidad=${unidad}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const estudiantes = data.estudiantes ?? [];
            const notaMinima = data.resumen?.nota_minima ?? 10.5;

            document.getElementById('coord-notas-total-estudiantes').textContent = estudiantes.length;
            renderKpisNotas(data.resumen ?? {});

            root.innerHTML = estudiantes.length
                ? `
                    <table class="coord-notas-tabla">
                        <thead>
                            <tr><th>Estudiante</th><th>Examen Práctico</th><th>Examen Teórico</th><th>Examen</th><th>Promedio</th></tr>
                        </thead>
                        <tbody>
                            ${estudiantes.map((e, i) => filaNotaHtml(e, i, notaMinima)).join('')}
                        </tbody>
                    </table>
                `
                : '<div class="coord-sesiones-lista-vacia">No hay estudiantes registrados en el semestre de este curso.</div>';

            wireFilaNotasInputs(notaMinima);
            aplicarFiltroNotas();
        })
        .catch(() => {
            root.innerHTML = '<div class="coord-sesiones-lista-vacia">No se pudo cargar la lista de estudiantes.</div>';
        });
}

/** Guarda todas las filas en UNA sola peticion (no una por estudiante): mas confiable y rapido. */
function guardarNotasCoordinador() {
    const unidad = document.getElementById('coord-notas-unidad').value;
    const errorBox = document.getElementById('coord-notas-error');
    const boton = document.getElementById('coord-notas-guardar-todo');
    errorBox.textContent = '';

    const filas = Array.from(document.querySelectorAll('#coord-notas-lista-estudiantes tbody tr'));
    if (!filas.length || !notasCursoActivo) return;

    const valor = (fila, selector) => {
        const raw = fila.querySelector(selector).value;

        return raw === '' ? null : Number(raw);
    };

    const cuerpo = {
        id_curso: notasCursoActivo,
        unidad,
        filas: filas.map((fila) => ({
            id_matricula_curso: Number(fila.dataset.idMatriculaCurso),
            practica: valor(fila, '.nota-practica'),
            teoria: valor(fila, '.nota-teoria'),
            examen: valor(fila, '.nota-examen'),
        })),
    };

    boton.disabled = true;
    boton.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando…';

    fetch('/api/coordinador/portafolios/notas', {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(cuerpo),
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then(() => {
            cargarNotasDelParcial();
            actualizarConteoNotas(notasCursoActivo);
        })
        .catch((error) => {
            errorBox.textContent = error?.errors ? Object.values(error.errors).flat().join(' ') : (error?.message ?? 'No se pudo guardar las notas.');
        })
        .finally(() => {
            boton.disabled = false;
            boton.innerHTML = '<i class="bi bi-save"></i> Guardar notas';
        });
}

function imprimirNotas() {
    window.print();
}

// --- Gestor de "Asistencia": un dia a la vez, tarjeta de curso + botones rapidos por estudiante (igual al modulo Docente). ---
let asistenciaCursos = [];
let asistenciaCursoActivo = null;
let asistenciaFechaActiva = null;
let asistenciaFiltroChip = null;
const PALETA_AVATAR_ASISTENCIA = ['teal', 'navy', 'gold', 'red', 'purple'];

function inicialesAsistencia(nombres, apellidos) {
    return `${(nombres[0] ?? '').toUpperCase()}${(apellidos[0] ?? '').toUpperCase()}`;
}

function nombreYApellidosAsistencia(nombreCompleto) {
    const [apellidos, nombres] = nombreCompleto.split(',').map((s) => s.trim());

    return { nombres: nombres ?? '', apellidos: apellidos ?? '' };
}

function formatearFechaLargaAsistencia(fechaIso) {
    const [anio, mes, dia] = fechaIso.split('-').map(Number);
    const fecha = new Date(anio, mes - 1, dia);
    const texto = fecha.toLocaleDateString('es-PE', { weekday: 'long', day: '2-digit', month: 'short' });

    return texto.charAt(0).toUpperCase() + texto.slice(1);
}

function abrirGestorAsistencia() {
    document.getElementById('coord-mi-portafolio-grid').style.display = 'none';
    document.getElementById('coord-asistencia-manager').style.display = 'block';

    asistenciaFechaActiva = new Date().toISOString().slice(0, 10);
    cargarCursosParaAsistencia();
}

function cerrarGestorAsistencia() {
    document.getElementById('coord-asistencia-manager').style.display = 'none';
    document.getElementById('coord-mi-portafolio-grid').style.display = 'grid';
}

function cargarCursosParaAsistencia() {
    const selectPrincipal = document.getElementById('coord-mi-portafolio-curso');

    asistenciaCursos = Array.from(selectPrincipal.options)
        .filter((o) => o.value)
        .map((o) => ({ id_curso: o.value, nombre_curso: o.textContent }));

    const select = document.getElementById('coord-asistencia-curso');
    select.innerHTML = asistenciaCursos.map((c) => `<option value="${c.id_curso}">${c.nombre_curso}</option>`).join('');

    document.getElementById('coord-asistencia-semestre-valor').textContent = selectPrincipal.dataset.periodoCodigo || '—';

    if (asistenciaCursos.length) {
        seleccionarCursoAsistencia(asistenciaCursos[0].id_curso);
    } else {
        document.getElementById('coord-asistencia-curso-nombre').textContent = 'Sin cursos asignados';
        document.getElementById('coord-asistencia-total-matriculados').textContent = '0';
    }
}

function seleccionarCursoAsistencia(idCurso) {
    asistenciaCursoActivo = idCurso;
    document.getElementById('coord-asistencia-curso').value = idCurso;

    const curso = asistenciaCursos.find((c) => c.id_curso === idCurso);
    document.getElementById('coord-asistencia-curso-nombre').textContent = curso?.nombre_curso ?? '—';

    cargarAsistenciaDelDia();
}

function cambiarFechaAsistencia(delta) {
    const [anio, mes, dia] = asistenciaFechaActiva.split('-').map(Number);
    const fecha = new Date(anio, mes - 1, dia + delta);
    asistenciaFechaActiva = `${fecha.getFullYear()}-${String(fecha.getMonth() + 1).padStart(2, '0')}-${String(fecha.getDate()).padStart(2, '0')}`;
    cargarAsistenciaDelDia();
}

function recalcularResumenLocalAsistencia() {
    const filas = Array.from(document.querySelectorAll('#coord-asistencia-lista .coord-asistencia-fila'));
    const conteo = { PRESENTE: 0, TARDANZA: 0, AUSENTE: 0, JUSTIFICADO: 0 };

    filas.forEach((fila) => { conteo[fila.dataset.estado] = (conteo[fila.dataset.estado] ?? 0) + 1; });

    const total = filas.length;
    const pct = total > 0 ? Math.round((conteo.PRESENTE / total) * 1000) / 10 : null;

    document.getElementById('coord-asistencia-stat-pct').textContent = pct !== null ? `${pct}%` : '—';
    document.getElementById('coord-asistencia-stat-ausentes').textContent = conteo.AUSENTE;
    document.getElementById('coord-asistencia-chip-presentes').textContent = conteo.PRESENTE;
    document.getElementById('coord-asistencia-chip-tardanzas').textContent = conteo.TARDANZA;
    document.getElementById('coord-asistencia-chip-ausentes').textContent = conteo.AUSENTE;
}

function aplicarFiltrosAsistencia() {
    const busqueda = document.getElementById('coord-asistencia-buscar').value.trim().toLowerCase();

    document.querySelectorAll('#coord-asistencia-lista .coord-asistencia-fila').forEach((fila) => {
        const coincideChip = !asistenciaFiltroChip || fila.dataset.estado === asistenciaFiltroChip;
        const coincideBusqueda = !busqueda || fila.dataset.nombre.includes(busqueda);
        fila.style.display = coincideChip && coincideBusqueda ? '' : 'none';
    });
}

function wireFilaBotonesAsistencia() {
    document.querySelectorAll('#coord-asistencia-lista .coord-asistencia-fila-botones button').forEach((btn) => {
        btn.addEventListener('click', () => {
            const fila = btn.closest('.coord-asistencia-fila');
            fila.dataset.estado = btn.dataset.estadoBtn;
            fila.querySelectorAll('button').forEach((b) => b.classList.toggle('active', b === btn));
            recalcularResumenLocalAsistencia();
        });
    });
}

function wireChipsAsistencia() {
    document.querySelectorAll('#coord-asistencia-manager .coord-asistencia-chip').forEach((chip) => {
        chip.addEventListener('click', () => {
            const filtro = chip.dataset.filtro;
            asistenciaFiltroChip = asistenciaFiltroChip === filtro ? null : filtro;
            document.querySelectorAll('#coord-asistencia-manager .coord-asistencia-chip').forEach((c) => c.classList.toggle('active', c.dataset.filtro === asistenciaFiltroChip));
            aplicarFiltrosAsistencia();
        });
    });
}

function renderAlertaAsistencia(alertas) {
    const panel = document.getElementById('coord-asistencia-alerta');
    const lista = document.getElementById('coord-asistencia-alerta-lista');

    if (!alertas.length) {
        panel.hidden = true;

        return;
    }

    panel.hidden = false;
    lista.innerHTML = alertas.map((a) => {
        const { nombres, apellidos } = nombreYApellidosAsistencia(a.nombre_completo);

        return `
            <div class="c-alert-item">
                <div class="c-alert-icon red"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="c-alert-body"><strong>${nombres} ${apellidos}</strong><span>Asistencia histórica: ${a.asistencia_historica_pct}%</span></div>
            </div>
        `;
    }).join('');
}

function filaAsistenciaHtml(est, indice) {
    const { nombres, apellidos } = nombreYApellidosAsistencia(est.nombre_completo);
    const color = PALETA_AVATAR_ASISTENCIA[indice % PALETA_AVATAR_ASISTENCIA.length];
    const estadoInicial = est.estado ?? 'PRESENTE';

    return `
        <div class="coord-asistencia-fila" data-id-estudiante="${est.id_estudiante}" data-nombre="${`${nombres} ${apellidos}`.toLowerCase()}" data-estado="${estadoInicial}">
            <div class="coord-asistencia-avatar ${color}">${inicialesAsistencia(nombres, apellidos)}</div>
            <div class="coord-asistencia-fila-info">
                <strong>${nombres} ${apellidos}</strong>
                <small>${est.codigo_estudiante}</small>
            </div>
            <div class="coord-asistencia-fila-botones">
                <button type="button" data-estado-btn="PRESENTE" class="${estadoInicial === 'PRESENTE' ? 'active' : ''}"><i class="bi bi-check-lg"></i> Presente</button>
                <button type="button" data-estado-btn="TARDANZA" class="${estadoInicial === 'TARDANZA' ? 'active' : ''}"><i class="bi bi-clock"></i> Tardanza</button>
                <button type="button" data-estado-btn="AUSENTE" class="${estadoInicial === 'AUSENTE' ? 'active' : ''}"><i class="bi bi-x-lg"></i> Ausente</button>
                <button type="button" data-estado-btn="JUSTIFICADO" class="${estadoInicial === 'JUSTIFICADO' ? 'active' : ''}"><i class="bi bi-file-earmark-text"></i> Justif.</button>
            </div>
        </div>
    `;
}

function cargarAsistenciaDelDia() {
    const lista = document.getElementById('coord-asistencia-lista');
    const vacio = document.getElementById('coord-asistencia-vacio');

    document.getElementById('coord-asistencia-fecha-texto').textContent = formatearFechaLargaAsistencia(asistenciaFechaActiva);

    if (!asistenciaCursoActivo) {
        lista.innerHTML = '';
        vacio.style.display = 'none';

        return;
    }

    lista.innerHTML = '<div class="coord-sesiones-lista-vacia">Cargando…</div>';
    vacio.style.display = 'none';

    fetch(`/api/coordinador/portafolios/asistencia?id_curso=${asistenciaCursoActivo}&fecha=${asistenciaFechaActiva}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const estudiantes = data.estudiantes ?? [];
            document.getElementById('coord-asistencia-total-matriculados').textContent = data.resumen?.total ?? estudiantes.length;

            if (!estudiantes.length) {
                lista.innerHTML = '';
                vacio.style.display = 'block';
                document.getElementById('coord-asistencia-alerta').hidden = true;
                recalcularResumenLocalAsistencia();

                return;
            }

            asistenciaFiltroChip = null;
            document.querySelectorAll('#coord-asistencia-manager .coord-asistencia-chip').forEach((c) => c.classList.remove('active'));

            renderAlertaAsistencia(data.alertas_bajo_70 ?? []);
            lista.innerHTML = estudiantes.map(filaAsistenciaHtml).join('');
            wireFilaBotonesAsistencia();
            recalcularResumenLocalAsistencia();
            aplicarFiltrosAsistencia();
        })
        .catch(() => {
            lista.innerHTML = '<div class="coord-sesiones-lista-vacia">No se pudo cargar la asistencia.</div>';
        });
}

function marcarTodosPresentesAsistencia() {
    document.querySelectorAll('#coord-asistencia-lista .coord-asistencia-fila').forEach((fila) => {
        fila.dataset.estado = 'PRESENTE';
        fila.querySelectorAll('button').forEach((b) => b.classList.toggle('active', b.dataset.estadoBtn === 'PRESENTE'));
    });
    recalcularResumenLocalAsistencia();
}

function guardarAsistenciaCoordinador() {
    const errorBox = document.getElementById('coord-asistencia-error');
    errorBox.textContent = '';

    const registros = Array.from(document.querySelectorAll('#coord-asistencia-lista .coord-asistencia-fila')).map((fila) => ({
        id_estudiante: Number(fila.dataset.idEstudiante),
        estado: fila.dataset.estado,
    }));

    if (!registros.length) return;

    fetch('/api/coordinador/portafolios/asistencia', {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ id_curso: asistenciaCursoActivo, fecha: asistenciaFechaActiva, registros }),
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then(() => {
            cargarAsistenciaDelDia();
            actualizarConteoAsistencia(asistenciaCursoActivo);
        })
        .catch((error) => {
            errorBox.textContent = error?.errors ? Object.values(error.errors).flat().join(' ') : 'No se pudo guardar la asistencia.';
        });
}

let subidaActiva = null;

/** Al pulsar "Subir" en una tarjeta se abre un modal para confirmar archivo, curso y semestre antes de enviar. */
function iniciarSubida(tipo, idCurso) {
    subidaActiva = { tipo, idCurso };
    abrirModalSubida();
}

function actualizarSemestreModalSubida() {
    const cursoSelect = document.getElementById('coord-mi-portafolio-curso');
    const modalSemestre = document.getElementById('coord-mi-portafolio-upload-semestre');
    const opcion = Array.from(cursoSelect.options).find((o) => o.value === subidaActiva.idCurso);
    const semestre = opcion?.dataset.semestre ?? '';

    modalSemestre.innerHTML = semestre ? `<option value="${semestre}">${semestre}</option>` : '<option value="">—</option>';
}

function abrirModalSubida() {
    const cursoSelect = document.getElementById('coord-mi-portafolio-curso');
    const modalCurso = document.getElementById('coord-mi-portafolio-upload-curso');

    modalCurso.innerHTML = cursoSelect.innerHTML;
    modalCurso.value = subidaActiva.idCurso;
    modalCurso.onchange = () => {
        subidaActiva.idCurso = modalCurso.value;
        actualizarSemestreModalSubida();
    };
    actualizarSemestreModalSubida();

    document.getElementById('coord-mi-portafolio-upload-archivo').value = '';
    document.getElementById('coord-mi-portafolio-upload-error').textContent = '';
    document.getElementById('coord-mi-portafolio-upload-modal').classList.add('show');
}

function cerrarModalSubida() {
    document.getElementById('coord-mi-portafolio-upload-modal').classList.remove('show');
    subidaActiva = null;
}

function confirmarSubidaArchivo() {
    const errorBox = document.getElementById('coord-mi-portafolio-upload-error');
    errorBox.textContent = '';

    const archivo = document.getElementById('coord-mi-portafolio-upload-archivo').files[0];
    if (!archivo) {
        errorBox.textContent = 'Selecciona un archivo.';
        return;
    }
    if (!subidaActiva) return;

    const idPeriodo = document.getElementById('coord-mi-portafolio-curso').dataset.idPeriodo;
    const tipoInfo = TIPOS_PORTAFOLIO.find((t) => t.valor === subidaActiva.tipo);
    const cursoTexto = document.getElementById('coord-mi-portafolio-upload-curso').selectedOptions[0]?.textContent ?? '';

    const formData = new FormData();
    formData.append('documento', archivo);
    formData.append('id_curso', subidaActiva.idCurso);
    formData.append('id_periodo', idPeriodo);
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
        .then(() => {
            cerrarModalSubida();
            cargarMiPortafolio();
        })
        .catch((error) => {
            errorBox.textContent = error?.mensaje ?? (error?.errors ? Object.values(error.errors).flat().join(' ') : 'No se pudo subir el documento.');
        });
}

export function initCoordinadorPortafolio() {
    const lista = document.getElementById('coord-revision-lista');
    if (!lista) return;

    capturarCursosDisponibles();
    cargarDocumentos();
    cargarKpis();
    wireGruposRevision();

    document.getElementById('coord-portafolio-filtro-docente')?.addEventListener('change', () => {
        actualizarCursosPorDocente();
        renderGruposRevision();
    });

    ['coord-portafolio-filtro-curso', 'coord-portafolio-filtro-estado'].forEach((id) => {
        document.getElementById(id)?.addEventListener('change', renderGruposRevision);
    });

    document.getElementById('coord-portafolio-cerrar')?.addEventListener('click', cerrarModal);
    document.getElementById('coord-portafolio-analizar-ia')?.addEventListener('click', analizarIA);
    document.getElementById('coord-portafolio-aprobar')?.addEventListener('click', () => validar('APROBADO'));
    document.getElementById('coord-portafolio-observar')?.addEventListener('click', () => validar('OBSERVADO'));

    document.querySelectorAll('.c-tab-btn').forEach((btn) => {
        btn.addEventListener('click', () => cambiarTab(btn.dataset.tab));
    });
    document.getElementById('coord-mi-portafolio-curso')?.addEventListener('change', cargarMiPortafolio);
    document.getElementById('coord-mi-portafolio-upload-cancelar')?.addEventListener('click', cerrarModalSubida);
    document.getElementById('coord-mi-portafolio-upload-confirmar')?.addEventListener('click', confirmarSubidaArchivo);

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
    document.getElementById('coord-notas-curso')?.addEventListener('change', (e) => seleccionarCursoNotas(e.target.value));
    document.getElementById('coord-notas-unidad')?.addEventListener('change', cargarNotasDelParcial);
    document.getElementById('coord-notas-buscar')?.addEventListener('input', aplicarFiltroNotas);
    document.getElementById('coord-notas-guardar-todo')?.addEventListener('click', guardarNotasCoordinador);
    document.getElementById('coord-notas-imprimir')?.addEventListener('click', imprimirNotas);

    document.getElementById('coord-asistencia-volver')?.addEventListener('click', cerrarGestorAsistencia);
    document.getElementById('coord-asistencia-curso')?.addEventListener('change', (e) => seleccionarCursoAsistencia(e.target.value));
    document.getElementById('coord-asistencia-fecha-anterior')?.addEventListener('click', () => cambiarFechaAsistencia(-1));
    document.getElementById('coord-asistencia-fecha-siguiente')?.addEventListener('click', () => cambiarFechaAsistencia(1));
    document.getElementById('coord-asistencia-fecha-hoy')?.addEventListener('click', () => {
        asistenciaFechaActiva = new Date().toISOString().slice(0, 10);
        cargarAsistenciaDelDia();
    });
    document.getElementById('coord-asistencia-buscar')?.addEventListener('input', aplicarFiltrosAsistencia);
    document.getElementById('coord-asistencia-marcar-todos')?.addEventListener('click', marcarTodosPresentesAsistencia);
    document.getElementById('coord-asistencia-guardar')?.addEventListener('click', guardarAsistenciaCoordinador);
    wireChipsAsistencia();
}

document.addEventListener('DOMContentLoaded', initCoordinadorPortafolio);
