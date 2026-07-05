const BADGE_ESTADO = { APROBADO: 'c-badge-green', DESAPROBADO: 'c-badge-red', PENDIENTE: 'c-badge-navy' };

function renderRow(nota) {
    const estudiante = nota.matricula_curso?.matricula?.estudiante;
    const curso = nota.matricula_curso?.curso;
    const nombreEstudiante = estudiante ? `${estudiante.nombres} ${estudiante.apellido_paterno ?? ''}` : '—';

    return `
        <tr>
            <td>${nombreEstudiante}</td>
            <td>${curso?.nombre_curso ?? '—'}</td>
            <td>${nota.unidad}</td>
            <td>${nota.practica ?? '—'}</td>
            <td>${nota.teoria ?? '—'}</td>
            <td>${nota.examen ?? '—'}</td>
            <td>${nota.promedio ?? '—'}</td>
            <td><span class="c-badge ${BADGE_ESTADO[nota.estado] ?? 'c-badge-navy'}">${nota.estado}</span></td>
        </tr>
    `;
}

function cargar() {
    const tbody = document.getElementById('dir-notas-tbody');
    const idCurso = document.getElementById('dir-notas-filtro-curso')?.value;
    const unidad = document.getElementById('dir-notas-filtro-unidad')?.value;
    const params = new URLSearchParams();
    if (idCurso) params.set('id_curso', idCurso);
    if (unidad) params.set('unidad', unidad);

    fetch(`/api/director/notas?${params.toString()}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const notas = data.notas ?? [];
            const resumen = data.resumen ?? {};

            tbody.innerHTML = notas.length
                ? notas.map(renderRow).join('')
                : '<tr><td colspan="8" class="c-table-empty">No hay notas para este filtro.</td></tr>';

            const root = document.getElementById('dir-notas-kpis');
            root.querySelector('[data-kpi="total"]').textContent = resumen.total ?? 0;
            root.querySelector('[data-kpi="aprobados"]').textContent = resumen.aprobados ?? 0;
            root.querySelector('[data-kpi="desaprobados"]').textContent = resumen.desaprobados ?? 0;
            root.querySelector('[data-kpi="promedio"]').textContent = resumen.promedio_general ?? '—';
        })
        .catch((error) => {
            tbody.innerHTML = '<tr><td colspan="8" class="c-table-empty">No se pudo cargar el registro de notas.</td></tr>';
            console.error(error);
        });
}

export function initDirectorNotas() {
    const tbody = document.getElementById('dir-notas-tbody');
    if (!tbody) return;

    cargar();
    document.getElementById('dir-notas-filtro-curso')?.addEventListener('change', cargar);
    document.getElementById('dir-notas-filtro-unidad')?.addEventListener('change', cargar);
}

document.addEventListener('DOMContentLoaded', initDirectorNotas);
