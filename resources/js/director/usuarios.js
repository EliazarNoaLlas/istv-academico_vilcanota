const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const BADGE_ROL = { director: 'c-badge-navy', jua: 'c-badge-gold', coordinador: 'c-badge-teal', docente: 'c-badge-teal' };
const BADGE_ESTADO = { ACTIVO: 'c-badge-green', INACTIVO: 'c-badge-gold', BLOQUEADO: 'c-badge-red' };

let usuariosCache = [];

function renderRow(usuario) {
    const iniciales = `${usuario.nombres?.[0] ?? ''}${usuario.apellidos?.[0] ?? ''}`.toUpperCase();
    const ultimoAcceso = usuario.ultimo_acceso
        ? new Date(usuario.ultimo_acceso).toLocaleString('es-PE', { dateStyle: 'short', timeStyle: 'short' })
        : 'Pendiente';
    const esDirector = usuario.rol?.codigo === 'director';

    const gestionadoFuera = '<span style="font-size:11px;color:var(--text-muted)">Gestionado fuera del sistema</span>';

    const colEstado = esDirector ? gestionadoFuera : `
        <select data-estado-select="${usuario.id_usuario}" class="input-inline" style="width:auto">
            <option value="ACTIVO" ${usuario.estado === 'ACTIVO' ? 'selected' : ''}>Activo</option>
            <option value="INACTIVO" ${usuario.estado === 'INACTIVO' ? 'selected' : ''}>Inactivo</option>
            <option value="BLOQUEADO" ${usuario.estado === 'BLOQUEADO' ? 'selected' : ''}>Bloqueado</option>
        </select>
    `;

    const colEditar = esDirector ? '' : `
        <button type="button" class="c-btn-icon" data-edit="${usuario.id_usuario}" title="Editar">
            <i class="bi bi-pencil-square"></i>
        </button>
    `;

    const colReset = esDirector ? '' : `
        <button type="button" class="c-btn-icon" data-reset="${usuario.id_usuario}" title="Reset clave">
            <i class="bi bi-key"></i>
        </button>
    `;

    const colEliminar = esDirector ? '' : `
        <button type="button" class="c-btn-icon c-btn-icon-danger" data-delete="${usuario.id_usuario}" title="Eliminar">
            <i class="bi bi-trash"></i>
        </button>
    `;

    return `
        <tr data-id="${usuario.id_usuario}">
            <td>
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="c-avatar-sm">${iniciales}</div>
                    <div>
                        <div>${usuario.nombres} ${usuario.apellidos ?? ''}</div>
                        <div style="font-size:11px;color:var(--text-muted)">${usuario.usuario}</div>
                    </div>
                </div>
            </td>
            <td>${usuario.correo}</td>
            <td><span class="c-badge ${BADGE_ROL[usuario.rol?.codigo] ?? 'c-badge-navy'}">${usuario.rol?.nombre ?? '—'}</span></td>
            <td><span class="c-badge ${BADGE_ESTADO[usuario.estado] ?? 'c-badge-navy'}">${usuario.estado}</span></td>
            <td>${ultimoAcceso}</td>
            <td>${colEstado}</td>
            <td>${colEditar}</td>
            <td>${colReset}</td>
            <td>${colEliminar}</td>
        </tr>
    `;
}

function filtrosActuales() {
    const params = {};
    const q = document.getElementById('dir-usuarios-search')?.value;
    const idRol = document.getElementById('dir-usuarios-filtro-rol')?.value;
    const estado = document.getElementById('dir-usuarios-filtro-estado')?.value;
    if (q) params.q = q;
    if (idRol) params.id_rol = idRol;
    if (estado) params.estado = estado;

    return params;
}

function cargarUsuarios() {
    const tbody = document.getElementById('dir-usuarios-tbody');
    const query = new URLSearchParams(filtrosActuales()).toString();

    fetch(`/api/director/usuarios${query ? `?${query}` : ''}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            usuariosCache = data.usuarios ?? [];

            tbody.innerHTML = usuariosCache.length
                ? usuariosCache.map(renderRow).join('')
                : '<tr><td colspan="9" class="c-table-empty">No hay usuarios para este filtro.</td></tr>';

            wireRowActions();
        })
        .catch((error) => {
            tbody.innerHTML = '<tr><td colspan="9" class="c-table-empty">No se pudo cargar la lista de usuarios.</td></tr>';
            console.error(error);
        });
}

function wireRowActions() {
    document.querySelectorAll('[data-edit]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const usuario = usuariosCache.find((u) => String(u.id_usuario) === btn.dataset.edit);
            if (usuario) abrirModal(usuario);
        });
    });

    document.querySelectorAll('[data-reset]').forEach((btn) => {
        btn.addEventListener('click', () => resetPassword(btn.dataset.reset));
    });

    document.querySelectorAll('[data-delete]').forEach((btn) => {
        btn.addEventListener('click', () => eliminarUsuario(btn.dataset.delete));
    });

    document.querySelectorAll('[data-estado-select]').forEach((select) => {
        select.addEventListener('change', () => {
            const idUsuario = select.dataset.estadoSelect;
            const nuevoEstado = select.value;
            const usuario = usuariosCache.find((u) => String(u.id_usuario) === idUsuario);

            pedirMotivo('Cambiar estado', (motivo) => cambiarEstado(idUsuario, nuevoEstado, motivo), () => {
                // si cancela, regresa el select a su valor anterior
                select.value = usuario.estado;
            });
        });
    });
}

function resetPassword(idUsuario) {
    if (!confirm('¿Generar y enviar una nueva contraseña temporal al correo institucional de este usuario?')) return;

    fetch(`/api/director/usuarios/${idUsuario}/reset-password`, {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
        .then((res) => res.json())
        .then((data) => alert(data.mensaje ?? 'Contraseña restablecida.'))
        .catch((error) => console.error(error));
}

function eliminarUsuario(idUsuario) {
    if (!confirm('¿Eliminar esta cuenta de usuario? Esta acción no se puede deshacer desde la interfaz.')) return;

    fetch(`/api/director/usuarios/${idUsuario}`, {
        method: 'DELETE',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then(() => {
            cargarUsuarios();
            alert('Usuario eliminado.');
        })
        .catch((error) => alert(error?.mensaje ?? 'No se pudo eliminar el usuario.'));
}

function cambiarEstado(idUsuario, estado, motivo) {
    fetch(`/api/director/usuarios/${idUsuario}/estado`, {
        method: 'PATCH',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ estado, motivo }),
    })
        .then((res) => res.json())
        .catch((error) => console.error(error))
        .finally(() => cargarUsuarios());
}

let usuarioEnEdicion = null;

/** Con tipo ESPECIFICO los checkboxes de programa se comportan como radio (solo 1); con GENERAL, seleccion libre. */
function esModoGeneral() {
    return document.querySelector('input[name="tipo_docente"]:checked')?.value === 'GENERAL';
}

function actualizarHintProgramas() {
    document.getElementById('dir-usuarios-programas-hint').textContent = esModoGeneral()
        ? 'Puede seleccionar varios programas.'
        : 'Seleccione un único programa (docente específico).';
}

/** Al pasar a ESPECIFICO con varios marcados, deja solo el primero para no dejar un estado invalido. */
function aplicarModoProgramas() {
    actualizarHintProgramas();

    if (esModoGeneral()) return;

    const marcados = document.querySelectorAll('#dir-usuarios-programas-lista input[type="checkbox"]:checked');
    marcados.forEach((chk, indice) => { if (indice > 0) chk.checked = false; });
}

/**
 * El perfil academico del docente (especialidad, tipo, programas[]) solo se
 * completa al crear: editar no lo modifica todavia. El programa asignado al
 * coordinador si es editable, porque el Director necesita poder reasignarlo.
 */
function actualizarVisibilidadDocente() {
    const select = document.getElementById('dir-usuarios-select-rol');
    const codigo = select?.selectedOptions[0]?.dataset.codigo;

    document.getElementById('dir-usuarios-campos-docente').style.display = (!usuarioEnEdicion && codigo === 'docente') ? 'block' : 'none';
    document.getElementById('dir-usuarios-campo-coordinador').style.display = codigo === 'coordinador' ? 'block' : 'none';
}

function abrirModal(usuario = null) {
    usuarioEnEdicion = usuario;
    const form = document.getElementById('dir-usuarios-form');
    form.reset();
    limpiarErrores();
    document.getElementById('dir-usuarios-modal-title').textContent = usuario ? 'Editar usuario' : 'Nuevo usuario';
    document.getElementById('dir-usuarios-campo-estado').style.display = usuario ? 'none' : 'block';

    if (usuario) {
        Object.entries(usuario).forEach(([campo, valor]) => {
            const input = form.elements.namedItem(campo);
            if (input && valor !== null && typeof valor !== 'object') input.value = valor;
        });
    }

    actualizarVisibilidadDocente();
    aplicarModoProgramas();
    document.getElementById('dir-usuarios-modal').classList.add('show');
}

function cerrarModal() {
    document.getElementById('dir-usuarios-modal').classList.remove('show');
    usuarioEnEdicion = null;
}

function construirPayload(form) {
    const formData = new FormData(form);
    const datos = {};

    const coordinadorVisible = document.getElementById('dir-usuarios-campo-coordinador').style.display !== 'none';

    formData.forEach((valor, clave) => {
        if (clave === 'programas[]') return;
        if (clave === 'id_programa' && !coordinadorVisible) return;
        datos[clave] = valor;
    });

    const programas = formData.getAll('programas[]').map(Number);
    if (document.getElementById('dir-usuarios-campos-docente').style.display !== 'none') {
        datos.programas = programas;
    }

    return datos;
}

function limpiarErrores() {
    document.getElementById('dir-usuarios-form-error').textContent = '';
    document.querySelectorAll('#dir-usuarios-form .dir-usuarios-field-error').forEach((el) => { el.textContent = ''; });
    document.querySelectorAll('#dir-usuarios-form .is-invalid').forEach((el) => el.classList.remove('is-invalid'));
}

function mostrarErrores(errores) {
    limpiarErrores();

    if (!errores) {
        document.getElementById('dir-usuarios-form-error').textContent = 'No se pudo guardar el usuario.';
        return;
    }

    Object.entries(errores).forEach(([campo, mensajes]) => {
        const campoBase = campo.replace('.*', '').split('.')[0];
        const contenedor = document.querySelector(`[data-error-for="${campoBase}"]`);
        const mensaje = Array.isArray(mensajes) ? mensajes[0] : mensajes;

        if (contenedor) {
            contenedor.textContent = mensaje;
            const input = document.querySelector(`#dir-usuarios-form [name="${campoBase}"], #dir-usuarios-form [name="${campoBase}[]"]`);
            input?.classList.add('is-invalid');
        } else {
            document.getElementById('dir-usuarios-form-error').textContent = mensaje;
        }
    });
}

function enviarFormulario(event) {
    event.preventDefault();
    const form = event.target;
    const datos = construirPayload(form);

    const url = usuarioEnEdicion ? `/api/director/usuarios/${usuarioEnEdicion.id_usuario}` : '/api/director/usuarios';
    const method = usuarioEnEdicion ? 'PUT' : 'POST';

    fetch(url, {
        method,
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
            cargarUsuarios();
            alert(usuarioEnEdicion ? 'Usuario actualizado.' : 'Usuario registrado exitosamente. Se envió la contraseña temporal a su correo institucional.');
        })
        .catch((error) => mostrarErrores(error?.errors));
}

let motivoCallback = null;
let motivoCancelCallback = null;

function pedirMotivo(titulo, onConfirmar, onCancelar = null) {
    motivoCallback = onConfirmar;
    motivoCancelCallback = onCancelar;
    document.getElementById('dir-usuarios-motivo-titulo').textContent = titulo;
    document.getElementById('dir-usuarios-motivo-form').reset();
    document.getElementById('dir-usuarios-motivo-error').textContent = '';
    document.getElementById('dir-usuarios-motivo-modal').classList.add('show');
}

function cerrarModalMotivo(cancelado) {
    document.getElementById('dir-usuarios-motivo-modal').classList.remove('show');
    if (cancelado && motivoCancelCallback) motivoCancelCallback();
    motivoCallback = null;
    motivoCancelCallback = null;
}

function renderSolicitudes(solicitudes) {
    const panel = document.getElementById('dir-usuarios-solicitudes-panel');
    const root = document.getElementById('dir-usuarios-solicitudes-lista');

    panel.style.display = solicitudes.length ? 'block' : 'none';
    if (!solicitudes.length) return;

    root.innerHTML = solicitudes.map((s) => `
        <div class="c-alert-item">
            <div class="c-alert-icon gold"><i class="bi bi-envelope-exclamation"></i></div>
            <div class="c-alert-body" style="flex:1">
                <strong>${s.usuario?.nombres ?? '—'} ${s.usuario?.apellidos ?? ''} (${s.usuario?.usuario ?? ''})</strong>
                <span>${s.usuario?.rol?.nombre ?? ''} · ${s.motivo ?? 'Sin motivo indicado'}</span>
            </div>
            <button type="button" class="c-btn c-btn-primary c-btn-sm" data-aprobar="${s.id_solicitud}">Aprobar</button>
            <button type="button" class="c-btn c-btn-outline c-btn-sm" data-rechazar="${s.id_solicitud}">Rechazar</button>
        </div>
    `).join('');

    document.querySelectorAll('[data-aprobar]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (!confirm('¿Aprobar esta solicitud y enviar una contraseña temporal al correo del usuario?')) return;

            fetch(`/api/director/usuarios-solicitudes-password/${btn.dataset.aprobar}/aprobar`, {
                method: 'PATCH',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
            }).then(() => { cargarSolicitudes(); cargarUsuarios(); });
        });
    });

    document.querySelectorAll('[data-rechazar]').forEach((btn) => {
        btn.addEventListener('click', () => {
            pedirMotivo('Rechazar solicitud', (motivo) => {
                fetch(`/api/director/usuarios-solicitudes-password/${btn.dataset.rechazar}/rechazar`, {
                    method: 'PATCH',
                    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ motivo_rechazo: motivo }),
                }).then(() => cargarSolicitudes());
            });
        });
    });
}

function cargarSolicitudes() {
    fetch('/api/director/usuarios-solicitudes-password', { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => renderSolicitudes(data.solicitudes ?? []))
        .catch((error) => console.error(error));
}

export function initDirectorUsuarios() {
    const tbody = document.getElementById('dir-usuarios-tbody');
    if (!tbody) return;

    cargarUsuarios();
    cargarSolicitudes();

    let debounce;
    document.getElementById('dir-usuarios-search')?.addEventListener('input', () => {
        clearTimeout(debounce);
        debounce = setTimeout(cargarUsuarios, 300);
    });
    document.getElementById('dir-usuarios-filtro-rol')?.addEventListener('change', cargarUsuarios);
    document.getElementById('dir-usuarios-filtro-estado')?.addEventListener('change', cargarUsuarios);

    document.getElementById('dir-usuarios-nuevo')?.addEventListener('click', () => abrirModal());
    document.getElementById('dir-usuarios-modal-cerrar')?.addEventListener('click', cerrarModal);
    document.getElementById('dir-usuarios-form')?.addEventListener('submit', enviarFormulario);
    document.getElementById('dir-usuarios-select-rol')?.addEventListener('change', actualizarVisibilidadDocente);
    document.querySelectorAll('input[name="tipo_docente"]').forEach((radio) => {
        radio.addEventListener('change', aplicarModoProgramas);
    });

    // Delegado y registrado una sola vez: si es ESPECIFICO, marcar un programa desmarca los demas.
    document.getElementById('dir-usuarios-programas-lista')?.addEventListener('change', (event) => {
        if (event.target.type !== 'checkbox' || esModoGeneral() || !event.target.checked) return;

        document.querySelectorAll('#dir-usuarios-programas-lista input[type="checkbox"]').forEach((otro) => {
            if (otro !== event.target) otro.checked = false;
        });
    });

    document.getElementById('dir-usuarios-motivo-cancelar')?.addEventListener('click', () => cerrarModalMotivo(true));
    document.getElementById('dir-usuarios-motivo-form')?.addEventListener('submit', (event) => {
        event.preventDefault();
        const motivo = new FormData(event.target).get('motivo');
        const callback = motivoCallback;
        document.getElementById('dir-usuarios-motivo-modal').classList.remove('show');
        motivoCallback = null;
        motivoCancelCallback = null;
        if (callback) callback(motivo);
    });
}

document.addEventListener('DOMContentLoaded', initDirectorUsuarios);
