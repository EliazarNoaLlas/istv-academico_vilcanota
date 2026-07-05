/**
 * Editor de horarios. Regla critica (Fase 5/7): el guardado reemplaza solo
 * el subconjunto del filtro activo (docente/semestre/programa), nunca
 * bloques fuera de ese filtro. Por eso este archivo siempre mantiene el
 * horario completo del filtro actual en memoria (`horarioActual`) y lo
 * envia entero al guardar — nunca un bloque suelto.
 */
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

let horarioActual = [];
let bloqueEnEdicion = null;

function filtrosActuales() {
    const docente = document.getElementById('coord-horarios-filtro-docente')?.value;
    const programa = document.getElementById('coord-horarios-filtro-programa')?.value;
    const ciclo = document.querySelector('.coord-horarios-ciclo-bar button.is-active')?.dataset.ciclo;

    const filtros = {};
    if (docente) filtros.id_docente = docente;
    if (programa) filtros.id_programa = programa;
    if (ciclo) filtros.semestre = ciclo;

    return filtros;
}

function queryDesdeFiltros(filtros) {
    const params = new URLSearchParams();
    if (filtros.id_docente) params.set('id_docente', filtros.id_docente);
    if (filtros.id_programa) params.set('id_programa', filtros.id_programa);
    if (filtros.semestre) params.set('semestre', filtros.semestre);

    return params.toString();
}

function limpiarTablero() {
    document.querySelectorAll('.academic-slot').forEach((slot) => { slot.innerHTML = ''; });
}

function pintarBloque(bloque, enConflicto) {
    const slot = document.querySelector(`.academic-slot[data-day="${bloque.dia}"][data-start="${bloque.hora_inicio.slice(0, 5)}"]`);
    if (!slot) return;

    const color = bloque.color ?? { border: '#439dc4', bg: '#7ec8e3' };
    const docenteNombre = bloque.docente ? `${bloque.docente.usuario?.nombres ?? ''} ${bloque.docente.usuario?.apellidos ?? ''}` : '';

    const article = document.createElement('article');
    article.className = 'schedule-block academic-block course-color';
    if (enConflicto) article.classList.add('has-conflict');
    article.style.setProperty('--course-border', color.border);
    article.style.setProperty('--course-bg', color.bg);
    article.dataset.id = bloque.id_horario;
    article.draggable = true;
    article.title = enConflicto
        ? 'Conflicto: hay más de un bloque en este mismo día y hora. Muévelo o elimínalo.'
        : `${bloque.curso?.nombre_curso ?? ''} — ${docenteNombre}`;
    article.innerHTML = `
        ${enConflicto ? '<i class="bi bi-exclamation-triangle-fill conflict-flag"></i>' : ''}
        <strong>${(bloque.curso?.nombre_curso ?? '').toUpperCase()}</strong>
        <span>${docenteNombre.toUpperCase()}</span>
    `;
    article.addEventListener('click', () => abrirModal(bloque));
    article.addEventListener('dragstart', (event) => {
        event.dataTransfer.setData('text/plain', String(bloque.id_horario));
        event.dataTransfer.effectAllowed = 'move';
        article.classList.add('is-dragging');
    });
    article.addEventListener('dragend', () => article.classList.remove('is-dragging'));

    slot.appendChild(article);
}

/**
 * Agrupa por dia+hora antes de pintar: si dos o mas bloques reales caen en
 * la misma celda (conflicto que el legacy nunca valido al guardar), se
 * marcan con .has-conflict y se listan en el resumen de conflictos en vez
 * de quedar amontonados sin distincion visual.
 */
function renderTablero() {
    limpiarTablero();

    const porSlot = new Map();
    horarioActual.forEach((bloque) => {
        const clave = `${bloque.dia}|${bloque.hora_inicio.slice(0, 5)}`;
        if (!porSlot.has(clave)) porSlot.set(clave, []);
        porSlot.get(clave).push(bloque);
    });

    const conflictosDeDatos = [];

    porSlot.forEach((bloques, clave) => {
        const enConflicto = bloques.length > 1;
        if (enConflicto) {
            const [dia, hora] = clave.split('|');
            const nombres = bloques.map((b) => b.curso?.nombre_curso ?? '—').join(' / ');
            conflictosDeDatos.push(`${dia} ${hora}: dos bloques ocupan la misma celda (${nombres}). Mueve o elimina uno.`);
        }
        bloques.forEach((bloque) => pintarBloque(bloque, enConflicto));
    });

    if (conflictosDeDatos.length > 0) {
        mostrarConflictos(conflictosDeDatos);
    }

    return conflictosDeDatos.length > 0;
}

function marcarCambiosSinGuardar() {
    document.getElementById('coord-horarios-guardar-hint')?.classList.add('show');
}

/**
 * Arrastrar y soltar entre celdas: si la celda destino ya tiene un bloque,
 * se intercambian dia/hora entre ambos; si esta vacia, el bloque se mueve
 * ahi. Todo queda en memoria (`horarioActual`) hasta pulsar
 * "Guardar horario completo" — nunca se persiste automaticamente.
 */
function moverBloque(idHorario, destino) {
    const origenIdx = horarioActual.findIndex((b) => String(b.id_horario) === String(idHorario));
    if (origenIdx === -1) return;

    const origen = horarioActual[origenIdx];
    if (origen.dia === destino.dia && origen.hora_inicio.slice(0, 5) === destino.inicio) return;

    const destinoIdx = horarioActual.findIndex((b) => b.dia === destino.dia && b.hora_inicio.slice(0, 5) === destino.inicio);

    if (destinoIdx !== -1 && destinoIdx !== origenIdx) {
        const destinoBloque = horarioActual[destinoIdx];
        const diaTmp = destinoBloque.dia;
        const inicioTmp = destinoBloque.hora_inicio;
        const finTmp = destinoBloque.hora_fin;

        destinoBloque.dia = origen.dia;
        destinoBloque.hora_inicio = origen.hora_inicio;
        destinoBloque.hora_fin = origen.hora_fin;

        origen.dia = diaTmp;
        origen.hora_inicio = inicioTmp;
        origen.hora_fin = finTmp;
    } else {
        origen.dia = destino.dia;
        origen.hora_inicio = `${destino.inicio}:00`;
        origen.hora_fin = `${destino.fin}:00`;
    }

    renderTablero();
    marcarCambiosSinGuardar();
}

function wireDragTargets() {
    document.querySelectorAll('.academic-slot').forEach((slot) => {
        slot.addEventListener('dragover', (event) => {
            event.preventDefault();
            slot.classList.add('is-drop-target');
        });
        slot.addEventListener('dragleave', () => slot.classList.remove('is-drop-target'));
        slot.addEventListener('drop', (event) => {
            event.preventDefault();
            slot.classList.remove('is-drop-target');
            const idHorario = event.dataTransfer.getData('text/plain');
            moverBloque(idHorario, {
                dia: slot.dataset.day,
                inicio: slot.dataset.start,
                fin: slot.dataset.end,
            });
        });
    });
}

function renderKpis(horarios, docentesActivos) {
    const root = document.getElementById('coord-horarios-kpis');
    const cursosUnicos = new Set(horarios.map((h) => h.id_curso)).size;

    root.querySelector('[data-kpi="cursos"]').textContent = cursosUnicos;
    root.querySelector('[data-kpi="docentes"]').textContent = docentesActivos ?? '—';
}

function actualizarTituloPrograma() {
    const select = document.getElementById('coord-horarios-filtro-programa');
    const texto = select?.selectedOptions[0]?.text ?? 'Todos los programas';
    document.getElementById('coord-horarios-programa-actual').textContent = texto;
    document.getElementById('coord-horarios-schedule-title').textContent = texto.toUpperCase();
}

function cargarHorario() {
    const filtros = filtrosActuales();
    const query = queryDesdeFiltros(filtros);

    fetch(`/api/coordinador/horarios${query ? `?${query}` : ''}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            horarioActual = data.horarios ?? [];
            const hayConflictosDeDatos = renderTablero();
            renderKpis(horarioActual, data.docentes_activos);
            renderTablaDebug(horarioActual);
            if (!hayConflictosDeDatos) ocultarConflictos();
            document.getElementById('coord-horarios-guardar-hint')?.classList.remove('show');
        })
        .catch((error) => console.error(error));
}

function abrirModal(bloque = null) {
    bloqueEnEdicion = bloque;
    const form = document.getElementById('coord-horarios-form');
    form.reset();
    document.getElementById('coord-horarios-form-error').classList.remove('show');
    document.getElementById('coord-horarios-modal-title').textContent = bloque ? 'Editar bloque' : 'Nuevo bloque';

    if (bloque) {
        form.elements.id_curso.value = bloque.id_curso;
        form.elements.id_docente.value = bloque.id_docente;
        form.elements.dia.value = bloque.dia;
        form.elements.aula.value = bloque.aula ?? '';
        const inicio = bloque.hora_inicio.slice(0, 5);
        const fin = bloque.hora_fin.slice(0, 5);
        form.elements._bloque.value = `${inicio}|${fin}`;
        form.elements.hora_inicio.value = inicio;
        form.elements.hora_fin.value = fin;
    }

    document.getElementById('coord-horarios-modal').classList.add('show');
}

function cerrarModal() {
    document.getElementById('coord-horarios-modal').classList.remove('show');
    bloqueEnEdicion = null;
}

function mostrarConflictos(mensajes) {
    const box = document.getElementById('coord-horarios-conflictos-box');
    const lista = document.getElementById('coord-horarios-conflictos-lista');
    lista.innerHTML = mensajes.map((m) => `<li>${m}</li>`).join('');
    box.classList.add('show');
    document.getElementById('coord-horarios-resumen-ok').style.display = 'none';

    document.getElementById('coord-horarios-kpis').querySelector('[data-kpi="conflictos"]').textContent = mensajes.length;
}

function ocultarConflictos() {
    document.getElementById('coord-horarios-conflictos-box').classList.remove('show');
    document.getElementById('coord-horarios-resumen-ok').style.display = 'flex';
    document.getElementById('coord-horarios-kpis').querySelector('[data-kpi="conflictos"]').textContent = 0;
}

function guardarHorarioCompleto() {
    const filtros = filtrosActuales();

    fetch('/api/coordinador/horarios', {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            horarios: horarioActual.map((b) => ({
                id_curso: b.id_curso,
                id_docente: b.id_docente,
                dia: b.dia,
                hora_inicio: b.hora_inicio.slice(0, 5),
                hora_fin: b.hora_fin.slice(0, 5),
                aula: b.aula,
            })),
            filtro_docente: filtros.id_docente ?? null,
            filtro_semestre: filtros.semestre ?? null,
            filtro_programa: filtros.id_programa ?? null,
        }),
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then(() => {
            cargarHorario();
        })
        .catch((error) => {
            const mensajes = error?.conflictos ?? error?.errores ?? (error?.errors ? Object.values(error.errors).flat() : ['No se pudo guardar el horario.']);
            mostrarConflictos(Array.isArray(mensajes) ? mensajes : [mensajes]);
        });
}

function enviarFormularioBloque(event) {
    event.preventDefault();
    const form = event.target;
    const errorBox = document.getElementById('coord-horarios-form-error');
    const datos = Object.fromEntries(new FormData(form).entries());

    const nuevoBloque = {
        id_curso: Number(datos.id_curso),
        id_docente: Number(datos.id_docente),
        dia: datos.dia,
        hora_inicio: datos.hora_inicio,
        hora_fin: datos.hora_fin,
        aula: datos.aula,
        docente: null,
        curso: null,
    };

    if (!nuevoBloque.id_curso || !nuevoBloque.id_docente || !nuevoBloque.dia || !nuevoBloque.hora_inicio || !nuevoBloque.aula) {
        errorBox.textContent = 'Complete todos los campos del bloque.';
        errorBox.classList.add('show');

        return;
    }

    if (bloqueEnEdicion) {
        horarioActual = horarioActual.map((b) => (b.id_horario === bloqueEnEdicion.id_horario ? { ...b, ...nuevoBloque, id_horario: b.id_horario } : b));
    } else {
        horarioActual.push({ ...nuevoBloque, id_horario: `tmp-${Date.now()}` });
    }

    cerrarModal();
    guardarHorarioCompleto();
}

function detectarConflictos() {
    fetch('/api/coordinador/horarios/detectar-conflictos', {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            horarios: horarioActual.map((b) => ({
                id_curso: b.id_curso,
                id_docente: b.id_docente,
                dia: b.dia,
                hora_inicio: b.hora_inicio.slice(0, 5),
                hora_fin: b.hora_fin.slice(0, 5),
                aula: b.aula,
            })),
        }),
    })
        .then((res) => res.json())
        .then((data) => {
            const mensajes = [...(data.conflictos ?? []), ...(data.errores_institucionales ?? [])];
            if (mensajes.length === 0) {
                ocultarConflictos();
            } else {
                mostrarConflictos(mensajes);
            }
        })
        .catch((error) => console.error(error));
}

function limpiarHorario() {
    if (!confirm('¿Confirma limpiar el horario del filtro actual? Esta acción no se puede deshacer.')) return;

    const filtros = filtrosActuales();

    fetch('/api/coordinador/horarios/limpiar', {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            filtro_docente: filtros.id_docente ?? null,
            filtro_semestre: filtros.semestre ?? null,
            filtro_programa: filtros.id_programa ?? null,
            confirmar: true,
        }),
    })
        .then((res) => res.json())
        .then(() => cargarHorario())
        .catch((error) => console.error(error));
}

let idGeneracionIaActual = null;

function idPeriodoSeleccionado() {
    const select = document.getElementById('coord-horarios-filtro-periodo');
    return select?.selectedOptions[0]?.dataset.idPeriodo ?? null;
}

function abrirModalIa() {
    document.getElementById('coord-horarios-ia-modal')?.classList.add('show');
}

function cerrarModalIa() {
    document.getElementById('coord-horarios-ia-modal')?.classList.remove('show');
    idGeneracionIaActual = null;
}

function pintarResultadoIa(data) {
    const generacion = data.generacion ?? {};
    idGeneracionIaActual = generacion.id_generacion ?? null;

    document.getElementById('coord-horarios-ia-estado').textContent = `Estado: ${generacion.estado ?? '—'} · Proveedor: ${generacion.modelo ?? '—'}`;

    const errorBox = document.getElementById('coord-horarios-ia-error');
    if (!data.ok && (!generacion.resultado || (generacion.resultado.detalles ?? []).length === 0)) {
        errorBox.textContent = data.mensaje ?? 'No se pudo generar la propuesta.';
        errorBox.classList.add('show');
    } else {
        errorBox.classList.remove('show');
    }

    const detalles = generacion.resultado?.detalles ?? [];
    const tbody = document.getElementById('coord-horarios-ia-tbody');
    tbody.innerHTML = detalles.length
        ? detalles.map((d) => `
            <tr>
                <td>${d.id_curso}</td>
                <td>${d.id_docente}</td>
                <td>${d.id_aula}</td>
                <td>${d.dia}</td>
                <td>${d.hora_inicio}</td>
                <td>${d.hora_fin}</td>
            </tr>
        `).join('')
        : '<tr><td colspan="6" class="coord-portafolio-empty">Sin bloques propuestos.</td></tr>';

    const observaciones = generacion.resultado?.observaciones ?? [];
    const observacionesBox = document.getElementById('coord-horarios-ia-observaciones-box');
    document.getElementById('coord-horarios-ia-observaciones').innerHTML = observaciones.map((o) => `<li>${o}</li>`).join('');
    observacionesBox.style.display = observaciones.length ? 'block' : 'none';

    const errores = generacion.errores ?? null;
    const mensajesConflicto = errores ? [...(errores.errores ?? []), ...(errores.conflictos ?? []).map((c) => c.mensaje ?? JSON.stringify(c))] : [];
    const conflictosBox = document.getElementById('coord-horarios-ia-conflictos-box');
    document.getElementById('coord-horarios-ia-conflictos').innerHTML = mensajesConflicto.map((m) => `<li>${m}</li>`).join('');
    conflictosBox.style.display = mensajesConflicto.length ? 'block' : 'none';

    const puedeAprobar = generacion.estado === 'BORRADOR' && mensajesConflicto.length === 0 && detalles.length > 0;
    document.getElementById('coord-horarios-ia-aprobar').style.display = generacion.estado === 'APROBADO' ? 'none' : '';
    document.getElementById('coord-horarios-ia-aprobar').disabled = !puedeAprobar;
    document.getElementById('coord-horarios-ia-reparar').style.display = mensajesConflicto.length > 0 ? '' : 'none';
    document.getElementById('coord-horarios-ia-descartar').style.display = generacion.estado === 'APROBADO' ? 'none' : '';
}

function llamarApiIa(url, method = 'POST') {
    return fetch(url, {
        method,
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
    }).then((res) => res.json());
}

function generarIA(endpoint) {
    const filtros = filtrosActuales();
    const idPrograma = filtros.id_programa;
    const idPeriodo = idPeriodoSeleccionado();

    if (!idPrograma || !idPeriodo) {
        mostrarConflictos(['Seleccione un programa y un periodo académico antes de generar con IA.']);

        return;
    }

    abrirModalIa();
    document.getElementById('coord-horarios-ia-estado').textContent = 'Generando propuesta…';
    document.getElementById('coord-horarios-ia-tbody').innerHTML = '<tr><td colspan="6" class="coord-portafolio-empty">Generando…</td></tr>';

    fetch(endpoint, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            id_programa: idPrograma,
            id_periodo: idPeriodo,
            semestre: filtros.semestre ?? null,
        }),
    })
        .then((res) => res.json())
        .then(pintarResultadoIa)
        .catch(() => {
            document.getElementById('coord-horarios-ia-error').textContent = 'No se pudo contactar al servidor.';
            document.getElementById('coord-horarios-ia-error').classList.add('show');
        });
}

function aprobarGeneracionIa() {
    if (!idGeneracionIaActual) return;
    llamarApiIa(`/api/coordinador/horarios/ia/${idGeneracionIaActual}/aprobar`)
        .then((data) => {
            pintarResultadoIa(data);
            if (data.ok) cargarHorario();
        });
}

function descartarGeneracionIa() {
    if (!idGeneracionIaActual) return;
    llamarApiIa(`/api/coordinador/horarios/ia/${idGeneracionIaActual}/descartar`)
        .then(() => cerrarModalIa());
}

function repararGeneracionIa() {
    if (!idGeneracionIaActual) return;
    document.getElementById('coord-horarios-ia-estado').textContent = 'Reparando…';
    llamarApiIa(`/api/coordinador/horarios/ia/${idGeneracionIaActual}/reparar`)
        .then(pintarResultadoIa);
}

function renderTablaDebug(horarios) {
    const tbody = document.getElementById('coord-horarios-debug-tbody');
    if (!tbody) return;

    tbody.innerHTML = horarios.length
        ? horarios.map((h) => `
            <tr>
                <td>${h.id_horario}</td>
                <td>${h.curso?.nombre_curso ?? '—'}</td>
                <td>${h.docente ? `${h.docente.usuario?.nombres ?? ''} ${h.docente.usuario?.apellidos ?? ''}` : '—'}</td>
                <td>${h.dia}</td>
                <td>${h.hora_inicio}</td>
                <td>${h.hora_fin}</td>
                <td>${h.aula ?? '—'}</td>
                <td>${h.estado}</td>
                <td>${h.fuente}</td>
            </tr>
        `).join('')
        : '<tr><td colspan="9" class="coord-portafolio-empty">Sin registros para este filtro.</td></tr>';
}

export function initCoordinadorHorarios() {
    const tbody = document.getElementById('coord-horarios-tbody');
    if (!tbody) return;

    cargarHorario();
    wireDragTargets();

    document.querySelectorAll('.coord-horarios-ciclo-bar button').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.coord-horarios-ciclo-bar button').forEach((b) => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            cargarHorario();
        });
    });

    document.getElementById('coord-horarios-filtro-docente')?.addEventListener('change', cargarHorario);
    document.getElementById('coord-horarios-filtro-programa')?.addEventListener('change', () => {
        actualizarTituloPrograma();
        cargarHorario();
    });

    document.getElementById('coord-horarios-nuevo')?.addEventListener('click', () => abrirModal());
    document.getElementById('coord-horarios-modal-cerrar')?.addEventListener('click', cerrarModal);
    document.getElementById('coord-horarios-form')?.addEventListener('submit', enviarFormularioBloque);

    document.getElementById('coord-horarios-select-bloque')?.addEventListener('change', (e) => {
        const [inicio, fin] = e.target.value.split('|');
        const form = e.target.form;
        form.elements.hora_inicio.value = inicio ?? '';
        form.elements.hora_fin.value = fin ?? '';
    });

    document.getElementById('coord-horarios-detectar')?.addEventListener('click', detectarConflictos);
    document.getElementById('coord-horarios-limpiar')?.addEventListener('click', limpiarHorario);
    document.getElementById('coord-horarios-generar-semestre')?.addEventListener('click', () => generarIA('/api/coordinador/horarios/generar-semestre'));
    document.getElementById('coord-horarios-generar-todos')?.addEventListener('click', () => generarIA('/api/coordinador/horarios/generar-todos'));

    document.getElementById('coord-horarios-ia-cerrar')?.addEventListener('click', cerrarModalIa);
    document.getElementById('coord-horarios-ia-aprobar')?.addEventListener('click', aprobarGeneracionIa);
    document.getElementById('coord-horarios-ia-descartar')?.addEventListener('click', descartarGeneracionIa);
    document.getElementById('coord-horarios-ia-reparar')?.addEventListener('click', repararGeneracionIa);

    actualizarTituloPrograma();
}

document.addEventListener('DOMContentLoaded', initCoordinadorHorarios);
