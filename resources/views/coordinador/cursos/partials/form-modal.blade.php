<div class="coord-cursos-modal-backdrop" id="coord-cursos-modal">
    <div class="coord-cursos-modal">
        <h2 id="coord-cursos-modal-title">Nuevo curso</h2>

        <div class="coord-cursos-form-error" id="coord-cursos-form-error"></div>

        <form id="coord-cursos-form">
            <div class="coord-cursos-modal-grid">
                <div class="form-group full">
                    <label>Nombre del curso</label>
                    <input type="text" name="nombre_curso" required>
                </div>

                <div class="form-group">
                    <label>Módulo</label>
                    <input type="text" name="modulo" required>
                </div>

                <div class="form-group">
                    <label>Semestre</label>
                    <select name="semestre" required>
                        @foreach (['I', 'II', 'III', 'IV', 'V', 'VI'] as $ciclo)
                            <option value="{{ $ciclo }}">{{ $ciclo }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Docente asignado</label>
                    <select name="id_docente">
                        <option value="">Sin asignar</option>
                        @foreach ($docentes as $docente)
                            <option value="{{ $docente->id_docente }}">
                                {{ $docente->usuario->nombres }} {{ $docente->usuario->apellidos }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Programa de estudio</label>
                    <select name="id_programa">
                        <option value="">Sin asignar</option>
                        @foreach ($programas as $programa)
                            <option value="{{ $programa->id_programa }}">{{ $programa->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group full">
                    <label>Estado</label>
                    <select name="estado" required>
                        <option value="ACTIVO">Activo</option>
                        <option value="INACTIVO">Inactivo</option>
                        <option value="ARCHIVADO">Archivado</option>
                    </select>
                </div>

                <div class="form-group"><label>Créditos</label><input type="number" name="creditos" min="0" required></div>
                <div class="form-group"><label>Horas UD</label><input type="number" name="horas_ud" min="0" required></div>
                <div class="form-group"><label>Horas teoría (semanal)</label><input type="number" name="horas_teoria" min="0" required></div>
                <div class="form-group"><label>Horas práctica (semanal)</label><input type="number" name="horas_practica" min="0" required></div>
                <div class="form-group"><label>Total teoría</label><input type="number" name="total_teoria" min="0" required></div>
                <div class="form-group"><label>Total práctica</label><input type="number" name="total_practica" min="0" required></div>
                <div class="form-group full"><label>Total horas</label><input type="number" name="total_horas" min="0" required></div>
            </div>

            <div class="coord-cursos-modal-actions">
                <button type="button" class="c-btn c-btn-outline" id="coord-cursos-modal-cerrar">Cancelar</button>
                <button type="submit" class="c-btn c-btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
