RUN.md — Complete End‑to‑End Guide (3 Backends, Azure, HTTPS, CI/CD, Safe & Secure)

This file is your single source of truth for:

Local development (Windows + WSL2 + Docker Desktop)

Production deployment (Linux server / Azure VM)

3 backends: Laravel (PHP), Java, Python

Nginx reverse proxy (one domain, three services)

HTTPS (Let’s Encrypt)

CI/CD (GitHub Actions → Azure VM)

Domain purchase + DNS

Health checks, restart policies, basic security

Follow this step‑by‑step. Don’t skip. Don’t improvise. If you do exactly this, your app will run, be reachable, and be reasonably secure.

PART 0 — Folder Structure (Start Here)

Make your project look like this:

yourproject/
  app/                 # Laravel app (or root if you keep it here)
  java/
    Dockerfile
    src/...
  python/
    Dockerfile
    src/...
  docker/
    nginx.prod.conf
    nginx.local.conf (optional)
    certs/            # for HTTPS certs (mounted from host)
  docker-compose.local.yml
  docker-compose.prod.yml
  .env
  .env.local.example
  .env.prod.example
  .github/
    workflows/
      deploy.yml
  docs/
    local_setup.md
    setup.md
    nginx.md
    RUN.md

You can adjust names, but keep this structure idea.

PART 1 — Local Development (Windows + WSL2 + Docker Desktop)

1. Requirements

You MUST have:

Windows 10/11

WSL2 enabled

Docker Desktop installed

2. Enable Docker TCP Support

Open Docker Desktop → Settings → General → enable:

Expose daemon on tcp://localhost:2375 without TLS

This is REQUIRED for WSL2 to talk to Docker Desktop.

3. Local .env Configuration

In your .env file (local):

DOCKER_HOST=http://host.docker.internal:2375
DOCKER_SOCKET=

This tells Laravel to use TCP instead of the Unix socket.

4. Local Docker Compose File (docker-compose.local.yml)

Example:

version: "3.9"

services:
  app:
    build: .
    container_name: demo_app_local
    environment:
      DOCKER_HOST: http://host.docker.internal:2375
    volumes:
      - .:/var/www/html
    networks:
      - backend

  java:
    build: ./java
    container_name: demo_java_local
    expose:
      - "8081"
    networks:
      - backend

  python:
    build: ./python
    container_name: demo_python_local
    expose:
      - "5000"
    networks:
      - backend

  web:
    image: nginx:1.27-alpine
    container_name: demo_web_local
    ports:
      - "8000:80"
    volumes:
      - .:/var/www/html:ro
      - ./docker/nginx.local.conf:/etc/nginx/conf.d/default.conf:ro
    depends_on:
      - app
      - java
      - python
    networks:
      - backend

networks:
  backend:
    driver: bridge

5. Local Nginx Config (docker/nginx.local.conf)

server {
    listen 80;
    server_name localhost;

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

6. Run Local Stack

In your project folder:

docker compose -f docker-compose.local.yml up --build

To rebuild:

docker compose -f docker-compose.local.yml down -v
docker compose -f docker-compose.local.yml up --build

7. Local URLs

http://localhost:8000/        → Laravel (PHP)
http://localhost:8000/java/   → Java backend
http://localhost:8000/python/ → Python backend

If these load, local is good.

PART 2 — Production Docker Compose (Linux / Azure VM)

1. Production .env Configuration

In your production .env:

DOCKER_SOCKET=/var/run/docker.sock
DOCKER_HOST=

2. Install Docker + Docker Compose on Server

On your Linux server (Ubuntu recommended):

sudo apt update
sudo apt install docker.io -y
sudo apt install docker-compose -y

3. Production Docker Compose (docker-compose.prod.yml)

version: "3.9"

services:
  app:
    build: .
    container_name: demo_app
    environment:
      DOCKER_SOCKET: /var/run/docker.sock
    volumes:
      - .:/var/www/html
      - /var/run/docker.sock:/var/run/docker.sock
    networks:
      - backend
    restart: always
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost:9000"] # adjust to your app
      interval: 30s
      timeout: 10s
      retries: 3

  java:
    build: ./java
    container_name: demo_java
    expose:
      - "8081"
    networks:
      - backend
    restart: always
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost:8081/health"] # implement /health
      interval: 30s
      timeout: 10s
      retries: 3

  python:
    build: ./python
    container_name: demo_python
    expose:
      - "5000"
    networks:
      - backend
    restart: always
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost:5000/health"] # implement /health
      interval: 30s
      timeout: 10s
      retries: 3

  web:
    image: nginx:1.27-alpine
    container_name: demo_web
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - .:/var/www/html:ro
      - ./docker/nginx.prod.conf:/etc/nginx/conf.d/default.conf:ro
      - /etc/letsencrypt:/etc/letsencrypt:ro
    depends_on:
      - app
      - java
      - python
    networks:
      - backend
    restart: always

networks:
  backend:
    driver: bridge

4. Health Endpoints (Java & Python)

In your Java and Python apps, add simple /health endpoints that return 200 OK and a small JSON like {"status":"ok"}. This makes health checks meaningful.

PART 3 — Nginx Reverse Proxy (HTTP + HTTPS)

1. Basic HTTP → HTTPS Redirect + Backend Routing

docker/nginx.prod.conf:

server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name yourdomain.com;

    ssl_certificate     /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    # Laravel (PHP)
    location / {
        proxy_pass http://app:9000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Java backend
    location /java/ {
        proxy_pass http://java:8081/;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Python backend
    location /python/ {
        proxy_pass http://python:5000/;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

PART 4 — HTTPS with Let’s Encrypt (Certbot)

1. Install Nginx + Certbot on Host

On your server (host, not inside container):

sudo apt update
sudo apt install nginx certbot python3-certbot-nginx -y

2. Temporary Nginx for Certbot

You can let certbot manage host Nginx just to get certs:

sudo certbot --nginx -d yourdomain.com

This will create certs at:

/etc/letsencrypt/live/yourdomain.com/

3. Use Certs in Docker Nginx

In docker-compose.prod.yml, we already mounted:

volumes:
  - /etc/letsencrypt:/etc/letsencrypt:ro

So your container Nginx can read the certs.

Restart:

docker compose -f docker-compose.prod.yml up -d

Now https://yourdomain.com should work.

PART 5 — Domain Purchase + DNS (Idiot‑Proof)

1. Buy a Domain

Use:

Namecheap

GoDaddy

Cloudflare

Example: yourdomain.com.

2. Get Server Public IP

In Azure:

Go to your VM

Copy Public IP (e.g., 20.50.123.10)

3. Set DNS A Record

In your domain DNS settings:

Type: A
Host: @
Value: YOUR_SERVER_IP
TTL: 300

Optional:

Type: A
Host: www
Value: YOUR_SERVER_IP
TTL: 300

4. Wait for DNS Propagation

Can take 5–30 minutes. Test:

ping yourdomain.com

If it resolves to your server IP, DNS is good.

PART 6 — CI/CD (GitHub Actions → Azure VM)

1. Secrets in GitHub

In GitHub → Settings → Secrets → Actions, add:

REGISTRY_URL

REGISTRY_USER

REGISTRY_PASSWORD

SERVER_IP

SERVER_USER

SERVER_SSH_KEY (private key for SSH)

2. GitHub Actions Workflow (.github/workflows/deploy.yml)

name: Deploy to Azure VM

on:
  push:
    branches: [ "main" ]

jobs:
  build-and-deploy:
    runs-on: ubuntu-latest

    steps:
    - name: Checkout
      uses: actions/checkout@v4

    - name: Login to Docker Registry
      run: echo ${{ secrets.REGISTRY_PASSWORD }} | docker login ${{ secrets.REGISTRY_URL }} -u ${{ secrets.REGISTRY_USER }} --password-stdin

    - name: Build images
      run: |
        docker build -t ${{ secrets.REGISTRY_URL }}/demo_app:latest .
        docker build -t ${{ secrets.REGISTRY_URL }}/demo_java:latest ./java
        docker build -t ${{ secrets.REGISTRY_URL }}/demo_python:latest ./python

    - name: Push images
      run: |
        docker push ${{ secrets.REGISTRY_URL }}/demo_app:latest
        docker push ${{ secrets.REGISTRY_URL }}/demo_java:latest
        docker push ${{ secrets.REGISTRY_URL }}/demo_python:latest

    - name: Deploy to Azure VM
      uses: appleboy/ssh-action@v1.0.0
      with:
        host: ${{ secrets.SERVER_IP }}
        username: ${{ secrets.SERVER_USER }}
        key: ${{ secrets.SERVER_SSH_KEY }}
        script: |
          cd /var/www/yourproject
          git pull
          docker compose -f docker-compose.prod.yml pull
          docker compose -f docker-compose.prod.yml up -d

This gives you:

Auto build on push to main

Auto deploy to Azure VM

Zero‑downtime updates via docker compose up -d

PART 7 — Security & Safety Basics

This is not “bank‑level” security, but it’s solid for a typical app.

1. Use HTTPS Everywhere

Force HTTP → HTTPS redirect (already in Nginx config).

Never serve login or sensitive pages over plain HTTP.

2. Environment Variables

Never commit real .env to Git.

Use .env.local.example and .env.prod.example as templates.

Store secrets in:

.env on server

GitHub Secrets for CI/CD

3. Health Checks

Implement /health endpoints in Java and Python.

Optionally add /health or /status in Laravel.

Use them for monitoring and uptime checks.

4. Restart Policies

restart: always in Docker Compose ensures services come back after crashes or reboots.

5. Basic Monitoring

You can later add:

docker logs checks

Simple uptime monitors (e.g., UptimeRobot) hitting /health endpoints.

PART 8 — Final Verification Checklist

Local

docker compose -f docker-compose.local.yml up --build

Visit http://localhost:8000/

Visit http://localhost:8000/java/

Visit http://localhost:8000/python/

Production (Server IP)

docker compose -f docker-compose.prod.yml up -d

docker ps shows app, java, python, web

Visit http://YOUR_SERVER_IP/ (before HTTPS)

After HTTPS: visit https://YOUR_SERVER_IP/ (if you test via IP)

Domain

DNS A record points yourdomain.com → server IP

ping yourdomain.com resolves correctly

http://yourdomain.com redirects to https://yourdomain.com

https://yourdomain.com/ loads Laravel

https://yourdomain.com/java/ loads Java backend

https://yourdomain.com/python/ loads Python backend

CI/CD

Push to main

GitHub Actions runs build-and-deploy

Azure VM pulls new images and restarts stack

App updates without downtime

PART 9 — What’s Next (Optional Upgrades)

Once all this works, you can later add:

Rate limiting in Nginx

WAF / firewall rules

Centralized logging (ELK, Loki, etc.)

Scaling beyond one VM (Kubernetes, Azure Container Apps)

You now have literally everything from:

Local dev

3 backends

Nginx

HTTPS

Azure VM

CI/CD

Domain + DNS

Health checks

Basic security

Save this RUN.md and treat it like your deployment bible.