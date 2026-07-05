<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Director\SolicitudPasswordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SolicitudPasswordController extends Controller
{
    public function __construct(private readonly SolicitudPasswordService $solicitudes) {}

    public function create(): View
    {
        return view('auth.solicitar-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'usuario' => ['required', 'string', 'max:150'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        $this->solicitudes->solicitar($datos['usuario'], $datos['motivo'] ?? null, $request->ip());

        // Mensaje generico: nunca revela si el usuario/correo existe realmente.
        return redirect()->route('login')->with('status',
            'Si el usuario existe, se notificó a Dirección Académica. Le contactarán con instrucciones para restablecer su contraseña.'
        );
    }
}
