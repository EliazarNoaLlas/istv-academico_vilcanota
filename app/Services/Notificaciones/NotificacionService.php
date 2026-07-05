<?php

namespace App\Services\Notificaciones;

use App\Models\Notificacion;
use Illuminate\Database\Eloquent\Collection;

class NotificacionService
{
    public function paraUsuario(int $idUsuario, int $limite = 20): Collection
    {
        return Notificacion::query()
            ->where('id_usuario', $idUsuario)
            ->orderByDesc('fecha_creacion')
            ->limit($limite)
            ->get();
    }

    public function marcarLeida(Notificacion $notificacion): Notificacion
    {
        $notificacion->update(['leido' => true]);

        return $notificacion;
    }

    public function marcarTodasLeidas(int $idUsuario): int
    {
        return Notificacion::where('id_usuario', $idUsuario)
            ->where('leido', false)
            ->update(['leido' => true]);
    }
}
