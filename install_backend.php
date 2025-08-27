<?php
/**
 * Backend da Instalação Automática
 * Sistema Vida Equilibrada
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Obter dados da requisição
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

$step = $input['step'] ?? 0;
$action = $input['action'] ?? '';
$config = $input['config'] ?? [];

try {
    switch ($action) {
        case 'test_connection':
            $result = testDatabaseConnection($config);
            break;
            
        case 'create_database':
            $result = createDatabase($config);
            break;
            
        case 'import_schema':
            $result = importDatabaseSchema($config);
            break;
            
        case 'configure_app':
            $result = configureApplication($config);
            break;
            
        case 'test_features':
            $result = testApplicationFeatures($config);
            break;
            
        case 'finalize':
            $result = finalizeInstallation($config);
            break;
            
        default:
            throw new Exception('Ação não reconhecida: ' . $action);
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Testa conexão com o banco de dados
 */
function testDatabaseConnection($config) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['db_host']};charset=utf8mb4",
            $config['db_user'],
            $config['db_pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        
        return [
            'success' => true,
            'message' => 'Conexão com MySQL estabelecida com sucesso!'
        ];
        
    } catch (PDOException $e) {
        throw new Exception('Erro ao conectar com MySQL: ' . $e->getMessage());
    }
}

/**
 * Cria o banco de dados
 */
function createDatabase($config) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['db_host']};charset=utf8mb4",
            $config['db_user'],
            $config['db_pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Criar banco se não existir
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        return [
            'success' => true,
            'message' => "Banco de dados '{$config['db_name']}' criado com sucesso!"
        ];
        
    } catch (PDOException $e) {
        throw new Exception('Erro ao criar banco de dados: ' . $e->getMessage());
    }
}

/**
 * Importa o esquema do banco de dados
 */
function importDatabaseSchema($config) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
            $config['db_user'],
            $config['db_pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Ler arquivo SQL
        $sqlFile = __DIR__ . '/database/database.sql';
        
        if (!file_exists($sqlFile)) {
            throw new Exception('Arquivo database.sql não encontrado!');
        }
        
        $sql = file_get_contents($sqlFile);
        
        // Dividir em comandos individuais
        $commands = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($commands as $command) {
            if (!empty($command)) {
                $pdo->exec($command);
            }
        }
        
        return [
            'success' => true,
            'message' => 'Esquema do banco de dados importado com sucesso!'
        ];
        
    } catch (PDOException $e) {
        throw new Exception('Erro ao importar esquema: ' . $e->getMessage());
    }
}

/**
 * Configura a aplicação
 */
function configureApplication($config) {
    try {
        // Criar arquivo de configuração
        $configContent = generateConfigFile($config);
        
        $configFile = __DIR__ . '/api/config/database.php';
        
        if (!is_dir(dirname($configFile))) {
            mkdir(dirname($configFile), 0755, true);
        }
        
        if (file_put_contents($configFile, $configContent) === false) {
            throw new Exception('Erro ao criar arquivo de configuração!');
        }
        
        // Criar diretório de logs
        $logsDir = __DIR__ . '/logs';
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true);
        }
        
        return [
            'success' => true,
            'message' => 'Aplicação configurada com sucesso!'
        ];
        
    } catch (Exception $e) {
        throw new Exception('Erro ao configurar aplicação: ' . $e->getMessage());
    }
}

/**
 * Testa funcionalidades da aplicação
 */
function testApplicationFeatures($config) {
    try {
        // Testar conexão com banco configurado
        $pdo = new PDO(
            "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
            $config['db_user'],
            $config['db_pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Verificar se tabelas foram criadas
        $tables = ['usuarios', 'habitos', 'habitos_completados', 'badges', 'badges_conquistadas', 'ranking_historico'];
        
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() === 0) {
                throw new Exception("Tabela '$table' não foi criada!");
            }
        }
        
        // Testar API
        $apiUrl = rtrim($config['app_url'], '/') . '/api/health';
        $response = makeHttpRequest($apiUrl);
        
        if ($response['http_code'] !== 200) {
            throw new Exception('API não está respondendo corretamente!');
        }
        
        return [
            'success' => true,
            'message' => 'Todas as funcionalidades testadas com sucesso!'
        ];
        
    } catch (Exception $e) {
        throw new Exception('Erro ao testar funcionalidades: ' . $e->getMessage());
    }
}

/**
 * Finaliza a instalação
 */
function finalizeInstallation($config) {
    try {
        // Criar arquivo de lock
        $lockFile = __DIR__ . '/installed.lock';
        $lockContent = json_encode([
            'installed_at' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'config' => $config
        ], JSON_PRETTY_PRINT);
        
        if (file_put_contents($lockFile, $lockContent) === false) {
            throw new Exception('Erro ao criar arquivo de lock!');
        }
        
        // Criar arquivo de informações da instalação
        $infoFile = __DIR__ . '/install_info.txt';
        $infoContent = "Sistema Vida Equilibrada - Instalado com sucesso!\n";
        $infoContent .= "Data da instalação: " . date('Y-m-d H:i:s') . "\n";
        $infoContent .= "Versão: 1.0.0\n";
        $infoContent .= "URL: " . $config['app_url'] . "\n";
        $infoContent .= "Banco: " . $config['db_name'] . "\n";
        
        file_put_contents($infoFile, $infoContent);
        
        return [
            'success' => true,
            'message' => 'Instalação finalizada com sucesso!'
        ];
        
    } catch (Exception $e) {
        throw new Exception('Erro ao finalizar instalação: ' . $e->getMessage());
    }
}

/**
 * Gera arquivo de configuração
 */
function generateConfigFile($config) {
    return "<?php
/**
 * Configuração do Banco de Dados
 * Sistema Vida Equilibrada
 * Gerado automaticamente em " . date('Y-m-d H:i:s') . "
 */

// Configurações do banco de dados
define('DB_HOST', '{$config['db_host']}');
define('DB_NAME', '{$config['db_name']}');
define('DB_USER', '{$config['db_user']}');
define('DB_PASS', '{$config['db_pass']}');
define('DB_CHARSET', 'utf8mb4');

// Configurações da aplicação
define('APP_NAME', 'Vida Equilibrada');
define('APP_VERSION', '1.0.0');
define('APP_URL', '{$config['app_url']}');
define('APP_ENVIRONMENT', 'production');

// Configurações de segurança
define('JWT_SECRET', '" . bin2hex(random_bytes(32)) . "');
define('PASSWORD_SALT', '" . bin2hex(random_bytes(16)) . "');

// Configurações de gamificação
define('PONTOS_BASE_HABITO', 10);
define('BONUS_STREAK_3', 25);
define('BONUS_STREAK_7', 50);
define('BONUS_STREAK_30', 100);
define('MULTIPLICADOR_MADRUGADOR', 1.5);
define('MULTIPLICADOR_NOITE', 1.2);
define('PONTOS_POR_NIVEL', 100);

// Configurações de API
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 1000);
define('API_RATE_WINDOW', 3600);

// Configurações de cache
define('CACHE_ENABLED', true);
define('CACHE_TTL', 3600);

// Configurações de log
define('LOG_ENABLED', true);
define('LOG_LEVEL', 'INFO');
define('LOG_FILE', __DIR__ . '/../../logs/app.log');

// Configurações de manutenção
define('MAINTENANCE_MODE', false);
define('MAINTENANCE_MESSAGE', 'Sistema em manutenção. Volte em breve!');

// Configurações de upload
define('UPLOAD_MAX_SIZE', 10485760); // 10MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// Configurações de email
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_FROM', 'noreply@vidaequilibrada.com');

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset(\$_SERVER['HTTPS']));

// Configurações de erro
if (APP_ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configurações de log
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

// Função para log personalizado
function logMessage(\$message, \$level = 'INFO') {
    if (!LOG_ENABLED) return;
    
    if (!is_dir(dirname(LOG_FILE))) {
        mkdir(dirname(LOG_FILE), 0755, true);
    }
    
    \$timestamp = date('Y-m-d H:i:s');
    \$logEntry = \"[\$timestamp] [\$level] \$message\" . PHP_EOL;
    
    file_put_contents(LOG_FILE, \$logEntry, FILE_APPEND | LOCK_EX);
}

// Função para sanitizar entrada
function sanitizeInput(\$data) {
    if (is_array(\$data)) {
        return array_map('sanitizeInput', \$data);
    }
    return htmlspecialchars(trim(\$data), ENT_QUOTES, 'UTF-8');
}

// Função para validar email
function validateEmail(\$email) {
    return filter_var(\$email, FILTER_VALIDATE_EMAIL);
}

// Função para gerar token
function generateToken(\$length = 32) {
    return bin2hex(random_bytes(\$length));
}

// Função para hash de senha
function hashPassword(\$password) {
    return password_hash(\$password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Função para verificar senha
function verifyPassword(\$password, \$hash) {
    return password_verify(\$password, \$hash);
}

// Função para calcular pontos
function calcularPontos(\$pontos_base, \$streak_atual, \$hora_completamento) {
    \$pontos = \$pontos_base;
    
    // Bônus de streak
    if (\$streak_atual >= 30) {
        \$pontos += BONUS_STREAK_30;
    } elseif (\$streak_atual >= 7) {
        \$pontos += BONUS_STREAK_7;
    } elseif (\$streak_atual >= 3) {
        \$pontos += BONUS_STREAK_3;
    }
    
    // Multiplicador por horário
    \$multiplicador = getMultiplicadorHorario(\$hora_completamento);
    \$pontos = \$pontos * \$multiplicador;
    
    return round(\$pontos);
}

// Função para obter multiplicador por horário
function getMultiplicadorHorario(\$hora) {
    \$hora_int = (int) \$hora;
    
    if (\$hora_int >= 5 && \$hora_int <= 7) {
        return MULTIPLICADOR_MADRUGADOR;
    } elseif (\$hora_int >= 22 || \$hora_int <= 0) {
        return MULTIPLICADOR_NOITE;
    }
    
    return 1.0;
}

// Classe de conexão com banco de dados
class Database {
    private \$connection;
    private static \$instance = null;
    
    private function __construct() {
        try {
            \$this->connection = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException \$e) {
            logMessage('Erro de conexão com banco: ' . \$e->getMessage(), 'ERROR');
            throw new Exception('Erro de conexão com banco de dados');
        }
    }
    
    public static function getInstance() {
        if (self::\$instance === null) {
            self::\$instance = new self();
        }
        return self::\$instance;
    }
    
    public function getConnection() {
        return \$this->connection;
    }
    
    public function testConnection() {
        try {
            \$stmt = \$this->connection->query('SELECT 1');
            return \$stmt !== false;
        } catch (PDOException \$e) {
            return false;
        }
    }
}

// Log de inicialização
logMessage('Sistema iniciado - Ambiente: ' . APP_ENVIRONMENT, 'INFO');
?>";
}

/**
 * Faz requisição HTTP
 */
function makeHttpRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'error' => $error
    ];
}
?>

