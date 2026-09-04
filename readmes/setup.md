Production Setup (Linux Server)

This guide explains how to deploy the multi‑backend stack on a real Linux server. It is written to be beginner‑friendly so you can reference it anytime.

📌 1. Requirements

Your server must have:

Docker Engine installed

/var/run/docker.sock available

Permissions allowing containers to read the socket

This is required for Docker metrics and container monitoring.

📌 2. Production .env Configuration

Add these values:

DOCKER_SOCKET=/var/run/docker.sock
DOCKER_HOST=

This forces Laravel to use the Unix socket instead of TCP.

📌 3. Production Docker Compose File

Use:

docker-compose.prod.yml

This file:

Mounts the Unix socket

Runs PHP, Java, Python, and Nginx containers

Supports CI/CD deployments

📌 4. Start Production Environment

To start:

docker compose -f docker-compose.prod.yml up --build -d

To update:

docker compose -f docker-compose.prod.yml pull
docker compose -f docker-compose.prod.yml up -d

📌 5. Nginx Reverse Proxy

This routes all traffic under one domain:

server {
    listen 80;
    server_name yourdomain.com;

    location / {
        proxy_pass http://app:9000;
    }

    location /java/ {
        proxy_pass http://java:8081/;
    }

    location /python/ {
        proxy_pass http://python:5000/;
    }
}

📌 6. CI/CD Deployment Flow

Push to main

GitHub Actions builds images

Images pushed to registry

Server pulls new images

Server runs production compose file

Nginx routes traffic to updated containers

This provides zero‑downtime deployments.

📌 7. Troubleshooting

❗ Error: source => "error"

Check:

/var/run/docker.sock exists

Socket mount is present

Container user has permission

📌 8. Summary

Production uses:

Unix socket

docker-compose.prod.yml

Nginx reverse proxy

CI/CD automated deployments

This setup ensures your app runs reliably on any Linux server.