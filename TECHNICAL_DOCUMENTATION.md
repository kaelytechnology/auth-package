# Documentación Técnica - Kaely Auth Package

## Arquitectura del Paquete

### Estructura de Directorios

```
paquete/
├── composer.json                 # Configuración del paquete
├── config/
│   └── auth-package.php         # Configuración del paquete
├── database/
│   ├── migrations/              # Migraciones que extienden Laravel
│   └── seeders/                 # Seeders para datos iniciales
├── src/
│   ├── AuthPackageServiceProvider.php  # Service Provider principal
│   ├── Console/                 # Comandos de Artisan
│   ├── Controllers/             # Controladores de la API
│   ├── Http/
│   │   └── Resources/           # API Resources
│   ├── Middleware/              # Middleware personalizado
│   └── Models/                  # Modelos Eloquent
├── routes/
│   └── api.php                  # Rutas de la API
├── tests/                       # Tests del paquete
├── README.md                    # Documentación principal
└── TECHNICAL_DOCUMENTATION.md   # Esta documentación
```

## Componentes Principales

### 1. Service Provider (`AuthPackageServiceProvider`)

El Service Provider es el punto de entrada del paquete y se encarga de:

- Registrar configuraciones
- Registrar middleware
- Publicar archivos de configuración, migraciones y seeders
- Cargar rutas y vistas
- Registrar comandos de Artisan

### 2. Modelos

#### User
- Extiende `Authenticatable` de Laravel
- Usa `HasApiTokens` de Sanctum para autenticación API
- Incluye relaciones con roles, sucursales y departamentos
- Métodos para verificar roles y permisos
- **Extiende la tabla `users` existente de Laravel**

#### Role
- Representa roles de usuario
- Relación many-to-many con usuarios y permisos
- Métodos para asignar y verificar permisos

#### Permission
- Representa permisos específicos del sistema
- Relacionado con módulos
- Asignado a roles

#### Module
- Representa módulos del sistema
- Contiene permisos relacionados
- Configurable con iconos y rutas

#### RoleCategory
- Categoriza roles para mejor organización
- Relación one-to-many con roles

#### Person
- Información personal de usuarios
- Relación one-to-one con User

#### Branch y Department
- Entidades organizacionales
- Relaciones many-to-many con usuarios

### 3. Controladores

#### AuthController
- Maneja autenticación (login, logout, register)
- Gestión de tokens con Sanctum
- Validación de credenciales
- Verificación de estado de usuario

### 4. Middleware

#### CheckRole
- Verifica si el usuario tiene un rol específico
- Retorna 403 si no tiene el rol requerido

#### CheckPermission
- Verifica si el usuario tiene un permiso específico
- Retorna 403 si no tiene el permiso requerido

### 5. API Resources

#### UserResource
- Transforma datos de usuario para la API
- Incluye roles, permisos y relaciones según configuración
- Documentación OpenAPI integrada

#### PersonResource, BranchResource, DepartmentResource
- Resources para entidades relacionadas
- Documentación OpenAPI incluida

## Base de Datos

### Compatibilidad con Laravel

El paquete está diseñado para **extender** las tablas existentes de Laravel, no recrearlas:

#### Tablas de Laravel utilizadas:
- `users` - **Extendida** con campos adicionales
- `personal_access_tokens` - **Usada** por Sanctum (creada automáticamente)

#### Campos adicionales agregados a `users`:
- `is_active` (boolean) - Estado activo/inactivo
- `deleted_at` (timestamp) - Soft deletes
- `user_add` (unsignedBigInteger) - Auditoría de creación
- `user_edit` (unsignedBigInteger) - Auditoría de edición
- `user_deleted` (unsignedBigInteger) - Auditoría de eliminación

### Esquema de Tablas Nuevas

#### Tablas Principales
- `roles` - Roles de usuario
- `permissions` - Permisos del sistema
- `modules` - Módulos del sistema
- `role_categories` - Categorías de roles
- `people` - Información personal
- `branches` - Sucursales
- `departments` - Departamentos

#### Tablas Pivot
- `user_role` - Usuarios ↔ Roles
- `role_permission` - Roles ↔ Permisos
- `branches_users` - Sucursales ↔ Usuarios
- `departments_users` - Departamentos ↔ Usuarios

### Características de las Migraciones

- **No interfiere** con las migraciones existentes de Laravel
- **Extiende** la tabla `users` con campos adicionales
- Soft deletes en todas las tablas nuevas
- Campos de auditoría en todas las tablas nuevas
- Claves foráneas con eliminación en cascada
- Índices únicos para optimización

## API RESTful

### Endpoints de Autenticación

```
POST   /api/v1/auth/login      # Iniciar sesión
POST   /api/v1/auth/register   # Registrar usuario
POST   /api/v1/auth/logout     # Cerrar sesión
GET    /api/v1/auth/me         # Obtener usuario actual
POST   /api/v1/auth/refresh    # Refrescar token
```

### Autenticación

- Usa Laravel Sanctum para autenticación API
- Tokens de acceso con expiración configurable
- Verificación de estado de usuario activo/inactivo
- Validación de credenciales con mensajes personalizados

### Respuestas

- Formato JSON consistente
- Códigos de estado HTTP apropiados
- Documentación OpenAPI integrada
- Recursos transformados con configuración flexible

## Configuración

### Archivo de Configuración (`config/auth-package.php`)

```php
return [
    'models' => [
        // Modelos personalizables
    ],
    'auth' => [
        // Configuración de autenticación
    ],
    'tokens' => [
        // Configuración de tokens
    ],
    'validation' => [
        // Reglas de validación
    ],
    'routes' => [
        // Configuración de rutas
    ],
    'responses' => [
        // Configuración de respuestas
    ],
];
```

### Personalización

#### Extender Modelos
```php
use Kaely\AuthPackage\Models\User as BaseUser;

class User extends BaseUser
{
    // Funcionalidad personalizada
}
```

#### Configurar Respuestas
```php
'responses' => [
    'include_user_roles' => true,
    'include_user_permissions' => true,
    'include_user_branches' => true,
    'include_user_departments' => true,
],
```

## Instalación Inteligente

### Comando de Instalación

El comando `auth-package:install` incluye verificaciones inteligentes:

1. **Verifica tabla `users`** - Si no existe, ejecuta migraciones de Laravel
2. **Verifica tabla `personal_access_tokens`** - Si no existe, instala Sanctum
3. **Publica configuraciones** del paquete
4. **Publica migraciones** del paquete
5. **Ejecuta migraciones** del paquete
6. **Ejecuta seeders** para datos iniciales

### Compatibilidad con Proyectos Existentes

- ✅ **Funciona con proyectos nuevos** - Crea todas las tablas necesarias
- ✅ **Funciona con proyectos existentes** - Solo agrega campos adicionales
- ✅ **No interfiere** con configuraciones existentes
- ✅ **Extiende** funcionalidad sin romper nada

## Testing

### Configuración de Tests

- Usa Orchestra Testbench para testing de paquetes
- Base de datos SQLite en memoria
- Migraciones y seeders automáticos
- Tests de integración para API

### Ejemplos de Tests

```php
/** @test */
public function it_can_register_a_user()
{
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201);
}
```

## Comandos de Artisan

### auth-package:install

Instala el paquete automáticamente con verificaciones inteligentes:

1. Verifica y crea tablas básicas de Laravel si es necesario
2. Publica configuraciones
3. Publica migraciones
4. Publica seeders
5. Ejecuta migraciones
6. Ejecuta seeders
7. Muestra información de instalación

## Seguridad

### Características de Seguridad

- Validación de contraseñas configurable
- Verificación de estado de usuario
- Tokens con expiración
- Middleware de autorización
- Soft deletes para auditoría
- Campos de auditoría en todas las tablas

### Mejores Prácticas

- Usar middleware de autenticación
- Validar permisos antes de operaciones críticas
- Mantener tokens seguros
- Implementar rate limiting
- Logging de actividades de autenticación

## Rendimiento

### Optimizaciones

- Eager loading de relaciones
- Índices en claves foráneas
- Caché de roles y permisos
- Consultas optimizadas
- Recursos API eficientes

### Escalabilidad

- Arquitectura modular
- Configuración flexible
- Extensibilidad de modelos
- Middleware personalizable
- API RESTful estándar

## Mantenimiento

### Actualizaciones

1. Verificar compatibilidad con Laravel
2. Actualizar dependencias
3. Ejecutar migraciones
4. Actualizar documentación
5. Ejecutar tests

### Debugging

- Logs de autenticación
- Validación de tokens
- Verificación de permisos
- Estado de usuarios
- Relaciones de base de datos

## Extensibilidad

### Puntos de Extensión

- Modelos personalizables
- Middleware personalizable
- Configuración flexible
- Recursos API extensibles
- Comandos de Artisan personalizables

### Hooks y Events

- Eventos de autenticación
- Eventos de autorización
- Eventos de creación/modificación
- Callbacks personalizables

## Documentación

### OpenAPI/Swagger

- Documentación automática de endpoints
- Esquemas de modelos
- Ejemplos de requests/responses
- Códigos de estado HTTP

### Ejemplos de Uso

- Código de ejemplo en README
- Tests como documentación
- Configuraciones de ejemplo
- Casos de uso comunes

## Migración desde Proyectos Existentes

### Proceso de Migración

1. **Instalar el paquete** - `composer require kaely/auth-package`
2. **Ejecutar instalación** - `php artisan auth-package:install`
3. **Verificar compatibilidad** - El paquete solo agrega campos
4. **Actualizar modelos** - Extender los modelos del paquete si es necesario
5. **Configurar rutas** - Las rutas se cargan automáticamente

### Campos Agregados a `users`

```sql
ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE;
ALTER TABLE users ADD COLUMN deleted_at TIMESTAMP NULL;
ALTER TABLE users ADD COLUMN user_add UNSIGNED BIGINT NULL;
ALTER TABLE users ADD COLUMN user_edit UNSIGNED BIGINT NULL;
ALTER TABLE users ADD COLUMN user_deleted UNSIGNED BIGINT NULL;
```

### Compatibilidad con Datos Existentes

- ✅ **No afecta** datos existentes
- ✅ **Agrega** campos con valores por defecto
- ✅ **Mantiene** funcionalidad existente
- ✅ **Extiende** capacidades sin romper nada 