Local Development Setup (WSL2 + Docker Desktop)

This guide explains how to run the full multi‑backend stack locally using WSL2 and Docker Desktop. It is written for beginners and intermediate developers so you can reference it anytime.

📌 1. Requirements

Local development uses TCP instead of the Docker Unix socket.

Enable this in Docker Desktop:

Expose daemon on tcp://localhost:2375 without TLS

This allows containers to communicate with Docker Desktop’s engine.

📌 2. Local .env Configuration

Add these values:

DOCKER_HOST=http://host.docker.internal:2375
DOCKER_SOCKET=

This forces Laravel to use TCP instead of the Unix socket.

📌 3. Local Docker Compose File

Use:

docker-compose.local.yml

This file:

Removes the Unix socket mount

Uses TCP for Docker metrics

Runs PHP, Java, Python, and Nginx containers

📌 4. Start Local Environment

To start:

docker compose -f docker-compose.local.yml up --build

To rebuild:

docker compose -f docker-compose.local.yml down -v
docker compose -f docker-compose.local.yml up --build

📌 5. Local Routing

http://localhost:8000/        → Laravel (PHP)
http://localhost:8000/java/   → Java backend
http://localhost:8000/python/ → Python backend

📌 6. Troubleshooting

❗ Error: source => "error"

Fix:

Ensure Docker Desktop TCP API is enabled

Restart Docker Desktop

Rebuild containers

📌 7. Summary

Local development uses:

TCP for Docker metrics

WSL2 backend

No Unix socket

docker-compose.local.yml

This setup ensures your app works perfectly on Windows + WSL2.