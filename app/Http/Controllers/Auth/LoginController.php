<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Auth\OtpException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\LoginOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(private readonly LoginOtpService $otpService)
    {
    }

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'usuario' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $usuario = User::where('usuario', $credentials['usuario'])->first();

        if (! $usuario || ! Hash::check($credentials['password'], $usuario->password_hash)) {
            return back()
                ->withInput($request->only('usuario'))
                ->withErrors(['usuario' => 'Usuario o contraseña incorrectos.']);
        }

        if ($usuario->estado !== 'ACTIVO') {
            return back()
                ->withInput($request->only('usuario'))
                ->withErrors(['usuario' => 'Esta cuenta no esta activa. Contacte al administrador.']);
        }

        if ($usuario->hasVerifiedOtp()) {
            Auth::login($usuario);
            $request->session()->regenerate();
            $usuario->forceFill(['ultimo_acceso' => now()])->save();

            return redirect()->intended(self::rutaPorRol($usuario->rol?->codigo));
        }

        $this->otpService->generateForUser($usuario, $request);

        $request->session()->put('login_2fa', ['user_id' => $usuario->id_usuario]);

        return redirect()->route('login.verificar')
            ->with('status', 'Código enviado. Revise el correo institucional indicado.');
    }

    public function verificarForm(Request $request): View|RedirectResponse
    {
        $pendiente = $request->session()->get('login_2fa');

        if (! $pendiente) {
            return redirect()->route('login');
        }

        $usuario = User::find($pendiente['user_id']);

        if (! $usuario) {
            $request->session()->forget('login_2fa');

            return redirect()->route('login');
        }

        return view('auth.verificar', [
            'correoEnmascarado' => $this->otpService->maskEmail($usuario->correo),
        ]);
    }

    public function verificar(Request $request): RedirectResponse
    {
        $pendiente = $request->session()->get('login_2fa');

        if (! $pendiente) {
            return redirect()->route('login');
        }

        $request->validate([
            'codigo' => ['required', 'digits:6'],
        ]);

        $usuario = User::find($pendiente['user_id']);

        if (! $usuario || $usuario->estado !== 'ACTIVO') {
            $request->session()->forget('login_2fa');

            return redirect()->route('login')
                ->withErrors(['usuario' => 'Esta cuenta no esta activa. Contacte al administrador.']);
        }

        try {
            $this->otpService->verify($usuario, $request->input('codigo'));
        } catch (OtpException $e) {
            return back()->withErrors(['codigo' => $e->getMessage()]);
        }

        $request->session()->forget('login_2fa');

        $usuario->forceFill([
            'ultimo_acceso' => now(),
            'otp_verified_at' => now(),
            'otp_last_verified_ip' => $request->ip(),
            'otp_last_verified_user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ])->save();

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->intended(self::rutaPorRol($usuario->rol?->codigo));
    }

    public function reenviar(Request $request): RedirectResponse
    {
        $pendiente = $request->session()->get('login_2fa');

        if (! $pendiente) {
            return redirect()->route('login');
        }

        $usuario = User::find($pendiente['user_id']);

        if (! $usuario) {
            $request->session()->forget('login_2fa');

            return redirect()->route('login');
        }

        try {
            $this->otpService->resend($usuario, $request);
        } catch (OtpException $e) {
            return redirect()->route('login.verificar')->withErrors(['codigo' => $e->getMessage()]);
        }

        return redirect()->route('login.verificar')
            ->with('status', 'Se envió un nuevo código a su correo institucional.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public static function rutaPorRol(?string $codigoRol): string
    {
        return match ($codigoRol) {
            'director' => route('director.dashboard'),
            'jua' => route('jua.dashboard'),
            'coordinador' => route('coordinador.dashboard'),
            'docente' => route('docente.dashboard'),
            default => route('login'),
        };
    }
}
