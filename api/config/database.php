<?php
/**
 * Configuração do Banco de Dados
 * Sistema Vida Equilibrada
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'vida_equilibrada';
    private $username = 'root';
    private $password = '';
    private $conn;

    /**
     * Conectar ao banco de dados
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                )
            );
        } catch(PDOException $exception) {
            echo "Erro de conexão: " . $exception->getMessage();
        }

        return $this->conn;
    }

    /**
     * Testar conexão
     */
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            if ($conn) {
                return array(
                    'status' => 'success',
                    'message' => 'Conexão estabelecida com sucesso!'
                );
            }
        } catch(Exception $e) {
            return array(
                'status' => 'error',
                'message' => 'Erro na conexão: ' . $e->getMessage()
            );
        }
    }
}

// Configurações adicionais
define('DB_HOST', 'localhost');
define('DB_NAME', 'vida_equilibrada');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configurações da aplicação
define('APP_NAME', 'Vida Equilibrada');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/gamify-crud-api');

// Configurações de segurança
define('JWT_SECRET', 'vida_equilibrada_secret_key_2025');
define('PASSWORD_COST', 12);

// Configurações de pontuação
define('PONTOS_BASE_HABITO', 10);
define('BONUS_PRIMEIRO_DIA', 5);
define('BONUS_STREAK_3', 25);
define('BONUS_STREAK_7', 50);
define('BONUS_STREAK_30', 100);
define('MULTIPLICADOR_MADRUGADOR', 1.5);
define('MULTIPLICADOR_NOTURNO', 1.2);

// Configurações de horário
define('HORA_MADRUGADOR', '08:00:00');
define('HORA_NOTURNO', '22:00:00');

// Configurações de paginação
define('ITENS_POR_PAGINA', 10);
define('MAX_ITENS_POR_PAGINA', 100);

// Configurações de cache
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 300); // 5 minutos

// Configurações de logs
define('LOG_ENABLED', true);
define('LOG_FILE', 'logs/app.log');

// Configurações de upload
define('UPLOAD_MAX_SIZE', 5242880); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Configurações de email (para futuras funcionalidades)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');

// Configurações de notificações
define('NOTIFICACOES_ENABLED', true);
define('NOTIFICACOES_EMAIL', false);
define('NOTIFICACOES_PUSH', false);

// Configurações de gamificação
define('NIVEIS_ENABLED', true);
define('NIVEL_MAXIMO', 100);
define('PONTOS_POR_NIVEL', 1000);

// Configurações de ranking
define('RANKING_ATUALIZACAO_AUTOMATICA', true);
define('RANKING_CACHE_DURATION', 600); // 10 minutos

// Configurações de badges
define('BADGES_AUTO_ATRIBUICAO', true);
define('BADGES_NOTIFICACAO', true);

// Configurações de streak
define('STREAK_RESET_AUTOMATICO', true);
define('STREAK_TOLERANCIA_DIAS', 1);

// Configurações de backup
define('BACKUP_ENABLED', false);
define('BACKUP_FREQUENCY', 'daily'); // daily, weekly, monthly
define('BACKUP_RETENTION_DAYS', 30);

// Configurações de manutenção
define('MAINTENANCE_MODE', false);
define('MAINTENANCE_MESSAGE', 'Sistema em manutenção. Volte em breve!');

// Configurações de debug
define('DEBUG_MODE', true);
define('SHOW_ERRORS', true);
define('LOG_QUERIES', false);

// Configurações de API
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 1000); // requests per hour
define('API_CORS_ENABLED', true);
define('API_CORS_ORIGINS', ['http://localhost:3000', 'http://localhost:8080']);

// Configurações de sessão
define('SESSION_LIFETIME', 3600); // 1 hora
define('SESSION_SECURE', false);
define('SESSION_HTTP_ONLY', true);

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de locale
setlocale(LC_ALL, 'pt_BR.utf-8', 'pt_BR', 'Portuguese_Brazil');

// Configurações de encoding
ini_set('default_charset', 'UTF-8');

// Configurações de memória
ini_set('memory_limit', '256M');

// Configurações de tempo de execução
set_time_limit(300); // 5 minutos

// Configurações de upload
ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '10M');

// Configurações de erro
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Função para obter configuração
function getConfig($key, $default = null) {
    if (defined($key)) {
        return constant($key);
    }
    return $default;
}

// Função para validar configuração
function validateConfig() {
    $required_configs = [
        'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS',
        'JWT_SECRET', 'APP_NAME', 'APP_URL'
    ];
    
    $missing = [];
    foreach ($required_configs as $config) {
        if (!defined($config) || empty(constant($config))) {
            $missing[] = $config;
        }
    }
    
    if (!empty($missing)) {
        throw new Exception('Configurações obrigatórias não definidas: ' . implode(', ', $missing));
    }
    
    return true;
}

// Função para log
function logMessage($message, $level = 'INFO') {
    if (!LOG_ENABLED) return;
    
    $log_entry = date('Y-m-d H:i:s') . " [{$level}] " . $message . PHP_EOL;
    
    if (defined('LOG_FILE') && !empty(LOG_FILE)) {
        $log_dir = dirname(LOG_FILE);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        file_put_contents(LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    if (DEBUG_MODE) {
        echo $log_entry;
    }
}

// Função para sanitizar dados
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Função para validar email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Função para gerar token único
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Função para hash de senha
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
}

// Função para verificar senha
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Função para calcular pontos
function calcularPontos($pontos_base, $multiplicador = 1.0, $bonus_streak = 0, $bonus_primeiro_dia = 0) {
    return ($pontos_base * $multiplicador) + $bonus_streak + $bonus_primeiro_dia;
}

// Função para verificar multiplicador por horário
function getMultiplicadorHorario($hora) {
    $hora_atual = strtotime($hora);
    $hora_madrugador = strtotime(HORA_MADRUGADOR);
    $hora_noturno = strtotime(HORA_NOTURNO);
    
    if ($hora_atual < $hora_madrugador) {
        return MULTIPLICADOR_MADRUGADOR;
    } elseif ($hora_atual > $hora_noturno) {
        return MULTIPLICADOR_NOTURNO;
    }
    
    return 1.0;
}

// Função para formatar data
function formatarData($data, $formato = 'd/m/Y') {
    if (is_string($data)) {
        $data = new DateTime($data);
    }
    return $data->format($formato);
}

// Função para formatar hora
function formatarHora($hora, $formato = 'H:i') {
    if (is_string($hora)) {
        $hora = new DateTime($hora);
    }
    return $hora->format($formato);
}

// Função para calcular diferença de dias
function calcularDiferencaDias($data1, $data2 = null) {
    if ($data2 === null) {
        $data2 = new DateTime();
    }
    
    if (is_string($data1)) {
        $data1 = new DateTime($data1);
    }
    if (is_string($data2)) {
        $data2 = new DateTime($data2);
    }
    
    return $data1->diff($data2)->days;
}

// Função para verificar se é hoje
function ehHoje($data) {
    if (is_string($data)) {
        $data = new DateTime($data);
    }
    $hoje = new DateTime();
    return $data->format('Y-m-d') === $hoje->format('Y-m-d');
}

// Função para verificar se é ontem
function ehOntem($data) {
    if (is_string($data)) {
        $data = new DateTime($data);
    }
    $ontem = new DateTime('yesterday');
    return $data->format('Y-m-d') === $ontem->format('Y-m-d');
}

// Função para obter início da semana
function getInicioSemana($data = null) {
    if ($data === null) {
        $data = new DateTime();
    } elseif (is_string($data)) {
        $data = new DateTime($data);
    }
    
    $data->modify('monday this week');
    return $data->format('Y-m-d');
}

// Função para obter fim da semana
function getFimSemana($data = null) {
    if ($data === null) {
        $data = new DateTime();
    } elseif (is_string($data)) {
        $data = new DateTime($data);
    }
    
    $data->modify('sunday this week');
    return $data->format('Y-m-d');
}

// Função para obter início do mês
function getInicioMes($data = null) {
    if ($data === null) {
        $data = new DateTime();
    } elseif (is_string($data)) {
        $data = new DateTime($data);
    }
    
    return $data->format('Y-m-01');
}

// Função para obter fim do mês
function getFimMes($data = null) {
    if ($data === null) {
        $data = new DateTime();
    } elseif (is_string($data)) {
        $data = new DateTime($data);
    }
    
    return $data->format('Y-m-t');
}

// Função para validar configuração inicial
try {
    validateConfig();
    logMessage('Configuração validada com sucesso');
} catch (Exception $e) {
    logMessage('Erro na validação da configuração: ' . $e->getMessage(), 'ERROR');
    die('Erro de configuração: ' . $e->getMessage());
}
?>
