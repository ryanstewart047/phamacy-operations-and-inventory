# Pharmacy Operations & Inventory Management System
## Local Setup Instructions

This is a complete pharmacy management system built with Laravel 12, Inertia.js, Vue 3, and Tailwind CSS.

## Prerequisites

Make sure you have these installed on your local machine:
- **PHP 8.2+** (with required extensions: sqlite, mbstring, openssl, pdo)
- **Composer** (PHP dependency manager)
- **Node.js 18+** and npm
- **Git**

## Installation Steps

### 1. Clone the Repository

```bash
git clone https://github.com/ryanstewart047/phamacy-operations-and-inventory.git
cd phamacy-operations-and-inventory/backend
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install JavaScript Dependencies

```bash
npm install
```

### 4. Setup Environment

```bash
# Copy the environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5. Setup Database

The project uses SQLite by default (no additional database server needed).

```bash
# Create the database file
touch database/database.sqlite

# Run migrations and seed sample data
php artisan migrate --seed
```

This will create:
- 3 users (admin, manager, staff) - all with password: `password`
- Sample products, categories, suppliers
- Sample sales and inventory data

### 6. Build Frontend Assets

```bash
# For development with hot-reload
npm run dev

# OR for production build
npm run build
```

### 7. Start the Development Server

In a new terminal:

```bash
php artisan serve
```

The application will be available at: **http://127.0.0.1:8000**

## Default Login Credentials

- **Admin**: admin@example.com / password
- **Manager**: manager@example.com / password  
- **Staff**: staff@example.com / password

## Features Implemented

### ✅ Backend (Laravel)
- User authentication with roles (Admin, Manager, Staff)
- Product management with categories and suppliers
- Sales and inventory tracking
- Purchase order management
- Customer and payment tracking
- Stock movement and batch tracking
- Audit logging system
- Low stock alerts
- RESTful API controllers
- Form validation requests
- Services: InventoryService, ReportingService

### ✅ Database
- 15+ tables with complete relationships
- Migrations for all entities
- Comprehensive seeders with sample data
- SQLite for easy local development

### ✅ Frontend (Vue 3 + Inertia.js)
- Authentication pages (Login, Register, Password Reset)
- Dashboard layout with navigation
- Profile management
- Responsive design with Tailwind CSS
- Server-side rendering (SSR) ready

### 🚧 In Progress / To Be Built
- Product listing and management pages
- Sales transaction interface
- Inventory dashboard with charts
- Purchase order workflow
- Reporting and analytics
- User management interface
- Low stock alerts UI

## Project Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # ProductController, SalesController, etc.
│   │   ├── Requests/        # Form validation
│   │   └── Middleware/
│   ├── Models/              # Eloquent models (Product, Sale, etc.)
│   ├── Services/            # Business logic services
│   └── Notifications/       # Email notifications
├── database/
│   ├── migrations/          # Database schema
│   └── seeders/             # Sample data
├── resources/
│   ├── js/
│   │   ├── Components/      # Reusable Vue components
│   │   ├── Layouts/         # Page layouts
│   │   └── Pages/           # Inertia pages
│   └── css/                 # Tailwind styles
└── routes/
    └── web.php              # Application routes
```

## Common Commands

```bash
# Clear all caches
php artisan optimize:clear

# Run tests
php artisan test

# Create new migration
php artisan make:migration create_example_table

# Create new controller
php artisan make:controller ExampleController

# Create new Vue component
# Manually create in resources/js/Pages/
```

## Troubleshooting

### White Screen / Blank Page
1. Clear browser cache (Ctrl+Shift+R or Cmd+Shift+R)
2. Rebuild assets: `npm run build`
3. Clear Laravel cache: `php artisan optimize:clear`
4. Check browser console (F12) for JavaScript errors

### Database Errors
```bash
# Reset database
php artisan migrate:fresh --seed
```

### Port Already in Use
```bash
# Use a different port
php artisan serve --port=8001
```

### Permission Errors
```bash
# Fix storage permissions (Mac/Linux)
chmod -R 775 storage bootstrap/cache
```

## Technology Stack

- **Backend**: Laravel 12.x (PHP 8.3)
- **Frontend**: Vue 3 + Inertia.js
- **Styling**: Tailwind CSS
- **Database**: SQLite (development)
- **Authentication**: Laravel Sanctum
- **Permissions**: Spatie Laravel Permission
- **Build Tool**: Vite
- **Testing**: PHPUnit

## Next Steps for Development

1. **Build Product Management UI**
   - Create `resources/js/Pages/Products/Index.vue`
   - Create `resources/js/Pages/Products/Create.vue`
   - Add routes in `routes/web.php`

2. **Build Sales Interface**
   - Create `resources/js/Pages/Sales/Index.vue`
   - Create `resources/js/Pages/Sales/Create.vue`
   - Integrate with InventoryService

3. **Build Dashboard with Charts**
   - Install Chart.js or similar
   - Add sales analytics
   - Add inventory summary

4. **Add Real-time Features**
   - Configure Laravel Echo
   - Add WebSocket support for live updates

## Support

For issues or questions:
- Check the Laravel documentation: https://laravel.com/docs
- Check the Inertia.js documentation: https://inertiajs.com
- Check the Vue 3 documentation: https://vuejs.org

## License

This project is open-source and available for educational purposes.
