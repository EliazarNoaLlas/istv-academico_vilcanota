const BADGE_ESTADO = {
    REGULAR: 'c-badge-green',
    OBSERVADO: 'c-badge-gold',
    RIESGO: 'c-badge-red',
    RETIRADO: 'c-badge-navy',
    EGRESADO: 'c-badge-navy',
};

function renderRow(estudiante) {
    const promedio = estudiante.promedio_general !== null && estudiante.promedio_general !== undefined
        ? estudiante.promedio_general
        : '—';

    return `
        <tr>
            <td>${estudiante.codigo_estudiante}</td>
            <td>${estudiante.nombres} ${estudiante.apellido_paterno ?? ''} ${estudiante.apellido_materno ?? ''}</td>
            <td>${estudiante.programa?.nombre ?? '—'}</td>
            <td>${estudiante.ciclo}</td>
            <td>${promedio}</td>
            <td><span class="c-badge ${BADGE_ESTADO[estudiante.estado] ?? 'c-badge-navy'}">${estudiante.estado}</span></td>
        </tr>
    `;
}

function cargar() {
    const tbody = document.getElementById('coord-estudiantes-tbody');
    const ciclo = document.getElementById('coord-estudiantes-filtro-ciclo')?.value;
    const query = ciclo ? `?ciclo=${ciclo}` : '';

    fetch(`/api/coordinador/estudiantes${query}`, {headers: {Accept: 'application/json'}})
        .then((res) => res.json())
        .then((data) => {
            const estudiantes = data.estudiantes ?? [];

            tbody.innerHTML = estudiantes.length
                ? estudiantes.map(renderRow).join('')
                : '<tr><td colspan="6" class="coord-portafolio-empty">No hay estudiantes para este filtro.</td></tr>';

            const root = document.getElementById('coord-estudiantes-kpis');
            const promedios = estudiantes.map((e) => e.promedio_general).filter((p) => p !== null && p !== undefined);
            const promedioGeneral = promedios.length ? (promedios.reduce((a, b) => a + b, 0) / promedios.length).toFixed(1) : '—';

            root.querySelector('[data-kpi="total"]').textContent = estudiantes.length;
            root.querySelector('[data-kpi="promedio"]').textContent = promedioGeneral;
            root.querySelector('[data-kpi="observados"]').textContent = estudiantes.filter((e) => e.estado === 'OBSERVADO').length;
            root.querySelector('[data-kpi="riesgo"]').textContent = estudiantes.filter((e) => e.estado === 'RIESGO').length;
        })
        .catch((error) => {
            tbody.innerHTML = '<tr><td colspan="6" class="coord-portafolio-empty">No se pudo cargar la lista de estudiantes.</td></tr>';
            console.error(error);
        });
}

export function initCoordinadorEstudiantes() {
    const tbody = document.getElementById('coord-estudiantes-tbody');
    if (!tbody) return;

    cargar();
    document.getElementById('coord-estudiantes-filtro-ciclo')?.addEventListener('change', cargar);
}

document.addEventListener('DOMContentLoaded', initCoordinadorEstudiantes);
