CREATE TABLE IF NOT EXISTS seasons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    start_date DATE,
    end_date DATE,
    points_win INT DEFAULT 3,
    points_draw INT DEFAULT 1,
    points_loss INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    season_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    short_name VARCHAR(50),
    city VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (season_id) REFERENCES seasons(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100),
    is_captain BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS matchdays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    season_id INT NOT NULL,
    round_number INT NOT NULL,
    name VARCHAR(100),
    location VARCHAR(100),
    start_date DATE,
    end_date DATE,
    FOREIGN KEY (season_id) REFERENCES seasons(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matchday_id INT NOT NULL,
    home_team_id INT NOT NULL,
    away_team_id INT NOT NULL,
    home_goals INT DEFAULT NULL,
    away_goals INT DEFAULT NULL,
    match_status ENUM('scheduled', 'played', 'cancelled') DEFAULT 'scheduled',
    played_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (matchday_id) REFERENCES matchdays(id) ON DELETE CASCADE,
    FOREIGN KEY (home_team_id) REFERENCES teams(id),
    FOREIGN KEY (away_team_id) REFERENCES teams(id)
);

CREATE TABLE IF NOT EXISTS goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    player_id INT NOT NULL,
    team_id INT NOT NULL,
    minute INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id),
    FOREIGN KEY (team_id) REFERENCES teams(id)
);

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'referee') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS live_ticker (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    message TEXT NOT NULL,
    event_type VARCHAR(50) DEFAULT 'message',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE
);

CREATE OR REPLACE VIEW league_table AS
SELECT
    t.id AS team_id,
    t.name AS team_name,
    COALESCE(COUNT(m.id), 0) AS games_played,
    COALESCE(SUM(CASE
        WHEN m.home_team_id = t.id AND m.home_goals > m.away_goals THEN 1
        WHEN m.away_team_id = t.id AND m.away_goals > m.home_goals THEN 1
        ELSE 0
    END), 0) AS wins,
    COALESCE(SUM(CASE
        WHEN m.home_goals = m.away_goals THEN 1
        ELSE 0
    END), 0) AS draws,
    COALESCE(SUM(CASE
        WHEN m.home_team_id = t.id AND m.home_goals < m.away_goals THEN 1
        WHEN m.away_team_id = t.id AND m.away_goals < m.home_goals THEN 1
        ELSE 0
    END), 0) AS losses,
    COALESCE(SUM(CASE
        WHEN m.home_team_id = t.id THEN m.home_goals
        WHEN m.away_team_id = t.id THEN m.away_goals
        ELSE 0
    END), 0) AS goals_for,
    COALESCE(SUM(CASE
        WHEN m.home_team_id = t.id THEN m.away_goals
        WHEN m.away_team_id = t.id THEN m.home_goals
        ELSE 0
    END), 0) AS goals_against,
    COALESCE(SUM(CASE
        WHEN m.home_team_id = t.id THEN m.home_goals - m.away_goals
        WHEN m.away_team_id = t.id THEN m.away_goals - m.home_goals
        ELSE 0
    END), 0) AS goal_difference,
    COALESCE(SUM(CASE
        WHEN m.home_team_id = t.id AND m.home_goals > m.away_goals THEN 3
        WHEN m.away_team_id = t.id AND m.away_goals > m.home_goals THEN 3
        WHEN m.home_goals = m.away_goals THEN 1
        ELSE 0
    END), 0) AS points
FROM teams t
LEFT JOIN matches m
    ON (m.home_team_id = t.id OR m.away_team_id = t.id)
    AND m.match_status = 'played'
GROUP BY t.id, t.name
ORDER BY points DESC, goal_difference DESC, goals_for DESC;
