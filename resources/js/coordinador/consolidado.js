const BADGE_ACTAS = {
    APROBADO: 'c-badge-green',
    EN_REVISION: 'c-badge-gold',
    OBSERVADO: 'c-badge-red',
    PENDIENTE: 'c-badge-navy',
};

function renderRiesgo(riesgo) {
    const root = document.getElementById('coord-consolidado-riesgo');
    const estudiantes = (riesgo.estudiantes ?? []).filter((e) => e.nivel === 'ALTO' || e.nivel === 'CRITICO');

    if (estudiantes.length === 0) {
        root.innerHTML = '<p style="color:var(--text-muted);font-size:13px">No se detectaron estudiantes con riesgo alto o crítico.</p>';

        return;
    }

    root.innerHTML = estudiantes.map((e) => `
        <div class="c-alert-item">
            <div class="c-alert-icon ${e.nivel === 'CRITICO' ? 'red' : 'gold'}"><i class="bi bi-graph-down-arrow"></i></div>
            <div class="c-alert-body">
                <strong>${e.nombres} — riesgo ${e.nivel}</strong>
                <span>${e.recomendacion}</span>
            </div>
        </div>
    `).join('');
}

function renderCursos(cursos) {
    const tbody = document.getElementById('coord-consolidado-tbody');

    tbody.innerHTML = cursos.length
        ? cursos.map((c) => `
            <tr>
                <td>${c.nombre_curso}</td>
                <td>${c.semestre}</td>
                <td>${c.docente ?? '—'}</td>
                <td>${c.promedio ?? '—'}</td>
                <td>${c.aprobados}</td>
                <td>${c.desaprobados}</td>
                <td><span class="c-badge ${BADGE_ACTAS[c.estado_actas] ?? 'c-badge-navy'}">${c.estado_actas}</span></td>
            </tr>
        `).join('')
        : '<tr><td colspan="7" class="coord-portafolio-empty">No hay cursos registrados.</td></tr>';
}

export function initCoordinadorConsolidado() {
    const tbody = document.getElementById('coord-consolidado-tbody');
    if (!tbody) return;

    fetch('/api/coordinador/consolidado', { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            renderRiesgo(data.riesgo ?? {});
            renderCursos(data.cursos ?? []);

            const root = document.getElementById('coord-consolidado-kpis');
            const enRiesgo = (data.riesgo?.estudiantes ?? []).filter((e) => e.nivel === 'ALTO' || e.nivel === 'CRITICO');
            root.querySelector('[data-kpi="riesgo"]').textContent = enRiesgo.length;
            root.querySelector('[data-kpi="baja-aprobacion"]').textContent = (data.cursos_baja_aprobacion ?? []).length;
            root.querySelector('[data-kpi="modelo"]').textContent = 'reglas-academicas-v1';
        })
        .catch((error) => {
            tbody.innerHTML = '<tr><td colspan="7" class="coord-portafolio-empty">No se pudo cargar el consolidado.</td></tr>';
            console.error(error);
        });
}

document.addEventListener('DOMContentLoaded', initCoordinadorConsolidado);
