<?php
/**
 * Controlador de Estatísticas
 * Sistema Vida Equilibrada
 */

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Habito.php';
require_once __DIR__ . '/../models/Badge.php';
require_once __DIR__ . '/../models/Ranking.php';

class EstatisticasController {
    private $db;
    private $usuario;
    private $habito;
    private $badge;
    private $ranking;

    public function __construct($db) {
        $this->db = $db;
        $this->usuario = new Usuario($db);
        $this->habito = new Habito($db);
        $this->badge = new Badge($db);
        $this->ranking = new Ranking($db);
    }

    /**
     * Obter estatísticas gerais do sistema
     */
    public function obter($params = []) {
        try {
            $estatisticas_usuarios = $this->ranking->getEstatisticasRanking();
            $estatisticas_badges = $this->badge->getEstatisticasBadges();
            $top_performers = $this->ranking->getTopPerformers(5);
            $usuarios_ascensao = $this->ranking->getUsuariosAscensao(5);
            $competicoes_ativas = $this->ranking->getCompeticoesAtivas();

            respostaSucesso([
                'usuarios' => $estatisticas_usuarios,
                'badges' => $estatisticas_badges,
                'top_performers' => $top_performers,
                'usuarios_ascensao' => $usuarios_ascensao,
                'competicoes_ativas' => $competicoes_ativas,
                'sistema' => [
                    'total_usuarios' => $estatisticas_usuarios['total_usuarios'],
                    'media_pontos' => round($estatisticas_usuarios['media_pontos'], 2),
                    'max_pontos' => $estatisticas_usuarios['max_pontos'],
                    'media_streak' => round($estatisticas_usuarios['media_streak'], 2),
                    'max_streak' => $estatisticas_usuarios['max_streak'],
                    'media_habitos' => round($estatisticas_usuarios['media_habitos'], 2),
                    'max_habitos' => $estatisticas_usuarios['max_habitos']
                ]
            ], 'Estatísticas gerais obtidas com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter estatísticas gerais: ' . $e->getMessage());
        }
    }

    /**
     * Obter estatísticas de um usuário específico
     */
    public function obterPorUsuario($usuario_id) {
        try {
            $usuario_id = validarId($usuario_id);

            // Verificar se usuário existe
            if (!$this->usuario->lerPorId($usuario_id)) {
                respostaNaoEncontrado('Usuário não encontrado');
                return;
            }

            // Obter estatísticas do usuário
            $estatisticas_usuario = $this->usuario->getEstatisticas();
            $posicao_ranking = $this->usuario->getPosicaoRanking();
            $habitos_por_categoria = $this->habito->getHabitosPorCategoria($usuario_id);
            $habitos_mais_produtivos = $this->habito->getHabitosMaisProdutivos($usuario_id);
            $badges_conquistadas = $this->badge->getBadgesUsuario($usuario_id);
            $progresso_badges = $this->badge->getProgressoBadges($usuario_id);
            $proximas_badges = $this->badge->getProximasBadges($usuario_id);

            // Calcular estatísticas adicionais
            $nivel = $this->usuario->getNivel();
            $progresso_nivel = $this->usuario->getProgressoNivel();
            $pontos_badges = $this->badge->getPontosBadgesUsuario($usuario_id);

            respostaSucesso([
                'usuario' => [
                    'id' => $this->usuario->id,
                    'nome' => $this->usuario->nome,
                    'pontos' => $this->usuario->pontos,
                    'streak_atual' => $this->usuario->streak_atual,
                    'streak_maximo' => $this->usuario->streak_maximo,
                    'total_habitos' => $this->usuario->total_habitos,
                    'nivel' => $nivel,
                    'progresso_nivel' => $progresso_nivel
                ],
                'estatisticas' => $estatisticas_usuario,
                'ranking' => [
                    'posicao' => $posicao_ranking
                ],
                'habitos' => [
                    'por_categoria' => $habitos_por_categoria,
                    'mais_produtivos' => $habitos_mais_produtivos
                ],
                'badges' => [
                    'conquistadas' => $badges_conquistadas,
                    'progresso' => $progresso_badges,
                    'proximas' => $proximas_badges,
                    'total_conquistadas' => count($badges_conquistadas),
                    'pontos_badges' => $pontos_badges
                ]
            ], 'Estatísticas do usuário obtidas com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter estatísticas do usuário: ' . $e->getMessage());
        }
    }

    /**
     * Obter estatísticas de hábitos
     */
    public function getEstatisticasHabitos($params = []) {
        try {
            $usuario_id = isset($params['usuario_id']) ? (int) $params['usuario_id'] : null;
            $categoria = isset($params['categoria']) ? $params['categoria'] : null;

            if ($usuario_id) {
                // Estatísticas de um usuário específico
                if (!$this->usuario->lerPorId($usuario_id)) {
                    respostaNaoEncontrado('Usuário não encontrado');
                    return;
                }

                $habitos_por_categoria = $this->habito->getHabitosPorCategoria($usuario_id);
                $habitos_mais_produtivos = $this->habito->getHabitosMaisProdutivos($usuario_id);

                respostaSucesso([
                    'usuario_id' => $usuario_id,
                    'habitos_por_categoria' => $habitos_por_categoria,
                    'habitos_mais_produtivos' => $habitos_mais_produtivos,
                    'categorias' => Habito::getCategorias()
                ], 'Estatísticas de hábitos do usuário obtidas com sucesso');
            } else {
                // Estatísticas gerais de hábitos
                respostaSucesso([
                    'categorias' => Habito::getCategorias(),
                    'frequencias' => Habito::getFrequencias(),
                    'mensagem' => 'Para estatísticas específicas, forneça o usuario_id'
                ], 'Categorias de hábitos obtidas com sucesso');
            }

        } catch (Exception $e) {
            respostaErro('Erro ao obter estatísticas de hábitos: ' . $e->getMessage());
        }
    }

    /**
     * Obter estatísticas de badges
     */
    public function getEstatisticasBadges($params = []) {
        try {
            $usuario_id = isset($params['usuario_id']) ? (int) $params['usuario_id'] : null;

            if ($usuario_id) {
                // Estatísticas de um usuário específico
                if (!$this->usuario->lerPorId($usuario_id)) {
                    respostaNaoEncontrado('Usuário não encontrado');
                    return;
                }

                $badges_conquistadas = $this->badge->getBadgesUsuario($usuario_id);
                $progresso_badges = $this->badge->getProgressoBadges($usuario_id);
                $proximas_badges = $this->badge->getProximasBadges($usuario_id);
                $total_badges = $this->badge->getTotalBadgesUsuario($usuario_id);
                $pontos_badges = $this->badge->getPontosBadgesUsuario($usuario_id);

                respostaSucesso([
                    'usuario_id' => $usuario_id,
                    'badges_conquistadas' => $badges_conquistadas,
                    'progresso' => $progresso_badges,
                    'proximas_badges' => $proximas_badges,
                    'total_conquistadas' => $total_badges,
                    'pontos_badges' => $pontos_badges
                ], 'Estatísticas de badges do usuário obtidas com sucesso');
            } else {
                // Estatísticas gerais de badges
                $estatisticas_badges = $this->badge->getEstatisticasBadges();
                $badges_populares = $this->badge->getBadgesMaisPopulares(5);
                $ranking_badges = $this->badge->getRankingBadges(10);

                respostaSucesso([
                    'estatisticas' => $estatisticas_badges,
                    'badges_populares' => $badges_populares,
                    'ranking_badges' => $ranking_badges,
                    'tipos' => Badge::getTiposBadges()
                ], 'Estatísticas gerais de badges obtidas com sucesso');
            }

        } catch (Exception $e) {
            respostaErro('Erro ao obter estatísticas de badges: ' . $e->getMessage());
        }
    }

    /**
     * Obter estatísticas de ranking
     */
    public function getEstatisticasRanking($params = []) {
        try {
            $estatisticas_ranking = $this->ranking->getEstatisticasRanking();
            $top_performers = $this->ranking->getTopPerformers(5);
            $usuarios_ascensao = $this->ranking->getUsuariosAscensao(5);
            $competicoes_ativas = $this->ranking->getCompeticoesAtivas();

            respostaSucesso([
                'estatisticas' => $estatisticas_ranking,
                'top_performers' => $top_performers,
                'usuarios_ascensao' => $usuarios_ascensao,
                'competicoes_ativas' => $competicoes_ativas,
                'filtros_disponiveis' => Ranking::getFiltrosDisponiveis(),
                'periodos_disponiveis' => Ranking::getPeriodosDisponiveis()
            ], 'Estatísticas de ranking obtidas com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter estatísticas de ranking: ' . $e->getMessage());
        }
    }

    /**
     * Obter dashboard completo
     */
    public function getDashboard($params = []) {
        try {
            $usuario_id = isset($params['usuario_id']) ? (int) $params['usuario_id'] : null;

            $dashboard = [];

            // Estatísticas gerais
            $estatisticas_gerais = $this->ranking->getEstatisticasRanking();
            $dashboard['geral'] = [
                'total_usuarios' => $estatisticas_gerais['total_usuarios'],
                'media_pontos' => round($estatisticas_gerais['media_pontos'], 2),
                'max_pontos' => $estatisticas_gerais['max_pontos'],
                'media_streak' => round($estatisticas_gerais['media_streak'], 2),
                'max_streak' => $estatisticas_gerais['max_streak']
            ];

            // Top performers
            $dashboard['top_performers'] = $this->ranking->getTopPerformers(3);

            // Usuários em ascensão
            $dashboard['usuarios_ascensao'] = $this->ranking->getUsuariosAscensao(3);

            // Competições ativas
            $dashboard['competicoes_ativas'] = $this->ranking->getCompeticoesAtivas();

            // Badges populares
            $dashboard['badges_populares'] = $this->badge->getBadgesMaisPopulares(3);

            // Se usuário específico fornecido
            if ($usuario_id) {
                if ($this->usuario->lerPorId($usuario_id)) {
                    $dashboard['usuario'] = [
                        'id' => $this->usuario->id,
                        'nome' => $this->usuario->nome,
                        'pontos' => $this->usuario->pontos,
                        'streak_atual' => $this->usuario->streak_atual,
                        'nivel' => $this->usuario->getNivel(),
                        'progresso_nivel' => $this->usuario->getProgressoNivel(),
                        'posicao_ranking' => $this->usuario->getPosicaoRanking(),
                        'total_badges' => $this->badge->getTotalBadgesUsuario($usuario_id)
                    ];
                }
            }

            respostaSucesso($dashboard, 'Dashboard obtido com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Obter relatório de atividade
     */
    public function getRelatorioAtividade($usuario_id, $params = []) {
        try {
            $usuario_id = validarId($usuario_id);

            // Verificar se usuário existe
            if (!$this->usuario->lerPorId($usuario_id)) {
                respostaNaoEncontrado('Usuário não encontrado');
                return;
            }

            $periodo = isset($params['periodo']) ? $params['periodo'] : 'semana';
            $limite = isset($params['limite']) ? (int) $params['limite'] : 30;

            // Obter dados do relatório
            $estatisticas_usuario = $this->usuario->getEstatisticas();
            $evolucao_ranking = $this->ranking->getEvolucaoRanking($usuario_id, $periodo, $limite);
            $habitos_mais_produtivos = $this->habito->getHabitosMaisProdutivos($usuario_id, 5);
            $badges_conquistadas = $this->badge->getBadgesUsuario($usuario_id);

            respostaSucesso([
                'usuario' => [
                    'id' => $this->usuario->id,
                    'nome' => $this->usuario->nome,
                    'pontos' => $this->usuario->pontos,
                    'streak_atual' => $this->usuario->streak_atual,
                    'nivel' => $this->usuario->getNivel()
                ],
                'estatisticas' => $estatisticas_usuario,
                'evolucao_ranking' => $evolucao_ranking,
                'habitos_mais_produtivos' => $habitos_mais_produtivos,
                'badges_conquistadas' => $badges_conquistadas,
                'periodo' => $periodo,
                'limite' => $limite
            ], 'Relatório de atividade obtido com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter relatório de atividade: ' . $e->getMessage());
        }
    }

    /**
     * Obter métricas de gamificação
     */
    public function getMetricasGamificacao($params = []) {
        try {
            $usuario_id = isset($params['usuario_id']) ? (int) $params['usuario_id'] : null;

            $metricas = [];

            if ($usuario_id) {
                // Métricas de um usuário específico
                if (!$this->usuario->lerPorId($usuario_id)) {
                    respostaNaoEncontrado('Usuário não encontrado');
                    return;
                }

                $nivel = $this->usuario->getNivel();
                $progresso_nivel = $this->usuario->getProgressoNivel();
                $total_badges = $this->badge->getTotalBadgesUsuario($usuario_id);
                $pontos_badges = $this->badge->getPontosBadgesUsuario($usuario_id);
                $posicao_ranking = $this->usuario->getPosicaoRanking();

                $metricas = [
                    'usuario_id' => $usuario_id,
                    'nivel' => $nivel,
                    'progresso_nivel' => $progresso_nivel,
                    'pontos_para_proximo_nivel' => PONTOS_POR_NIVEL - ($this->usuario->pontos % PONTOS_POR_NIVEL),
                    'total_badges' => $total_badges,
                    'pontos_badges' => $pontos_badges,
                    'posicao_ranking' => $posicao_ranking,
                    'streak_atual' => $this->usuario->streak_atual,
                    'streak_maximo' => $this->usuario->streak_maximo
                ];
            } else {
                // Métricas gerais do sistema
                $estatisticas = $this->ranking->getEstatisticasRanking();
                
                $metricas = [
                    'total_usuarios' => $estatisticas['total_usuarios'],
                    'media_nivel' => round($estatisticas['media_pontos'] / PONTOS_POR_NIVEL, 2),
                    'max_nivel' => floor($estatisticas['max_pontos'] / PONTOS_POR_NIVEL),
                    'media_streak' => round($estatisticas['media_streak'], 2),
                    'max_streak' => $estatisticas['max_streak'],
                    'pontos_por_nivel' => PONTOS_POR_NIVEL
                ];
            }

            respostaSucesso($metricas, 'Métricas de gamificação obtidas com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter métricas de gamificação: ' . $e->getMessage());
        }
    }
}
?>
