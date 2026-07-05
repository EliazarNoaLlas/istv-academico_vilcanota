const ITEMS = [
    { clave: 'sin_docente', icono: 'person-x', color: 'red', titulo: 'Cursos sin docente' },
    { clave: 'sin_horario', icono: 'calendar-x', color: 'gold', titulo: 'Cursos sin horario' },
    { clave: 'sin_programa', icono: 'diagram-3', color: 'navy', titulo: 'Cursos sin programa asignado' },
    { clave: 'portafolio_incompleto', icono: 'folder-x', color: 'gold', titulo: 'Portafolio docente pendiente' },
    { clave: 'actas_pendientes', icono: 'clipboard-x', color: 'red', titulo: 'Actas pendientes de cierre' },
];

export function initCoordinadorValidaciones() {
    const root = document.getElementById('coord-validaciones-alertas');
    const kpis = document.getElementById('coord-validaciones-kpis');
    if (!root) return;

    fetch('/api/coordinador/validaciones', { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const pendientes = data.pendientes ?? {};

            ITEMS.forEach((item) => {
                const el = kpis.querySelector(`[data-kpi="${item.clave}"]`);
                if (el) el.textContent = pendientes[item.clave] ?? 0;
            });

            root.innerHTML = ITEMS.map((item) => {
                const valor = pendientes[item.clave] ?? 0;
                const detalle = valor === 0 ? 'Sin incidencias.' : 'Requiere revisión del coordinador.';

                return `
                    <div class="c-alert-item">
                        <div class="c-alert-icon ${item.color}"><i class="bi bi-${item.icono}"></i></div>
                        <div class="c-alert-body"><strong>${item.titulo}: ${valor}</strong><span>${detalle}</span></div>
                    </div>
                `;
            }).join('');
        })
        .catch((error) => {
            root.innerHTML = '<p style="color:var(--text-muted);font-size:13px">No se pudo cargar el control de validaciones.</p>';
            console.error(error);
        });
}

document.addEventListener('DOMContentLoaded', initCoordinadorValidaciones);
