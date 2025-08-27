<?php
/**
 * Script de Instalação Automática
 * Sistema Vida Equilibrada
 */

// Configurações de instalação
$config = [
    'db_host' => 'localhost',
    'db_name' => 'vida_equilibrada',
    'db_user' => 'root',
    'db_pass' => '',
    'app_url' => 'http://localhost/gamify-crud-api'
];

// Verificar se já foi instalado
if (file_exists('installed.lock')) {
    die('❌ Sistema já foi instalado! Para reinstalar, delete o arquivo installed.lock');
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 Instalação - Vida Equilibrada</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #667eea;
            margin: 0;
            font-size: 2.5rem;
        }
        .header p {
            color: #666;
            margin: 10px 0 0 0;
        }
        .step {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            border-left: 5px solid #667eea;
        }
        .step h3 {
            margin: 0 0 15px 0;
            color: #333;
        }
        .step p {
            margin: 5px 0;
            color: #666;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .warning {
            color: #ffc107;
            font-weight: bold;
        }
        .info {
            color: #17a2b8;
            font-weight: bold;
        }
        .btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            margin: 10px 5px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .form-group {
            margin: 15px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .progress {
            width: 100%;
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }
        .log {
            background: #1e1e1e;
            color: #00ff00;
            padding: 20px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            max-height: 300px;
            overflow-y: auto;
            margin: 20px 0;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏆 Vida Equilibrada</h1>
            <p>Instalação Automática do Sistema Gamificado</p>
        </div>

        <div id="configForm">
            <div class="step">
                <h3>📋 Configuração do Banco de Dados</h3>
                <p>Preencha as informações de conexão com o MySQL:</p>
                
                <form id="installForm">
                    <div class="form-group">
                        <label for="db_host">Host do Banco:</label>
                        <input type="text" id="db_host" name="db_host" value="<?= $config['db_host'] ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_name">Nome do Banco:</label>
                        <input type="text" id="db_name" name="db_name" value="<?= $config['db_name'] ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_user">Usuário:</label>
                        <input type="text" id="db_user" name="db_user" value="<?= $config['db_user'] ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_pass">Senha:</label>
                        <input type="password" id="db_pass" name="db_pass" value="<?= $config['db_pass'] ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="app_url">URL da Aplicação:</label>
                        <input type="text" id="app_url" name="app_url" value="<?= $config['app_url'] ?>" required>
                    </div>
                    
                    <button type="submit" class="btn">🚀 Iniciar Instalação</button>
                </form>
            </div>
        </div>

        <div id="installation" class="hidden">
            <div class="step">
                <h3>⚙️ Instalação em Progresso</h3>
                <div class="progress">
                    <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                </div>
                <div class="log" id="installLog"></div>
            </div>
        </div>

        <div id="success" class="hidden">
            <div class="step">
                <h3>✅ Instalação Concluída!</h3>
                <p class="success">O sistema foi instalado com sucesso!</p>
                <p>Você pode agora acessar a aplicação:</p>
                <a href="index.html" class="btn">🎮 Acessar Sistema</a>
                <button onclick="showConfig()" class="btn">⚙️ Ver Configurações</button>
            </div>
        </div>

        <div id="error" class="hidden">
            <div class="step">
                <h3>❌ Erro na Instalação</h3>
                <p class="error" id="errorMessage"></p>
                <button onclick="retryInstallation()" class="btn">🔄 Tentar Novamente</button>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 0;
        const totalSteps = 6;

        document.getElementById('installForm').addEventListener('submit', function(e) {
            e.preventDefault();
            startInstallation();
        });

        function startInstallation() {
            const formData = new FormData(document.getElementById('installForm'));
            const config = Object.fromEntries(formData.entries());

            document.getElementById('configForm').classList.add('hidden');
            document.getElementById('installation').classList.remove('hidden');

            installStep(config, 0);
        }

        function installStep(config, step) {
            const steps = [
                { name: 'Verificando conexão com MySQL...', action: 'test_connection' },
                { name: 'Criando banco de dados...', action: 'create_database' },
                { name: 'Importando esquema...', action: 'import_schema' },
                { name: 'Configurando aplicação...', action: 'configure_app' },
                { name: 'Testando funcionalidades...', action: 'test_features' },
                { name: 'Finalizando instalação...', action: 'finalize' }
            ];

            if (step >= steps.length) {
                completeInstallation();
                return;
            }

            const currentStepInfo = steps[step];
            log(currentStepInfo.name);

            // Simular progresso
            const progress = ((step + 1) / totalSteps) * 100;
            document.getElementById('progressBar').style.width = progress + '%';

            // Fazer requisição para o backend
            fetch('install_backend.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    step: step,
                    action: currentStepInfo.action,
                    config: config
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    log('✅ ' + currentStepInfo.name + ' - Concluído!', 'success');
                    setTimeout(() => installStep(config, step + 1), 1000);
                } else {
                    log('❌ ' + currentStepInfo.name + ' - Erro: ' + data.message, 'error');
                    showError(data.message);
                }
            })
            .catch(error => {
                log('❌ Erro na requisição: ' + error.message, 'error');
                showError('Erro de conexão: ' + error.message);
            });
        }

        function log(message, type = 'info') {
            const logElement = document.getElementById('installLog');
            const timestamp = new Date().toLocaleTimeString();
            const logEntry = document.createElement('div');
            logEntry.innerHTML = `[${timestamp}] ${message}`;
            
            if (type === 'success') {
                logEntry.style.color = '#28a745';
            } else if (type === 'error') {
                logEntry.style.color = '#dc3545';
            } else if (type === 'warning') {
                logEntry.style.color = '#ffc107';
            }
            
            logElement.appendChild(logEntry);
            logElement.scrollTop = logElement.scrollHeight;
        }

        function completeInstallation() {
            document.getElementById('installation').classList.add('hidden');
            document.getElementById('success').classList.remove('hidden');
        }

        function showError(message) {
            document.getElementById('installation').classList.add('hidden');
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('error').classList.remove('hidden');
        }

        function retryInstallation() {
            document.getElementById('error').classList.add('hidden');
            document.getElementById('configForm').classList.remove('hidden');
        }

        function showConfig() {
            alert('Configurações salvas em: api/config/database.php');
        }
    </script>
</body>
</html>

