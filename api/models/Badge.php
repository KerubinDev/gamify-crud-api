<?php
/**
 * Modelo Badge
 * Sistema Vida Equilibrada
 */

require_once __DIR__ . '/../config/database.php';

class Badge {
    private $conn;
    private $table_name = "badges";
    private $table_conquistadas = "badges_conquistadas";

    public $id;
    public $nome;
    public $descricao;
    public $icone;
    public $cor;
    public $tipo;
    public $requisito_valor;
    public $pontos_bonus;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Ler todas as badges disponíveis
     */
    public function lerTodas() {
        $query = "SELECT id, nome, descricao, icone, cor, tipo, requisito_valor, pontos_bonus
                  FROM " . $this->table_name . "
                  ORDER BY tipo, requisito_valor";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Ler badge por ID
     */
    public function lerPorId($id) {
        $query = "SELECT id, nome, descricao, icone, cor, tipo, requisito_valor, pontos_bonus
                  FROM " . $this->table_name . "
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        $row = $stmt->fetch();
        if ($row) {
            $this->id = $row['id'];
            $this->nome = $row['nome'];
            $this->descricao = $row['descricao'];
            $this->icone = $row['icone'];
            $this->cor = $row['cor'];
            $this->tipo = $row['tipo'];
            $this->requisito_valor = $row['requisito_valor'];
            $this->pontos_bonus = $row['pontos_bonus'];
            return true;
        }
        return false;
    }

    /**
     * Obter badges de um usuário
     */
    public function getBadgesUsuario($usuario_id) {
        $query = "SELECT 
                    b.id,
                    b.nome,
                    b.descricao,
                    b.icone,
                    b.cor,
                    b.tipo,
                    b.requisito_valor,
                    b.pontos_bonus,
                    bc.data_conquista,
                    bc.pontos_ganhos
                  FROM " . $this->table_conquistadas . " bc
                  JOIN " . $this->table_name . " b ON bc.badge_id = b.id
                  WHERE bc.usuario_id = :usuario_id
                  ORDER BY bc.data_conquista DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter badges não conquistadas de um usuário
     */
    public function getBadgesNaoConquistadas($usuario_id) {
        $query = "SELECT 
                    b.id,
                    b.nome,
                    b.descricao,
                    b.icone,
                    b.cor,
                    b.tipo,
                    b.requisito_valor,
                    b.pontos_bonus
                  FROM " . $this->table_name . " b
                  WHERE b.id NOT IN (
                      SELECT badge_id 
                      FROM " . $this->table_conquistadas . " 
                      WHERE usuario_id = :usuario_id
                  )
                  ORDER BY b.tipo, b.requisito_valor";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Conceder badge a um usuário
     */
    public function concederBadge($usuario_id, $badge_id) {
        // Verificar se já possui a badge
        if ($this->usuarioPossuiBadge($usuario_id, $badge_id)) {
            return [
                'success' => false,
                'message' => 'Usuário já possui esta badge!'
            ];
        }

        // Obter dados da badge
        if (!$this->lerPorId($badge_id)) {
            return [
                'success' => false,
                'message' => 'Badge não encontrada!'
            ];
        }

        $query = "INSERT INTO " . $this->table_conquistadas . "
                (usuario_id, badge_id, pontos_ganhos)
                VALUES (:usuario_id, :badge_id, :pontos_ganhos)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":badge_id", $badge_id);
        $stmt->bindParam(":pontos_ganhos", $this->pontos_bonus);

        if ($stmt->execute()) {
            // Adicionar pontos bônus ao usuário
            $this->adicionarPontosBonus($usuario_id, $this->pontos_bonus);
            
            logMessage("Badge concedida: {$this->nome} para usuário {$usuario_id}");
            
            return [
                'success' => true,
                'message' => "Badge '{$this->nome}' conquistada!",
                'badge' => $this->toArray(),
                'pontos_bonus' => $this->pontos_bonus
            ];
        }

        logMessage("Erro ao conceder badge: " . implode(", ", $stmt->errorInfo()), 'ERROR');
        return [
            'success' => false,
            'message' => 'Erro ao conceder badge'
        ];
    }

    /**
     * Verificar se usuário possui uma badge
     */
    public function usuarioPossuiBadge($usuario_id, $badge_id) {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_conquistadas . "
                  WHERE usuario_id = :usuario_id AND badge_id = :badge_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":badge_id", $badge_id);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result['total'] > 0;
    }

    /**
     * Verificar e conceder badges automaticamente
     */
    public function verificarBadgesAutomaticas($usuario_id) {
        if (!BADGES_AUTO_ATRIBUICAO) {
            return [];
        }

        $badges_concedidas = [];
        $usuario = new Usuario($this->conn);
        $usuario->lerPorId($usuario_id);

        // Verificar badges de quantidade
        $badges_quantidade = $this->verificarBadgesQuantidade($usuario);
        $badges_concedidas = array_merge($badges_concedidas, $badges_quantidade);

        // Verificar badges de streak
        $badges_streak = $this->verificarBadgesStreak($usuario);
        $badges_concedidas = array_merge($badges_concedidas, $badges_streak);

        return $badges_concedidas;
    }

    /**
     * Verificar badges de quantidade
     */
    private function verificarBadgesQuantidade($usuario) {
        $badges_concedidas = [];
        $total_habitos = $usuario->total_habitos;

        $badges_quantidade = [
            1 => 'Iniciante',
            10 => 'Disciplinado',
            50 => 'Estrela',
            100 => 'Lendário'
        ];

        foreach ($badges_quantidade as $requisito => $nome_badge) {
            if ($total_habitos >= $requisito) {
                $badge_id = $this->getBadgeIdPorNome($nome_badge);
                if ($badge_id && !$this->usuarioPossuiBadge($usuario->id, $badge_id)) {
                    $resultado = $this->concederBadge($usuario->id, $badge_id);
                    if ($resultado['success']) {
                        $badges_concedidas[] = $resultado;
                    }
                }
            }
        }

        return $badges_concedidas;
    }

    /**
     * Verificar badges de streak
     */
    private function verificarBadgesStreak($usuario) {
        $badges_concedidas = [];
        $streak_atual = $usuario->streak_atual;

        $badges_streak = [
            3 => 'Em Chamas',
            7 => 'Velocista',
            30 => 'Mestre'
        ];

        foreach ($badges_streak as $requisito => $nome_badge) {
            if ($streak_atual >= $requisito) {
                $badge_id = $this->getBadgeIdPorNome($nome_badge);
                if ($badge_id && !$this->usuarioPossuiBadge($usuario->id, $badge_id)) {
                    $resultado = $this->concederBadge($usuario->id, $badge_id);
                    if ($resultado['success']) {
                        $badges_concedidas[] = $resultado;
                    }
                }
            }
        }

        return $badges_concedidas;
    }

    /**
     * Verificar badges de tempo (madrugador, noturno)
     */
    public function verificarBadgesTempo($usuario_id, $hora_completado) {
        $badges_concedidas = [];
        $hora = strtotime($hora_completado);

        // Badge Madrugador (antes das 8h)
        if ($hora < strtotime(HORA_MADRUGADOR)) {
            $badge_id = $this->getBadgeIdPorNome('Madrugador');
            if ($badge_id && !$this->usuarioPossuiBadge($usuario_id, $badge_id)) {
                $resultado = $this->concederBadge($usuario_id, $badge_id);
                if ($resultado['success']) {
                    $badges_concedidas[] = $resultado;
                }
            }
        }

        // Badge Noturno (após 22h)
        if ($hora > strtotime(HORA_NOTURNO)) {
            $badge_id = $this->getBadgeIdPorNome('Noturno');
            if ($badge_id && !$this->usuarioPossuiBadge($usuario_id, $badge_id)) {
                $resultado = $this->concederBadge($usuario_id, $badge_id);
                if ($resultado['success']) {
                    $badges_concedidas[] = $resultado;
                }
            }
        }

        return $badges_concedidas;
    }

    /**
     * Obter ID da badge por nome
     */
    private function getBadgeIdPorNome($nome) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE nome = :nome";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nome", $nome);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result ? $result['id'] : null;
    }

    /**
     * Adicionar pontos bônus ao usuário
     */
    private function adicionarPontosBonus($usuario_id, $pontos_bonus) {
        $query = "UPDATE usuarios 
                  SET pontos = pontos + :pontos_bonus
                  WHERE id = :usuario_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pontos_bonus", $pontos_bonus);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();
    }

    /**
     * Obter estatísticas de badges
     */
    public function getEstatisticasBadges($usuario_id = null) {
        $where_clause = "";
        if ($usuario_id) {
            $where_clause = "WHERE bc.usuario_id = :usuario_id";
        }

        $query = "SELECT 
                    b.tipo,
                    COUNT(bc.id) as total_conquistas,
                    COUNT(DISTINCT bc.usuario_id) as usuarios_unicos,
                    AVG(b.pontos_bonus) as media_pontos_bonus
                  FROM " . $this->table_name . " b
                  LEFT JOIN " . $this->table_conquistadas . " bc ON b.id = bc.badge_id
                  {$where_clause}
                  GROUP BY b.tipo
                  ORDER BY total_conquistas DESC";

        $stmt = $this->conn->prepare($query);
        if ($usuario_id) {
            $stmt->bindParam(":usuario_id", $usuario_id);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter ranking de badges
     */
    public function getRankingBadges($limite = 10) {
        $query = "SELECT 
                    u.nome,
                    u.pontos,
                    COUNT(bc.id) as total_badges,
                    SUM(b.pontos_bonus) as pontos_badges
                  FROM usuarios u
                  LEFT JOIN " . $this->table_conquistadas . " bc ON u.id = bc.usuario_id
                  LEFT JOIN " . $this->table_name . " b ON bc.badge_id = b.id
                  WHERE u.ativo = TRUE
                  GROUP BY u.id
                  ORDER BY total_badges DESC, pontos_badges DESC
                  LIMIT :limite";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter badges mais populares
     */
    public function getBadgesMaisPopulares($limite = 5) {
        $query = "SELECT 
                    b.nome,
                    b.descricao,
                    b.icone,
                    b.cor,
                    COUNT(bc.id) as total_conquistas
                  FROM " . $this->table_name . " b
                  LEFT JOIN " . $this->table_conquistadas . " bc ON b.id = bc.badge_id
                  GROUP BY b.id
                  ORDER BY total_conquistas DESC
                  LIMIT :limite";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter progresso de badges para um usuário
     */
    public function getProgressoBadges($usuario_id) {
        $query = "SELECT 
                    b.tipo,
                    COUNT(b.id) as total_badges_tipo,
                    COUNT(bc.id) as badges_conquistadas,
                    ROUND((COUNT(bc.id) / COUNT(b.id)) * 100, 2) as percentual_conquista
                  FROM " . $this->table_name . " b
                  LEFT JOIN " . $this->table_conquistadas . " bc ON b.id = bc.badge_id AND bc.usuario_id = :usuario_id
                  GROUP BY b.tipo
                  ORDER BY b.tipo";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter próximas badges a serem conquistadas
     */
    public function getProximasBadges($usuario_id) {
        $query = "SELECT 
                    b.id,
                    b.nome,
                    b.descricao,
                    b.icone,
                    b.cor,
                    b.tipo,
                    b.requisito_valor,
                    b.pontos_bonus
                  FROM " . $this->table_name . " b
                  WHERE b.id NOT IN (
                      SELECT badge_id 
                      FROM " . $this->table_conquistadas . " 
                      WHERE usuario_id = :usuario_id
                  )
                  ORDER BY b.requisito_valor ASC
                  LIMIT 5";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter total de badges conquistadas por usuário
     */
    public function getTotalBadgesUsuario($usuario_id) {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_conquistadas . "
                  WHERE usuario_id = :usuario_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Obter total de pontos ganhos com badges
     */
    public function getPontosBadgesUsuario($usuario_id) {
        $query = "SELECT SUM(b.pontos_bonus) as total_pontos
                  FROM " . $this->table_conquistadas . " bc
                  JOIN " . $this->table_name . " b ON bc.badge_id = b.id
                  WHERE bc.usuario_id = :usuario_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result['total_pontos'] ?: 0;
    }

    /**
     * Obter dados para API
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'icone' => $this->icone,
            'cor' => $this->cor,
            'tipo' => $this->tipo,
            'requisito_valor' => $this->requisito_valor,
            'pontos_bonus' => $this->pontos_bonus
        ];
    }

    /**
     * Obter tipos de badges disponíveis
     */
    public static function getTiposBadges() {
        return [
            'streak' => 'Sequência',
            'quantidade' => 'Quantidade',
            'especial' => 'Especial',
            'tempo' => 'Tempo'
        ];
    }

    /**
     * Validar tipo de badge
     */
    public static function validarTipo($tipo) {
        $tipos = self::getTiposBadges();
        return array_key_exists($tipo, $tipos);
    }

    /**
     * Obter cores padrão para badges
     */
    public static function getCoresPadrao() {
        return [
            '#007bff' => 'Azul',
            '#28a745' => 'Verde',
            '#ffc107' => 'Amarelo',
            '#dc3545' => 'Vermelho',
            '#6c757d' => 'Cinza',
            '#fd7e14' => 'Laranja',
            '#6f42c1' => 'Roxo',
            '#e83e8c' => 'Rosa',
            '#20c997' => 'Verde água',
            '#17a2b8' => 'Azul claro'
        ];
    }
}
?>
