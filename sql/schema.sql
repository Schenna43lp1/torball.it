CREATE TABLE IF NOT EXISTS seasons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    season_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    short_name VARCHAR(80)
);

CREATE TABLE IF NOT EXISTS matchdays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    season_id INT NOT NULL,
    round_number INT NOT NULL,
    name VARCHAR(100),
    location VARCHAR(100),
    start_date DATE,
    end_date DATE
);

CREATE TABLE IF NOT EXISTS matches (
    id INT PRIMARY KEY,
    matchday_id INT NOT NULL,
    home_team_id INT NOT NULL,
    away_team_id INT NOT NULL,
    home_goals INT,
    away_goals INT,
    match_status VARCHAR(20) DEFAULT 'played'
);

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE,
    password_hash VARCHAR(255),
    role VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS live_ticker (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NULL,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE OR REPLACE VIEW league_table AS
SELECT
    t.name AS team_name,

    COUNT(m.id) AS games_played,

    SUM(
        CASE
            WHEN (m.home_team_id = t.id AND m.home_goals > m.away_goals)
              OR (m.away_team_id = t.id AND m.away_goals > m.home_goals)
            THEN 1 ELSE 0
        END
    ) AS wins,

    SUM(
        CASE
            WHEN m.home_goals = m.away_goals
            THEN 1 ELSE 0
        END
    ) AS draws,

    SUM(
        CASE
            WHEN (m.home_team_id = t.id AND m.home_goals < m.away_goals)
              OR (m.away_team_id = t.id AND m.away_goals < m.home_goals)
            THEN 1 ELSE 0
        END
    ) AS losses,

    SUM(
        CASE
            WHEN m.home_team_id = t.id THEN m.home_goals
            WHEN m.away_team_id = t.id THEN m.away_goals
            ELSE 0
        END
    ) AS goals_for,

    SUM(
        CASE
            WHEN m.home_team_id = t.id THEN m.away_goals
            WHEN m.away_team_id = t.id THEN m.home_goals
            ELSE 0
        END
    ) AS goals_against,

    SUM(
        CASE
            WHEN m.home_team_id = t.id THEN m.home_goals - m.away_goals
            WHEN m.away_team_id = t.id THEN m.away_goals - m.home_goals
            ELSE 0
        END
    ) AS goal_difference,

    SUM(
        CASE
            WHEN (m.home_team_id = t.id AND m.home_goals > m.away_goals)
              OR (m.away_team_id = t.id AND m.away_goals > m.home_goals)
            THEN 3

            WHEN m.home_goals = m.away_goals
            THEN 1

            ELSE 0
        END
    ) AS points

FROM teams t
LEFT JOIN matches m
    ON (m.home_team_id = t.id OR m.away_team_id = t.id)
    AND m.match_status = 'played'

GROUP BY t.id, t.name
ORDER BY points DESC, goal_difference DESC;
