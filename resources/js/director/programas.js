const COLORES = ['teal', 'gold', 'navy', 'red'];

function renderCard(programa, indice) {
    return `
        <div class="c-stat-card ${COLORES[indice % COLORES.length]}">
            <i class="bi bi-mortarboard c-stat-icon"></i>
            <div class="c-stat-label">${programa.nombre}</div>
            <div class="c-stat-value">${programa.estudiantes_count}</div>
            <div class="c-stat-sub">estudiantes · ${programa.cursos_count} cursos · ${programa.duracion_ciclos ?? '—'} ciclos</div>
        </div>
    `;
}

export function initDirectorProgramas() {
    const root = document.getElementById('dir-programas-grid');
    if (!root) return;

    fetch('/api/director/programas', { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const programas = data.programas ?? [];
            root.innerHTML = programas.length
                ? programas.map(renderCard).join('')
                : '<p style="color:var(--text-muted);font-size:13px">No hay programas registrados.</p>';
        })
        .catch((error) => {
            root.innerHTML = '<p style="color:var(--text-muted);font-size:13px">No se pudo cargar la información de programas.</p>';
            console.error(error);
        });
}

document.addEventListener('DOMContentLoaded', initDirectorProgramas);
