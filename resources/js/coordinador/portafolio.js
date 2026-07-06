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

function renderRowMio(doc) {
    const curso = doc.portafolio?.curso?.nombre_curso ?? '—';

    return `
        <tr>
            <td>${doc.titulo}</td>
            <td>${curso}</td>
            <td>${doc.tipo}</td>
            <td>${badgeEstado(doc)}</td>
            <td><button type="button" class="c-btn c-btn-outline c-btn-sm" data-eliminar-mio="${doc.id_documento}">Eliminar</button></td>
        </tr>
    `;
}

function cargarMiPortafolio() {
    const form = document.getElementById('coord-mi-portafolio-form');
    const tbody = document.getElementById('coord-mi-portafolio-tbody');
    if (!form || !tbody) return;

    fetch(`/api/coordinador/portafolios?id_docente=${form.dataset.idDocente}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const documentos = data.documentos ?? [];

            tbody.innerHTML = documentos.length
                ? documentos.map(renderRowMio).join('')
                : '<tr><td colspan="5" class="coord-portafolio-empty">Todavía no subiste documentos.</td></tr>';

            document.querySelectorAll('[data-eliminar-mio]').forEach((btn) => {
                btn.addEventListener('click', () => eliminarDocumentoMio(btn.dataset.eliminarMio));
            });
        })
        .catch((error) => {
            tbody.innerHTML = '<tr><td colspan="5" class="coord-portafolio-empty">No se pudo cargar tu portafolio.</td></tr>';
            console.error(error);
        });
}

function eliminarDocumentoMio(idDocumento) {
    if (!confirm('¿Eliminar este documento de tu portafolio?')) return;

    fetch(`/api/portafolios/documentos/${idDocumento}`, {
        method: 'DELETE',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
        .then((res) => res.json())
        .then(() => cargarMiPortafolio())
        .catch((error) => console.error(error));
}

function subirDocumentoMio(event) {
    event.preventDefault();
    const form = event.target;
    const errorBox = document.getElementById('coord-mi-portafolio-error');
    errorBox.textContent = '';

    fetch('/api/portafolios/documentos', {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: new FormData(form),
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then(() => {
            form.reset();
            cargarMiPortafolio();
        })
        .catch((error) => {
            errorBox.textContent = error?.mensaje ?? (error?.errors ? Object.values(error.errors).flat().join(' ') : 'No se pudo subir el documento.');
        });
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
    document.getElementById('coord-mi-portafolio-form')?.addEventListener('submit', subirDocumentoMio);
}

document.addEventListener('DOMContentLoaded', initCoordinadorPortafolio);
