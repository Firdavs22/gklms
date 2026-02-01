# GloboKids LMS

Платформа для онлайн-обучения.

## Технологии

- Laravel 12
- Filament Admin Panel
- SQLite / MySQL / PostgreSQL

## Установка

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

## Доступ к админке

`/admin`
