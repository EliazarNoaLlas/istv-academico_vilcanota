import Chart from 'chart.js/auto';

/**
 * Analitica docente con datos reales desde /api/docente/analitica.
 * No inventa datos: si un curso no tiene notas/asistencia, se muestra vacio.
 */
const TEAL = '#1ABFA0';
const GOLD = '#C9922A';
const NAVY = '#0B1C3A';
const RED = '#E05050';

function renderRendimiento(datos) {
    const canvas = document.getElementById('doc-analitica-chart-rendimiento');
    const conDatos = datos.filter((d) => d.promedio !== null);

    if (conDatos.length === 0) {
        canvas.closest('.doc-chart-wrap').hidden = true;
        document.getElementById('doc-analitica-chart-rendimiento-empty').hidden = false;

        return;
    }

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: datos.map((d) => d.curso),
            datasets: [{ label: 'Promedio', data: datos.map((d) => d.promedio ?? 0), backgroundColor: TEAL, borderRadius: 6 }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { min: 0, max: 20 } },
        },
    });
}

function renderDistribucion(distribucion) {
    const canvas = document.getElementById('doc-analitica-chart-distribucion');
    const entradas = Object.entries(distribucion);
    const total = entradas.reduce((acc, [, v]) => acc + v, 0);

    if (total === 0) {
        canvas.closest('.doc-chart-wrap').hidden = true;
        document.getElementById('doc-analitica-chart-distribucion-empty').hidden = false;

        return;
    }

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: entradas.map(([rango]) => rango),
            datasets: [{
                data: entradas.map(([, v]) => v),
                backgroundColor: [RED, GOLD, TEAL, '#0284C7'],
                borderColor: '#fff',
                borderWidth: 2,
            }],
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '55%', plugins: { legend: { position: 'bottom' } } },
    });
}

function renderAsistencia(datos) {
    const canvas = document.getElementById('doc-analitica-chart-asistencia');
    const conDatos = datos.filter((d) => d.asistencia_promedio !== null && d.asistencia_promedio !== undefined);

    if (conDatos.length === 0) {
        canvas.closest('.doc-chart-wrap').hidden = true;
        document.getElementById('doc-analitica-chart-asistencia-empty').hidden = false;

        return;
    }

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: datos.map((d) => d.curso),
            datasets: [{ label: 'Asistencia (%)', data: datos.map((d) => d.asistencia_promedio ?? 0), backgroundColor: GOLD, borderRadius: 6 }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { min: 0, max: 100 } },
        },
    });
}

function renderEvolucion(datos) {
    const canvas = document.getElementById('doc-analitica-chart-evolucion');

    if (datos.length < 2) {
        canvas.closest('.doc-chart-wrap').hidden = true;
        document.getElementById('doc-analitica-chart-evolucion-empty').hidden = false;

        return;
    }

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: datos.map((d) => `Unidad ${d.unidad}`),
            datasets: [{
                label: 'Promedio',
                data: datos.map((d) => d.promedio),
                borderColor: NAVY,
                backgroundColor: `${NAVY}22`,
                tension: 0.4,
                fill: true,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { min: 0, max: 20 } },
        },
    });
}

function renderRiesgo(estudiantes) {
    const tbody = document.getElementById('doc-analitica-riesgo-tbody');

    if (estudiantes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="c-table-empty">Sin estudiantes en riesgo por ahora.</td></tr>';

        return;
    }

    tbody.innerHTML = estudiantes.map((e) => `
        <tr>
            <td>${e.nombre_completo}</td>
            <td>${e.curso}</td>
            <td>${e.promedio ?? '—'}</td>
            <td>${e.asistencia_pct !== null ? `${e.asistencia_pct}%` : '—'}</td>
            <td><span class="c-badge c-badge-red">${e.motivos.join(', ')}</span></td>
        </tr>
    `).join('');
}

export function initDocenteAnalitica() {
    const root = document.getElementById('doc-analitica-kpis');
    if (!root) return;

    fetch('/api/docente/analitica', { headers: { Accept: 'application/json' } })
        .then((res) => {
            if (!res.ok) throw new Error('No se pudo cargar la analítica.');

            return res.json();
        })
        .then((data) => {
            const { aprobados, desaprobados } = data.aprobados_vs_desaprobados;
            root.querySelector('[data-kpi="aprobados"]').textContent = aprobados;
            root.querySelector('[data-kpi="desaprobados"]').textContent = desaprobados;
            root.querySelector('[data-kpi="en_riesgo"]').textContent = data.estudiantes_en_riesgo.length;

            renderRendimiento(data.rendimiento_por_curso);
            renderDistribucion(data.distribucion_notas);
            renderAsistencia(data.asistencia_por_curso);
            renderEvolucion(data.evolucion_por_unidad);
            renderRiesgo(data.estudiantes_en_riesgo);
        })
        .catch((error) => {
            root.querySelectorAll('[data-kpi]').forEach((el) => { el.textContent = '—'; });
            console.error(error);
        });
}

document.addEventListener('DOMContentLoaded', initDocenteAnalitica);
