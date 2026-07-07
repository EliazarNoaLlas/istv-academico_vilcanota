/**
 * Renderiza "Mi Horario" con datos reales desde /api/docente/horario
 * (horarios.id_docente del docente autenticado). Solo lectura: el docente
 * no puede editar horarios desde este panel.
 */
const DIAS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
const COLORES = ['#1ABFA0', '#C9922A', '#0B1C3A', '#E05050', '#7c3aed', '#0284C7', '#059669'];

function normalizarDia(dia) {
    return (dia ?? '').trim().toLowerCase()
        .replace('á', 'a').replace('é', 'e').replace('í', 'i').replace('ó', 'o').replace('ú', 'u');
}

function colorPorCurso(idCurso, mapa) {
    if (!mapa.has(idCurso)) {
        mapa.set(idCurso, COLORES[mapa.size % COLORES.length]);
    }

    return mapa.get(idCurso);
}

function renderGrid(horario) {
    const grid = document.getElementById('doc-horario-grid');
    const coloresPorCurso = new Map();

    grid.innerHTML = DIAS.map((dia) => {
        const clasesDia = horario
            .filter((h) => normalizarDia(h.dia) === normalizarDia(dia))
            .sort((a, b) => (a.hora_inicio ?? '').localeCompare(b.hora_inicio ?? ''));

        const contenido = clasesDia.length === 0
            ? '<div class="doc-horario-dia-vacio">Sin clases</div>'
            : clasesDia.map((h) => {
                const color = colorPorCurso(h.curso?.id_curso ?? h.id_curso, coloresPorCurso);

                return `
                    <div class="doc-horario-clase" style="border-left-color:${color}">
                        <strong>${h.curso?.nombre_curso ?? 'Curso'}</strong>
                        <span>${h.hora_inicio ?? '--:--'} - ${h.hora_fin ?? '--:--'}</span>
                        <span>${h.aulaAsignada?.nombre ?? h.aula ?? 'Sin aula asignada'}</span>
                    </div>
                `;
            }).join('');

        return `
            <div class="doc-horario-dia">
                <div class="doc-horario-dia-header">${dia}</div>
                <div class="doc-horario-dia-body">${contenido}</div>
            </div>
        `;
    }).join('');
}

function cargarHorario(idPeriodo) {
    const grid = document.getElementById('doc-horario-grid');
    const empty = document.getElementById('doc-horario-empty');
    const url = idPeriodo ? `/api/docente/horario?id_periodo=${idPeriodo}` : '/api/docente/horario';

    return fetch(url, { headers: { Accept: 'application/json' } })
        .then((res) => {
            if (!res.ok) throw new Error('No se pudo cargar el horario.');

            return res.json();
        })
        .then((data) => {
            if (data.horario.length === 0) {
                grid.hidden = true;
                empty.hidden = false;

                return data;
            }

            grid.hidden = false;
            empty.hidden = true;
            renderGrid(data.horario);

            return data;
        });
}

function poblarPeriodos(select, periodos, seleccionado) {
    select.innerHTML = periodos.map((p) => `
        <option value="${p.id_periodo}" ${seleccionado === p.id_periodo ? 'selected' : ''}>${p.codigo} — ${p.nombre}</option>
    `).join('');
}

export function initDocenteHorario() {
    const grid = document.getElementById('doc-horario-grid');
    if (!grid) return;

    const select = document.getElementById('doc-horario-periodo');
    const imprimir = document.getElementById('doc-horario-imprimir');

    imprimir.addEventListener('click', () => window.print());

    cargarHorario(null)
        .then((data) => {
            poblarPeriodos(select, data.periodos, data.periodo_seleccionado?.id_periodo);

            select.addEventListener('change', () => {
                cargarHorario(select.value).catch((error) => console.error(error));
            });
        })
        .catch((error) => {
            grid.innerHTML = '<p style="color:var(--text-muted);font-size:13px">No se pudo cargar tu horario.</p>';
            console.error(error);
        });
}

document.addEventListener('DOMContentLoaded', initDocenteHorario);
