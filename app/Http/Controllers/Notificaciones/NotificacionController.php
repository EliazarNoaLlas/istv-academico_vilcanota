<?php

namespace App\Http\Controllers\Notificaciones;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use App\Services\Notificaciones\NotificacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function __construct(private NotificacionService $notificaciones) {}

    public function index(Request $request): JsonResponse
    {
        $notificaciones = $this->notificaciones->paraUsuario($request->user()->id_usuario);

        return response()->json(['ok' => true, 'notificaciones' => $notificaciones]);
    }

    public function marcarLeida(Request $request, Notificacion $notificacion): JsonResponse
    {
        abort_if($notificacion->id_usuario !== $request->user()->id_usuario, 403);

        $notificacion = $this->notificaciones->marcarLeida($notificacion);

        return response()->json(['ok' => true, 'notificacion' => $notificacion]);
    }

    public function marcarTodasLeidas(Request $request): JsonResponse
    {
        $actualizadas = $this->notificaciones->marcarTodasLeidas($request->user()->id_usuario);

        return response()->json(['ok' => true, 'actualizadas' => $actualizadas]);
    }
}
