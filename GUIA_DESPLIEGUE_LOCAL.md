# Guía de Despliegue Local — ISTV Laravel

Guía del proceso de despliegue local del proyecto **istv-laravel** (Laravel 12) usando XAMPP. cambio

## 1. Requisitos verificados

| Herramienta | Versión detectada |
|---|---|
| PHP | 8.2.12 (XAMPP) |
| Composer | 2.9.7 |
| Node.js | v24.14.1 |
| npm | 11.11.0 |
| MySQL | Servicio activo en `127.0.0.1:3306` |

## 2. Configuración del entorno (`.env`)

El archivo `.env` ya estaba presente con la siguiente configuración clave:

```env
APP_NAME=Laravel
APP_ENV=local
APP_URL=http://localhost
APP_KEY=base64:BlcRHDNLRVTYUT7hRn6Sg4iqQxSDvF3A++P0ii+4NBk=

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vilcanotaistv_laravel_test
DB_USERNAME=root
DB_PASSWORD=12345678

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

> `APP_KEY` ya estaba generada. Si necesitas regenerarla: `php artisan key:generate`.

## 3. Dependencias

### Backend (Composer)
El directorio `vendor/` ya existía con `autoload.php` presente, por lo que las dependencias de PHP (Laravel 12, Tinker, Pail, Pint, Sail, etc.) ya estaban instaladas. Para reinstalarlas o actualizarlas:

```bash
composer install
```

### Frontend (npm)
El directorio `node_modules/` ya existía con los binarios de Vite disponibles (axios, concurrently, laravel-vite-plugin, vite). Para reinstalarlas:

```bash
npm install
```

## 4. Base de datos y migraciones

Se verificó el estado de las migraciones con:

```bash
php artisan migrate:status
```

**Resultado:** las 32 migraciones del proyecto ya estaban aplicadas (batches 1 a 3), incluyendo las tablas del dominio académico: `roles`, `programas_estudio`, `periodos_academicos`, `aulas`, `docentes`, `usuarios`, `estudiantes`, `cursos`, `horarios`, `matriculas`, `portafolio_docente`, `sesiones_aprendizaje`, `notas`, `asistencia_*`, `ia_predicciones`, `alertas_academicas`, entre otras.

Si necesitas ejecutar migraciones pendientes en el futuro:

```bash
php artisan migrate
```

## 5. Assets compilados (Vite)

Se confirmó que `public/build/` ya contiene los assets compilados (`assets/` + `manifest.json`), generados previamente con:

```bash
npm run build
```

Para desarrollo con recarga en caliente:

```bash
npm run dev
```

## 6. Storage público

Se detectó que el enlace simbólico de storage **no existía** y se creó:

```bash
php artisan storage:link
```

Esto conecta `public/storage` → `storage/app/public`, necesario para servir archivos subidos (p. ej. `portafolio_documentos`).

## 7. Levantar el servidor local

Se limpió la caché de configuración y se inició el servidor embebido de Laravel:

```bash
php artisan config:clear
php artisan serve --host=127.0.0.1 --port=8000
```

**Verificación realizada:**
- `GET http://127.0.0.1:8000` → `302 Found` redirigiendo a `/login` (comportamiento esperado, la ruta raíz requiere autenticación).
- `GET http://127.0.0.1:8000/login` → `200 OK` (la vista de login renderiza correctamente).

La aplicación queda disponible en:

```
http://127.0.0.1:8000
```

## 8. Comandos de arranque rápido (referencia)

Para futuras veces que quieras levantar todo el stack (servidor + cola + logs + vite) en un solo comando, el `composer.json` ya define el script `dev`:

```bash
composer run dev
```

Esto ejecuta en paralelo: `php artisan serve`, `php artisan queue:listen`, `php artisan pail` y `npm run dev`.

## 9. Resumen de estado final

| Componente | Estado |
|---|---|
| Dependencias PHP (Composer) | ✅ Instaladas |
| Dependencias JS (npm) | ✅ Instaladas |
| Migraciones | ✅ 32/32 aplicadas |
| Assets Vite | ✅ Compilados (`public/build`) |
| Storage link | ✅ Creado |
| Servidor local | ✅ Corriendo en `http://127.0.0.1:8000` |
