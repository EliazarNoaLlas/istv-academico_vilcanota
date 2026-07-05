@extends('layouts.guest', ['title' => 'Cambio de contraseña obligatorio — ISTV Vilcanota'])

@section('content')
    <div class="auth-shell">
        @include('auth.partials.panel-visual')

        <div class="auth-panel-form">
            <div class="auth-form-wrap">
                @include('auth.partials.brand')

                <div style="text-align: center; margin-bottom: 18px;">
                    <div class="auth-brand-logo" style="width: 48px; height: 48px; margin: 0 auto 12px; color: #0a3d62;">
                        <i class="bi bi-shield-lock" style="font-size: 20px;"></i>
                    </div>
                    <h2 style="font-size: 16px; font-weight: 800; color: var(--navy);">Debe cambiar su contraseña</h2>
                    <p style="margin-top: 8px; font-size: 12px; color: var(--text-muted); line-height: 1.6;">
                        Por seguridad, Dirección Académica requiere que establezca una nueva contraseña
                        antes de continuar.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="auth-alert is-error" role="status">
                        <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.cambiar.store') }}">
                    @csrf

                    <div class="auth-field">
                        <label for="cambiar-password">Nueva contraseña</label>
                        <div class="auth-input-wrap" data-password-field>
                            <span class="auth-input-icon"><i class="bi bi-lock-fill"></i></span>
                            <input id="cambiar-password" type="password" name="password"
                                   placeholder="Mínimo 8 caracteres" autocomplete="new-password" required>
                            <button type="button" class="auth-input-toggle" data-password-toggle aria-label="Mostrar contraseña">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="auth-field">
                        <label for="cambiar-password-confirmation">Confirmar nueva contraseña</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="bi bi-lock-fill"></i></span>
                            <input id="cambiar-password-confirmation" type="password" name="password_confirmation"
                                   placeholder="Repita la contraseña" autocomplete="new-password" required>
                        </div>
                    </div>

                    <button type="submit" class="c-btn c-btn-primary auth-submit">
                        <i class="bi bi-check-lg"></i> Guardar y continuar
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" style="text-align: center; margin-top: 14px;">
                    @csrf
                    <button type="submit" class="auth-link-muted" style="background: none; border: none; font-size: 12px; cursor: pointer;">
                        Cerrar sesión
                    </button>
                </form>

                <p class="auth-footer-note">Sistema Académico ISTV Vilcanota · Acceso institucional</p>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-password-field]').forEach(function (wrap) {
            var toggle = wrap.querySelector('[data-password-toggle]');
            var input = wrap.querySelector('input');
            toggle.addEventListener('click', function () {
                var isPassword = input.getAttribute('type') === 'password';
                input.setAttribute('type', isPassword ? 'text' : 'password');
                toggle.querySelector('i').className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        });
    </script>
@endsection
