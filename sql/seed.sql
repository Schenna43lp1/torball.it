INSERT INTO seasons (name, start_date, end_date)
VALUES ('Serie A 2026', '2026-02-28', '2026-05-10');

INSERT INTO teams (season_id, name, short_name, city) VALUES
(1, 'GSD Alto Adige 1', 'Alto Adige 1', 'Bolzano'),
(1, 'GSD Alto Adige 2', 'Alto Adige 2', 'Bolzano'),
(1, 'A.S.D. Reggina UIC', 'Reggina', 'Reggio Calabria'),
(1, 'ASD Pol. Torino', 'Torino', 'Torino'),
(1, 'ASD Teramo', 'Teramo', 'Teramo');

INSERT INTO admins (username, password_hash, role)
VALUES (
'admin',
'$2y$10$2f0B0w7xj9A2G0m5M0rRZeV4B6o2lF9x8YxM7y0v9eM8nR1rVQ4jW',
'admin'
);
