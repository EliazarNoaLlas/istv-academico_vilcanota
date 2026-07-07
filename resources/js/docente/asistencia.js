/**
 * Registro de asistencia con datos reales desde /api/docente/asistencia.
 * Una sola sesion por curso+fecha (la crea el backend, no se duplica).
 * Un docente solo puede ver/editar asistencia de sus propios cursos.
 */
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
const PALETA_AVATAR = ['teal', 'navy', 'gold', 'red', 'purple'];

const estado = {
    cursos: [],
    periodoActivo: null,
    idCurso: null,
    fecha: null,
    filtroChip: null,
};

function formatearFechaLarga(fechaIso) {
    const [anio, mes, dia] = fechaIso.split('-').map(Number);
    const fecha = new Date(anio, mes - 1, dia);
    const texto = fecha.toLocaleDateString('es-PE', { weekday: 'long', day: '2-digit', month: 'short' });

    return texto.charAt(0).toUpperCase() + texto.slice(1);
}

function iniciales(nombres, apellidos) {
    return `${(nombres[0] ?? '').toUpperCase()}${(apellidos[0] ?? '').toUpperCase()}`;
}

function nombreYApellidos(nombreCompleto) {
    const [apellidos, nombres] = nombreCompleto.split(',').map((s) => s.trim());

    return { nombres: nombres ?? '', apellidos: apellidos ?? '' };
}

function filaHtml(est, indice) {
    const { nombres, apellidos } = nombreYApellidos(est.nombre_completo);
    const color = PALETA_AVATAR[indice % PALETA_AVATAR.length];
    const estadoInicial = est.estado ?? 'PRESENTE';

    return `
        <div class="doc-asistencia-fila" data-id-estudiante="${est.id_estudiante}" data-nombre="${`${nombres} ${apellidos}`.toLowerCase()}" data-estado="${estadoInicial}">
            <div class="doc-asistencia-avatar ${color}">${iniciales(nombres, apellidos)}</div>
            <div class="doc-asistencia-fila-info">
                <strong>${nombres} ${apellidos}</strong>
                <small>${est.codigo_estudiante}</small>
            </div>
            <div class="doc-asistencia-fila-botones">
                <button type="button" data-estado-btn="PRESENTE" class="${estadoInicial === 'PRESENTE' ? 'active' : ''}"><i class="bi bi-check-lg"></i> Presente</button>
                <button type="button" data-estado-btn="TARDANZA" class="${estadoInicial === 'TARDANZA' ? 'active' : ''}"><i class="bi bi-clock"></i> Tardanza</button>
                <button type="button" data-estado-btn="AUSENTE" class="${estadoInicial === 'AUSENTE' ? 'active' : ''}"><i class="bi bi-x-lg"></i> Ausente</button>
                <button type="button" data-estado-btn="JUSTIFICADO" class="${estadoInicial === 'JUSTIFICADO' ? 'active' : ''}"><i class="bi bi-file-earmark-text"></i> Justif.</button>
            </div>
        </div>
    `;
}

function recalcularResumenLocal() {
    const filas = Array.from(document.querySelectorAll('.doc-asistencia-fila'));
    const conteo = { PRESENTE: 0, TARDANZA: 0, AUSENTE: 0, JUSTIFICADO: 0 };

    filas.forEach((fila) => { conteo[fila.dataset.estado] = (conteo[fila.dataset.estado] ?? 0) + 1; });

    const total = filas.length;
    const pct = total > 0 ? Math.round((conteo.PRESENTE / total) * 1000) / 10 : null;

    document.getElementById('doc-asistencia-stat-pct').textContent = pct !== null ? `${pct}%` : '—';
    document.getElementById('doc-asistencia-stat-ausentes').textContent = conteo.AUSENTE;
    document.getElementById('doc-asistencia-chip-presentes').textContent = conteo.PRESENTE;
    document.getElementById('doc-asistencia-chip-tardanzas').textContent = conteo.TARDANZA;
    document.getElementById('doc-asistencia-chip-ausentes').textContent = conteo.AUSENTE;
}

function aplicarFiltros() {
    const busqueda = document.getElementById('doc-asistencia-buscar').value.trim().toLowerCase();

    document.querySelectorAll('.doc-asistencia-fila').forEach((fila) => {
        const coincideChip = !estado.filtroChip || fila.dataset.estado === estado.filtroChip;
        const coincideBusqueda = !busqueda || fila.dataset.nombre.includes(busqueda);
        fila.style.display = coincideChip && coincideBusqueda ? '' : 'none';
    });
}

function wireFilaBotones() {
    document.querySelectorAll('.doc-asistencia-fila-botones button').forEach((btn) => {
        btn.addEventListener('click', () => {
            const fila = btn.closest('.doc-asistencia-fila');
            fila.dataset.estado = btn.dataset.estadoBtn;
            fila.querySelectorAll('button').forEach((b) => b.classList.toggle('active', b === btn));
            recalcularResumenLocal();
        });
    });
}

function wireChips() {
    document.querySelectorAll('.doc-asistencia-chip').forEach((chip) => {
        chip.addEventListener('click', () => {
            const filtro = chip.dataset.filtro;
            estado.filtroChip = estado.filtroChip === filtro ? null : filtro;
            document.querySelectorAll('.doc-asistencia-chip').forEach((c) => c.classList.toggle('active', c.dataset.filtro === estado.filtroChip));
            aplicarFiltros();
        });
    });
}

function renderAlerta(alertas) {
    const panel = document.getElementById('doc-asistencia-alerta');
    const lista = document.getElementById('doc-asistencia-alerta-lista');

    if (!alertas.length) {
        panel.hidden = true;

        return;
    }

    panel.hidden = false;
    lista.innerHTML = alertas.map((a) => {
        const { nombres, apellidos } = nombreYApellidos(a.nombre_completo);

        return `
            <div class="c-alert-item">
                <div class="c-alert-icon red"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="c-alert-body"><strong>${nombres} ${apellidos}</strong><span>Asistencia histórica: ${a.asistencia_historica_pct}%</span></div>
            </div>
        `;
    }).join('');
}

function actualizarCardCurso() {
    const curso = estado.cursos.find((c) => String(c.id_curso) === String(estado.idCurso));

    document.getElementById('doc-asistencia-total-matriculados').textContent = curso?.estudiantes_count ?? 0;
    document.getElementById('doc-asistencia-semestre-valor').textContent = estado.periodoActivo?.codigo ?? '—';
}

function actualizarFechaTexto() {
    document.getElementById('doc-asistencia-fecha-texto').textContent = formatearFechaLarga(estado.fecha);
    document.getElementById('doc-asistencia-fecha').value = estado.fecha;
}

function cargarAsistencia() {
    const contenido = document.getElementById('doc-asistencia-contenido');
    const sinCurso = document.getElementById('doc-asistencia-sin-curso');
    const vacio = document.getElementById('doc-asistencia-empty');

    if (!estado.idCurso || !estado.fecha) {
        contenido.hidden = true;
        vacio.hidden = true;
        sinCurso.hidden = false;

        return;
    }

    sinCurso.hidden = true;
    actualizarCardCurso();
    actualizarFechaTexto();

    fetch(`/api/docente/asistencia?id_curso=${estado.idCurso}&fecha=${estado.fecha}`, { headers: { Accept: 'application/json' } })
        .then((res) => {
            if (!res.ok) throw new Error('No se pudo cargar la asistencia.');

            return res.json();
        })
        .then((data) => {
            if (!data.estudiantes.length) {
                contenido.hidden = true;
                vacio.hidden = false;

                return;
            }

            vacio.hidden = true;
            contenido.hidden = false;
            estado.filtroChip = null;
            document.querySelectorAll('.doc-asistencia-chip').forEach((c) => c.classList.remove('active'));

            renderAlerta(data.alertas_bajo_70);
            document.getElementById('doc-asistencia-lista').innerHTML = data.estudiantes.map(filaHtml).join('');
            wireFilaBotones();
            recalcularResumenLocal();
            aplicarFiltros();
        })
        .catch((error) => console.error(error));
}

function marcarTodosPresentes() {
    document.querySelectorAll('.doc-asistencia-fila').forEach((fila) => {
        fila.dataset.estado = 'PRESENTE';
        fila.querySelectorAll('button').forEach((b) => b.classList.toggle('active', b.dataset.estadoBtn === 'PRESENTE'));
    });
    recalcularResumenLocal();
}

function recolectarRegistros() {
    return Array.from(document.querySelectorAll('.doc-asistencia-fila')).map((fila) => ({
        id_estudiante: Number(fila.dataset.idEstudiante),
        estado: fila.dataset.estado,
    }));
}

function guardarAsistencia() {
    fetch('/api/docente/asistencia/guardar', {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ id_curso: estado.idCurso, fecha: estado.fecha, registros: recolectarRegistros() }),
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then(() => cargarAsistencia())
        .catch((error) => alert(error.mensaje ?? error.message ?? 'No se pudo guardar la asistencia.'));
}

function cambiarFecha(delta) {
    const [anio, mes, dia] = estado.fecha.split('-').map(Number);
    const fecha = new Date(anio, mes - 1, dia + delta);
    estado.fecha = `${fecha.getFullYear()}-${String(fecha.getMonth() + 1).padStart(2, '0')}-${String(fecha.getDate()).padStart(2, '0')}`;
    cargarAsistencia();
}

function poblarSelectsCurso() {
    const opciones = '<option value="">Selecciona un curso…</option>'
        + estado.cursos.map((c) => `<option value="${c.id_curso}">${c.nombre_curso}</option>`).join('');

    document.getElementById('doc-asistencia-curso').innerHTML = opciones;
    document.getElementById('doc-asistencia-curso-vacio').innerHTML = opciones;
}

function seleccionarCurso(idCurso) {
    estado.idCurso = idCurso || null;
    document.getElementById('doc-asistencia-curso').value = idCurso ?? '';
    document.getElementById('doc-asistencia-curso-vacio').value = idCurso ?? '';
    cargarAsistencia();
}

export function initDocenteAsistencia() {
    const raiz = document.getElementById('doc-asistencia-sin-curso');
    if (!raiz) return;

    estado.fecha = new Date().toISOString().slice(0, 10);

    fetch('/api/docente/cursos', { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            estado.cursos = data.cursos ?? [];
            estado.periodoActivo = data.periodo_activo ?? null;
            poblarSelectsCurso();

            const params = new URLSearchParams(window.location.search);
            const idCursoInicial = params.get('curso') ?? (estado.cursos[0]?.id_curso ?? null);
            if (idCursoInicial) seleccionarCurso(idCursoInicial);
        });

    document.getElementById('doc-asistencia-curso').addEventListener('change', (e) => seleccionarCurso(e.target.value));
    document.getElementById('doc-asistencia-curso-vacio').addEventListener('change', (e) => seleccionarCurso(e.target.value));

    document.getElementById('doc-asistencia-fecha-anterior').addEventListener('click', () => cambiarFecha(-1));
    document.getElementById('doc-asistencia-fecha-siguiente').addEventListener('click', () => cambiarFecha(1));
    document.getElementById('doc-asistencia-fecha-hoy').addEventListener('click', () => {
        estado.fecha = new Date().toISOString().slice(0, 10);
        cargarAsistencia();
    });

    document.getElementById('doc-asistencia-buscar').addEventListener('input', aplicarFiltros);
    document.getElementById('doc-asistencia-marcar-todos').addEventListener('click', marcarTodosPresentes);
    document.getElementById('doc-asistencia-guardar').addEventListener('click', guardarAsistencia);
    wireChips();
}

document.addEventListener('DOMContentLoaded', initDocenteAsistencia);
