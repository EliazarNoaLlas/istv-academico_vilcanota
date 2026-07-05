<div class="dir-usuarios-modal-backdrop" id="dir-usuarios-modal">
    <div class="dir-usuarios-modal">
        <div class="dir-usuarios-modal-head">
            <div>
                <h2 id="dir-usuarios-modal-title">Nuevo usuario</h2>
                <p class="dir-usuarios-modal-subtitle">Complete los datos de la cuenta y, si corresponde, el perfil academico del docente.</p>
            </div>
        </div>

        <div class="dir-usuarios-form-error" id="dir-usuarios-form-error"></div>

        <form id="dir-usuarios-form" data-codigo-docente-sugerido="{{ $codigoDocenteSugerido }}">
            <div class="dir-usuarios-modal-grid">
                <div class="form-group">
                    <label for="dir-usuarios-nombres">Nombres</label>
                    <input type="text" name="nombres" id="dir-usuarios-nombres" required>
                    <small class="dir-usuarios-field-error" data-error-for="nombres"></small>
                </div>
                <div class="form-group">
                    <label for="dir-usuarios-apellidos">Apellidos</label>
                    <input type="text" name="apellidos" id="dir-usuarios-apellidos">
                    <small class="dir-usuarios-field-error" data-error-for="apellidos"></small>
                </div>
                <div class="form-group">
                    <label for="dir-usuarios-usuario">Usuario (login)</label>
                    <input type="text" name="usuario" id="dir-usuarios-usuario" required>
                    <small class="dir-usuarios-field-error" data-error-for="usuario"></small>
                </div>
                <div class="form-group">
                    <label for="dir-usuarios-correo">Correo institucional</label>
                    <input type="email" name="correo" id="dir-usuarios-correo" required>
                    <small class="dir-usuarios-field-error" data-error-for="correo"></small>
                </div>
                <div class="form-group">
                    <label for="dir-usuarios-select-rol">Rol</label>
                    <select name="id_rol" id="dir-usuarios-select-rol" required>
                        <option value="">Seleccione rol</option>
                        @foreach ($rolesAsignables as $rol)
                            <option value="{{ $rol->id_rol }}" data-codigo="{{ $rol->codigo }}">{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                    <small class="dir-usuarios-field-help">Por seguridad institucional, el rol Director no se asigna desde este modulo.</small>
                    <small class="dir-usuarios-field-error" data-error-for="id_rol"></small>
                </div>
                <div class="form-group">
                    <label for="dir-usuarios-dni">DNI</label>
                    <input type="text" name="dni" id="dir-usuarios-dni" maxlength="8" inputmode="numeric">
                    <small class="dir-usuarios-field-error" data-error-for="dni"></small>
                </div>
                <div class="form-group">
                    <label for="dir-usuarios-telefono">Telefono</label>
                    <input type="text" name="telefono" id="dir-usuarios-telefono">
                    <small class="dir-usuarios-field-error" data-error-for="telefono"></small>
                </div>
                <div class="form-group" id="dir-usuarios-campo-estado">
                    <label for="dir-usuarios-estado">Estado</label>
                    <select name="estado" id="dir-usuarios-estado">
                        <option value="ACTIVO">Activo</option>
                        <option value="INACTIVO">Inactivo</option>
                        <option value="BLOQUEADO">Bloqueado</option>
                    </select>
                    <small class="dir-usuarios-field-error" data-error-for="estado"></small>
                </div>
            </div>

            <div id="dir-usuarios-campos-docente" class="dir-usuarios-docente-box" style="display:none">
                <div class="dir-usuarios-docente-title">
                    <i class="bi bi-person-video3"></i>
                    Perfil academico del docente
                </div>

                <div class="dir-usuarios-docente-summary">
                    <div>
                        <span class="dir-usuarios-docente-label">Codigo docente</span>
                        <strong id="dir-usuarios-codigo-docente">doc001</strong>
                    </div>
                    <div class="dir-usuarios-docente-note">
                        Se asigna automaticamente al guardar. El patron sigue el formato <code>doc001</code>, <code>doc002</code> y asi sucesivamente.
                    </div>
                </div>

                <div class="dir-usuarios-modal-grid">
                    <div class="form-group">
                        <label for="dir-usuarios-especialidad">Especialidad</label>
                        <input
                            type="text"
                            name="especialidad"
                            id="dir-usuarios-especialidad"
                            list="dir-usuarios-lista-especialidades"
                            autocomplete="off"
                            placeholder="Escriba o seleccione una especialidad"
                        >
                        <datalist id="dir-usuarios-lista-especialidades">
                            @foreach ($especialidadesDocente as $especialidad)
                                <option value="{{ $especialidad }}"></option>
                            @endforeach
                        </datalist>
                        <small class="dir-usuarios-field-help">Puede escribir manualmente o elegir una sugerencia.</small>
                        <small class="dir-usuarios-field-error" data-error-for="especialidad"></small>
                    </div>
                    <div class="form-group">
                        <label for="dir-usuarios-tipo-docente">Tipo de docente</label>
                        <select name="tipo_docente" id="dir-usuarios-tipo-docente">
                            <option value="">Seleccione tipo</option>
                            <option value="ESPECIFICO">Especifico</option>
                            <option value="GENERAL">General</option>
                        </select>
                        <small class="dir-usuarios-field-help">General permite varios programas. Especifico solo uno.</small>
                        <small class="dir-usuarios-field-error" data-error-for="tipo_docente"></small>
                    </div>
                    <div class="form-group full">
                        <div class="dir-usuarios-programas-head">
                            <label for="dir-usuarios-programas-lista">Programas</label>
                            <span id="dir-usuarios-programas-ayuda" class="dir-usuarios-programas-ayuda">Seleccione al menos un programa.</span>
                        </div>

                        <div id="dir-usuarios-programas-lista" class="dir-usuarios-programas-lista">
                            @foreach ($programas as $programa)
                                <label class="dir-usuarios-programa-option">
                                    <span>{{ $programa->nombre }}</span>
                                    <input
                                        type="checkbox"
                                        name="programas[]"
                                        value="{{ $programa->id_programa }}"
                                        data-programa-checkbox
                                    >
                                </label>
                            @endforeach
                        </div>
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
        <p class="dir-usuarios-modal-subtitle">Indique el motivo del cambio. Queda registrado en la auditoria del sistema.</p>

        <div class="dir-usuarios-form-error" id="dir-usuarios-motivo-error"></div>

        <form id="dir-usuarios-motivo-form">
            <div class="form-group">
                <label for="dir-usuarios-motivo">Motivo</label>
                <textarea
                    name="motivo"
                    id="dir-usuarios-motivo"
                    rows="3"
                    required
                    minlength="5"
                    maxlength="255"
                    style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:var(--radius-sm);font-family:inherit"
                ></textarea>
            </div>
            <div class="dir-usuarios-modal-actions">
                <button type="button" class="c-btn c-btn-outline" id="dir-usuarios-motivo-cancelar">Cancelar</button>
                <button type="submit" class="c-btn c-btn-primary">Confirmar</button>
            </div>
        </form>
    </div>
</div>
