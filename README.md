# Torball League Manager

PHP/MySQL Torball league system.

## Features

- Automatic league table
- Match schedule & results
- Goal difference
- Team statistics
- Admin panel
- MySQL/MariaDB support
- Responsive design

## Pages

- `/index.php` → League table
- `/matches.php` → Results
- `/stats.php` → Statistics
- `/admin/login.php` → Admin login

## Stack

- PHP 8
- MariaDB / MySQL
- HTML/CSS
- Bootstrap-ready structure

## Setup

```bash
mysql -u root -p torball_league < sql/schema.sql
mysql -u root -p torball_league < sql/seed.sql
```

Edit:

```php
config.php
```

Default admin:

```txt
admin / admin123
```

Change password immediately.
