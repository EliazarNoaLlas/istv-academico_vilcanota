@extends('layouts.guest', ['title' => 'Iniciar sesión — ISTV Vilcanota'])

@section('content')
    <div class="auth-shell">
        @include('auth.partials.panel-visual')

        <div class="auth-panel-form">
            <div class="auth-form-wrap">
                @include('auth.partials.brand')

                <div class="auth-steps">
                    <span class="is-active">1. Credenciales</span>
                    <span>2. Verificación</span>
                </div>

                @if ($errors->any())
                    <div class="auth-alert is-error" role="status">
                        <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                @if (session('status'))
                    <div class="auth-alert is-success" role="status">
                        <i class="bi bi-check-circle" aria-hidden="true"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}">
                    @csrf

                    <div class="auth-field">
                        <label for="login-user">Usuario institucional</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="bi bi-person-fill"></i></span>
                            <input id="login-user" type="text" name="usuario" value="{{ old('usuario') }}"
                                   placeholder="Ingrese su DNI o usuario institucional" autocomplete="username"
                                   required autofocus>
                        </div>
                    </div>

                    <div class="auth-field">
                        <label for="login-pass">Contraseña</label>
                        <div class="auth-input-wrap" data-password-field>
                            <span class="auth-input-icon"><i class="bi bi-lock-fill"></i></span>
                            <input id="login-pass" type="password" name="password"
                                   placeholder="Ingrese su contraseña" autocomplete="current-password" required>
                            <button type="button" class="auth-input-toggle" data-password-toggle
                                    aria-label="Mostrar contraseña">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="auth-badge-2fa">
                        <span class="auth-badge-2fa-icon"><i class="bi bi-shield-check"></i></span>
                        <div>
                            <strong>Verificación en dos pasos</strong>
                            <p>Validamos credenciales y luego confirmamos un código temporal enviado a su correo.</p>
                        </div>
                    </div>

                    <button type="submit" class="c-btn c-btn-primary auth-submit">
                        <i class="bi bi-box-arrow-in-right"></i> Continuar
                    </button>
                </form>

                <p class="auth-foot-link" style="margin-top: 18px;">
                    ¿Olvidó su contraseña?
                    <a href="{{ route('password.solicitar') }}" class="auth-link-teal">Solicitar restablecimiento</a>
                </p>

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
