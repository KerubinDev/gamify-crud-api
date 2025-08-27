# 🚀 Guia de Instalação - Sistema Vida Equilibrada

## 📋 Pré-requisitos

Antes de instalar o sistema, certifique-se de ter os seguintes componentes instalados:

- **PHP 7.4 ou superior**
- **MySQL 5.7 ou superior** (ou MariaDB 10.2+)
- **Servidor Web** (Apache/Nginx) ou **XAMPP/Laragon**
- **Navegador moderno** (Chrome, Firefox, Safari, Edge)

## 🛠️ Instalação

### 1. Configuração do Banco de Dados

1. **Crie um banco de dados MySQL:**
   ```sql
   CREATE DATABASE vida_equilibrada;
   ```

2. **Importe o esquema do banco:**
   - Abra o arquivo `database/database.sql` no seu cliente MySQL
   - Execute o script completo para criar todas as tabelas, views, procedures e triggers

3. **Verifique se as tabelas foram criadas:**
   ```sql
   USE vida_equilibrada;
   SHOW TABLES;
   ```

### 2. Configuração do Backend

1. **Configure as credenciais do banco:**
   - Abra o arquivo `api/config/database.php`
   - Atualize as constantes de conexão:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'vida_equilibrada');
   define('DB_USER', 'seu_usuario');
   define('DB_PASS', 'sua_senha');
   ```

2. **Configure o servidor web:**
   - **XAMPP:** Coloque a pasta do projeto em `htdocs/`
   - **Laragon:** Coloque a pasta do projeto em `www/`
   - **Apache/Nginx:** Configure o DocumentRoot para apontar para a pasta do projeto

3. **Verifique as permissões:**
   - Certifique-se de que o PHP tem permissão de escrita na pasta do projeto
   - Configure as permissões adequadas para logs (se necessário)

### 3. Configuração do Frontend

1. **Acesse a aplicação:**
   - Abra seu navegador
   - Acesse: `http://localhost/gamify-crud-api/`

2. **Verifique se a API está funcionando:**
   - Acesse: `http://localhost/gamify-crud-api/api/`
   - Você deve ver a documentação da API

## 🔧 Configurações Avançadas

### Configurações de Segurança

1. **Altere a chave JWT:**
   ```php
   // Em api/config/database.php
   define('JWT_SECRET', 'sua_chave_secreta_muito_segura_aqui');
   ```

2. **Configure HTTPS (recomendado para produção):**
   - Instale um certificado SSL
   - Configure redirecionamento HTTPS

3. **Configure CORS (se necessário):**
   ```php
   // Em api/endpoints/index.php
   header('Access-Control-Allow-Origin: https://seu-dominio.com');
   ```

### Configurações de Gamificação

Você pode personalizar as regras de gamificação editando as constantes em `api/config/database.php`:

```php
// Pontos base por hábito completado
define('PONTOS_BASE_HABITO', 10);

// Bônus de streak
define('BONUS_STREAK_3', 25);
define('BONUS_STREAK_7', 50);
define('BONUS_STREAK_30', 100);

// Multiplicadores por horário
define('MULTIPLICADOR_MADRUGADOR', 1.5);
define('MULTIPLICADOR_NOITE', 1.2);

// Pontos por nível
define('PONTOS_POR_NIVEL', 100);
```

## 🧪 Testando a Instalação

### 1. Teste da API

1. **Teste de saúde da API:**
   ```
   GET http://localhost/gamify-crud-api/api/health
   ```

2. **Teste de registro de usuário:**
   ```json
   POST http://localhost/gamify-crud-api/api/auth/register
   {
     "nome": "Usuário Teste",
     "email": "teste@exemplo.com",
     "password": "123456"
   }
   ```

3. **Teste de login:**
   ```json
   POST http://localhost/gamify-crud-api/api/auth/login
   {
     "email": "teste@exemplo.com",
     "password": "123456"
   }
   ```

### 2. Teste do Frontend

1. **Acesse a aplicação web**
2. **Crie uma conta de teste**
3. **Faça login**
4. **Teste as funcionalidades:**
   - Criar hábitos
   - Completar hábitos
   - Ver ranking
   - Ver conquistas

## 🐛 Solução de Problemas

### Problemas Comuns

1. **Erro de conexão com banco:**
   - Verifique as credenciais em `api/config/database.php`
   - Certifique-se de que o MySQL está rodando
   - Verifique se o banco `vida_equilibrada` existe

2. **Erro 404 na API:**
   - Verifique se o mod_rewrite está habilitado (Apache)
   - Configure o .htaccess corretamente
   - Verifique as permissões dos arquivos

3. **Erro de CORS:**
   - Verifique se os headers CORS estão configurados
   - Certifique-se de que está acessando pelo mesmo domínio

4. **Erro de permissão:**
   - Verifique as permissões dos arquivos e pastas
   - Certifique-se de que o usuário do servidor web tem acesso

### Logs de Erro

- **PHP:** Verifique o log de erros do PHP
- **Apache/Nginx:** Verifique o log de erros do servidor web
- **MySQL:** Verifique o log de erros do MySQL

## 📚 Próximos Passos

Após a instalação bem-sucedida:

1. **Leia a documentação da API** em `docs/API.md`
2. **Explore as funcionalidades** do sistema
3. **Personalize o tema** e regras de gamificação
4. **Configure backups** do banco de dados
5. **Monitore o desempenho** da aplicação

## 🆘 Suporte

Se encontrar problemas durante a instalação:

1. Verifique se todos os pré-requisitos estão atendidos
2. Consulte a seção de solução de problemas
3. Verifique os logs de erro
4. Consulte a documentação completa do projeto

---

**Boa sorte em sua jornada de gamificação! 🎮✨**
