<?php
require __DIR__ . '/../config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $stmt = $pdo->prepare(
        'UPDATE matches SET home_goals=?, away_goals=?, match_status="played" WHERE id=?'
    );

    $stmt->execute([
        (int)$_POST['home_goals'],
        (int)$_POST['away_goals'],
        (int)$_POST['match_id']
    ]);

    header('Location: matches.php');
    exit;
}

$matches = $pdo->query('
    SELECT
        m.id,
        ht.name AS home_team,
        at.name AS away_team,
        m.home_goals,
        m.away_goals,
        m.match_status
    FROM matches m
    JOIN teams ht ON ht.id = m.home_team_id
    JOIN teams at ON at.id = m.away_team_id
    ORDER BY m.id DESC
')->fetchAll();
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Match Verwaltung</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header>
    <h1>Matches verwalten</h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>
<main>
<?php foreach ($matches as $match): ?>
<form method="post" class="match-form">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="match_id" value="<?= (int)$match['id'] ?>">

    <div><?= e($match['home_team']) ?></div>

    <input type="number" name="home_goals" value="<?= e((string)$match['home_goals']) ?>">

    <div>:</div>

    <input type="number" name="away_goals" value="<?= e((string)$match['away_goals']) ?>">

    <div><?= e($match['away_team']) ?></div>

    <button type="submit">Speichern</button>
</form>
<?php endforeach; ?>
</main>
</body>
</html>
