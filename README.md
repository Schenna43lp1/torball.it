# Torball.it — Realtime Torball League Platform

Eine moderne, Docker-basierte Torball Liga- und Live-Score Plattform mit:

- ⚡ Realtime Live Scores
- 📊 automatische Tabellenberechnung
- 📱 Progressive Web App (PWA)
- 🔴 WebSocket Live Ticker
- 🚀 Redis Live Cache
- 🐳 Docker Production Stack
- 🔐 Admin Panel
- 🌙 Darkmode
- 📡 Push Notifications
- 📈 REST API
- 🏆 Multi-Matchday Support

---

# Features

## Liga & Ergebnisse

- Spieltage
- Tabellenberechnung
- Live Ergebnisse
- Match Importer
- Teamverwaltung
- Saisonverwaltung
- Mobile optimiert

---

## Realtime Features

- WebSocket Live Scores
- Match Rooms
- Redis Pub/Sub
- Auto Refresh
- Live Tabellen
- Push Notifications
- Offline Cache

---

## PWA / Mobile

- installierbare App
- Offline Support
- Homescreen Modus
- Darkmode
- Responsive Navbar

---

## Backend

- Go API (Gin)
- MariaDB
- Redis
- WebSocket Server
- PHP Frontend
- Docker Compose

---

# Architektur

```txt
Browser / Mobile App
        │
        ▼
     Caddy
        │
 ┌──────┴──────┐
 ▼             ▼
Frontend      API
(PHP)         (Go)
 │             │
 └──────┬──────┘
        ▼
      Redis
        │
        ▼
    WebSocket
        │
        ▼
     MariaDB
```

---

# Docker Services

| Service | Beschreibung |
|---|---|
| torball-web | PHP Frontend |
| torball-api | Go REST API |
| websocket | Live WebSocket Server |
| redis | Live Cache + Pub/Sub |
| torball-db | MariaDB |
| caddy | Reverse Proxy + HTTPS |

---

# Schnellstart

## Repository klonen

```bash
git clone https://github.com/Schenna43lp1/torball.it.git
cd torball.it
```

---

## .env erstellen

```env
MYSQL_ROOT_PASSWORD=CHANGE_ME
DB_NAME=torball_league
DB_USER=torball
DB_PASS=CHANGE_ME

JWT_SECRET=CHANGE_ME

ALLOWED_ORIGINS=https://torball.it

TELEGRAM_BOT_TOKEN=
TELEGRAM_CHAT_ID=

WS_BROADCAST_SECRET=CHANGE_ME
```

---

## Development starten

```bash
docker compose up -d --build
```

---

## Production starten

```bash
docker compose -f docker-compose.prod.yml up -d --build
```

---

# API Endpoints

## Health

```txt
GET /api/health
```

---

## Tabelle

```txt
GET /api/table
```

---

## Matches

```txt
GET /api/matches
```

---

## Match Details

```txt
GET /api/matches/:id
```

---

# WebSocket

## Verbindung

```txt
ws://localhost:3001
```

---

## Match Room joinen

```json
{
  "type": "join_room",
  "room": "match_112"
}
```

---

## Broadcast senden

```bash
curl -X POST http://localhost:3001/broadcast \
-H "x-broadcast-secret: SECRET" \
-H "Content-Type: application/json" \
-d '{
  "type":"goal",
  "room":"match_112",
  "message":"GOAL Alto Adige 2"
}'
```

---

# Redis Cache

Genutzt für:

- Tabellen Cache
- Match Cache
- Live Events
- Pub/Sub
- WebSocket Scaling

---

# PWA Features

- installierbar
- Offline Cache
- App Modus
- Push Notifications
- Live Refresh

---

# CI/CD

GitHub Actions unterstützt:

- Auto Deploy
- Docker Rebuild
- Healthchecks
- Rollback Vorbereitung

---

# Geplante Features

- GraphQL API
- OBS Overlay
- Spielerstatistiken
- Schiedsrichter-Modul
- Live Spieluhr
- Admin Audit Logs
- Multi-League Support
- Sponsorenmodule
- Streaming Integration

---

# Sicherheit

- interne Docker Netzwerke
- Redis intern
- MariaDB nicht öffentlich
- WebSocket Secret
- HTTPS via Caddy
- Rate Limiting geplant

---

# Lizenz

MIT License

---

# Entwickler

Projekt von Markus Stuefer  
🌐 markusstuefer.com  
🏆 BSSG Südtirol / Torball Serie A
