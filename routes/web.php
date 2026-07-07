<?php

use App\Http\Controllers\Academic\CursoController;
use App\Http\Controllers\Academic\DocenteController;
use App\Http\Controllers\Academic\EstudianteController;
use App\Http\Controllers\Academic\HorarioController;
use App\Http\Controllers\Academic\RiesgoAcademicoController;
use App\Http\Controllers\Auth\CambiarPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SolicitudPasswordController;
use App\Http\Controllers\Director\DirectorAlertaController;
use App\Http\Controllers\Director\DirectorAnalyticsController;
use App\Http\Controllers\Director\DirectorConfiguracionController;
use App\Http\Controllers\Director\DirectorCursoController;
use App\Http\Controllers\Director\DirectorDashboardController;
use App\Http\Controllers\Director\DirectorDocenteController;
use App\Http\Controllers\Director\DirectorEstudianteController;
use App\Http\Controllers\Director\DirectorHorarioController;
use App\Http\Controllers\Director\DirectorNotaController;
use App\Http\Controllers\Director\DirectorNotificacionController;
use App\Http\Controllers\Director\DirectorPortafolioController;
use App\Http\Controllers\Director\DirectorProgramaController;
use App\Http\Controllers\Director\DirectorReporteController;
use App\Http\Controllers\Director\DirectorUsuarioController;
use App\Http\Controllers\Jua\JuaDashboardController;
use App\Http\Controllers\Coordinador\CoordinadorConsolidadoController;
use App\Http\Controllers\Coordinador\CoordinadorCursoController;
use App\Http\Controllers\Coordinador\CoordinadorDashboardController;
use App\Http\Controllers\Coordinador\CoordinadorDataController;
use App\Http\Controllers\Coordinador\CoordinadorDocenteController;
use App\Http\Controllers\Coordinador\CoordinadorEstudianteController;
use App\Http\Controllers\Coordinador\CoordinadorHorarioController;
use App\Http\Controllers\Coordinador\CoordinadorNotaController;
use App\Http\Controllers\Coordinador\CoordinadorPortafolioController;
use App\Http\Controllers\Coordinador\CoordinadorValidacionController;
use App\Http\Controllers\Docente\DocenteAnaliticaController;
use App\Http\Controllers\Docente\DocenteAsistenciaController;
use App\Http\Controllers\Docente\DocenteCursoController;
use App\Http\Controllers\Docente\DocenteDashboardController;
use App\Http\Controllers\Docente\DocenteHorarioController;
use App\Http\Controllers\Docente\DocenteNotaController;
use App\Http\Controllers\Docente\DocentePortafolioController;
use App\Http\Controllers\Docente\DocenteSesionController;
use App\Http\Controllers\Notificaciones\NotificacionController;
use App\Http\Controllers\Portafolios\PortafolioDocumentoController;
use App\Http\Controllers\Portafolios\PortafolioIAController;
use App\Http\Controllers\Portafolios\PortafolioRevisionController;
use App\Http\Controllers\Portafolios\SilaboEstructuraController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->to(LoginController::rutaPorRol(Auth::user()->rol?->codigo));
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/login/verificar', [LoginController::class, 'verificarForm'])->name('login.verificar');
    Route::post('/login/verificar', [LoginController::class, 'verificar'])->middleware('throttle:10,1')->name('login.verificar.store');
    Route::post('/login/reenviar', [LoginController::class, 'reenviar'])->middleware('throttle:5,1')->name('login.reenviar');

    Route::get('/login/solicitar-password', [SolicitudPasswordController::class, 'create'])->name('password.solicitar');
    Route::post('/login/solicitar-password', [SolicitudPasswordController::class, 'store'])->middleware('throttle:5,1')->name('password.solicitar.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/cambiar-password', [CambiarPasswordController::class, 'create'])->name('password.cambiar');
    Route::post('/cambiar-password', [CambiarPasswordController::class, 'store'])->name('password.cambiar.store');
});

Route::middleware(['auth', 'role:director'])->prefix('director')->name('director.')->group(function () {
    Route::get('/dashboard', [DirectorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/usuarios', [DirectorUsuarioController::class, 'page'])->name('usuarios.index');
    Route::get('/docentes', [DirectorDocenteController::class, 'page'])->name('docentes.index');
    Route::get('/horarios', [DirectorHorarioController::class, 'page'])->name('horarios.index');
    Route::get('/estudiantes', [DirectorEstudianteController::class, 'page'])->name('estudiantes.index');
    Route::get('/cursos', [DirectorCursoController::class, 'page'])->name('cursos.index');
    Route::get('/configuracion', [DirectorConfiguracionController::class, 'page'])->name('configuracion.index');
    Route::get('/notas', [DirectorNotaController::class, 'page'])->name('notas.index');
    Route::get('/portafolio', [DirectorPortafolioController::class, 'page'])->name('portafolio.index');
    Route::get('/analytics', [DirectorAnalyticsController::class, 'page'])->name('analytics.index');
    Route::get('/alertas', [DirectorAlertaController::class, 'page'])->name('alertas.index');
    Route::get('/notificaciones', [DirectorNotificacionController::class, 'page'])->name('notificaciones.index');
    Route::get('/reportes', [DirectorReporteController::class, 'page'])->name('reportes.index');
});

Route::middleware(['auth', 'role:jua'])->prefix('jua')->name('jua.')->group(function () {
    Route::get('/dashboard', [JuaDashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:coordinador', 'coordinador.programa'])->prefix('coordinador')->name('coordinador.')->group(function () {
    Route::get('/dashboard', [CoordinadorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/cursos', [CoordinadorCursoController::class, 'page'])->name('cursos.index');
    Route::get('/horarios', [CoordinadorHorarioController::class, 'page'])->name('horarios.index');
    Route::get('/portafolio', [CoordinadorPortafolioController::class, 'page'])->name('portafolio.index');
    Route::get('/docentes', [CoordinadorDocenteController::class, 'page'])->name('docentes.index');
    Route::get('/estudiantes', [CoordinadorEstudianteController::class, 'page'])->name('estudiantes.index');
    Route::get('/notas', [CoordinadorNotaController::class, 'page'])->name('notas.index');
    Route::get('/consolidado', [CoordinadorConsolidadoController::class, 'page'])->name('consolidado.index');
    Route::get('/validaciones', [CoordinadorValidacionController::class, 'page'])->name('validaciones.index');
});

Route::middleware(['auth', 'role:docente'])->prefix('docente')->name('docente.')->group(function () {
    Route::get('/dashboard', [DocenteDashboardController::class, 'index'])->name('dashboard');
    Route::get('/cursos', [DocenteCursoController::class, 'page'])->name('cursos.index');
    Route::get('/horario', [DocenteHorarioController::class, 'page'])->name('horario.index');
    Route::get('/analitica', [DocenteAnaliticaController::class, 'page'])->name('analitica.index');
    Route::get('/notas', [DocenteNotaController::class, 'page'])->name('notas.index');
    Route::get('/asistencia', [DocenteAsistenciaController::class, 'page'])->name('asistencia.index');
    Route::get('/portafolio', [DocentePortafolioController::class, 'page'])->name('portafolio.index');
    Route::get('/sesiones', [DocenteSesionController::class, 'page'])->name('sesiones.index');
});

/*
|--------------------------------------------------------------------------
| Endpoints internos (JSON) - reemplazan a los PHP sueltos legacy
|--------------------------------------------------------------------------
| Se registran aqui (no en routes/api.php) para reutilizar la sesion de
| Laravel ya validada en la Fase 4, en vez de instalar Sanctum/tokens que
| esta fase no pidio.
*/
Route::middleware(['auth', 'role:coordinador', 'coordinador.programa'])->prefix('api/coordinador')->group(function () {
    Route::get('/data', [CoordinadorDataController::class, 'index']);
    Route::get('/cursos', [CoordinadorCursoController::class, 'index']);
    Route::post('/cursos', [CoordinadorCursoController::class, 'store']);
    Route::put('/cursos/{curso}', [CoordinadorCursoController::class, 'update']);
    Route::get('/horarios', [CoordinadorHorarioController::class, 'index']);
    Route::post('/horarios', [CoordinadorHorarioController::class, 'store']);
    Route::get('/horarios/catalogos', [CoordinadorHorarioController::class, 'catalogs']);
    Route::post('/horarios/detectar-conflictos', [CoordinadorHorarioController::class, 'detectConflicts']);
    Route::post('/horarios/limpiar', [CoordinadorHorarioController::class, 'clear']);
    Route::post('/horarios/generar-semestre', [CoordinadorHorarioController::class, 'generateSemester']);
    Route::post('/horarios/generar-todos', [CoordinadorHorarioController::class, 'generateAllSemesters']);
    Route::post('/horarios/ia/{idGeneracion}/aprobar', [CoordinadorHorarioController::class, 'aprobarGeneracionIa']);
    Route::post('/horarios/ia/{idGeneracion}/descartar', [CoordinadorHorarioController::class, 'descartarGeneracionIa']);
    Route::post('/horarios/ia/{idGeneracion}/reparar', [CoordinadorHorarioController::class, 'repararGeneracionIa']);
    Route::get('/horarios/ia/{idGeneracion}/estado', [CoordinadorHorarioController::class, 'estadoGeneracionIa']);
    Route::get('/portafolios', [CoordinadorPortafolioController::class, 'index']);
    Route::get('/portafolios/notas', [CoordinadorPortafolioController::class, 'estudiantesNotas']);
    Route::post('/portafolios/notas', [CoordinadorPortafolioController::class, 'guardarNota']);
    Route::get('/portafolios/asistencia/sesiones', [CoordinadorPortafolioController::class, 'sesionesAsistencia']);
    Route::post('/portafolios/asistencia/sesiones', [CoordinadorPortafolioController::class, 'crearSesionAsistencia']);
    Route::get('/portafolios/asistencia', [CoordinadorPortafolioController::class, 'estudiantesAsistencia']);
    Route::post('/portafolios/asistencia', [CoordinadorPortafolioController::class, 'guardarAsistencia']);
    Route::get('/sesiones', [DocenteSesionController::class, 'index']);
    Route::post('/sesiones', [DocenteSesionController::class, 'store']);
    Route::delete('/sesiones/{sesion}', [DocenteSesionController::class, 'destroy']);
    Route::get('/docentes', [CoordinadorDocenteController::class, 'index']);
    Route::get('/estudiantes', [CoordinadorEstudianteController::class, 'index']);
    Route::get('/notas', [CoordinadorNotaController::class, 'index']);
    Route::get('/consolidado', [CoordinadorConsolidadoController::class, 'index']);
    Route::get('/validaciones', [CoordinadorValidacionController::class, 'index']);
});

Route::middleware(['auth', 'role:director'])->prefix('api/director')->group(function () {
    Route::get('/dashboard', [DirectorDashboardController::class, 'data']);

    Route::get('/usuarios', [DirectorUsuarioController::class, 'index']);
    Route::post('/usuarios', [DirectorUsuarioController::class, 'store']);
    Route::put('/usuarios/{usuario}', [DirectorUsuarioController::class, 'update']);
    Route::patch('/usuarios/{usuario}/estado', [DirectorUsuarioController::class, 'updateEstado']);
    Route::post('/usuarios/{usuario}/reset-password', [DirectorUsuarioController::class, 'resetPassword']);
    Route::delete('/usuarios/{usuario}', [DirectorUsuarioController::class, 'destroy']);

    Route::get('/usuarios-solicitudes-password', [DirectorUsuarioController::class, 'solicitudesPassword']);
    Route::patch('/usuarios-solicitudes-password/{solicitud}/aprobar', [DirectorUsuarioController::class, 'aprobarSolicitudPassword']);
    Route::patch('/usuarios-solicitudes-password/{solicitud}/rechazar', [DirectorUsuarioController::class, 'rechazarSolicitudPassword']);

    Route::get('/docentes', [DirectorDocenteController::class, 'index']);
    Route::get('/programas', [DirectorProgramaController::class, 'index']);

    Route::get('/horarios', [DirectorHorarioController::class, 'index']);
    Route::post('/horarios', [DirectorHorarioController::class, 'store']);
    Route::get('/horarios/catalogos', [DirectorHorarioController::class, 'catalogs']);
    Route::post('/horarios/detectar-conflictos', [DirectorHorarioController::class, 'detectConflicts']);
    Route::post('/horarios/limpiar', [DirectorHorarioController::class, 'clear']);
    Route::post('/horarios/generar-semestre', [DirectorHorarioController::class, 'generateSemester']);
    Route::post('/horarios/generar-todos', [DirectorHorarioController::class, 'generateAllSemesters']);
    Route::post('/horarios/ia/{idGeneracion}/aprobar', [DirectorHorarioController::class, 'aprobarGeneracionIa']);
    Route::post('/horarios/ia/{idGeneracion}/descartar', [DirectorHorarioController::class, 'descartarGeneracionIa']);
    Route::post('/horarios/ia/{idGeneracion}/reparar', [DirectorHorarioController::class, 'repararGeneracionIa']);
    Route::get('/horarios/ia/{idGeneracion}/estado', [DirectorHorarioController::class, 'estadoGeneracionIa']);

    Route::get('/estudiantes', [DirectorEstudianteController::class, 'index']);
    Route::post('/estudiantes', [DirectorEstudianteController::class, 'store']);
    Route::get('/cursos', [DirectorCursoController::class, 'index']);

    Route::get('/configuracion', [DirectorConfiguracionController::class, 'index']);
    Route::put('/configuracion', [DirectorConfiguracionController::class, 'update']);

    Route::get('/notas', [DirectorNotaController::class, 'index']);
    Route::get('/portafolio', [DirectorPortafolioController::class, 'index']);

    Route::get('/analytics', [DirectorAnalyticsController::class, 'data']);

    Route::get('/alertas', [DirectorAlertaController::class, 'index']);
    Route::patch('/alertas/{alerta}/gestionar', [DirectorAlertaController::class, 'gestionar']);

    Route::get('/reportes', [DirectorReporteController::class, 'index']);
    Route::post('/reportes/generar', [DirectorReporteController::class, 'generar']);
    Route::get('/reportes/{reporte}/descargar', [DirectorReporteController::class, 'descargar']);
});

Route::middleware(['auth', 'role:coordinador,director,jua'])->prefix('api')->group(function () {
    Route::get('/riesgo-academico', [RiesgoAcademicoController::class, 'index']);
    Route::get('/academic/cursos', [CursoController::class, 'index']);
    Route::get('/academic/docentes', [DocenteController::class, 'index']);
    Route::get('/academic/estudiantes', [EstudianteController::class, 'index']);
    Route::get('/academic/horarios', [HorarioController::class, 'index']);
});

Route::middleware(['auth', 'role:director,jua,coordinador,docente'])->prefix('api')->group(function () {
    Route::get('/notificaciones', [NotificacionController::class, 'index']);
    Route::patch('/notificaciones/{notificacion}/leer', [NotificacionController::class, 'marcarLeida']);
    Route::post('/notificaciones/marcar-todas-leidas', [NotificacionController::class, 'marcarTodasLeidas']);
});

Route::middleware(['auth', 'role:coordinador,docente', 'coordinador.programa'])->prefix('api/portafolios')->group(function () {
    Route::get('/documentos', [PortafolioDocumentoController::class, 'index']);
    Route::post('/documentos', [PortafolioDocumentoController::class, 'store']);
    Route::delete('/documentos/{documento}', [PortafolioDocumentoController::class, 'destroy']);
    Route::get('/silabo-estructura', [SilaboEstructuraController::class, 'index']);
    Route::post('/{documento}/analizar', [PortafolioIAController::class, 'analizar']);
});

Route::middleware(['auth', 'role:coordinador', 'coordinador.programa'])->prefix('api/portafolios')->group(function () {
    Route::post('/{documento}/validar', [PortafolioRevisionController::class, 'validar']);
});

Route::middleware(['auth', 'role:docente'])->prefix('api/docente')->group(function () {
    Route::get('/dashboard', [DocenteDashboardController::class, 'data']);
    Route::get('/cursos', [DocenteCursoController::class, 'index']);
    Route::get('/horario', [DocenteHorarioController::class, 'data']);
    Route::get('/notas', [DocenteNotaController::class, 'data']);
    Route::post('/notas/guardar', [DocenteNotaController::class, 'guardar']);
    Route::post('/notas/cerrar-acta', [DocenteNotaController::class, 'cerrarActa']);
    Route::get('/asistencia', [DocenteAsistenciaController::class, 'data']);
    Route::post('/asistencia/guardar', [DocenteAsistenciaController::class, 'guardar']);
    Route::get('/portafolio', [DocentePortafolioController::class, 'index']);
    Route::post('/portafolio', [DocentePortafolioController::class, 'store']);
    Route::get('/sesiones', [DocenteSesionController::class, 'index']);
    Route::post('/sesiones', [DocenteSesionController::class, 'store']);
    Route::delete('/sesiones/{sesion}', [DocenteSesionController::class, 'destroy']);
});
