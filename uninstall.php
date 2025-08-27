<?php
/**
 * Desinstalação do Sistema - Vida Equilibrada
 * Remove o sistema completamente
 */

echo "🗑️ Desinstalação - Sistema Vida Equilibrada\n";
echo "==========================================\n\n";

// Verificar se foi instalado
if (!file_exists('installed.lock')) {
    echo "❌ Sistema não foi instalado ou já foi desinstalado!\n";
    exit;
}

echo "⚠️ ATENÇÃO: Esta ação irá remover completamente o sistema!\n";
echo "Isso inclui:\n";
echo "• Banco de dados 'vida_equilibrada'\n";
echo "• Arquivos de configuração\n";
echo "• Logs e cache\n";
echo "• Arquivo de lock\n\n";

echo "Tem certeza que deseja continuar? (digite 'SIM' para confirmar): ";
$confirmation = trim(fgets(STDIN));

if ($confirmation !== 'SIM') {
    echo "❌ Desinstalação cancelada!\n";
    exit;
}

echo "\n🗑️ Iniciando desinstalação...\n\n";

try {
    // Passo 1: Remover banco de dados
    echo "1️⃣ Removendo banco de dados...\n";
    if (file_exists('api/config/database.php')) {
        require_once 'api/config/database.php';
        
        if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER')) {
            try {
                $pdo = new PDO(
                    "mysql:host=" . DB_HOST . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                $pdo->exec("DROP DATABASE IF EXISTS `" . DB_NAME . "`");
                echo "   ✅ Banco de dados removido!\n";
            } catch (PDOException $e) {
                echo "   ⚠️ Erro ao remover banco: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Passo 2: Remover arquivos de configuração
    echo "\n2️⃣ Removendo arquivos de configuração...\n";
    $configFiles = [
        'api/config/database.php',
        'installed.lock',
        'install_info.txt'
    ];
    
    foreach ($configFiles as $file) {
        if (file_exists($file)) {
            if (unlink($file)) {
                echo "   ✅ $file removido!\n";
            } else {
                echo "   ⚠️ Erro ao remover $file\n";
            }
        }
    }
    
    // Passo 3: Remover logs
    echo "\n3️⃣ Removendo logs...\n";
    $logFiles = [
        'logs/app.log',
        'logs/php_errors.log'
    ];
    
    foreach ($logFiles as $file) {
        if (file_exists($file)) {
            if (unlink($file)) {
                echo "   ✅ $file removido!\n";
            } else {
                echo "   ⚠️ Erro ao remover $file\n";
            }
        }
    }
    
    // Passo 4: Remover diretórios vazios
    echo "\n4️⃣ Removendo diretórios vazios...\n";
    $dirs = ['logs', 'uploads', 'cache'];
    
    foreach ($dirs as $dir) {
        if (is_dir($dir)) {
            if (rmdir($dir)) {
                echo "   ✅ Diretório '$dir' removido!\n";
            } else {
                echo "   ⚠️ Diretório '$dir' não está vazio ou não pode ser removido\n";
            }
        }
    }
    
    // Passo 5: Limpar cache
    echo "\n5️⃣ Limpando cache...\n";
    if (is_dir('cache')) {
        $files = glob('cache/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "   ✅ Cache limpo!\n";
    }
    
    // Sucesso!
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ DESINSTALAÇÃO CONCLUÍDA!\n";
    echo str_repeat("=", 50) . "\n\n";
    echo "🗑️ Sistema removido com sucesso!\n";
    echo "📁 Arquivos do projeto mantidos para reinstalação\n";
    echo "🔄 Para reinstalar, execute: php install_quick.php\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRO NA DESINSTALAÇÃO!\n";
    echo "========================\n\n";
    echo "Erro: " . $e->getMessage() . "\n\n";
    echo "🔧 Soluções:\n";
    echo "1. Verifique as permissões dos arquivos\n";
    echo "2. Remova manualmente os arquivos restantes\n";
    echo "3. Execute novamente a desinstalação\n\n";
}
?>

