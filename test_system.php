<?php
/**
 * Teste do Sistema - Vida Equilibrada
 * Verifica se tudo está funcionando corretamente
 */

echo "🧪 Teste do Sistema - Vida Equilibrada\n";
echo "====================================\n\n";

$tests = [];
$passed = 0;
$failed = 0;

// Teste 1: Verificar arquivos essenciais
echo "1️⃣ Verificando arquivos essenciais...\n";
$essentialFiles = [
    'index.html',
    'api/config/database.php',
    'api/endpoints/index.php',
    'database/database.sql',
    'assets/css/style.css',
    'assets/js/app.js'
];

foreach ($essentialFiles as $file) {
    if (file_exists($file)) {
        echo "   ✅ $file\n";
        $tests[] = ['file' => $file, 'status' => 'OK'];
        $passed++;
    } else {
        echo "   ❌ $file\n";
        $tests[] = ['file' => $file, 'status' => 'MISSING'];
        $failed++;
    }
}

// Teste 2: Verificar configuração do banco
echo "\n2️⃣ Verificando configuração do banco...\n";
if (file_exists('api/config/database.php')) {
    try {
        require_once 'api/config/database.php';
        echo "   ✅ Arquivo de configuração carregado\n";
        $tests[] = ['config' => 'database.php', 'status' => 'OK'];
        $passed++;
    } catch (Exception $e) {
        echo "   ❌ Erro ao carregar configuração: " . $e->getMessage() . "\n";
        $tests[] = ['config' => 'database.php', 'status' => 'ERROR'];
        $failed++;
    }
} else {
    echo "   ❌ Arquivo de configuração não encontrado\n";
    $tests[] = ['config' => 'database.php', 'status' => 'MISSING'];
    $failed++;
}

// Teste 3: Verificar conexão com banco
echo "\n3️⃣ Testando conexão com banco de dados...\n";
if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER')) {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "   ✅ Conexão com banco estabelecida\n";
        $tests[] = ['database' => 'connection', 'status' => 'OK'];
        $passed++;
        
        // Teste 4: Verificar tabelas
        echo "\n4️⃣ Verificando tabelas do banco...\n";
        $tables = ['usuarios', 'habitos', 'habitos_completados', 'badges', 'badges_conquistadas', 'ranking_historico'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "   ✅ Tabela '$table' existe\n";
                $tests[] = ['table' => $table, 'status' => 'OK'];
                $passed++;
            } else {
                echo "   ❌ Tabela '$table' não encontrada\n";
                $tests[] = ['table' => $table, 'status' => 'MISSING'];
                $failed++;
            }
        }
        
    } catch (PDOException $e) {
        echo "   ❌ Erro de conexão: " . $e->getMessage() . "\n";
        $tests[] = ['database' => 'connection', 'status' => 'ERROR'];
        $failed++;
    }
} else {
    echo "   ❌ Constantes de banco não definidas\n";
    $tests[] = ['database' => 'constants', 'status' => 'MISSING'];
    $failed++;
}

// Teste 5: Verificar API
echo "\n5️⃣ Testando API...\n";
$apiUrl = 'http://localhost/gamify-crud-api/api/';
$response = makeHttpRequest($apiUrl);

if ($response['http_code'] === 200) {
    echo "   ✅ API respondendo corretamente\n";
    $tests[] = ['api' => 'endpoint', 'status' => 'OK'];
    $passed++;
} else {
    echo "   ❌ API não está respondendo (HTTP {$response['http_code']})\n";
    $tests[] = ['api' => 'endpoint', 'status' => 'ERROR'];
    $failed++;
}

// Teste 6: Verificar frontend
echo "\n6️⃣ Verificando frontend...\n";
if (file_exists('index.html')) {
    $html = file_get_contents('index.html');
    if (strpos($html, 'Vida Equilibrada') !== false) {
        echo "   ✅ Frontend carregado corretamente\n";
        $tests[] = ['frontend' => 'html', 'status' => 'OK'];
        $passed++;
    } else {
        echo "   ❌ Frontend não contém conteúdo esperado\n";
        $tests[] = ['frontend' => 'html', 'status' => 'ERROR'];
        $failed++;
    }
} else {
    echo "   ❌ Arquivo index.html não encontrado\n";
    $tests[] = ['frontend' => 'html', 'status' => 'MISSING'];
    $failed++;
}

// Teste 7: Verificar assets
echo "\n7️⃣ Verificando assets...\n";
$assets = [
    'assets/css/style.css',
    'assets/js/app.js',
    'assets/js/auth.js'
];

foreach ($assets as $asset) {
    if (file_exists($asset)) {
        echo "   ✅ $asset\n";
        $tests[] = ['asset' => $asset, 'status' => 'OK'];
        $passed++;
    } else {
        echo "   ❌ $asset\n";
        $tests[] = ['asset' => $asset, 'status' => 'MISSING'];
        $failed++;
    }
}

// Teste 8: Verificar permissões
echo "\n8️⃣ Verificando permissões...\n";
$writableDirs = ['logs', 'uploads', 'cache'];
foreach ($writableDirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "   ✅ Diretório '$dir' é gravável\n";
            $tests[] = ['permission' => $dir, 'status' => 'OK'];
            $passed++;
        } else {
            echo "   ⚠️ Diretório '$dir' não é gravável\n";
            $tests[] = ['permission' => $dir, 'status' => 'WARNING'];
        }
    } else {
        echo "   ⚠️ Diretório '$dir' não existe\n";
        $tests[] = ['permission' => $dir, 'status' => 'WARNING'];
    }
}

// Resumo dos testes
echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 RESUMO DOS TESTES\n";
echo str_repeat("=", 50) . "\n";
echo "✅ Testes aprovados: $passed\n";
echo "❌ Testes falharam: $failed\n";
echo "📈 Taxa de sucesso: " . round(($passed / ($passed + $failed)) * 100, 1) . "%\n\n";

if ($failed === 0) {
    echo "🎉 SISTEMA FUNCIONANDO PERFEITAMENTE! 🎉\n";
    echo "=====================================\n\n";
    echo "🚀 Você pode acessar:\n";
    echo "   • Aplicação: http://localhost/gamify-crud-api/\n";
    echo "   • API: http://localhost/gamify-crud-api/api/\n\n";
    echo "🎮 Boa sorte em sua jornada de gamificação! ⚔️🏆\n";
} else {
    echo "⚠️ ALGUNS PROBLEMAS ENCONTRADOS ⚠️\n";
    echo "================================\n\n";
    echo "🔧 Soluções:\n";
    echo "1. Execute a instalação: php install_quick.php\n";
    echo "2. Verifique se o MySQL está rodando\n";
    echo "3. Verifique as permissões dos arquivos\n";
    echo "4. Consulte o arquivo INSTALAR.md\n\n";
}

// Função para fazer requisição HTTP
function makeHttpRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
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

