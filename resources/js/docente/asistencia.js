/**
 * Registro de asistencia con datos reales desde /api/docente/asistencia.
 * Una sola sesion por curso+fecha (la crea el backend, no se duplica).
 * Un docente solo puede ver/editar asistencia de sus propios cursos.
 */
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
const ESTADOS = ['PRESENTE', 'TARDANZA', 'AUSENTE', 'JUSTIFICADO'];

let estadoActual = { idCurso: null, fecha: null };

function segmentedHtml(idEstudiante, estadoSeleccionado) {
    return `
        <div class="doc-segmented" data-id-estudiante="${idEstudiante}">
            ${ESTADOS.map((e) => `
                <button type="button" data-estado="${e}" class="${e === estadoSeleccionado ? 'active' : ''}">${e.slice(0, 4)}</button>
            `).join('')}
        </div>
    `;
}

function filaHtml(est) {
    const estadoInicial = est.estado ?? 'PRESENTE';

    return `
        <tr data-id-estudiante="${est.id_estudiante}">
            <td>${est.codigo_estudiante}</td>
            <td>${est.dni ?? '-'}</td>
            <td>${est.nombre_completo}</td>
            <td>${segmentedHtml(est.id_estudiante, estadoInicial)}</td>
            <td><input type="text" class="doc-asistencia-obs" data-campo="observacion" value="${est.observacion ?? ''}" placeholder="Observación…"></td>
        </tr>
    `;
}

function renderKpis(resumen) {
    const root = document.getElementById('doc-asistencia-kpis');
    root.querySelector('[data-kpi="presentes"]').textContent = resumen.presentes;
    root.querySelector('[data-kpi="ausentes"]').textContent = resumen.ausentes;
    root.querySelector('[data-kpi="tardanzas"]').textContent = resumen.tardanzas;
    root.querySelector('[data-kpi="justificados"]').textContent = resumen.justificados;
    root.querySelector('[data-kpi="porcentaje"]').textContent = resumen.porcentaje_asistencia !== null ? `${resumen.porcentaje_asistencia}%` : '—';
}

function renderAlerta(alertas) {
    const panel = document.getElementById('doc-asistencia-alerta');
    const lista = document.getElementById('doc-asistencia-alerta-lista');

    if (alertas.length === 0) {
        panel.hidden = true;

        return;
    }

    panel.hidden = false;
    lista.innerHTML = alertas.map((a) => `
        <div class="c-alert-item">
            <div class="c-alert-icon red"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="c-alert-body"><strong>${a.nombre_completo}</strong><span>Asistencia histórica: ${a.asistencia_historica_pct}%</span></div>
        </div>
    `).join('');
}

function renderEstadoSesion(sesion) {
    const el = document.getElementById('doc-asistencia-estado');
    el.hidden = false;
    el.textContent = sesion ? `Sesión registrada · ${sesion.estado}` : 'Todavía no se registró asistencia para esta fecha';
}

function wireSegmentedButtons() {
    document.querySelectorAll('.doc-segmented button').forEach((btn) => {
        btn.addEventListener('click', () => {
            btn.parentElement.querySelectorAll('button').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });
}

function cargarAsistencia() {
    const contenido = document.getElementById('doc-asistencia-contenido');
    const sinCurso = document.getElementById('doc-asistencia-sin-curso');
    const vacio = document.getElementById('doc-asistencia-empty');

    if (!estadoActual.idCurso || !estadoActual.fecha) {
        contenido.hidden = true;
        vacio.hidden = true;
        sinCurso.hidden = false;
        document.getElementById('doc-asistencia-estado').hidden = true;

        return;
    }

    sinCurso.hidden = true;

    fetch(`/api/docente/asistencia?id_curso=${estadoActual.idCurso}&fecha=${estadoActual.fecha}`, { headers: { Accept: 'application/json' } })
        .then((res) => {
            if (!res.ok) throw new Error('No se pudo cargar la asistencia.');

            return res.json();
        })
        .then((data) => {
            renderEstadoSesion(data.sesion);

            if (data.estudiantes.length === 0) {
                contenido.hidden = true;
                vacio.hidden = false;

                return;
            }

            vacio.hidden = true;
            contenido.hidden = false;
            renderKpis(data.resumen);
            renderAlerta(data.alertas_bajo_70);
            document.getElementById('doc-asistencia-tbody').innerHTML = data.estudiantes.map(filaHtml).join('');
            wireSegmentedButtons();
        })
        .catch((error) => console.error(error));
}

function recolectarRegistros() {
    return Array.from(document.querySelectorAll('#doc-asistencia-tbody tr')).map((fila) => ({
        id_estudiante: Number(fila.dataset.idEstudiante),
        estado: fila.querySelector('.doc-segmented button.active')?.dataset.estado ?? 'PRESENTE',
        observacion: fila.querySelector('[data-campo="observacion"]').value || null,
    }));
}

function guardarAsistencia() {
    fetch('/api/docente/asistencia/guardar', {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ id_curso: estadoActual.idCurso, fecha: estadoActual.fecha, registros: recolectarRegistros() }),
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then(() => cargarAsistencia())
        .catch((error) => alert(error.mensaje ?? error.message ?? 'No se pudo guardar la asistencia.'));
}

function poblarCursos(select) {
    return fetch('/api/docente/cursos', { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            select.innerHTML = '<option value="">Selecciona un curso…</option>'
                + data.cursos.map((c) => `<option value="${c.id_curso}">${c.nombre_curso}</option>`).join('');
        });
}

export function initDocenteAsistencia() {
    const selectCurso = document.getElementById('doc-asistencia-curso');
    if (!selectCurso) return;

    const inputFecha = document.getElementById('doc-asistencia-fecha');
    inputFecha.value = new Date().toISOString().slice(0, 10);
    estadoActual.fecha = inputFecha.value;

    poblarCursos(selectCurso).then(() => {
        const params = new URLSearchParams(window.location.search);
        const idCursoInicial = params.get('curso');
        if (idCursoInicial) {
            selectCurso.value = idCursoInicial;
            estadoActual.idCurso = idCursoInicial;
            cargarAsistencia();
        }
    });

    selectCurso.addEventListener('change', () => {
        estadoActual.idCurso = selectCurso.value || null;
        cargarAsistencia();
    });

    inputFecha.addEventListener('change', () => {
        estadoActual.fecha = inputFecha.value;
        cargarAsistencia();
    });

    document.getElementById('doc-asistencia-guardar').addEventListener('click', guardarAsistencia);
}

document.addEventListener('DOMContentLoaded', initDocenteAsistencia);
