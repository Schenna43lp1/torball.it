# Torball League Manager

PHP/MySQL Torball league system with Docker support.

## Features

- Automatic league table
- Match schedule & results
- Goal difference
- Team statistics
- Admin panel
- Live ticker
- Telegram bot integration
- MariaDB support
- Docker deployment
- phpMyAdmin included
- Responsive design

## Included Services

- PHP 8.3 Apache
- MariaDB 11
- phpMyAdmin
- Telegram notifications

## Pages

- `/index.php` → League table
- `/matches.php` → Results
- `/stats.php` → Statistics
- `/live.php` → Live ticker
- `/admin/login.php` → Admin login
- `/admin/live_ticker.php` → Live ticker management

## Quick Start

```bash
cp .env.example .env
nano .env
chmod +x install.sh
./install.sh
```

## Docker

```bash
docker compose up -d --build
```

## URLs

```txt
Web:         http://SERVER-IP:8080
phpMyAdmin: http://SERVER-IP:8081
```

## Default Admin

```txt
admin / admin123
```

Change password immediately.

## Telegram Setup

Edit `.env`

```env
TELEGRAM_BOT_TOKEN=
TELEGRAM_CHAT_ID=
```

## Future Features

- WebSocket live updates
- OBS live overlay
- REST API
- Mobile referee panel
- PWA support
- Match event timeline
- Push notifications
