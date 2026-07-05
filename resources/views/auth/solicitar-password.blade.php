@extends('layouts.guest', ['title' => 'Solicitar restablecimiento — ISTV Vilcanota'])

@section('content')
    <div class="auth-shell">
        @include('auth.partials.panel-visual')

        <div class="auth-panel-form">
            <div class="auth-form-wrap">
                @include('auth.partials.brand')

                <div style="text-align: center; margin-bottom: 18px;">
                    <div class="auth-brand-logo" style="width: 48px; height: 48px; margin: 0 auto 12px; color: #0a3d62;">
                        <i class="bi bi-key" style="font-size: 20px;"></i>
                    </div>
                    <h2 style="font-size: 16px; font-weight: 800; color: var(--navy);">Solicitar restablecimiento</h2>
                    <p style="margin-top: 8px; font-size: 12px; color: var(--text-muted); line-height: 1.6;">
                        Su solicitud será enviada a Dirección Académica, quien la revisará y le hará
                        llegar una contraseña temporal al correo institucional registrado.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="auth-alert is-error" role="status">
                        <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.solicitar.store') }}">
                    @csrf

                    <div class="auth-field">
                        <label for="solicitud-usuario">Usuario o correo institucional</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="bi bi-person-fill"></i></span>
                            <input id="solicitud-usuario" type="text" name="usuario" value="{{ old('usuario') }}"
                                   placeholder="Ingrese su usuario o correo" required autofocus>
                        </div>
                    </div>

                    <div class="auth-field">
                        <label for="solicitud-motivo">Motivo (opcional)</label>
                        <div class="auth-input-wrap">
                            <span class="auth-input-icon"><i class="bi bi-chat-left-text"></i></span>
                            <input id="solicitud-motivo" type="text" name="motivo" value="{{ old('motivo') }}"
                                   placeholder="Ej. olvidé mi contraseña">
                        </div>
                    </div>

                    <button type="submit" class="c-btn c-btn-primary auth-submit">
                        <i class="bi bi-send"></i> Enviar solicitud
                    </button>

                    <div class="auth-links-row" style="margin-top: 14px;">
                        <a href="{{ route('login') }}" class="auth-link-muted">Volver al inicio de sesión</a>
                    </div>
                </form>

                <p class="auth-footer-note">Sistema Académico ISTV Vilcanota · Acceso institucional</p>
            </div>
        </div>
    </div>
@endsection
