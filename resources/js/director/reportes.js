const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

function renderRow(reporte) {
    const fecha = reporte.fecha_generacion
        ? new Date(reporte.fecha_generacion).toLocaleString('es-PE', { dateStyle: 'short', timeStyle: 'short' })
        : '—';

    return `
        <tr>
            <td>${reporte.titulo}</td>
            <td><span class="c-badge c-badge-navy">${reporte.formato}</span></td>
            <td>${reporte.usuario?.nombres ?? '—'}</td>
            <td>${fecha}</td>
            <td><a href="/api/director/reportes/${reporte.id_reporte}/descargar" class="c-btn c-btn-outline c-btn-sm"><i class="bi bi-download"></i> Descargar</a></td>
        </tr>
    `;
}

function cargarHistorial() {
    const tbody = document.getElementById('dir-reportes-tbody');

    fetch('/api/director/reportes', { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const reportes = data.reportes ?? [];

            tbody.innerHTML = reportes.length
                ? reportes.map(renderRow).join('')
                : '<tr><td colspan="5" class="c-table-empty">Todavía no se generó ningún reporte.</td></tr>';
        })
        .catch((error) => {
            tbody.innerHTML = '<tr><td colspan="5" class="c-table-empty">No se pudo cargar el historial.</td></tr>';
            console.error(error);
        });
}

function generar(event) {
    event.preventDefault();
    const form = event.target;
    const datos = Object.fromEntries(new FormData(form).entries());
    const boton = document.getElementById('dir-reportes-generar');
    boton.disabled = true;
    boton.textContent = 'Generando…';

    fetch('/api/director/reportes/generar', {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(datos),
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then((body) => {
            cargarHistorial();
            window.location.href = `/api/director/reportes/${body.reporte.id_reporte}/descargar`;
        })
        .catch((error) => {
            alert(error?.message ?? 'No se pudo generar el reporte.');
        })
        .finally(() => {
            boton.disabled = false;
            boton.innerHTML = '<i class="bi bi-download"></i> Generar y descargar';
        });
}

export function initDirectorReportes() {
    const tbody = document.getElementById('dir-reportes-tbody');
    if (!tbody) return;

    cargarHistorial();
    document.getElementById('dir-reportes-form')?.addEventListener('submit', generar);
}

document.addEventListener('DOMContentLoaded', initDirectorReportes);
