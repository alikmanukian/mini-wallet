# Mini Wallet

A simplified digital wallet application that allows users to transfer money to each other. Built with Laravel 12, Inertia.js v2, Vue 3, and Tailwind CSS v4.

## Features

- **Laravel 12** - Latest Laravel framework
- **Inertia.js v2** - Modern monolith with Vue 3
- **Tailwind CSS v4** - Utility-first CSS framework
- **Laravel Fortify** - Headless authentication backend
- **Wayfinder** - Type-safe routing between backend and frontend
- **Pusher** - Real-time broadcasting
- **Pest** - Elegant PHP testing framework

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js 18+ and NPM
- SQLite (default) or MySQL/PostgreSQL

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/alikmanukian/mini-wallet.git
cd mini-wallet
```

### 2. Install Dependencies

You can use the automated setup script:

```bash
composer run setup
```

This will:
- Install PHP dependencies
- Copy `.env.example` to `.env`
- Generate application key
- Run database migrations
- Install Node.js dependencies
- Build frontend assets

### 3. Manual Installation (Alternative)

If you prefer manual installation:

```bash
# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Install Node.js dependencies
npm install

# Build frontend assets
npm run build
```

## Configuration

### Database

By default, the application uses SQLite. The database file will be created at `database/database.sqlite`.

To use MySQL or PostgreSQL, update your `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini_wallet
DB_USERNAME=root
DB_PASSWORD=
```

### Broadcasting (Optional)

If you want to use real-time features, configure Pusher in your `.env`:

```env
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1
```

## Running the Application

### Development Mode

The easiest way to run the application in development:

```bash
composer run dev
```

This will start:
- PHP development server (http://localhost:8000)
- Queue worker
- Log viewer
- Vite development server

### Alternative: Run Services Individually

```bash
# Terminal 1: Start the development server
php artisan serve

# Terminal 2: Start the queue worker
php artisan queue:listen

# Terminal 3: Build frontend assets
npm run dev
```

Visit http://localhost:8000 in your browser.

### Production Build

```bash
npm run build
```


## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
