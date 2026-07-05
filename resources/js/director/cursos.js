const BADGE_ESTADO = { ACTIVO: 'c-badge-green', INACTIVO: 'c-badge-gold', ARCHIVADO: 'c-badge-navy' };

function renderRow(curso) {
    const docente = curso.docente ? `${curso.docente.usuario?.nombres ?? ''} ${curso.docente.usuario?.apellidos ?? ''}` : '—';

    return `
        <tr>
            <td>${curso.nombre_curso}</td>
            <td>${curso.programa?.nombre ?? '—'}</td>
            <td>${curso.semestre}</td>
            <td>${curso.total_horas}h</td>
            <td>${docente}</td>
            <td><span class="c-badge ${BADGE_ESTADO[curso.estado] ?? 'c-badge-navy'}">${curso.estado}</span></td>
        </tr>
    `;
}

function filtrosActuales() {
    const params = {};
    const q = document.getElementById('dir-cursos-search')?.value;
    const idPrograma = document.getElementById('dir-cursos-filtro-programa')?.value;
    const semestre = document.getElementById('dir-cursos-filtro-semestre')?.value;
    if (q) params.q = q;
    if (idPrograma) params.id_programa = idPrograma;
    if (semestre) params.semestre = semestre;

    return params;
}

function cargarCursos() {
    const tbody = document.getElementById('dir-cursos-tbody');
    const query = new URLSearchParams(filtrosActuales()).toString();

    fetch(`/api/director/cursos${query ? `?${query}` : ''}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const cursos = data.cursos ?? [];

            tbody.innerHTML = cursos.length
                ? cursos.map(renderRow).join('')
                : '<tr><td colspan="6" class="c-table-empty">No hay cursos para este filtro.</td></tr>';

            const root = document.getElementById('dir-cursos-kpis');
            root.querySelector('[data-kpi="total"]').textContent = cursos.length;
            root.querySelector('[data-kpi="activos"]').textContent = cursos.filter((c) => c.estado === 'ACTIVO').length;
            root.querySelector('[data-kpi="sin-docente"]').textContent = cursos.filter((c) => !c.id_docente).length;
            root.querySelector('[data-kpi="sin-programa"]').textContent = cursos.filter((c) => !c.id_programa).length;
        })
        .catch((error) => {
            tbody.innerHTML = '<tr><td colspan="6" class="c-table-empty">No se pudo cargar la lista de cursos.</td></tr>';
            console.error(error);
        });
}

export function initDirectorCursos() {
    const tbody = document.getElementById('dir-cursos-tbody');
    if (!tbody) return;

    cargarCursos();

    let debounce;
    document.getElementById('dir-cursos-search')?.addEventListener('input', () => {
        clearTimeout(debounce);
        debounce = setTimeout(cargarCursos, 300);
    });
    document.getElementById('dir-cursos-filtro-programa')?.addEventListener('change', cargarCursos);
    document.getElementById('dir-cursos-filtro-semestre')?.addEventListener('change', cargarCursos);
}

document.addEventListener('DOMContentLoaded', initDirectorCursos);
