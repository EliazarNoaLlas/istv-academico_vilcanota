/**
 * Renderiza el dashboard de director con datos reales desde
 * /api/director/dashboard (protegido por auth + role:director). No calcula
 * riesgo ni reglas de negocio: solo pinta lo que la API entrega.
 */
function renderRendimiento(programas) {
    const root = document.getElementById('dir-dashboard-rendimiento');

    if (programas.length === 0) {
        root.innerHTML = '<p style="color:var(--text-muted);font-size:13px">Sin programas registrados.</p>';

        return;
    }

    const conDatos = programas.filter((p) => p.porcentaje !== null);

    root.innerHTML = programas.map((p) => {
        const valor = p.porcentaje ?? 0;
        const etiqueta = p.porcentaje !== null ? `${p.porcentaje}%` : 'Sin notas';

        return `
            <div class="c-bar-row">
                <span class="c-bar-label" title="${p.programa}">${p.programa}</span>
                <div class="c-bar-track"><div class="c-bar-fill" style="width:${valor}%"></div></div>
                <span class="c-bar-val">${etiqueta}</span>
            </div>
        `;
    }).join('');

    if (conDatos.length === 0) {
        root.innerHTML += '<p style="color:var(--text-muted);font-size:12px;margin-top:8px">Ningún programa tiene notas registradas todavía.</p>';
    }
}

const PORTAFOLIO_COLORES = {
    COMPLETO: 'var(--teal)',
    EN_REVISION: 'var(--gold)',
    INCOMPLETO: 'var(--red-alert)',
    OBSERVADO: '#7c3aed',
};

const PORTAFOLIO_ETIQUETAS = {
    COMPLETO: 'Completo',
    EN_REVISION: 'En revisión',
    INCOMPLETO: 'Incompleto',
    OBSERVADO: 'Observado',
};

function renderPortafolio(portafolio) {
    const root = document.getElementById('dir-dashboard-portafolio');
    const total = portafolio.total_registros;

    if (total === 0) {
        root.innerHTML = '<p style="color:var(--text-muted);font-size:13px">Sin registros de portafolio docente todavía.</p>';

        return;
    }

    const entradas = Object.entries(portafolio.distribucion).filter(([, cantidad]) => cantidad > 0);
    const circunferencia = 2 * Math.PI * 45;
    let acumulado = 0;

    const circulos = entradas.map(([clave, cantidad]) => {
        const proporcion = cantidad / total;
        const largo = proporcion * circunferencia;
        const offset = -acumulado;
        acumulado += largo;

        return `<circle cx="55" cy="55" r="45" fill="none" stroke="${PORTAFOLIO_COLORES[clave]}" stroke-width="12"
            stroke-dasharray="${largo} ${circunferencia - largo}" stroke-dashoffset="${offset}" stroke-linecap="round"/>`;
    }).join('');

    const leyenda = entradas.map(([clave, cantidad]) => {
        const pct = Math.round((cantidad / total) * 100);

        return `
            <div class="c-alert-item" style="border-bottom:none;padding:6px 0">
                <span style="width:10px;height:10px;border-radius:50%;background:${PORTAFOLIO_COLORES[clave]};display:inline-block;flex-shrink:0;margin-top:4px"></span>
                <div class="c-alert-body"><span>${PORTAFOLIO_ETIQUETAS[clave]} — ${cantidad} (${pct}%)</span></div>
            </div>
        `;
    }).join('');

    root.innerHTML = `
        <div class="dir-donut">
            <svg viewBox="0 0 110 110" width="110" height="110">
                <circle cx="55" cy="55" r="45" fill="none" stroke="var(--surface-2)" stroke-width="12"/>
                ${circulos}
            </svg>
            <div class="dir-donut-center"><strong>${portafolio.total_docentes}</strong><small>docentes</small></div>
        </div>
        <div class="dir-donut-legend">${leyenda}</div>
    `;
}

const SEVERIDAD_ICONO = { CRITICA: 'red', ALTA: 'red', MEDIA: 'gold', BAJA: 'teal' };

function renderAlertas(alertas) {
    const root = document.getElementById('dir-dashboard-alertas');

    if (alertas.length === 0) {
        root.innerHTML = '<p style="color:var(--text-muted);font-size:13px">Sin alertas tempranas abiertas.</p>';

        return;
    }

    root.innerHTML = alertas.map((alerta) => `
        <div class="c-alert-item">
            <div class="c-alert-icon ${SEVERIDAD_ICONO[alerta.severidad] ?? 'navy'}"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="c-alert-body"><strong>${alerta.titulo}</strong><span>${alerta.detalle ?? ''}</span></div>
        </div>
    `).join('');
}

const PROGRAMA_COLORES = ['teal', 'gold', 'navy', 'red'];

function renderProgramas(programas) {
    const root = document.getElementById('dir-dashboard-programas');
    if (!root) return;

    root.innerHTML = programas.length
        ? programas.map((programa, indice) => `
            <div class="c-stat-card ${PROGRAMA_COLORES[indice % PROGRAMA_COLORES.length]}">
                <i class="bi bi-mortarboard c-stat-icon"></i>
                <div class="c-stat-label">${programa.nombre}</div>
                <div class="c-stat-value">${programa.estudiantes_count}</div>
                <div class="c-stat-sub">estudiantes · ${programa.cursos_count} cursos · ${programa.duracion_ciclos ?? '—'} ciclos</div>
            </div>
        `).join('')
        : '<p style="color:var(--text-muted);font-size:13px">No hay programas registrados.</p>';
}

function cargarProgramas() {
    const root = document.getElementById('dir-dashboard-programas');
    if (!root) return;

    fetch('/api/director/programas', { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => renderProgramas(data.programas ?? []))
        .catch((error) => {
            root.innerHTML = '<p style="color:var(--text-muted);font-size:13px">No se pudo cargar la información de programas.</p>';
            console.error(error);
        });
}

function renderActividad(actividad) {
    const root = document.getElementById('dir-dashboard-actividad');

    if (actividad.length === 0) {
        root.innerHTML = '<p style="color:var(--text-muted);font-size:13px">Sin actividad registrada en el sistema todavía.</p>';

        return;
    }

    root.innerHTML = actividad.map((evento) => `
        <div class="c-alert-item">
            <div class="c-alert-icon teal"><i class="bi bi-clock-history"></i></div>
            <div class="c-alert-body"><strong>${evento.accion}</strong><span>${evento.usuario?.nombres ?? 'Sistema'} · ${evento.detalle ?? ''}</span></div>
        </div>
    `).join('');
}

export function initDirectorDashboard() {
    const root = document.getElementById('dir-dashboard-kpis');
    if (!root) return;

    cargarProgramas();

    fetch('/api/director/dashboard', { headers: { Accept: 'application/json' } })
        .then((res) => {
            if (!res.ok) throw new Error('No se pudo cargar la informacion del panel.');

            return res.json();
        })
        .then((data) => {
            Object.entries(data.kpis).forEach(([clave, valor]) => {
                const el = root.querySelector(`[data-kpi="${clave}"]`);
                if (el) el.textContent = valor;
            });

            renderRendimiento(data.rendimiento_programas);
            renderPortafolio(data.portafolio);
            renderAlertas(data.alertas);
            renderActividad(data.actividad);
        })
        .catch((error) => {
            root.querySelectorAll('[data-kpi]').forEach((el) => { el.textContent = '—'; });
            console.error(error);
        });
}

document.addEventListener('DOMContentLoaded', initDirectorDashboard);
