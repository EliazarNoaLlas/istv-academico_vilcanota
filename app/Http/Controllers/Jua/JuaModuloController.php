<?php

namespace App\Http\Controllers\Jua;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Modulos del menu de JUA que todavia no tienen pantalla propia: una sola vista "en construccion" reutilizable. */
class JuaModuloController extends Controller
{
    private const MODULOS = [
        'jua.consolidados.index' => ['titulo' => 'Consolidados', 'icono' => 'bi-file-earmark-bar-graph', 'descripcion' => 'Consolidados académicos institucionales por programa y periodo.'],
        'jua.reportes.index' => ['titulo' => 'Reportes', 'icono' => 'bi-file-earmark-text', 'descripcion' => 'Genera y descarga reportes académicos institucionales.'],
        'jua.indicadores.index' => ['titulo' => 'Indicadores', 'icono' => 'bi-graph-up', 'descripcion' => 'Indicadores clave de gestión académica del instituto.'],
        'jua.parametros.index' => ['titulo' => 'Parámetros', 'icono' => 'bi-sliders', 'descripcion' => 'Configura los parámetros generales del sistema académico.'],
        'jua.roles.index' => ['titulo' => 'Roles y Permisos', 'icono' => 'bi-shield-lock', 'descripcion' => 'Configura los roles y permisos de acceso al sistema.'],
    ];

    public function stub(Request $request): View
    {
        $nombreRuta = $request->route()->getName();
        $info = self::MODULOS[$nombreRuta] ?? ['titulo' => 'Módulo', 'icono' => 'bi-gear', 'descripcion' => ''];

        return view('jua.stub', $info);
    }
}
