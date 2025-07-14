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

### Opción 2: Ejecutar directamente desde vendor (si tu Laravel lo permite)

```bash
php artisan db:seed --class=Kaely\\AuthPackage\\Database\\Seeders\\AuthPackageSeeder
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

## 9. Configuración Básica

Revisa y ajusta el archivo `config/auth-package.php` según tus necesidades:

```php
return [
    'routes' => [
        'prefix' => 'api/v1/auth',
        'middleware' => ['api'],
        'auth_middleware' => ['auth:sanctum'],
    ],
    // ...otros ajustes
];
```

---

## 10. Uso de las Rutas

Las rutas del paquete estarán disponibles bajo el prefijo configurado (por defecto `api/v1/auth`).

Consulta la documentación de rutas en `API_ROUTES.md` para ver todos los endpoints disponibles.

---

## 11. Integración con Frontend

- Usa los endpoints de login, logout, registro y menú dinámico para autenticar y construir el menú de navegación.
- Protege tus rutas frontend usando los permisos y roles retornados por el backend.

---

## 12. Personalización

- Puedes extender los modelos del paquete en tu propio proyecto.
- Puedes modificar los controladores y recursos si publicas el código fuente.
- Puedes cambiar el prefijo de rutas y middlewares en la configuración.

---

## 13. Actualización del Paquete

Para actualizar a la última versión:

```bash
composer update kaelytechnology/auth-package
```

---

## 14. Soporte

Para dudas, reportes o sugerencias, abre un issue en:
https://github.com/kaelytechnology/auth-package

---

¡Listo! El paquete Kaely Auth Package estará funcionando en tu proyecto Laravel 🚀 