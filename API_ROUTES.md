# Kaely Auth Package - API Routes Documentation

## Base URL
All routes are prefixed with a configurable prefix. By default, the prefix is `/api/auth`, but you can customize it in your configuration.

### Default Configuration
- **Prefix:** `/api/auth`
- **Example routes:** `/api/auth/login`, `/api/auth/register`

### Customizable Configuration
You can configure the routes prefix in `config/auth-package.php`:

```php
'routes' => [
    'prefix' => 'auth', // Base prefix
    'api_prefix' => 'api', // API prefix (optional)
    'version_prefix' => null, // Version prefix (optional)
    'auto_api_prefix' => true, // Auto-add API prefix
    'enable_versioning' => false, // Enable versioning
],
```

### Configuration Examples

#### Simple routes: `/auth/`
```php
'prefix' => 'auth',
'api_prefix' => null,
'auto_api_prefix' => false,
```

#### API routes: `/api/auth/`
```php
'prefix' => 'auth',
'api_prefix' => 'api',
'auto_api_prefix' => true,
```

#### Versioned routes: `/api/v1/auth/`
```php
'prefix' => 'auth',
'api_prefix' => 'api',
'version_prefix' => 'v1',
'auto_api_prefix' => true,
'enable_versioning' => true,
```

For more configuration options, see [ROUTES_CONFIGURATION.md](ROUTES_CONFIGURATION.md).

## Authentication Routes

### Public Routes
- `POST /login` - User login
- `POST /register` - User registration

### Protected Routes (require authentication)
- `POST /logout` - User logout
- `GET /me` - Get current user info
- `POST /refresh` - Refresh authentication token

## Menu & Permissions Routes

### Dynamic Menu
- `GET /menu` - Get dynamic menu for authenticated user
- `GET /menu/permissions` - Get user permissions for frontend authorization
- `POST /menu/has-permission` - Check if user has specific permission
- `POST /menu/has-any-permission` - Check if user has any of specified permissions
- `GET /menu/modules` - Get user's accessible modules

## Branch Management Routes

### CRUD Operations
- `GET /branches` - List all branches (with pagination and filters)
- `POST /branches` - Create new branch
- `GET /branches/{branch}` - Get specific branch
- `PUT /branches/{branch}` - Update branch
- `DELETE /branches/{branch}` - Delete branch

### Utility Routes
- `GET /branches/active` - Get all active branches for dropdown

## Department Management Routes

### CRUD Operations
- `GET /departments` - List all departments (with pagination and filters)
- `POST /departments` - Create new department
- `GET /departments/{department}` - Get specific department
- `PUT /departments/{department}` - Update department
- `DELETE /departments/{department}` - Delete department

### Utility Routes
- `GET /departments/active` - Get all active departments for dropdown
- `GET /departments/by-branch/{branchId}` - Get departments by branch

## Module Management Routes

### CRUD Operations
- `GET /modules` - List all modules (with pagination and filters)
- `POST /modules` - Create new module
- `GET /modules/{module}` - Get specific module
- `PUT /modules/{module}` - Update module
- `DELETE /modules/{module}` - Delete module

### Utility Routes
- `GET /modules/active` - Get all active modules for menu
- `POST /modules/update-order` - Update module order

## Permission Management Routes

### CRUD Operations
- `GET /permissions` - List all permissions (with pagination and filters)
- `POST /permissions` - Create new permission
- `GET /permissions/{permission}` - Get specific permission
- `PUT /permissions/{permission}` - Update permission
- `DELETE /permissions/{permission}` - Delete permission

### Utility Routes
- `GET /permissions/active` - Get all active permissions for dropdown
- `GET /permissions/by-module/{moduleId}` - Get permissions by module
- `POST /permissions/bulk-create` - Bulk create permissions for a module

## Role Management Routes

### CRUD Operations
- `GET /roles` - List all roles (with pagination and filters)
- `POST /roles` - Create new role
- `GET /roles/{role}` - Get specific role
- `PUT /roles/{role}` - Update role
- `DELETE /roles/{role}` - Delete role

### Utility Routes
- `GET /roles/active` - Get all active roles for dropdown
- `POST /roles/{role}/assign-permissions` - Assign permissions to role
- `GET /roles/{role}/permissions` - Get role permissions

## User Management Routes

### CRUD Operations
- `GET /users` - List all users (with pagination and filters)
- `POST /users` - Create new user
- `GET /users/{user}` - Get specific user
- `PUT /users/{user}` - Update user
- `DELETE /users/{user}` - Delete user

### Utility Routes
- `GET /users/by-branch/{branchId}` - Get users by branch
- `GET /users/by-department/{departmentId}` - Get users by department
- `POST /users/{user}/assign-roles` - Assign roles to user
- `GET /users/{user}/roles` - Get user roles
- `GET /users/{user}/permissions` - Get user permissions

## Query Parameters

### Common Filters
- `search` - Search by name or code
- `status` - Filter by status (true/false)
- `sort_by` - Sort field (default: name)
- `sort_order` - Sort direction (asc/desc)
- `per_page` - Items per page (default: 15)

### Specific Filters
- `branch_id` - Filter by branch (departments, users)
- `department_id` - Filter by department (users)
- `module_id` - Filter by module (permissions)

## Response Format

All endpoints return JSON responses with the following structure:

### Success Response
```json
{
    "message": "Success message",
    "data": {
        // Resource data
    }
}
```

### Paginated Response
```json
{
    "data": [
        // Array of resources
    ],
    "pagination": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75,
        "from": 1,
        "to": 15
    }
}
```

### Error Response
```json
{
    "message": "Error message",
    "errors": {
        // Validation errors
    }
}
```

## Authentication

All protected routes require a valid Sanctum token in the Authorization header:
```
Authorization: Bearer {token}
```

## Middleware

The package uses the following middleware:
- `api` - For all routes
- `auth:sanctum` - For protected routes

## Configuration

You can customize the routes prefix and middleware in your `config/auth-package.php`:

```php
'routes' => [
    'prefix' => 'auth', // Base prefix
    'api_prefix' => 'api', // API prefix (optional)
    'version_prefix' => null, // Version prefix (optional)
    'middleware' => ['api'],
    'auth_middleware' => ['auth:sanctum'],
    'enable_versioning' => false, // Enable versioning
    'auto_api_prefix' => true, // Auto-add API prefix
],
```

### Configuration Parameters

- `prefix`: Base prefix for all routes (default: `'auth'`)
- `api_prefix`: Optional API prefix (default: `'api'`)
- `version_prefix`: Optional version prefix (default: `null`)
- `auto_api_prefix`: Whether to automatically add API prefix (default: `true`)
- `enable_versioning`: Whether to enable automatic versioning (default: `false`)
- `middleware`: Middleware for all routes (default: `['api']`)
- `auth_middleware`: Middleware for protected routes (default: `['auth:sanctum']`)

For detailed configuration examples, see [ROUTES_CONFIGURATION.md](ROUTES_CONFIGURATION.md). 