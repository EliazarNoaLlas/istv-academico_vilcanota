/**
 * Calendario Académico (JUA): navegación por mes, filtros por tipo de evento
 * (solo visual, sobre los eventos ya cargados) y CRUD de eventos vía modal.
 */
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const NOMBRES_MES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
const ETIQUETA_TIPO = {
    EVALUACION: 'Evaluación',
    FERIADO: 'Feriado',
    PLAZO_ADMINISTRATIVO: 'Plazo administrativo',
    MATRICULA: 'Matrícula',
    REUNION_CAPACITACION: 'Reunión / Capacitación',
};
const COLOR_TIPO = {
    EVALUACION: 'blue',
    FERIADO: 'red',
    PLAZO_ADMINISTRATIVO: 'gold',
    MATRICULA: 'teal',
    REUNION_CAPACITACION: 'purple',
};

const hoy = new Date();
const estado = {
    anio: hoy.getFullYear(),
    mes: hoy.getMonth() + 1,
    eventos: [],
    tiposOcultos: new Set(),
    eventoActivo: null,
};

function formatearFechaCorta(fechaIso) {
    const [anio, mes, dia] = fechaIso.split('-').map(Number);
    const fecha = new Date(anio, mes - 1, dia);

    return { dia: String(fecha.getDate()).padStart(2, '0'), mes: NOMBRES_MES[fecha.getMonth()].slice(0, 3).toUpperCase() };
}

function renderKpis(kpis) {
    document.querySelector('[data-kpi="eventos_semestre"]').textContent = kpis.eventos_semestre;
    document.querySelector('[data-kpi="evaluaciones"]').textContent = kpis.evaluaciones;
    document.querySelector('[data-kpi="plazos_administrativos"]').textContent = kpis.plazos_administrativos;
    document.querySelector('[data-kpi="proximos_7_dias"]').textContent = kpis.proximos_7_dias;
}

function renderProximos(proximos) {
    const root = document.getElementById('jua-cal-proximos');

    root.innerHTML = proximos.length
        ? proximos.map((e) => {
            const { dia, mes } = formatearFechaCorta(e.fecha);

            return `
                <button type="button" class="jua-cal-proximo-item" data-id-evento="${e.id_evento}">
                    <div class="jua-cal-proximo-fecha ${COLOR_TIPO[e.tipo]}">
                        <strong>${dia}</strong>
                        <span>${mes}</span>
                    </div>
                    <div class="jua-cal-proximo-info">
                        <strong>${e.titulo}</strong>
                        <span class="jua-cal-tag ${COLOR_TIPO[e.tipo]}">${ETIQUETA_TIPO[e.tipo]}</span>
                    </div>
                </button>
            `;
        }).join('')
        : '<p class="coord-portafolio-empty">No hay eventos próximos.</p>';

    root.querySelectorAll('[data-id-evento]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const evento = estado.eventos.find((e) => e.id_evento === Number(btn.dataset.idEvento))
                ?? proximos.find((e) => e.id_evento === Number(btn.dataset.idEvento));
            if (evento) abrirModal(evento);
        });
    });
}

function renderGrid() {
    const grid = document.getElementById('jua-cal-grid');
    const { anio, mes } = estado;

    document.getElementById('jua-cal-mes-titulo').textContent = `${NOMBRES_MES[mes - 1]} ${anio}`;

    const primerDia = new Date(anio, mes - 1, 1);
    const ultimoDia = new Date(anio, mes, 0);
    const inicioGrid = new Date(primerDia);
    inicioGrid.setDate(inicioGrid.getDate() - primerDia.getDay());
    const finGrid = new Date(ultimoDia);
    finGrid.setDate(finGrid.getDate() + (6 - ultimoDia.getDay()));

    const eventosPorDia = {};
    estado.eventos
        .filter((e) => !estado.tiposOcultos.has(e.tipo))
        .forEach((e) => {
            (eventosPorDia[e.fecha] ??= []).push(e);
        });

    const hoyIso = new Date().toISOString().slice(0, 10);
    const celdas = [];
    const cursor = new Date(inicioGrid);

    while (cursor <= finGrid) {
        const iso = `${cursor.getFullYear()}-${String(cursor.getMonth() + 1).padStart(2, '0')}-${String(cursor.getDate()).padStart(2, '0')}`;
        const fueraDeMes = cursor.getMonth() + 1 !== mes;
        const eventosDia = eventosPorDia[iso] ?? [];

        celdas.push(`
            <div class="jua-cal-celda ${fueraDeMes ? 'fuera' : ''} ${iso === hoyIso ? 'hoy' : ''}" data-fecha="${iso}">
                <span class="jua-cal-celda-num">${cursor.getDate()}</span>
                <div class="jua-cal-celda-eventos">
                    ${eventosDia.map((e) => `<div class="jua-cal-badge ${COLOR_TIPO[e.tipo]}" data-id-evento="${e.id_evento}">${e.titulo}</div>`).join('')}
                </div>
            </div>
        `);

        cursor.setDate(cursor.getDate() + 1);
    }

    grid.innerHTML = celdas.join('');

    grid.querySelectorAll('[data-id-evento]').forEach((el) => {
        el.addEventListener('click', (ev) => {
            ev.stopPropagation();
            const evento = estado.eventos.find((e) => e.id_evento === Number(el.dataset.idEvento));
            if (evento) abrirModal(evento);
        });
    });

    grid.querySelectorAll('.jua-cal-celda').forEach((celda) => {
        celda.addEventListener('click', () => abrirModal(null, celda.dataset.fecha));
    });
}

function cargarMes() {
    fetch(`/jua/calendario-academico/eventos?anio=${estado.anio}&mes=${estado.mes}`, { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => {
            estado.eventos = data.eventos ?? [];
            renderKpis(data.kpis);
            renderProximos(data.proximos ?? []);
            renderGrid();
        })
        .catch((error) => console.error(error));
}

function cambiarMes(delta) {
    let mes = estado.mes + delta;
    let anio = estado.anio;
    if (mes < 1) { mes = 12; anio -= 1; }
    if (mes > 12) { mes = 1; anio += 1; }
    estado.anio = anio;
    estado.mes = mes;
    cargarMes();
}

function wireLeyenda() {
    document.querySelectorAll('.jua-cal-chip').forEach((chip) => {
        chip.addEventListener('click', () => {
            const tipo = chip.dataset.tipo;
            chip.classList.toggle('active');
            if (estado.tiposOcultos.has(tipo)) {
                estado.tiposOcultos.delete(tipo);
            } else {
                estado.tiposOcultos.add(tipo);
            }
            renderGrid();
        });
    });
}

function abrirModal(evento, fechaSugerida) {
    estado.eventoActivo = evento;
    const errorBox = document.getElementById('jua-cal-form-error');
    errorBox.textContent = '';

    document.getElementById('jua-cal-modal-titulo').textContent = evento ? 'Editar evento' : 'Nuevo evento';
    document.getElementById('jua-cal-form-titulo').value = evento?.titulo ?? '';
    document.getElementById('jua-cal-form-tipo').value = evento?.tipo ?? 'REUNION_CAPACITACION';
    document.getElementById('jua-cal-form-fecha').value = evento?.fecha ?? fechaSugerida ?? new Date().toISOString().slice(0, 10);
    document.getElementById('jua-cal-form-descripcion').value = evento?.descripcion ?? '';
    document.getElementById('jua-cal-eliminar').style.display = evento ? 'inline-flex' : 'none';

    document.getElementById('jua-cal-modal').classList.add('show');
}

function cerrarModal() {
    document.getElementById('jua-cal-modal').classList.remove('show');
    estado.eventoActivo = null;
}

function guardarEvento() {
    const errorBox = document.getElementById('jua-cal-form-error');
    errorBox.textContent = '';

    const cuerpo = {
        titulo: document.getElementById('jua-cal-form-titulo').value.trim(),
        tipo: document.getElementById('jua-cal-form-tipo').value,
        fecha: document.getElementById('jua-cal-form-fecha').value,
        descripcion: document.getElementById('jua-cal-form-descripcion').value.trim() || null,
    };

    const editando = estado.eventoActivo;
    const url = editando ? `/jua/calendario-academico/eventos/${editando.id_evento}` : '/jua/calendario-academico/eventos';

    fetch(url, {
        method: editando ? 'PUT' : 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(cuerpo),
    })
        .then(async (res) => {
            const body = await res.json();
            if (!res.ok) throw body;

            return body;
        })
        .then(() => {
            cerrarModal();
            cargarMes();
        })
        .catch((error) => {
            errorBox.textContent = error?.errors ? Object.values(error.errors).flat().join(' ') : 'No se pudo guardar el evento.';
        });
}

function eliminarEvento() {
    if (!estado.eventoActivo) return;
    if (!confirm('¿Eliminar este evento del calendario?')) return;

    fetch(`/jua/calendario-academico/eventos/${estado.eventoActivo.id_evento}`, {
        method: 'DELETE',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
        .then((res) => res.json())
        .then(() => {
            cerrarModal();
            cargarMes();
        })
        .catch(() => { document.getElementById('jua-cal-form-error').textContent = 'No se pudo eliminar el evento.'; });
}

export function initJuaCalendario() {
    const grid = document.getElementById('jua-cal-grid');
    if (!grid) return;

    cargarMes();
    wireLeyenda();

    document.getElementById('jua-cal-mes-anterior').addEventListener('click', () => cambiarMes(-1));
    document.getElementById('jua-cal-mes-siguiente').addEventListener('click', () => cambiarMes(1));
    document.getElementById('jua-cal-hoy').addEventListener('click', () => {
        estado.anio = hoy.getFullYear();
        estado.mes = hoy.getMonth() + 1;
        cargarMes();
    });

    document.getElementById('jua-cal-nuevo-evento').addEventListener('click', () => abrirModal(null));
    document.getElementById('jua-cal-cancelar').addEventListener('click', cerrarModal);
    document.getElementById('jua-cal-guardar').addEventListener('click', guardarEvento);
    document.getElementById('jua-cal-eliminar').addEventListener('click', eliminarEvento);
}

document.addEventListener('DOMContentLoaded', initJuaCalendario);
