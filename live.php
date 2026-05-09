<?php
require __DIR__ . '/config.php';

$ticker = $pdo->query("
    SELECT lt.*, ht.name AS home_team, at.name AS away_team
    FROM live_ticker lt
    JOIN matches m ON m.id = lt.match_id
    JOIN teams ht ON ht.id = m.home_team_id
    JOIN teams at ON at.id = m.away_team_id
    ORDER BY lt.created_at DESC
    LIMIT 50
")->fetchAll();
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Live-Ticker</title>
    <meta http-equiv="refresh" content="15">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<header>
    <h1>Torball Live-Ticker</h1>
</header>
<main>
<?php foreach ($ticker as $entry): ?>
<div style="background:white;padding:15px;margin-bottom:10px;border-radius:8px;">
    <strong><?= e($entry['home_team']) ?> vs <?= e($entry['away_team']) ?></strong>
    <p><?= nl2br(e($entry['message'])) ?></p>
    <small><?= e($entry['created_at']) ?></small>
</div>
<?php endforeach; ?>
</main>
</body>
</html>
