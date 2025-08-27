<?php
/**
 * Configuração do Ambiente
 * Sistema Vida Equilibrada
 */

// Detectar ambiente
$environment = getenv('APP_ENV') ?: 'development';

// Configurações por ambiente
switch ($environment) {
    case 'production':
        // Configurações de produção
        define('DEBUG_MODE', false);
        define('LOG_LEVEL', 'ERROR');
        define('CACHE_ENABLED', true);
        define('COMPRESSION_ENABLED', true);
        break;
        
    case 'staging':
        // Configurações de staging
        define('DEBUG_MODE', true);
        define('LOG_LEVEL', 'WARNING');
        define('CACHE_ENABLED', false);
        define('COMPRESSION_ENABLED', true);
        break;
        
    default:
        // Configurações de desenvolvimento
        define('DEBUG_MODE', true);
        define('LOG_LEVEL', 'DEBUG');
        define('CACHE_ENABLED', false);
        define('COMPRESSION_ENABLED', false);
        break;
}

// Configurações gerais
define('APP_NAME', 'Vida Equilibrada');
define('APP_VERSION', '1.0.0');
define('APP_ENVIRONMENT', $environment);

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de erro
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configurações de log
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

// Função para log personalizado
function logMessage($message, $level = 'INFO') {
    if (!is_dir(__DIR__ . '/logs')) {
        mkdir(__DIR__ . '/logs', 0755, true);
    }
    
    $logFile = __DIR__ . '/logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Função para debug
function debug($data) {
    if (DEBUG_MODE) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}

// Função para sanitizar entrada
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

// Função para gerar token seguro
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Função para verificar se é requisição AJAX
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// Função para obter IP do cliente
function getClientIP() {
    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// Função para formatar data
function formatDate($date, $format = 'd/m/Y H:i') {
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    return $date->format($format);
}

// Função para calcular diferença de datas
function dateDiff($date1, $date2) {
    if (is_string($date1)) {
        $date1 = new DateTime($date1);
    }
    if (is_string($date2)) {
        $date2 = new DateTime($date2);
    }
    
    return $date1->diff($date2);
}

// Função para formatar números
function formatNumber($number, $decimals = 0) {
    return number_format($number, $decimals, ',', '.');
}

// Função para formatar bytes
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

// Função para verificar se string contém apenas números
function isNumeric($string) {
    return preg_match('/^[0-9]+$/', $string);
}

// Função para verificar se string contém apenas letras
function isAlpha($string) {
    return preg_match('/^[a-zA-ZÀ-ÿ\s]+$/', $string);
}

// Função para verificar se string contém apenas letras e números
function isAlphanumeric($string) {
    return preg_match('/^[a-zA-Z0-9À-ÿ\s]+$/', $string);
}

// Função para limitar string
function limitString($string, $limit = 100, $suffix = '...') {
    if (strlen($string) <= $limit) {
        return $string;
    }
    
    return substr($string, 0, $limit) . $suffix;
}

// Função para gerar slug
function generateSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

// Função para verificar se arquivo é imagem
function isImage($filename) {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filename);
    finfo_close($finfo);
    
    return in_array($mimeType, $allowedTypes);
}

// Função para redimensionar imagem
function resizeImage($source, $destination, $width, $height, $quality = 80) {
    list($originalWidth, $originalHeight) = getimagesize($source);
    
    $ratio = min($width / $originalWidth, $height / $originalHeight);
    $newWidth = $originalWidth * $ratio;
    $newHeight = $originalHeight * $ratio;
    
    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    
    $sourceImage = imagecreatefromjpeg($source);
    imagecopyresampled($thumb, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
    
    imagejpeg($thumb, $destination, $quality);
    imagedestroy($thumb);
    imagedestroy($sourceImage);
}

// Função para criar diretório se não existir
function createDirectory($path) {
    if (!is_dir($path)) {
        return mkdir($path, 0755, true);
    }
    return true;
}

// Função para listar arquivos em diretório
function listFiles($directory, $extension = null) {
    $files = [];
    
    if (is_dir($directory)) {
        $items = scandir($directory);
        
        foreach ($items as $item) {
            if ($item != '.' && $item != '..') {
                $filePath = $directory . '/' . $item;
                
                if (is_file($filePath)) {
                    if ($extension === null || pathinfo($filePath, PATHINFO_EXTENSION) === $extension) {
                        $files[] = $filePath;
                    }
                }
            }
        }
    }
    
    return $files;
}

// Função para deletar arquivo com segurança
function deleteFile($filePath) {
    if (file_exists($filePath) && is_file($filePath)) {
        return unlink($filePath);
    }
    return false;
}

// Função para copiar arquivo
function copyFile($source, $destination) {
    if (file_exists($source)) {
        return copy($source, $destination);
    }
    return false;
}

// Função para mover arquivo
function moveFile($source, $destination) {
    if (file_exists($source)) {
        return rename($source, $destination);
    }
    return false;
}

// Função para obter extensão de arquivo
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Função para obter nome do arquivo sem extensão
function getFileName($filename) {
    return pathinfo($filename, PATHINFO_FILENAME);
}

// Função para verificar se URL é válida
function isValidUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL);
}

// Função para fazer requisição HTTP
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

// Função para enviar email (placeholder)
function sendEmail($to, $subject, $message, $from = null) {
    // Implementar envio de email
    // Por enquanto, apenas log
    logMessage("Email enviado para: $to - Assunto: $subject", 'INFO');
    return true;
}

// Função para gerar senha aleatória
function generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    
    return $password;
}

// Função para verificar força da senha
function checkPasswordStrength($password) {
    $score = 0;
    
    if (strlen($password) >= 8) $score++;
    if (preg_match('/[a-z]/', $password)) $score++;
    if (preg_match('/[A-Z]/', $password)) $score++;
    if (preg_match('/[0-9]/', $password)) $score++;
    if (preg_match('/[^a-zA-Z0-9]/', $password)) $score++;
    
    return $score;
}

// Função para obter descrição da força da senha
function getPasswordStrengthDescription($password) {
    $strength = checkPasswordStrength($password);
    
    switch ($strength) {
        case 0:
        case 1:
            return 'Muito fraca';
        case 2:
            return 'Fraca';
        case 3:
            return 'Média';
        case 4:
            return 'Forte';
        case 5:
            return 'Muito forte';
        default:
            return 'Desconhecida';
    }
}

// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

// Iniciar sessão se não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log de inicialização
logMessage("Aplicação iniciada em ambiente: $environment", 'INFO');
?>
