const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const ETIQUETAS = {
    institucion_nombre: 'Nombre de la institución',
    semestre_activo: 'Semestre académico activo',
    nota_minima_aprobatoria: 'Nota mínima aprobatoria',
    porcentaje_riesgo_asistencia: 'Umbral de asistencia para alerta (%)',
    max_horas_docente_semana: 'Máximo de horas semanales por docente',
    ia_predictiva_modelo: 'Modelo de IA predictiva activo',
    horarios_protegidos: 'Horarios protegidos (1 = sí, 0 = no)',
};

function renderCampos(configuracion) {
    const root = document.getElementById('dir-configuracion-campos');

    if (configuracion.length === 0) {
        root.innerHTML = '<p style="color:var(--text-muted);font-size:13px">No hay parámetros de configuración registrados.</p>';

        return;
    }

    root.innerHTML = configuracion.map((item) => `
        <div class="form-group">
            <label>${ETIQUETAS[item.clave] ?? item.clave}</label>
            <input type="text" name="${item.clave}" value="${item.valor ?? ''}">
            ${item.descripcion ? `<small style="color:var(--text-muted);font-size:11px">${item.descripcion}</small>` : ''}
        </div>
    `).join('');
}

function cargar() {
    fetch('/api/director/configuracion', { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => renderCampos(data.configuracion ?? []))
        .catch((error) => {
            document.getElementById('dir-configuracion-campos').innerHTML = '<p style="color:var(--text-muted);font-size:13px">No se pudo cargar la configuración.</p>';
            console.error(error);
        });
}

function guardar(event) {
    event.preventDefault();
    const form = event.target;
    const valores = Object.fromEntries(new FormData(form).entries());

    fetch('/api/director/configuracion', {
        method: 'PUT',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ valores }),
    })
        .then((res) => res.json())
        .then((data) => {
            renderCampos(data.configuracion ?? []);
            alert('Configuración actualizada.');
        })
        .catch((error) => console.error(error));
}

export function initDirectorConfiguracion() {
    const form = document.getElementById('dir-configuracion-form');
    if (!form) return;

    cargar();
    form.addEventListener('submit', guardar);
}

document.addEventListener('DOMContentLoaded', initDirectorConfiguracion);
