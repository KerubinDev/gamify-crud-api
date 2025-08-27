<?php
/**
 * Controlador de Conquistas/Badges
 * Sistema Vida Equilibrada
 */

require_once __DIR__ . '/../models/Badge.php';

class ConquistasController {
    private $db;
    private $badge;

    public function __construct($db) {
        $this->db = $db;
        $this->badge = new Badge($db);
    }

    /**
     * Listar todas as badges disponíveis
     */
    public function listar($params = []) {
        try {
            $tipo = isset($params['tipo']) ? $params['tipo'] : null;
            $badges = $this->badge->lerTodas();

            // Filtrar por tipo se especificado
            if ($tipo && Badge::validarTipo($tipo)) {
                $badges = array_filter($badges, function($badge) use ($tipo) {
                    return $badge['tipo'] === $tipo;
                });
            }

            // Formatar dados para resposta
            $badges_formatadas = [];
            foreach ($badges as $badge) {
                $badges_formatadas[] = [
                    'id' => $badge['id'],
                    'nome' => $badge['nome'],
                    'descricao' => $badge['descricao'],
                    'icone' => $badge['icone'],
                    'cor' => $badge['cor'],
                    'tipo' => $badge['tipo'],
                    'tipo_nome' => Badge::getTiposBadges()[$badge['tipo']] ?? $badge['tipo'],
                    'requisito_valor' => $badge['requisito_valor'],
                    'pontos_bonus' => $badge['pontos_bonus']
                ];
            }

            respostaSucesso([
                'badges' => $badges_formatadas,
                'total' => count($badges_formatadas),
                'tipos' => Badge::getTiposBadges(),
                'cores' => Badge::getCoresPadrao()
            ], 'Badges listadas com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao listar badges: ' . $e->getMessage());
        }
    }

    /**
     * Obter badge por ID
     */
    public function obter($id) {
        try {
            $id = validarId($id);

            if ($this->badge->lerPorId($id)) {
                $dados_badge = $this->badge->toArray();
                $dados_badge['tipo_nome'] = Badge::getTiposBadges()[$this->badge->tipo] ?? $this->badge->tipo;

                respostaSucesso($dados_badge, 'Badge encontrada');
            } else {
                respostaNaoEncontrado('Badge não encontrada');
            }

        } catch (Exception $e) {
            respostaErro('Erro ao obter badge: ' . $e->getMessage());
        }
    }

    /**
     * Obter badges de um usuário
     */
    public function obterPorUsuario($usuario_id) {
        try {
            $usuario_id = validarId($usuario_id);

            $badges_conquistadas = $this->badge->getBadgesUsuario($usuario_id);
            $badges_nao_conquistadas = $this->badge->getBadgesNaoConquistadas($usuario_id);
            $proximas_badges = $this->badge->getProximasBadges($usuario_id);
            $progresso = $this->badge->getProgressoBadges($usuario_id);

            // Formatar badges conquistadas
            $badges_conquistadas_formatadas = [];
            foreach ($badges_conquistadas as $badge) {
                $badges_conquistadas_formatadas[] = [
                    'id' => $badge['id'],
                    'nome' => $badge['nome'],
                    'descricao' => $badge['descricao'],
                    'icone' => $badge['icone'],
                    'cor' => $badge['cor'],
                    'tipo' => $badge['tipo'],
                    'tipo_nome' => Badge::getTiposBadges()[$badge['tipo']] ?? $badge['tipo'],
                    'requisito_valor' => $badge['requisito_valor'],
                    'pontos_bonus' => $badge['pontos_bonus'],
                    'data_conquista' => $badge['data_conquista'],
                    'pontos_ganhos' => $badge['pontos_ganhos']
                ];
            }

            // Formatar próximas badges
            $proximas_badges_formatadas = [];
            foreach ($proximas_badges as $badge) {
                $proximas_badges_formatadas[] = [
                    'id' => $badge['id'],
                    'nome' => $badge['nome'],
                    'descricao' => $badge['descricao'],
                    'icone' => $badge['icone'],
                    'cor' => $badge['cor'],
                    'tipo' => $badge['tipo'],
                    'tipo_nome' => Badge::getTiposBadges()[$badge['tipo']] ?? $badge['tipo'],
                    'requisito_valor' => $badge['requisito_valor'],
                    'pontos_bonus' => $badge['pontos_bonus']
                ];
            }

            respostaSucesso([
                'badges_conquistadas' => $badges_conquistadas_formatadas,
                'badges_nao_conquistadas' => $badges_nao_conquistadas,
                'proximas_badges' => $proximas_badges_formatadas,
                'progresso' => $progresso,
                'total_conquistadas' => count($badges_conquistadas_formatadas),
                'total_disponiveis' => count($badges_conquistadas_formatadas) + count($badges_nao_conquistadas),
                'pontos_badges' => $this->badge->getPontosBadgesUsuario($usuario_id)
            ], 'Badges do usuário obtidas com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter badges do usuário: ' . $e->getMessage());
        }
    }

    /**
     * Conceder badge a um usuário
     */
    public function conceder($badge_id, $data) {
        try {
            $badge_id = validarId($badge_id);

            // Validar dados obrigatórios
            validarDadosObrigatorios($data, ['usuario_id']);

            $usuario_id = (int) $data['usuario_id'];
            if ($usuario_id <= 0) {
                respostaErro('ID do usuário inválido');
                return;
            }

            // Conceder badge
            $resultado = $this->badge->concederBadge($usuario_id, $badge_id);

            if ($resultado['success']) {
                respostaSucesso($resultado, $resultado['message']);
            } else {
                respostaErro($resultado['message']);
            }

        } catch (Exception $e) {
            respostaErro('Erro ao conceder badge: ' . $e->getMessage());
        }
    }

    /**
     * Verificar se usuário possui uma badge
     */
    public function verificarPossui($badge_id, $data) {
        try {
            $badge_id = validarId($badge_id);

            // Validar dados obrigatórios
            validarDadosObrigatorios($data, ['usuario_id']);

            $usuario_id = (int) $data['usuario_id'];
            if ($usuario_id <= 0) {
                respostaErro('ID do usuário inválido');
                return;
            }

            $possui = $this->badge->usuarioPossuiBadge($usuario_id, $badge_id);

            respostaSucesso([
                'badge_id' => $badge_id,
                'usuario_id' => $usuario_id,
                'possui' => $possui
            ], 'Verificação realizada com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao verificar badge: ' . $e->getMessage());
        }
    }

    /**
     * Obter estatísticas de badges
     */
    public function getEstatisticas($params = []) {
        try {
            $usuario_id = isset($params['usuario_id']) ? (int) $params['usuario_id'] : null;

            $estatisticas = $this->badge->getEstatisticasBadges($usuario_id);
            $badges_populares = $this->badge->getBadgesMaisPopulares(5);
            $ranking_badges = $this->badge->getRankingBadges(10);

            respostaSucesso([
                'estatisticas' => $estatisticas,
                'badges_populares' => $badges_populares,
                'ranking_badges' => $ranking_badges
            ], 'Estatísticas obtidas com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter estatísticas: ' . $e->getMessage());
        }
    }

    /**
     * Obter progresso de badges de um usuário
     */
    public function getProgresso($usuario_id) {
        try {
            $usuario_id = validarId($usuario_id);

            $progresso = $this->badge->getProgressoBadges($usuario_id);
            $total_badges = $this->badge->getTotalBadgesUsuario($usuario_id);
            $pontos_badges = $this->badge->getPontosBadgesUsuario($usuario_id);

            respostaSucesso([
                'progresso' => $progresso,
                'total_badges' => $total_badges,
                'pontos_badges' => $pontos_badges
            ], 'Progresso obtido com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter progresso: ' . $e->getMessage());
        }
    }

    /**
     * Obter próximas badges a serem conquistadas
     */
    public function getProximas($usuario_id) {
        try {
            $usuario_id = validarId($usuario_id);

            $proximas_badges = $this->badge->getProximasBadges($usuario_id);

            // Formatar dados para resposta
            $proximas_formatadas = [];
            foreach ($proximas_badges as $badge) {
                $proximas_formatadas[] = [
                    'id' => $badge['id'],
                    'nome' => $badge['nome'],
                    'descricao' => $badge['descricao'],
                    'icone' => $badge['icone'],
                    'cor' => $badge['cor'],
                    'tipo' => $badge['tipo'],
                    'tipo_nome' => Badge::getTiposBadges()[$badge['tipo']] ?? $badge['tipo'],
                    'requisito_valor' => $badge['requisito_valor'],
                    'pontos_bonus' => $badge['pontos_bonus']
                ];
            }

            respostaSucesso([
                'proximas_badges' => $proximas_formatadas,
                'total' => count($proximas_formatadas)
            ], 'Próximas badges obtidas com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter próximas badges: ' . $e->getMessage());
        }
    }

    /**
     * Obter badges por tipo
     */
    public function getPorTipo($tipo) {
        try {
            // Validar tipo
            if (!Badge::validarTipo($tipo)) {
                respostaErro('Tipo de badge inválido');
                return;
            }

            $badges = $this->badge->lerTodas();

            // Filtrar por tipo
            $badges_tipo = array_filter($badges, function($badge) use ($tipo) {
                return $badge['tipo'] === $tipo;
            });

            // Formatar dados para resposta
            $badges_formatadas = [];
            foreach ($badges_tipo as $badge) {
                $badges_formatadas[] = [
                    'id' => $badge['id'],
                    'nome' => $badge['nome'],
                    'descricao' => $badge['descricao'],
                    'icone' => $badge['icone'],
                    'cor' => $badge['cor'],
                    'tipo' => $badge['tipo'],
                    'tipo_nome' => Badge::getTiposBadges()[$badge['tipo']],
                    'requisito_valor' => $badge['requisito_valor'],
                    'pontos_bonus' => $badge['pontos_bonus']
                ];
            }

            respostaSucesso([
                'tipo' => $tipo,
                'tipo_nome' => Badge::getTiposBadges()[$tipo],
                'badges' => $badges_formatadas,
                'total' => count($badges_formatadas)
            ], 'Badges do tipo obtidas com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter badges do tipo: ' . $e->getMessage());
        }
    }

    /**
     * Obter ranking de badges
     */
    public function getRanking($params = []) {
        try {
            $limite = isset($params['limite']) ? (int) $params['limite'] : 10;
            if ($limite < 1 || $limite > 50) $limite = 10;

            $ranking = $this->badge->getRankingBadges($limite);

            respostaSucesso([
                'ranking' => $ranking,
                'limite' => $limite
            ], 'Ranking de badges obtido com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter ranking de badges: ' . $e->getMessage());
        }
    }

    /**
     * Obter badges mais populares
     */
    public function getPopulares($params = []) {
        try {
            $limite = isset($params['limite']) ? (int) $params['limite'] : 5;
            if ($limite < 1 || $limite > 20) $limite = 5;

            $badges_populares = $this->badge->getBadgesMaisPopulares($limite);

            respostaSucesso([
                'badges_populares' => $badges_populares,
                'limite' => $limite
            ], 'Badges populares obtidas com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter badges populares: ' . $e->getMessage());
        }
    }

    /**
     * Verificar badges automáticas para um usuário
     */
    public function verificarAutomaticas($usuario_id) {
        try {
            $usuario_id = validarId($usuario_id);

            $badges_concedidas = $this->badge->verificarBadgesAutomaticas($usuario_id);

            respostaSucesso([
                'badges_concedidas' => $badges_concedidas,
                'total_concedidas' => count($badges_concedidas)
            ], 'Verificação automática realizada com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao verificar badges automáticas: ' . $e->getMessage());
        }
    }

    /**
     * Obter total de badges conquistadas
     */
    public function getTotalConquistadas($usuario_id) {
        try {
            $usuario_id = validarId($usuario_id);

            $total_badges = $this->badge->getTotalBadgesUsuario($usuario_id);
            $pontos_badges = $this->badge->getPontosBadgesUsuario($usuario_id);

            respostaSucesso([
                'total_badges' => $total_badges,
                'pontos_badges' => $pontos_badges
            ], 'Total de badges obtido com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter total de badges: ' . $e->getMessage());
        }
    }
}
?>
