CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'coach', 'analyst') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE seasons (
    id_season INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL,
    start_date DATE,
    end_date DATE,
    is_active TINYINT(1) DEFAULT 0
);

CREATE TABLE positions (
    position_code VARCHAR(10) PRIMARY KEY,
    position_name VARCHAR(50) NOT NULL,
    line ENUM('defense', 'midfield', 'attack') NOT NULL
);

CREATE TABLE players (
    id_player INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    nickname VARCHAR(50),
    usual_position VARCHAR(10),
    status ENUM('active', 'injured', 'retired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE player_squad_numbers (
    id_squad_number INT AUTO_INCREMENT PRIMARY KEY,
    id_player INT,
    id_season INT,
    squad_number INT,
    FOREIGN KEY (id_player) REFERENCES players(id_player),
    FOREIGN KEY (id_season) REFERENCES seasons(id_season)
);

CREATE TABLE matches (
    id_match INT AUTO_INCREMENT PRIMARY KEY,
    id_season INT,
    date DATETIME NOT NULL,
    competition VARCHAR(100),
    opponent VARCHAR(100),
    venue ENUM('home', 'away'),
    goals_for INT,
    goals_against INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_season) REFERENCES seasons(id_season)
);

CREATE TABLE player_match_stats (
    id_stat INT AUTO_INCREMENT PRIMARY KEY,
    id_match INT,
    id_player INT,
    played_position VARCHAR(10),
    minutes_played INT,
    goals INT DEFAULT 0,
    助攻 INT DEFAULT 0, -- Se usa 'assists' en el mapeo final
    assists INT DEFAULT 0,
    shots INT DEFAULT 0,
    shots_on_target INT DEFAULT 0,
    xg FLOAT DEFAULT 0,
    xa FLOAT DEFAULT 0,
    key_passes INT DEFAULT 0,
    secondary_assists INT DEFAULT 0,
    total_passes INT DEFAULT 0,
    accurate_passes INT DEFAULT 0,
    long_passes INT DEFAULT 0,
    accurate_long_passes INT DEFAULT 0,
    passes_to_final_third INT DEFAULT 0,
    accurate_passes_to_final_third INT DEFAULT 0,
    through_passes INT DEFAULT 0,
    accurate_through_passes INT DEFAULT 0,
    forward_passes INT DEFAULT 0,
    accurate_forward_passes INT DEFAULT 0,
    backward_passes INT DEFAULT 0,
    accurate_backward_passes INT DEFAULT 0,
    received_passes INT DEFAULT 0,
    dribbles INT DEFAULT 0,
    successful_dribbles INT DEFAULT 0,
    progressive_runs INT DEFAULT 0,
    duels INT DEFAULT 0,
    duels_won INT DEFAULT 0,
    defensive_duels INT DEFAULT 0,
    defensive_duels_won INT DEFAULT 0,
    offensive_duels INT DEFAULT 0,
    offensive_duels_won INT DEFAULT 0,
    aerial_duels INT DEFAULT 0,
    aerial_duels_won INT DEFAULT 0,
    interceptions INT DEFAULT 0,
    clearances INT DEFAULT 0,
    recoveries_opp_half INT DEFAULT 0,
    losses_own_half INT DEFAULT 0,
    yellow_card TINYINT(1) DEFAULT 0,
    red_card TINYINT(1) DEFAULT 0,
    source_file VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_match) REFERENCES matches(id_match),
    FOREIGN KEY (id_player) REFERENCES players(id_player)
);

CREATE TABLE weight_configs (
    id_config INT AUTO_INCREMENT PRIMARY KEY,
    config_name VARCHAR(100),
    is_active TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE scores (
    id_score INT AUTO_INCREMENT PRIMARY KEY,
    id_match INT,
    id_player INT,
    evaluated_position VARCHAR(10),
    attack_score FLOAT,
    build_up_score FLOAT,
    defense_score FLOAT,
    minutes_factor FLOAT,
    final_score FLOAT,
    positive_feedback TEXT,
    negative_feedback TEXT,
    model_version VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_match) REFERENCES matches(id_match),
    FOREIGN KEY (id_player) REFERENCES players(id_player)
);

CREATE TABLE injuries (
    id_injury INT AUTO_INCREMENT PRIMARY KEY,
    id_player INT,
    start_date DATE NOT NULL,
    end_date DATE,
    injury_type VARCHAR(100),
    severity VARCHAR(50),
    expected_return_date DATE,
    observations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_player) REFERENCES players(id_player)
);

CREATE TABLE position_block_weights (
    id_block_weight INT AUTO_INCREMENT PRIMARY KEY,
    id_config INT,
    position_code VARCHAR(10),
    attack_percentage FLOAT,
    build_up_percentage FLOAT,
    defense_percentage FLOAT,
    FOREIGN KEY (id_config) REFERENCES weight_configs(id_config),
    FOREIGN KEY (position_code) REFERENCES positions(position_code)
);

CREATE TABLE position_metric_weights (
    id_metric_weight INT AUTO_INCREMENT PRIMARY KEY,
    id_config INT,
    position_code VARCHAR(10),
    block ENUM('attack', 'build_up', 'defense'),
    metric_key VARCHAR(50),
    percentage FLOAT,
    is_penalty TINYINT(1) DEFAULT 0,
    FOREIGN KEY (id_config) REFERENCES weight_configs(id_config),
    FOREIGN KEY (position_code) REFERENCES positions(position_code)
);

CREATE TABLE coach_notes (
    id_note INT AUTO_INCREMENT PRIMARY KEY,
    id_match INT,
    id_player INT,
    rating FLOAT,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_match) REFERENCES matches(id_match),
    FOREIGN KEY (id_player) REFERENCES players(id_player)
);


-- Ejemplos para probar los endpoints de la primera fase

-- 1. INSERTAR TEMPORADA (Requisito para Partidos y Dorsales) [cite: 99, 119]
INSERT INTO seasons (name, start_date, end_date, is_active) 
VALUES ('Temporada 2025/26', '2025-08-15', '2026-06-15', 1);

-- 2. INSERTAR POSICIONES (Catálogo base según el PDF) [cite: 98, 118]
INSERT INTO positions (position_code, position_name, line) VALUES 
('GK', 'Goalkeeper', 'defense'),
('CB', 'Center Back', 'defense'),
('LB', 'Left Back', 'defense'),
('CM', 'Central Midfielder', 'midfield'),
('RW', 'Right Wing', 'attack'),
('ST', 'Striker', 'attack');

-- 3. INSERTAR JUGADORES (CRUD Completo) [cite: 53, 118]
INSERT INTO players (first_name, last_name, nickname, usual_position, status) VALUES 
('Lamine', 'Yamal', 'Lamine', 'RW', 'active'),
('Robert', 'Lewandowski', 'Lewy', 'ST', 'active'),
('Ronald', 'Araújo', NULL, 'CB', 'injured'),
('Pedro', 'González', 'Pedri', 'CM', 'active'),
('Pau', 'Cubarsí', NULL, 'CB', 'active'),
('Pablo', 'Páez', 'Gavi', 'CM', 'injured');

-- 4. INSERTAR DORSALES (Vinculados a Jugador y Temporada) 
INSERT INTO player_squad_numbers (id_player, id_season, squad_number) VALUES 
(1, 1, 19), -- Lamine
(2, 1, 9),  -- Lewy
(3, 1, 4),  -- Araújo
(4, 1, 8),  -- Pedri
(5, 1, 2);  -- Cubarsí

-- 5. INSERTAR PARTIDOS (Alta de partidos Semanas 1-2) [cite: 72, 118]
INSERT INTO matches (id_season, date, competition, opponent, venue, goals_for, goals_against) VALUES 
(1, '2026-02-15 21:00:00', 'La Liga', 'Real Madrid', 'home', 2, 1),
(1, '2026-02-22 18:30:00', 'Champions League', 'Bayern Munich', 'away', 1, 1);

-- 6. INSERTAR LESIONES (Módulo inicial de lesiones) [cite: 101, 119]
-- Ronald Araújo (ID 3) con lesión activa
INSERT INTO injuries (id_player, start_date, injury_type, severity, expected_return_date, observations) 
VALUES (3, '2026-02-10', 'Rotura de fibras', 'Alta', '2026-03-20', 'Lesión en el isquiotibial izquierdo.');

-- Gavi (ID 6) con lesión activa
INSERT INTO injuries (id_player, start_date, injury_type, severity, expected_return_date, observations) 
VALUES (6, '2026-02-25', 'Rotura cruzado', 'Alta', '2026-09-01', 'En proceso de recuperación larga.');