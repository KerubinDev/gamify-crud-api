# 🚀 FIT BATTLE - Guia de Instalação

## 📋 Pré-requisitos

- **PHP**: 8.0 ou superior
- **MySQL**: 8.0 ou superior
- **Servidor Web**: Apache ou Nginx
- **Extensões PHP**: PDO, PDO_MySQL, JSON

## ⚡ Instalação Rápida

### 1. **Baixar o Projeto**
```bash
git clone [URL_DO_REPOSITORIO]
cd fit-battle
```

### 2. **Configurar o Banco de Dados**
- Acesse `install.php` no seu navegador
- Configure as credenciais do banco
- Clique em "Instalar FIT BATTLE"

### 3. **Acessar a Aplicação**
- Acesse `index.html` no seu navegador
- Pronto! 🎉

## 🔧 Instalação Manual

### 1. **Configurar Banco de Dados**
```sql
-- Criar banco de dados
CREATE DATABASE fit_battle CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Executar script SQL
mysql -u root -p fit_battle < database/fit_battle.sql
```

### 2. **Configurar Conexão**
Edite `api/config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'fit_battle');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
```

### 3. **Configurar Servidor Web**
- Apache: Habilitar mod_rewrite
- Nginx: Configurar rewrite rules

## 🧪 Testando a Instalação

### **Credenciais de Teste**
- **Email**: `admin@fitbattle.com`
- **Usuário**: `Renato Cariani`
- **Senha**: `password`

### **Funcionalidades para Testar**
1. ✅ Login/Registro
2. ✅ Visualização do ranking
3. ✅ Sistema de desafios
4. ✅ Navegação entre seções

## 🚨 Solução de Problemas

### **Erro de Conexão com Banco**
- Verificar credenciais em `api/config/database.php`
- Confirmar se MySQL está rodando
- Verificar se o banco `fit_battle` existe

### **Página em Branco**
- Verificar logs de erro do PHP
- Confirmar se todas as extensões estão habilitadas
- Verificar permissões de arquivo

### **CSS/JS não Carregando**
- Verificar se o servidor web está configurado corretamente
- Confirmar se os arquivos estão na pasta `assets/`

## 📁 Estrutura de Arquivos

```
fit-battle/
├── api/                    # Backend PHP
│   ├── config/            # Configurações
│   ├── controllers/       # Controladores
│   ├── models/            # Modelos
│   └── endpoints/         # Endpoints da API
├── assets/                # Frontend
│   ├── css/              # Estilos
│   ├── js/               # JavaScript
│   └── images/           # Imagens
├── database/              # Scripts SQL
├── docs/                  # Documentação
├── install.php            # Instalador automático
└── index.html             # Página principal
```

## 🔒 Configurações de Segurança

### **Produção**
- Alterar `JWT_SECRET` em `api/config/database.php`
- Configurar HTTPS
- Definir permissões de arquivo adequadas
- Configurar firewall

### **Desenvolvimento**
- Manter configurações padrão
- Usar banco local
- Habilitar exibição de erros

## 🚀 Próximos Passos

1. **Personalizar Design**
   - Editar `assets/css/style.css`
   - Modificar cores e estilos

2. **Adicionar Funcionalidades**
   - Implementar novos endpoints na API
   - Criar novas páginas

3. **Integração com Apps**
   - Desenvolver app mobile
   - Integrar com wearables

## 📞 Suporte

- **Issues**: Abrir no repositório
- **Documentação**: Ver pasta `docs/`
- **Email**: [seu-email@exemplo.com]

---

**🏃‍♂️💪 FIT BATTLE está pronto para dominar o mundo do fitness! ⚔️🏆**
