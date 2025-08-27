# 🚀 Instalação Super Rápida - Vida Equilibrada

## Para quem tem preguiça! 😄

### Opção 1: Instalação Automática (Recomendada)

1. **Abra o terminal/prompt** na pasta do projeto
2. **Execute o comando:**
   ```bash
   php install_quick.php
   ```
3. **Pronto!** Acesse: `http://localhost/gamify-crud-api`

### Opção 2: Instalação com Interface

1. **Abra no navegador:**
   ```
   http://localhost/gamify-crud-api/install.php
   ```
2. **Preencha os dados** (ou use os padrões)
3. **Clique em "Iniciar Instalação"**
4. **Pronto!** Acesse: `http://localhost/gamify-crud-api`

### Opção 3: Instalação Manual (Para quem gosta de sofrer)

1. **Crie o banco:**
   ```sql
   CREATE DATABASE vida_equilibrada;
   ```

2. **Importe o esquema:**
   - Abra `database/database.sql`
   - Execute no MySQL

3. **Configure a conexão:**
   - Edite `api/config/database.php`
   - Ajuste as credenciais

4. **Pronto!** Acesse: `http://localhost/gamify-crud-api`

## 🎯 Pré-requisitos

- ✅ **XAMPP** ou **Laragon** (recomendado)
- ✅ **PHP 7.4+**
- ✅ **MySQL 5.7+**
- ✅ **Navegador moderno**

## 🔧 Configurações Padrão

- **Host:** localhost
- **Banco:** vida_equilibrada
- **Usuário:** root
- **Senha:** (vazia)

## 🎮 Após a Instalação

1. **Acesse a aplicação:** `http://localhost/gamify-crud-api`
2. **Crie uma conta** de usuário
3. **Comece sua jornada** de gamificação!

## 🆘 Problemas?

### Erro de conexão com MySQL:
- Verifique se o MySQL está rodando
- Use XAMPP/Laragon para facilitar

### Erro 404:
- Verifique se o mod_rewrite está habilitado
- Configure o .htaccess corretamente

### Erro de permissão:
- Verifique as permissões dos arquivos
- Certifique-se de que o usuário do servidor web tem acesso

## 📱 URLs Importantes

- **Aplicação:** `http://localhost/gamify-crud-api/`
- **API:** `http://localhost/gamify-crud-api/api/`
- **Instalação:** `http://localhost/gamify-crud-api/install.php`

## ✨ Dicas

- Use **XAMPP** ou **Laragon** para facilitar
- O sistema funciona melhor no **Chrome** ou **Firefox**
- Para produção, configure **HTTPS**

---

**Boa sorte em sua jornada de gamificação! 🎮⚔️🏆**

