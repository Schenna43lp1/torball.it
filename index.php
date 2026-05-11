<?php require __DIR__ . '/config.php'; ?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>BSSG Südtirol - Torball</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Offizielle Torball-Übersicht für BSSG Südtirol mit Tabelle, Ergebnissen, Live-Ticker und Statistiken.">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="hero">
    <nav class="topnav">
        <strong>BSSG Südtirol</strong>
        <div>
            <a href="index.php">Start</a>
            <a href="matches.php">Spiele</a>
            <a href="stats.php">Statistiken</a>
            <a href="live.php">Live</a>
            <a href="admin/login.php">Admin</a>
        </div>
    </nav>

    <section class="hero-content">
        <p class="eyebrow">Torball • Südtirol • Serie A</p>
        <h1>BSSG Südtirol Torball</h1>
        <p>
            Aktuelle Ergebnisse, Tabelle, Live-Ticker und Statistiken für die Torball-Saison.
            Schnell, übersichtlich und bereit für Spieltage.
        </p>
        <div class="hero-actions">
            <a class="btn" href="matches.php">Spiele ansehen</a>
            <a class="btn secondary" href="live.php">Live-Ticker</a>
        </div>
    </section>
</header>

<main>
    <section class="grid cards-overview">
        <div class="card highlight">
            <h2>BSSG Südtirol 1</h2>
            <p>Teamübersicht, Ergebnisse und Saisonleistung der ersten Mannschaft.</p>
        </div>
        <div class="card highlight">
            <h2>BSSG Südtirol 2</h2>
            <p>Aktuelle Resultate, direkte Duelle und Tabellenposition der zweiten Mannschaft.</p>
        </div>
        <div class="card highlight">
            <h2>Live am Spieltag</h2>
            <p>Live-Meldungen, Zwischenstände und wichtige Ereignisse direkt im Browser.</p>
        </div>
    </section>

    <section class="card">
        <div class="section-head">
            <div>
                <h2>Aktuelle Tabelle</h2>
                <p>Automatisch berechnet aus den eingetragenen Ergebnissen.</p>
            </div>
            <span id="api-status" class="badge">API lädt…</span>
        </div>

        <div class="table-wrap">
            <table id="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Team</th>
                        <th>Sp</th>
                        <th>S</th>
                        <th>U</th>
                        <th>N</th>
                        <th>Tore</th>
                        <th>Diff</th>
                        <th>Punkte</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="9">Tabelle wird geladen…</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="grid">
        <div class="card">
            <h2>Spielplan</h2>
            <p>Alle Spieltage und Ergebnisse der Saison.</p>
            <a class="text-link" href="matches.php">Zu den Spielen →</a>
        </div>
        <div class="card">
            <h2>Statistiken</h2>
            <p>Offensive, Defensive, Punkte, Torverhältnis und weitere Kennzahlen.</p>
            <a class="text-link" href="stats.php">Statistiken öffnen →</a>
        </div>
        <div class="card">
            <h2>Adminbereich</h2>
            <p>Ergebnisse verwalten, Live-Ticker bedienen und Daten pflegen.</p>
            <a class="text-link" href="admin/login.php">Admin Login →</a>
        </div>
    </section>
</main>

<footer>
    <p>© BSSG Südtirol • Torball System</p>
</footer>

<script>
const apiBase = 'http://' + location.hostname + ':8082';

async function loadHealth() {
    const badge = document.getElementById('api-status');
    try {
        const res = await fetch(apiBase + '/api/health');
        const data = await res.json();
        badge.textContent = data.status === 'ok' ? 'API online' : 'API prüfen';
        badge.className = data.status === 'ok' ? 'badge ok' : 'badge warn';
    } catch (error) {
        badge.textContent = 'API offline';
        badge.className = 'badge error';
    }
}

async function loadTable() {
    const tbody = document.querySelector('#table tbody');
    try {
        const res = await fetch(apiBase + '/api/table');
        const data = await res.json();
        tbody.innerHTML = '';

        if (!Array.isArray(data) || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9">Noch keine Tabellendaten vorhanden.</td></tr>';
            return;
        }

        let rank = 1;
        data.forEach(team => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${rank++}</td>
                <td><strong>${team.team}</strong></td>
                <td>${team.games_played}</td>
                <td>${team.wins}</td>
                <td>${team.draws}</td>
                <td>${team.losses}</td>
                <td>${team.goals_for}:${team.goals_against}</td>
                <td>${team.goal_difference}</td>
                <td><strong>${team.points}</strong></td>
            `;
            tbody.appendChild(row);
        });
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="9">Tabelle konnte nicht geladen werden.</td></tr>';
    }
}

loadHealth();
loadTable();
</script>
</body>
</html>
