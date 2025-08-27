<?php
/**
 * Endpoint Principal da API
 * Sistema Vida Equilibrada
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Configurações
require_once __DIR__ . '/../config/database.php';

// Verificar método OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Verificar modo de manutenção
if (MAINTENANCE_MODE) {
    http_response_code(503);
    echo json_encode([
        'status' => 'error',
        'message' => MAINTENANCE_MESSAGE,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

// Obter URL e método
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Remover base path se necessário
$base_path = '/api';
if (strpos($request_uri, $base_path) === 0) {
    $request_uri = substr($request_uri, strlen($base_path));
}

// Dividir URL em partes
$url_parts = explode('/', trim($request_uri, '/'));
$endpoint = $url_parts[0] ?? '';
$resource_id = $url_parts[1] ?? null;
$action = $url_parts[2] ?? null;

// Obter dados do corpo da requisição
$input_data = json_decode(file_get_contents('php://input'), true);
if (!$input_data) {
    $input_data = $_POST;
}

// Log da requisição
logMessage("API Request: {$request_method} {$request_uri}");

try {
    // Conectar ao banco
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        throw new Exception('Erro na conexão com o banco de dados');
    }

    // Roteamento da API
    switch ($endpoint) {
        case 'usuarios':
            require_once __DIR__ . '/usuarios.php';
            $controller = new UsuariosController($db);
            handleUsuariosRequest($controller, $request_method, $resource_id, $action, $input_data);
            break;

        case 'habitos':
            require_once __DIR__ . '/habitos.php';
            $controller = new HabitosController($db);
            handleHabitosRequest($controller, $request_method, $resource_id, $action, $input_data);
            break;

        case 'conquistas':
        case 'badges':
            require_once __DIR__ . '/conquistas.php';
            $controller = new ConquistasController($db);
            handleConquistasRequest($controller, $request_method, $resource_id, $action, $input_data);
            break;

        case 'ranking':
            require_once __DIR__ . '/ranking.php';
            $controller = new RankingController($db);
            handleRankingRequest($controller, $request_method, $resource_id, $action, $input_data);
            break;

        case 'auth':
        case 'login':
            require_once __DIR__ . '/auth.php';
            $controller = new AuthController($db);
            handleAuthRequest($controller, $request_method, $resource_id, $action, $input_data);
            break;

        case 'estatisticas':
        case 'stats':
            require_once __DIR__ . '/estatisticas.php';
            $controller = new EstatisticasController($db);
            handleEstatisticasRequest($controller, $request_method, $resource_id, $action, $input_data);
            break;

        case 'health':
        case 'status':
            // Endpoint de saúde da API
            echo json_encode([
                'status' => 'success',
                'message' => 'API Vida Equilibrada funcionando!',
                'version' => APP_VERSION,
                'timestamp' => date('Y-m-d H:i:s'),
                'database' => $database->testConnection()
            ]);
            break;

        case '':
        case 'home':
            // Documentação da API
            echo json_encode([
                'status' => 'success',
                'message' => 'Bem-vindo à API Vida Equilibrada!',
                'version' => APP_VERSION,
                'endpoints' => [
                    'usuarios' => [
                        'GET /api/usuarios' => 'Listar usuários',
                        'POST /api/usuarios' => 'Criar usuário',
                        'GET /api/usuarios/{id}' => 'Obter usuário',
                        'PUT /api/usuarios/{id}' => 'Atualizar usuário',
                        'DELETE /api/usuarios/{id}' => 'Deletar usuário'
                    ],
                    'habitos' => [
                        'GET /api/habitos' => 'Listar hábitos',
                        'POST /api/habitos' => 'Criar hábito',
                        'GET /api/habitos/{id}' => 'Obter hábito',
                        'PUT /api/habitos/{id}' => 'Atualizar hábito',
                        'DELETE /api/habitos/{id}' => 'Deletar hábito',
                        'POST /api/habitos/{id}/completar' => 'Completar hábito'
                    ],
                    'conquistas' => [
                        'GET /api/conquistas' => 'Listar conquistas',
                        'GET /api/conquistas/usuario/{id}' => 'Conquistas do usuário'
                    ],
                    'ranking' => [
                        'GET /api/ranking' => 'Ranking geral',
                        'GET /api/ranking?filtro=pontos' => 'Ranking por pontos',
                        'GET /api/ranking?filtro=badges' => 'Ranking por badges',
                        'GET /api/ranking?filtro=streak' => 'Ranking por streak'
                    ],
                    'auth' => [
                        'POST /api/auth/login' => 'Login',
                        'POST /api/auth/register' => 'Registro'
                    ],
                    'estatisticas' => [
                        'GET /api/estatisticas' => 'Estatísticas gerais',
                        'GET /api/estatisticas/usuario/{id}' => 'Estatísticas do usuário'
                    ]
                ],
                'documentation' => 'Consulte o README.md para mais detalhes'
            ]);
            break;

        default:
            // Endpoint não encontrado
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Endpoint não encontrado: ' . $endpoint,
                'available_endpoints' => [
                    'usuarios', 'habitos', 'conquistas', 'ranking', 'auth', 'estatisticas', 'health'
                ]
            ]);
            break;
    }

} catch (Exception $e) {
    logMessage("API Error: " . $e->getMessage(), 'ERROR');
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => DEBUG_MODE ? $e->getMessage() : 'Erro interno do servidor',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Funções auxiliares para roteamento
 */

function handleUsuariosRequest($controller, $method, $id, $action, $data) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $controller->obter($id);
            } else {
                $controller->listar($_GET);
            }
            break;
        case 'POST':
            $controller->criar($data);
            break;
        case 'PUT':
            if ($id) {
                $controller->atualizar($id, $data);
            } else {
                throw new Exception('ID do usuário é obrigatório para atualização');
            }
            break;
        case 'DELETE':
            if ($id) {
                $controller->deletar($id);
            } else {
                throw new Exception('ID do usuário é obrigatório para exclusão');
            }
            break;
        default:
            throw new Exception('Método não suportado: ' . $method);
    }
}

function handleHabitosRequest($controller, $method, $id, $action, $data) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $controller->obter($id);
            } else {
                $controller->listar($_GET);
            }
            break;
        case 'POST':
            if ($action === 'completar') {
                $controller->completar($id, $data);
            } else {
                $controller->criar($data);
            }
            break;
        case 'PUT':
            if ($id) {
                $controller->atualizar($id, $data);
            } else {
                throw new Exception('ID do hábito é obrigatório para atualização');
            }
            break;
        case 'DELETE':
            if ($id) {
                $controller->deletar($id);
            } else {
                throw new Exception('ID do hábito é obrigatório para exclusão');
            }
            break;
        default:
            throw new Exception('Método não suportado: ' . $method);
    }
}

function handleConquistasRequest($controller, $method, $id, $action, $data) {
    switch ($method) {
        case 'GET':
            if ($action === 'usuario' && $id) {
                $controller->obterPorUsuario($id);
            } else {
                $controller->listar($_GET);
            }
            break;
        case 'POST':
            if ($id) {
                $controller->conceder($id, $data);
            } else {
                throw new Exception('ID da conquista é obrigatório');
            }
            break;
        default:
            throw new Exception('Método não suportado: ' . $method);
    }
}

function handleRankingRequest($controller, $method, $id, $action, $data) {
    switch ($method) {
        case 'GET':
            $controller->obter($_GET);
            break;
        default:
            throw new Exception('Método não suportado: ' . $method);
    }
}

function handleAuthRequest($controller, $method, $id, $action, $data) {
    switch ($method) {
        case 'POST':
            if ($action === 'login') {
                $controller->login($data);
            } elseif ($action === 'register') {
                $controller->register($data);
            } else {
                throw new Exception('Ação não reconhecida: ' . $action);
            }
            break;
        default:
            throw new Exception('Método não suportado: ' . $method);
    }
}

function handleEstatisticasRequest($controller, $method, $id, $action, $data) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $controller->obterPorUsuario($id);
            } else {
                $controller->obter($_GET);
            }
            break;
        default:
            throw new Exception('Método não suportado: ' . $method);
    }
}

/**
 * Função para validar dados obrigatórios
 */
function validarDadosObrigatorios($data, $campos_obrigatorios) {
    $campos_faltando = [];
    
    foreach ($campos_obrigatorios as $campo) {
        if (!isset($data[$campo]) || empty($data[$campo])) {
            $campos_faltando[] = $campo;
        }
    }
    
    if (!empty($campos_faltando)) {
        throw new Exception('Campos obrigatórios faltando: ' . implode(', ', $campos_faltando));
    }
}

/**
 * Função para validar ID
 */
function validarId($id) {
    if (!$id || !is_numeric($id) || $id <= 0) {
        throw new Exception('ID inválido');
    }
    return (int) $id;
}

/**
 * Função para resposta de sucesso
 */
function respostaSucesso($data = null, $message = 'Operação realizada com sucesso') {
    echo json_encode([
        'status' => 'success',
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Função para resposta de erro
 */
function respostaErro($message = 'Erro na operação', $code = 400) {
    http_response_code($code);
    echo json_encode([
        'status' => 'error',
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Função para resposta de não encontrado
 */
function respostaNaoEncontrado($message = 'Recurso não encontrado') {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Função para resposta de não autorizado
 */
function respostaNaoAutorizado($message = 'Não autorizado') {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
