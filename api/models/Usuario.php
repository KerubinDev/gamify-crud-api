<?php
/**
 * Modelo Usuario
 * Sistema Vida Equilibrada
 */

require_once __DIR__ . '/../config/database.php';

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $nome;
    public $email;
    public $senha;
    public $pontos;
    public $streak_atual;
    public $streak_maximo;
    public $total_habitos;
    public $data_cadastro;
    public $ultimo_login;
    public $ativo;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Criar novo usuário
     */
    public function criar() {
        $query = "INSERT INTO " . $this->table_name . "
                (nome, email, senha, pontos, streak_atual, streak_maximo, total_habitos, ativo)
                VALUES (:nome, :email, :senha, :pontos, :streak_atual, :streak_maximo, :total_habitos, :ativo)";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->nome = sanitizeInput($this->nome);
        $this->email = sanitizeInput($this->email);
        $this->senha = hashPassword($this->senha);

        // Bind dos valores
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":senha", $this->senha);
        $stmt->bindParam(":pontos", $this->pontos);
        $stmt->bindParam(":streak_atual", $this->streak_atual);
        $stmt->bindParam(":streak_maximo", $this->streak_maximo);
        $stmt->bindParam(":total_habitos", $this->total_habitos);
        $stmt->bindParam(":ativo", $this->ativo);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            logMessage("Usuário criado: {$this->email}");
            return true;
        }

        logMessage("Erro ao criar usuário: " . implode(", ", $stmt->errorInfo()), 'ERROR');
        return false;
    }

    /**
     * Ler todos os usuários
     */
    public function ler($pagina = 1, $limite = ITENS_POR_PAGINA, $busca = null, $ordenacao = 'pontos') {
        $offset = ($pagina - 1) * $limite;
        
        $where_clause = "WHERE ativo = TRUE";
        if ($busca) {
            $busca = sanitizeInput($busca);
            $where_clause .= " AND (nome LIKE :busca OR email LIKE :busca)";
        }

        $query = "SELECT id, nome, email, pontos, streak_atual, streak_maximo, 
                         total_habitos, data_cadastro, ultimo_login, ativo
                  FROM " . $this->table_name . "
                  {$where_clause}
                  ORDER BY {$ordenacao} DESC
                  LIMIT :limite OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        
        if ($busca) {
            $busca_param = "%{$busca}%";
            $stmt->bindParam(":busca", $busca_param);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Ler usuário por ID
     */
    public function lerPorId($id) {
        $query = "SELECT id, nome, email, pontos, streak_atual, streak_maximo, 
                         total_habitos, data_cadastro, ultimo_login, ativo
                  FROM " . $this->table_name . "
                  WHERE id = :id AND ativo = TRUE";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        $row = $stmt->fetch();
        if ($row) {
            $this->id = $row['id'];
            $this->nome = $row['nome'];
            $this->email = $row['email'];
            $this->pontos = $row['pontos'];
            $this->streak_atual = $row['streak_atual'];
            $this->streak_maximo = $row['streak_maximo'];
            $this->total_habitos = $row['total_habitos'];
            $this->data_cadastro = $row['data_cadastro'];
            $this->ultimo_login = $row['ultimo_login'];
            $this->ativo = $row['ativo'];
            return true;
        }
        return false;
    }

    /**
     * Ler usuário por email
     */
    public function lerPorEmail($email) {
        $query = "SELECT id, nome, email, senha, pontos, streak_atual, streak_maximo, 
                         total_habitos, data_cadastro, ultimo_login, ativo
                  FROM " . $this->table_name . "
                  WHERE email = :email AND ativo = TRUE";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $row = $stmt->fetch();
        if ($row) {
            $this->id = $row['id'];
            $this->nome = $row['nome'];
            $this->email = $row['email'];
            $this->senha = $row['senha'];
            $this->pontos = $row['pontos'];
            $this->streak_atual = $row['streak_atual'];
            $this->streak_maximo = $row['streak_maximo'];
            $this->total_habitos = $row['total_habitos'];
            $this->data_cadastro = $row['data_cadastro'];
            $this->ultimo_login = $row['ultimo_login'];
            $this->ativo = $row['ativo'];
            return true;
        }
        return false;
    }

    /**
     * Atualizar usuário
     */
    public function atualizar() {
        $query = "UPDATE " . $this->table_name . "
                SET nome = :nome, email = :email, pontos = :pontos, 
                    streak_atual = :streak_atual, streak_maximo = :streak_maximo, 
                    total_habitos = :total_habitos, ativo = :ativo
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->nome = sanitizeInput($this->nome);
        $this->email = sanitizeInput($this->email);

        // Bind dos valores
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":pontos", $this->pontos);
        $stmt->bindParam(":streak_atual", $this->streak_atual);
        $stmt->bindParam(":streak_maximo", $this->streak_maximo);
        $stmt->bindParam(":total_habitos", $this->total_habitos);
        $stmt->bindParam(":ativo", $this->ativo);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            logMessage("Usuário atualizado: ID {$this->id}");
            return true;
        }

        logMessage("Erro ao atualizar usuário: " . implode(", ", $stmt->errorInfo()), 'ERROR');
        return false;
    }

    /**
     * Atualizar senha
     */
    public function atualizarSenha($nova_senha) {
        $query = "UPDATE " . $this->table_name . "
                SET senha = :senha
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $senha_hash = hashPassword($nova_senha);

        $stmt->bindParam(":senha", $senha_hash);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            logMessage("Senha atualizada para usuário: ID {$this->id}");
            return true;
        }

        logMessage("Erro ao atualizar senha: " . implode(", ", $stmt->errorInfo()), 'ERROR');
        return false;
    }

    /**
     * Deletar usuário (soft delete)
     */
    public function deletar() {
        $query = "UPDATE " . $this->table_name . "
                SET ativo = FALSE
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            logMessage("Usuário deletado: ID {$this->id}");
            return true;
        }

        logMessage("Erro ao deletar usuário: " . implode(", ", $stmt->errorInfo()), 'ERROR');
        return false;
    }

    /**
     * Deletar usuário permanentemente
     */
    public function deletarPermanente() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            logMessage("Usuário deletado permanentemente: ID {$this->id}");
            return true;
        }

        logMessage("Erro ao deletar usuário permanentemente: " . implode(", ", $stmt->errorInfo()), 'ERROR');
        return false;
    }

    /**
     * Atualizar último login
     */
    public function atualizarUltimoLogin() {
        $query = "UPDATE " . $this->table_name . "
                SET ultimo_login = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Adicionar pontos ao usuário
     */
    public function adicionarPontos($pontos) {
        $query = "UPDATE " . $this->table_name . "
                SET pontos = pontos + :pontos
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pontos", $pontos);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            $this->pontos += $pontos;
            logMessage("Pontos adicionados ao usuário {$this->id}: +{$pontos}");
            return true;
        }

        logMessage("Erro ao adicionar pontos: " . implode(", ", $stmt->errorInfo()), 'ERROR');
        return false;
    }

    /**
     * Atualizar streak
     */
    public function atualizarStreak($novo_streak) {
        $query = "UPDATE " . $this->table_name . "
                SET streak_atual = :streak_atual,
                    streak_maximo = GREATEST(streak_maximo, :streak_atual)
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":streak_atual", $novo_streak);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            $this->streak_atual = $novo_streak;
            if ($novo_streak > $this->streak_maximo) {
                $this->streak_maximo = $novo_streak;
            }
            logMessage("Streak atualizado para usuário {$this->id}: {$novo_streak}");
            return true;
        }

        logMessage("Erro ao atualizar streak: " . implode(", ", $stmt->errorInfo()), 'ERROR');
        return false;
    }

    /**
     * Resetar streak
     */
    public function resetarStreak() {
        return $this->atualizarStreak(0);
    }

    /**
     * Incrementar total de hábitos
     */
    public function incrementarHabitos() {
        $query = "UPDATE " . $this->table_name . "
                SET total_habitos = total_habitos + 1
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            $this->total_habitos++;
            logMessage("Total de hábitos incrementado para usuário {$this->id}");
            return true;
        }

        logMessage("Erro ao incrementar hábitos: " . implode(", ", $stmt->errorInfo()), 'ERROR');
        return false;
    }

    /**
     * Obter estatísticas do usuário
     */
    public function getEstatisticas() {
        $query = "SELECT 
                    u.pontos,
                    u.streak_atual,
                    u.streak_maximo,
                    u.total_habitos,
                    COUNT(DISTINCT h.id) as total_habitos_criados,
                    COUNT(DISTINCT hc.id) as total_completamentos,
                    COUNT(DISTINCT bc.id) as total_badges,
                    AVG(hc.total_pontos) as media_pontos_por_habito,
                    MAX(hc.data_completado) as ultimo_habito_completado
                  FROM " . $this->table_name . " u
                  LEFT JOIN habitos h ON u.id = h.usuario_id AND h.ativo = TRUE
                  LEFT JOIN habitos_completados hc ON u.id = hc.usuario_id
                  LEFT JOIN badges_conquistadas bc ON u.id = bc.usuario_id
                  WHERE u.id = :id
                  GROUP BY u.id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Obter ranking do usuário
     */
    public function getPosicaoRanking() {
        $query = "SELECT 
                    (SELECT COUNT(*) + 1 
                     FROM usuarios u2 
                     WHERE u2.pontos > u1.pontos AND u2.ativo = TRUE) as posicao
                  FROM " . $this->table_name . " u1
                  WHERE u1.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result ? $result['posicao'] : null;
    }

    /**
     * Verificar se email já existe
     */
    public function emailExiste($email, $excluir_id = null) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
        if ($excluir_id) {
            $query .= " AND id != :excluir_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        if ($excluir_id) {
            $stmt->bindParam(":excluir_id", $excluir_id);
        }

        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Autenticar usuário
     */
    public function autenticar($email, $senha) {
        if ($this->lerPorEmail($email)) {
            if (verifyPassword($senha, $this->senha)) {
                $this->atualizarUltimoLogin();
                logMessage("Usuário autenticado: {$email}");
                return true;
            }
        }
        logMessage("Tentativa de autenticação falhou: {$email}", 'WARNING');
        return false;
    }

    /**
     * Obter nível do usuário baseado nos pontos
     */
    public function getNivel() {
        if (!NIVEIS_ENABLED) {
            return 1;
        }
        
        $nivel = floor($this->pontos / PONTOS_POR_NIVEL) + 1;
        return min($nivel, NIVEL_MAXIMO);
    }

    /**
     * Obter progresso para o próximo nível
     */
    public function getProgressoNivel() {
        if (!NIVEIS_ENABLED) {
            return 100;
        }
        
        $pontos_nivel_atual = ($this->getNivel() - 1) * PONTOS_POR_NIVEL;
        $pontos_proximo_nivel = $this->getNivel() * PONTOS_POR_NIVEL;
        $pontos_restantes = $pontos_proximo_nivel - $this->pontos;
        
        if ($pontos_restantes <= 0) {
            return 100;
        }
        
        $progresso = (($this->pontos - $pontos_nivel_atual) / PONTOS_POR_NIVEL) * 100;
        return round($progresso, 2);
    }

    /**
     * Obter dados para API
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'email' => $this->email,
            'pontos' => $this->pontos,
            'streak_atual' => $this->streak_atual,
            'streak_maximo' => $this->streak_maximo,
            'total_habitos' => $this->total_habitos,
            'nivel' => $this->getNivel(),
            'progresso_nivel' => $this->getProgressoNivel(),
            'data_cadastro' => $this->data_cadastro,
            'ultimo_login' => $this->ultimo_login,
            'ativo' => $this->ativo
        ];
    }

    /**
     * Contar total de usuários
     */
    public function contar($busca = null) {
        $where_clause = "WHERE ativo = TRUE";
        if ($busca) {
            $busca = sanitizeInput($busca);
            $where_clause .= " AND (nome LIKE :busca OR email LIKE :busca)";
        }

        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " {$where_clause}";

        $stmt = $this->conn->prepare($query);
        if ($busca) {
            $busca_param = "%{$busca}%";
            $stmt->bindParam(":busca", $busca_param);
        }

        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }
}
?>
