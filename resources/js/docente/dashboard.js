/**
 * Renderiza los KPI del dashboard de docente con datos reales desde
 * /api/docente/cursos (ya protegido por auth + role:docente).
 */
export function initDocenteDashboard() {
    const root = document.getElementById('doc-dashboard-kpis');
    if (!root) return;

    fetch('/api/docente/cursos', { headers: { Accept: 'application/json' } })
        .then((res) => {
            if (!res.ok) throw new Error('No se pudo cargar la informacion de cursos.');
            return res.json();
        })
        .then((data) => {
            const cursos = data.cursos ?? [];
            const totalSesiones = cursos.reduce((acc, c) => acc + (c.sesiones_aprendizaje_count ?? 0), 0);

            root.querySelector('[data-kpi="mis-cursos"]').textContent = cursos.length;
            root.querySelector('[data-kpi="sesiones-registradas"]').textContent = totalSesiones;
        })
        .catch((error) => {
            root.querySelectorAll('[data-kpi]').forEach((el) => { el.textContent = '—'; });
            console.error(error);
        });
}

document.addEventListener('DOMContentLoaded', initDocenteDashboard);
