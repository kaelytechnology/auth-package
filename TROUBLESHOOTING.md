# Solución de Problemas - Kaely Auth Package

Este documento te ayudará a resolver problemas comunes durante la instalación y uso del paquete.

## Problemas de Instalación

### 1. Error: "Target class [AuthPackageSeeder] does not exist"

**Síntomas:**
```
Target class [Kaely\AuthPackage\Database\Seeders\AuthPackageSeeder] does not exist.
```

**Solución:**
El seeder no se ha publicado correctamente. Ejecuta estos comandos en orden:

```bash
# 1. Publicar el seeder
php artisan vendor:publish --provider="Kaely\AuthPackage\AuthPackageServiceProvider" --tag=auth-package-seeders

# 2. Verificar que el archivo existe
ls database/seeders/AuthPackageSeeder.php

# 3. Ejecutar el seeder
php artisan db:seed --class=AuthPackageSeeder
```

### 2. Error: "Class 'Database\Seeders\AuthPackageSeeder' does not exist"

**Síntomas:**
```
Class "Database\Seeders\AuthPackageSeeder" does not exist
```

**Solución:**
El seeder no está en el directorio correcto. Verifica que el archivo existe:

```bash
# Verificar que el archivo existe
ls database/seeders/AuthPackageSeeder.php

# Si no existe, publicar nuevamente
php artisan vendor:publish --provider="Kaely\AuthPackage\AuthPackageServiceProvider" --tag=auth-package-seeders --force
```

### 3. Error: "Table 'users' doesn't exist"

**Síntomas:**
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'users' doesn't exist
```

**Solución:**
Las migraciones de Laravel no se han ejecutado. Ejecuta:

```bash
# Ejecutar migraciones de Laravel
php artisan migrate

# Si es un proyecto nuevo, instalar Sanctum
php artisan install:api
```

### 4. Error: "Table 'personal_access_tokens' doesn't exist"

**Síntomas:**
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'personal_access_tokens' doesn't exist
```

**Solución:**
Sanctum no está instalado o configurado. Ejecuta:

```bash
# Instalar Sanctum
composer require laravel/sanctum

# Publicar configuración de Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Ejecutar migraciones
php artisan migrate
```

### 5. Error: "Class 'Kaely\AuthPackage\Models\User' not found"

**Síntomas:**
```
Class 'Kaely\AuthPackage\Models\User' not found
```

**Solución:**
El autoloader no se ha actualizado. Ejecuta:

```bash
# Actualizar autoloader
composer dump-autoload

# Limpiar caché
php artisan config:clear
php artisan cache:clear
```

## Problemas de Rutas

### 1. Las rutas no aparecen en `php artisan route:list`

**Solución:**
Verifica que el ServiceProvider esté registrado en `config/app.php`:

```php
'providers' => [
    // ...
    Kaely\AuthPackage\AuthPackageServiceProvider::class,
],
```

### 2. Error 404 en las rutas de autenticación

**Solución:**
Verifica la configuración de rutas en `config/auth-package.php`:

```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => 'api',
    'auto_api_prefix' => true,
    // ...
],
```

## Problemas de Autenticación

### 1. Error: "Guard [sanctum] is not defined"

**Síntomas:**
```
Guard [sanctum] is not defined.
```

**Solución:**
Sanctum no está configurado correctamente. Verifica `config/auth.php`:

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'api' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
],
```

### 2. Error: "Token not found"

**Síntomas:**
```
Token not found
```

**Solución:**
El token no se está enviando correctamente. Asegúrate de incluir el header:

```
Authorization: Bearer {tu-token}
```

## Problemas de Base de Datos

### 1. Error: "Column 'is_active' doesn't exist"

**Síntomas:**
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'is_active' in 'users' table
```

**Solución:**
Las migraciones del paquete no se han ejecutado. Ejecuta:

```bash
# Ejecutar migraciones del paquete
php artisan migrate

# Si hay problemas, refrescar migraciones
php artisan migrate:refresh
```

### 2. Error: "Table 'modules' doesn't exist"

**Síntomas:**
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'modules' doesn't exist
```

**Solución:**
Las migraciones del paquete no se han publicado. Ejecuta:

```bash
# Publicar migraciones
php artisan vendor:publish --provider="Kaely\AuthPackage\AuthPackageServiceProvider" --tag=auth-package-migrations

# Ejecutar migraciones
php artisan migrate
```

### 3. Error: "Column 'order' not found in 'modules' table"

**Síntomas:**
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'order' in 'field list'
```

**Solución:**
La migración de módulos no incluye la columna `order`. Ejecuta:

```bash
# Opción 1: Usar el comando de reparación automática (recomendado)
php artisan auth-package:fix-migrations

# Opción 2: Refrescar migraciones para aplicar los cambios
php artisan migrate:refresh

# Opción 3: Agregar la columna manualmente
php artisan make:migration add_order_to_modules_table
```

Luego en la nueva migración:
```php
public function up()
{
    Schema::table('modules', function (Blueprint $table) {
        $table->integer('order')->default(0)->after('slug');
    });
}
```

## Problemas de Configuración

### 1. Error: "Configuration file not found"

**Síntomas:**
```
Configuration file not found
```

**Solución:**
El archivo de configuración no se ha publicado. Ejecuta:

```bash
# Publicar configuración
php artisan vendor:publish --provider="Kaely\AuthPackage\AuthPackageServiceProvider" --tag=auth-package-config
```

### 2. Las rutas no respetan la configuración

**Solución:**
Limpia la caché de rutas:

```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

## Verificación de Instalación

Para verificar que todo está funcionando correctamente:

```bash
# 1. Verificar que las rutas existen
php artisan route:list | grep auth

# 2. Verificar que las tablas existen
php artisan tinker
>>> Schema::hasTable('users')
>>> Schema::hasTable('modules')
>>> Schema::hasTable('roles')
>>> exit

# 3. Verificar que el usuario admin existe
php artisan tinker
>>> \Kaely\AuthPackage\Models\User::where('email', 'admin@example.com')->exists()
>>> exit
```

## Comandos Útiles

### Limpiar todo y reinstalar
```bash
# Limpiar caché
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Reinstalar
composer dump-autoload
php artisan migrate:fresh
php artisan db:seed --class=AuthPackageSeeder
```

### Verificar archivos publicados
```bash
# Verificar configuración
ls config/auth-package.php

# Verificar migraciones
ls database/migrations/*auth_package*

# Verificar seeder
ls database/seeders/AuthPackageSeeder.php
```

## Contacto

Si sigues teniendo problemas, puedes:

1. Verificar que estás usando la versión correcta de Laravel (10.x, 11.x, 12.x)
2. Verificar que PHP es >= 8.1
3. Revisar los logs en `storage/logs/laravel.log`
4. Abrir un issue en el repositorio del paquete 