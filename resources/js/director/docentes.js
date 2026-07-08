/** Docentes con carga semanal real (bloques del horario generado) desde /api/director/docentes. */
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const DIAS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
const LIMITE_HORAS = 40;

const ESTADO_CARGA = {
    SIN_CARGA: { clase: 'sin', label: 'Sin carga' },
    NORMAL: { clase: 'normal', label: 'Carga normal' },
    MODERADA: { clase: 'moderada', label: 'Carga moderada' },
    ALTA: { clase: 'alta', label: 'Carga alta' },
    SOBRECARGA: { clase: 'sobrecarga', label: 'Sobrecarga' },
};

const TIPO_DOCENTE_LABEL = { ESPECIFICO: 'Específico', GENERAL: 'General' };

let docentesCache = [];
let docentesFiltrados = [];
let paginaActual = 1;
let tamanoPagina = 10;

let docenteEnModal = null;
let docenteEnVista = null;
let cursosModalCache = { asignados: [], disponibles: [] };
let seleccionados = new Map();

function iniciales(nombre) {
    return (nombre ?? '').trim().split(/\s+/).filter(Boolean).slice(0, 2).map((p) => p[0]).join('').toUpperCase() || '—';
}

function nombreCompleto(docente) {
    return `${docente?.usuario?.nombres ?? ''} ${docente?.usuario?.apellidos ?? ''}`.trim();
}

function claseDia(horas) {
    if (horas === 0) return 'sin';
    if (horas <= 4) return 'normal';
    if (horas <= 6) return 'moderada';
    if (horas <= 8) return 'alta';
    return 'sobrecarga';
}

/* ---------- Tabla principal ---------- */

function renderRow(docente) {
    const nombre = nombreCompleto(docente);
    const estado = ESTADO_CARGA[docente.estado_carga] ?? ESTADO_CARGA.SIN_CARGA;

    const dias = (docente.carga_por_dia ?? []).map((horas, i) => `
        <span class="dir-docentes-day-cell ${claseDia(horas)}" title="${DIAS[i]}: ${horas}h">${horas}</span>
    `).join('');

    return `
        <tr data-id="${docente.id_docente}">
            <td>${nombre || '—'}</td>
            <td>${docente.especialidad ?? '—'}</td>
            <td>${TIPO_DOCENTE_LABEL[docente.tipo_docente] ?? docente.tipo_docente ?? '—'}</td>
            <td>${docente.cursos_count ?? 0}</td>
            <td colspan="7">
                <div class="dir-docentes-days">
                    ${dias}
                    <span class="dir-docentes-pill ${estado.clase}">${docente.carga_semanal ?? 0}h</span>
                </div>
            </td>
            <td><span class="c-badge dir-docentes-badge ${estado.clase}">${estado.label}</span></td>
            <td>
                <div class="dir-docentes-actions">
                    <button type="button" class="c-btn-icon" data-ver="${docente.id_docente}" title="Ver docente">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button type="button" class="c-btn-icon" data-asignar="${docente.id_docente}" title="Asignar cursos">
                        <i class="bi bi-journal-plus"></i>
                    </button>
                </div>
            </td>
        </tr>
    `;
}

function especialidadesUnicas() {
    return [...new Set(docentesCache.map((d) => d.especialidad).filter(Boolean))].sort();
}

function poblarFiltroEspecialidad() {
    const select = document.getElementById('dir-docentes-filtro-especialidad');
    if (!select) return;

    const actual = select.value;
    select.innerHTML = '<option value="">Todas las especialidades</option>'
        + especialidadesUnicas().map((e) => `<option value="${e}">${e}</option>`).join('');
    select.value = actual;
}

function poblarSelectDocentes() {
    const select = document.getElementById('dir-docentes-modal-select-docente');
    if (!select) return;

    select.innerHTML = '<option value="">Elija un docente…</option>'
        + docentesCache.map((d) => `<option value="${d.id_docente}">${nombreCompleto(d) || `Docente #${d.id_docente}`}</option>`).join('');
    select.value = '';
}

function filtrosActuales() {
    return {
        idPrograma: document.getElementById('dir-docentes-filtro-programa')?.value ?? '',
        especialidad: document.getElementById('dir-docentes-filtro-especialidad')?.value ?? '',
        estado: document.getElementById('dir-docentes-filtro-estado')?.value ?? '',
        q: (document.getElementById('dir-docentes-search')?.value ?? '').trim().toLowerCase(),
    };
}

function aplicarFiltros() {
    const { idPrograma, especialidad, estado, q } = filtrosActuales();

    docentesFiltrados = docentesCache.filter((d) => {
        if (idPrograma && !(d.programas ?? []).some((p) => String(p.id_programa) === idPrograma)) return false;
        if (especialidad && d.especialidad !== especialidad) return false;
        if (estado && d.estado_carga !== estado) return false;
        if (q && !nombreCompleto(d).toLowerCase().includes(q)) return false;

        return true;
    });

    paginaActual = 1;
    renderTabla();
}

function renderPaginacion(totalPaginas) {
    const cont = document.getElementById('dir-docentes-pagination');
    if (!cont) return;

    if (totalPaginas <= 1) {
        cont.innerHTML = '';

        return;
    }

    const paginas = [];
    for (let p = 1; p <= totalPaginas; p++) {
        if (p === 1 || p === totalPaginas || Math.abs(p - paginaActual) <= 1) paginas.push(p);
        else if (paginas[paginas.length - 1] !== '…') paginas.push('…');
    }

    const boton = (label, pagina, disabled = false, activo = false) => `
        <div class="dir-docentes-page-btn ${activo ? 'active' : ''} ${disabled ? 'disabled' : ''}" ${pagina ? `data-page="${pagina}"` : ''}>${label}</div>
    `;

    cont.innerHTML = boton('«', 1, paginaActual === 1)
        + boton('‹', Math.max(1, paginaActual - 1), paginaActual === 1)
        + paginas.map((p) => (p === '…' ? '<div class="dir-docentes-page-btn disabled">…</div>' : boton(p, p, false, p === paginaActual))).join('')
        + boton('›', Math.min(totalPaginas, paginaActual + 1), paginaActual === totalPaginas)
        + boton('»', totalPaginas, paginaActual === totalPaginas);

    cont.querySelectorAll('[data-page]').forEach((btn) => {
        btn.addEventListener('click', () => {
            paginaActual = Number(btn.dataset.page);
            renderTabla();
        });
    });
}

function renderTabla() {
    const tbody = document.getElementById('dir-docentes-tbody');
    if (!tbody) return;

    const total = docentesFiltrados.length;
    const totalPaginas = Math.max(1, Math.ceil(total / tamanoPagina));
    paginaActual = Math.min(paginaActual, totalPaginas);

    const inicio = (paginaActual - 1) * tamanoPagina;
    const pagina = docentesFiltrados.slice(inicio, inicio + tamanoPagina);

    tbody.innerHTML = pagina.length
        ? pagina.map(renderRow).join('')
        : '<tr><td colspan="13" class="c-table-empty">No hay docentes para este filtro.</td></tr>';

    document.getElementById('dir-docentes-resumen').textContent = total
        ? `Mostrando ${inicio + 1} a ${Math.min(inicio + tamanoPagina, total)} de ${total} docentes`
        : 'Mostrando 0 de 0 docentes';

    renderPaginacion(totalPaginas);
    wireRowActions();
}

function renderKpis() {
    const root = document.getElementById('dir-docentes-kpis');
    if (!root) return;

    const totalCursos = docentesCache.reduce((acc, d) => acc + (d.cursos_count ?? 0), 0);
    const cargaPromedio = docentesCache.length
        ? Math.round(docentesCache.reduce((acc, d) => acc + (d.carga_semanal ?? 0), 0) / docentesCache.length)
        : 0;

    root.querySelector('[data-kpi="total"]').textContent = docentesCache.length;
    root.querySelector('[data-kpi="carga-promedio"]').textContent = `${cargaPromedio}h`;
    root.querySelector('[data-kpi="cursos-asignados"]').textContent = totalCursos;
    root.querySelector('[data-kpi="sobrecarga"]').textContent = docentesCache.filter((d) => d.estado_carga === 'SOBRECARGA').length;

    const sinCarga = document.querySelector('[data-kpi="sin-carga"]');
    if (sinCarga) sinCarga.textContent = docentesCache.filter((d) => d.estado_carga === 'SIN_CARGA').length;
}

function renderEspecialidades() {
    const root = document.getElementById('dir-docentes-especialidades');
    if (!root) return;

    const total = docentesCache.length;
    const conteo = new Map();
    docentesCache.forEach((d) => {
        const clave = d.especialidad || 'Sin especialidad';
        conteo.set(clave, (conteo.get(clave) ?? 0) + 1);
    });

    const filas = [...conteo.entries()].sort((a, b) => b[1] - a[1]).map(([especialidad, cantidad]) => `
        <div class="dir-docentes-spec-row">
            <div class="dir-docentes-spec-row-head"><span>${especialidad}</span><b>${cantidad}</b></div>
            <div class="dir-docentes-bar-track"><div class="dir-docentes-bar-fill" style="width:${total ? Math.round((cantidad / total) * 100) : 0}%;"></div></div>
        </div>
    `).join('');

    root.innerHTML = `${filas}<div class="dir-docentes-spec-total"><span>Total docentes</span><span>${total}</span></div>`;
}

function cargarDocentes() {
    const tbody = document.getElementById('dir-docentes-tbody');

    return fetch('/api/director/docentes', { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            docentesCache = data.docentes ?? [];
            poblarFiltroEspecialidad();
            poblarSelectDocentes();
            renderKpis();
            renderEspecialidades();
            aplicarFiltros();

            document.getElementById('dir-docentes-fecha').textContent = new Date().toLocaleString('es-PE', { dateStyle: 'short', timeStyle: 'short' });
        })
        .catch((error) => {
            tbody.innerHTML = '<tr><td colspan="13" class="c-table-empty">No se pudo cargar la lista de docentes.</td></tr>';
            console.error(error);
        });
}

function wireRowActions() {
    document.querySelectorAll('[data-ver]').forEach((btn) => {
        btn.addEventListener('click', () => abrirModalVer(btn.dataset.ver));
    });
    document.querySelectorAll('[data-asignar]').forEach((btn) => {
        btn.addEventListener('click', () => abrirModalAsignar(btn.dataset.asignar));
    });
}

/* ---------- Modal "Ver docente" ---------- */

function abrirModalVer(idDocente) {
    document.getElementById('dir-docentes-view-modal').classList.add('show');
    document.getElementById('dv-nombre').textContent = 'Cargando…';
    document.getElementById('dv-cursos-tbody').innerHTML = '<tr><td colspan="6" class="c-table-empty">Cargando…</td></tr>';
    document.getElementById('dv-dist-grid').innerHTML = '';
    document.getElementById('dv-observaciones').innerHTML = '';
    docenteEnVista = idDocente;

    fetch(`/api/director/docentes/${idDocente}/detalle`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => renderModalVer(data))
        .catch((error) => {
            document.getElementById('dv-nombre').textContent = 'No se pudo cargar el detalle del docente.';
            console.error(error);
        });
}

function renderModalVer(data) {
    const nombre = nombreCompleto(data.docente);
    const estado = ESTADO_CARGA[data.estado_carga] ?? ESTADO_CARGA.SIN_CARGA;

    document.getElementById('dv-avatar').textContent = iniciales(nombre);
    document.getElementById('dv-nombre').textContent = nombre || '—';
    document.getElementById('dv-especialidad').textContent = data.docente?.especialidad ?? '—';
    document.getElementById('dv-tipo').textContent = TIPO_DOCENTE_LABEL[data.docente?.tipo_docente] ?? data.docente?.tipo_docente ?? '—';

    const estadoEl = document.getElementById('dv-estado');
    estadoEl.className = `c-badge dir-docentes-badge ${estado.clase}`;
    estadoEl.textContent = estado.label;

    document.getElementById('dv-cursos').textContent = data.cursos_count ?? 0;
    document.getElementById('dv-carga').textContent = `${data.carga_semanal ?? 0}h`;
    document.getElementById('dv-limite').textContent = `${data.limite_horas ?? 0}h`;

    const disponible = data.disponible ?? 0;
    document.getElementById('dv-disp-label').textContent = disponible < 0 ? 'Exceso de carga' : 'Disponibilidad restante';
    document.getElementById('dv-disponible').textContent = `${Math.abs(disponible)}h`;
    document.getElementById('dv-disp-icon').className = disponible < 0
        ? 'bi bi-exclamation-triangle-fill dir-docentes-icon-red'
        : 'bi bi-signpost-split dir-docentes-icon-green';

    const cursos = data.cursos ?? [];
    document.getElementById('dv-cursos-tbody').innerHTML = cursos.length
        ? cursos.map((c) => `
            <tr>
                <td>${c.nombre_curso}</td>
                <td>${c.programa ?? '—'}</td>
                <td>${c.ciclo ?? '—'}</td>
                <td>${c.horas_semana}h</td>
                <td>${c.periodo ?? '—'}</td>
                <td>${c.aula ?? '—'}</td>
            </tr>
        `).join('')
        : '<tr><td colspan="6" class="c-table-empty">Sin cursos asignados.</td></tr>';
    document.getElementById('dv-cursos-total').textContent = `${data.carga_semanal ?? 0}h`;

    document.getElementById('dv-dist-grid').innerHTML = (data.carga_por_dia ?? []).map((horas, i) => `
        <div class="dir-docentes-dist-day">
            <div class="dir-docentes-dist-label">${DIAS[i][0]}</div>
            <div class="dir-docentes-dist-box ${claseDia(horas)}">${horas}h</div>
        </div>
    `).join('');

    const conflictos = data.conflictos ?? [];
    let observaciones = conflictos.length
        ? conflictos.map((msg) => `
            <div class="dir-docentes-obs-item amber">
                <i class="bi bi-exclamation-triangle"></i>
                <div><div class="dir-docentes-obs-title">Coincidencia de horario</div><div class="dir-docentes-obs-desc">${msg}</div></div>
            </div>
        `).join('')
        : `
            <div class="dir-docentes-obs-item blue">
                <i class="bi bi-info-circle"></i>
                <div><div class="dir-docentes-obs-title">Sin observaciones</div><div class="dir-docentes-obs-desc">No se detectaron cruces de horario para este docente.</div></div>
            </div>
        `;

    if (disponible < 0) {
        observaciones += `
            <div class="dir-docentes-obs-item red">
                <i class="bi bi-exclamation-octagon"></i>
                <div><div class="dir-docentes-obs-title">Sobrecarga</div><div class="dir-docentes-obs-desc">La carga semanal supera en ${Math.abs(disponible)}h el límite institucional de ${data.limite_horas}h.</div></div>
            </div>
        `;
    }

    document.getElementById('dv-observaciones').innerHTML = observaciones;
}

function cerrarModalVer() {
    document.getElementById('dir-docentes-view-modal').classList.remove('show');
    docenteEnVista = null;
}

/* ---------- Modal "Asignar cursos" ---------- */

function itemCurso(curso, estadoBtn) {
    const meta = `${curso.programa?.nombre ?? '—'} · Ciclo ${curso.ciclo ?? '—'} · ${curso.horas_ud ?? 0}h/sem`;

    const boton = estadoBtn === 'asignado'
        ? '<button type="button" class="dir-docentes-btn-toggle added" disabled><i class="bi bi-check2"></i> Asignado</button>'
        : estadoBtn === 'seleccionado'
            ? `<button type="button" class="dir-docentes-btn-toggle added" data-quitar="${curso.id_curso}"><i class="bi bi-check2"></i> Seleccionado</button>`
            : `<button type="button" class="dir-docentes-btn-toggle" data-agregar="${curso.id_curso}"><i class="bi bi-plus-lg"></i> Asignar</button>`;

    return `
        <div class="dir-docentes-curso-item">
            <div>
                <div class="dir-docentes-curso-nombre">${curso.nombre_curso}</div>
                <div class="dir-docentes-curso-meta">${meta}</div>
            </div>
            ${boton}
        </div>
    `;
}

function filtrosModalActuales() {
    return {
        q: (document.getElementById('dir-docentes-modal-search')?.value ?? '').trim().toLowerCase(),
        idPrograma: document.getElementById('dir-docentes-modal-filtro-programa')?.value ?? '',
        ciclo: document.getElementById('dir-docentes-modal-filtro-ciclo')?.value ?? '',
    };
}

function renderListaCursosModal() {
    const cont = document.getElementById('dir-docentes-modal-cursos');
    if (!cont) return;

    const { q, idPrograma, ciclo } = filtrosModalActuales();
    const pasaFiltro = (curso) => {
        if (q && !curso.nombre_curso.toLowerCase().includes(q)) return false;
        if (idPrograma && String(curso.id_programa) !== idPrograma) return false;
        if (ciclo && curso.ciclo !== ciclo) return false;

        return true;
    };

    const asignados = (cursosModalCache.asignados ?? []).filter(pasaFiltro).map((c) => itemCurso(c, 'asignado'));
    const disponibles = (cursosModalCache.disponibles ?? []).filter(pasaFiltro)
        .map((c) => itemCurso(c, seleccionados.has(c.id_curso) ? 'seleccionado' : 'disponible'));

    const items = [...asignados, ...disponibles];
    cont.innerHTML = items.length ? items.join('') : '<div class="c-table-empty">No hay cursos para este filtro.</div>';
}

function renderSeleccionadosTabla() {
    const tbody = document.getElementById('dir-docentes-modal-seleccionados');
    if (!tbody) return;

    const filas = [...seleccionados.values()];
    tbody.innerHTML = filas.length
        ? filas.map((c) => `
            <tr>
                <td>${c.nombre_curso}</td>
                <td>${c.ciclo ?? '—'}</td>
                <td>${c.horas_ud ?? 0}h</td>
                <td><div class="c-btn-icon c-btn-icon-danger" data-quitar-sel="${c.id_curso}" title="Quitar"><i class="bi bi-trash"></i></div></td>
            </tr>
        `).join('')
        : '<tr><td colspan="4" class="c-table-empty">Sin cursos seleccionados.</td></tr>';
}

function actualizarResumenAsignacion() {
    const filas = [...seleccionados.values()];
    const horas = filas.reduce((acc, c) => acc + Number(c.horas_ud ?? 0), 0);
    const proyectada = (docenteEnModal?.carga_semanal ?? 0) + horas;

    document.getElementById('am-sum-cursos').textContent = filas.length;
    document.getElementById('am-sum-horas').textContent = `${horas}h`;

    const proyEl = document.getElementById('am-sum-proyectada');
    proyEl.textContent = `${proyectada}h`;
    proyEl.classList.toggle('over', proyectada > LIMITE_HORAS);
}

function renderCabeceraAsignacion() {
    const d = docenteEnModal;
    const nombre = nombreCompleto(d);
    const estado = ESTADO_CARGA[d.estado_carga] ?? ESTADO_CARGA.SIN_CARGA;

    document.getElementById('am-avatar').textContent = iniciales(nombre);
    document.getElementById('am-nombre').textContent = nombre || '—';
    document.getElementById('am-especialidad').textContent = d.especialidad ?? '—';
    document.getElementById('am-tipo').textContent = TIPO_DOCENTE_LABEL[d.tipo_docente] ?? d.tipo_docente ?? '—';

    const estadoEl = document.getElementById('am-estado');
    estadoEl.className = `c-badge dir-docentes-badge ${estado.clase}`;
    estadoEl.textContent = estado.label;

    document.getElementById('am-carga-actual').textContent = `${d.carga_semanal ?? 0}h`;

    const exceso = (d.carga_semanal ?? 0) - LIMITE_HORAS;
    const flag = document.getElementById('am-overload-flag');
    if (exceso > 0) {
        flag.style.display = 'inline-flex';
        flag.textContent = `Exceso de carga: +${exceso}h`;
    } else {
        flag.style.display = 'none';
    }
}

function cargarDocenteEnModal(idDocente) {
    docenteEnModal = docentesCache.find((d) => String(d.id_docente) === String(idDocente));
    if (!docenteEnModal) return;

    seleccionados = new Map();
    cursosModalCache = { asignados: [], disponibles: [] };

    renderCabeceraAsignacion();
    renderSeleccionadosTabla();
    actualizarResumenAsignacion();
    document.getElementById('dir-docentes-modal-cursos').innerHTML = '<div class="c-table-empty">Cargando…</div>';

    fetch(`/api/director/docentes/${idDocente}/cursos-disponibles`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            cursosModalCache = { asignados: data.asignados ?? [], disponibles: data.disponibles ?? [] };
            renderListaCursosModal();
        })
        .catch((error) => {
            document.getElementById('dir-docentes-modal-error').textContent = 'No se pudo cargar la información de cursos.';
            console.error(error);
        });
}

function abrirModalAsignar(idDocente) {
    document.getElementById('dir-docentes-modal-error').textContent = '';
    document.getElementById('dir-docentes-modal').classList.add('show');

    if (!idDocente) {
        docenteEnModal = null;
        document.getElementById('dir-docentes-modal-picker').style.display = 'block';
        document.getElementById('dir-docentes-modal-body').style.display = 'none';
        poblarSelectDocentes();

        return;
    }

    document.getElementById('dir-docentes-modal-picker').style.display = 'none';
    document.getElementById('dir-docentes-modal-body').style.display = 'block';
    cargarDocenteEnModal(idDocente);
}

function cerrarModalAsignar() {
    document.getElementById('dir-docentes-modal').classList.remove('show');
    docenteEnModal = null;
    seleccionados = new Map();
    cursosModalCache = { asignados: [], disponibles: [] };
    document.getElementById('dir-docentes-modal-search').value = '';
    document.getElementById('dir-docentes-modal-filtro-programa').value = '';
    document.getElementById('dir-docentes-modal-filtro-ciclo').value = '';
}

function agregarCursoSeleccion(idCurso) {
    const curso = (cursosModalCache.disponibles ?? []).find((c) => c.id_curso === idCurso);
    if (!curso) return;

    seleccionados.set(idCurso, curso);
    renderListaCursosModal();
    renderSeleccionadosTabla();
    actualizarResumenAsignacion();
}

function quitarCursoSeleccion(idCurso) {
    seleccionados.delete(idCurso);
    renderListaCursosModal();
    renderSeleccionadosTabla();
    actualizarResumenAsignacion();
}

function guardarAsignacion() {
    if (!docenteEnModal) {
        document.getElementById('dir-docentes-modal-error').textContent = 'Seleccione un docente.';

        return;
    }

    const cursos = [...seleccionados.keys()];
    if (!cursos.length) {
        document.getElementById('dir-docentes-modal-error').textContent = 'Seleccione al menos un curso para asignar.';

        return;
    }

    fetch(`/api/director/docentes/${docenteEnModal.id_docente}/asignar-cursos`, {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ cursos }),
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then(() => {
            cerrarModalAsignar();
            cargarDocentes();
        })
        .catch((error) => {
            document.getElementById('dir-docentes-modal-error').textContent = error?.message ?? 'No se pudo asignar los cursos seleccionados.';
        });
}

export function initDirectorDocentes() {
    const tbody = document.getElementById('dir-docentes-tbody');
    if (!tbody) return;

    cargarDocentes();

    let debounce;
    document.getElementById('dir-docentes-search')?.addEventListener('input', () => {
        clearTimeout(debounce);
        debounce = setTimeout(aplicarFiltros, 300);
    });
    document.getElementById('dir-docentes-filtro-programa')?.addEventListener('change', aplicarFiltros);
    document.getElementById('dir-docentes-filtro-especialidad')?.addEventListener('change', aplicarFiltros);
    document.getElementById('dir-docentes-filtro-estado')?.addEventListener('change', aplicarFiltros);

    document.getElementById('dir-docentes-limpiar')?.addEventListener('click', () => {
        document.getElementById('dir-docentes-filtro-programa').value = '';
        document.getElementById('dir-docentes-filtro-especialidad').value = '';
        document.getElementById('dir-docentes-filtro-estado').value = '';
        document.getElementById('dir-docentes-search').value = '';
        aplicarFiltros();
    });

    document.getElementById('dir-docentes-page-size')?.addEventListener('change', (event) => {
        tamanoPagina = Number(event.target.value);
        paginaActual = 1;
        renderTabla();
    });

    document.getElementById('dir-docentes-btn-asignar')?.addEventListener('click', () => abrirModalAsignar(null));

    document.getElementById('dir-docentes-view-cerrar')?.addEventListener('click', cerrarModalVer);
    document.getElementById('dir-docentes-view-cerrar-x')?.addEventListener('click', cerrarModalVer);
    document.getElementById('dir-docentes-view-reasignar')?.addEventListener('click', () => {
        const id = docenteEnVista;
        cerrarModalVer();
        if (id) abrirModalAsignar(id);
    });

    document.getElementById('dir-docentes-modal-cancelar')?.addEventListener('click', cerrarModalAsignar);
    document.getElementById('dir-docentes-modal-cerrar-x')?.addEventListener('click', cerrarModalAsignar);
    document.getElementById('dir-docentes-modal-guardar')?.addEventListener('click', guardarAsignacion);

    document.getElementById('dir-docentes-modal-select-docente')?.addEventListener('change', (event) => {
        if (!event.target.value) return;
        document.getElementById('dir-docentes-modal-picker').style.display = 'none';
        document.getElementById('dir-docentes-modal-body').style.display = 'block';
        cargarDocenteEnModal(event.target.value);
    });

    document.getElementById('dir-docentes-modal-search')?.addEventListener('input', renderListaCursosModal);
    document.getElementById('dir-docentes-modal-filtro-programa')?.addEventListener('change', renderListaCursosModal);
    document.getElementById('dir-docentes-modal-filtro-ciclo')?.addEventListener('change', renderListaCursosModal);

    document.getElementById('dir-docentes-modal-cursos')?.addEventListener('click', (event) => {
        const agregar = event.target.closest('[data-agregar]');
        if (agregar) { agregarCursoSeleccion(Number(agregar.dataset.agregar)); return; }

        const quitar = event.target.closest('[data-quitar]');
        if (quitar) quitarCursoSeleccion(Number(quitar.dataset.quitar));
    });

    document.getElementById('dir-docentes-modal-seleccionados')?.addEventListener('click', (event) => {
        const quitar = event.target.closest('[data-quitar-sel]');
        if (quitar) quitarCursoSeleccion(Number(quitar.dataset.quitarSel));
    });
}

document.addEventListener('DOMContentLoaded', initDirectorDocentes);
