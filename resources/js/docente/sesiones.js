/**
 * Sesiones de Aprendizaje con datos reales desde /api/docente/sesiones.
 * Modulo independiente del portafolio (tabla sesiones_aprendizaje propia).
 */
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

let idCursoActual = null;

const ESTADO_BADGE = { APROBADO: 'c-badge-green', EN_REVISION: 'c-badge-gold', PENDIENTE: 'c-badge-navy', RECHAZADO: 'c-badge-red' };
const ESTADO_TEXTO = { APROBADO: 'Aprobado', EN_REVISION: 'En revisión', PENDIENTE: 'Pendiente', RECHAZADO: 'Rechazado' };

function filaHtml(s) {
    const fecha = s.fecha_subida ? new Date(s.fecha_subida).toLocaleDateString('es-PE') : '-';

    return `
        <tr>
            <td>${s.numero_sesion ?? '-'}</td>
            <td>${s.titulo}</td>
            <td><span class="c-badge ${ESTADO_BADGE[s.estado] ?? 'c-badge-navy'}">${ESTADO_TEXTO[s.estado] ?? s.estado}</span></td>
            <td>${fecha}</td>
            <td>
                <a href="/api/docente/sesiones/${s.id_sesion}/descargar" class="c-btn c-btn-outline c-btn-sm"><i class="bi bi-download"></i></a>
                ${s.estado !== 'APROBADO' ? `<button type="button" class="c-btn c-btn-danger c-btn-sm" data-eliminar="${s.id_sesion}"><i class="bi bi-trash"></i></button>` : ''}
            </td>
        </tr>
    `;
}

function cargarSesiones() {
    const panel = document.getElementById('doc-sesiones-panel');
    const empty = document.getElementById('doc-sesiones-empty');
    const sinCurso = document.getElementById('doc-sesiones-sin-curso');

    if (!idCursoActual) {
        panel.hidden = true;
        empty.hidden = true;
        sinCurso.hidden = false;

        return;
    }

    sinCurso.hidden = true;

    fetch(`/api/docente/sesiones?id_curso=${idCursoActual}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const sesiones = data.sesiones ?? [];

            if (sesiones.length === 0) {
                panel.hidden = true;
                empty.hidden = false;

                return;
            }

            empty.hidden = true;
            panel.hidden = false;
            const tbody = document.getElementById('doc-sesiones-tbody');
            tbody.innerHTML = sesiones.map(filaHtml).join('');

            tbody.querySelectorAll('[data-eliminar]').forEach((btn) => {
                btn.addEventListener('click', () => eliminarSesion(btn.dataset.eliminar));
            });
        })
        .catch((error) => console.error(error));
}

function eliminarSesion(idSesion) {
    if (!confirm('¿Eliminar esta sesión de aprendizaje?')) return;

    fetch(`/api/docente/sesiones/${idSesion}`, {
        method: 'DELETE',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok || body.ok === false) throw body;

            return body;
        })
        .then(() => cargarSesiones())
        .catch((error) => alert(error.mensaje ?? 'No se pudo eliminar la sesión.'));
}

function abrirModal() {
    const form = document.getElementById('doc-sesiones-form');
    form.reset();
    document.getElementById('doc-sesiones-form-error').textContent = '';
    form.elements.namedItem('id_curso').value = idCursoActual;
    document.getElementById('doc-sesiones-modal').classList.add('show');
}

function cerrarModal() {
    document.getElementById('doc-sesiones-modal').classList.remove('show');
}

function enviarFormulario(event) {
    event.preventDefault();
    const formData = new FormData(event.target);

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
        .then(() => {
            cerrarModal();
            cargarSesiones();
        })
        .catch((error) => {
            document.getElementById('doc-sesiones-form-error').textContent = error.mensaje ?? error.message ?? 'No se pudo subir la sesión.';
        });
}

function poblarCursos(select) {
    return fetch('/api/docente/cursos', { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            select.innerHTML = '<option value="">Selecciona un curso…</option>'
                + data.cursos.map((c) => `<option value="${c.id_curso}">${c.nombre_curso}</option>`).join('');
        });
}

export function initDocenteSesiones() {
    const select = document.getElementById('doc-sesiones-curso');
    if (!select) return;

    const btnNueva = document.getElementById('doc-sesiones-nueva');

    poblarCursos(select);

    select.addEventListener('change', () => {
        idCursoActual = select.value || null;
        btnNueva.disabled = !idCursoActual;
        cargarSesiones();
    });

    btnNueva.addEventListener('click', abrirModal);
    document.getElementById('doc-sesiones-modal-cerrar').addEventListener('click', cerrarModal);
    document.getElementById('doc-sesiones-form').addEventListener('submit', enviarFormulario);
}

document.addEventListener('DOMContentLoaded', initDocenteSesiones);
