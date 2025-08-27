<?php
/**
 * Modelo Habito
 * Sistema Vida Equilibrada
 */

require_once __DIR__ . '/../config/database.php';

class Habito {
    private $conn;
    private $table_name = "habitos";

    public $id;
    public $usuario_id;
    public $titulo;
    public $descricao;
    public $categoria;
    public $frequencia;
    public $pontos_base;
    public $ativo;
    public $data_criacao;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Criar novo hábito
     */
    public function criar() {
        $query = "INSERT INTO " . $this->table_name . "
                (usuario_id, titulo, descricao, categoria, frequencia, pontos_base, ativo)
                VALUES (:usuario_id, :titulo, :descricao, :categoria, :frequencia, :pontos_base, :ativo)";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->titulo = sanitizeInput($this->titulo);
        $this->descricao = sanitizeInput($this->descricao);

        // Bind dos valores
        $stmt->bindParam(":usuario_id", $this->usuario_id);
        $stmt->bindParam(":titulo", $this->titulo);
        $stmt->bindParam(":descricao", $this->descricao);
        $stmt->bindParam(":categoria", $this->categoria);
        $stmt->bindParam(":frequencia", $this->frequencia);
        $stmt->bindParam(":pontos_base", $this->pontos_base);
        $stmt->bindParam(":ativo", $this->ativo);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            logMessage("Hábito criado: {$this->titulo} para usuário {$this->usuario_id}");
            return true;
        }

        logMessage("Erro ao criar hábito: " . implode(", ", $stmt->errorInfo()), 'ERROR');
        return false;
    }

    /**
     * Ler todos os hábitos de um usuário
     */
    public function lerPorUsuario($usuario_id, $pagina = 1, $limite = ITENS_POR_PAGINA, $categoria = null) {
        $offset = ($pagina - 1) * $limite;
        
        $where_clause = "WHERE usuario_id = :usuario_id AND ativo = TRUE";
        if ($categoria) {
            $where_clause .= " AND categoria = :categoria";
        }

        $query = "SELECT id, usuario_id, titulo, descricao, categoria, frequencia, 
                         pontos_base, ativo, data_criacao
                  FROM " . $this->table_name . "
                  {$where_clause}
                  ORDER BY data_criacao DESC
                  LIMIT :limite OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        
        if ($categoria) {
            $stmt->bindParam(":categoria", $categoria);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Ler hábito por ID
     */
    public function lerPorId($id) {
        $query = "SELECT id, usuario_id, titulo, descricao, categoria, frequencia, 
                         pontos_base, ativo, data_criacao
                  FROM " . $this->table_name . "
                  WHERE id = :id AND ativo = TRUE";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        $row = $stmt->fetch();
        if ($row) {
            $this->id = $row['id'];
            $this->usuario_id = $row['usuario_id'];
            $this->titulo = $row['titulo'];
            $this->descricao = $row['descricao'];
            $this->categoria = $row['categoria'];
            $this->frequencia = $row['frequencia'];
            $this->pontos_base = $row['pontos_base'];
            $this->ativo = $row['ativo'];
            $this->data_criacao = $row['data_criacao'];
            return true;
        }
        return false;
    }

    /**
     * Atualizar hábito
     */
    public function atualizar() {
        $query = "UPDATE " . $this->table_name . "
                SET titulo = :titulo, descricao = :descricao, categoria = :categoria, 
                    frequencia = :frequencia, pontos_base = :pontos_base, ativo = :ativo
                WHERE id = :id AND usuario_id = :usuario_id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->titulo = sanitizeInput($this->titulo);
        $this->descricao = sanitizeInput($this->descricao);

        // Bind dos valores
        $stmt->bindParam(":titulo", $this->titulo);
        $stmt->bindParam(":descricao", $this->descricao);
        $stmt->bindParam(":categoria", $this->categoria);
        $stmt->bindParam(":frequencia", $this->frequencia);
        $stmt->bindParam(":pontos_base", $this->pontos_base);
        $stmt->bindParam(":ativo", $this->ativo);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":usuario_id", $this->usuario_id);

        if ($stmt->execute()) {
            logMessage("Hábito atualizado: ID {$this->id}");
            return true;
        }

        logMessage("Erro ao atualizar hábito: " . implode(", ", $stmt->errorInfo()), 'ERROR');
        return false;
    }

    /**
     * Deletar hábito (soft delete)
     */
    public function deletar() {
        $query = "UPDATE " . $this->table_name . "
                SET ativo = FALSE
                WHERE id = :id AND usuario_id = :usuario_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":usuario_id", $this->usuario_id);

        if ($stmt->execute()) {
            logMessage("Hábito deletado: ID {$this->id}");
            return true;
        }

        logMessage("Erro ao deletar hábito: " . implode(", ", $stmt->errorInfo()), 'ERROR');
        return false;
    }

    /**
     * Completar hábito
     */
    public function completar($usuario_id) {
        // Verificar se já foi completado hoje
        if ($this->jaCompletadoHoje($usuario_id)) {
            return [
                'success' => false,
                'message' => 'Hábito já foi completado hoje!'
            ];
        }

        // Calcular pontos
        $pontos = $this->calcularPontosCompletamento($usuario_id);

        // Inserir completamento
        $query = "INSERT INTO habitos_completados 
                (habito_id, usuario_id, data_completado, hora_completado, 
                 pontos_ganhos, multiplicador, bonus_streak, bonus_primeiro_dia, total_pontos)
                VALUES (:habito_id, :usuario_id, CURDATE(), CURTIME(), 
                        :pontos_ganhos, :multiplicador, :bonus_streak, :bonus_primeiro_dia, :total_pontos)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":habito_id", $this->id);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":pontos_ganhos", $pontos['pontos_base']);
        $stmt->bindParam(":multiplicador", $pontos['multiplicador']);
        $stmt->bindParam(":bonus_streak", $pontos['bonus_streak']);
        $stmt->bindParam(":bonus_primeiro_dia", $pontos['bonus_primeiro_dia']);
        $stmt->bindParam(":total_pontos", $pontos['total']);

        if ($stmt->execute()) {
            // Atualizar usuário
            $this->atualizarUsuarioAposCompletamento($usuario_id, $pontos['total']);
            
            logMessage("Hábito completado: ID {$this->id} por usuário {$usuario_id} - {$pontos['total']} pontos");
            
            return [
                'success' => true,
                'message' => 'Hábito completado com sucesso!',
                'pontos_ganhos' => $pontos['total'],
                'detalhes' => $pontos
            ];
        }

        logMessage("Erro ao completar hábito: " . implode(", ", $stmt->errorInfo()), 'ERROR');
        return [
            'success' => false,
            'message' => 'Erro ao completar hábito'
        ];
    }

    /**
     * Verificar se hábito já foi completado hoje
     */
    private function jaCompletadoHoje($usuario_id) {
        $query = "SELECT COUNT(*) as total 
                  FROM habitos_completados 
                  WHERE habito_id = :habito_id 
                  AND usuario_id = :usuario_id 
                  AND data_completado = CURDATE()";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":habito_id", $this->id);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result['total'] > 0;
    }

    /**
     * Calcular pontos do completamento
     */
    private function calcularPontosCompletamento($usuario_id) {
        $hora_atual = date('H:i:s');
        $multiplicador = getMultiplicadorHorario($hora_atual);
        $bonus_streak = 0;
        $bonus_primeiro_dia = 0;

        // Verificar se é primeiro hábito do dia
        $query = "SELECT COUNT(*) as total 
                  FROM habitos_completados 
                  WHERE usuario_id = :usuario_id 
                  AND data_completado = CURDATE()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result['total'] == 0) {
            $bonus_primeiro_dia = BONUS_PRIMEIRO_DIA;
        }

        // Calcular streak atual
        $streak_atual = $this->getStreakAtual($usuario_id);
        
        // Calcular bônus de streak
        if ($streak_atual >= 2) {
            if ($streak_atual == 2) { // 3 dias consecutivos
                $bonus_streak = BONUS_STREAK_3;
            } elseif ($streak_atual == 6) { // 7 dias consecutivos
                $bonus_streak = BONUS_STREAK_7;
            } elseif ($streak_atual == 29) { // 30 dias consecutivos
                $bonus_streak = BONUS_STREAK_30;
            }
        }

        $total = ($this->pontos_base * $multiplicador) + $bonus_streak + $bonus_primeiro_dia;

        return [
            'pontos_base' => $this->pontos_base,
            'multiplicador' => $multiplicador,
            'bonus_streak' => $bonus_streak,
            'bonus_primeiro_dia' => $bonus_primeiro_dia,
            'total' => $total
        ];
    }

    /**
     * Obter streak atual do usuário
     */
    private function getStreakAtual($usuario_id) {
        $query = "SELECT streak_atual FROM usuarios WHERE id = :usuario_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ? $result['streak_atual'] : 0;
    }

    /**
     * Atualizar usuário após completamento
     */
    private function atualizarUsuarioAposCompletamento($usuario_id, $pontos) {
        // Atualizar pontos e streak
        $query = "UPDATE usuarios 
                  SET pontos = pontos + :pontos,
                      streak_atual = streak_atual + 1,
                      streak_maximo = GREATEST(streak_maximo, streak_atual + 1),
                      total_habitos = total_habitos + 1
                  WHERE id = :usuario_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pontos", $pontos);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();
    }

    /**
     * Obter estatísticas do hábito
     */
    public function getEstatisticas($usuario_id = null) {
        $where_clause = "WHERE h.id = :habito_id";
        if ($usuario_id) {
            $where_clause .= " AND h.usuario_id = :usuario_id";
        }

        $query = "SELECT 
                    h.titulo,
                    h.categoria,
                    COUNT(hc.id) as total_completamentos,
                    SUM(hc.total_pontos) as pontos_totais,
                    AVG(hc.total_pontos) as media_pontos,
                    MAX(hc.data_completado) as ultimo_completado,
                    MIN(hc.data_completado) as primeiro_completado,
                    COUNT(DISTINCT hc.data_completado) as dias_ativos
                  FROM " . $this->table_name . " h
                  LEFT JOIN habitos_completados hc ON h.id = hc.habito_id
                  {$where_clause}
                  GROUP BY h.id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":habito_id", $this->id);
        if ($usuario_id) {
            $stmt->bindParam(":usuario_id", $usuario_id);
        }
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Obter histórico de completamentos
     */
    public function getHistoricoCompletamentos($usuario_id, $limite = 30) {
        $query = "SELECT 
                    data_completado,
                    hora_completado,
                    pontos_ganhos,
                    multiplicador,
                    bonus_streak,
                    bonus_primeiro_dia,
                    total_pontos
                  FROM habitos_completados
                  WHERE habito_id = :habito_id 
                  AND usuario_id = :usuario_id
                  ORDER BY data_completado DESC, hora_completado DESC
                  LIMIT :limite";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":habito_id", $this->id);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Verificar se hábito foi completado em uma data específica
     */
    public function foiCompletadoEm($usuario_id, $data) {
        $query = "SELECT COUNT(*) as total 
                  FROM habitos_completados 
                  WHERE habito_id = :habito_id 
                  AND usuario_id = :usuario_id 
                  AND data_completado = :data";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":habito_id", $this->id);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":data", $data);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result['total'] > 0;
    }

    /**
     * Obter hábitos por categoria
     */
    public function getHabitosPorCategoria($usuario_id) {
        $query = "SELECT 
                    categoria,
                    COUNT(*) as total_habitos,
                    SUM(CASE WHEN ativo = TRUE THEN 1 ELSE 0 END) as habitos_ativos
                  FROM " . $this->table_name . "
                  WHERE usuario_id = :usuario_id
                  GROUP BY categoria
                  ORDER BY total_habitos DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter hábitos mais produtivos
     */
    public function getHabitosMaisProdutivos($usuario_id, $limite = 5) {
        $query = "SELECT 
                    h.id,
                    h.titulo,
                    h.categoria,
                    COUNT(hc.id) as total_completamentos,
                    SUM(hc.total_pontos) as pontos_totais
                  FROM " . $this->table_name . " h
                  LEFT JOIN habitos_completados hc ON h.id = hc.habito_id
                  WHERE h.usuario_id = :usuario_id AND h.ativo = TRUE
                  GROUP BY h.id
                  ORDER BY pontos_totais DESC
                  LIMIT :limite";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obter dados para API
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'usuario_id' => $this->usuario_id,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'categoria' => $this->categoria,
            'frequencia' => $this->frequencia,
            'pontos_base' => $this->pontos_base,
            'ativo' => $this->ativo,
            'data_criacao' => $this->data_criacao
        ];
    }

    /**
     * Contar hábitos de um usuário
     */
    public function contarPorUsuario($usuario_id, $categoria = null) {
        $where_clause = "WHERE usuario_id = :usuario_id AND ativo = TRUE";
        if ($categoria) {
            $where_clause .= " AND categoria = :categoria";
        }

        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " {$where_clause}";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        if ($categoria) {
            $stmt->bindParam(":categoria", $categoria);
        }

        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Obter categorias disponíveis
     */
    public static function getCategorias() {
        return [
            'saude' => 'Saúde',
            'exercicio' => 'Exercício',
            'alimentacao' => 'Alimentação',
            'mental' => 'Mental',
            'produtividade' => 'Produtividade',
            'social' => 'Social'
        ];
    }

    /**
     * Obter frequências disponíveis
     */
    public static function getFrequencias() {
        return [
            'diario' => 'Diário',
            'semanal' => 'Semanal',
            'mensal' => 'Mensal'
        ];
    }

    /**
     * Validar categoria
     */
    public static function validarCategoria($categoria) {
        $categorias = self::getCategorias();
        return array_key_exists($categoria, $categorias);
    }

    /**
     * Validar frequência
     */
    public static function validarFrequencia($frequencia) {
        $frequencias = self::getFrequencias();
        return array_key_exists($frequencia, $frequencias);
    }
}
?>
