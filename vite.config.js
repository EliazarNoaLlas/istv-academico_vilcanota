import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/coordinador/dashboard.js',
                'resources/js/coordinador/cursos.js',
                'resources/js/coordinador/horarios.js',
                'resources/js/coordinador/portafolio.js',
                'resources/js/coordinador/docentes.js',
                'resources/js/coordinador/estudiantes.js',
                'resources/js/coordinador/notas.js',
                'resources/js/coordinador/consolidado.js',
                'resources/js/coordinador/validaciones.js',
                'resources/js/docente/dashboard.js',
                'resources/js/director/dashboard.js',
                'resources/js/director/usuarios.js',
                'resources/js/director/docentes.js',
                'resources/js/director/horarios.js',
                'resources/js/director/estudiantes.js',
                'resources/js/director/cursos.js',
                'resources/js/director/configuracion.js',
                'resources/js/director/notas.js',
                'resources/js/director/portafolio.js',
                'resources/js/director/analytics.js',
                'resources/js/director/alertas.js',
                'resources/js/director/notificaciones.js',
                'resources/js/director/reportes.js',
                'resources/js/jua/dashboard.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
