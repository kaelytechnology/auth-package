# Ejemplos de Configuración de Rutas

Este documento muestra ejemplos prácticos de cómo configurar las rutas del paquete de autenticación para diferentes casos de uso.

## Configuración por Defecto

La configuración por defecto genera rutas como `/api/auth/login`:

```php
// config/auth-package.php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => 'api',
    'version_prefix' => null,
    'middleware' => ['api'],
    'auth_middleware' => ['auth:sanctum'],
    'enable_versioning' => false,
    'auto_api_prefix' => true,
],
```

**Rutas generadas:**
- `POST /api/auth/login`
- `POST /api/auth/register`
- `GET /api/auth/users`
- `GET /api/auth/roles`

## Ejemplos de Configuración

### 1. Rutas Simples sin Prefijo API

**Configuración:**
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => null,
    'version_prefix' => null,
    'auto_api_prefix' => false,
    'enable_versioning' => false,
],
```

**Rutas generadas:**
- `POST /auth/login`
- `POST /auth/register`
- `GET /auth/users`
- `GET /auth/roles`

**Casos de uso:**
- Aplicaciones web simples
- APIs internas
- Cuando no necesitas el prefijo `/api`

### 2. Rutas con Prefijo API Personalizado

**Configuración:**
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => 'rest',
    'version_prefix' => null,
    'auto_api_prefix' => true,
    'enable_versioning' => false,
],
```

**Rutas generadas:**
- `POST /rest/auth/login`
- `POST /rest/auth/register`
- `GET /rest/auth/users`
- `GET /rest/auth/roles`

**Casos de uso:**
- APIs con prefijo personalizado
- Cuando quieres diferenciar de otras APIs
- Integración con sistemas legacy

### 3. Rutas con Versionado

**Configuración:**
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => 'api',
    'version_prefix' => 'v1',
    'auto_api_prefix' => true,
    'enable_versioning' => true,
],
```

**Rutas generadas:**
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/register`
- `GET /api/v1/auth/users`
- `GET /api/v1/auth/roles`

**Casos de uso:**
- APIs públicas
- Cuando necesitas versionado
- Compatibilidad con versiones anteriores

### 4. Rutas con Versión Personalizada

**Configuración:**
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => 'api',
    'version_prefix' => 'v2',
    'auto_api_prefix' => true,
    'enable_versioning' => true,
],
```

**Rutas generadas:**
- `POST /api/v2/auth/login`
- `POST /api/v2/auth/register`
- `GET /api/v2/auth/users`
- `GET /api/v2/auth/roles`

**Casos de uso:**
- Nuevas versiones de API
- APIs beta
- Migración gradual de versiones

### 5. Rutas sin API pero con Versión

**Configuración:**
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => null,
    'version_prefix' => 'v1',
    'auto_api_prefix' => false,
    'enable_versioning' => true,
],
```

**Rutas generadas:**
- `POST /v1/auth/login`
- `POST /v1/auth/register`
- `GET /v1/auth/users`
- `GET /v1/auth/roles`

**Casos de uso:**
- APIs simples con versionado
- Cuando no quieres el prefijo `/api`
- APIs internas con versionado

### 6. Rutas Completamente Personalizadas

**Configuración:**
```php
'routes' => [
    'prefix' => 'authentication',
    'api_prefix' => 'my-api',
    'version_prefix' => 'beta',
    'auto_api_prefix' => true,
    'enable_versioning' => true,
],
```

**Rutas generadas:**
- `POST /my-api/beta/authentication/login`
- `POST /my-api/beta/authentication/register`
- `GET /my-api/beta/authentication/users`
- `GET /my-api/beta/authentication/roles`

**Casos de uso:**
- APIs completamente personalizadas
- APIs de prueba/beta
- Cuando necesitas control total sobre las rutas

## Migración desde Configuración Anterior

Si estás migrando desde la configuración anterior que usaba `'prefix' => 'api/v1/auth'`, aquí tienes las opciones:

### Opción 1: Mantener la misma estructura
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => 'api',
    'version_prefix' => 'v1',
    'auto_api_prefix' => true,
    'enable_versioning' => true,
],
```

### Opción 2: Simplificar a solo `/auth/`
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => null,
    'version_prefix' => null,
    'auto_api_prefix' => false,
    'enable_versioning' => false,
],
```

### Opción 3: Solo con prefijo API `/api/auth/`
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => 'api',
    'version_prefix' => null,
    'auto_api_prefix' => true,
    'enable_versioning' => false,
],
```

## Configuración por Entorno

Puedes usar diferentes configuraciones según el entorno:

### Desarrollo
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => 'api',
    'version_prefix' => 'dev',
    'auto_api_prefix' => true,
    'enable_versioning' => true,
],
```

### Producción
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => 'api',
    'version_prefix' => 'v1',
    'auto_api_prefix' => true,
    'enable_versioning' => true,
],
```

### Testing
```php
'routes' => [
    'prefix' => 'auth',
    'api_prefix' => null,
    'version_prefix' => null,
    'auto_api_prefix' => false,
    'enable_versioning' => false,
],
```

## Verificación de Rutas

Para verificar que las rutas se han configurado correctamente, puedes usar el comando de Artisan:

```bash
php artisan route:list --name=auth
```

O para ver todas las rutas del paquete:

```bash
php artisan route:list | grep auth
```

## Consideraciones de Seguridad

- **Prefijos largos:** Pueden ayudar a ocultar la estructura de tu API
- **Versionado:** Permite mantener compatibilidad con versiones anteriores
- **Middleware:** Asegúrate de que el middleware de autenticación esté configurado correctamente
- **CORS:** Si usas diferentes prefijos, actualiza la configuración de CORS

## Mejores Prácticas

1. **Consistencia:** Usa el mismo patrón en todo tu proyecto
2. **Documentación:** Documenta la configuración de rutas para tu equipo
3. **Versionado:** Usa versionado para APIs públicas
4. **Simplicidad:** No uses prefijos innecesariamente largos
5. **Testing:** Prueba las rutas después de cambiar la configuración 