<?php require __DIR__ . '/config.php'; ?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Spiele</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header>
    <h1>Spiele</h1>
</header>
<main>
    <table id="matches">
        <thead>
            <tr>
                <th>ID</th>
                <th>Heim</th>
                <th>Ergebnis</th>
                <th>Auswärts</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5">Spiele werden geladen…</td>
            </tr>
        </tbody>
    </table>
</main>
<script>
const apiBase = '';

function formatScore(match) {
    const homeGoals = match.home_goals ?? '-';
    const awayGoals = match.away_goals ?? '-';

    return `${homeGoals}:${awayGoals}`;
}

async function loadMatches() {
    const tbody = document.querySelector('#matches tbody');

    try {
        const response = await fetch(apiBase + '/api/matches');
        const matches = await response.json();

        tbody.innerHTML = '';

        if (matches.length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 5;
            cell.textContent = 'Keine Spiele vorhanden.';
            row.appendChild(cell);
            tbody.appendChild(row);
            return;
        }

        matches.forEach((match) => {
            const row = document.createElement('tr');
            [
                match.id,
                match.home_team,
                formatScore(match),
                match.away_team,
                match.match_status,
            ].forEach((value) => {
                const cell = document.createElement('td');
                cell.textContent = String(value ?? '-');
                row.appendChild(cell);
            });
            tbody.appendChild(row);
        });
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="5">Spiele konnten nicht geladen werden.</td></tr>';
    }
}

loadMatches();
</script>
</body>
</html>
