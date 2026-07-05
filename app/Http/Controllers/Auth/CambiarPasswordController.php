<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CambiarPasswordRequest;
use App\Models\AuditoriaSistema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CambiarPasswordController extends Controller
{
    public function create(): View
    {
        return view('auth.cambiar-password');
    }

    public function store(CambiarPasswordRequest $request): RedirectResponse
    {
        $usuario = $request->user();

        $usuario->forceFill([
            'password_hash' => Hash::make($request->validated('password')),
            'password_algoritmo' => 'bcrypt',
            'cambio_password_obligatorio' => false,
        ])->save();

        AuditoriaSistema::create([
            'id_usuario' => $usuario->id_usuario,
            'accion' => 'PASSWORD_CAMBIADA_POR_USUARIO',
            'tabla_afectada' => 'usuarios',
            'registro_id' => (string) $usuario->id_usuario,
            'detalle' => 'El usuario completó el cambio de contraseña obligatorio',
        ]);

        return redirect()->to(\App\Http\Controllers\Auth\LoginController::rutaPorRol($usuario->rol?->codigo))
            ->with('status', 'Contraseña actualizada correctamente.');
    }
}
