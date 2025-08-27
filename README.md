# 🏆 Vida Equilibrada - Sistema CRUD Gamificado

## 📖 Sobre o Projeto

**Vida Equilibrada** é uma plataforma gamificada que transforma hábitos saudáveis em uma jornada épica de autodesenvolvimento. Os usuários são "Guerreiros do Equilíbrio" que conquistam pontos, badges e rankings ao manterem hábitos saudáveis diários.

### 🎮 Tema e Lore

Em um mundo onde a tecnologia domina nossas vidas, surge o "Vida Equilibrada" - uma plataforma que transforma hábitos saudáveis em uma jornada épica de autodesenvolvimento. Os usuários são "Guerreiros do Equilíbrio" que conquistam pontos, badges e rankings ao manterem hábitos saudáveis diários.

## 🚀 Funcionalidades

### ✅ CRUD Completo
- **Usuários**: Cadastro, edição, exclusão e listagem
- **Hábitos**: Criação, edição, exclusão e acompanhamento
- **Conquistas**: Sistema de badges automático
- **Ranking**: Sistema de pontuação e competição

### 🎯 Sistema de Gamificação
- **Pontos**: Cada hábito completado gera pontos
- **Streaks**: Bônus por sequência de dias consecutivos
- **Níveis**: Sistema de progressão baseado em pontos
- **Badges**: Conquistas automáticas por metas atingidas
- **Ranking**: Competição entre usuários
- **Multiplicadores**: Bônus por horário de completamento

### 🔧 Tecnologias Utilizadas
- **Backend**: PHP 7.4+ com arquitetura MVC
- **Banco de Dados**: MySQL com stored procedures e triggers
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **API**: RESTful com autenticação JWT
- **Design**: Interface moderna e responsiva

## 📁 Estrutura do Projeto

```
gamify-crud-api/
├── api/                          # Backend PHP
│   ├── config/                   # Configurações
│   │   └── database.php          # Conexão e constantes
│   ├── controllers/              # Controladores
│   │   ├── UsuariosController.php
│   │   ├── HabitosController.php
│   │   ├── ConquistasController.php
│   │   ├── RankingController.php
│   │   └── AuthController.php
│   ├── models/                   # Modelos
│   │   ├── Usuario.php
│   │   ├── Habito.php
│   │   ├── Badge.php
│   │   └── Ranking.php
│   └── endpoints/                # Endpoints da API
│       ├── index.php             # Roteamento principal
│       ├── usuarios.php
│       ├── habitos.php
│       ├── conquistas.php
│       ├── ranking.php
│       ├── auth.php
│       └── estatisticas.php
├── assets/                       # Frontend
│   ├── css/
│   │   └── style.css             # Estilos principais
│   └── js/
│       ├── app.js                # App principal
│       ├── auth.js               # Autenticação
│       ├── habits.js             # Gerenciamento de hábitos
│       ├── ranking.js            # Sistema de ranking
│       ├── badges.js             # Sistema de conquistas
│       └── profile.js            # Perfil do usuário
├── database/
│   └── database.sql              # Esquema completo do banco
├── docs/                         # Documentação
│   └── INSTALACAO.md             # Guia de instalação
└── index.html                    # Página principal
```

## 🎮 Sistema de Pontuação

### Pontos Base
- **Hábito Completado**: 10 pontos
- **Bônus de Streak**: 
  - 3 dias: +25 pontos
  - 7 dias: +50 pontos
  - 30 dias: +100 pontos

### Multiplicadores
- **Madrugador** (5h-7h): 1.5x pontos
- **Noite** (22h-00h): 1.2x pontos
- **Horário Normal**: 1.0x pontos

### Níveis
- **Nível 1**: 0-99 pontos
- **Nível 2**: 100-199 pontos
- **Nível 3**: 200-299 pontos
- E assim por diante...

## 🏆 Sistema de Conquistas

### Badges Automáticas
- **Primeiro Passo**: Completar o primeiro hábito
- **Consistente**: Manter streak de 7 dias
- **Viciado**: Manter streak de 30 dias
- **Madrugador**: Completar hábitos entre 5h-7h
- **Noite**: Completar hábitos entre 22h-00h
- **Diversificado**: Completar hábitos de 3 categorias diferentes
- **Produtivo**: Completar 50 hábitos
- **Mestre**: Alcançar nível 10

## 🔌 API RESTful

### Endpoints Principais

#### Autenticação
- `POST /api/auth/register` - Registro de usuário
- `POST /api/auth/login` - Login
- `GET /api/auth/profile` - Perfil do usuário

#### Usuários
- `GET /api/usuarios` - Listar usuários
- `POST /api/usuarios` - Criar usuário
- `GET /api/usuarios/{id}` - Obter usuário
- `PUT /api/usuarios/{id}` - Atualizar usuário
- `DELETE /api/usuarios/{id}` - Deletar usuário

#### Hábitos
- `GET /api/habitos` - Listar hábitos
- `POST /api/habitos` - Criar hábito
- `GET /api/habitos/{id}` - Obter hábito
- `PUT /api/habitos/{id}` - Atualizar hábito
- `DELETE /api/habitos/{id}` - Deletar hábito
- `POST /api/habitos/{id}/completar` - Completar hábito

#### Conquistas
- `GET /api/conquistas` - Listar todas as conquistas
- `GET /api/conquistas/usuario/{id}` - Conquistas do usuário

#### Ranking
- `GET /api/ranking` - Ranking geral
- `GET /api/ranking?filtro=pontos` - Ranking por pontos
- `GET /api/ranking?filtro=badges` - Ranking por conquistas
- `GET /api/ranking?filtro=streak` - Ranking por streak

#### Estatísticas
- `GET /api/estatisticas` - Estatísticas gerais
- `GET /api/estatisticas/usuario/{id}` - Estatísticas do usuário

## 🛠️ Instalação

### Pré-requisitos
- PHP 7.4+
- MySQL 5.7+
- Servidor Web (Apache/Nginx)

### Passos
1. **Clone o repositório**
2. **Configure o banco de dados** (veja `docs/INSTALACAO.md`)
3. **Configure as credenciais** em `api/config/database.php`
4. **Acesse a aplicação** no navegador

Para instruções detalhadas, consulte o [Guia de Instalação](docs/INSTALACAO.md).

## 🎨 Interface do Usuário

### Dashboard
- Visão geral do progresso
- Estatísticas em tempo real
- Hábitos de hoje
- Top ranking
- Últimas conquistas

### Gerenciamento de Hábitos
- Criar novos hábitos
- Categorizar hábitos
- Definir frequência
- Completar hábitos
- Visualizar histórico

### Ranking
- Ranking geral
- Filtros por categoria
- Busca de usuários
- Estatísticas de competição

### Conquistas
- Badges disponíveis
- Progresso das conquistas
- Histórico de conquistas
- Próximas metas

## 🔒 Segurança

- **Autenticação JWT** para sessões seguras
- **Hash de senhas** com bcrypt
- **Validação de entrada** em todos os endpoints
- **Sanitização de dados** para prevenir SQL injection
- **Headers CORS** configurados adequadamente

## 📊 Banco de Dados

### Tabelas Principais
- `usuarios` - Dados dos usuários
- `habitos` - Hábitos criados
- `habitos_completados` - Histórico de completamentos
- `badges` - Conquistas disponíveis
- `badges_conquistadas` - Conquistas dos usuários
- `ranking_historico` - Histórico de rankings

### Recursos Avançados
- **Stored Procedures** para lógica de negócio
- **Triggers** para atualizações automáticas
- **Views** para consultas complexas
- **Índices** para performance

## 🚀 Funcionalidades Avançadas

### Gamificação Inteligente
- Cálculo automático de pontos
- Atribuição automática de badges
- Sistema de streaks inteligente
- Multiplicadores dinâmicos

### Analytics
- Estatísticas detalhadas
- Relatórios de progresso
- Análise de comportamento
- Métricas de engajamento

### Personalização
- Temas visuais
- Regras de gamificação configuráveis
- Categorias personalizáveis
- Badges customizáveis

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

## 📝 Licença

Este projeto está sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.

## 👥 Autores

- **Desenvolvedor**: [Seu Nome]
- **Professor**: [Nome do Professor]
- **Disciplina**: [Nome da Disciplina]
- **Instituição**: [Nome da Instituição]

## 📞 Suporte

Para suporte técnico ou dúvidas:
- Abra uma issue no repositório
- Consulte a documentação em `docs/`
- Entre em contato com o desenvolvedor

---

**Transforme seus hábitos em uma aventura épica! 🎮⚔️✨**
- **Rankings**: Listagem com filtros e busca

### 🎯 Sistema de Pontos
- **Hábito Completo**: +10 pontos
- **Streak de 3 dias**: +25 pontos bônus
- **Streak de 7 dias**: +50 pontos bônus
- **Streak de 30 dias**: +100 pontos bônus
- **Primeiro hábito do dia**: +5 pontos bônus

### 🏅 Sistema de Badges/Conquistas
- **🏃‍♂️ Iniciante**: Primeiro hábito completado
- **🔥 Em Chamas**: Streak de 3 dias
- **⚡ Velocista**: Streak de 7 dias
- **👑 Mestre**: Streak de 30 dias
- **💪 Disciplinado**: 10 hábitos completados
- **🌟 Estrela**: 50 hábitos completados
- **🏆 Lendário**: 100 hábitos completados
- **🌅 Madrugador**: Completar hábito antes das 8h
- **🌙 Noturno**: Completar hábito após 22h

### 📊 Ranking e Competição
- Ranking geral por pontos
- Ranking por badges conquistadas
- Ranking por streak atual
- Filtros por período (semana, mês, ano)
- Busca por nome de usuário

## 🛠️ Tecnologias Utilizadas

### Backend
- **PHP**: Linguagem principal
- **MySQL**: Banco de dados
- **PDO**: Conexão com banco
- **JSON**: Respostas da API

### Frontend
- **HTML5/CSS3**: Estrutura e estilo
- **JavaScript**: Interatividade
- **jQuery**: Manipulação DOM
- **Anime.js**: Animações
- **Font Awesome**: Ícones
- **Bootstrap**: Framework CSS

## 📁 Estrutura do Projeto

```
gamify-crud-api/
├── api/                    # Backend PHP
│   ├── config/            # Configurações
│   ├── controllers/       # Controladores
│   ├── models/           # Modelos
│   └── endpoints/        # Endpoints da API
├── assets/               # Recursos frontend
│   ├── css/             # Estilos
│   ├── js/              # JavaScript
│   └── images/          # Imagens
├── database/            # Scripts SQL
├── docs/               # Documentação
└── index.html          # Página principal
```

## 🚀 Como Executar

### Pré-requisitos
- XAMPP, Laragon ou servidor PHP local
- MySQL 5.7+
- Navegador moderno

### Instalação
1. Clone o repositório
2. Configure o banco de dados (veja `database/banco.sql`)
3. Configure as credenciais em `api/config/database.php`
4. Acesse via servidor local

### Configuração do Banco
```sql
-- Execute o script database/banco.sql
-- Configure as credenciais em api/config/database.php
```

## 📚 Documentação da API

### Endpoints Principais

#### Usuários
- `GET /api/usuarios` - Listar usuários
- `POST /api/usuarios` - Criar usuário
- `PUT /api/usuarios/{id}` - Atualizar usuário
- `DELETE /api/usuarios/{id}` - Deletar usuário

#### Hábitos
- `GET /api/habitos` - Listar hábitos
- `POST /api/habitos` - Criar hábito
- `PUT /api/habitos/{id}` - Atualizar hábito
- `DELETE /api/habitos/{id}` - Deletar hábito
- `POST /api/habitos/{id}/completar` - Completar hábito

#### Conquistas
- `GET /api/conquistas` - Listar conquistas
- `GET /api/conquistas/usuario/{id}` - Conquistas do usuário

#### Ranking
- `GET /api/ranking` - Ranking geral
- `GET /api/ranking?filtro=pontos` - Ranking por pontos
- `GET /api/ranking?filtro=badges` - Ranking por badges

## 🎨 Interface Gamificada

### Elementos Visuais
- **Animações**: Feedback visual ao completar hábitos
- **Badges**: Ícones coloridos para conquistas
- **Progress Bars**: Visualização de progresso
- **Ranking Cards**: Cards estilizados para rankings
- **Streak Counter**: Contador visual de sequências

### Feedback Gamificado
- **Sons**: Efeitos sonoros ao conquistar badges
- **Confetti**: Animação de confete ao atingir metas
- **Level Up**: Notificações de evolução
- **Achievement Popup**: Popups de conquista

## 📊 Sistema de Pontuação Detalhado

### Pontos Base
- Completar hábito: **10 pontos**
- Primeiro hábito do dia: **+5 pontos bônus**

### Bônus de Streak
- 3 dias consecutivos: **+25 pontos**
- 7 dias consecutivos: **+50 pontos**
- 30 dias consecutivos: **+100 pontos**

### Multiplicadores
- Hábito completado antes das 8h: **x1.5**
- Hábito completado após 22h: **x1.2**

## 🏆 Badges e Conquistas

### Badges de Streak
- 🔥 **Em Chamas**: 3 dias consecutivos
- ⚡ **Velocista**: 7 dias consecutivos
- 👑 **Mestre**: 30 dias consecutivos

### Badges de Quantidade
- 💪 **Disciplinado**: 10 hábitos
- 🌟 **Estrela**: 50 hábitos
- 🏆 **Lendário**: 100 hábitos

### Badges Especiais
- 🌅 **Madrugador**: Hábito antes das 8h
- 🌙 **Noturno**: Hábito após 22h
- 🎯 **Preciso**: 100% de acerto em uma semana

## 👥 Autores

- **Desenvolvido por**: [Seu Nome]
- **Data**: Agosto 2025
- **Projeto**: CRUD Gamificado - Vida Equilibrada

## 📝 Licença

Este projeto foi desenvolvido para fins educacionais como parte do curso de desenvolvimento web.

---

**🏆 Transforme seus hábitos em uma jornada épica!**