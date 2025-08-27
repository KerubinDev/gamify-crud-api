<?php
/**
 * Controlador de Hábitos
 * Sistema Vida Equilibrada
 */

require_once __DIR__ . '/../models/Habito.php';
require_once __DIR__ . '/../models/Badge.php';

class HabitosController {
    private $db;
    private $habito;
    private $badge;

    public function __construct($db) {
        $this->db = $db;
        $this->habito = new Habito($db);
        $this->badge = new Badge($db);
    }

    /**
     * Listar hábitos
     */
    public function listar($params = []) {
        try {
            $usuario_id = isset($params['usuario_id']) ? (int) $params['usuario_id'] : null;
            $pagina = isset($params['pagina']) ? (int) $params['pagina'] : 1;
            $limite = isset($params['limite']) ? (int) $params['limite'] : ITENS_POR_PAGINA;
            $categoria = isset($params['categoria']) ? $params['categoria'] : null;

            // Validar parâmetros
            if ($pagina < 1) $pagina = 1;
            if ($limite < 1 || $limite > MAX_ITENS_POR_PAGINA) $limite = ITENS_POR_PAGINA;

            if (!$usuario_id) {
                respostaErro('ID do usuário é obrigatório');
                return;
            }

            // Obter hábitos
            $habitos = $this->habito->lerPorUsuario($usuario_id, $pagina, $limite, $categoria);
            $total = $this->habito->contarPorUsuario($usuario_id, $categoria);
            $total_paginas = ceil($total / $limite);

            // Formatar dados para resposta
            $habitos_formatados = [];
            foreach ($habitos as $hab) {
                $habitos_formatados[] = [
                    'id' => $hab['id'],
                    'titulo' => $hab['titulo'],
                    'descricao' => $hab['descricao'],
                    'categoria' => $hab['categoria'],
                    'categoria_nome' => Habito::getCategorias()[$hab['categoria']] ?? $hab['categoria'],
                    'frequencia' => $hab['frequencia'],
                    'frequencia_nome' => Habito::getFrequencias()[$hab['frequencia']] ?? $hab['frequencia'],
                    'pontos_base' => $hab['pontos_base'],
                    'ativo' => $hab['ativo'],
                    'data_criacao' => $hab['data_criacao']
                ];
            }

            respostaSucesso([
                'habitos' => $habitos_formatados,
                'paginacao' => [
                    'pagina_atual' => $pagina,
                    'itens_por_pagina' => $limite,
                    'total_itens' => $total,
                    'total_paginas' => $total_paginas
                ],
                'categorias' => Habito::getCategorias(),
                'frequencias' => Habito::getFrequencias()
            ], 'Hábitos listados com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao listar hábitos: ' . $e->getMessage());
        }
    }

    /**
     * Obter hábito por ID
     */
    public function obter($id) {
        try {
            $id = validarId($id);

            if ($this->habito->lerPorId($id)) {
                $estatisticas = $this->habito->getEstatisticas($this->habito->usuario_id);
                $historico = $this->habito->getHistoricoCompletamentos($this->habito->usuario_id, 10);

                $dados_habito = $this->habito->toArray();
                $dados_habito['categoria_nome'] = Habito::getCategorias()[$this->habito->categoria] ?? $this->habito->categoria;
                $dados_habito['frequencia_nome'] = Habito::getFrequencias()[$this->habito->frequencia] ?? $this->habito->frequencia;
                $dados_habito['estatisticas'] = $estatisticas;
                $dados_habito['historico'] = $historico;

                respostaSucesso($dados_habito, 'Hábito encontrado');
            } else {
                respostaNaoEncontrado('Hábito não encontrado');
            }

        } catch (Exception $e) {
            respostaErro('Erro ao obter hábito: ' . $e->getMessage());
        }
    }

    /**
     * Criar hábito
     */
    public function criar($data) {
        try {
            // Validar dados obrigatórios
            validarDadosObrigatorios($data, ['usuario_id', 'titulo', 'categoria']);

            $usuario_id = (int) $data['usuario_id'];
            if ($usuario_id <= 0) {
                respostaErro('ID do usuário inválido');
                return;
            }

            // Validar categoria
            if (!Habito::validarCategoria($data['categoria'])) {
                respostaErro('Categoria inválida');
                return;
            }

            // Validar frequência
            $frequencia = isset($data['frequencia']) ? $data['frequencia'] : 'diario';
            if (!Habito::validarFrequencia($frequencia)) {
                respostaErro('Frequência inválida');
                return;
            }

            // Configurar dados do hábito
            $this->habito->usuario_id = $usuario_id;
            $this->habito->titulo = $data['titulo'];
            $this->habito->descricao = isset($data['descricao']) ? $data['descricao'] : '';
            $this->habito->categoria = $data['categoria'];
            $this->habito->frequencia = $frequencia;
            $this->habito->pontos_base = isset($data['pontos_base']) ? (int) $data['pontos_base'] : PONTOS_BASE_HABITO;
            $this->habito->ativo = true;

            // Validar pontos base
            if ($this->habito->pontos_base <= 0) {
                respostaErro('Pontos base devem ser maiores que zero');
                return;
            }

            // Criar hábito
            if ($this->habito->criar()) {
                $dados_habito = $this->habito->toArray();
                $dados_habito['categoria_nome'] = Habito::getCategorias()[$this->habito->categoria];
                $dados_habito['frequencia_nome'] = Habito::getFrequencias()[$this->habito->frequencia];

                respostaSucesso($dados_habito, 'Hábito criado com sucesso');
            } else {
                respostaErro('Erro ao criar hábito');
            }

        } catch (Exception $e) {
            respostaErro('Erro ao criar hábito: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar hábito
     */
    public function atualizar($id, $data) {
        try {
            $id = validarId($id);

            // Verificar se hábito existe
            if (!$this->habito->lerPorId($id)) {
                respostaNaoEncontrado('Hábito não encontrado');
                return;
            }

            // Validar dados obrigatórios
            validarDadosObrigatorios($data, ['titulo', 'categoria']);

            // Validar categoria
            if (!Habito::validarCategoria($data['categoria'])) {
                respostaErro('Categoria inválida');
                return;
            }

            // Validar frequência
            if (isset($data['frequencia']) && !Habito::validarFrequencia($data['frequencia'])) {
                respostaErro('Frequência inválida');
                return;
            }

            // Atualizar dados
            $this->habito->titulo = $data['titulo'];
            $this->habito->descricao = isset($data['descricao']) ? $data['descricao'] : $this->habito->descricao;
            $this->habito->categoria = $data['categoria'];
            
            if (isset($data['frequencia'])) {
                $this->habito->frequencia = $data['frequencia'];
            }
            
            if (isset($data['pontos_base'])) {
                $pontos_base = (int) $data['pontos_base'];
                if ($pontos_base <= 0) {
                    respostaErro('Pontos base devem ser maiores que zero');
                    return;
                }
                $this->habito->pontos_base = $pontos_base;
            }
            
            if (isset($data['ativo'])) {
                $this->habito->ativo = (bool) $data['ativo'];
            }

            // Atualizar hábito
            if ($this->habito->atualizar()) {
                $dados_habito = $this->habito->toArray();
                $dados_habito['categoria_nome'] = Habito::getCategorias()[$this->habito->categoria];
                $dados_habito['frequencia_nome'] = Habito::getFrequencias()[$this->habito->frequencia];

                respostaSucesso($dados_habito, 'Hábito atualizado com sucesso');
            } else {
                respostaErro('Erro ao atualizar hábito');
            }

        } catch (Exception $e) {
            respostaErro('Erro ao atualizar hábito: ' . $e->getMessage());
        }
    }

    /**
     * Deletar hábito
     */
    public function deletar($id) {
        try {
            $id = validarId($id);

            // Verificar se hábito existe
            if (!$this->habito->lerPorId($id)) {
                respostaNaoEncontrado('Hábito não encontrado');
                return;
            }

            // Deletar hábito (soft delete)
            if ($this->habito->deletar()) {
                respostaSucesso(null, 'Hábito deletado com sucesso');
            } else {
                respostaErro('Erro ao deletar hábito');
            }

        } catch (Exception $e) {
            respostaErro('Erro ao deletar hábito: ' . $e->getMessage());
        }
    }

    /**
     * Completar hábito
     */
    public function completar($id, $data) {
        try {
            $id = validarId($id);

            // Verificar se hábito existe
            if (!$this->habito->lerPorId($id)) {
                respostaNaoEncontrado('Hábito não encontrado');
                return;
            }

            $usuario_id = $this->habito->usuario_id;

            // Completar hábito
            $resultado = $this->habito->completar($usuario_id);

            if ($resultado['success']) {
                // Verificar badges automáticas
                $badges_concedidas = $this->badge->verificarBadgesAutomaticas($usuario_id);
                
                // Verificar badges de tempo
                $hora_atual = date('H:i:s');
                $badges_tempo = $this->badge->verificarBadgesTempo($usuario_id, $hora_atual);
                
                $todas_badges = array_merge($badges_concedidas, $badges_tempo);

                $resposta = [
                    'pontos_ganhos' => $resultado['pontos_ganhos'],
                    'detalhes' => $resultado['detalhes'],
                    'badges_concedidas' => $todas_badges
                ];

                respostaSucesso($resposta, $resultado['message']);
            } else {
                respostaErro($resultado['message']);
            }

        } catch (Exception $e) {
            respostaErro('Erro ao completar hábito: ' . $e->getMessage());
        }
    }

    /**
     * Obter estatísticas de hábitos
     */
    public function getEstatisticas($usuario_id) {
        try {
            $usuario_id = validarId($usuario_id);

            $habitos_por_categoria = $this->habito->getHabitosPorCategoria($usuario_id);
            $habitos_mais_produtivos = $this->habito->getHabitosMaisProdutivos($usuario_id);

            respostaSucesso([
                'habitos_por_categoria' => $habitos_por_categoria,
                'habitos_mais_produtivos' => $habitos_mais_produtivos,
                'categorias' => Habito::getCategorias()
            ], 'Estatísticas obtidas com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter estatísticas: ' . $e->getMessage());
        }
    }

    /**
     * Obter histórico de completamentos
     */
    public function getHistorico($id, $params = []) {
        try {
            $id = validarId($id);

            // Verificar se hábito existe
            if (!$this->habito->lerPorId($id)) {
                respostaNaoEncontrado('Hábito não encontrado');
                return;
            }

            $limite = isset($params['limite']) ? (int) $params['limite'] : 30;
            if ($limite < 1 || $limite > 100) $limite = 30;

            $historico = $this->habito->getHistoricoCompletamentos($this->habito->usuario_id, $limite);

            respostaSucesso([
                'habito' => [
                    'id' => $this->habito->id,
                    'titulo' => $this->habito->titulo,
                    'categoria' => $this->habito->categoria
                ],
                'historico' => $historico
            ], 'Histórico obtido com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter histórico: ' . $e->getMessage());
        }
    }

    /**
     * Verificar se hábito foi completado em uma data
     */
    public function verificarCompletamento($id, $data) {
        try {
            $id = validarId($id);

            // Verificar se hábito existe
            if (!$this->habito->lerPorId($id)) {
                respostaNaoEncontrado('Hábito não encontrado');
                return;
            }

            $data_verificar = isset($data['data']) ? $data['data'] : date('Y-m-d');
            
            // Validar formato da data
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_verificar)) {
                respostaErro('Formato de data inválido. Use YYYY-MM-DD');
                return;
            }

            $foi_completado = $this->habito->foiCompletadoEm($this->habito->usuario_id, $data_verificar);

            respostaSucesso([
                'habito_id' => $this->habito->id,
                'data' => $data_verificar,
                'foi_completado' => $foi_completado
            ], 'Verificação realizada com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao verificar completamento: ' . $e->getMessage());
        }
    }

    /**
     * Obter hábitos por categoria
     */
    public function getPorCategoria($usuario_id, $categoria) {
        try {
            $usuario_id = validarId($usuario_id);

            // Validar categoria
            if (!Habito::validarCategoria($categoria)) {
                respostaErro('Categoria inválida');
                return;
            }

            $habitos = $this->habito->lerPorUsuario($usuario_id, 1, 100, $categoria);

            // Formatar dados para resposta
            $habitos_formatados = [];
            foreach ($habitos as $hab) {
                $habitos_formatados[] = [
                    'id' => $hab['id'],
                    'titulo' => $hab['titulo'],
                    'descricao' => $hab['descricao'],
                    'categoria' => $hab['categoria'],
                    'categoria_nome' => Habito::getCategorias()[$hab['categoria']],
                    'frequencia' => $hab['frequencia'],
                    'frequencia_nome' => Habito::getFrequencias()[$hab['frequencia']],
                    'pontos_base' => $hab['pontos_base'],
                    'ativo' => $hab['ativo'],
                    'data_criacao' => $hab['data_criacao']
                ];
            }

            respostaSucesso([
                'categoria' => $categoria,
                'categoria_nome' => Habito::getCategorias()[$categoria],
                'habitos' => $habitos_formatados,
                'total' => count($habitos_formatados)
            ], 'Hábitos da categoria obtidos com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter hábitos da categoria: ' . $e->getMessage());
        }
    }

    /**
     * Obter hábitos ativos
     */
    public function getAtivos($usuario_id) {
        try {
            $usuario_id = validarId($usuario_id);

            $habitos = $this->habito->lerPorUsuario($usuario_id, 1, 100);
            
            // Filtrar apenas hábitos ativos
            $habitos_ativos = array_filter($habitos, function($hab) {
                return $hab['ativo'] == true;
            });

            // Formatar dados para resposta
            $habitos_formatados = [];
            foreach ($habitos_ativos as $hab) {
                $habitos_formatados[] = [
                    'id' => $hab['id'],
                    'titulo' => $hab['titulo'],
                    'descricao' => $hab['descricao'],
                    'categoria' => $hab['categoria'],
                    'categoria_nome' => Habito::getCategorias()[$hab['categoria']],
                    'frequencia' => $hab['frequencia'],
                    'frequencia_nome' => Habito::getFrequencias()[$hab['frequencia']],
                    'pontos_base' => $hab['pontos_base'],
                    'data_criacao' => $hab['data_criacao']
                ];
            }

            respostaSucesso([
                'habitos' => $habitos_formatados,
                'total' => count($habitos_formatados)
            ], 'Hábitos ativos obtidos com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter hábitos ativos: ' . $e->getMessage());
        }
    }
}
?>
