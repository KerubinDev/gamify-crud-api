<?php
/**
 * FIT BATTLE - Configuração do Banco de Dados
 * Sistema de Competição Fitness
 */

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'fit_battle');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configurações da aplicação
define('APP_NAME', 'FIT BATTLE');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/fit-battle');

// Configurações JWT
define('JWT_SECRET', 'fit_battle_secret_key_2025');
define('JWT_EXPIRATION', 86400); // 24 horas

// Configurações de pontos
define('POINTS_PER_LEVEL', 100);
define('STREAK_BONUS_3_DAYS', 25);
define('STREAK_BONUS_7_DAYS', 50);
define('STREAK_BONUS_30_DAYS', 100);

// Configurações de horário
define('MORNING_BONUS_START', 5);
define('MORNING_BONUS_END', 8);
define('NIGHT_BONUS_START', 22);
define('NIGHT_BONUS_END', 2);

// Configurações de intensidade
define('INTENSITY_MULTIPLIERS', [
    'low' => 0.8,
    'medium' => 1.0,
    'high' => 1.3,
    'extreme' => 1.6
]);

// Classe de conexão com o banco
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Erro na query: " . $e->getMessage());
        }
    }
    
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function insert($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $this->connection->lastInsertId();
    }
    
    public function update($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function delete($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
}

// Função helper para obter conexão
function getDB() {
    return Database::getInstance()->getConnection();
}

// Função helper para executar queries
function dbQuery($sql, $params = []) {
    return Database::getInstance()->query($sql, $params);
}

// Função helper para buscar um registro
function dbFetch($sql, $params = []) {
    return Database::getInstance()->fetch($sql, $params);
}

// Função helper para buscar todos os registros
function dbFetchAll($sql, $params = []) {
    return Database::getInstance()->fetchAll($sql, $params);
}

// Função helper para inserir
function dbInsert($sql, $params = []) {
    return Database::getInstance()->insert($sql, $params);
}

// Função helper para atualizar
function dbUpdate($sql, $params = []) {
    return Database::getInstance()->update($sql, $params);
}

// Função helper para deletar
function dbDelete($sql, $params = []) {
    return Database::getInstance()->delete($sql, $params);
}
?>
