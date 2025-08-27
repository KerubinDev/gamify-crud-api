<?php
/**
 * Instalação Rápida - Sistema Vida Equilibrada
 * Para quem tem preguiça de configurar! 😄
 */

echo "🚀 Instalação Rápida - Vida Equilibrada\n";
echo "=====================================\n\n";

// Configurações padrão (XAMPP/Laragon)
$config = [
    'db_host' => 'localhost',
    'db_name' => 'vida_equilibrada',
    'db_user' => 'root',
    'db_pass' => '',
    'app_url' => 'http://localhost/gamify-crud-api'
];

echo "📋 Configurações padrão:\n";
echo "Host: {$config['db_host']}\n";
echo "Banco: {$config['db_name']}\n";
echo "Usuário: {$config['db_user']}\n";
echo "Senha: (vazia)\n";
echo "URL: {$config['app_url']}\n\n";

echo "⚙️ Iniciando instalação...\n\n";

try {
    // Passo 1: Testar conexão
    echo "1️⃣ Testando conexão com MySQL...\n";
    $pdo = new PDO(
        "mysql:host={$config['db_host']};charset=utf8mb4",
        $config['db_user'],
        $config['db_pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Conexão estabelecida!\n\n";
    
    // Passo 2: Criar banco
    echo "2️⃣ Criando banco de dados...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Banco '{$config['db_name']}' criado!\n\n";
    
    // Passo 3: Conectar ao banco
    echo "3️⃣ Conectando ao banco...\n";
    $pdo = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
        $config['db_user'],
        $config['db_pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Conectado ao banco!\n\n";
    
    // Passo 4: Importar esquema
    echo "4️⃣ Importando esquema do banco...\n";
    $sqlFile = __DIR__ . '/database/database.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("❌ Arquivo database.sql não encontrado!");
    }
    
    $sql = file_get_contents($sqlFile);
    $commands = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($commands as $command) {
        if (!empty($command)) {
            $pdo->exec($command);
        }
    }
    echo "✅ Esquema importado!\n\n";
    
    // Passo 5: Criar configuração
    echo "5️⃣ Criando arquivo de configuração...\n";
    $configContent = generateConfigFile($config);
    
    $configFile = __DIR__ . '/api/config/database.php';
    if (!is_dir(dirname($configFile))) {
        mkdir(dirname($configFile), 0755, true);
    }
    
    if (file_put_contents($configFile, $configContent) === false) {
        throw new Exception("❌ Erro ao criar arquivo de configuração!");
    }
    echo "✅ Configuração criada!\n\n";
    
    // Passo 6: Criar diretórios
    echo "6️⃣ Criando diretórios necessários...\n";
    $dirs = ['logs', 'uploads', 'cache'];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    echo "✅ Diretórios criados!\n\n";
    
    // Passo 7: Testar funcionalidades
    echo "7️⃣ Testando funcionalidades...\n";
    $tables = ['usuarios', 'habitos', 'habitos_completados', 'badges', 'badges_conquistadas', 'ranking_historico'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() === 0) {
            throw new Exception("❌ Tabela '$table' não foi criada!");
        }
    }
    echo "✅ Todas as tabelas criadas!\n\n";
    
    // Passo 8: Finalizar
    echo "8️⃣ Finalizando instalação...\n";
    $lockFile = __DIR__ . '/installed.lock';
    $lockContent = json_encode([
        'installed_at' => date('Y-m-d H:i:s'),
        'version' => '1.0.0',
        'config' => $config
    ], JSON_PRETTY_PRINT);
    
    file_put_contents($lockFile, $lockContent);
    echo "✅ Instalação finalizada!\n\n";
    
    // Sucesso!
    echo "🎉 INSTALAÇÃO CONCLUÍDA COM SUCESSO! 🎉\n";
    echo "=====================================\n\n";
    echo "📱 Acesse sua aplicação em:\n";
    echo "   {$config['app_url']}\n\n";
    echo "🎮 Ou abra diretamente:\n";
    echo "   {$config['app_url']}/index.html\n\n";
    echo "📊 Para testar a API:\n";
    echo "   {$config['app_url']}/api/\n\n";
    echo "🔧 Configurações salvas em:\n";
    echo "   api/config/database.php\n\n";
    echo "📝 Logs em:\n";
    echo "   logs/app.log\n\n";
    echo "✨ Boa sorte em sua jornada de gamificação! ⚔️🏆\n";
    
} catch (Exception $e) {
    echo "❌ ERRO NA INSTALAÇÃO!\n";
    echo "=====================\n\n";
    echo "Erro: " . $e->getMessage() . "\n\n";
    echo "🔧 Soluções possíveis:\n";
    echo "1. Verifique se o MySQL está rodando\n";
    echo "2. Verifique se o usuário 'root' existe\n";
    echo "3. Verifique se o arquivo database.sql existe\n";
    echo "4. Verifique as permissões de escrita\n\n";
    echo "💡 Dica: Use XAMPP ou Laragon para facilitar!\n";
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
?>

