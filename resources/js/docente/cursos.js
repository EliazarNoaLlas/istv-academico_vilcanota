/**
 * Renderiza "Mis Cursos" con datos reales desde /api/docente/cursos
 * (cursos.id_docente del docente autenticado). Sin cursos asignados, se
 * muestra el estado vacio profesional en vez de una tabla en blanco.
 */
const PORTAFOLIO_BADGE = {
    COMPLETO: 'c-badge-green',
    EN_REVISION: 'c-badge-gold',
    INCOMPLETO: 'c-badge-red',
    OBSERVADO: 'c-badge-red',
    SIN_INICIAR: 'c-badge-navy',
};

const PORTAFOLIO_TEXTO = {
    COMPLETO: 'Portafolio completo',
    EN_REVISION: 'Portafolio en revisión',
    INCOMPLETO: 'Portafolio incompleto',
    OBSERVADO: 'Portafolio observado',
    SIN_INICIAR: 'Portafolio sin iniciar',
};

function cursoCardHtml(curso) {
    const promedio = curso.notas_avg_promedio !== null ? Number(curso.notas_avg_promedio).toFixed(1) : '—';
    const asistencia = curso.asistencia_promedio !== null ? `${curso.asistencia_promedio}%` : '—';
    const badge = PORTAFOLIO_BADGE[curso.portafolio_estado] ?? 'c-badge-navy';
    const badgeTexto = PORTAFOLIO_TEXTO[curso.portafolio_estado] ?? curso.portafolio_estado;

    return `
        <div class="doc-course-card" data-nombre="${curso.nombre_curso.toLowerCase()}">
            <div>
                <h3>${curso.nombre_curso}</h3>
                <div class="doc-course-meta">
                    ${curso.programa?.nombre ?? 'Sin programa'} · Semestre ${curso.semestre ?? '-'} · ${curso.aula_principal ?? 'Sin aula asignada'}
                </div>
            </div>

            <div class="doc-course-stats">
                <div class="doc-course-stat"><strong>${curso.estudiantes_count}</strong><span>Estudiantes</span></div>
                <div class="doc-course-stat"><strong>${promedio}</strong><span>Promedio</span></div>
                <div class="doc-course-stat"><strong>${asistencia}</strong><span>Asistencia</span></div>
            </div>

            <span class="c-badge ${badge}">${badgeTexto}</span>

            <div class="doc-course-actions">
                <a href="/docente/notas?curso=${curso.id_curso}" class="c-btn c-btn-primary c-btn-sm"><i class="bi bi-clipboard-data"></i> Ver notas</a>
                <a href="/docente/asistencia?curso=${curso.id_curso}" class="c-btn c-btn-outline c-btn-sm"><i class="bi bi-check2-square"></i> Asistencia</a>
                <a href="/docente/horario" class="c-btn c-btn-outline c-btn-sm"><i class="bi bi-calendar-week"></i> Horario</a>
                <a href="/docente/portafolio?curso=${curso.id_curso}" class="c-btn c-btn-outline c-btn-sm"><i class="bi bi-folder2-open"></i> Portafolio</a>
            </div>
        </div>
    `;
}

export function initDocenteCursos() {
    const grid = document.getElementById('doc-cursos-grid');
    if (!grid) return;

    const empty = document.getElementById('doc-cursos-empty');
    const periodoEl = document.getElementById('doc-cursos-periodo');
    const buscar = document.getElementById('doc-cursos-buscar');

    fetch('/api/docente/cursos', { headers: { Accept: 'application/json' } })
        .then((res) => {
            if (!res.ok) throw new Error('No se pudo cargar la informacion de cursos.');

            return res.json();
        })
        .then((data) => {
            periodoEl.textContent = data.periodo_activo
                ? `Periodo académico activo: ${data.periodo_activo.codigo}`
                : 'No hay un periodo académico activo.';

            if (data.cursos.length === 0) {
                grid.hidden = true;
                buscar.hidden = true;
                empty.hidden = false;

                return;
            }

            grid.innerHTML = data.cursos.map(cursoCardHtml).join('');

            buscar.addEventListener('input', () => {
                const q = buscar.value.trim().toLowerCase();
                grid.querySelectorAll('.doc-course-card').forEach((card) => {
                    card.hidden = q.length > 0 && !card.dataset.nombre.includes(q);
                });
            });
        })
        .catch((error) => {
            grid.innerHTML = '<p style="color:var(--text-muted);font-size:13px">No se pudo cargar la información de tus cursos.</p>';
            console.error(error);
        });
}

document.addEventListener('DOMContentLoaded', initDocenteCursos);
