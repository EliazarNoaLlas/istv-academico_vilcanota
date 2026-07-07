/**
 * Registro de notas con datos reales desde /api/docente/notas. El promedio
 * lo calcula MySQL (columna generada); el docente solo edita practica/teoria/examen.
 * Un docente solo puede ver/editar cursos que le pertenecen (validado en backend).
 */
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

let estadoActual = { idCurso: null, unidad: 'I', actaCerrada: false };

function notaBadge(promedio, notaMinima) {
    if (promedio === null || promedio === undefined) return '<span class="c-badge c-badge-navy">Sin registrar</span>';

    return Number(promedio) >= notaMinima
        ? '<span class="c-badge c-badge-green">Aprobado</span>'
        : '<span class="c-badge c-badge-red">Desaprobado</span>';
}

function filaHtml(est, notaMinima, actaCerrada) {
    const nota = est.nota ?? {};
    const disabled = actaCerrada ? 'disabled' : '';

    return `
        <tr data-id-matricula-curso="${est.id_matricula_curso}">
            <td>${est.codigo_estudiante}</td>
            <td>${est.dni ?? '-'}</td>
            <td>${est.nombre_completo}</td>
            <td><input type="number" class="doc-notas-input" data-campo="practica" min="0" max="20" step="0.5" value="${nota.practica ?? ''}" ${disabled}></td>
            <td><input type="number" class="doc-notas-input" data-campo="teoria" min="0" max="20" step="0.5" value="${nota.teoria ?? ''}" ${disabled}></td>
            <td><input type="number" class="doc-notas-input" data-campo="examen" min="0" max="20" step="0.5" value="${nota.examen ?? ''}" ${disabled}></td>
            <td data-promedio>${nota.promedio ?? '—'}</td>
            <td data-badge>${notaBadge(nota.promedio, notaMinima)}</td>
        </tr>
    `;
}

function renderKpis(resumen) {
    const root = document.getElementById('doc-notas-kpis');
    root.querySelector('[data-kpi="total"]').textContent = resumen.total;
    root.querySelector('[data-kpi="aprobados"]').textContent = resumen.aprobados;
    root.querySelector('[data-kpi="desaprobados"]').textContent = resumen.desaprobados;
    root.querySelector('[data-kpi="promedio"]').textContent = resumen.promedio_general ?? '—';
}

function renderActaEstado(actaCerrada) {
    const el = document.getElementById('doc-notas-acta-estado');
    el.hidden = false;
    el.className = `doc-notas-acta-estado ${actaCerrada ? 'cerrado' : 'abierto'}`;
    el.textContent = actaCerrada ? 'Acta cerrada' : 'Acta abierta';

    document.getElementById('doc-notas-cerrar').disabled = actaCerrada;
    document.getElementById('doc-notas-guardar').disabled = actaCerrada;
}

function cargarNotas() {
    const tbody = document.getElementById('doc-notas-tbody');
    const contenido = document.getElementById('doc-notas-contenido');
    const sinCurso = document.getElementById('doc-notas-sin-curso');
    const vacio = document.getElementById('doc-notas-empty');

    if (!estadoActual.idCurso) {
        contenido.hidden = true;
        vacio.hidden = true;
        sinCurso.hidden = false;
        document.getElementById('doc-notas-acta-estado').hidden = true;

        return;
    }

    sinCurso.hidden = true;

    fetch(`/api/docente/notas?id_curso=${estadoActual.idCurso}&unidad=${estadoActual.unidad}`, { headers: { Accept: 'application/json' } })
        .then((res) => {
            if (!res.ok) throw new Error('No se pudo cargar la información de notas.');

            return res.json();
        })
        .then((data) => {
            estadoActual.actaCerrada = data.acta_cerrada;
            renderActaEstado(data.acta_cerrada);

            if (data.estudiantes.length === 0) {
                contenido.hidden = true;
                vacio.hidden = false;

                return;
            }

            vacio.hidden = true;
            contenido.hidden = false;
            renderKpis(data.resumen);
            tbody.innerHTML = data.estudiantes.map((e) => filaHtml(e, data.resumen.nota_minima, data.acta_cerrada)).join('');
        })
        .catch((error) => console.error(error));
}

function recolectarNotas() {
    return Array.from(document.querySelectorAll('#doc-notas-tbody tr')).map((fila) => ({
        id_matricula_curso: Number(fila.dataset.idMatriculaCurso),
        practica: fila.querySelector('[data-campo="practica"]').value || null,
        teoria: fila.querySelector('[data-campo="teoria"]').value || null,
        examen: fila.querySelector('[data-campo="examen"]').value || null,
    }));
}

function guardarBorrador() {
    fetch('/api/docente/notas/guardar', {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ id_curso: estadoActual.idCurso, unidad: estadoActual.unidad, notas: recolectarNotas() }),
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then(() => cargarNotas())
        .catch((error) => alert(error.mensaje ?? error.message ?? 'No se pudieron guardar las notas.'));
}

function cerrarActa() {
    if (!confirm('¿Cerrar el acta de esta unidad? Ya no podrás editar las notas después de esto.')) return;

    fetch('/api/docente/notas/cerrar-acta', {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ id_curso: estadoActual.idCurso, unidad: estadoActual.unidad }),
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then(() => cargarNotas())
        .catch((error) => alert(error.mensaje ?? error.message ?? 'No se pudo cerrar el acta.'));
}

function poblarCursos(select) {
    return fetch('/api/docente/cursos', { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            select.innerHTML = '<option value="">Selecciona un curso…</option>'
                + data.cursos.map((c) => `<option value="${c.id_curso}">${c.nombre_curso}</option>`).join('');
        });
}

export function initDocenteNotas() {
    const selectCurso = document.getElementById('doc-notas-curso');
    if (!selectCurso) return;

    const selectUnidad = document.getElementById('doc-notas-unidad');

    poblarCursos(selectCurso).then(() => {
        const params = new URLSearchParams(window.location.search);
        const idCursoInicial = params.get('curso');
        if (idCursoInicial) {
            selectCurso.value = idCursoInicial;
            estadoActual.idCurso = idCursoInicial;
            cargarNotas();
        }
    });

    selectCurso.addEventListener('change', () => {
        estadoActual.idCurso = selectCurso.value || null;
        cargarNotas();
    });

    selectUnidad.addEventListener('change', () => {
        estadoActual.unidad = selectUnidad.value;
        cargarNotas();
    });

    document.getElementById('doc-notas-guardar').addEventListener('click', guardarBorrador);
    document.getElementById('doc-notas-cerrar').addEventListener('click', cerrarActa);
}

document.addEventListener('DOMContentLoaded', initDocenteNotas);
