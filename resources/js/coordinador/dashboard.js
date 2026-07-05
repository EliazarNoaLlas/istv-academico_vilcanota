/**
 * Renderiza el dashboard de coordinador con datos reales desde
 * /api/coordinador/data (ya protegido por auth + role:coordinador). No
 * calcula riesgo ni reglas de negocio: solo cuenta y agrupa lo que la
 * API entrega.
 */
function renderAlertas(alertas, portafolios) {
    const root = document.getElementById('coord-dashboard-alertas');
    const pendientes = portafolios.filter((p) => p.estado !== 'COMPLETO');

    if (alertas.length === 0 && pendientes.length === 0) {
        root.innerHTML = '<p style="color:var(--text-muted);font-size:13px">Sin alertas registradas.</p>';

        return;
    }

    let html = '';

    if (pendientes.length > 0) {
        const nombres = pendientes
            .slice(0, 5)
            .map((p) => `${p.docente?.nombres ?? '—'}: ${p.curso?.nombre_curso ?? '—'}`)
            .join(', ');
        html += `
            <div class="c-alert-item">
                <div class="c-alert-icon gold"><i class="bi bi-folder-x"></i></div>
                <div class="c-alert-body"><strong>${pendientes.length} portafolio(s) incompleto(s)</strong><span>${nombres}</span></div>
            </div>
        `;
    }

    alertas.forEach((alerta) => {
        const iconos = { CRITICA: 'red', ALTA: 'red', MEDIA: 'gold', BAJA: 'teal' };
        html += `
            <div class="c-alert-item">
                <div class="c-alert-icon ${iconos[alerta.severidad] ?? 'navy'}"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="c-alert-body"><strong>${alerta.titulo}</strong><span>${alerta.detalle ?? ''}</span></div>
            </div>
        `;
    });

    root.innerHTML = html;
}

function renderCiclos(cursos) {
    const root = document.getElementById('coord-dashboard-ciclos');
    const porCiclo = {};
    cursos.forEach((c) => { porCiclo[c.semestre] = (porCiclo[c.semestre] ?? 0) + 1; });

    const ciclos = Object.keys(porCiclo).sort();
    if (ciclos.length === 0) {
        root.innerHTML = '<p style="color:var(--text-muted);font-size:13px">Sin cursos registrados.</p>';

        return;
    }

    const max = Math.max(...Object.values(porCiclo));
    root.innerHTML = ciclos.map((ciclo) => {
        const valor = porCiclo[ciclo];
        const pct = Math.round((valor / max) * 100);

        return `
            <div class="c-bar-row">
                <span class="c-bar-label">Ciclo ${ciclo}</span>
                <div class="c-bar-track"><div class="c-bar-fill" style="width:${pct}%"></div></div>
                <span class="c-bar-val">${valor}</span>
            </div>
        `;
    }).join('');
}

export function initCoordinadorDashboard() {
    const root = document.getElementById('coord-dashboard-kpis');
    if (!root) return;

    fetch('/api/coordinador/data', { headers: { Accept: 'application/json' } })
        .then((res) => {
            if (!res.ok) throw new Error('No se pudo cargar la informacion del panel.');

            return res.json();
        })
        .then((data) => {
            const portafoliosPendientes = data.portafolios.filter((p) => p.estado !== 'COMPLETO').length;

            root.querySelector('[data-kpi="docentes"]').textContent = data.docentes.length;
            root.querySelector('[data-kpi="cursos"]').textContent = data.cursos.length;
            root.querySelector('[data-kpi="estudiantes"]').textContent = data.estudiantes.length;
            root.querySelector('[data-kpi="horarios"]').textContent = data.horarios.length;
            root.querySelector('[data-kpi="portafolios-pendientes"]').textContent = portafoliosPendientes;

            if (data.periodo_activo) {
                document.getElementById('coord-dashboard-periodo').textContent = ` — Periodo: ${data.periodo_activo.codigo}`;
            }

            renderAlertas(data.alertas, data.portafolios);
            renderCiclos(data.cursos);
        })
        .catch((error) => {
            root.querySelectorAll('[data-kpi]').forEach((el) => { el.textContent = '—'; });
            console.error(error);
        });
}

document.addEventListener('DOMContentLoaded', initCoordinadorDashboard);
