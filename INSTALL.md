# Instalación de Kaely Auth Package en un Proyecto Laravel

Esta guía te ayudará a instalar y configurar el paquete de autenticación Kaely en cualquier proyecto Laravel.

---

## 1. Requisitos Previos

- Laravel 10.x, 11.x o 12.x
- PHP >= 8.1
- Base de datos configurada
- Composer instalado

---

## 2. Para Proyectos Laravel Nuevos

Si acabas de crear un proyecto Laravel desde cero, ejecuta primero:

```bash
php artisan install:api
```

Esto instalará y configurará Sanctum, Passport o el stack API necesario para autenticación.

---

## 3. Agregar el repositorio del paquete (si es privado o desarrollo)

Antes de instalar el paquete, agrega el repositorio a tu `composer.json`:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/kaelytechnology/auth-package"
    }
],
```

Coloca esto antes de la sección `require`.

---

## 4. Instalar el Paquete vía Composer

```bash
composer require kaelytechnology/auth-package
```

---

## 4.1. Sincronizar variables de entorno

Para agregar automáticamente las variables de entorno necesarias a tu archivo `.env`, ejecuta:

```bash
php artisan auth-package:sync-env
```

Esto copiará todas las variables de `env.example` del paquete a tu `.env` (solo las que no existan). Si quieres sobrescribir valores existentes, usa la opción `--force`:

```bash
php artisan auth-package:sync-env --force
```

---

## 5. Publicar Configuración y Migraciones

```bash
php artisan vendor:publish --provider="Kaely\AuthPackage\AuthPackageServiceProvider" --tag=auth-package-config
php artisan vendor:publish --provider="Kaely\AuthPackage\AuthPackageServiceProvider" --tag=auth-package-migrations
```

Esto generará los archivos de configuración en `config/auth-package.php` y las migraciones en `database/migrations`.

---

## 6. Ejecutar Migraciones

```bash
php artisan migrate
```

---

## 7. Ejecutar Seeders (Opcional pero recomendado)

### Opción 1: Publicar y ejecutar desde tu proyecto (recomendado)

1. **Publica los seeders:**
   ```bash
   php artisan vendor:publish --provider="Kaely\AuthPackage\AuthPackageServiceProvider" --tag=auth-package-seeders
   ```
   Esto copiará el seeder a `database/seeders/AuthPackageSeeder.php` en tu proyecto.

2. **Ejecuta el seeder:**
   ```bash
   php artisan db:seed --class=AuthPackageSeeder
   ```

### Opción 2: Ejecutar directamente desde vendor

```bash
php artisan db:seed --class="Kaely\AuthPackage\Database\Seeders\AuthPackageSeeder"
```

> **Nota:** Si tienes problemas con la opción 2, usa la opción 1 (publicar y ejecutar desde tu proyecto).

Esto creará roles, permisos, módulos y un usuario administrador por defecto:
- **Email:** admin@example.com
- **Password:** password

---

## 8. Instalación Automática (Opcional)

El paquete incluye un comando para instalar todo automáticamente:

```bash
php artisan auth-package:install
```

Este comando:
- Publica configuración y migraciones
- Ejecuta migraciones y seeders
- Verifica que las tablas base existan

---

## 9. Generar Modelos y Controladores Extendibles (Personalización recomendada)

Para permitir la personalización sin modificar el paquete, puedes generar copias extendibles de los modelos y controladores principales. Estos archivos se crearán en tu aplicación y extenderán los del paquete, permitiéndote sobreescribir métodos, agregar relaciones, endpoints, etc.

### Paso a paso:

1. Ejecuta el siguiente comando:
   ```bash
   php artisan auth-package:generate-extendable
   ```
   Esto generará:
   - Modelos en `app/Models/AuthPackage/`
   - Controladores en `app/Http/Controllers/AuthPackage/`

2. Personaliza los archivos generados según tus necesidades. Por ejemplo, puedes agregar relaciones, métodos, scopes o endpoints propios.

3. El paquete detectará y usará automáticamente tus modelos y controladores extendidos si existen en esas rutas.

> **Tip:** Si ya existen archivos y quieres sobrescribirlos, usa la opción `--force`:
> ```bash
> php artisan auth-package:generate-extendable --force
> ```

---

## 10. Configuración Básica

Revisa y ajusta el archivo `config/auth-package.php` según tus necesidades:

```php
return [
    'routes' => [
        'prefix' => 'auth', // Prefijo base
        'api_prefix' => 'api', // Prefijo de API (opcional)
        'version_prefix' => null, // Prefijo de versión (opcional)
        'middleware' => ['api'],
        'auth_middleware' => ['auth:sanctum'],
        'enable_versioning' => false, // Habilitar versionado
        'auto_api_prefix' => true, // Agregar automáticamente prefijo API
    ],
    // ...otros ajustes
];
```

### Ejemplos de Configuración de Rutas

#### Rutas simples: `/auth/`
```php
'prefix' => 'auth',
'api_prefix' => null,
'auto_api_prefix' => false,
```

#### Rutas con API: `/api/auth/`
```php
'prefix' => 'auth',
'api_prefix' => 'api',
'auto_api_prefix' => true,
```

#### Rutas con versionado: `/api/v1/auth/`
```php
'prefix' => 'auth',
'api_prefix' => 'api',
'version_prefix' => 'v1',
'auto_api_prefix' => true,
'enable_versioning' => true,
```

Para más opciones de configuración, consulta [ROUTES_CONFIGURATION.md](ROUTES_CONFIGURATION.md).

---

## 11. Uso de las Rutas

Las rutas del paquete estarán disponibles bajo el prefijo configurado (por defecto `api/auth`).

Consulta la documentación de rutas en `API_ROUTES.md` para ver todos los endpoints disponibles.

### Verificar Rutas

Para verificar que las rutas se han configurado correctamente:

```bash
php artisan route:list | grep auth
```

---

## 12. Integración con Frontend

- Usa los endpoints de login, logout, registro y menú dinámico para autenticar y construir el menú de navegación.
- Protege tus rutas frontend usando los permisos y roles retornados por el backend.

---

## 13. Personalización

- Puedes extender los modelos del paquete en tu propio proyecto usando el comando `auth-package:generate-extendable`.
- Puedes modificar los controladores y recursos si publicas el código fuente.
- Puedes cambiar el prefijo de rutas y middlewares en la configuración.

---

## 14. Actualización del Paquete

Para actualizar a la última versión:

```bash
composer update kaelytechnology/auth-package
```

---

## 15. Solución de Problemas

Si encuentras problemas durante la instalación, consulta [TROUBLESHOOTING.md](TROUBLESHOOTING.md) para soluciones comunes.

## 16. Soporte

Para dudas, reportes o sugerencias, abre un issue en:
https://github.com/kaelytechnology/auth-package

---

¡Listo! El paquete Kaely Auth Package estará funcionando en tu proyecto Laravel 🚀 