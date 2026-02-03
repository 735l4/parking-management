# Parking Management System

A simple parking management system built with Laravel and Filament.

## Requirements

- PHP 8.4+
- Composer
- MySQL
- Node.js & NPM

## Installation

1. Clone the repository and install dependencies:

```bash
composer install
npm install
```

2. Copy environment file and generate app key:

```bash
cp .env.example .env
php artisan key:generate
```

3. Configure your database in `.env` file:

```
DB_DATABASE=parking
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

4. Run migrations with seeding:

```bash
php artisan migrate --seed
php artisan shield:generate --all --panel=admin
```

5. Build frontend assets:

```bash
npm run build
```

6. Start the development server:

```bash
php artisan serve
```

## Default Users

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@parking.local | password |
| Staff | staff@parking.local | password |
