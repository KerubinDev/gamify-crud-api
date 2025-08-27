<?php
/**
 * Controlador de Ranking
 * Sistema Vida Equilibrada
 */

require_once __DIR__ . '/../models/Ranking.php';

class RankingController {
    private $db;
    private $ranking;

    public function __construct($db) {
        $this->db = $db;
        $this->ranking = new Ranking($db);
    }

    /**
     * Obter ranking geral
     */
    public function obter($params = []) {
        try {
            $filtro = isset($params['filtro']) ? $params['filtro'] : 'pontos';
            $periodo = isset($params['periodo']) ? $params['periodo'] : 'geral';
            $categoria = isset($params['categoria']) ? $params['categoria'] : null;
            $limite = isset($params['limite']) ? (int) $params['limite'] : 10;
            $pagina = isset($params['pagina']) ? (int) $params['pagina'] : 1;
            $busca = isset($params['busca']) ? $params['busca'] : null;

            // Validar parâmetros
            if ($pagina < 1) $pagina = 1;
            if ($limite < 1 || $limite > MAX_ITENS_POR_PAGINA) $limite = 10;

            // Validar filtro
            if (!Ranking::validarFiltro($filtro)) {
                respostaErro('Filtro inválido');
                return;
            }

            // Validar período
            if ($periodo !== 'geral' && !Ranking::validarPeriodo($periodo)) {
                respostaErro('Período inválido');
                return;
            }

            $ranking_data = null;

            // Obter ranking baseado nos parâmetros
            switch ($filtro) {
                case 'streak':
                    $ranking_data = $this->ranking->getRankingStreak($limite);
                    break;
                case 'badges':
                    $ranking_data = $this->ranking->getRankingBadges($limite);
                    break;
                case 'categoria':
                    if (!$categoria) {
                        respostaErro('Categoria é obrigatória para este filtro');
                        return;
                    }
                    $ranking_data = $this->ranking->getRankingPorCategoria($categoria, $limite);
                    break;
                case 'pontos':
                default:
                    if ($periodo === 'geral') {
                        $ranking_data = $this->ranking->getRankingGeral($limite, $pagina, $filtro);
                    } else {
                        $ranking_data = $this->ranking->getRankingPorPeriodo($periodo, $limite);
                    }
                    break;
            }

            if ($busca) {
                $ranking_data = $this->ranking->buscarUsuarios($busca, $limite);
            }

            // Formatar dados para resposta
            $ranking_formatado = $this->ranking->formatarRankingParaAPI($ranking_data);

            // Obter estatísticas do ranking
            $estatisticas = $this->ranking->getEstatisticasRanking();

            respostaSucesso([
                'ranking' => $ranking_formatado,
                'filtro' => $filtro,
                'filtro_nome' => Ranking::getFiltrosDisponiveis()[$filtro] ?? $filtro,
                'periodo' => $periodo,
                'periodo_nome' => Ranking::getPeriodosDisponiveis()[$periodo] ?? $periodo,
                'categoria' => $categoria,
                'limite' => $limite,
                'pagina' => $pagina,
                'estatisticas' => $estatisticas,
                'filtros_disponiveis' => Ranking::getFiltrosDisponiveis(),
                'periodos_disponiveis' => Ranking::getPeriodosDisponiveis()
            ], 'Ranking obtido com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter ranking: ' . $e->getMessage());
        }
    }

    /**
     * Obter ranking por pontos
     */
    public function getRankingPontos($params = []) {
        try {
            $limite = isset($params['limite']) ? (int) $params['limite'] : 10;
            $pagina = isset($params['pagina']) ? (int) $params['pagina'] : 1;

            if ($pagina < 1) $pagina = 1;
            if ($limite < 1 || $limite > MAX_ITENS_POR_PAGINA) $limite = 10;

            $ranking_data = $this->ranking->getRankingGeral($limite, $pagina, 'pontos');
            $ranking_formatado = $this->ranking->formatarRankingParaAPI($ranking_data);

            respostaSucesso([
                'ranking' => $ranking_formatado,
                'tipo' => 'pontos',
                'limite' => $limite,
                'pagina' => $pagina
            ], 'Ranking por pontos obtido com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter ranking por pontos: ' . $e->getMessage());
        }
    }

    /**
     * Obter ranking por badges
     */
    public function getRankingBadges($params = []) {
        try {
            $limite = isset($params['limite']) ? (int) $params['limite'] : 10;

            if ($limite < 1 || $limite > MAX_ITENS_POR_PAGINA) $limite = 10;

            $ranking_data = $this->ranking->getRankingBadges($limite);

            respostaSucesso([
                'ranking' => $ranking_data,
                'tipo' => 'badges',
                'limite' => $limite
            ], 'Ranking por badges obtido com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter ranking por badges: ' . $e->getMessage());
        }
    }

    /**
     * Obter ranking por streak
     */
    public function getRankingStreak($params = []) {
        try {
            $limite = isset($params['limite']) ? (int) $params['limite'] : 10;

            if ($limite < 1 || $limite > MAX_ITENS_POR_PAGINA) $limite = 10;

            $ranking_data = $this->ranking->getRankingStreak($limite);

            respostaSucesso([
                'ranking' => $ranking_data,
                'tipo' => 'streak',
                'limite' => $limite
            ], 'Ranking por streak obtido com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter ranking por streak: ' . $e->getMessage());
        }
    }

    /**
     * Obter ranking por categoria
     */
    public function getRankingCategoria($categoria, $params = []) {
        try {
            $limite = isset($params['limite']) ? (int) $params['limite'] : 10;

            if ($limite < 1 || $limite > MAX_ITENS_POR_PAGINA) $limite = 10;

            $ranking_data = $this->ranking->getRankingPorCategoria($categoria, $limite);

            respostaSucesso([
                'ranking' => $ranking_data,
                'tipo' => 'categoria',
                'categoria' => $categoria,
                'limite' => $limite
            ], 'Ranking por categoria obtido com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter ranking por categoria: ' . $e->getMessage());
        }
    }

    /**
     * Obter ranking por período
     */
    public function getRankingPeriodo($periodo, $params = []) {
        try {
            // Validar período
            if (!Ranking::validarPeriodo($periodo)) {
                respostaErro('Período inválido');
                return;
            }

            $limite = isset($params['limite']) ? (int) $params['limite'] : 10;

            if ($limite < 1 || $limite > MAX_ITENS_POR_PAGINA) $limite = 10;

            $ranking_data = $this->ranking->getRankingPorPeriodo($periodo, $limite);

            respostaSucesso([
                'ranking' => $ranking_data,
                'tipo' => 'periodo',
                'periodo' => $periodo,
                'periodo_nome' => Ranking::getPeriodosDisponiveis()[$periodo],
                'limite' => $limite
            ], 'Ranking por período obtido com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter ranking por período: ' . $e->getMessage());
        }
    }

    /**
     * Obter posição de um usuário no ranking
     */
    public function getPosicaoUsuario($usuario_id, $params = []) {
        try {
            $usuario_id = validarId($usuario_id);
            $filtro = isset($params['filtro']) ? $params['filtro'] : 'pontos';

            // Validar filtro
            if (!Ranking::validarFiltro($filtro)) {
                respostaErro('Filtro inválido');
                return;
            }

            $posicao = $this->ranking->getPosicaoUsuario($usuario_id, $filtro);

            respostaSucesso([
                'usuario_id' => $usuario_id,
                'filtro' => $filtro,
                'filtro_nome' => Ranking::getFiltrosDisponiveis()[$filtro] ?? $filtro,
                'posicao' => $posicao
            ], 'Posição do usuário obtida com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter posição do usuário: ' . $e->getMessage());
        }
    }

    /**
     * Buscar usuários no ranking
     */
    public function buscarUsuarios($params = []) {
        try {
            $termo = isset($params['termo']) ? $params['termo'] : '';
            $limite = isset($params['limite']) ? (int) $params['limite'] : 10;

            if (empty($termo)) {
                respostaErro('Termo de busca é obrigatório');
                return;
            }

            if ($limite < 1 || $limite > MAX_ITENS_POR_PAGINA) $limite = 10;

            $usuarios = $this->ranking->buscarUsuarios($termo, $limite);

            respostaSucesso([
                'usuarios' => $usuarios,
                'termo' => $termo,
                'limite' => $limite,
                'total' => count($usuarios)
            ], 'Busca realizada com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao buscar usuários: ' . $e->getMessage());
        }
    }

    /**
     * Obter estatísticas do ranking
     */
    public function getEstatisticas($params = []) {
        try {
            $estatisticas = $this->ranking->getEstatisticasRanking();
            $top_performers = $this->ranking->getTopPerformers(5);
            $usuarios_ascensao = $this->ranking->getUsuariosAscensao(5);
            $competicoes_ativas = $this->ranking->getCompeticoesAtivas();

            respostaSucesso([
                'estatisticas' => $estatisticas,
                'top_performers' => $top_performers,
                'usuarios_ascensao' => $usuarios_ascensao,
                'competicoes_ativas' => $competicoes_ativas
            ], 'Estatísticas obtidas com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter estatísticas: ' . $e->getMessage());
        }
    }

    /**
     * Obter evolução do ranking de um usuário
     */
    public function getEvolucao($usuario_id, $params = []) {
        try {
            $usuario_id = validarId($usuario_id);
            $periodo = isset($params['periodo']) ? $params['periodo'] : 'semana';
            $limite = isset($params['limite']) ? (int) $params['limite'] : 30;

            // Validar período
            if (!Ranking::validarPeriodo($periodo)) {
                respostaErro('Período inválido');
                return;
            }

            if ($limite < 1 || $limite > 100) $limite = 30;

            $evolucao = $this->ranking->getEvolucaoRanking($usuario_id, $periodo, $limite);

            respostaSucesso([
                'usuario_id' => $usuario_id,
                'periodo' => $periodo,
                'periodo_nome' => Ranking::getPeriodosDisponiveis()[$periodo],
                'evolucao' => $evolucao,
                'limite' => $limite
            ], 'Evolução do ranking obtida com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter evolução do ranking: ' . $e->getMessage());
        }
    }

    /**
     * Obter top performers
     */
    public function getTopPerformers($params = []) {
        try {
            $limite = isset($params['limite']) ? (int) $params['limite'] : 5;

            if ($limite < 1 || $limite > 20) $limite = 5;

            $top_performers = $this->ranking->getTopPerformers($limite);

            respostaSucesso([
                'top_performers' => $top_performers,
                'limite' => $limite
            ], 'Top performers obtidos com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter top performers: ' . $e->getMessage());
        }
    }

    /**
     * Obter usuários em ascensão
     */
    public function getUsuariosAscensao($params = []) {
        try {
            $limite = isset($params['limite']) ? (int) $params['limite'] : 5;

            if ($limite < 1 || $limite > 20) $limite = 5;

            $usuarios_ascensao = $this->ranking->getUsuariosAscensao($limite);

            respostaSucesso([
                'usuarios_ascensao' => $usuarios_ascensao,
                'limite' => $limite
            ], 'Usuários em ascensão obtidos com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter usuários em ascensão: ' . $e->getMessage());
        }
    }

    /**
     * Obter competições ativas
     */
    public function getCompeticoesAtivas($params = []) {
        try {
            $competicoes = $this->ranking->getCompeticoesAtivas();

            respostaSucesso([
                'competicoes' => $competicoes,
                'total' => count($competicoes)
            ], 'Competições ativas obtidas com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter competições ativas: ' . $e->getMessage());
        }
    }

    /**
     * Salvar ranking histórico
     */
    public function salvarHistorico($params = []) {
        try {
            $periodo = isset($params['periodo']) ? $params['periodo'] : 'semana';

            // Validar período
            if (!Ranking::validarPeriodo($periodo)) {
                respostaErro('Período inválido');
                return;
            }

            $this->ranking->salvarRankingHistorico($periodo);

            respostaSucesso([
                'periodo' => $periodo,
                'periodo_nome' => Ranking::getPeriodosDisponiveis()[$periodo]
            ], 'Ranking histórico salvo com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao salvar ranking histórico: ' . $e->getMessage());
        }
    }

    /**
     * Obter total de usuários no ranking
     */
    public function getTotalUsuarios($params = []) {
        try {
            $busca = isset($params['busca']) ? $params['busca'] : null;

            $total = $this->ranking->getTotalUsuarios($busca);

            respostaSucesso([
                'total_usuarios' => $total,
                'busca' => $busca
            ], 'Total de usuários obtido com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter total de usuários: ' . $e->getMessage());
        }
    }
}
?>
