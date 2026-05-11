<?php require __DIR__.'/config.php'; ?>
<!doctype html>
<html lang='de'>
<head>
<meta charset='utf-8'>
<title>Torball Tabelle</title>
<link rel='stylesheet' href='assets/css/style.css'>
</head>
<body>
<header>
<h1>Torball Liga</h1>
<nav>
<a href='index.php'>Tabelle</a>
<a href='matches.php'>Spiele</a>
<a href='stats.php'>Statistiken</a>
<a href='live.php'>Live</a>
</nav>
</header>
<main>
<div class='card'>
<h2>API Status</h2>
<pre id='health'>Loading...</pre>
</div>
<div class='card'>
<h2>Tabelle</h2>
<table id='table'>
<thead>
<tr>
<th>#</th><th>Team</th><th>Sp</th><th>S</th><th>U</th><th>N</th><th>Tore</th><th>Diff</th><th>Punkte</th>
</tr>
</thead>
<tbody></tbody>
</table>
</div>
</main>
<script>
async function loadTable(){
const res=await fetch('http://'+location.hostname+':8082/api/table');
const data=await res.json();
const tbody=document.querySelector('#table tbody');
tbody.innerHTML='';
let i=1;
data.forEach(team=>{
const row=document.createElement('tr');
row.innerHTML=`<td>${i++}</td><td>${team.team}</td><td>${team.games_played}</td><td>${team.wins}</td><td>${team.draws}</td><td>${team.losses}</td><td>${team.goals_for}:${team.goals_against}</td><td>${team.goal_difference}</td><td><strong>${team.points}</strong></td>`;
tbody.appendChild(row);
});
}
async function loadHealth(){
const res=await fetch('http://'+location.hostname+':8082/api/health');
const data=await res.json();
document.getElementById('health').textContent=JSON.stringify(data,null,2);
}
loadHealth();
loadTable();
</script>
</body>
</html>