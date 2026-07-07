/* Módulo Itinerarios Formativos (director): dashboard + editor de malla oficial. */

const SEMANAS_TEORIA = 16;
const SEMANAS_PRACTICA = 32;
const CICLOS = ['I', 'II', 'III', 'IV', 'V', 'VI'];

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/* ---------- Fórmulas (mismo cálculo que ItinerarioCalculoService) ---------- */
function calcularCampos(teoricas, practicas) {
    const t = Number(teoricas) || 0;
    const p = Number(practicas) || 0;
    const totalTeoria = t * SEMANAS_TEORIA;
    const totalPractica = p * SEMANAS_PRACTICA;

    return {
        horas_ciclo: t + p * 2,
        creditos: t + p,
        total_horas_teoria: totalTeoria,
        total_horas_practica: totalPractica,
        horas_ud: totalTeoria + totalPractica,
    };
}

/* ---------- Toast ---------- */
let toastTimer = null;
function showToast(mensaje, esAdvertencia = false) {
    const toast = document.getElementById('dir-iti-toast');
    if (!toast) return;
    toast.textContent = mensaje;
    toast.classList.toggle('warn', esAdvertencia);
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 3200);
}

function escapeHtml(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}

/* ---------- Paneles colapsables ---------- */
function initCollapsibles() {
    document.querySelectorAll('[data-iti-collapse]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const cuerpo = document.getElementById(btn.dataset.itiCollapse);
            if (!cuerpo) return;
            const oculto = cuerpo.style.display === 'none';
            cuerpo.style.display = oculto ? '' : 'none';
            btn.textContent = oculto ? '–' : '+';
        });
    });
}

/* ---------- Index: modal + filtros ---------- */
function initIndex() {
    const modal = document.getElementById('dir-iti-modal');

    document.querySelectorAll('[data-iti-modal-open]').forEach((btn) => {
        btn.addEventListener('click', () => modal?.classList.add('is-open'));
    });
    modal?.querySelectorAll('[data-iti-modal-close]').forEach((btn) => {
        btn.addEventListener('click', () => modal.classList.remove('is-open'));
    });
    modal?.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.remove('is-open');
    });

    const filtros = document.getElementById('dir-iti-filtros');
    if (filtros) {
        filtros.querySelectorAll('select').forEach((sel) => {
            sel.addEventListener('change', () => filtros.submit());
        });
        let debounce;
        filtros.querySelector('input[name="q"]')?.addEventListener('input', () => {
            clearTimeout(debounce);
            debounce = setTimeout(() => filtros.submit(), 500);
        });
    }
}

/* ---------- Editor ---------- */
function initEditor() {
    const root = document.getElementById('dir-iti-editor');
    if (!root) return;

    const tabla = document.getElementById('dir-iti-editor-table');
    const form = document.getElementById('dir-iti-unidad-form');
    const placeholder = document.getElementById('dir-iti-props-placeholder');
    const statusPill = document.getElementById('dir-iti-status-pill');
    const ultimaModificacion = document.getElementById('dir-iti-ultima-modificacion');
    let filaSeleccionada = null;
    let hayCambiosPendientes = false;

    const campos = {
        nombre: document.getElementById('iti-unidad-nombre'),
        codigo: document.getElementById('iti-unidad-codigo'),
        teoricas: document.getElementById('iti-unidad-teoricas'),
        practicas: document.getElementById('iti-unidad-practicas'),
        bloque: document.getElementById('iti-unidad-bloque'),
        estado: document.getElementById('iti-unidad-estado'),
        observacion: document.getElementById('iti-unidad-observacion'),
    };

    function cicloSeleccionado() {
        return document.querySelector('#iti-unidad-ciclos input:checked')?.value ?? 'I';
    }

    function marcarCiclo(ciclo) {
        document.querySelectorAll('#iti-unidad-ciclos label').forEach((label) => {
            const radio = label.querySelector('input');
            const activo = radio.value === ciclo;
            radio.checked = activo;
            label.classList.toggle('checked', activo);
        });
    }

    function estadoGuardado(estado, texto) {
        if (statusPill) {
            statusPill.textContent = texto;
            statusPill.className = 'dir-iti-status-pill'
                + (estado === 'guardado' ? ' saved' : '')
                + (estado === 'error' ? ' error' : '');
        }
        hayCambiosPendientes = estado === 'pendiente';
    }

    function refrescarCalculos() {
        const calc = calcularCampos(campos.teoricas?.value, campos.practicas?.value);
        document.getElementById('iti-calc-horas-ciclo').value = calc.horas_ciclo;
        document.getElementById('iti-calc-creditos').value = calc.creditos;
        document.getElementById('iti-calc-teoria').value = calc.total_horas_teoria;
        document.getElementById('iti-calc-practica').value = calc.total_horas_practica;
        document.getElementById('iti-calc-horas-ud').value = calc.horas_ud;
    }

    function seleccionarFila(fila) {
        filaSeleccionada?.classList.remove('is-selected');
        filaSeleccionada = fila;
        fila.classList.add('is-selected');

        placeholder.hidden = true;
        form.hidden = false;

        campos.nombre.value = fila.dataset.nombre ?? '';
        campos.codigo.value = fila.dataset.codigo ?? '';
        campos.teoricas.value = fila.dataset.teoricas ?? 0;
        campos.practicas.value = fila.dataset.practicas ?? 0;
        campos.bloque.value = fila.dataset.bloque ?? '';
        campos.estado.value = fila.dataset.estado ?? 'ACTIVO';
        campos.observacion.value = fila.dataset.observacion ?? '';
        marcarCiclo(fila.dataset.ciclo ?? 'I');
        refrescarCalculos();
    }

    /* Selección de filas */
    tabla?.querySelectorAll('.dir-iti-row-unidad[data-unidad-id]').forEach((fila) => {
        fila.addEventListener('click', () => seleccionarFila(fila));
        fila.addEventListener('keydown', (e) => {
            if ((e.key === 'Enter' || e.key === ' ') && !e.target.closest('.dir-iti-editable')) {
                e.preventDefault();
                seleccionarFila(fila);
            }
        });
    });

    /* Chips de ciclo */
    document.querySelectorAll('#iti-unidad-ciclos input').forEach((radio) => {
        radio.addEventListener('change', () => {
            marcarCiclo(radio.value);
            estadoGuardado('pendiente', 'Cambios sin guardar');
        });
    });

    [campos.teoricas, campos.practicas].forEach((input) => {
        input?.addEventListener('input', () => {
            refrescarCalculos();
            estadoGuardado('pendiente', 'Cambios sin guardar');
        });
    });
    [campos.nombre, campos.codigo, campos.bloque, campos.estado, campos.observacion].forEach((input) => {
        input?.addEventListener('input', () => estadoGuardado('pendiente', 'Cambios sin guardar'));
    });

    document.getElementById('iti-unidad-cancelar')?.addEventListener('click', () => {
        if (filaSeleccionada) seleccionarFila(filaSeleccionada); // restaura valores del dataset
        estadoGuardado('guardado', 'Sin cambios pendientes');
    });

    /* ---- Actualización del DOM tras guardar ---- */
    function actualizarFila(fila, unidad) {
        fila.dataset.nombre = unidad.nombre ?? '';
        fila.dataset.codigo = unidad.codigo ?? '';
        fila.dataset.ciclo = unidad.ciclo;
        fila.dataset.teoricas = unidad.horas_teoricas_semanales;
        fila.dataset.practicas = unidad.horas_practicas_semanales;
        fila.dataset.estado = unidad.estado;
        fila.dataset.observacion = unidad.observacion ?? '';

        const nombreSpan = fila.querySelector('.dir-iti-cell-nombre .dir-iti-editable');
        if (nombreSpan) nombreSpan.textContent = unidad.nombre;

        CICLOS.forEach((ciclo) => {
            const celda = fila.querySelector(`[data-col="ciclo-${ciclo}"]`);
            if (!celda) return;
            const activo = unidad.ciclo === ciclo;
            celda.textContent = activo ? unidad.horas_ciclo : '—';
            celda.classList.toggle('is-activo', activo);
            celda.classList.toggle('is-vacio', !activo);
        });

        const setSpan = (col, valor) => {
            const celda = fila.querySelector(`[data-col="${col}"]`);
            if (!celda) return;
            const span = celda.querySelector('.dir-iti-editable');
            (span ?? celda).textContent = valor;
        };
        setSpan('teoricas', unidad.horas_teoricas_semanales);
        setSpan('practicas', unidad.horas_practicas_semanales);
        setSpan('creditos', unidad.creditos);
        setSpan('total-teoria', unidad.total_horas_teoria);
        setSpan('total-practica', unidad.total_horas_practica);
        setSpan('horas-ud', unidad.horas_ud);
    }

    function recalcularSumasModulo(idModulo) {
        const filaTotales = tabla?.querySelector(`[data-modulo-total="${idModulo}"]`);
        if (!filaTotales) return;

        const sumas = { teoricas: 0, practicas: 0, creditos: 0, teoria: 0, practica: 0, ud: 0 };
        tabla.querySelectorAll(`.dir-iti-row-unidad[data-modulo="${idModulo}"]`).forEach((fila) => {
            const leer = (col) => {
                const celda = fila.querySelector(`[data-col="${col}"]`);
                return Number((celda?.textContent ?? '').trim()) || 0;
            };
            sumas.teoricas += leer('teoricas');
            sumas.practicas += leer('practicas');
            sumas.creditos += leer('creditos');
            sumas.teoria += leer('total-teoria');
            sumas.practica += leer('total-practica');
            sumas.ud += leer('horas-ud');
        });

        Object.entries(sumas).forEach(([clave, valor]) => {
            const celda = filaTotales.querySelector(`[data-sum="${clave}"]`);
            if (celda) celda.textContent = valor;
        });
    }

    function actualizarTotales(totales) {
        if (!totales) return;

        (totales.modulos ?? []).forEach((modulo) => {
            const filaModulo = tabla?.querySelector(`[data-modulo-total="${modulo.id_modulo}"]`);
            if (filaModulo) {
                filaModulo.querySelector('[data-total="creditos"]').textContent = modulo.total_creditos;
                filaModulo.querySelector('[data-total="horas"]').textContent = modulo.total_horas;
            }
            (modulo.bloques ?? []).forEach((bloque) => {
                const celdaCred = tabla?.querySelector(`[data-bloque-cred="${bloque.id_bloque}"]`);
                const celdaHoras = tabla?.querySelector(`[data-bloque-horas="${bloque.id_bloque}"]`);
                if (celdaCred) celdaCred.textContent = bloque.creditos_bloque;
                if (celdaHoras) celdaHoras.textContent = bloque.horas_bloque;
            });
        });

        const filaGeneral = tabla?.querySelector('[data-itinerario-total]');
        if (filaGeneral) {
            filaGeneral.querySelector('[data-total="creditos"]').textContent = totales.total_creditos;
            filaGeneral.querySelector('[data-total="horas"]').textContent = totales.total_horas;
        }
    }

    /* ---- Panel de validación ---- */
    function renderValidaciones(validaciones) {
        const lista = document.getElementById('dir-iti-validaciones-lista');
        const contador = document.getElementById('dir-iti-validaciones-count');
        if (!lista) return;

        if (contador) {
            contador.textContent = validaciones.length;
            contador.className = `dir-iti-badge ${validaciones.length ? 'is-borrador' : 'is-activo'}`;
        }

        if (!validaciones.length) {
            lista.innerHTML = '<div class="dir-iti-valid-ok">✅ Todo cuadra: los créditos y horas registrados coinciden con la suma real de las unidades didácticas.</div>';
            return;
        }

        lista.innerHTML = validaciones.map((v) => {
            const comparacion = v.comparacion
                ? `<div class="dir-iti-compare-row">
                        <div class="dir-iti-compare-box">
                            <div class="dir-iti-compare-label">${escapeHtml(v.comparacion.etiqueta)} calculado</div>
                            <div class="dir-iti-compare-value">${escapeHtml(String(v.comparacion.calculado))}</div>
                        </div>
                        <div class="dir-iti-compare-neq">≠</div>
                        <div class="dir-iti-compare-box">
                            <div class="dir-iti-compare-label">${escapeHtml(v.comparacion.etiqueta)} registrado</div>
                            <div class="dir-iti-compare-value">${escapeHtml(String(v.comparacion.registrado))}</div>
                        </div>
                    </div>`
                : `<p class="dir-iti-valid-detalle">${escapeHtml(v.detalle)}</p>`;

            const goto = v.id_bloque
                ? `<button type="button" class="dir-iti-btn outline dir-iti-goto-btn" data-goto-bloque="${Number(v.id_bloque)}">Ir al bloque</button>`
                : '';

            return `<div class="dir-iti-valid-item ${v.nivel === 'ERROR' ? 'is-error' : 'is-warning'}">
                <div class="dir-iti-valid-title">${v.nivel === 'ERROR' ? '⚠' : 'ℹ'} ${escapeHtml(v.titulo)}</div>
                <div class="dir-iti-valid-ambito">${escapeHtml(v.ambito)}</div>
                ${comparacion}
                <div class="dir-iti-valid-recomendacion"><strong>Recomendación:</strong> ${escapeHtml(v.recomendacion)}</div>
                ${goto}
            </div>`;
        }).join('');
    }

    /* "Ir al bloque" (delegación: sirve para render inicial Blade y re-render JS) */
    document.getElementById('dir-iti-validaciones-lista')?.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-goto-bloque]');
        if (!btn) return;
        const fila = tabla?.querySelector(`.dir-iti-row-unidad[data-bloque="${btn.dataset.gotoBloque}"]`);
        if (fila) {
            fila.scrollIntoView({ behavior: 'smooth', block: 'center' });
            seleccionarFila(fila);
        }
    });

    /* ---- Guardado (PATCH real) ---- */
    function guardarUnidad(fila, payload) {
        const url = root.dataset.unidadUrl.replace('__UNIDAD__', fila.dataset.unidadId);
        const bloqueOriginal = fila.dataset.bloque;
        estadoGuardado('guardando', 'Guardando…');

        return fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf(),
            },
            body: JSON.stringify(payload),
        })
            .then(async (res) => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    const detalle = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message ?? 'Error al guardar.');
                    throw new Error(detalle);
                }
                return data;
            })
            .then((data) => {
                if (payload.id_bloque && String(payload.id_bloque) !== String(bloqueOriginal)) {
                    // La unidad cambió de bloque: recargar para reubicar la fila y los rowspan.
                    window.location.reload();
                    return data;
                }

                actualizarFila(fila, data.unidad);
                actualizarTotales(data.totales);
                recalcularSumasModulo(fila.dataset.modulo);
                renderValidaciones(data.validaciones ?? []);
                estadoGuardado('guardado', 'Sin cambios pendientes');
                if (ultimaModificacion) {
                    ultimaModificacion.textContent = 'Última modificación: ' + new Date().toLocaleString('es-PE');
                }
                showToast(data.message ?? 'Unidad didáctica actualizada correctamente');
                if (filaSeleccionada === fila) seleccionarFila(fila);
                return data;
            })
            .catch((error) => {
                estadoGuardado('error', 'Error al guardar');
                showToast(error.message, true);
                throw error;
            });
    }

    function payloadDesdePanel() {
        return {
            nombre: campos.nombre.value.trim(),
            codigo: campos.codigo.value || null,
            ciclo: cicloSeleccionado(),
            horas_teoricas_semanales: Math.max(0, Number(campos.teoricas.value) || 0),
            horas_practicas_semanales: Math.max(0, Number(campos.practicas.value) || 0),
            id_bloque: campos.bloque.value ? Number(campos.bloque.value) : null,
            observacion: campos.observacion.value || null,
            estado: campos.estado.value,
        };
    }

    form?.addEventListener('submit', (e) => {
        e.preventDefault();
        if (!filaSeleccionada) return;

        const btn = document.getElementById('iti-unidad-guardar');
        btn.disabled = true;
        guardarUnidad(filaSeleccionada, payloadDesdePanel()).finally(() => { btn.disabled = false; });
    });

    /* ---- Edición inline en la tabla ---- */
    tabla?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && e.target.classList?.contains('dir-iti-editable')) {
            e.preventDefault();
            e.target.blur();
        }
    });

    tabla?.addEventListener('focusout', (e) => {
        const span = e.target;
        if (!span.classList?.contains('dir-iti-editable')) return;

        const fila = span.closest('.dir-iti-row-unidad[data-unidad-id]');
        if (!fila) return;

        const campo = span.dataset.field;
        const texto = span.textContent.trim();
        const valorOriginal = campo === 'nombre'
            ? fila.dataset.nombre
            : (campo === 'horas_teoricas_semanales' ? fila.dataset.teoricas : fila.dataset.practicas);

        let valorNuevo;
        if (campo === 'nombre') {
            valorNuevo = texto || valorOriginal;
        } else {
            const n = parseInt(texto, 10);
            valorNuevo = String(Number.isNaN(n) || n < 0 ? 0 : Math.min(n, 20));
        }

        if (String(valorNuevo) === String(valorOriginal)) {
            span.textContent = valorOriginal; // normaliza el texto mostrado
            return;
        }

        // Payload desde el estado guardado de la fila, sobrescribiendo el campo editado
        // y los otros spans visibles (por si se editaron sin blur intermedio).
        const leerSpan = (col, porDefecto) => {
            const s = fila.querySelector(`[data-col="${col}"] .dir-iti-editable`);
            const n = parseInt((s?.textContent ?? '').trim(), 10);
            return Number.isNaN(n) || n < 0 ? Number(porDefecto) || 0 : Math.min(n, 20);
        };

        const payload = {
            nombre: campo === 'nombre' ? valorNuevo : (fila.querySelector('.dir-iti-cell-nombre .dir-iti-editable')?.textContent.trim() || fila.dataset.nombre),
            codigo: fila.dataset.codigo || null,
            ciclo: fila.dataset.ciclo,
            horas_teoricas_semanales: campo === 'horas_teoricas_semanales' ? Number(valorNuevo) : leerSpan('teoricas', fila.dataset.teoricas),
            horas_practicas_semanales: campo === 'horas_practicas_semanales' ? Number(valorNuevo) : leerSpan('practicas', fila.dataset.practicas),
            observacion: fila.dataset.observacion || null,
            estado: fila.dataset.estado || 'ACTIVO',
        };

        guardarUnidad(fila, payload).catch(() => {
            // Restaura el valor original si el servidor rechazó el cambio.
            span.textContent = valorOriginal;
        });
    });

    /* ---- Validar totales ---- */
    document.getElementById('dir-iti-btn-validar')?.addEventListener('click', function () {
        this.disabled = true;
        fetch(root.dataset.validarUrl, {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
        })
            .then((res) => res.json())
            .then((data) => {
                const validaciones = data.validaciones ?? [];
                renderValidaciones(validaciones);
                showToast(
                    validaciones.length
                        ? `Validación completa: ${validaciones.length} inconsistencia(s) encontrada(s)`
                        : 'Validación completa: sin inconsistencias',
                    validaciones.length > 0,
                );
            })
            .catch(() => showToast('No se pudo ejecutar la validación de totales.', true))
            .finally(() => { this.disabled = false; });
    });

    /* ---- Recalcular totales ---- */
    document.getElementById('dir-iti-btn-recalcular')?.addEventListener('click', function () {
        if (!window.confirm('¿Recalcular todos los totales del itinerario a partir de las unidades didácticas?')) return;

        this.disabled = true;
        fetch(root.dataset.recalcularUrl, {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
        })
            .then((res) => res.json())
            .then((data) => {
                actualizarTotales(data.totales);
                renderValidaciones(data.validaciones ?? []);
                showToast(data.message ?? 'Totales recalculados correctamente');
            })
            .catch(() => showToast('No se pudo recalcular los totales.', true))
            .finally(() => { this.disabled = false; });
    });

    /* ---- Volver con cambios pendientes ---- */
    document.getElementById('dir-iti-btn-volver')?.addEventListener('click', (e) => {
        if (hayCambiosPendientes && !window.confirm('Tienes cambios sin guardar en el panel. ¿Deseas salir de todas formas?')) {
            e.preventDefault();
        }
    });

    window.addEventListener('beforeunload', (e) => {
        if (hayCambiosPendientes) e.preventDefault();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initIndex();
    initEditor();
    initCollapsibles();
});
