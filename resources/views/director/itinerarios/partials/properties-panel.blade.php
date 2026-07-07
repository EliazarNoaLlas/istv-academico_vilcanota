<section class="dir-iti-panel-card" id="dir-iti-props">
    <div class="dir-iti-panel-header">
        <h4>Propiedades de la unidad didáctica</h4>
        <button type="button" class="dir-iti-icon-btn" data-iti-collapse="dir-iti-props-body" title="Colapsar">–</button>
    </div>
    <div class="dir-iti-panel-body" id="dir-iti-props-body">
        <p class="dir-iti-empty-hint" id="dir-iti-props-placeholder">
            Selecciona una fila de la tabla para editar sus propiedades aquí.
        </p>

        <form id="dir-iti-unidad-form" hidden>
            <div class="dir-iti-field">
                <label>Nombre</label>
                <input type="text" name="nombre" id="iti-unidad-nombre" maxlength="180" required>
            </div>

            <div class="dir-iti-field">
                <label>Módulo / bloque</label>
                <select name="id_bloque" id="iti-unidad-bloque">
                    @foreach ($bloques as $bloque)
                        <option value="{{ $bloque['id_bloque'] }}">{{ $bloque['nombre'] }} ({{ $bloque['tipo_bloque'] }})</option>
                    @endforeach
                </select>
            </div>

            <div class="dir-iti-field">
                <label>Código</label>
                <input type="text" name="codigo" id="iti-unidad-codigo" maxlength="50">
            </div>

            <div class="dir-iti-field">
                <label>Ciclo</label>
                <div class="dir-iti-ciclo-picker" id="iti-unidad-ciclos">
                    @foreach (['I', 'II', 'III', 'IV', 'V', 'VI'] as $ciclo)
                        <label>
                            <input type="radio" name="ciclo" value="{{ $ciclo }}"> {{ $ciclo }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="dir-iti-field-row">
                <div class="dir-iti-field">
                    <label>Teóricos</label>
                    <input type="number" name="horas_teoricas_semanales" id="iti-unidad-teoricas" min="0" max="20" required>
                </div>
                <div class="dir-iti-field">
                    <label>Prácticos</label>
                    <input type="number" name="horas_practicas_semanales" id="iti-unidad-practicas" min="0" max="20" required>
                </div>
            </div>

            <div class="dir-iti-field-row">
                <div class="dir-iti-field">
                    <label>Créditos (calc.)</label>
                    <input type="text" id="iti-calc-creditos" readonly>
                </div>
                <div class="dir-iti-field">
                    <label>Horas U.D. (calc.)</label>
                    <input type="text" id="iti-calc-horas-ud" readonly>
                </div>
            </div>

            <div class="dir-iti-field-row">
                <div class="dir-iti-field">
                    <label>Horas teoría (calc.)</label>
                    <input type="text" id="iti-calc-teoria" readonly>
                </div>
                <div class="dir-iti-field">
                    <label>Horas práctica (calc.)</label>
                    <input type="text" id="iti-calc-practica" readonly>
                </div>
            </div>

            <div class="dir-iti-field-row">
                <div class="dir-iti-field">
                    <label>Horas ciclo (calc.)</label>
                    <input type="text" id="iti-calc-horas-ciclo" readonly>
                </div>
                <div class="dir-iti-field">
                    <label>Estado</label>
                    <select name="estado" id="iti-unidad-estado">
                        <option value="ACTIVO">ACTIVO</option>
                        <option value="INACTIVO">INACTIVO</option>
                    </select>
                </div>
            </div>

            <div class="dir-iti-field">
                <label>Observación</label>
                <textarea name="observacion" id="iti-unidad-observacion" rows="2"></textarea>
            </div>

            <div class="dir-iti-panel-actions">
                <button type="button" class="dir-iti-btn outline" id="iti-unidad-cancelar">Cancelar</button>
                <button type="submit" class="dir-iti-btn primary" id="iti-unidad-guardar">Guardar fila</button>
            </div>
        </form>
    </div>
</section>
