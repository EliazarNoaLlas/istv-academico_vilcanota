<div class="coord-horarios-modal-backdrop" id="dir-horarios-modal">
    <div class="coord-horarios-modal">
        <h2 id="dir-horarios-modal-title">Nuevo bloque</h2>

        <div class="coord-horarios-form-error" id="dir-horarios-form-error"></div>

        <form id="dir-horarios-form">
            <div class="form-group">
                <label>Curso</label>
                <select name="id_curso" required>
                    <option value="">Seleccione curso</option>
                    @foreach ($cursos as $curso)
                        <option value="{{ $curso->id_curso }}">{{ $curso->nombre_curso }} ({{ $curso->semestre }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Docente</label>
                <select name="id_docente" required>
                    <option value="">Seleccione docente</option>
                    @foreach ($docentes as $docente)
                        <option value="{{ $docente->id_docente }}">{{ $docente->usuario->nombres }} {{ $docente->usuario->apellidos }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Día</label>
                <select name="dia" required>
                    <option value="">Seleccione día</option>
                    @foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'] as $dia)
                        <option value="{{ $dia }}">{{ $dia }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Bloque horario</label>
                <select name="_bloque" id="dir-horarios-select-bloque" required>
                    <option value="">Seleccione bloque</option>
                    @foreach ($bloques_horario as $bloque)
                        @if (empty($bloque['receso']))
                            <option value="{{ $bloque['inicio'] }}|{{ $bloque['fin'] }}">{{ $bloque['inicio'] }} - {{ $bloque['fin'] }}</option>
                        @endif
                    @endforeach
                </select>
                <input type="hidden" name="hora_inicio">
                <input type="hidden" name="hora_fin">
            </div>
            <div class="form-group">
                <label>Aula</label>
                <select name="aula" required>
                    <option value="">Seleccione aula</option>
                    @foreach ($aulas as $aula)
                        <option value="{{ $aula->codigo }}">{{ $aula->codigo }} — {{ $aula->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="coord-horarios-modal-actions">
                <button type="button" class="c-btn c-btn-outline" id="dir-horarios-modal-cerrar">Cancelar</button>
                <button type="submit" class="c-btn c-btn-primary">Guardar bloque</button>
            </div>
        </form>
    </div>
</div>
