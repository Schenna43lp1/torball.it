#!/bin/bash

set -e

echo "[+] Starting Torball Stack"

if [ ! -f .env ]; then
  cp .env.example .env
  echo "[+] Created .env from template"
fi

docker compose down -v || true

docker compose up -d --build

echo ""
echo "Torball started"
echo ""
echo "Frontend:    http://SERVER-IP:8080"
echo "Admin:       http://SERVER-IP:8080/admin/login.php"
echo "phpMyAdmin: http://SERVER-IP:8081"
echo "API:         http://SERVER-IP:8082/api/health"
