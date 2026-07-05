@extends('layouts.guest', ['title' => 'Verificación — ISTV Vilcanota'])

@section('content')
    <div class="auth-shell">
        @include('auth.partials.panel-visual')

        <div class="auth-panel-form">
            <div class="auth-form-wrap">
                @include('auth.partials.brand')

                <div class="auth-steps">
                    <span>1. Credenciales</span>
                    <span class="is-active">2. Verificación</span>
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

                <div style="text-align: center; margin-bottom: 18px;">
                    <div class="auth-brand-logo" style="width: 48px; height: 48px; margin: 0 auto 12px; color: #0a3d62;">
                        <i class="bi bi-shield-check" style="font-size: 20px;"></i>
                    </div>
                    <h2 style="font-size: 16px; font-weight: 800; color: var(--navy);">Verifique su identidad</h2>
                    <p style="margin-top: 8px; font-size: 12px; color: var(--text-muted); line-height: 1.6;">
                        Enviamos un código temporal al correo institucional:
                        <strong style="display: block; margin-top: 4px; font-size: 14px; color: var(--text-primary);">
                            {{ $correoEnmascarado }}
                        </strong>
                    </p>
                </div>

                <form method="POST" action="{{ route('login.verificar.store') }}" id="form-verificar">
                    @csrf
                    <input type="hidden" name="codigo" id="codigo-final">

                    <label class="auth-code-label">Código de 6 dígitos</label>
                    <div class="auth-code-row" id="code-boxes">
                        @for ($i = 0; $i < 6; $i++)
                            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"
                                   aria-label="Dígito {{ $i + 1 }} del código">
                        @endfor
                    </div>

                    <button type="submit" class="c-btn c-btn-primary auth-submit" style="margin-top: 18px;">
                        Verificar e ingresar
                    </button>

                    <div class="auth-links-row" style="margin-top: 14px;">
                        <a href="{{ route('login') }}" class="auth-link-muted">Cambiar usuario</a>
                    </div>
                </form>

                <form method="POST" action="{{ route('login.reenviar') }}" style="text-align: center; margin-top: 6px;">
                    @csrf
                    <button type="submit" class="auth-link-teal" style="background: none; border: none; font-size: 12px; cursor: pointer;">
                        Reenviar código
                    </button>
                </form>

                <p class="auth-footer-note">Sistema Académico ISTV Vilcanota · Acceso institucional</p>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var boxes = Array.prototype.slice.call(document.querySelectorAll('#code-boxes input'));
            var hidden = document.getElementById('codigo-final');

            function sync() {
                hidden.value = boxes.map(function (b) { return b.value; }).join('');
            }

            boxes.forEach(function (box, index) {
                box.addEventListener('input', function () {
                    box.value = box.value.replace(/\D/g, '').slice(-1);
                    if (box.value && index < boxes.length - 1) {
                        boxes[index + 1].focus();
                    }
                    sync();
                });

                box.addEventListener('keydown', function (event) {
                    if (event.key !== 'Backspace') return;
                    if (!box.value && index > 0) {
                        boxes[index - 1].value = '';
                        boxes[index - 1].focus();
                        sync();
                    }
                });

                box.addEventListener('paste', function (event) {
                    event.preventDefault();
                    var texto = (event.clipboardData || window.clipboardData).getData('text').trim();
                    if (!/^\d{6}$/.test(texto)) return;
                    texto.split('').forEach(function (digito, i) {
                        if (boxes[i]) boxes[i].value = digito;
                    });
                    boxes[boxes.length - 1].focus();
                    sync();
                });
            });

            if (boxes[0]) boxes[0].focus();
        })();
    </script>
@endsection
