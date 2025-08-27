-- =====================================================
-- FIT BATTLE - BANCO DE DADOS
-- Sistema de Competição Fitness
-- =====================================================

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS fit_battle CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fit_battle;

-- =====================================================
-- TABELAS PRINCIPAIS
-- =====================================================

-- Tabela de usuários
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    birth_date DATE,
    gender ENUM('M', 'F', 'O') DEFAULT 'O',
    height DECIMAL(5,2), -- em cm
    weight DECIMAL(5,2), -- em kg
    city VARCHAR(100),
    state VARCHAR(50),
    country VARCHAR(50) DEFAULT 'Brasil',
    profile_image VARCHAR(255),
    bio TEXT,
    total_points INT DEFAULT 0,
    current_level INT DEFAULT 1,
    current_streak INT DEFAULT 0,
    longest_streak INT DEFAULT 0,
    total_exercises INT DEFAULT 0,
    total_calories_burned INT DEFAULT 0,
    total_distance_km DECIMAL(10,2) DEFAULT 0,
    total_time_minutes INT DEFAULT 0,
    is_premium BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_total_points (total_points),
    INDEX idx_current_level (current_level),
    INDEX idx_city (city),
    INDEX idx_country (country)
);

-- Tabela de categorias de exercícios
CREATE TABLE exercise_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(100),
    color VARCHAR(7) DEFAULT '#007bff',
    points_per_unit DECIMAL(8,2) NOT NULL,
    unit_type ENUM('km', 'minute', 'repetition', 'set', 'session') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserir categorias padrão
INSERT INTO exercise_categories (name, description, icon, color, points_per_unit, unit_type) VALUES
('Corrida', 'Corrida ao ar livre ou na esteira', '🏃‍♂️', '#28a745', 10.00, 'km'),
('Ciclismo', 'Pedalar ao ar livre ou na bicicleta ergométrica', '🚴‍♂️', '#17a2b8', 8.00, 'km'),
('Natação', 'Natação em piscina ou mar', '🏊‍♂️', '#007bff', 15.00, 'minute'),
('Academia', 'Treino de força com pesos', '💪', '#dc3545', 15.00, 'set'),
('Yoga', 'Prática de yoga e meditação', '🧘‍♀️', '#6f42c1', 8.00, 'minute'),
('Pilates', 'Exercícios de pilates', '🤸‍♀️', '#fd7e14', 10.00, 'minute'),
('HIIT', 'Treino intervalado de alta intensidade', '⚡', '#e83e8c', 20.00, 'minute'),
('Caminhada', 'Caminhada leve ou moderada', '🚶‍♂️', '#6c757d', 5.00, 'km'),
('CrossFit', 'Treino funcional de alta intensidade', '🔥', '#dc3545', 25.00, 'minute'),
('Calistenia', 'Exercícios com peso corporal', '🏋️‍♂️', '#fd7e14', 12.00, 'repetition'),
('Futebol', 'Jogo de futebol', '⚽', '#28a745', 18.00, 'minute'),
('Basquete', 'Jogo de basquete', '🏀', '#ffc107', 16.00, 'minute'),
('Tênis', 'Jogo de tênis', '🎾', '#20c997', 14.00, 'minute'),
('Dança', 'Aula de dança', '💃', '#e83e8c', 12.00, 'minute'),
('Escalada', 'Escalada indoor ou outdoor', '🧗‍♂️', '#6f42c1', 30.00, 'minute');

-- Tabela de exercícios registrados
CREATE TABLE exercises (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    duration_minutes INT,
    distance_km DECIMAL(8,2),
    repetitions INT,
    sets INT,
    weight_kg DECIMAL(6,2),
    calories_burned INT,
    intensity ENUM('low', 'medium', 'high', 'extreme') DEFAULT 'medium',
    location VARCHAR(200),
    weather_conditions VARCHAR(100),
    mood_before ENUM('terrible', 'bad', 'ok', 'good', 'excellent') DEFAULT 'ok',
    mood_after ENUM('terrible', 'bad', 'ok', 'good', 'excellent') DEFAULT 'ok',
    notes TEXT,
    photo_url VARCHAR(255),
    points_earned INT DEFAULT 0,
    streak_bonus INT DEFAULT 0,
    time_bonus INT DEFAULT 0,
    total_points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES exercise_categories(id),
    
    INDEX idx_user_id (user_id),
    INDEX idx_category_id (category_id),
    INDEX idx_created_at (created_at),
    INDEX idx_points_earned (points_earned)
);

-- Tabela de badges/conquistas
CREATE TABLE badges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    icon VARCHAR(100) NOT NULL,
    color VARCHAR(7) DEFAULT '#ffc107',
    category ENUM('streak', 'exercise', 'special', 'level', 'social') NOT NULL,
    requirement_type ENUM('count', 'streak', 'level', 'time', 'custom') NOT NULL,
    requirement_value INT NOT NULL,
    points_reward INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserir badges padrão
INSERT INTO badges (name, description, icon, color, category, requirement_type, requirement_value, points_reward) VALUES
-- Badges de Streak
('🔥 Em Chamas', 'Mantenha um streak de 3 dias consecutivos', '🔥', '#dc3545', 'streak', 'streak', 3, 25),
('⚡ Velocista', 'Mantenha um streak de 7 dias consecutivos', '⚡', '#ffc107', 'streak', 'streak', 7, 50),
('👑 Mestre', 'Mantenha um streak de 30 dias consecutivos', '👑', '#6f42c1', 'streak', 'streak', 30, 100),
('🚀 Lendário', 'Mantenha um streak de 100 dias consecutivos', '🚀', '#e83e8c', 'streak', 'streak', 100, 500),

-- Badges de Exercício
('🏃 Corredor', 'Complete 10 corridas', '🏃', '#28a745', 'exercise', 'count', 10, 50),
('💪 Musculoso', 'Complete 50 sessões de academia', '💪', '#dc3545', 'exercise', 'count', 50, 100),
('🧘 Zen', 'Complete 20 sessões de yoga', '🧘', '#6f42c1', 'exercise', 'count', 20, 75),
('🏆 Atleta', 'Complete 100 exercícios de qualquer tipo', '🏆', '#fd7e14', 'exercise', 'count', 100, 200),

-- Badges Especiais
('🌅 Madrugador', 'Complete um exercício antes das 8h da manhã', '🌅', '#17a2b8', 'special', 'time', 8, 30),
('🌙 Noturno', 'Complete um exercício após 22h', '🌙', '#6c757d', 'special', 'time', 22, 25),
('🎯 Preciso', 'Mantenha 100% de consistência por uma semana', '🎯', '#20c997', 'special', 'custom', 7, 100),
('🚀 Rocket', 'Atinga 3 níveis em um mês', '🚀', '#e83e8c', 'special', 'custom', 3, 150),

-- Badges de Nível
('⭐ Iniciante', 'Atinga o nível 5', '⭐', '#6c757d', 'level', 'level', 5, 50),
('🌟 Amador', 'Atinga o nível 10', '🌟', '#17a2b8', 'level', 'level', 10, 100),
('💫 Profissional', 'Atinga o nível 20', '💫', '#28a745', 'level', 'level', 20, 200),
('👑 Elite', 'Atinga o nível 50', '👑', '#ffc107', 'level', 'level', 50, 500);

-- Tabela de badges conquistadas pelos usuários
CREATE TABLE user_badges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    badge_id INT NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    points_earned INT DEFAULT 0,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id),
    
    UNIQUE KEY unique_user_badge (user_id, badge_id),
    INDEX idx_user_id (user_id),
    INDEX idx_badge_id (badge_id)
);

-- Tabela de ranking histórico
CREATE TABLE ranking_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total_points INT NOT NULL,
    current_level INT NOT NULL,
    current_streak INT NOT NULL,
    rank_position INT,
    category VARCHAR(50),
    period ENUM('daily', 'weekly', 'monthly', 'yearly') DEFAULT 'daily',
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_period (period),
    INDEX idx_recorded_at (recorded_at),
    INDEX idx_rank_position (rank_position)
);

-- Tabela de desafios
CREATE TABLE challenges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    creator_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    category_id INT,
    challenge_type ENUM('1v1', 'group', 'tournament') DEFAULT '1v1',
    goal_type ENUM('distance', 'time', 'repetitions', 'sets', 'points') NOT NULL,
    goal_value DECIMAL(10,2) NOT NULL,
    duration_days INT NOT NULL,
    entry_fee_points INT DEFAULT 0,
    prize_pool_points INT DEFAULT 0,
    max_participants INT DEFAULT 2,
    current_participants INT DEFAULT 1,
    status ENUM('open', 'active', 'completed', 'cancelled') DEFAULT 'open',
    start_date TIMESTAMP,
    end_date TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (creator_id) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES exercise_categories(id),
    
    INDEX idx_creator_id (creator_id),
    INDEX idx_category_id (category_id),
    INDEX idx_status (status),
    INDEX idx_start_date (start_date)
);

-- Tabela de participantes de desafios
CREATE TABLE challenge_participants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    challenge_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    current_progress DECIMAL(10,2) DEFAULT 0,
    is_completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    final_position INT,
    points_earned INT DEFAULT 0,
    
    FOREIGN KEY (challenge_id) REFERENCES challenges(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_challenge_user (challenge_id, user_id),
    INDEX idx_challenge_id (challenge_id),
    INDEX idx_user_id (user_id)
);

-- Tabela de amizades/seguidores
CREATE TABLE user_relationships (
    id INT PRIMARY KEY AUTO_INCREMENT,
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    relationship_type ENUM('friend', 'follower', 'blocked') DEFAULT 'follower',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_relationship (follower_id, following_id),
    INDEX idx_follower_id (follower_id),
    INDEX idx_following_id (following_id)
);

-- Tabela de notificações
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('badge', 'challenge', 'ranking', 'social', 'system') NOT NULL,
    related_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
);

-- =====================================================
-- STORED PROCEDURES
-- =====================================================

DELIMITER //

-- Calcular pontos de um exercício
CREATE PROCEDURE CalculateExercisePoints(
    IN p_user_id INT,
    IN p_category_id INT,
    IN p_duration_minutes INT,
    IN p_distance_km DECIMAL(8,2),
    IN p_repetitions INT,
    IN p_sets INT,
    IN p_weight_kg DECIMAL(6,2),
    IN p_intensity VARCHAR(20),
    OUT p_total_points INT,
    OUT p_streak_bonus INT,
    OUT p_time_bonus INT
)
BEGIN
    DECLARE base_points DECIMAL(8,2);
    DECLARE current_streak INT;
    DECLARE current_hour INT;
    DECLARE intensity_multiplier DECIMAL(3,2);
    
    -- Obter pontos base da categoria
    SELECT points_per_unit INTO base_points 
    FROM exercise_categories 
    WHERE id = p_category_id;
    
    -- Obter streak atual do usuário
    SELECT current_streak INTO current_streak 
    FROM users 
    WHERE id = p_user_id;
    
    -- Obter hora atual
    SET current_hour = HOUR(NOW());
    
    -- Calcular multiplicador de intensidade
    SET intensity_multiplier = CASE p_intensity
        WHEN 'low' THEN 0.8
        WHEN 'medium' THEN 1.0
        WHEN 'high' THEN 1.3
        WHEN 'extreme' THEN 1.6
        ELSE 1.0
    END;
    
    -- Calcular pontos base
    SET p_total_points = 0;
    
    -- Pontos por duração (para exercícios baseados em tempo)
    IF p_duration_minutes IS NOT NULL AND p_duration_minutes > 0 THEN
        SET p_total_points = p_total_points + (base_points * p_duration_minutes / 60);
    END IF;
    
    -- Pontos por distância (para exercícios baseados em distância)
    IF p_distance_km IS NOT NULL AND p_distance_km > 0 THEN
        SET p_total_points = p_total_points + (base_points * p_distance_km);
    END IF;
    
    -- Pontos por repetições (para exercícios de força)
    IF p_repetitions IS NOT NULL AND p_repetitions > 0 THEN
        SET p_total_points = p_total_points + (base_points * p_repetitions / 10);
    END IF;
    
    -- Pontos por séries (para exercícios de academia)
    IF p_sets IS NOT NULL AND p_sets > 0 THEN
        SET p_total_points = p_total_points + (base_points * p_sets);
    END IF;
    
    -- Aplicar multiplicador de intensidade
    SET p_total_points = p_total_points * intensity_multiplier;
    
    -- Calcular bônus de streak
    SET p_streak_bonus = 0;
    IF current_streak >= 3 AND current_streak < 7 THEN
        SET p_streak_bonus = 25;
    ELSEIF current_streak >= 7 AND current_streak < 30 THEN
        SET p_streak_bonus = 50;
    ELSEIF current_streak >= 30 THEN
        SET p_streak_bonus = 100;
    END IF;
    
    -- Calcular bônus de horário
    SET p_time_bonus = 0;
    IF current_hour >= 5 AND current_hour < 8 THEN
        SET p_time_bonus = ROUND(p_total_points * 0.5);
    ELSEIF current_hour >= 22 OR current_hour < 2 THEN
        SET p_time_bonus = ROUND(p_total_points * 0.2);
    END IF;
    
    -- Total final
    SET p_total_points = ROUND(p_total_points + p_streak_bonus + p_time_bonus);
END //

-- Atualizar ranking de um usuário
CREATE PROCEDURE UpdateUserRanking(
    IN p_user_id INT
)
BEGIN
    DECLARE user_points INT;
    DECLARE user_level INT;
    DECLARE user_streak INT;
    DECLARE current_rank INT;
    
    -- Obter dados atuais do usuário
    SELECT total_points, current_level, current_streak 
    INTO user_points, user_level, user_streak
    FROM users 
    WHERE id = p_user_id;
    
    -- Calcular novo nível baseado nos pontos
    SET user_level = GREATEST(1, FLOOR(user_points / 100) + 1);
    
    -- Atualizar usuário
    UPDATE users 
    SET current_level = user_level,
        updated_at = NOW()
    WHERE id = p_user_id;
    
    -- Inserir no histórico de ranking
    INSERT INTO ranking_history (user_id, total_points, current_level, current_streak, rank_position, period, recorded_at)
    VALUES (p_user_id, user_points, user_level, user_streak, 0, 'daily', NOW());
    
    -- Atualizar posição no ranking (será calculada por uma query separada)
    SELECT COUNT(*) + 1 INTO current_rank
    FROM users 
    WHERE total_points > user_points;
    
    UPDATE ranking_history 
    SET rank_position = current_rank
    WHERE user_id = p_user_id 
    AND recorded_at = (SELECT MAX(recorded_at) FROM ranking_history WHERE user_id = p_user_id);
END //

-- Verificar e atribuir badges automaticamente
CREATE PROCEDURE CheckAndAwardBadges(
    IN p_user_id INT
)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE badge_id INT;
    DECLARE badge_name VARCHAR(100);
    DECLARE requirement_type VARCHAR(20);
    DECLARE requirement_value INT;
    DECLARE current_value INT;
    DECLARE badge_cursor CURSOR FOR
        SELECT id, name, requirement_type, requirement_value
        FROM badges 
        WHERE is_active = TRUE;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN badge_cursor;
    
    read_loop: LOOP
        FETCH badge_cursor INTO badge_id, badge_name, requirement_type, requirement_value;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Verificar se o usuário já tem a badge
        IF NOT EXISTS (SELECT 1 FROM user_badges WHERE user_id = p_user_id AND badge_id = badge_id) THEN
            
            -- Calcular valor atual baseado no tipo de requisito
            CASE requirement_type
                WHEN 'count' THEN
                    SELECT total_exercises INTO current_value FROM users WHERE id = p_user_id;
                WHEN 'streak' THEN
                    SELECT current_streak INTO current_value FROM users WHERE id = p_user_id;
                WHEN 'level' THEN
                    SELECT current_level INTO current_value FROM users WHERE id = p_user_id;
                ELSE
                    SET current_value = 0;
            END CASE;
            
            -- Se atendeu aos requisitos, atribuir badge
            IF current_value >= requirement_value THEN
                INSERT INTO user_badges (user_id, badge_id, points_earned)
                VALUES (p_user_id, badge_id, 
                    (SELECT points_reward FROM badges WHERE id = badge_id)
                );
                
                -- Adicionar pontos da badge
                UPDATE users 
                SET total_points = total_points + (SELECT points_reward FROM badges WHERE id = badge_id)
                WHERE id = p_user_id;
                
                -- Inserir notificação
                INSERT INTO notifications (user_id, title, message, type, related_id)
                VALUES (p_user_id, '🏆 Nova Conquista!', 
                    CONCAT('Você conquistou a badge "', badge_name, '"!'), 
                    'badge', badge_id);
            END IF;
        END IF;
    END LOOP;
    
    CLOSE badge_cursor;
END //

DELIMITER ;

-- =====================================================
-- TRIGGERS
-- =====================================================

-- Trigger para atualizar estatísticas do usuário quando um exercício é inserido
DELIMITER //
CREATE TRIGGER after_exercise_insert
AFTER INSERT ON exercises
FOR EACH ROW
BEGIN
    DECLARE new_streak INT;
    DECLARE last_exercise_date DATE;
    
    -- Atualizar estatísticas do usuário
    UPDATE users 
    SET total_exercises = total_exercises + 1,
        total_points = total_points + NEW.total_points,
        updated_at = NOW()
    WHERE id = NEW.user_id;
    
    -- Verificar se é o primeiro exercício do dia
    SELECT MAX(DATE(created_at)) INTO last_exercise_date
    FROM exercises 
    WHERE user_id = NEW.user_id 
    AND DATE(created_at) < DATE(NEW.created_at);
    
    -- Se não há exercício no dia anterior, resetar streak
    IF last_exercise_date IS NULL OR DATEDIFF(DATE(NEW.created_at), last_exercise_date) > 1 THEN
        UPDATE users SET current_streak = 1 WHERE id = NEW.user_id;
    ELSE
        -- Incrementar streak
        UPDATE users 
        SET current_streak = current_streak + 1,
            longest_streak = GREATEST(current_streak + 1, longest_streak)
        WHERE id = NEW.user_id;
    END IF;
    
    -- Chamar stored procedure para verificar badges
    CALL CheckAndAwardBadges(NEW.user_id);
    
    -- Chamar stored procedure para atualizar ranking
    CALL UpdateUserRanking(NEW.user_id);
END //
DELIMITER ;

-- =====================================================
-- VIEWS ÚTEIS
-- =====================================================

-- View do ranking geral
CREATE VIEW v_ranking_general AS
SELECT 
    u.id,
    u.username,
    u.full_name,
    u.profile_image,
    u.city,
    u.country,
    u.total_points,
    u.current_level,
    u.current_streak,
    u.total_exercises,
    ROW_NUMBER() OVER (ORDER BY u.total_points DESC) as rank_position
FROM users u
WHERE u.is_active = TRUE
ORDER BY u.total_points DESC;

-- View do ranking por categoria
CREATE VIEW v_ranking_by_category AS
SELECT 
    u.id,
    u.username,
    u.full_name,
    u.profile_image,
    ec.name as category_name,
    COUNT(e.id) as exercises_count,
    SUM(e.total_points) as category_points,
    ROW_NUMBER() OVER (PARTITION BY ec.id ORDER BY SUM(e.total_points) DESC) as rank_position
FROM users u
JOIN exercises e ON u.id = e.user_id
JOIN exercise_categories ec ON e.category_id = ec.id
WHERE u.is_active = TRUE
GROUP BY u.id, ec.id
ORDER BY ec.id, category_points DESC;

-- View de estatísticas do usuário
CREATE VIEW v_user_stats AS
SELECT 
    u.id,
    u.username,
    u.full_name,
    u.total_points,
    u.current_level,
    u.current_streak,
    u.longest_streak,
    u.total_exercises,
    u.total_calories_burned,
    u.total_distance_km,
    u.total_time_minutes,
    COUNT(ub.id) as badges_count,
    COUNT(DISTINCT e.category_id) as categories_used
FROM users u
LEFT JOIN user_badges ub ON u.id = ub.user_id
LEFT JOIN exercises e ON u.id = e.user_id
GROUP BY u.id;

-- =====================================================
-- ÍNDICES ADICIONAIS PARA PERFORMANCE
-- =====================================================

-- Índices para consultas de ranking
CREATE INDEX idx_users_total_points_active ON users(total_points, is_active);
CREATE INDEX idx_exercises_user_date ON exercises(user_id, created_at);
CREATE INDEX idx_exercises_category_points ON exercises(category_id, total_points);

-- Índices para consultas de desafios
CREATE INDEX idx_challenges_status_dates ON challenges(status, start_date, end_date);
CREATE INDEX idx_challenge_participants_progress ON challenge_participants(challenge_id, current_progress);

-- Índices para consultas de notificações
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read, created_at);

-- =====================================================
-- DADOS DE EXEMPLO (OPCIONAL)
-- =====================================================

-- Inserir usuário de exemplo
INSERT INTO users (username, email, password_hash, full_name, city, state, country) VALUES
('admin', 'admin@fitbattle.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'São Paulo', 'SP', 'Brasil'),
('testuser', 'test@fitbattle.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Usuário Teste', 'Rio de Janeiro', 'RJ', 'Brasil');

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================
