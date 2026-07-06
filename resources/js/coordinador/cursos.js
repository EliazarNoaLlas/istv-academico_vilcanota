/**
 * Interaccion visual + fetch de coordinador/cursos. Toda validacion real
 * la hace el backend (StoreCursoRequest/UpdateCursoRequest); este archivo
 * solo pinta la tabla, filtra y muestra los errores 422 que devuelve la API.
 */
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const BADGE_ESTADO = {
    ACTIVO: 'c-badge-green',
    INACTIVO: 'c-badge-gold',
    ARCHIVADO: 'c-badge-navy',
};

function badgeEstado(curso) {
    const clase = BADGE_ESTADO[curso.estado] ?? 'c-badge-navy';

    return `<span class="c-badge ${clase}">${curso.estado}</span>`;
}

function renderRow(curso) {
    const docente = curso.docente ? `${curso.docente.usuario?.nombres ?? ''} ${curso.docente.usuario?.apellidos ?? ''}` : '—';
    const programa = curso.programa ? curso.programa.nombre : '—';

    return `
        <tr data-id="${curso.id_curso}">
            <td>${curso.nombre_curso}</td>
            <td>${programa}</td>
            <td>${curso.modulo}</td>
            <td>${curso.semestre}</td>
            <td>${docente}</td>
            <td>${curso.total_horas}h (${curso.horas_teoria}T / ${curso.horas_practica}P)</td>
            <td>${badgeEstado(curso)}</td>
            <td><button type="button" class="c-btn c-btn-outline c-btn-sm" data-edit="${curso.id_curso}">Editar</button></td>
        </tr>
    `;
}

function poblarFiltroModulo(cursos) {
    const select = document.getElementById('coord-cursos-filtro-modulo');
    const actuales = new Set(Array.from(select.options).map((o) => o.value));
    const modulos = [...new Set(cursos.map((c) => c.modulo))].sort();

    modulos.forEach((modulo) => {
        if (!actuales.has(modulo)) {
            const opt = document.createElement('option');
            opt.value = modulo;
            opt.textContent = modulo;
            select.appendChild(opt);
        }
    });
}

let cursosCache = [];

function cargarKpis() {
    const root = document.getElementById('coord-cursos-kpis');
    if (!root) return;

    fetch('/api/coordinador/cursos', { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const cursos = data.cursos ?? [];
            root.querySelector('[data-kpi="total"]').textContent = cursos.length;
            root.querySelector('[data-kpi="activos"]').textContent = cursos.filter((c) => c.estado === 'ACTIVO').length;
            root.querySelector('[data-kpi="sin-docente"]').textContent = cursos.filter((c) => !c.id_docente).length;
            root.querySelector('[data-kpi="sin-programa"]').textContent = cursos.filter((c) => !c.id_programa).length;
        })
        .catch((error) => console.error(error));
}

function cargarCursos(params = {}) {
    const tbody = document.getElementById('coord-cursos-tbody');
    const query = new URLSearchParams(params).toString();

    fetch(`/api/coordinador/cursos${query ? `?${query}` : ''}`, {
        headers: { Accept: 'application/json' },
    })
        .then((res) => res.json())
        .then((data) => {
            cursosCache = data.cursos ?? [];

            const modulo = document.getElementById('coord-cursos-filtro-modulo')?.value;
            const filtrados = modulo ? cursosCache.filter((c) => c.modulo === modulo) : cursosCache;

            tbody.innerHTML = filtrados.length
                ? filtrados.map(renderRow).join('')
                : '<tr><td colspan="8" class="coord-cursos-empty">No hay cursos para este filtro.</td></tr>';

            poblarFiltroModulo(cursosCache);
            wireEditButtons();
        })
        .catch((error) => {
            tbody.innerHTML = '<tr><td colspan="8" class="coord-cursos-empty">No se pudo cargar la lista de cursos.</td></tr>';
            console.error(error);
        });
}

function wireEditButtons() {
    document.querySelectorAll('[data-edit]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const curso = cursosCache.find((c) => String(c.id_curso) === btn.dataset.edit);
            if (curso) abrirModal(curso);
        });
    });
}

function abrirModal(curso = null) {
    const modal = document.getElementById('coord-cursos-modal');
    const form = document.getElementById('coord-cursos-form');
    form.reset();
    document.getElementById('coord-cursos-form-error').style.display = 'none';

    document.getElementById('coord-cursos-modal-title').textContent = curso ? 'Editar curso' : 'Nuevo curso';
    form.dataset.idCurso = curso?.id_curso ?? '';

    if (curso) {
        Object.entries(curso).forEach(([campo, valor]) => {
            const input = form.elements.namedItem(campo);
            if (input && valor !== null) input.value = valor;
        });
    }

    modal.classList.add('show');
}

function cerrarModal() {
    document.getElementById('coord-cursos-modal').classList.remove('show');
}

function enviarFormulario(event) {
    event.preventDefault();
    const form = event.target;
    const idCurso = form.dataset.idCurso;
    const datos = Object.fromEntries(new FormData(form).entries());
    const errorBox = document.getElementById('coord-cursos-form-error');

    const url = idCurso ? `/api/coordinador/cursos/${idCurso}` : '/api/coordinador/cursos';
    const method = idCurso ? 'PUT' : 'POST';

    fetch(url, {
        method,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(datos),
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;
            return body;
        })
        .then(() => {
            cerrarModal();
            cargarCursos(filtrosActuales());
            cargarKpis();
        })
        .catch((error) => {
            const mensajes = error?.errors ? Object.values(error.errors).flat().join(' ') : 'No se pudo guardar el curso.';
            errorBox.textContent = mensajes;
            errorBox.style.display = 'block';
        });
}

function filtrosActuales() {
    const params = {};
    const q = document.getElementById('coord-cursos-search')?.value;
    const semestre = document.getElementById('coord-cursos-filtro-semestre')?.value;
    const idPrograma = document.getElementById('coord-cursos-filtro-programa')?.value;
    if (q) params.q = q;
    if (semestre) params.semestre = semestre;
    if (idPrograma) params.id_programa = idPrograma;
    return params;
}

export function initCoordinadorCursos() {
    const tbody = document.getElementById('coord-cursos-tbody');
    if (!tbody) return;

    cargarCursos();
    cargarKpis();

    let debounce;
    document.getElementById('coord-cursos-search')?.addEventListener('input', () => {
        clearTimeout(debounce);
        debounce = setTimeout(() => cargarCursos(filtrosActuales()), 300);
    });

    document.getElementById('coord-cursos-filtro-semestre')?.addEventListener('change', () => cargarCursos(filtrosActuales()));
    document.getElementById('coord-cursos-filtro-modulo')?.addEventListener('change', () => cargarCursos(filtrosActuales()));
    document.getElementById('coord-cursos-filtro-programa')?.addEventListener('change', () => cargarCursos(filtrosActuales()));

    document.getElementById('coord-cursos-modal-cerrar')?.addEventListener('click', cerrarModal);
    document.getElementById('coord-cursos-form')?.addEventListener('submit', enviarFormulario);
}

document.addEventListener('DOMContentLoaded', initCoordinadorCursos);
