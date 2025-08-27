<?php
/**
 * FIT BATTLE - Instalação Rápida
 * Sistema de Competição Fitness
 */

// Verificar se já foi instalado
if (file_exists('installed.txt')) {
    die('FIT BATTLE já foi instalado! Para reinstalar, delete o arquivo installed.txt');
}

// Configurações padrão
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'fit_battle';

// Processar formulário de instalação
if ($_POST) {
    $db_host = $_POST['db_host'] ?? $db_host;
    $db_user = $_POST['db_user'] ?? $db_user;
    $db_pass = $_POST['db_pass'] ?? $db_pass;
    $db_name = $_POST['db_name'] ?? $db_name;
    
    try {
        // Testar conexão
        $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Criar banco se não existir
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$db_name`");
        
        // Ler e executar script SQL
        $sql = file_get_contents('database/fit_battle.sql');
        $pdo->exec($sql);
        
        // Atualizar arquivo de configuração
        $config_content = file_get_contents('api/config/database.php');
        $config_content = str_replace("define('DB_HOST', 'localhost');", "define('DB_HOST', '$db_host');", $config_content);
        $config_content = str_replace("define('DB_NAME', 'fit_battle');", "define('DB_NAME', '$db_name');", $config_content);
        $config_content = str_replace("define('DB_USER', 'root');", "define('DB_USER', '$db_user');", $config_content);
        $config_content = str_replace("define('DB_PASS', '');", "define('DB_PASS', '$db_pass');", $config_content);
        
        file_put_contents('api/config/database.php', $config_content);
        
        // Marcar como instalado
        file_put_contents('installed.txt', date('Y-m-d H:i:s'));
        
        $success = "FIT BATTLE instalado com sucesso! 🎉";
        
    } catch (Exception $e) {
        $error = "Erro na instalação: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FIT BATTLE - Instalação</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            font-size: 2.5em;
            color: #333;
            margin-bottom: 10px;
        }
        
        .logo .subtitle {
            color: #666;
            font-size: 1.1em;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: transform 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .features {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e1e5e9;
        }
        
        .features h3 {
            color: #333;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .feature-list {
            list-style: none;
        }
        
        .feature-list li {
            padding: 8px 0;
            color: #666;
            display: flex;
            align-items: center;
        }
        
        .feature-list li::before {
            content: "✅";
            margin-right: 10px;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>🏃‍♂️💪 FIT BATTLE</h1>
            <div class="subtitle">A Guerra pela Saúde</div>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
            <div class="features">
                <h3>🎉 Instalação Concluída!</h3>
                <p style="text-align: center; margin-bottom: 20px;">
                    Seu FIT BATTLE está pronto para uso!
                </p>
                <a href="index.html" class="btn">🚀 Acessar Aplicação</a>
            </div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!isset($success)): ?>
            <form method="POST">
                <div class="form-group">
                    <label for="db_host">Host do Banco:</label>
                    <input type="text" id="db_host" name="db_host" value="<?php echo htmlspecialchars($db_host); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="db_user">Usuário do Banco:</label>
                    <input type="text" id="db_user" name="db_user" value="<?php echo htmlspecialchars($db_user); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="db_pass">Senha do Banco:</label>
                    <input type="password" id="db_pass" name="db_pass" value="<?php echo htmlspecialchars($db_pass); ?>">
                </div>
                
                <div class="form-group">
                    <label for="db_name">Nome do Banco:</label>
                    <input type="text" id="db_name" name="db_name" value="<?php echo htmlspecialchars($db_name); ?>" required>
                </div>
                
                <button type="submit" class="btn">🚀 Instalar FIT BATTLE</button>
            </form>
            
            <div class="features">
                <h3>✨ O que será instalado:</h3>
                <ul class="feature-list">
                    <li>Banco de dados completo com todas as tabelas</li>
                    <li>Sistema de usuários e autenticação</li>
                    <li>Sistema de exercícios e categorias</li>
                    <li>Sistema de pontuação e ranking</li>
                    <li>Sistema de badges e conquistas</li>
                    <li>Sistema de desafios e competições</li>
                    <li>Stored procedures e triggers</li>
                    <li>Views para consultas otimizadas</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
