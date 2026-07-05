const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const SEVERIDAD_ICONO = { CRITICA: 'red', ALTA: 'red', MEDIA: 'gold', BAJA: 'teal' };
const ESTADO_BADGE = { ABIERTA: 'c-badge-red', EN_SEGUIMIENTO: 'c-badge-gold', CERRADA: 'c-badge-green' };

function renderAlerta(alerta) {
    const contexto = [
        alerta.estudiante ? `${alerta.estudiante.nombres} ${alerta.estudiante.apellido_paterno ?? ''}` : null,
        alerta.docente?.usuario ? `${alerta.docente.usuario.nombres} ${alerta.docente.usuario.apellidos ?? ''}` : null,
        alerta.curso?.nombre_curso,
    ].filter(Boolean).join(' · ');

    const acciones = alerta.estado === 'CERRADA'
        ? ''
        : `
            <div style="display:flex;gap:8px;margin-top:8px">
                ${alerta.estado === 'ABIERTA' ? `<button type="button" class="c-btn c-btn-outline c-btn-sm" data-gestionar="${alerta.id_alerta}" data-estado="EN_SEGUIMIENTO">En seguimiento</button>` : ''}
                <button type="button" class="c-btn c-btn-primary c-btn-sm" data-gestionar="${alerta.id_alerta}" data-estado="CERRADA">Cerrar alerta</button>
            </div>
        `;

    return `
        <div class="c-alert-item" style="flex-direction:column;align-items:stretch">
            <div style="display:flex;gap:12px;align-items:flex-start">
                <div class="c-alert-icon ${SEVERIDAD_ICONO[alerta.severidad] ?? 'navy'}"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="c-alert-body" style="flex:1">
                    <strong>${alerta.titulo}</strong>
                    <span>${alerta.detalle ?? ''}</span>
                    ${contexto ? `<span style="display:block;font-size:11px;color:var(--text-muted)">${contexto}</span>` : ''}
                </div>
                <span class="c-badge ${ESTADO_BADGE[alerta.estado] ?? 'c-badge-navy'}">${alerta.estado}</span>
            </div>
            ${acciones}
        </div>
    `;
}

function cargar() {
    const root = document.getElementById('dir-alertas-lista');
    const estado = document.getElementById('dir-alertas-filtro-estado')?.value;
    const params = estado ? `?estado=${estado}` : '';

    fetch(`/api/director/alertas${params}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const alertas = data.alertas ?? [];

            root.innerHTML = alertas.length
                ? alertas.map(renderAlerta).join('')
                : '<p style="color:var(--text-muted);font-size:13px">No hay alertas para este filtro.</p>';

            wireAcciones();
        })
        .catch((error) => {
            root.innerHTML = '<p style="color:var(--text-muted);font-size:13px">No se pudo cargar las alertas.</p>';
            console.error(error);
        });
}

function wireAcciones() {
    document.querySelectorAll('[data-gestionar]').forEach((btn) => {
        btn.addEventListener('click', () => {
            fetch(`/api/director/alertas/${btn.dataset.gestionar}/gestionar`, {
                method: 'PATCH',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ estado: btn.dataset.estado }),
            })
                .then((res) => res.json())
                .then(() => cargar())
                .catch((error) => console.error(error));
        });
    });
}

export function initDirectorAlertas() {
    const root = document.getElementById('dir-alertas-lista');
    if (!root) return;

    cargar();
    document.getElementById('dir-alertas-filtro-estado')?.addEventListener('change', cargar);
}

document.addEventListener('DOMContentLoaded', initDirectorAlertas);
