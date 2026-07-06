const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const BADGE_ESTADO = {
    REGULAR: 'c-badge-green',
    OBSERVADO: 'c-badge-gold',
    RIESGO: 'c-badge-red',
    RETIRADO: 'c-badge-navy',
    EGRESADO: 'c-badge-navy',
};

function renderRow(estudiante) {
    const promedio = estudiante.promedio_general !== null && estudiante.promedio_general !== undefined
        ? estudiante.promedio_general
        : '—';

    return `
        <tr>
            <td>${estudiante.codigo_estudiante}</td>
            <td>${estudiante.nombres} ${estudiante.apellido_paterno ?? ''} ${estudiante.apellido_materno ?? ''}</td>
            <td>${estudiante.programa?.nombre ?? '—'}</td>
            <td>${estudiante.ciclo}</td>
            <td>${promedio}</td>
            <td><span class="c-badge ${BADGE_ESTADO[estudiante.estado] ?? 'c-badge-navy'}">${estudiante.estado}</span></td>
        </tr>
    `;
}

function cargar() {
    const tbody = document.getElementById('dir-estudiantes-tbody');
    const idPrograma = document.getElementById('dir-estudiantes-filtro-programa')?.value;
    const ciclo = document.getElementById('dir-estudiantes-filtro-ciclo')?.value;
    const params = new URLSearchParams();
    if (idPrograma) params.set('id_programa', idPrograma);
    if (ciclo) params.set('ciclo', ciclo);

    fetch(`/api/director/estudiantes?${params.toString()}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const estudiantes = data.estudiantes ?? [];

            tbody.innerHTML = estudiantes.length
                ? estudiantes.map(renderRow).join('')
                : '<tr><td colspan="6" class="c-table-empty">No hay estudiantes para este filtro.</td></tr>';

            const root = document.getElementById('dir-estudiantes-kpis');
            const promedios = estudiantes.map((e) => e.promedio_general).filter((p) => p !== null && p !== undefined);
            const promedioGeneral = promedios.length ? (promedios.reduce((a, b) => a + b, 0) / promedios.length).toFixed(1) : '—';

            root.querySelector('[data-kpi="total"]').textContent = estudiantes.length;
            root.querySelector('[data-kpi="promedio"]').textContent = promedioGeneral;
            root.querySelector('[data-kpi="observados"]').textContent = estudiantes.filter((e) => e.estado === 'OBSERVADO').length;
            root.querySelector('[data-kpi="riesgo"]').textContent = estudiantes.filter((e) => e.estado === 'RIESGO').length;
        })
        .catch((error) => {
            tbody.innerHTML = '<tr><td colspan="6" class="c-table-empty">No se pudo cargar la lista de estudiantes.</td></tr>';
            console.error(error);
        });
}

function abrirModal() {
    document.getElementById('dir-estudiantes-form').reset();
    limpiarErrores();
    document.getElementById('dir-estudiantes-modal').classList.add('show');
}

function cerrarModal() {
    document.getElementById('dir-estudiantes-modal').classList.remove('show');
}

function limpiarErrores() {
    document.getElementById('dir-estudiantes-form-error').textContent = '';
    document.querySelectorAll('#dir-estudiantes-form .dir-usuarios-field-error').forEach((el) => { el.textContent = ''; });
    document.querySelectorAll('#dir-estudiantes-form .is-invalid').forEach((el) => el.classList.remove('is-invalid'));
}

function mostrarErrores(errores) {
    limpiarErrores();

    if (!errores) {
        document.getElementById('dir-estudiantes-form-error').textContent = 'No se pudo guardar el estudiante.';
        return;
    }

    Object.entries(errores).forEach(([campo, mensajes]) => {
        const contenedor = document.querySelector(`[data-error-for="${campo}"]`);
        const mensaje = Array.isArray(mensajes) ? mensajes[0] : mensajes;

        if (contenedor) {
            contenedor.textContent = mensaje;
            document.querySelector(`#dir-estudiantes-form [name="${campo}"]`)?.classList.add('is-invalid');
        } else {
            document.getElementById('dir-estudiantes-form-error').textContent = mensaje;
        }
    });
}

function enviarFormulario(event) {
    event.preventDefault();
    const datos = Object.fromEntries(new FormData(event.target));

    fetch('/api/director/estudiantes', {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(datos),
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then(() => {
            cerrarModal();
            cargar();
            alert('Estudiante registrado exitosamente.');
        })
        .catch((error) => mostrarErrores(error?.errors));
}

export function initDirectorEstudiantes() {
    const tbody = document.getElementById('dir-estudiantes-tbody');
    if (!tbody) return;

    cargar();
    document.getElementById('dir-estudiantes-filtro-programa')?.addEventListener('change', cargar);
    document.getElementById('dir-estudiantes-filtro-ciclo')?.addEventListener('change', cargar);

    document.getElementById('dir-estudiantes-nuevo')?.addEventListener('click', abrirModal);
    document.getElementById('dir-estudiantes-modal-cerrar')?.addEventListener('click', cerrarModal);
    document.getElementById('dir-estudiantes-form')?.addEventListener('submit', enviarFormulario);
}

document.addEventListener('DOMContentLoaded', initDirectorEstudiantes);
