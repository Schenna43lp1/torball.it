<?php
require __DIR__ . '/../config.php';
require_admin();

$username = $_SESSION['admin_username'] ?? 'Admin';
$role = $_SESSION['admin_role'] ?? 'admin';
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header>
    <h1>Torball Admin Dashboard</h1>
    <nav>
        <a href="matches.php">Matches</a>
        <a href="live_ticker.php">Live-Ticker</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>
<main>
    <div class="card">
        <h2>Willkommen <?= e($username) ?></h2>
        <p>Rolle: <?= e($role) ?></p>
    </div>

    <div class="card">
        <h2>System</h2>
        <ul>
            <li>PHP Frontend</li>
            <li>Go API</li>
            <li>Redis Cache</li>
            <li>WebSocket Live System</li>
            <li>MariaDB</li>
        </ul>
    </div>
</main>
</body>
</html>
