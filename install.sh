#!/bin/bash

set -e

echo "[+] Starting Torball League Stack"

docker compose up -d --build

echo "[+] Waiting for MariaDB..."
sleep 15

echo "[+] Importing schema"
docker exec -i torball-db mysql -utorball -ptorballpass torball_league < sql/schema.sql

echo "[+] Importing seed data"
docker exec -i torball-db mysql -utorball -ptorballpass torball_league < sql/seed.sql

echo "[+] Importing live ticker schema"
docker exec -i torball-db mysql -utorball -ptorballpass torball_league < sql/live_ticker.sql

echo ""
echo "Torball League started"
echo ""
echo "Web:         http://SERVER-IP:8080"
echo "phpMyAdmin: http://SERVER-IP:8081"
echo ""
