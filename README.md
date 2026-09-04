<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Run With Docker

Docker handles the PHP dependencies, frontend dependencies, PHP application, database, Redis, queue worker, scheduler, and Nginx web server. Production images build frontend assets; development uses the Vite `dev` profile.

### Prerequisites

- Docker Desktop with Docker Compose

### Quick start

Run these commands from the project root, where `docker-compose.yml` is located.

For the first boot, build the images and start every service:

```bash
docker compose up -d --build
```

For frontend development, use the Compose `dev` profile and Vite's live development server. You do not need to run `npm run build` while working locally:

```bash
docker compose --profile dev up -d frontend
```

The frontend is available at [http://localhost:5173](http://localhost:5173). The `frontend` service runs `npm run dev` only when the `dev` profile is enabled.

For a production-style run, omit the profile:

```bash
docker compose up -d --build
```

The production image builds assets with `npm run build` in the Dockerfile and does not start a Vite development server.

For later boots, start the existing services:

```bash
docker compose up -d
```

Check service status with:

```bash
docker compose ps
```

During `docker compose build app`:

- Composer runs `composer install` in the `vendor` build stage.
- npm runs `npm install` and `npm run build` in the `assets` build stage.
- The optional `frontend` service runs `npm install` and starts Vite only with `--profile dev`.
- The `reverb` service runs the WebSocket server.

The application is available at [http://localhost:8000](http://localhost:8000). Vite runs at [http://localhost:5173](http://localhost:5173) and watches frontend files for changes.

### Start the frontend with Docker

Start the Vite frontend service in development with:

```bash
docker compose --profile dev up -d frontend
docker compose up -d reverb
```

The service installs the npm packages automatically and runs:

```bash
npm run dev
```

To reinstall frontend dependencies manually:

```bash
docker compose run --rm frontend npm install
```

### Rebuild production frontend assets

The Vite service watches frontend files during development. To rebuild the production assets:

```bash
docker compose build app
docker compose up -d
```

Or do both in one command:

```bash
docker compose up -d --build
```

View service output with:

```bash
docker compose logs -f app web frontend
```

Stop the Docker services with:

```bash
docker compose down
```

## Operations and Integrations

The dashboard exposes tenant-scoped operations data at `/operations/stocks` and `/operations/docker`. Set `STOCK_API_KEY` to enable the Finnhub-compatible provider; without a key the UI uses deterministic demo quotes. Docker metrics are read-only and require the app container to mount `/var/run/docker.sock`.

Admin actions are policy protected and available under `/tenants/{tenant}/admin/*` for tenant members with the `owner` or `admin` role. The command endpoint accepts only `about`, `route:list`, and `queue:monitor`; arbitrary shell commands are intentionally unavailable.

The `/java/` Nginx route proxies to the internal Java health service on `java:8081`. Replace `java/Main.java` with the application you want to integrate while keeping its health contract.

### Architecture

```mermaid
flowchart LR
	Browser[Vue + Pinia] --> Nginx[Nginx]
	Nginx --> Laravel[Laravel PHP-FPM]
	Laravel --> MySQL[(MySQL)]
	Laravel --> Redis[(Redis / queues)]
	Laravel --> Reverb[Laravel Reverb]
	Laravel --> Docker[Docker Engine socket]
	Laravel --> Stocks[Stock provider]
	Nginx --> Java[Java service :8081]
```

For production, put web and Reverb behind TLS, use managed database and Redis services, configure the stock API key through the deployment secret store, and restrict Docker socket access to the metrics container only.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
