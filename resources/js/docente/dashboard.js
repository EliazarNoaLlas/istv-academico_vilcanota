import Chart from 'chart.js/auto';

/**
 * Renderiza el dashboard de docente con datos reales desde
 * /api/docente/dashboard (protegido por auth + role:docente). No inventa
 * datos: si un curso/horario/nota no existe en BD, se muestra un estado vacio.
 */
const TEAL = '#1ABFA0';
const GOLD = '#C9922A';
const NAVY = '#0B1C3A';
const RED = '#E05050';
const PURPLE = '#7c3aed';

function renderHero(docente, periodo) {
    const detalle = document.querySelector('[data-hero="detalle"]');
    if (!detalle) return;

    const partes = [docente.codigo_docente, docente.especialidad, docente.tipo_docente].filter(Boolean);
    const periodoTexto = periodo ? `Periodo ${periodo.codigo}` : 'Sin periodo académico activo';

    detalle.textContent = `${partes.join(' · ')} — ${periodoTexto}`;
}

function renderClasesHoy(clases) {
    const root = document.getElementById('doc-dashboard-clases');

    if (clases.length === 0) {
        root.innerHTML = '<p style="color:var(--text-muted);font-size:13px">No tienes clases programadas para hoy.</p>';

        return;
    }

    root.innerHTML = clases.map((c) => `
        <div class="doc-clase-item">
            <div class="c-alert-body"><strong>${c.curso ?? 'Curso'}</strong><span>${c.aula ?? 'Sin aula asignada'}</span></div>
            <span class="doc-clase-hora">${c.hora_inicio ?? '--:--'} - ${c.hora_fin ?? '--:--'}</span>
        </div>
    `).join('');
}

function renderPendientes(alertas) {
    const root = document.getElementById('doc-dashboard-pendientes');
    const items = [
        { valor: alertas.cursos_sin_notas, texto: 'curso(s) sin notas registradas', icono: 'red' },
        { valor: alertas.cursos_sin_asistencia, texto: 'curso(s) sin asistencia registrada', icono: 'gold' },
        { valor: alertas.portafolio_incompleto, texto: 'curso(s) con portafolio incompleto', icono: 'navy' },
    ].filter((i) => i.valor > 0);

    if (items.length === 0) {
        root.innerHTML = '<p style="color:var(--text-muted);font-size:13px">Sin pendientes académicos. Todo está al día.</p>';

        return;
    }

    root.innerHTML = items.map((i) => `
        <div class="c-alert-item">
            <div class="c-alert-icon ${i.icono}"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="c-alert-body"><strong>${i.valor} ${i.texto}</strong></div>
        </div>
    `).join('');
}

function renderSesiones(sesiones) {
    const root = document.getElementById('doc-dashboard-sesiones');

    if (sesiones.length === 0) {
        root.innerHTML = '<p style="color:var(--text-muted);font-size:13px">Todavía no has subido sesiones de aprendizaje.</p>';

        return;
    }

    const estadoIcono = { APROBADO: 'teal', EN_REVISION: 'gold', PENDIENTE: 'navy', RECHAZADO: 'red' };

    root.innerHTML = sesiones.map((s) => `
        <div class="c-alert-item">
            <div class="c-alert-icon ${estadoIcono[s.estado] ?? 'navy'}"><i class="bi bi-file-earmark-text"></i></div>
            <div class="c-alert-body"><strong>${s.titulo}</strong><span>${s.curso ?? ''} · Sesión ${s.numero_sesion ?? '-'} · ${s.fecha_subida ?? ''}</span></div>
        </div>
    `).join('');
}

function renderRendimientoChart(datos) {
    const canvas = document.getElementById('doc-chart-rendimiento');
    const conDatos = datos.filter((d) => d.promedio !== null);

    if (conDatos.length === 0) {
        canvas.closest('.doc-chart-wrap').hidden = true;
        document.getElementById('doc-chart-rendimiento-empty').hidden = false;

        return;
    }

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: datos.map((d) => d.curso),
            datasets: [{
                label: 'Promedio',
                data: datos.map((d) => d.promedio ?? 0),
                backgroundColor: TEAL,
                borderRadius: 6,
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

function renderAsistenciaChart(cursos) {
    const canvas = document.getElementById('doc-chart-asistencia');
    const conDatos = cursos.filter((c) => c.asistencia_promedio !== null && c.asistencia_promedio !== undefined);

    if (conDatos.length === 0) {
        canvas.closest('.doc-chart-wrap').hidden = true;
        document.getElementById('doc-chart-asistencia-empty').hidden = false;

        return;
    }

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: cursos.map((c) => c.curso),
            datasets: [{
                label: 'Asistencia (%)',
                data: cursos.map((c) => c.asistencia_promedio ?? 0),
                backgroundColor: GOLD,
                borderRadius: 6,
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

const PORTAFOLIO_COLORES = { COMPLETO: TEAL, EN_REVISION: GOLD, INCOMPLETO: RED, OBSERVADO: PURPLE, SIN_INICIAR: NAVY };
const PORTAFOLIO_ETIQUETAS = { COMPLETO: 'Completo', EN_REVISION: 'En revisión', INCOMPLETO: 'Incompleto', OBSERVADO: 'Observado', SIN_INICIAR: 'Sin iniciar' };

function renderPortafolioChart(portafolio) {
    const canvas = document.getElementById('doc-chart-portafolio');

    if (portafolio.total === 0) {
        canvas.closest('.doc-chart-wrap').hidden = true;
        document.getElementById('doc-chart-portafolio-empty').hidden = false;

        return;
    }

    const entradas = Object.entries(portafolio.distribucion).filter(([, cantidad]) => cantidad > 0);

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: entradas.map(([k]) => PORTAFOLIO_ETIQUETAS[k] ?? k),
            datasets: [{
                data: entradas.map(([, v]) => v),
                backgroundColor: entradas.map(([k]) => PORTAFOLIO_COLORES[k] ?? NAVY),
                borderColor: '#fff',
                borderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '55%',
            plugins: { legend: { position: 'bottom' } },
        },
    });
}

export function initDocenteDashboard() {
    const root = document.getElementById('doc-dashboard-kpis');
    if (!root) return;

    fetch('/api/docente/dashboard', { headers: { Accept: 'application/json' } })
        .then((res) => {
            if (!res.ok) throw new Error('No se pudo cargar la informacion del panel.');

            return res.json();
        })
        .then((data) => {
            Object.entries(data.kpis).forEach(([clave, valor]) => {
                const el = root.querySelector(`[data-kpi="${clave}"]`);
                if (el) el.textContent = valor ?? '—';
            });

            renderHero(data.docente, data.periodo_activo);
            renderClasesHoy(data.clases_hoy);
            renderPendientes(data.alertas);
            renderSesiones(data.ultimas_sesiones);
            renderRendimientoChart(data.rendimiento_por_curso);
            renderAsistenciaChart(data.asistencia_por_curso);
            renderPortafolioChart(data.portafolio);
        })
        .catch((error) => {
            root.querySelectorAll('[data-kpi]').forEach((el) => { el.textContent = '—'; });
            console.error(error);
        });
}

document.addEventListener('DOMContentLoaded', initDocenteDashboard);
