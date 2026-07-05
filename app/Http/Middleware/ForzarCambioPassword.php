<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForzarCambioPassword
{
    /**
     * Si el usuario autenticado tiene cambio_password_obligatorio, lo confina
     * a la pantalla de cambio de contraseña hasta que la complete. No aplica
     * a rutas guest (login) ni a la propia ruta de cambio/logout, para evitar
     * un bucle de redirecciones.
     */
    private const RUTAS_PERMITIDAS = ['password.cambiar', 'password.cambiar.store', 'logout'];

    public function handle(Request $request, Closure $next): Response
    {
        $usuario = Auth::user();

        if ($usuario && $usuario->debeCambiarPassword() && ! in_array($request->route()?->getName(), self::RUTAS_PERMITIDAS, true)) {
            return redirect()->route('password.cambiar');
        }

        return $next($request);
    }
}
