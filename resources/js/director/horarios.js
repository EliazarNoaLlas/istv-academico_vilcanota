/**
 * Editor de horarios (Direccion). Espejo de resources/js/coordinador/horarios.js:
 * misma logica de guardado por filtro, drag&drop en memoria y generacion IA,
 * apuntando a los endpoints /api/director/horarios/*.
 */
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

let horarioActual = [];
let bloqueEnEdicion = null;

function filtrosActuales() {
    const docente = document.getElementById('dir-horarios-filtro-docente')?.value;
    const programa = document.getElementById('dir-horarios-filtro-programa')?.value;
    const semestre = document.getElementById('dir-horarios-filtro-semestre')?.value;

    const filtros = {};
    if (docente) filtros.id_docente = docente;
    if (programa) filtros.id_programa = programa;
    if (semestre) filtros.semestre = semestre;

    return filtros;
}

function idPeriodoSeleccionado() {
    const select = document.getElementById('dir-horarios-filtro-periodo');
    return select?.selectedOptions[0]?.dataset.idPeriodo ?? null;
}

function queryDesdeFiltros(filtros) {
    const params = new URLSearchParams();
    if (filtros.id_docente) params.set('id_docente', filtros.id_docente);
    if (filtros.id_programa) params.set('id_programa', filtros.id_programa);
    if (filtros.semestre) params.set('semestre', filtros.semestre);

    return params.toString();
}

function limpiarTablero() {
    document.querySelectorAll('#dir-horarios-tbody .academic-slot').forEach((slot) => { slot.innerHTML = ''; });
}

function pintarBloque(bloque, enConflicto) {
    const slot = document.querySelector(`#dir-horarios-tbody .academic-slot[data-day="${bloque.dia}"][data-start="${bloque.hora_inicio.slice(0, 5)}"]`);
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
    document.getElementById('dir-horarios-guardar-hint')?.classList.add('show');
}

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
    document.querySelectorAll('#dir-horarios-tbody .academic-slot').forEach((slot) => {
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
    const root = document.getElementById('dir-horarios-kpis');
    const cursosUnicos = new Set(horarios.map((h) => h.id_curso)).size;

    root.querySelector('[data-kpi="cursos"]').textContent = cursosUnicos;
    root.querySelector('[data-kpi="docentes"]').textContent = docentesActivos ?? '—';
}

function actualizarTituloPrograma() {
    const select = document.getElementById('dir-horarios-filtro-programa');
    const texto = select?.selectedOptions[0]?.text ?? 'Todos los programas';
    document.getElementById('dir-horarios-programa-actual').textContent = texto;
    document.getElementById('dir-horarios-schedule-title').textContent = texto.toUpperCase();
}

function cargarHorario() {
    const filtros = filtrosActuales();
    const query = queryDesdeFiltros(filtros);

    fetch(`/api/director/horarios${query ? `?${query}` : ''}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            horarioActual = data.horarios ?? [];
            const hayConflictosDeDatos = renderTablero();
            renderKpis(horarioActual, data.docentes_activos);
            if (!hayConflictosDeDatos) ocultarConflictos();
            document.getElementById('dir-horarios-guardar-hint')?.classList.remove('show');
            evaluarEstadoSemestre();
        })
        .catch((error) => console.error(error));
}

/**
 * Fase 5: decide que mostrar segun el estado real del semestre seleccionado
 * (programa + periodo + semestre): tabla normal, panel "sin horario aun" con
 * boton para generar, o panel "cursos sin docente" con la lista y un enlace
 * a asignar docentes. Sin programa/semestre especifico no hay contexto para
 * evaluar por semestre, asi que se mantiene el comportamiento historico
 * (tabla agregada tal cual).
 */
function evaluarEstadoSemestre() {
    const filtros = filtrosActuales();
    const idPeriodo = idPeriodoSeleccionado();
    const wrapper = document.getElementById('dir-horarios-schedule-wrapper');
    const panelVacio = document.getElementById('dir-horarios-estado-vacio');
    const panelSinDocente = document.getElementById('dir-horarios-estado-sin-docente');

    if (!wrapper || !panelVacio || !panelSinDocente) return;

    if (!filtros.id_programa || !filtros.semestre || !idPeriodo) {
        wrapper.style.display = '';
        panelVacio.style.display = 'none';
        panelSinDocente.style.display = 'none';

        return;
    }

    fetch(`/api/director/horarios/dsi/estado?id_programa=${filtros.id_programa}&id_periodo=${idPeriodo}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const info = (data.semestres ?? []).find((s) => s.semestre === filtros.semestre);

            wrapper.style.display = '';
            panelVacio.style.display = 'none';
            panelSinDocente.style.display = 'none';

            if (!info || info.cursos === 0 || info.bloques_generados > 0) return;

            if (info.cursos_sin_docente > 0) {
                wrapper.style.display = 'none';
                cargarCursosSinDocente(filtros.id_programa, filtros.semestre);
                panelSinDocente.style.display = '';

                return;
            }

            wrapper.style.display = 'none';
            document.getElementById('dir-horarios-estado-vacio-texto').textContent =
                `El semestre ${filtros.semestre} tiene ${info.cursos} curso(s) activos y ${info.bloques_requeridos} bloque(s) por asignar.`;
            panelVacio.style.display = '';
        })
        .catch((error) => console.error(error));
}

function cargarCursosSinDocente(idPrograma, semestre) {
    const lista = document.getElementById('dir-horarios-estado-sin-docente-lista');
    if (!lista) return;
    lista.innerHTML = '<li>Cargando…</li>';

    fetch('/api/director/horarios/catalogos', { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            const cursos = (data.cursos ?? []).filter(
                (c) => String(c.id_programa) === String(idPrograma) && c.semestre === semestre && !c.id_docente,
            );
            lista.innerHTML = cursos.length
                ? cursos.map((c) => `<li>${c.nombre_curso}</li>`).join('')
                : '<li>No se pudo determinar el detalle. Actualice la página.</li>';
        })
        .catch(() => { lista.innerHTML = '<li>No se pudo cargar el detalle.</li>'; });
}

function generarHorarioSemestreActual() {
    const filtros = filtrosActuales();
    const idPeriodo = idPeriodoSeleccionado();
    const boton = document.getElementById('dir-horarios-estado-generar');
    if (!filtros.id_programa || !filtros.semestre || !idPeriodo) return;

    boton.disabled = true;
    boton.innerHTML = '<i class="bi bi-hourglass-split"></i> Generando…';

    fetch('/api/director/horarios/dsi/generar-semestre', {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ id_programa: filtros.id_programa, id_periodo: idPeriodo, semestre: filtros.semestre }),
    })
        .then((res) => res.json())
        .then((data) => {
            boton.disabled = false;
            boton.innerHTML = '<i class="bi bi-magic"></i> Generar horario del semestre';

            if (data.ok) {
                cargarHorario();
            } else {
                mostrarConflictos([data.mensaje ?? 'No se pudo generar el horario.']);
            }
        })
        .catch(() => {
            boton.disabled = false;
            boton.innerHTML = '<i class="bi bi-magic"></i> Generar horario del semestre';
            mostrarConflictos(['No se pudo contactar al servidor.']);
        });
}

function abrirModal(bloque = null) {
    bloqueEnEdicion = bloque;
    const form = document.getElementById('dir-horarios-form');
    form.reset();
    document.getElementById('dir-horarios-form-error').classList.remove('show');
    document.getElementById('dir-horarios-modal-title').textContent = bloque ? 'Editar bloque' : 'Nuevo bloque';

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

    document.getElementById('dir-horarios-modal').classList.add('show');
}

function cerrarModal() {
    document.getElementById('dir-horarios-modal').classList.remove('show');
    bloqueEnEdicion = null;
}

function mostrarConflictos(mensajes) {
    const box = document.getElementById('dir-horarios-conflictos-box');
    const lista = document.getElementById('dir-horarios-conflictos-lista');
    lista.innerHTML = mensajes.map((m) => `<li>${m}</li>`).join('');
    box.classList.add('show');
    document.getElementById('dir-horarios-resumen-ok').style.display = 'none';

    document.getElementById('dir-horarios-kpis').querySelector('[data-kpi="conflictos"]').textContent = mensajes.length;
}

function ocultarConflictos() {
    document.getElementById('dir-horarios-conflictos-box').classList.remove('show');
    document.getElementById('dir-horarios-resumen-ok').style.display = 'flex';
    document.getElementById('dir-horarios-kpis').querySelector('[data-kpi="conflictos"]').textContent = 0;
}

function guardarHorarioCompleto() {
    const filtros = filtrosActuales();

    fetch('/api/director/horarios', {
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
    const errorBox = document.getElementById('dir-horarios-form-error');
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
    fetch('/api/director/horarios/detectar-conflictos', {
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

    fetch('/api/director/horarios/limpiar', {
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
let modoGeneracionActual = 'ia';
let contextoDsiActual = null;
let esAlternativaActual = false;
let accionBotonReparar = 'reparar';

function abrirModalIa() {
    document.getElementById('dir-horarios-ia-modal')?.classList.add('show');
}

function cerrarModalIa() {
    document.getElementById('dir-horarios-ia-modal')?.classList.remove('show');
    idGeneracionIaActual = null;
    modoGeneracionActual = 'ia';
    contextoDsiActual = null;
    esAlternativaActual = false;
    accionBotonReparar = 'reparar';
}

/** Fase 3: exige programa y semestre especificos (nunca "Todos") antes de generar con el algoritmo determinista. */
function validarFiltrosDsi() {
    const filtros = filtrosActuales();
    const idPeriodo = idPeriodoSeleccionado();

    if (!filtros.id_programa) {
        mostrarConflictos(['Seleccione un programa específico (no "Todos los programas") antes de generar.']);

        return null;
    }
    if (!filtros.semestre) {
        mostrarConflictos(['Seleccione un semestre específico (no "Todos") antes de generar.']);

        return null;
    }
    if (!idPeriodo) {
        mostrarConflictos(['Seleccione un periodo académico antes de generar.']);

        return null;
    }
    return { id_programa: filtros.id_programa, id_periodo: idPeriodo, semestre: filtros.semestre };
}

function pintarResultadoIa(data) {
    const generacion = data.generacion ?? {};
    idGeneracionIaActual = generacion.id_generacion ?? null;

    document.getElementById('dir-horarios-ia-estado').textContent = `Estado: ${generacion.estado ?? '—'} · Proveedor: ${generacion.modelo ?? '—'}`;

    const errorBox = document.getElementById('dir-horarios-ia-error');
    if (!data.ok && (!generacion.resultado || (generacion.resultado.detalles ?? []).length === 0)) {
        errorBox.textContent = data.mensaje ?? 'No se pudo generar la propuesta.';
        errorBox.classList.add('show');
    } else {
        errorBox.classList.remove('show');
    }

    const detalles = generacion.resultado?.detalles ?? [];
    const tbody = document.getElementById('dir-horarios-ia-tbody');
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
    const observacionesBox = document.getElementById('dir-horarios-ia-observaciones-box');
    document.getElementById('dir-horarios-ia-observaciones').innerHTML = observaciones.map((o) => `<li>${o}</li>`).join('');
    observacionesBox.style.display = observaciones.length ? 'block' : 'none';

    const errores = generacion.errores ?? null;
    const mensajesConflicto = errores ? [...(errores.errores ?? []), ...(errores.conflictos ?? []).map((c) => c.mensaje ?? JSON.stringify(c))] : [];
    const conflictosBox = document.getElementById('dir-horarios-ia-conflictos-box');
    document.getElementById('dir-horarios-ia-conflictos').innerHTML = mensajesConflicto.map((m) => `<li>${m}</li>`).join('');
    conflictosBox.style.display = mensajesConflicto.length ? 'block' : 'none';

    const puedeAprobar = generacion.estado === 'BORRADOR' && mensajesConflicto.length === 0 && detalles.length > 0;
    document.getElementById('dir-horarios-ia-aprobar').style.display = generacion.estado === 'APROBADO' ? 'none' : '';
    document.getElementById('dir-horarios-ia-aprobar').disabled = !puedeAprobar;
    document.getElementById('dir-horarios-ia-reparar').style.display = mensajesConflicto.length > 0 ? '' : 'none';
    document.getElementById('dir-horarios-ia-descartar').style.display = generacion.estado === 'APROBADO' ? 'none' : '';
}

function llamarApiIa(url, method = 'POST') {
    return fetch(url, {
        method,
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
    }).then((res) => res.json());
}

/** Pinta el resultado de generarSemestreDsi/repararSemestreDsi: exito ya guardado, o BORRADOR reparable con conflictos. */
function pintarResultadoDsi(data) {
    modoGeneracionActual = 'dsi';
    idGeneracionIaActual = data.id_generacion ?? null;

    document.getElementById('dir-horarios-ia-titulo').textContent = `Generación de horario — Semestre ${data.semestre ?? ''}`;
    document.getElementById('dir-horarios-ia-estado').textContent = `Estado: ${data.estado ?? '—'} · Proveedor: local (determinista)`;

    const errorBox = document.getElementById('dir-horarios-ia-error');
    if (!data.ok) {
        errorBox.textContent = data.mensaje ?? 'No se pudo generar el horario.';
        errorBox.classList.add('show');
    } else {
        errorBox.classList.remove('show');
    }

    const horarios = data.horarios ?? [];
    const tbody = document.getElementById('dir-horarios-ia-tbody');
    tbody.innerHTML = horarios.length
        ? horarios.map((h) => `
            <tr>
                <td>${h.curso?.nombre_curso ?? h.id_curso}</td>
                <td>${h.docente ? `${h.docente.usuario?.nombres ?? ''} ${h.docente.usuario?.apellidos ?? ''}` : h.id_docente}</td>
                <td>${h.aula ?? '—'}</td>
                <td>${h.dia}</td>
                <td>${(h.hora_inicio ?? '').slice(0, 5)}</td>
                <td>${(h.hora_fin ?? '').slice(0, 5)}</td>
            </tr>
        `).join('')
        : '<tr><td colspan="6" class="coord-portafolio-empty">Sin bloques.</td></tr>';

    const resumen = data.resumen ?? null;
    const observacionesBox = document.getElementById('dir-horarios-ia-observaciones-box');
    if (resumen) {
        document.getElementById('dir-horarios-ia-observaciones').innerHTML = `
            <li>Cursos cubiertos: ${resumen.cursos ?? '—'}</li>
            <li>Bloques requeridos: ${resumen.bloques_requeridos ?? '—'}</li>
            <li>Bloques generados: ${resumen.bloques_generados ?? '—'}</li>
            <li>Docentes usados: ${resumen.docentes_usados ?? '—'}</li>
            <li>Aulas usadas: ${resumen.aulas_usadas ?? '—'}</li>
        `;
        observacionesBox.style.display = 'block';
    } else {
        observacionesBox.style.display = 'none';
    }

    const conflictos = data.conflictos ?? [];
    const listaConflictos = conflictos.slice(0, 20).map((c) => (typeof c === 'string' ? c : `${c.nombre_curso ?? `Curso #${c.id_curso}`}: ${c.horas_sin_ubicar} hora(s) sin ubicar`));
    if (conflictos.length > 20) listaConflictos.push(`Se encontraron ${conflictos.length} conflictos. Mostrando los primeros 20.`);
    const conflictosBox = document.getElementById('dir-horarios-ia-conflictos-box');
    document.getElementById('dir-horarios-ia-conflictos').innerHTML = listaConflictos.map((m) => `<li>${m}</li>`).join('');
    conflictosBox.style.display = conflictos.length ? 'block' : 'none';

    const botonAprobar = document.getElementById('dir-horarios-ia-aprobar');
    const botonReparar = document.getElementById('dir-horarios-ia-reparar');
    const botonDescartar = document.getElementById('dir-horarios-ia-descartar');

    esAlternativaActual = data.ok && data.estado === 'BORRADOR' && !!data.id_generacion;

    if (data.estado === 'HORARIO_EXISTENTE') {
        // Ya hay horario real para este semestre: en vez de un error simple, se ofrece generar una alternativa.
        accionBotonReparar = 'nueva_propuesta';
        botonAprobar.style.display = 'none';
        botonDescartar.style.display = 'none';
        botonReparar.style.display = '';
        botonReparar.innerHTML = '<i class="bi bi-shuffle"></i> Generar nueva propuesta';
    } else if (esAlternativaActual) {
        // Propuesta alternativa valida y lista para revisar: aprobar reemplaza el horario real de este semestre.
        accionBotonReparar = 'reparar';
        botonReparar.style.display = 'none';
        botonDescartar.style.display = '';
        botonAprobar.style.display = '';
        botonAprobar.disabled = false;
        botonAprobar.innerHTML = '<i class="bi bi-check2-circle"></i> Aprobar y reemplazar horario actual';
    } else {
        // DSI normal: o genera ya validado (GENERADO), o guarda BORRADOR solo reparable.
        accionBotonReparar = 'reparar';
        botonAprobar.style.display = 'none';
        botonReparar.innerHTML = '<i class="bi bi-wrench-adjustable"></i> Reparar';
        botonReparar.style.display = !data.ok && data.id_generacion ? '' : 'none';
        botonDescartar.style.display = !data.ok && data.id_generacion ? '' : 'none';
    }
}

function generarSemestreDsiUI(opciones = {}) {
    const contexto = validarFiltrosDsi();
    if (!contexto) return;

    contexto.modo = opciones.modo ?? 'normal';
    contextoDsiActual = contexto;
    modoGeneracionActual = 'dsi';

    abrirModalIa();
    document.getElementById('dir-horarios-ia-titulo').textContent = `Generación de horario — Semestre ${contexto.semestre}`;
    document.getElementById('dir-horarios-ia-estado').textContent = contexto.modo === 'nueva_propuesta' ? 'Generando nueva propuesta…' : 'Generando propuesta…';
    document.getElementById('dir-horarios-ia-tbody').innerHTML = '<tr><td colspan="6" class="coord-portafolio-empty">Generando…</td></tr>';

    fetch('/api/director/horarios/dsi/generar-semestre', {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(contexto),
    })
        .then((res) => res.json())
        .then((data) => {
            pintarResultadoDsi(data);
            if (data.estado === 'GENERADO') cargarHorario();
        })
        .catch(() => {
            document.getElementById('dir-horarios-ia-error').textContent = 'No se pudo contactar al servidor.';
            document.getElementById('dir-horarios-ia-error').classList.add('show');
        });
}

function repararSemestreDsiUI() {
    if (!contextoDsiActual) return;

    document.getElementById('dir-horarios-ia-estado').textContent = 'Reparando…';

    fetch('/api/director/horarios/dsi/reparar-semestre', {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(contextoDsiActual),
    })
        .then((res) => res.json())
        .then((data) => {
            pintarResultadoDsi(data);
            if (data.ok) cargarHorario();
        });
}

function pintarResultadoDsiMultiple(data) {
    modoGeneracionActual = 'dsi-multiple';
    idGeneracionIaActual = null;

    document.getElementById('dir-horarios-ia-titulo').textContent = 'Generación de horario — Semestres pendientes (II, IV, V, VI)';
    document.getElementById('dir-horarios-ia-estado').textContent = `Estado: ${data.estado ?? '—'}`;
    document.getElementById('dir-horarios-ia-error').classList.remove('show');
    document.getElementById('dir-horarios-ia-tbody').innerHTML = '<tr><td colspan="6" class="coord-portafolio-empty">Ver resumen por semestre abajo.</td></tr>';

    const resumen = data.resumen_global ?? {};
    const detalleSemestres = (data.resultados ?? [])
        .map((r) => `<li>Semestre ${r.semestre}: ${r.estado}${r.ok ? ` (${r.resumen?.bloques_generados ?? 0} bloques)` : ` — ${r.mensaje ?? ''}`}</li>`)
        .join('');

    document.getElementById('dir-horarios-ia-observaciones').innerHTML = `
        ${detalleSemestres}
        <li>Semestres generados: ${(resumen.semestres_generados ?? []).join(', ') || '—'}</li>
        <li>Semestres protegidos (no tocados): ${(resumen.semestres_no_tocados ?? []).join(', ') || '—'}</li>
        <li>Total de bloques generados: ${resumen.bloques_generados ?? 0}</li>
    `;
    document.getElementById('dir-horarios-ia-observaciones-box').style.display = 'block';
    document.getElementById('dir-horarios-ia-conflictos-box').style.display = 'none';
    document.getElementById('dir-horarios-ia-aprobar').style.display = 'none';
    document.getElementById('dir-horarios-ia-reparar').style.display = 'none';
    document.getElementById('dir-horarios-ia-descartar').style.display = 'none';
}

function generarTodosSemestresDsiUI() {
    const filtros = filtrosActuales();
    const idPeriodo = idPeriodoSeleccionado();

    if (!filtros.id_programa) {
        mostrarConflictos(['Seleccione un programa específico (no "Todos los programas") antes de generar.']);

        return;
    }
    if (!idPeriodo) {
        mostrarConflictos(['Seleccione un periodo académico antes de generar.']);

        return;
    }

    modoGeneracionActual = 'dsi-multiple';
    abrirModalIa();
    document.getElementById('dir-horarios-ia-titulo').textContent = 'Generación de horario — Semestres pendientes (II, IV, V, VI)';
    document.getElementById('dir-horarios-ia-estado').textContent = 'Generando propuestas…';
    document.getElementById('dir-horarios-ia-tbody').innerHTML = '<tr><td colspan="6" class="coord-portafolio-empty">Generando…</td></tr>';

    fetch('/api/director/horarios/dsi/generar-pendientes', {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ id_programa: filtros.id_programa, id_periodo: idPeriodo }),
    })
        .then((res) => res.json())
        .then((data) => {
            pintarResultadoDsiMultiple(data);
            cargarHorario();
        })
        .catch(() => {
            document.getElementById('dir-horarios-ia-error').textContent = 'No se pudo contactar al servidor.';
            document.getElementById('dir-horarios-ia-error').classList.add('show');
        });
}

/** Reparar puede venir del flujo generico (Gemini/Grok), del determinista DSI, o (repurposed) pedir una nueva propuesta. */
function repararClick() {
    if (modoGeneracionActual === 'dsi' && accionBotonReparar === 'nueva_propuesta') {
        generarSemestreDsiUI({ modo: 'nueva_propuesta' });
    } else if (modoGeneracionActual === 'dsi') {
        repararSemestreDsiUI();
    } else {
        repararGeneracionIa();
    }
}

/** Aprobar puede venir del flujo generico (Gemini/Grok) o de una propuesta alternativa DSI que reemplaza el semestre actual. */
function aprobarClick() {
    if (modoGeneracionActual === 'dsi' && esAlternativaActual) {
        aprobarPropuestaAlternativaUI();
    } else {
        aprobarGeneracionIa();
    }
}

function aprobarPropuestaAlternativaUI() {
    if (!idGeneracionIaActual) return;
    if (!confirm('Se reemplazará el horario actual de este semestre con la nueva propuesta. No afecta otros semestres ni programas. ¿Continuar?')) return;

    llamarApiIa(`/api/director/horarios/ia/${idGeneracionIaActual}/aprobar`)
        .then((data) => {
            if (data.ok) {
                cerrarModalIa();
                cargarHorario();
            } else {
                document.getElementById('dir-horarios-ia-error').textContent = data.mensaje ?? 'No se pudo aprobar la propuesta.';
                document.getElementById('dir-horarios-ia-error').classList.add('show');
            }
        });
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
    document.getElementById('dir-horarios-ia-estado').textContent = 'Generando propuesta…';
    document.getElementById('dir-horarios-ia-tbody').innerHTML = '<tr><td colspan="6" class="coord-portafolio-empty">Generando…</td></tr>';

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
            document.getElementById('dir-horarios-ia-error').textContent = 'No se pudo contactar al servidor.';
            document.getElementById('dir-horarios-ia-error').classList.add('show');
        });
}

function aprobarGeneracionIa() {
    if (!idGeneracionIaActual) return;
    llamarApiIa(`/api/director/horarios/ia/${idGeneracionIaActual}/aprobar`)
        .then((data) => {
            pintarResultadoIa(data);
            if (data.ok) cargarHorario();
        });
}

function descartarGeneracionIa() {
    if (!idGeneracionIaActual) return;
    llamarApiIa(`/api/director/horarios/ia/${idGeneracionIaActual}/descartar`)
        .then(() => cerrarModalIa());
}

function repararGeneracionIa() {
    if (!idGeneracionIaActual) return;
    document.getElementById('dir-horarios-ia-estado').textContent = 'Reparando…';
    llamarApiIa(`/api/director/horarios/ia/${idGeneracionIaActual}/reparar`)
        .then(pintarResultadoIa);
}

export function initDirectorHorarios() {
    const tbody = document.getElementById('dir-horarios-tbody');
    if (!tbody) return;

    cargarHorario();
    wireDragTargets();

    document.getElementById('dir-horarios-filtro-docente')?.addEventListener('change', cargarHorario);
    document.getElementById('dir-horarios-filtro-semestre')?.addEventListener('change', cargarHorario);
    document.getElementById('dir-horarios-filtro-programa')?.addEventListener('change', () => {
        actualizarTituloPrograma();
        cargarHorario();
    });

    document.getElementById('dir-horarios-nuevo')?.addEventListener('click', () => abrirModal());
    document.getElementById('dir-horarios-guardar')?.addEventListener('click', guardarHorarioCompleto);
    document.getElementById('dir-horarios-modal-cerrar')?.addEventListener('click', cerrarModal);
    document.getElementById('dir-horarios-form')?.addEventListener('submit', enviarFormularioBloque);

    document.getElementById('dir-horarios-select-bloque')?.addEventListener('change', (e) => {
        const [inicio, fin] = e.target.value.split('|');
        const form = e.target.form;
        form.elements.hora_inicio.value = inicio ?? '';
        form.elements.hora_fin.value = fin ?? '';
    });

    document.getElementById('dir-horarios-detectar')?.addEventListener('click', detectarConflictos);
    document.getElementById('dir-horarios-limpiar')?.addEventListener('click', limpiarHorario);
    document.getElementById('dir-horarios-generar-semestre')?.addEventListener('click', generarSemestreDsiUI);
    document.getElementById('dir-horarios-generar-todos')?.addEventListener('click', generarTodosSemestresDsiUI);
    document.getElementById('dir-horarios-estado-generar')?.addEventListener('click', generarHorarioSemestreActual);

    document.getElementById('dir-horarios-ia-cerrar')?.addEventListener('click', cerrarModalIa);
    document.getElementById('dir-horarios-ia-aprobar')?.addEventListener('click', aprobarClick);
    document.getElementById('dir-horarios-ia-descartar')?.addEventListener('click', descartarGeneracionIa);
    document.getElementById('dir-horarios-ia-reparar')?.addEventListener('click', repararClick);

    actualizarTituloPrograma();
}

document.addEventListener('DOMContentLoaded', initDirectorHorarios);
