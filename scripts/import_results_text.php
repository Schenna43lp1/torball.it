<?php
require __DIR__ . '/../config.php';

$file = __DIR__ . '/../data/results.txt';

if (!file_exists($file)) {
    exit("results.txt missing\n");
}

$content = file_get_contents($file);
$lines = explode("\n", $content);

$seasonId = 1;
$matchdayId = null;

$pdo->exec("INSERT INTO seasons (id, name) VALUES (1, 'Serie A 2026') ON DUPLICATE KEY UPDATE name = VALUES(name)");

foreach ($lines as $line) {
    $line = trim($line);

    if ($line === '' || str_starts_with($line, '#') || strtolower($line) === 'incontri') {
        continue;
    }

    if (preg_match('/^(\d+)° giornata - (.+?), (\d{2}\/\d{2}\/\d{4})$/i', $line, $m)) {
        $round = (int)$m[1];
        $location = trim($m[2]);
        $date = DateTime::createFromFormat('d/m/Y', $m[3])->format('Y-m-d');

        $stmt = $pdo->prepare('SELECT id FROM matchdays WHERE season_id = ? AND round_number = ? AND location = ? AND start_date = ? LIMIT 1');
        $stmt->execute([$seasonId, $round, $location, $date]);
        $existing = $stmt->fetchColumn();

        if ($existing) {
            $matchdayId = (int)$existing;
        } else {
            $insert = $pdo->prepare('INSERT INTO matchdays (season_id, round_number, name, location, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)');
            $insert->execute([
                $seasonId,
                $round,
                $round . 'a Giornata',
                $location,
                $date,
                $date
            ]);
            $matchdayId = (int)$pdo->lastInsertId();
        }

        echo "Matchday ready: {$round} {$location}\n";
        continue;
    }

    if (preg_match('/^\(Partita n\. (\d+)\) (.+?) - (.+?) : (\d+) - (\d+)/', $line, $m)) {
        if ($matchdayId === null) {
            echo "Skipping match without matchday: {$line}\n";
            continue;
        }

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

        $insertMatch = $pdo->prepare('
            INSERT INTO matches (id, matchday_id, home_team_id, away_team_id, home_goals, away_goals, match_status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                matchday_id = VALUES(matchday_id),
                home_team_id = VALUES(home_team_id),
                away_team_id = VALUES(away_team_id),
                home_goals = VALUES(home_goals),
                away_goals = VALUES(away_goals),
                match_status = VALUES(match_status)
        ');

        $insertMatch->execute([
            $matchNo,
            $matchdayId,
            $homeTeam,
            $awayTeam,
            $homeGoals,
            $awayGoals,
            'played'
        ]);

        echo "Match imported/updated: {$matchNo}\n";
    }
}

echo "Done\n";
