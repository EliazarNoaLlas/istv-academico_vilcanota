<div class="dir-usuarios-modal-backdrop" id="dir-usuarios-modal">
    <div class="dir-usuarios-modal dir-usuarios-modal-lg">
        <div class="dir-usuarios-modal-head">
            <h2 id="dir-usuarios-modal-title">Nuevo usuario</h2>
            <p class="dir-usuarios-modal-subtitle">Complete los datos de la cuenta institucional. Los campos marcados con * son obligatorios.</p>
        </div>

        <div class="dir-usuarios-form-error" id="dir-usuarios-form-error"></div>

        <form id="dir-usuarios-form" novalidate>
            <div class="dir-usuarios-section">
                <div class="dir-usuarios-section-title"><i class="bi bi-person-badge"></i> Datos personales</div>
                <div class="dir-usuarios-modal-grid">
                    <div class="form-group">
                        <label>Nombres *</label>
                        <input type="text" name="nombres" required>
                        <small class="dir-usuarios-field-error" data-error-for="nombres"></small>
                    </div>
                    <div class="form-group">
                        <label>Apellidos</label>
                        <input type="text" name="apellidos">
                        <small class="dir-usuarios-field-error" data-error-for="apellidos"></small>
                    </div>
                    <div class="form-group">
                        <label>DNI</label>
                        <input type="text" name="dni" maxlength="8">
                        <small class="dir-usuarios-field-error" data-error-for="dni"></small>
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono">
                        <small class="dir-usuarios-field-error" data-error-for="telefono"></small>
                    </div>
                </div>
            </div>

            <div class="dir-usuarios-section">
                <div class="dir-usuarios-section-title"><i class="bi bi-shield-lock"></i> Cuenta y acceso</div>
                <div class="dir-usuarios-modal-grid">
                    <div class="form-group">
                        <label>Usuario (login) *</label>
                        <input type="text" name="usuario" required>
                        <small class="dir-usuarios-field-error" data-error-for="usuario"></small>
                    </div>
                    <div class="form-group">
                        <label>Correo institucional *</label>
                        <input type="email" name="correo" required>
                        <small class="dir-usuarios-field-error" data-error-for="correo"></small>
                    </div>
                    <div class="form-group">
                        <label>Rol *</label>
                        <select name="id_rol" id="dir-usuarios-select-rol" required>
                            <option value="">Seleccione rol</option>
                            @foreach ($rolesAsignables as $rol)
                                <option value="{{ $rol->id_rol }}" data-codigo="{{ $rol->codigo }}">{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                        <small class="dir-usuarios-hint">Por seguridad institucional, el rol Director no se asigna desde este módulo.</small>
                        <small class="dir-usuarios-field-error" data-error-for="id_rol"></small>
                    </div>
                    <div class="form-group" id="dir-usuarios-campo-estado">
                        <label>Estado</label>
                        <select name="estado">
                            <option value="ACTIVO">Activo</option>
                            <option value="INACTIVO">Inactivo</option>
                            <option value="BLOQUEADO">Bloqueado</option>
                        </select>
                        <small class="dir-usuarios-field-error" data-error-for="estado"></small>
                    </div>
                </div>
            </div>

            <div id="dir-usuarios-campo-coordinador" class="dir-usuarios-section dir-usuarios-docente-box" style="display:none">
                <div class="dir-usuarios-section-title"><i class="bi bi-diagram-3"></i> Programa a cargo</div>
                <div class="dir-usuarios-modal-grid">
                    <div class="form-group full">
                        <label>Programa de estudio *</label>
                        <select name="id_programa">
                            <option value="">Seleccione programa</option>
                            @foreach ($programas as $programa)
                                <option value="{{ $programa->id_programa }}">{{ $programa->nombre }}</option>
                            @endforeach
                        </select>
                        <small class="dir-usuarios-hint">El coordinador queda a cargo de este programa de estudio.</small>
                        <small class="dir-usuarios-field-error" data-error-for="id_programa"></small>
                    </div>
                </div>
            </div>

            <div id="dir-usuarios-campos-docente" class="dir-usuarios-section dir-usuarios-docente-box" style="display:none">
                <div class="dir-usuarios-section-title"><i class="bi bi-person-video3"></i> Perfil académico del docente</div>

                <div class="dir-usuarios-codigo-preview" id="dir-usuarios-codigo-preview">
                    <i class="bi bi-magic"></i> El código de docente se genera automáticamente (ej. DOC008) al guardar.
                </div>

                <div class="dir-usuarios-modal-grid">
                    <div class="form-group full">
                        <label>Especialidad</label>
                        <input type="text" name="especialidad" list="dir-usuarios-especialidades" placeholder="Escriba o elija una especialidad...">
                        <datalist id="dir-usuarios-especialidades">
                            @foreach ($especialidades as $especialidad)
                                <option value="{{ $especialidad }}"></option>
                            @endforeach
                        </datalist>
                        <small class="dir-usuarios-field-error" data-error-for="especialidad"></small>
                    </div>

                    <div class="form-group full">
                        <label>Tipo de docente</label>
                        <div class="dir-usuarios-tipo-docente">
                            <label class="dir-usuarios-radio-card">
                                <input type="radio" name="tipo_docente" value="ESPECIFICO" checked>
                                <div>
                                    <strong>Específico</strong>
                                    <span>Dicta en un solo programa de estudio.</span>
                                </div>
                            </label>
                            <label class="dir-usuarios-radio-card">
                                <input type="radio" name="tipo_docente" value="GENERAL">
                                <div>
                                    <strong>General</strong>
                                    <span>Puede dictar en varios programas (ej. Inglés, Comunicación).</span>
                                </div>
                            </label>
                        </div>
                        <small class="dir-usuarios-field-error" data-error-for="tipo_docente"></small>
                    </div>

                    <div class="form-group full">
                        <label id="dir-usuarios-programas-label">Programa asignado</label>
                        <div class="dir-usuarios-programas-lista" id="dir-usuarios-programas-lista">
                            @foreach ($programas as $programa)
                                <label class="dir-usuarios-programa-item">
                                    <span>{{ $programa->nombre }}</span>
                                    <input type="checkbox" name="programas[]" value="{{ $programa->id_programa }}">
                                </label>
                            @endforeach
                        </div>
                        <small class="dir-usuarios-hint" id="dir-usuarios-programas-hint">Seleccione un único programa (docente específico).</small>
                        <small class="dir-usuarios-field-error" data-error-for="programas"></small>
                    </div>
                </div>
            </div>

            <div class="dir-usuarios-modal-actions">
                <button type="button" class="c-btn c-btn-outline" id="dir-usuarios-modal-cerrar">Cancelar</button>
                <button type="submit" class="c-btn c-btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div class="dir-usuarios-modal-backdrop" id="dir-usuarios-motivo-modal">
    <div class="dir-usuarios-modal" style="width:420px">
        <h2 id="dir-usuarios-motivo-titulo">Cambiar estado</h2>
        <p style="font-size:12px;color:var(--text-muted);margin-bottom:14px">Indique el motivo del cambio. Queda registrado en la auditoría del sistema.</p>

        <div class="dir-usuarios-form-error" id="dir-usuarios-motivo-error"></div>

        <form id="dir-usuarios-motivo-form">
            <div class="form-group">
                <label>Motivo</label>
                <textarea name="motivo" rows="3" required minlength="5" maxlength="255" style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-family:inherit"></textarea>
            </div>
            <div class="dir-usuarios-modal-actions">
                <button type="button" class="c-btn c-btn-outline" id="dir-usuarios-motivo-cancelar">Cancelar</button>
                <button type="submit" class="c-btn c-btn-primary">Confirmar</button>
            </div>
        </form>
    </div>
</div>
