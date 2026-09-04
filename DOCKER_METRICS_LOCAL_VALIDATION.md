# Docker Metrics Local Validation Guide

This document records the exact steps needed to validate the Laravel app can read live Docker container metrics locally via the Docker Engine API.

## Goal

The app should be able to read container metadata and runtime statistics from Docker and use them in the dashboard UI instead of demo-only values.

## Required local setup

1. Start the project containers:

```bash
cd /home/pittmans1/projects/demo
docker compose up -d
```

2. Confirm the Docker socket is mounted and visible inside the app container:

```bash
cd /home/pittmans1/projects/demo
docker compose exec app sh -lc 'ls -l /var/run/docker.sock && id && curl -sv --max-time 5 --unix-socket /var/run/docker.sock http://localhost/_ping 2>&1 | head -n 40'
```

Expected result:

- the socket exists
- the container has access to it
- the endpoint returns `HTTP/1.1 200 OK`

## Validate Docker Engine data access

Run this inside the app container:

```bash
cd /home/pittmans1/projects/demo
docker compose exec app php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class); \$kernel->bootstrap(); \$service = app(App\Services\DockerMetricsService::class); \$snapshot = \$service->snapshot(); echo json_encode(\$snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;"
```

Expected result:

- `source` should be `docker-engine`
- `containers` should be a non-empty array
- each container should include runtime fields like `state`, `status`, `cpu_percent`, `memory_percent`, `pids`, and `healthy`

Example shape:

```json
{
  "source": "docker-engine",
  "containers": [
    {
      "id": "5f081cb0497b",
      "name": "demo_app",
      "state": "running",
      "status": "Up 6 minutes",
      "cpu_percent": 0,
      "memory_percent": 0.49,
      "pids": 4,
      "healthy": true
    }
  ],
  "updated_at": "2026-09-04T19:07:30+00:00"
}
```

## Validate dashboard rendering

1. Open the app in the browser at:

```text
http://localhost:8000/dashboard
```

2. Confirm the `Runtime observability` widget no longer shows:

- `Demo mode`
- `0 active services`
- `0% healthy`

3. Confirm it displays actual Docker container data for running services.

## Important notes

- For local development, use the Unix socket path: `/var/run/docker.sock`
- Do not rely on `localhost` as a Docker daemon endpoint for local Docker; the socket is the correct local transport
- If a container shows `source: "error"` or an empty `containers` list, the most likely issue is the socket is not mounted or not reachable from the app container
- For production, use `DOCKER_HOST` with a managed Docker daemon endpoint rather than the local Unix socket

## Quick troubleshooting

If the snapshot still returns an error, re-check:

```bash
cd /home/pittmans1/projects/demo
docker compose up -d --no-deps --force-recreate app
docker compose exec app sh -lc 'ls -l /var/run/docker.sock && curl -sv --unix-socket /var/run/docker.sock http://localhost/_ping'
```

If the ping succeeds and the app still fails, re-run the Laravel snapshot command and inspect the exception path in the Docker metrics service.
