<?php require __DIR__ . '/config.php'; ?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>BSSG Südtirol - Torball</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Offizielle Torball-Übersicht für BSSG Südtirol mit Tabelle, Ergebnissen, Live-Ticker und Statistiken.">
    <meta name="theme-color" content="#1e3a8a">
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="hero">
    <nav class="topnav">
        <strong>BSSG Südtirol</strong>
        <button class="nav-toggle" type="button" aria-label="Menü öffnen" aria-expanded="false">☰</button>
        <div class="nav-links" id="nav-links">
            <a href="index.php">Start</a>
            <a href="matches.php">Spiele</a>
            <a href="stats.php">Statistiken</a>
            <a href="live.php">Live</a>
            <a href="admin/login.php">Admin</a>
            <button class="theme-toggle" type="button" aria-label="Darkmode umschalten">🌙</button>
        </div>
    </nav>

    <section class="hero-content">
        <p class="eyebrow">Torball • Südtirol • Serie A</p>
        <h1>BSSG Südtirol Torball</h1>
        <p>
            Aktuelle Ergebnisse, Tabelle, Live-Ticker und Statistiken für die Torball-Saison.
            Schnell, modern und bereit für echte Spieltage.
        </p>
        <div class="hero-actions">
            <a class="btn" href="matches.php">Spiele ansehen</a>
            <a class="btn secondary" href="live.php">Live-Ticker</a>
        </div>
    </section>
</header>

<main>
    <section class="grid cards-overview">
        <div class="card highlight"><h2>BSSG Südtirol 1</h2><p>Teamübersicht, Ergebnisse und Saisonleistung der ersten Mannschaft.</p></div>
        <div class="card highlight"><h2>BSSG Südtirol 2</h2><p>Aktuelle Resultate, direkte Duelle und Tabellenposition der zweiten Mannschaft.</p></div>
        <div class="card highlight"><h2>Live am Spieltag</h2><p>Live-Meldungen, Zwischenstände und wichtige Ereignisse direkt im Browser.</p></div>
    </section>

    <section class="grid">
        <div class="card"><h2>Letzte Ergebnisse</h2><div id="latest-results" class="results-list"><p>Lade Ergebnisse…</p></div></div>
        <div class="card"><h2>Nächste Spiele</h2><div id="next-matches" class="next-list"><p>Lade Spiele…</p></div></div>
    </section>

    <section class="card">
        <div class="section-head">
            <div><h2>Aktuelle Tabelle</h2><p>Automatisch berechnet aus den eingetragenen Ergebnissen.</p></div>
            <span id="api-status" class="badge">API lädt…</span>
        </div>
        <div class="table-wrap">
            <table id="table">
                <thead><tr><th>#</th><th>Team</th><th>Sp</th><th>S</th><th>U</th><th>N</th><th>Tore</th><th>Diff</th><th>Punkte</th></tr></thead>
                <tbody><tr><td colspan="9">Tabelle wird geladen…</td></tr></tbody>
            </table>
        </div>
    </section>
</main>

<footer><p>© BSSG Südtirol • Torball System</p></footer>

<script src="assets/js/app.js"></script>
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
        let rank = 1;
        data.forEach(team => {
            const row = document.createElement('tr');
            if(rank <= 3) row.classList.add('table-top');
            if(rank >= data.length - 1) row.classList.add('table-relegation');
            row.innerHTML = `<td>${rank}</td><td><strong>${team.team}</strong></td><td>${team.games_played}</td><td>${team.wins}</td><td>${team.draws}</td><td>${team.losses}</td><td>${team.goals_for}:${team.goals_against}</td><td>${team.goal_difference}</td><td><strong>${team.points}</strong></td>`;
            tbody.appendChild(row);
            rank++;
        });
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="9">Tabelle konnte nicht geladen werden.</td></tr>';
    }
}

async function loadMatches() {
    try {
        const res = await fetch(apiBase + '/api/matches');
        const matches = await res.json();
        const latest = document.getElementById('latest-results');
        const upcoming = document.getElementById('next-matches');
        latest.innerHTML = '';
        upcoming.innerHTML = '';
        const played = matches.filter(m => m.match_status === 'played').slice(-5).reverse();
        const scheduled = matches.filter(m => m.match_status !== 'played').slice(0,5);
        played.forEach(match => {
            const el = document.createElement('div');
            el.className = 'match-card';
            el.innerHTML = `<div><strong>${match.home_team}</strong><br><small>vs ${match.away_team}</small></div><div class="match-score">${match.home_goals} : ${match.away_goals}</div>`;
            latest.appendChild(el);
        });
        if (scheduled.length === 0) upcoming.innerHTML = '<p>Aktuell keine offenen Spiele.</p>';
        scheduled.forEach(match => {
            const el = document.createElement('div');
            el.className = 'match-card';
            el.innerHTML = `<div><strong>${match.home_team}</strong><br><small>vs ${match.away_team}</small></div><div class="match-score">VS</div>`;
            upcoming.appendChild(el);
        });
    } catch (error) {
        document.getElementById('latest-results').innerHTML = '<p>Keine Ergebnisse verfügbar.</p>';
        document.getElementById('next-matches').innerHTML = '<p>Keine Spiele verfügbar.</p>';
    }
}

loadHealth();
loadTable();
loadMatches();
</script>
</body>
</html>
