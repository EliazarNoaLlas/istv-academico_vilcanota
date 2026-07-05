<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Http\Requests\Director\RechazarSolicitudPasswordRequest;
use App\Http\Requests\Director\ResetUsuarioPasswordRequest;
use App\Http\Requests\Director\StoreUsuarioRequest;
use App\Http\Requests\Director\UpdateUsuarioEstadoRequest;
use App\Http\Requests\Director\UpdateUsuarioRequest;
use App\Models\ProgramaEstudio;
use App\Models\Role;
use App\Models\SolicitudPassword;
use App\Models\User;
use App\Services\Director\SolicitudPasswordService;
use App\Services\Director\UsuarioAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DirectorUsuarioController extends Controller
{
    public function __construct(
        private readonly UsuarioAdminService $usuarios,
        private readonly SolicitudPasswordService $solicitudes,
    ) {}

    public function page(): View
    {
        return view('director.usuarios.index', [
            'roles' => Role::orderBy('nombre')->get(),
            'rolesAsignables' => Role::whereIn('codigo', Role::CODIGOS_ASIGNABLES_POR_DIRECTOR)->orderBy('nombre')->get(),
            'programas' => ProgramaEstudio::orderBy('nombre')->get(),
            'especialidadesDocente' => $this->usuarios->catalogoEspecialidades(),
            'codigoDocenteSugerido' => $this->usuarios->siguienteCodigoDocente(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $usuarios = $this->usuarios->listar(
            $request->query('q'),
            $request->query('id_rol') ? (int) $request->query('id_rol') : null,
            $request->query('estado'),
        );

        return response()->json(['ok' => true, 'usuarios' => $usuarios]);
    }

    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        $usuario = $this->usuarios->crear($request->validated(), $request->user());

        return response()->json(['ok' => true, 'usuario' => $usuario], 201);
    }

    public function update(UpdateUsuarioRequest $request, User $usuario): JsonResponse
    {
        $usuario = $this->usuarios->actualizar($usuario, $request->validated(), $request->user());

        return response()->json(['ok' => true, 'usuario' => $usuario]);
    }

    public function updateEstado(UpdateUsuarioEstadoRequest $request, User $usuario): JsonResponse
    {
        $usuario = $this->usuarios->cambiarEstado(
            $usuario,
            $request->validated('estado'),
            $request->validated('motivo'),
            $request->user(),
        );

        return response()->json(['ok' => true, 'usuario' => $usuario]);
    }

    public function resetPassword(ResetUsuarioPasswordRequest $request, User $usuario): JsonResponse
    {
        $this->usuarios->resetPassword($usuario, $request->user());

        return response()->json(['ok' => true, 'mensaje' => 'Se envio una contrasena temporal al correo institucional del usuario.']);
    }

    public function solicitudesPassword(): JsonResponse
    {
        return response()->json(['ok' => true, 'solicitudes' => $this->solicitudes->pendientes()]);
    }

    public function aprobarSolicitudPassword(Request $request, SolicitudPassword $solicitud): JsonResponse
    {
        $solicitud = $this->solicitudes->aprobar($solicitud, $request->user());

        return response()->json(['ok' => true, 'solicitud' => $solicitud]);
    }

    public function rechazarSolicitudPassword(RechazarSolicitudPasswordRequest $request, SolicitudPassword $solicitud): JsonResponse
    {
        $solicitud = $this->solicitudes->rechazar($solicitud, $request->validated('motivo_rechazo'), $request->user());

        return response()->json(['ok' => true, 'solicitud' => $solicitud]);
    }
}
