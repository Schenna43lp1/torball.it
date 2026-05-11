INSERT INTO seasons (id, name, start_date, end_date)
VALUES (1, 'Serie A 2026', '2026-02-28', '2026-05-10')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO teams (id, season_id, name, short_name, city) VALUES
(1, 1, 'GSD Alto Adige 1', 'Alto Adige 1', 'Bolzano'),
(2, 1, 'GSD Alto Adige 2', 'Alto Adige 2', 'Bolzano'),
(3, 1, 'A.S.D. Reggina UIC', 'Reggina', 'Reggio Calabria'),
(4, 1, 'ASD Pol. Torino', 'Torino', 'Torino'),
(5, 1, 'ASD Teramo', 'Teramo', 'Teramo'),
(6, 1, 'Omero Bergamo A', 'Bergamo A', 'Bergamo'),
(7, 1, 'GSD Fucà', 'Fucà', NULL),
(8, 1, 'A.S.D. Augusta', 'Augusta', 'Augusta')
ON DUPLICATE KEY UPDATE name = VALUES(name), short_name = VALUES(short_name), city = VALUES(city);

INSERT INTO players (team_id, first_name, last_name, is_captain) VALUES
(1, 'Franz', 'Gatscher', 1),
(1, 'Manfred', 'Wieser', 0),
(1, 'Gabriel', 'Psenner', 0),
(1, 'Willi', 'Augschöll', 0),
(2, 'Christian', 'Mair', 0),
(2, 'Peter', 'Mair', 0),
(2, 'Armin', 'Plaikner', 0),
(2, 'Markus', NULL, 0);

INSERT INTO matchdays (id, season_id, round_number, name, location, start_date, end_date) VALUES
(1, 1, 1, '1a Giornata', 'Bolzano', '2026-02-28', '2026-03-01'),
(2, 1, 2, '2a Giornata', 'Torino', '2026-03-28', '2026-03-29'),
(3, 1, 3, 'Finale', 'Reggio Calabria', '2026-05-09', '2026-05-10')
ON DUPLICATE KEY UPDATE name = VALUES(name), location = VALUES(location);

INSERT INTO matches (id, matchday_id, home_team_id, away_team_id, home_goals, away_goals, match_status) VALUES
(1, 1, 2, 1, 2, 1, 'played'),
(2, 1, 6, 2, 2, 6, 'played'),
(3, 1, 2, 4, NULL, NULL, 'scheduled'),
(4, 2, 1, 4, 2, 2, 'played'),
(5, 2, 8, 2, 2, 3, 'played'),
(6, 3, 3, 2, NULL, NULL, 'scheduled'),
(7, 3, 5, 2, NULL, NULL, 'scheduled')
ON DUPLICATE KEY UPDATE
home_goals = VALUES(home_goals),
away_goals = VALUES(away_goals),
match_status = VALUES(match_status);

INSERT INTO live_ticker (match_id, message, event_type) VALUES
(1, 'System bereit: Live-Ticker gestartet.', 'system');
