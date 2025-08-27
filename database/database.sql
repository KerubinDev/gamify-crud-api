-- =====================================================
-- SISTEMA VIDA EQUILIBRADA - BANCO DE DADOS (CORRIGIDO)
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
-- TABELA DE ESTATÍSTICAS
-- =====================================================
CREATE TABLE estatisticas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_registro DATE NOT NULL,
    habitos_completados INT DEFAULT 0,
    pontos_ganhos INT DEFAULT 0,
    streak_atual INT DEFAULT 0,
    badges_conquistadas INT DEFAULT 0,
    tempo_ativo_minutos INT DEFAULT 0,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_data (usuario_id, data_registro)
);

-- =====================================================
-- INSERIR DADOS INICIAIS
-- =====================================================

-- Inserir badges padrão
INSERT INTO badges (nome, descricao, icone, cor, tipo, requisito_valor, pontos_bonus) VALUES
('Primeiro Passo', 'Complete seu primeiro hábito', '🎯', '#28a745', 'quantidade', 1, 10),
('Streak 3 Dias', 'Mantenha um streak de 3 dias', '🔥', '#ffc107', 'streak', 3, 25),
('Streak 7 Dias', 'Mantenha um streak de 7 dias', '⚡', '#fd7e14', 'streak', 7, 50),
('Streak 30 Dias', 'Mantenha um streak de 30 dias', '👑', '#e83e8c', 'streak', 30, 100),
('Madrugador', 'Complete um hábito antes das 8h', '🌅', '#17a2b8', 'especial', 1, 15),
('Noturno', 'Complete um hábito após as 22h', '🌙', '#6f42c1', 'especial', 1, 10),
('Produtivo', 'Complete 5 hábitos em um dia', '🚀', '#dc3545', 'quantidade', 5, 30),
('Consistente', 'Complete hábitos por 5 dias seguidos', '📈', '#20c997', 'streak', 5, 40);

-- Inserir usuário admin padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, pontos, ativo) VALUES
('Administrador', 'admin@vidaequilibrada.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewdBPj8J/8KqKqKq', 0, TRUE);

-- =====================================================
-- ÍNDICES PARA PERFORMANCE
-- =====================================================
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_habitos_usuario ON habitos(usuario_id);
CREATE INDEX idx_habitos_categoria ON habitos(categoria);
CREATE INDEX idx_habitos_completados_data ON habitos_completados(data_completado);
CREATE INDEX idx_habitos_completados_usuario ON habitos_completados(usuario_id);
CREATE INDEX idx_badges_conquistadas_usuario ON badges_conquistadas(usuario_id);
CREATE INDEX idx_ranking_periodo ON ranking_historico(periodo, data_registro);
CREATE INDEX idx_estatisticas_usuario_data ON estatisticas(usuario_id, data_registro);
