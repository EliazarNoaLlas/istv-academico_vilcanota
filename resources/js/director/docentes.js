/** Lista de docentes con carga horaria real desde /api/director/docentes. */
function renderRow(docente) {
    const cursos = docente.cursos_count ?? 0;
    const carga = docente.carga_horaria ?? 0;
    const badge = carga > 20 ? 'c-badge-red' : 'c-badge-green';

    return `
        <tr>
            <td>${docente.usuario?.nombres ?? ''} ${docente.usuario?.apellidos ?? ''}</td>
            <td>${docente.especialidad ?? '—'}</td>
            <td>${cursos}</td>
            <td><span class="c-badge ${badge}">${carga}h</span></td>
            <td><span class="c-badge c-badge-green">${docente.estado_academico}</span></td>
        </tr>
    `;
}

export function initDirectorDocentes() {
    const tbody = document.getElementById('dir-docentes-tbody');
    if (!tbody) return;

    fetch('/api/director/docentes', { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const docentes = data.docentes ?? [];

            tbody.innerHTML = docentes.length
                ? docentes.map(renderRow).join('')
                : '<tr><td colspan="5" class="c-table-empty">No hay docentes registrados.</td></tr>';

            const root = document.getElementById('dir-docentes-kpis');
            const totalCursos = docentes.reduce((acc, d) => acc + (d.cursos_count ?? 0), 0);
            const cargaPromedio = docentes.length
                ? Math.round(docentes.reduce((acc, d) => acc + (d.carga_horaria ?? 0), 0) / docentes.length)
                : 0;

            root.querySelector('[data-kpi="total"]').textContent = docentes.length;
            root.querySelector('[data-kpi="carga-promedio"]').textContent = `${cargaPromedio}h`;
            root.querySelector('[data-kpi="cursos-asignados"]').textContent = totalCursos;
            root.querySelector('[data-kpi="sobrecarga"]').textContent = docentes.filter((d) => (d.carga_horaria ?? 0) > 20).length;
        })
        .catch((error) => {
            tbody.innerHTML = '<tr><td colspan="5" class="c-table-empty">No se pudo cargar la lista de docentes.</td></tr>';
            console.error(error);
        });
}

document.addEventListener('DOMContentLoaded', initDirectorDocentes);
