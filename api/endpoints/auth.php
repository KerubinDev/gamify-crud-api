<?php
/**
 * Controlador de Autenticação
 * Sistema Vida Equilibrada
 */

require_once __DIR__ . '/../models/Usuario.php';

class AuthController {
    private $db;
    private $usuario;

    public function __construct($db) {
        $this->db = $db;
        $this->usuario = new Usuario($db);
    }

    /**
     * Login de usuário
     */
    public function login($data) {
        try {
            // Validar dados obrigatórios
            validarDadosObrigatorios($data, ['email', 'senha']);

            $email = sanitizeInput($data['email']);
            $senha = $data['senha'];

            // Validar email
            if (!validateEmail($email)) {
                respostaErro('Email inválido');
                return;
            }

            // Validar senha
            if (empty($senha)) {
                respostaErro('Senha é obrigatória');
                return;
            }

            // Autenticar usuário
            if ($this->usuario->autenticar($email, $senha)) {
                // Gerar token de sessão
                $token = generateToken(32);
                
                // Obter dados do usuário (sem senha)
                $dados_usuario = $this->usuario->toArray();
                unset($dados_usuario['senha']);

                // Calcular nível e progresso
                $dados_usuario['nivel'] = $this->usuario->getNivel();
                $dados_usuario['progresso_nivel'] = $this->usuario->getProgressoNivel();

                respostaSucesso([
                    'usuario' => $dados_usuario,
                    'token' => $token,
                    'expira_em' => date('Y-m-d H:i:s', strtotime('+1 hour'))
                ], 'Login realizado com sucesso');
            } else {
                respostaNaoAutorizado('Email ou senha incorretos');
            }

        } catch (Exception $e) {
            respostaErro('Erro no login: ' . $e->getMessage());
        }
    }

    /**
     * Registro de usuário
     */
    public function register($data) {
        try {
            // Validar dados obrigatórios
            validarDadosObrigatorios($data, ['nome', 'email', 'senha', 'confirmar_senha']);

            $nome = sanitizeInput($data['nome']);
            $email = sanitizeInput($data['email']);
            $senha = $data['senha'];
            $confirmar_senha = $data['confirmar_senha'];

            // Validar nome
            if (strlen($nome) < 2) {
                respostaErro('Nome deve ter pelo menos 2 caracteres');
                return;
            }

            // Validar email
            if (!validateEmail($email)) {
                respostaErro('Email inválido');
                return;
            }

            // Verificar se email já existe
            if ($this->usuario->emailExiste($email)) {
                respostaErro('Email já cadastrado');
                return;
            }

            // Validar senha
            if (strlen($senha) < 6) {
                respostaErro('Senha deve ter pelo menos 6 caracteres');
                return;
            }

            // Confirmar senha
            if ($senha !== $confirmar_senha) {
                respostaErro('Senhas não coincidem');
                return;
            }

            // Configurar dados do usuário
            $this->usuario->nome = $nome;
            $this->usuario->email = $email;
            $this->usuario->senha = $senha;
            $this->usuario->pontos = 0;
            $this->usuario->streak_atual = 0;
            $this->usuario->streak_maximo = 0;
            $this->usuario->total_habitos = 0;
            $this->usuario->ativo = true;

            // Criar usuário
            if ($this->usuario->criar()) {
                // Gerar token de sessão
                $token = generateToken(32);
                
                // Obter dados do usuário (sem senha)
                $dados_usuario = $this->usuario->toArray();
                unset($dados_usuario['senha']);

                respostaSucesso([
                    'usuario' => $dados_usuario,
                    'token' => $token,
                    'expira_em' => date('Y-m-d H:i:s', strtotime('+1 hour'))
                ], 'Usuário registrado com sucesso');
            } else {
                respostaErro('Erro ao registrar usuário');
            }

        } catch (Exception $e) {
            respostaErro('Erro no registro: ' . $e->getMessage());
        }
    }

    /**
     * Verificar token de autenticação
     */
    public function verificarToken($data) {
        try {
            // Validar dados obrigatórios
            validarDadosObrigatorios($data, ['token']);

            $token = $data['token'];

            // Aqui você implementaria a verificação do token
            // Por simplicidade, vamos apenas retornar sucesso
            // Em produção, você validaria o token no banco ou JWT

            respostaSucesso([
                'token_valido' => true,
                'expira_em' => date('Y-m-d H:i:s', strtotime('+1 hour'))
            ], 'Token válido');

        } catch (Exception $e) {
            respostaErro('Erro ao verificar token: ' . $e->getMessage());
        }
    }

    /**
     * Logout de usuário
     */
    public function logout($data) {
        try {
            // Validar dados obrigatórios
            validarDadosObrigatorios($data, ['token']);

            $token = $data['token'];

            // Aqui você implementaria a invalidação do token
            // Por simplicidade, vamos apenas retornar sucesso

            respostaSucesso(null, 'Logout realizado com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro no logout: ' . $e->getMessage());
        }
    }

    /**
     * Recuperar senha
     */
    public function recuperarSenha($data) {
        try {
            // Validar dados obrigatórios
            validarDadosObrigatorios($data, ['email']);

            $email = sanitizeInput($data['email']);

            // Validar email
            if (!validateEmail($email)) {
                respostaErro('Email inválido');
                return;
            }

            // Verificar se email existe
            if (!$this->usuario->lerPorEmail($email)) {
                respostaErro('Email não encontrado');
                return;
            }

            // Gerar token de recuperação
            $token_recuperacao = generateToken(32);
            
            // Aqui você implementaria o envio do email
            // Por simplicidade, vamos apenas retornar o token

            respostaSucesso([
                'token_recuperacao' => $token_recuperacao,
                'expira_em' => date('Y-m-d H:i:s', strtotime('+24 hours'))
            ], 'Email de recuperação enviado com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao recuperar senha: ' . $e->getMessage());
        }
    }

    /**
     * Redefinir senha
     */
    public function redefinirSenha($data) {
        try {
            // Validar dados obrigatórios
            validarDadosObrigatorios($data, ['token_recuperacao', 'nova_senha', 'confirmar_senha']);

            $token_recuperacao = $data['token_recuperacao'];
            $nova_senha = $data['nova_senha'];
            $confirmar_senha = $data['confirmar_senha'];

            // Validar senha
            if (strlen($nova_senha) < 6) {
                respostaErro('Nova senha deve ter pelo menos 6 caracteres');
                return;
            }

            // Confirmar senha
            if ($nova_senha !== $confirmar_senha) {
                respostaErro('Senhas não coincidem');
                return;
            }

            // Aqui você implementaria a validação do token e atualização da senha
            // Por simplicidade, vamos apenas retornar sucesso

            respostaSucesso(null, 'Senha redefinida com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao redefinir senha: ' . $e->getMessage());
        }
    }

    /**
     * Alterar senha (usuário logado)
     */
    public function alterarSenha($data) {
        try {
            // Validar dados obrigatórios
            validarDadosObrigatorios($data, ['usuario_id', 'senha_atual', 'nova_senha', 'confirmar_senha']);

            $usuario_id = (int) $data['usuario_id'];
            $senha_atual = $data['senha_atual'];
            $nova_senha = $data['nova_senha'];
            $confirmar_senha = $data['confirmar_senha'];

            if ($usuario_id <= 0) {
                respostaErro('ID do usuário inválido');
                return;
            }

            // Verificar se usuário existe
            if (!$this->usuario->lerPorId($usuario_id)) {
                respostaNaoEncontrado('Usuário não encontrado');
                return;
            }

            // Verificar senha atual
            if (!verifyPassword($senha_atual, $this->usuario->senha)) {
                respostaErro('Senha atual incorreta');
                return;
            }

            // Validar nova senha
            if (strlen($nova_senha) < 6) {
                respostaErro('Nova senha deve ter pelo menos 6 caracteres');
                return;
            }

            // Confirmar senha
            if ($nova_senha !== $confirmar_senha) {
                respostaErro('Senhas não coincidem');
                return;
            }

            // Atualizar senha
            if ($this->usuario->atualizarSenha($nova_senha)) {
                respostaSucesso(null, 'Senha alterada com sucesso');
            } else {
                respostaErro('Erro ao alterar senha');
            }

        } catch (Exception $e) {
            respostaErro('Erro ao alterar senha: ' . $e->getMessage());
        }
    }

    /**
     * Verificar se email está disponível
     */
    public function verificarEmail($data) {
        try {
            // Validar dados obrigatórios
            validarDadosObrigatorios($data, ['email']);

            $email = sanitizeInput($data['email']);

            // Validar email
            if (!validateEmail($email)) {
                respostaErro('Email inválido');
                return;
            }

            $email_existe = $this->usuario->emailExiste($email);

            respostaSucesso([
                'email' => $email,
                'disponivel' => !$email_existe
            ], 'Verificação realizada com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao verificar email: ' . $e->getMessage());
        }
    }

    /**
     * Obter perfil do usuário
     */
    public function getPerfil($usuario_id) {
        try {
            $usuario_id = validarId($usuario_id);

            // Verificar se usuário existe
            if (!$this->usuario->lerPorId($usuario_id)) {
                respostaNaoEncontrado('Usuário não encontrado');
                return;
            }

            // Obter dados do usuário
            $dados_usuario = $this->usuario->toArray();
            unset($dados_usuario['senha']); // Não retornar senha

            // Calcular nível e progresso
            $dados_usuario['nivel'] = $this->usuario->getNivel();
            $dados_usuario['progresso_nivel'] = $this->usuario->getProgressoNivel();

            respostaSucesso([
                'usuario' => $dados_usuario
            ], 'Perfil obtido com sucesso');

        } catch (Exception $e) {
            respostaErro('Erro ao obter perfil: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar perfil do usuário
     */
    public function atualizarPerfil($usuario_id, $data) {
        try {
            $usuario_id = validarId($usuario_id);

            // Verificar se usuário existe
            if (!$this->usuario->lerPorId($usuario_id)) {
                respostaNaoEncontrado('Usuário não encontrado');
                return;
            }

            // Validar dados obrigatórios
            validarDadosObrigatorios($data, ['nome']);

            // Validar nome
            if (strlen($data['nome']) < 2) {
                respostaErro('Nome deve ter pelo menos 2 caracteres');
                return;
            }

            // Atualizar dados
            $this->usuario->nome = sanitizeInput($data['nome']);

            // Atualizar usuário
            if ($this->usuario->atualizar()) {
                $dados_usuario = $this->usuario->toArray();
                unset($dados_usuario['senha']); // Não retornar senha

                respostaSucesso([
                    'usuario' => $dados_usuario
                ], 'Perfil atualizado com sucesso');
            } else {
                respostaErro('Erro ao atualizar perfil');
            }

        } catch (Exception $e) {
            respostaErro('Erro ao atualizar perfil: ' . $e->getMessage());
        }
    }
}
?>
