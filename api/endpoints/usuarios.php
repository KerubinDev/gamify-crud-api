<?php
/**
 * Controlador de Usuários
 * Sistema Vida Equilibrada
 */

require_once __DIR__ . '/../models/Usuario.php';

class UsuariosController {
    private $db;
    private $usuario;

    public function __construct($db) {
        $this->db = $db;
        $this->usuario = new Usuario($db);
    }

    /**
     * Listar usuários
     */
    public function listar($params = []) {
        try {
            $pagina = isset($params['pagina']) ? (int) $params['pagina'] : 1;
            $limite = isset($params['limite']) ? (int) $params['limite'] : ITENS_POR_PAGINA;
            $busca = isset($params['busca']) ? $params['busca'] : null;
            $ordenacao = isset($params['ordenacao']) ? $params['ordenacao'] : 'pontos';

            // Validar parâmetros
            if ($pagina < 1) $pagina = 1;
            if ($limite < 1 || $limite > MAX_ITENS_POR_PAGINA) $limite = ITENS_POR_PAGINA;

            // Obter usuários
            $usuarios = $this->usuario->ler($pagina, $limite, $busca, $ordenacao);
            $total = $this->usuario->contar($busca);
            $total_paginas = ceil($total / $limite);

            // Formatar dados para resposta
            $usuarios_formatados = [];
            foreach ($usuarios as $user) {
                $usuarios_formatados[] = [
                    'id' => $user['id'],
                    'nome' => $user['nome'],
                    'email' => $user['email'],
                    'pontos' => $user['pontos'],
                    'streak_atual' => $user['streak_atual'],
                    'streak_maximo' => $user['streak_maximo'],
                    'total_habitos' => $user['total_habitos'],
                    'nivel' => $this->calcularNivel($user['pontos']),
                    'data_cadastro' => $user['data_cadastro'],
                    'ultimo_login' => $user['ultimo_login']
                ];
            }

            respostaSucesso([
                'usuarios' => $usuarios_formatados,
                'paginacao' => [
                    'pagina_atual' => $pagina,
                    'itens_por_pagina' => $limite,
                    'total_itens' => $total,
                    'total_paginas' => $total_paginas
                ]
            ], 'Usuários listados com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao listar usuários: ' . $e->getMessage());
        }
    }

    /**
     * Obter usuário por ID
     */
    public function obter($id) {
        try {
            $id = validarId($id);

            if ($this->usuario->lerPorId($id)) {
                $estatisticas = $this->usuario->getEstatisticas();
                $posicao_ranking = $this->usuario->getPosicaoRanking();

                $dados_usuario = $this->usuario->toArray();
                $dados_usuario['estatisticas'] = $estatisticas;
                $dados_usuario['posicao_ranking'] = $posicao_ranking;

                respostaSucesso($dados_usuario, 'Usuário encontrado');
            } else {
                respostaNaoEncontrado('Usuário não encontrado');
            }

        } catch (Exception $e) {
            respostaErro('Erro ao obter usuário: ' . $e->getMessage());
        }
    }

    /**
     * Criar usuário
     */
    public function criar($data) {
        try {
            // Validar dados obrigatórios
            validarDadosObrigatorios($data, ['nome', 'email', 'senha']);

            // Validar email
            if (!validateEmail($data['email'])) {
                respostaErro('Email inválido');
                return;
            }

            // Verificar se email já existe
            if ($this->usuario->emailExiste($data['email'])) {
                respostaErro('Email já cadastrado');
                return;
            }

            // Validar senha
            if (strlen($data['senha']) < 6) {
                respostaErro('Senha deve ter pelo menos 6 caracteres');
                return;
            }

            // Configurar dados do usuário
            $this->usuario->nome = $data['nome'];
            $this->usuario->email = $data['email'];
            $this->usuario->senha = $data['senha'];
            $this->usuario->pontos = 0;
            $this->usuario->streak_atual = 0;
            $this->usuario->streak_maximo = 0;
            $this->usuario->total_habitos = 0;
            $this->usuario->ativo = true;

            // Criar usuário
            if ($this->usuario->criar()) {
                $dados_usuario = $this->usuario->toArray();
                unset($dados_usuario['senha']); // Não retornar senha

                respostaSucesso($dados_usuario, 'Usuário criado com sucesso');
            } else {
                respostaErro('Erro ao criar usuário');
            }

        } catch (Exception $e) {
            respostaErro('Erro ao criar usuário: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar usuário
     */
    public function atualizar($id, $data) {
        try {
            $id = validarId($id);

            // Verificar se usuário existe
            if (!$this->usuario->lerPorId($id)) {
                respostaNaoEncontrado('Usuário não encontrado');
                return;
            }

            // Validar dados obrigatórios
            validarDadosObrigatorios($data, ['nome', 'email']);

            // Validar email
            if (!validateEmail($data['email'])) {
                respostaErro('Email inválido');
                return;
            }

            // Verificar se email já existe (excluindo o usuário atual)
            if ($this->usuario->emailExiste($data['email'], $id)) {
                respostaErro('Email já cadastrado por outro usuário');
                return;
            }

            // Atualizar dados
            $this->usuario->nome = $data['nome'];
            $this->usuario->email = $data['email'];

            // Campos opcionais
            if (isset($data['pontos'])) {
                $this->usuario->pontos = (int) $data['pontos'];
            }
            if (isset($data['streak_atual'])) {
                $this->usuario->streak_atual = (int) $data['streak_atual'];
            }
            if (isset($data['streak_maximo'])) {
                $this->usuario->streak_maximo = (int) $data['streak_maximo'];
            }
            if (isset($data['total_habitos'])) {
                $this->usuario->total_habitos = (int) $data['total_habitos'];
            }
            if (isset($data['ativo'])) {
                $this->usuario->ativo = (bool) $data['ativo'];
            }

            // Atualizar usuário
            if ($this->usuario->atualizar()) {
                $dados_usuario = $this->usuario->toArray();
                respostaSucesso($dados_usuario, 'Usuário atualizado com sucesso');
            } else {
                respostaErro('Erro ao atualizar usuário');
            }

        } catch (Exception $e) {
            respostaErro('Erro ao atualizar usuário: ' . $e->getMessage());
        }
    }

    /**
     * Deletar usuário
     */
    public function deletar($id) {
        try {
            $id = validarId($id);

            // Verificar se usuário existe
            if (!$this->usuario->lerPorId($id)) {
                respostaNaoEncontrado('Usuário não encontrado');
                return;
            }

            // Deletar usuário (soft delete)
            if ($this->usuario->deletar()) {
                respostaSucesso(null, 'Usuário deletado com sucesso');
            } else {
                respostaErro('Erro ao deletar usuário');
            }

        } catch (Exception $e) {
            respostaErro('Erro ao deletar usuário: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar senha
     */
    public function atualizarSenha($id, $data) {
        try {
            $id = validarId($id);

            // Validar dados obrigatórios
            validarDadosObrigatorios($data, ['senha_atual', 'nova_senha']);

            // Verificar se usuário existe
            if (!$this->usuario->lerPorId($id)) {
                respostaNaoEncontrado('Usuário não encontrado');
                return;
            }

            // Verificar senha atual
            if (!verifyPassword($data['senha_atual'], $this->usuario->senha)) {
                respostaErro('Senha atual incorreta');
                return;
            }

            // Validar nova senha
            if (strlen($data['nova_senha']) < 6) {
                respostaErro('Nova senha deve ter pelo menos 6 caracteres');
                return;
            }

            // Atualizar senha
            if ($this->usuario->atualizarSenha($data['nova_senha'])) {
                respostaSucesso(null, 'Senha atualizada com sucesso');
            } else {
                respostaErro('Erro ao atualizar senha');
            }

        } catch (Exception $e) {
            respostaErro('Erro ao atualizar senha: ' . $e->getMessage());
        }
    }

    /**
     * Obter estatísticas do usuário
     */
    public function getEstatisticas($id) {
        try {
            $id = validarId($id);

            // Verificar se usuário existe
            if (!$this->usuario->lerPorId($id)) {
                respostaNaoEncontrado('Usuário não encontrado');
                return;
            }

            $estatisticas = $this->usuario->getEstatisticas();
            $posicao_ranking = $this->usuario->getPosicaoRanking();

            respostaSucesso([
                'estatisticas' => $estatisticas,
                'posicao_ranking' => $posicao_ranking,
                'nivel' => $this->usuario->getNivel(),
                'progresso_nivel' => $this->usuario->getProgressoNivel()
            ], 'Estatísticas obtidas com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter estatísticas: ' . $e->getMessage());
        }
    }

    /**
     * Obter ranking do usuário
     */
    public function getRanking($id) {
        try {
            $id = validarId($id);

            // Verificar se usuário existe
            if (!$this->usuario->lerPorId($id)) {
                respostaNaoEncontrado('Usuário não encontrado');
                return;
            }

            $posicao_pontos = $this->usuario->getPosicaoRanking('pontos');
            $posicao_badges = $this->usuario->getPosicaoRanking('badges');
            $posicao_streak = $this->usuario->getPosicaoRanking('streak');

            respostaSucesso([
                'ranking_pontos' => $posicao_pontos,
                'ranking_badges' => $posicao_badges,
                'ranking_streak' => $posicao_streak
            ], 'Ranking obtido com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter ranking: ' . $e->getMessage());
        }
    }

    /**
     * Adicionar pontos ao usuário
     */
    public function adicionarPontos($id, $data) {
        try {
            $id = validarId($id);

            // Validar dados obrigatórios
            validarDadosObrigatorios($data, ['pontos']);

            $pontos = (int) $data['pontos'];
            if ($pontos <= 0) {
                respostaErro('Pontos devem ser maiores que zero');
                return;
            }

            // Verificar se usuário existe
            if (!$this->usuario->lerPorId($id)) {
                respostaNaoEncontrado('Usuário não encontrado');
                return;
            }

            // Adicionar pontos
            if ($this->usuario->adicionarPontos($pontos)) {
                respostaSucesso([
                    'pontos_adicionados' => $pontos,
                    'pontos_totais' => $this->usuario->pontos,
                    'nivel' => $this->usuario->getNivel(),
                    'progresso_nivel' => $this->usuario->getProgressoNivel()
                ], 'Pontos adicionados com sucesso');
            } else {
                respostaErro('Erro ao adicionar pontos');
            }

        } catch (Exception $e) {
            respostaErro('Erro ao adicionar pontos: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar streak do usuário
     */
    public function atualizarStreak($id, $data) {
        try {
            $id = validarId($id);

            // Validar dados obrigatórios
            validarDadosObrigatorios($data, ['streak']);

            $streak = (int) $data['streak'];
            if ($streak < 0) {
                respostaErro('Streak deve ser maior ou igual a zero');
                return;
            }

            // Verificar se usuário existe
            if (!$this->usuario->lerPorId($id)) {
                respostaNaoEncontrado('Usuário não encontrado');
                return;
            }

            // Atualizar streak
            if ($this->usuario->atualizarStreak($streak)) {
                respostaSucesso([
                    'streak_atual' => $this->usuario->streak_atual,
                    'streak_maximo' => $this->usuario->streak_maximo
                ], 'Streak atualizado com sucesso');
            } else {
                respostaErro('Erro ao atualizar streak');
            }

        } catch (Exception $e) {
            respostaErro('Erro ao atualizar streak: ' . $e->getMessage());
        }
    }

    /**
     * Resetar streak do usuário
     */
    public function resetarStreak($id) {
        try {
            $id = validarId($id);

            // Verificar se usuário existe
            if (!$this->usuario->lerPorId($id)) {
                respostaNaoEncontrado('Usuário não encontrado');
                return;
            }

            // Resetar streak
            if ($this->usuario->resetarStreak()) {
                respostaSucesso([
                    'streak_atual' => $this->usuario->streak_atual,
                    'streak_maximo' => $this->usuario->streak_maximo
                ], 'Streak resetado com sucesso');
            } else {
                respostaErro('Erro ao resetar streak');
            }

        } catch (Exception $e) {
            respostaErro('Erro ao resetar streak: ' . $e->getMessage());
        }
    }

    /**
     * Calcular nível baseado nos pontos
     */
    private function calcularNivel($pontos) {
        if (!NIVEIS_ENABLED) {
            return 1;
        }
        
        $nivel = floor($pontos / PONTOS_POR_NIVEL) + 1;
        return min($nivel, NIVEL_MAXIMO);
    }
}
?>
