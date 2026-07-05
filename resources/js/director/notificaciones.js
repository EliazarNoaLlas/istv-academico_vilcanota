const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

function renderNotificacion(n) {
    return `
        <div class="c-alert-item" style="background:${n.leido ? 'transparent' : 'rgba(26,191,160,.04)'};border-radius:8px;padding:14px">
            <div class="c-alert-icon ${n.leido ? 'navy' : 'teal'}"><i class="bi bi-bell"></i></div>
            <div class="c-alert-body" style="flex:1">
                <strong>${n.titulo}</strong>
                <span>${n.detalle ?? ''}</span>
            </div>
            ${n.leido ? '' : `<button type="button" class="c-btn c-btn-outline c-btn-sm" data-leer="${n.id_notificacion}">Marcar leída</button>`}
        </div>
    `;
}

function cargar() {
    const root = document.getElementById('dir-notificaciones-lista');

    fetch('/api/notificaciones', { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const notificaciones = data.notificaciones ?? [];

            root.innerHTML = notificaciones.length
                ? notificaciones.map(renderNotificacion).join('')
                : '<p style="color:var(--text-muted);font-size:13px">No tiene notificaciones.</p>';

            document.querySelectorAll('[data-leer]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    fetch(`/api/notificaciones/${btn.dataset.leer}/leer`, {
                        method: 'PATCH',
                        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    }).then(() => cargar());
                });
            });
        })
        .catch((error) => {
            root.innerHTML = '<p style="color:var(--text-muted);font-size:13px">No se pudo cargar las notificaciones.</p>';
            console.error(error);
        });
}

export function initDirectorNotificaciones() {
    const root = document.getElementById('dir-notificaciones-lista');
    if (!root) return;

    cargar();

    document.getElementById('dir-notificaciones-marcar-todas')?.addEventListener('click', () => {
        fetch('/api/notificaciones/marcar-todas-leidas', {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
        }).then(() => cargar());
    });
}

document.addEventListener('DOMContentLoaded', initDirectorNotificaciones);
