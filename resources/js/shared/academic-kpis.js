/**
 * Helper compartido por director/dashboard.js y jua/dashboard.js: ambos
 * muestran los mismos 3 KPI institucionales basicos desde /api/academic/*.
 * Evita duplicar el mismo fetch en dos archivos identicos.
 */
export function renderAcademicKpis(rootId) {
    const root = document.getElementById(rootId);
    if (!root) return;

    Promise.all([
        fetch('/api/academic/docentes', { headers: { Accept: 'application/json' } }).then((r) => r.json()),
        fetch('/api/academic/cursos', { headers: { Accept: 'application/json' } }).then((r) => r.json()),
        fetch('/api/academic/estudiantes', { headers: { Accept: 'application/json' } }).then((r) => r.json()),
    ])
        .then(([docentes, cursos, estudiantes]) => {
            root.querySelector('[data-kpi="docentes"]').textContent = docentes.docentes.length;
            root.querySelector('[data-kpi="cursos"]').textContent = cursos.cursos.length;
            root.querySelector('[data-kpi="estudiantes"]').textContent = estudiantes.estudiantes.length;
        })
        .catch((error) => {
            root.querySelectorAll('[data-kpi]').forEach((el) => { el.textContent = '—'; });
            console.error(error);
        });
}
