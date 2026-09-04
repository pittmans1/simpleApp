Nginx Reverse Proxy Configuration

This file contains the Nginx configuration used to route all backend services under a single domain. It is written to be beginner‑friendly and easy to reference later.

📌 Overview

Your domain:

yourdomain.com

Backend routing:

Path

Service

Description

/

PHP (Laravel)

Main application

/java/

Java backend

Microservice

/python/

Python backend

AI/ML or worker service

Nginx acts as a reverse proxy and forwards requests to the correct container.

📌 Nginx Configuration

Save this file as nginx.prod.conf or default.conf inside your Docker nginx folder.

server {
    listen 80;
    server_name yourdomain.com;

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

📌 Notes for Beginners

proxy_pass tells Nginx which container to forward traffic to.

app, java, and python are the service names from your Docker Compose file.

Ports (9000, 8081, 5000) must match the ports exposed by each container.

The trailing slash in proxy_pass http://java:8081/; ensures correct path forwarding.

This file is used only in production.

📌 Summary

This Nginx configuration allows you to:

Host multiple backend services under one domain

Avoid CORS issues

Keep routing clean and simple

Scale your platform easily

Use this file whenever you deploy your application to a Linux server.