import Chart from 'chart.js/auto';

const TEAL = '#1ABFA0';
const GOLD = '#C9922A';
const NAVY = '#0B1C3A';
const RED = '#E05050';

function renderRendimiento(programas) {
    const canvas = document.getElementById('dir-chart-rendimiento');
    if (!canvas) return;

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: programas.map((p) => p.programa),
            datasets: [{
                label: 'Rendimiento (%)',
                data: programas.map((p) => p.porcentaje ?? 0),
                backgroundColor: [TEAL, GOLD, NAVY, RED, '#7c3aed'],
                borderRadius: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: { x: { min: 0, max: 100 } },
        },
    });
}

function renderPortafolio(portafolio) {
    const canvas = document.getElementById('dir-chart-portafolio');
    if (!canvas) return;

    const distribucion = portafolio.distribucion ?? {};
    const etiquetas = { COMPLETO: 'Completo', EN_REVISION: 'En revisión', INCOMPLETO: 'Incompleto', OBSERVADO: 'Observado' };
    const colores = { COMPLETO: TEAL, EN_REVISION: GOLD, INCOMPLETO: RED, OBSERVADO: '#7c3aed' };

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: Object.keys(distribucion).map((k) => etiquetas[k] ?? k),
            datasets: [{
                data: Object.values(distribucion),
                backgroundColor: Object.keys(distribucion).map((k) => colores[k] ?? NAVY),
                borderColor: '#fff',
                borderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '50%',
            plugins: { legend: { position: 'bottom' } },
        },
    });
}

function renderSilabo(porCiclo) {
    const canvas = document.getElementById('dir-chart-silabo');
    if (!canvas) return;

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: porCiclo.map((c) => `Ciclo ${c.ciclo}`),
            datasets: [{
                label: 'Sílabos aprobados (%)',
                data: porCiclo.map((c) => c.porcentaje),
                backgroundColor: TEAL,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { min: 0, max: 100 } },
        },
    });
}

function renderRiesgo(datos) {
    const canvas = document.getElementById('dir-chart-riesgo');
    if (!canvas) return;

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: datos.map((d) => d.programa),
            datasets: [
                {
                    label: 'Asistencia promedio (%)',
                    data: datos.map((d) => d.asistencia_promedio ?? 0),
                    borderColor: TEAL,
                    backgroundColor: `${TEAL}22`,
                    tension: 0.4,
                    fill: true,
                },
                {
                    label: 'Riesgo promedio (score)',
                    data: datos.map((d) => d.riesgo_promedio ?? 0),
                    borderColor: RED,
                    backgroundColor: `${RED}22`,
                    tension: 0.4,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { min: 0, max: 100 } },
        },
    });
}

export function initDirectorAnalytics() {
    const root = document.getElementById('dir-chart-rendimiento');
    if (!root) return;

    fetch('/api/director/analytics', { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            renderRendimiento(data.rendimiento_programas ?? []);
            renderPortafolio(data.portafolio ?? {});
            renderSilabo(data.silabo_por_ciclo ?? []);
            renderRiesgo(data.riesgo_vs_asistencia ?? []);
        })
        .catch((error) => console.error(error));
}

document.addEventListener('DOMContentLoaded', initDirectorAnalytics);
