<?php
/**
 * Modelo Ranking
 * Sistema Vida Equilibrada
 */

require_once __DIR__ . '/../config/database.php';

class Ranking {
    private $conn;
    private $table_name = "ranking_historico";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obter ranking geral
     */
    public function getRankingGeral($limite = 10, $pagina = 1, $filtro = 'pontos') {
        $offset = ($pagina - 1) * $limite;
        
        $order_by = $this->getOrderBy($filtro);
        
        $query = "SELECT 
                    u.id,
                    u.nome,
                    u.pontos,
                    u.streak_atual,
                    u.streak_maximo,
                    u.total_habitos,
                    COUNT(bc.id) as total_badges,
                    ROW_NUMBER() OVER (ORDER BY {$order_by}) as posicao
                  FROM usuarios u
                  LEFT JOIN badges_conquistadas bc ON u.id = bc.usuario_id
                  WHERE u.ativo = TRUE
                  GROUP BY u.id
                  ORDER BY {$order_by}
                  LIMIT :limite OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter ranking por período
     */
    public function getRankingPorPeriodo($periodo = 'semana', $limite = 10) {
        $data_inicio = $this->getDataInicioPeriodo($periodo);
        $data_fim = date('Y-m-d');

        $query = "SELECT 
                    u.id,
                    u.nome,
                    u.pontos,
                    u.streak_atual,
                    COUNT(bc.id) as total_badges,
                    SUM(hc.total_pontos) as pontos_periodo,
                    ROW_NUMBER() OVER (ORDER BY SUM(hc.total_pontos) DESC) as posicao
                  FROM usuarios u
                  LEFT JOIN habitos_completados hc ON u.id = hc.usuario_id 
                    AND hc.data_completado BETWEEN :data_inicio AND :data_fim
                  LEFT JOIN badges_conquistadas bc ON u.id = bc.usuario_id
                  WHERE u.ativo = TRUE
                  GROUP BY u.id
                  ORDER BY pontos_periodo DESC
                  LIMIT :limite";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":data_inicio", $data_inicio);
        $stmt->bindParam(":data_fim", $data_fim);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter ranking por categoria de hábito
     */
    public function getRankingPorCategoria($categoria, $limite = 10) {
        $query = "SELECT 
                    u.id,
                    u.nome,
                    u.pontos,
                    COUNT(hc.id) as total_completamentos,
                    SUM(hc.total_pontos) as pontos_categoria,
                    ROW_NUMBER() OVER (ORDER BY SUM(hc.total_pontos) DESC) as posicao
                  FROM usuarios u
                  JOIN habitos h ON u.id = h.usuario_id
                  JOIN habitos_completados hc ON h.id = hc.habito_id
                  WHERE u.ativo = TRUE AND h.categoria = :categoria
                  GROUP BY u.id
                  ORDER BY pontos_categoria DESC
                  LIMIT :limite";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":categoria", $categoria);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter ranking de streak
     */
    public function getRankingStreak($limite = 10) {
        $query = "SELECT 
                    u.id,
                    u.nome,
                    u.pontos,
                    u.streak_atual,
                    u.streak_maximo,
                    ROW_NUMBER() OVER (ORDER BY u.streak_atual DESC, u.streak_maximo DESC) as posicao
                  FROM usuarios u
                  WHERE u.ativo = TRUE AND u.streak_atual > 0
                  ORDER BY u.streak_atual DESC, u.streak_maximo DESC
                  LIMIT :limite";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter ranking de badges
     */
    public function getRankingBadges($limite = 10) {
        $query = "SELECT 
                    u.id,
                    u.nome,
                    u.pontos,
                    COUNT(bc.id) as total_badges,
                    SUM(b.pontos_bonus) as pontos_badges,
                    ROW_NUMBER() OVER (ORDER BY COUNT(bc.id) DESC, SUM(b.pontos_bonus) DESC) as posicao
                  FROM usuarios u
                  LEFT JOIN badges_conquistadas bc ON u.id = bc.usuario_id
                  LEFT JOIN badges b ON bc.badge_id = b.id
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
     * Obter posição de um usuário no ranking
     */
    public function getPosicaoUsuario($usuario_id, $filtro = 'pontos') {
        $order_by = $this->getOrderBy($filtro);
        
        $query = "SELECT posicao FROM (
                    SELECT 
                        u.id,
                        ROW_NUMBER() OVER (ORDER BY {$order_by}) as posicao
                    FROM usuarios u
                    LEFT JOIN badges_conquistadas bc ON u.id = bc.usuario_id
                    WHERE u.ativo = TRUE
                    GROUP BY u.id
                    ORDER BY {$order_by}
                  ) as ranking
                  WHERE id = :usuario_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result ? $result['posicao'] : null;
    }

    /**
     * Buscar usuários no ranking
     */
    public function buscarUsuarios($termo, $limite = 10) {
        $termo = sanitizeInput($termo);
        
        $query = "SELECT 
                    u.id,
                    u.nome,
                    u.pontos,
                    u.streak_atual,
                    u.total_habitos,
                    COUNT(bc.id) as total_badges,
                    ROW_NUMBER() OVER (ORDER BY u.pontos DESC) as posicao
                  FROM usuarios u
                  LEFT JOIN badges_conquistadas bc ON u.id = bc.usuario_id
                  WHERE u.ativo = TRUE 
                    AND (u.nome LIKE :termo OR u.email LIKE :termo)
                  GROUP BY u.id
                  ORDER BY u.pontos DESC
                  LIMIT :limite";

        $stmt = $this->conn->prepare($query);
        $termo_param = "%{$termo}%";
        $stmt->bindParam(":termo", $termo_param);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter estatísticas do ranking
     */
    public function getEstatisticasRanking() {
        $query = "SELECT 
                    COUNT(*) as total_usuarios,
                    AVG(pontos) as media_pontos,
                    MAX(pontos) as max_pontos,
                    MIN(pontos) as min_pontos,
                    AVG(streak_atual) as media_streak,
                    MAX(streak_atual) as max_streak,
                    AVG(total_habitos) as media_habitos,
                    MAX(total_habitos) as max_habitos
                  FROM usuarios
                  WHERE ativo = TRUE";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Salvar ranking histórico
     */
    public function salvarRankingHistorico($periodo = 'semana') {
        $ranking = $this->getRankingGeral(100, 1, 'pontos');
        
        foreach ($ranking as $posicao => $usuario) {
            $query = "INSERT INTO " . $this->table_name . "
                    (usuario_id, posicao, pontos, total_badges, streak_atual, periodo)
                    VALUES (:usuario_id, :posicao, :pontos, :total_badges, :streak_atual, :periodo)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":usuario_id", $usuario['id']);
            $stmt->bindParam(":posicao", $posicao + 1);
            $stmt->bindParam(":pontos", $usuario['pontos']);
            $stmt->bindParam(":total_badges", $usuario['total_badges']);
            $stmt->bindParam(":streak_atual", $usuario['streak_atual']);
            $stmt->bindParam(":periodo", $periodo);
            $stmt->execute();
        }

        logMessage("Ranking histórico salvo para período: {$periodo}");
    }

    /**
     * Obter evolução do ranking de um usuário
     */
    public function getEvolucaoRanking($usuario_id, $periodo = 'semana', $limite = 30) {
        $query = "SELECT 
                    posicao,
                    pontos,
                    total_badges,
                    streak_atual,
                    data_registro
                  FROM " . $this->table_name . "
                  WHERE usuario_id = :usuario_id 
                    AND periodo = :periodo
                  ORDER BY data_registro DESC
                  LIMIT :limite";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":periodo", $periodo);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter top performers
     */
    public function getTopPerformers($limite = 5) {
        $query = "SELECT 
                    u.nome,
                    u.pontos,
                    u.streak_atual,
                    COUNT(bc.id) as total_badges,
                    COUNT(hc.id) as total_completamentos
                  FROM usuarios u
                  LEFT JOIN badges_conquistadas bc ON u.id = bc.usuario_id
                  LEFT JOIN habitos_completados hc ON u.id = hc.usuario_id
                  WHERE u.ativo = TRUE
                  GROUP BY u.id
                  ORDER BY u.pontos DESC, u.streak_atual DESC
                  LIMIT :limite";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter usuários em ascensão
     */
    public function getUsuariosAscensao($limite = 5) {
        $data_inicio = date('Y-m-d', strtotime('-7 days'));
        $data_fim = date('Y-m-d');

        $query = "SELECT 
                    u.nome,
                    u.pontos,
                    COUNT(hc.id) as completamentos_semana,
                    SUM(hc.total_pontos) as pontos_semana
                  FROM usuarios u
                  LEFT JOIN habitos_completados hc ON u.id = hc.usuario_id 
                    AND hc.data_completado BETWEEN :data_inicio AND :data_fim
                  WHERE u.ativo = TRUE
                  GROUP BY u.id
                  HAVING completamentos_semana > 0
                  ORDER BY pontos_semana DESC
                  LIMIT :limite";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":data_inicio", $data_inicio);
        $stmt->bindParam(":data_fim", $data_fim);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter competições ativas
     */
    public function getCompeticoesAtivas() {
        $query = "SELECT 
                    'Maior Streak' as titulo,
                    'Quem consegue manter o maior streak?' as descricao,
                    'streak' as tipo,
                    MAX(streak_atual) as valor_maximo,
                    COUNT(CASE WHEN streak_atual > 0 THEN 1 END) as participantes
                  FROM usuarios
                  WHERE ativo = TRUE
                  
                  UNION ALL
                  
                  SELECT 
                    'Mais Badges' as titulo,
                    'Quem conquista mais badges?' as descricao,
                    'badges' as tipo,
                    (SELECT COUNT(bc.id) 
                     FROM usuarios u2 
                     LEFT JOIN badges_conquistadas bc ON u2.id = bc.usuario_id 
                     WHERE u2.ativo = TRUE 
                     GROUP BY u2.id 
                     ORDER BY COUNT(bc.id) DESC 
                     LIMIT 1) as valor_maximo,
                    COUNT(DISTINCT bc.usuario_id) as participantes
                  FROM usuarios u
                  LEFT JOIN badges_conquistadas bc ON u.id = bc.usuario_id
                  WHERE u.ativo = TRUE";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter total de usuários no ranking
     */
    public function getTotalUsuarios($busca = null) {
        $where_clause = "WHERE ativo = TRUE";
        if ($busca) {
            $busca = sanitizeInput($busca);
            $where_clause .= " AND (nome LIKE :busca OR email LIKE :busca)";
        }

        $query = "SELECT COUNT(*) as total FROM usuarios {$where_clause}";

        $stmt = $this->conn->prepare($query);
        if ($busca) {
            $busca_param = "%{$busca}%";
            $stmt->bindParam(":busca", $busca_param);
        }

        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Obter dados para API
     */
    public function formatarRankingParaAPI($ranking) {
        $formatted = [];
        foreach ($ranking as $item) {
            $formatted[] = [
                'posicao' => $item['posicao'],
                'usuario' => [
                    'id' => $item['id'],
                    'nome' => $item['nome'],
                    'pontos' => $item['pontos'],
                    'streak_atual' => $item['streak_atual'],
                    'streak_maximo' => $item['streak_maximo'] ?? 0,
                    'total_habitos' => $item['total_habitos'],
                    'total_badges' => $item['total_badges'] ?? 0
                ]
            ];
        }
        return $formatted;
    }

    /**
     * Obter ORDER BY baseado no filtro
     */
    private function getOrderBy($filtro) {
        switch ($filtro) {
            case 'badges':
                return 'COUNT(bc.id) DESC, u.pontos DESC';
            case 'streak':
                return 'u.streak_atual DESC, u.streak_maximo DESC';
            case 'habitos':
                return 'u.total_habitos DESC, u.pontos DESC';
            case 'pontos':
            default:
                return 'u.pontos DESC, u.streak_atual DESC';
        }
    }

    /**
     * Obter data de início do período
     */
    private function getDataInicioPeriodo($periodo) {
        switch ($periodo) {
            case 'semana':
                return date('Y-m-d', strtotime('-7 days'));
            case 'mes':
                return date('Y-m-01');
            case 'ano':
                return date('Y-01-01');
            default:
                return date('Y-m-d', strtotime('-7 days'));
        }
    }

    /**
     * Obter filtros disponíveis
     */
    public static function getFiltrosDisponiveis() {
        return [
            'pontos' => 'Por Pontos',
            'badges' => 'Por Badges',
            'streak' => 'Por Streak',
            'habitos' => 'Por Hábitos'
        ];
    }

    /**
     * Obter períodos disponíveis
     */
    public static function getPeriodosDisponiveis() {
        return [
            'semana' => 'Última Semana',
            'mes' => 'Este Mês',
            'ano' => 'Este Ano',
            'geral' => 'Geral'
        ];
    }

    /**
     * Validar filtro
     */
    public static function validarFiltro($filtro) {
        $filtros = self::getFiltrosDisponiveis();
        return array_key_exists($filtro, $filtros);
    }

    /**
     * Validar período
     */
    public static function validarPeriodo($periodo) {
        $periodos = self::getPeriodosDisponiveis();
        return array_key_exists($periodo, $periodos);
    }
}
?>
