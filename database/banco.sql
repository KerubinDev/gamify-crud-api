-- =====================================================
-- SISTEMA VIDA EQUILIBRADA - BANCO DE DADOS
-- =====================================================

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS vida_equilibrada;
USE vida_equilibrada;

-- =====================================================
-- TABELA DE USUÁRIOS
-- =====================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    pontos INT DEFAULT 0,
    streak_atual INT DEFAULT 0,
    streak_maximo INT DEFAULT 0,
    total_habitos INT DEFAULT 0,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_login TIMESTAMP NULL,
    ativo BOOLEAN DEFAULT TRUE
);

-- =====================================================
-- TABELA DE HÁBITOS
-- =====================================================
CREATE TABLE habitos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT,
    categoria ENUM('saude', 'exercicio', 'alimentacao', 'mental', 'produtividade', 'social') NOT NULL,
    frequencia ENUM('diario', 'semanal', 'mensal') DEFAULT 'diario',
    pontos_base INT DEFAULT 10,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- =====================================================
-- TABELA DE COMPLETAMENTO DE HÁBITOS
-- =====================================================
CREATE TABLE habitos_completados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    habito_id INT NOT NULL,
    usuario_id INT NOT NULL,
    data_completado DATE NOT NULL,
    hora_completado TIME NOT NULL,
    pontos_ganhos INT NOT NULL,
    multiplicador DECIMAL(3,2) DEFAULT 1.00,
    bonus_streak INT DEFAULT 0,
    bonus_primeiro_dia INT DEFAULT 0,
    total_pontos INT NOT NULL,
    FOREIGN KEY (habito_id) REFERENCES habitos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_habito_dia (habito_id, data_completado)
);

-- =====================================================
-- TABELA DE BADGES/CONQUISTAS
-- =====================================================
CREATE TABLE badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    icone VARCHAR(50) NOT NULL,
    cor VARCHAR(20) DEFAULT '#007bff',
    tipo ENUM('streak', 'quantidade', 'especial', 'tempo') NOT NULL,
    requisito_valor INT NOT NULL,
    pontos_bonus INT DEFAULT 0
);

-- =====================================================
-- TABELA DE BADGES CONQUISTADAS
-- =====================================================
CREATE TABLE badges_conquistadas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    badge_id INT NOT NULL,
    data_conquista TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    pontos_ganhos INT DEFAULT 0,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_badge (usuario_id, badge_id)
);

-- =====================================================
-- TABELA DE RANKING HISTÓRICO
-- =====================================================
CREATE TABLE ranking_historico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    posicao INT NOT NULL,
    pontos INT NOT NULL,
    total_badges INT NOT NULL,
    streak_atual INT NOT NULL,
    periodo ENUM('semana', 'mes', 'ano') NOT NULL,
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- =====================================================
-- INSERIR BADGES PADRÃO
-- =====================================================
INSERT INTO badges (nome, descricao, icone, cor, tipo, requisito_valor, pontos_bonus) VALUES
-- Badges de Streak
('Em Chamas', 'Manteve hábito por 3 dias consecutivos', '🔥', '#ff6b35', 'streak', 3, 25),
('Velocista', 'Manteve hábito por 7 dias consecutivos', '⚡', '#ffd23f', 'streak', 7, 50),
('Mestre', 'Manteve hábito por 30 dias consecutivos', '👑', '#ffd700', 'streak', 30, 100),

-- Badges de Quantidade
('Iniciante', 'Completou seu primeiro hábito', '🏃‍♂️', '#28a745', 'quantidade', 1, 10),
('Disciplinado', 'Completou 10 hábitos', '💪', '#17a2b8', 'quantidade', 10, 50),
('Estrela', 'Completou 50 hábitos', '🌟', '#6f42c1', 'quantidade', 50, 100),
('Lendário', 'Completou 100 hábitos', '🏆', '#dc3545', 'quantidade', 100, 200),

-- Badges Especiais
('Madrugador', 'Completou hábito antes das 8h', '🌅', '#fd7e14', 'tempo', 8, 15),
('Noturno', 'Completou hábito após 22h', '🌙', '#6c757d', 'tempo', 22, 10),
('Preciso', 'Manteve 100% de acerto por uma semana', '🎯', '#e83e8c', 'especial', 7, 75);

-- =====================================================
-- INSERIR DADOS DE EXEMPLO
-- =====================================================

-- Usuários de exemplo
INSERT INTO usuarios (nome, email, senha, pontos, streak_atual, streak_maximo, total_habitos) VALUES
('João Silva', 'joao@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 150, 5, 12, 15),
('Maria Santos', 'maria@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 320, 8, 25, 32),
('Pedro Costa', 'pedro@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 75, 2, 7, 8),
('Ana Oliveira', 'ana@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 450, 15, 30, 45);

-- Hábitos de exemplo
INSERT INTO habitos (usuario_id, titulo, descricao, categoria, pontos_base) VALUES
(1, 'Beber 2L de água', 'Beber pelo menos 2 litros de água por dia', 'saude', 10),
(1, 'Exercício matinal', 'Fazer 30 minutos de exercício pela manhã', 'exercicio', 15),
(2, 'Meditação', 'Meditar por 10 minutos antes de dormir', 'mental', 12),
(2, 'Ler 30 páginas', 'Ler pelo menos 30 páginas de um livro', 'produtividade', 8),
(3, 'Comer frutas', 'Comer pelo menos 3 porções de frutas', 'alimentacao', 10),
(4, 'Caminhar 10k passos', 'Dar pelo menos 10 mil passos por dia', 'exercicio', 20);

-- Completamentos de exemplo (últimos 7 dias)
INSERT INTO habitos_completados (habito_id, usuario_id, data_completado, hora_completado, pontos_ganhos, multiplicador, bonus_streak, bonus_primeiro_dia, total_pontos) VALUES
-- João Silva
(1, 1, CURDATE() - INTERVAL 6 DAY, '08:30:00', 10, 1.5, 0, 5, 20),
(1, 1, CURDATE() - INTERVAL 5 DAY, '09:15:00', 10, 1.0, 0, 0, 10),
(1, 1, CURDATE() - INTERVAL 4 DAY, '08:45:00', 10, 1.5, 25, 0, 40),
(1, 1, CURDATE() - INTERVAL 3 DAY, '10:20:00', 10, 1.0, 0, 0, 10),
(1, 1, CURDATE() - INTERVAL 2 DAY, '08:30:00', 10, 1.5, 0, 0, 15),
(1, 1, CURDATE() - INTERVAL 1 DAY, '09:00:00', 10, 1.0, 25, 0, 35),
(1, 1, CURDATE(), '08:15:00', 10, 1.5, 0, 0, 15),

-- Maria Santos
(3, 2, CURDATE() - INTERVAL 6 DAY, '22:30:00', 12, 1.2, 0, 5, 19),
(3, 2, CURDATE() - INTERVAL 5 DAY, '22:15:00', 12, 1.2, 0, 0, 14),
(3, 2, CURDATE() - INTERVAL 4 DAY, '22:45:00', 12, 1.2, 50, 0, 64),
(3, 2, CURDATE() - INTERVAL 3 DAY, '22:00:00', 12, 1.2, 0, 0, 14),
(3, 2, CURDATE() - INTERVAL 2 DAY, '22:30:00', 12, 1.2, 0, 0, 14),
(3, 2, CURDATE() - INTERVAL 1 DAY, '22:15:00', 12, 1.2, 50, 0, 64),
(3, 2, CURDATE(), '22:45:00', 12, 1.2, 0, 0, 14);

-- Badges conquistadas
INSERT INTO badges_conquistadas (usuario_id, badge_id, pontos_ganhos) VALUES
(1, 1, 25), -- João: Em Chamas
(1, 4, 10), -- João: Iniciante
(2, 1, 25), -- Maria: Em Chamas
(2, 2, 50), -- Maria: Velocista
(2, 4, 10), -- Maria: Iniciante
(2, 5, 50), -- Maria: Disciplinado
(4, 1, 25), -- Ana: Em Chamas
(4, 2, 50), -- Ana: Velocista
(4, 3, 100), -- Ana: Mestre
(4, 4, 10), -- Ana: Iniciante
(4, 5, 50), -- Ana: Disciplinado
(4, 6, 100); -- Ana: Estrela

-- =====================================================
-- ÍNDICES PARA PERFORMANCE
-- =====================================================
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_habitos_usuario ON habitos(usuario_id);
CREATE INDEX idx_habitos_completados_data ON habitos_completados(data_completado);
CREATE INDEX idx_habitos_completados_usuario ON habitos_completados(usuario_id);
CREATE INDEX idx_badges_conquistadas_usuario ON badges_conquistadas(usuario_id);
CREATE INDEX idx_ranking_historico_periodo ON ranking_historico(periodo, data_registro);

-- =====================================================
-- VIEWS ÚTEIS
-- =====================================================

-- View para ranking atual
CREATE VIEW v_ranking_atual AS
SELECT 
    u.id,
    u.nome,
    u.pontos,
    u.streak_atual,
    u.streak_maximo,
    u.total_habitos,
    COUNT(bc.id) as total_badges
FROM usuarios u
LEFT JOIN badges_conquistadas bc ON u.id = bc.usuario_id
WHERE u.ativo = TRUE
GROUP BY u.id
ORDER BY u.pontos DESC, u.streak_atual DESC;

-- View para estatísticas de hábitos
CREATE VIEW v_estatisticas_habitos AS
SELECT 
    h.id,
    h.titulo,
    h.categoria,
    u.nome as usuario,
    COUNT(hc.id) as vezes_completado,
    SUM(hc.total_pontos) as pontos_totais,
    MAX(hc.data_completado) as ultimo_completado
FROM habitos h
JOIN usuarios u ON h.usuario_id = u.id
LEFT JOIN habitos_completados hc ON h.id = hc.habito_id
WHERE h.ativo = TRUE
GROUP BY h.id
ORDER BY pontos_totais DESC;

-- =====================================================
-- PROCEDURES ÚTEIS
-- =====================================================

-- Procedure para calcular pontos de um hábito completado
DELIMITER //
CREATE PROCEDURE CompletarHabito(
    IN p_habito_id INT,
    IN p_usuario_id INT
)
BEGIN
    DECLARE v_pontos_base INT;
    DECLARE v_multiplicador DECIMAL(3,2) DEFAULT 1.00;
    DECLARE v_bonus_streak INT DEFAULT 0;
    DECLARE v_bonus_primeiro_dia INT DEFAULT 0;
    DECLARE v_hora_atual TIME;
    DECLARE v_streak_atual INT;
    DECLARE v_total_pontos INT;
    
    -- Obter pontos base do hábito
    SELECT pontos_base INTO v_pontos_base FROM habitos WHERE id = p_habito_id;
    
    -- Obter hora atual
    SET v_hora_atual = CURTIME();
    
    -- Calcular multiplicador por horário
    IF v_hora_atual < '08:00:00' THEN
        SET v_multiplicador = 1.5;
    ELSEIF v_hora_atual > '22:00:00' THEN
        SET v_multiplicador = 1.2;
    END IF;
    
    -- Verificar se é primeiro hábito do dia
    IF NOT EXISTS (SELECT 1 FROM habitos_completados 
                   WHERE usuario_id = p_usuario_id 
                   AND data_completado = CURDATE()) THEN
        SET v_bonus_primeiro_dia = 5;
    END IF;
    
    -- Calcular streak atual
    SELECT streak_atual INTO v_streak_atual FROM usuarios WHERE id = p_usuario_id;
    
    -- Calcular bônus de streak
    IF v_streak_atual >= 2 THEN
        IF v_streak_atual = 2 THEN -- 3 dias consecutivos
            SET v_bonus_streak = 25;
        ELSEIF v_streak_atual = 6 THEN -- 7 dias consecutivos
            SET v_bonus_streak = 50;
        ELSEIF v_streak_atual = 29 THEN -- 30 dias consecutivos
            SET v_bonus_streak = 100;
        END IF;
    END IF;
    
    -- Calcular total de pontos
    SET v_total_pontos = (v_pontos_base * v_multiplicador) + v_bonus_streak + v_bonus_primeiro_dia;
    
    -- Inserir completamento
    INSERT INTO habitos_completados (
        habito_id, usuario_id, data_completado, hora_completado,
        pontos_ganhos, multiplicador, bonus_streak, bonus_primeiro_dia, total_pontos
    ) VALUES (
        p_habito_id, p_usuario_id, CURDATE(), v_hora_atual,
        v_pontos_base, v_multiplicador, v_bonus_streak, v_bonus_primeiro_dia, v_total_pontos
    );
    
    -- Atualizar usuário
    UPDATE usuarios 
    SET pontos = pontos + v_total_pontos,
        streak_atual = streak_atual + 1,
        streak_maximo = GREATEST(streak_maximo, streak_atual + 1),
        total_habitos = total_habitos + 1
    WHERE id = p_usuario_id;
    
    SELECT v_total_pontos as pontos_ganhos;
END //
DELIMITER ;

-- =====================================================
-- TRIGGERS
-- =====================================================

-- Trigger para verificar conquistas quando hábito é completado
DELIMITER //
CREATE TRIGGER tr_verificar_conquistas
AFTER INSERT ON habitos_completados
FOR EACH ROW
BEGIN
    DECLARE v_total_habitos INT;
    DECLARE v_streak_atual INT;
    DECLARE v_hora_completado TIME;
    
    -- Obter dados do usuário
    SELECT total_habitos, streak_atual INTO v_total_habitos, v_streak_atual
    FROM usuarios WHERE id = NEW.usuario_id;
    
    SET v_hora_completado = NEW.hora_completado;
    
    -- Verificar badges de quantidade
    IF v_total_habitos = 1 THEN
        INSERT IGNORE INTO badges_conquistadas (usuario_id, badge_id, pontos_ganhos)
        SELECT NEW.usuario_id, id, pontos_bonus FROM badges WHERE tipo = 'quantidade' AND requisito_valor = 1;
    ELSEIF v_total_habitos = 10 THEN
        INSERT IGNORE INTO badges_conquistadas (usuario_id, badge_id, pontos_ganhos)
        SELECT NEW.usuario_id, id, pontos_bonus FROM badges WHERE tipo = 'quantidade' AND requisito_valor = 10;
    ELSEIF v_total_habitos = 50 THEN
        INSERT IGNORE INTO badges_conquistadas (usuario_id, badge_id, pontos_ganhos)
        SELECT NEW.usuario_id, id, pontos_bonus FROM badges WHERE tipo = 'quantidade' AND requisito_valor = 50;
    ELSEIF v_total_habitos = 100 THEN
        INSERT IGNORE INTO badges_conquistadas (usuario_id, badge_id, pontos_ganhos)
        SELECT NEW.usuario_id, id, pontos_bonus FROM badges WHERE tipo = 'quantidade' AND requisito_valor = 100;
    END IF;
    
    -- Verificar badges de streak
    IF v_streak_atual = 3 THEN
        INSERT IGNORE INTO badges_conquistadas (usuario_id, badge_id, pontos_ganhos)
        SELECT NEW.usuario_id, id, pontos_bonus FROM badges WHERE tipo = 'streak' AND requisito_valor = 3;
    ELSEIF v_streak_atual = 7 THEN
        INSERT IGNORE INTO badges_conquistadas (usuario_id, badge_id, pontos_ganhos)
        SELECT NEW.usuario_id, id, pontos_bonus FROM badges WHERE tipo = 'streak' AND requisito_valor = 7;
    ELSEIF v_streak_atual = 30 THEN
        INSERT IGNORE INTO badges_conquistadas (usuario_id, badge_id, pontos_ganhos)
        SELECT NEW.usuario_id, id, pontos_bonus FROM badges WHERE tipo = 'streak' AND requisito_valor = 30;
    END IF;
    
    -- Verificar badges de tempo
    IF v_hora_completado < '08:00:00' THEN
        INSERT IGNORE INTO badges_conquistadas (usuario_id, badge_id, pontos_bonus)
        SELECT NEW.usuario_id, id, pontos_bonus FROM badges WHERE tipo = 'tempo' AND requisito_valor = 8;
    ELSEIF v_hora_completado > '22:00:00' THEN
        INSERT IGNORE INTO badges_conquistadas (usuario_id, badge_id, pontos_bonus)
        SELECT NEW.usuario_id, id, pontos_bonus FROM badges WHERE tipo = 'tempo' AND requisito_valor = 22;
    END IF;
    
    -- Atualizar pontos do usuário se ganhou badge
    UPDATE usuarios u
    JOIN badges_conquistadas bc ON u.id = bc.usuario_id
    JOIN badges b ON bc.badge_id = b.id
    SET u.pontos = u.pontos + b.pontos_bonus
    WHERE u.id = NEW.usuario_id AND bc.data_conquista = CURDATE();
END //
DELIMITER ;

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================
