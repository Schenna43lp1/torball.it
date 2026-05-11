<?php
require __DIR__ . '/../config.php';

$file = __DIR__ . '/../data/results.txt';

if (!file_exists($file)) {
    exit("results.txt missing\n");
}

$content = file_get_contents($file);
$lines = explode("\n", $content);

$seasonId = 1;
$currentMatchday = null;
$matchdayId = null;
$round = 0;

foreach ($lines as $line) {
    $line = trim($line);

    if ($line === '' || str_starts_with($line, '#')) {
        continue;
    }

    if (preg_match('/^(\d+)° giornata - (.+?), (\d{2}\/\d{2}\/\d{4})$/i', $line, $m)) {
        $round = (int)$m[1];
        $location = trim($m[2]);
        $date = DateTime::createFromFormat('d/m/Y', $m[3])->format('Y-m-d');

        $stmt = $pdo->prepare('INSERT INTO matchdays (season_id, round_number, name, location, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $seasonId,
            $round,
            $round . 'a Giornata',
            $location,
            $date,
            $date
        ]);

        $matchdayId = (int)$pdo->lastInsertId();
        echo "Imported matchday {$round}\n";
        continue;
    }

    if (preg_match('/^\(Partita n\. (\d+)\) (.+?) - (.+?) : (\d+) - (\d+)/', $line, $m)) {
        $matchNo = (int)$m[1];
        $home = trim($m[2]);
        $away = trim($m[3]);
        $homeGoals = (int)$m[4];
        $awayGoals = (int)$m[5];

        $teamStmt = $pdo->prepare('SELECT id FROM teams WHERE name = ? LIMIT 1');

        $teamStmt->execute([$home]);
        $homeTeam = $teamStmt->fetchColumn();

        if (!$homeTeam) {
            $insert = $pdo->prepare('INSERT INTO teams (season_id, name, short_name) VALUES (?, ?, ?)');
            $insert->execute([$seasonId, $home, $home]);
            $homeTeam = $pdo->lastInsertId();
        }

        $teamStmt->execute([$away]);
        $awayTeam = $teamStmt->fetchColumn();

        if (!$awayTeam) {
            $insert = $pdo->prepare('INSERT INTO teams (season_id, name, short_name) VALUES (?, ?, ?)');
            $insert->execute([$seasonId, $away, $away]);
            $awayTeam = $pdo->lastInsertId();
        }

        $insertMatch = $pdo->prepare('INSERT INTO matches (id, matchday_id, home_team_id, away_team_id, home_goals, away_goals, match_status) VALUES (?, ?, ?, ?, ?, ?, ?)');

        $insertMatch->execute([
            $matchNo,
            $matchdayId,
            $homeTeam,
            $awayTeam,
            $homeGoals,
            $awayGoals,
            'played'
        ]);

        echo "Imported match {$matchNo}\n";
    }
}

echo "Done\n";
