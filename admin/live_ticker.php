<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../telegram.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $pdo->prepare(
        "INSERT INTO live_ticker (match_id, message) VALUES (?, ?)"
    );

    $stmt->execute([
        (int)$_POST['match_id'],
        trim($_POST['message'])
    ]);

    $telegramMessage = "📢 <b>Torball Live-Ticker</b>\n\n" . trim($_POST['message']);
    send_telegram_message($telegramMessage);

    header('Location: live_ticker.php');
    exit;
}

$matches = $pdo->query("
    SELECT m.id, ht.name AS home_team, at.name AS away_team
    FROM matches m
    JOIN teams ht ON ht.id = m.home_team_id
    JOIN teams at ON at.id = m.away_team_id
    ORDER BY m.id DESC
")->fetchAll();
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Live-Ticker Admin</title>
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<header>
    <h1>Live-Ticker Verwaltung</h1>
</header>
<main>
<form method="post">
    <label>Spiel</label>
    <select name="match_id" required>
        <?php foreach ($matches as $match): ?>
            <option value="<?= (int)$match['id'] ?>">
                <?= e($match['home_team']) ?> vs <?= e($match['away_team']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Nachricht</label>
    <textarea name="message" rows="5" required></textarea>

    <button type="submit">Senden</button>
</form>
</main>
</body>
</html>
