# Multi-Database Multi-Tenancy Implementation

This Laravel application implements a comprehensive multi-database multi-tenancy system where each tenant has their own separate database, providing complete data isolation and enhanced security.

## 🏗️ Architecture Overview

### Database Isolation Strategy
- **Separate Databases**: Each tenant has their own dedicated database
- **Complete Isolation**: No shared tables or data between tenants
- **Enhanced Security**: Tenant data is physically separated
- **Scalability**: Individual database optimization per tenant

### Key Components

1. **Tenant Model** (`app/Models/Tenant.php`)
   - Stores tenant metadata and database connection details
   - Includes database host, port, username, and password fields
   - Manages tenant-specific database configurations

2. **TenantDatabaseManager** (`app/Services/TenantDatabaseManager.php`)
   - Handles dynamic database connections
   - Manages tenant database creation and configuration
   - Provides database existence checks and connection testing

3. **Tenant Resolution Middleware** (`app/Http/Middleware/ResolveTenant.php`)
   - Resolves tenant from subdomain, domain, or API headers
   - Sets up tenant-specific database connection
   - Provides tenant context throughout the application

## 📊 Database Schema

### Main Application Database
Contains tenant metadata and configuration:

```sql
-- tenants table
CREATE TABLE tenants (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    domain VARCHAR(255),
    subdomain VARCHAR(255),
    database_name VARCHAR(255),
    database_host VARCHAR(255),
    database_port INT,
    database_username VARCHAR(255),
    database_password VARCHAR(255),
    settings JSON,
    is_active BOOLEAN DEFAULT TRUE,
    trial_ends_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Tenant-Specific Databases
Each tenant database contains:

1. **users** - User accounts with roles and authentication
2. **roles** - Role definitions with permissions
3. **permissions** - Available permissions
4. **role_user** - User-role relationships
5. **permission_role** - Role-permission relationships
6. **personal_access_tokens** - API authentication tokens
7. **password_reset_tokens** - Password reset functionality
8. **sessions** - User session management

## 🔧 Management Commands

### Tenant Database Creation
```bash
# Create database for a specific tenant
php artisan tenant:db:create demo

# Create database and drop existing if it exists
php artisan tenant:db:create demo --drop
```

### Tenant Migrations
```bash
# Migrate all tenants
php artisan tenant:migrate

# Migrate specific tenant
php artisan tenant:migrate demo

# Fresh migration with seeding
php artisan tenant:migrate demo --fresh --seed

# Force migration in production
php artisan tenant:migrate --force
```

## 🔐 Authentication System

### API Authentication
The system uses Laravel Sanctum for API authentication:

```php
// Registration
POST /api/auth/register
{
    "name": "John Doe",
    "email": "john@demo.com",
    "password": "password123",
    "password_confirmation": "password123"
}

// Login
POST /api/auth/login
{
    "email": "john@demo.com",
    "password": "password123"
}

// Get user info (requires Bearer token)
GET /api/auth/me
Authorization: Bearer {token}
```

### Role-Based Access Control
- **Admin**: Full system access
- **Manager**: Limited administrative access
- **User**: Basic user access

### Permission System
Granular permissions for:
- User management (view, create, edit, delete)
- Role management (view, create, edit, delete)
- Settings management (view, edit)

## 🚀 Setup Instructions

### 1. Run Main Application Migrations
```bash
php artisan migrate
```

### 2. Create Demo Tenant
```bash
php artisan db:seed --class=TenantSeeder
```

### 3. Set Up Tenant Database
```bash
# Create tenant database
php artisan tenant:db:create demo

# Run tenant migrations with seeding
php artisan tenant:migrate demo --fresh --seed
```

### 4. Configure Web Server
Set up subdomain routing for tenant access:

```nginx
# Nginx configuration
server {
    listen 80;
    server_name *.localhost;
    root /path/to/your/app/public;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 🧪 Testing the Implementation

### Demo Tenant Access
Access the demo tenant at: `http://demo.localhost`

### Demo Users
```
Admin User:
- Email: admin@demo.com
- Password: password
- Role: Administrator (full access)

Manager User:
- Email: manager@demo.com
- Password: password
- Role: Manager (limited admin access)

Regular User:
- Email: user@demo.com
- Password: password
- Role: User (basic access)
```

### API Testing Examples

```bash
# Register new user
curl -X POST http://demo.localhost/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@demo.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Login
curl -X POST http://demo.localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@demo.com",
    "password": "password"
  }'

# Get user info (replace TOKEN with actual token)
curl -X GET http://demo.localhost/api/auth/me \
  -H "Authorization: Bearer TOKEN"
```

## 🔄 Tenant Provisioning Workflow

### 1. Create Tenant Record
```php
$tenant = Tenant::create([
    'name' => 'Acme Corp',
    'slug' => 'acme',
    'subdomain' => 'acme',
    'database_name' => 'tenant_acme',
    'database_host' => 'localhost',
    'database_port' => 3306,
    'database_username' => 'tenant_user',
    'database_password' => 'secure_password',
    'is_active' => true,
]);
```

### 2. Create Tenant Database
```bash
php artisan tenant:db:create acme
```

### 3. Run Tenant Migrations
```bash
php artisan tenant:migrate acme --seed
```

### 4. Tenant Ready for Use
Access at: `http://acme.localhost`

## 🛡️ Security Features

### Database Isolation
- Complete physical separation of tenant data
- No risk of cross-tenant data leakage
- Individual database security configurations

### Authentication Security
- Laravel Sanctum token-based authentication
- Password hashing with bcrypt
- Token expiration and revocation
- Role-based access control

### Middleware Protection
- Tenant resolution and validation
- Role and permission checking
- Request validation and sanitization

## 📈 Performance Considerations

### Database Connections
- Dynamic connection management
- Connection pooling per tenant
- Optimized query performance per tenant

### Caching Strategy
- Tenant-specific cache keys
- Database connection caching
- Permission and role caching

### Scaling Options
- Horizontal database scaling
- Load balancing per tenant
- Database sharding possibilities

## 🔧 Configuration

### Environment Variables
```env
# Main database connection
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_main
DB_USERNAME=root
DB_PASSWORD=

# Tenant database defaults
TENANT_DB_HOST=127.0.0.1
TENANT_DB_PORT=3306
TENANT_DB_USERNAME=tenant_user
TENANT_DB_PASSWORD=tenant_password
```

### Sanctum Configuration
```php
// config/sanctum.php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    Sanctum::currentApplicationUrlWithPort()
))),
```

## 🚨 Important Notes

### Data Isolation
- Each tenant's data is completely isolated in separate databases
- No shared tables or cross-tenant queries possible
- Enhanced security through physical separation

### Migration Management
- Tenant migrations are stored in `database/migrations/tenant/`
- Main application migrations in `database/migrations/`
- Use tenant-specific commands for tenant database operations

### Authentication Flow
- Tenant resolution happens before authentication
- Users exist only within their tenant's database
- API tokens are tenant-specific

## 🔮 Future Enhancements

### Planned Features
1. **Tenant Management Interface**
   - Admin panel for tenant management
   - Tenant provisioning automation
   - Resource usage monitoring

2. **Backup and Restore**
   - Automated tenant database backups
   - Point-in-time recovery
   - Cross-tenant data migration tools

3. **Advanced Security**
   - Two-factor authentication
   - Audit logging per tenant
   - Advanced permission system

4. **Performance Optimization**
   - Database query optimization
   - Caching improvements
   - Connection pooling enhancements

5. **Monitoring and Analytics**
   - Tenant usage analytics
   - Performance monitoring
   - Resource utilization tracking

## 📚 Additional Resources

- [Laravel Multi-Tenancy Documentation](https://laravel.com/docs/database#multiple-database-connections)
- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [Database Migration Best Practices](https://laravel.com/docs/migrations)

---

This implementation provides a robust, secure, and scalable multi-tenant architecture suitable for SaaS applications requiring complete data isolation between tenants.
