# 🏃‍♂️💪 FIT BATTLE - A GUERRA PELA SAÚDE!

## 🎯 **SOBRE O PROJETO**

**FIT BATTLE** é um app revolucionário de competição fitness onde pessoas competem para ser a pessoa mais saudável! Não é só sobre exercícios - é sobre **DOMINAR O RANKING** e provar que você é o **REI/RAINHA DA SAÚDE**! 👑

### 🌟 **CONCEITO INOVADOR**
- **Competição social real** - veja seus amigos, família e desconhecidos
- **Ranking em tempo real** - quem está na frente?
- **Sistema de desafios** - desafie qualquer pessoa para uma batalha fitness
- **Ligas e torneios** - participe de competições organizadas
- **Gamificação épica** - níveis, badges, títulos e conquistas

## 🚀 **FUNCIONALIDADES PRINCIPAIS**

### 🏆 **SISTEMA DE COMPETIÇÃO**
- **Ranking Global** - veja quem é o mais saudável do mundo
- **Ranking por Categoria** - corrida, academia, yoga, etc.
- **Ranking por Região** - competa com pessoas próximas
- **Ranking por Idade** - desafios justos por faixa etária

### ⚔️ **SISTEMA DE DESAFIOS**
- **Desafios 1v1** - desafie qualquer pessoa
- **Desafios em Grupo** - crie ou participe de ligas
- **Torneios** - competições organizadas com prêmios
- **Apostas** - aposte pontos em desafios

### 🎮 **GAMIFICAÇÃO**
- **Níveis**: Iniciante → Amador → Profissional → Elite → Lendário
- **Pontos de Saúde**: Sistema inteligente de pontuação
- **Streaks**: Sequências de dias exercitando
- **Badges**: Conquistas por metas atingidas
- **Títulos**: "Rei da Corrida", "Rainha da Academia"

### 📊 **EXERCÍCIOS SUPORTADOS**
- **Cardio**: Corrida, Ciclismo, Natação, HIIT
- **Força**: Academia, Calistenia, CrossFit
- **Flexibilidade**: Yoga, Pilates, Alongamento
- **Esportes**: Futebol, Basquete, Tênis, etc.
- **Atividades Diárias**: Caminhada, Escadas, etc.

## 🛠️ **TECNOLOGIAS**

- **Backend**: PHP 8.0+ com arquitetura MVC
- **Banco de Dados**: MySQL 8.0+ com stored procedures
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **API**: RESTful com autenticação JWT
- **Design**: Interface moderna de app fitness

## 📁 **ESTRUTURA DO PROJETO**

```
fit-battle/
├── api/                          # Backend PHP
│   ├── config/                   # Configurações
│   ├── controllers/              # Controladores
│   ├── models/                   # Modelos
│   └── endpoints/                # Endpoints da API
├── assets/                       # Frontend
│   ├── css/                      # Estilos
│   ├── js/                       # JavaScript
│   └── images/                   # Imagens
├── database/                     # Scripts SQL
├── docs/                         # Documentação
└── index.html                    # Página principal
```

## 🎯 **SISTEMA DE PONTUAÇÃO**

### 📊 **PONTOS BASE POR EXERCÍCIO**
- **Corrida**: 10 pontos/km + bônus por velocidade
- **Academia**: 15 pontos/série + bônus por peso
- **Yoga**: 8 pontos/sessão + bônus por duração
- **HIIT**: 20 pontos/sessão + bônus por intensidade

### 🚀 **BÔNUS E MULTIPLICADORES**
- **Streak de 3 dias**: +25 pontos
- **Streak de 7 dias**: +50 pontos
- **Streak de 30 dias**: +100 pontos
- **Primeiro exercício do dia**: +10 pontos
- **Exercício antes das 8h**: x1.5 pontos
- **Exercício após 22h**: x1.2 pontos

### 🏅 **NÍVEIS E PROGRESSÃO**
- **Nível 1**: 0-99 pontos (Iniciante)
- **Nível 5**: 500-599 pontos (Amador)
- **Nível 10**: 1000-1099 pontos (Profissional)
- **Nível 20**: 2000-2099 pontos (Elite)
- **Nível 50**: 5000+ pontos (Lendário)

## 🏆 **SISTEMA DE CONQUISTAS**

### 🎯 **BADGES DE STREAK**
- 🔥 **Em Chamas**: 3 dias consecutivos
- ⚡ **Velocista**: 7 dias consecutivos
- 👑 **Mestre**: 30 dias consecutivos
- 🚀 **Lendário**: 100 dias consecutivos

### 🏃‍♂️ **BADGES DE EXERCÍCIO**
- 🏃 **Corredor**: Completar 10 corridas
- 💪 **Musculoso**: Completar 50 sessões de academia
- 🧘 **Zen**: Completar 20 sessões de yoga
- 🏆 **Atleta**: Completar 100 exercícios

### 🌟 **BADGES ESPECIAIS**
- 🌅 **Madrugador**: Exercício antes das 8h
- 🌙 **Noturno**: Exercício após 22h
- 🎯 **Preciso**: 100% de consistência em uma semana
- 🚀 **Rocket**: Atingir 3 níveis em um mês

## 🔌 **API RESTFUL**

### 📍 **ENDPOINTS PRINCIPAIS**

#### 👤 **Usuários**
- `POST /api/auth/register` - Registro
- `POST /api/auth/login` - Login
- `GET /api/users/profile` - Perfil
- `PUT /api/users/profile` - Atualizar perfil

#### 🏃‍♂️ **Exercícios**
- `GET /api/exercises` - Listar exercícios
- `POST /api/exercises` - Registrar exercício
- `PUT /api/exercises/{id}` - Atualizar exercício
- `DELETE /api/exercises/{id}` - Deletar exercício

#### 🏆 **Ranking**
- `GET /api/ranking` - Ranking geral
- `GET /api/ranking/category/{category}` - Ranking por categoria
- `GET /api/ranking/region/{region}` - Ranking por região

#### ⚔️ **Desafios**
- `GET /api/challenges` - Listar desafios
- `POST /api/challenges` - Criar desafio
- `POST /api/challenges/{id}/accept` - Aceitar desafio
- `POST /api/challenges/{id}/complete` - Completar desafio

## 🚀 **COMO EXECUTAR**

### 📋 **PRÉ-REQUISITOS**
- PHP 8.0+
- MySQL 8.0+
- Servidor Web (Apache/Nginx)

### ⚙️ **INSTALAÇÃO**
1. Clone o repositório
2. Configure o banco de dados
3. Configure as credenciais em `api/config/database.php`
4. Acesse a aplicação

## 🎨 **INTERFACE DO USUÁRIO**

### 📱 **DASHBOARD PRINCIPAL**
- Ranking pessoal e global
- Estatísticas de exercícios
- Desafios ativos
- Próximas metas

### 🏃‍♂️ **REGISTRO DE EXERCÍCIOS**
- Interface intuitiva para registrar exercícios
- Categorias e tipos de exercício
- Duração e intensidade
- Fotos e comentários

### 🏆 **RANKING E COMPETIÇÃO**
- Visualização do ranking em tempo real
- Filtros por categoria e região
- Perfis de outros usuários
- Sistema de desafios

### ⚔️ **DESAFIOS E BATALHAS**
- Criar desafios personalizados
- Aceitar desafios de outros usuários
- Acompanhar progresso
- Sistema de apostas

## 🔒 **SEGURANÇA**

- **Autenticação JWT** para sessões seguras
- **Hash de senhas** com bcrypt
- **Validação de entrada** em todos os endpoints
- **Sanitização de dados** para prevenir SQL injection
- **Headers CORS** configurados adequadamente

## 🚀 **ROADMAP FUTURO**

- **App Mobile** (React Native/Flutter)
- **Integração com wearables** (Apple Watch, Fitbit)
- **Sistema de prêmios reais** e patrocínios
- **IA para sugestões** de exercícios
- **Sistema de streaming** de exercícios
- **Integração com redes sociais**

---

## 👥 **AUTORES**

- **Desenvolvido por**: [Seu Nome]
- **Projeto**: FIT BATTLE - A Guerra pela Saúde
- **Data**: Janeiro 2025

## 📝 **LICENÇA**

Este projeto foi desenvolvido para fins educacionais e de demonstração.

---

**🏃‍♂️💪 PRONTO PARA A BATALHA? ENTRE NO FIT BATTLE E PROVE QUE VOCÊ É O MAIS SAUDÁVEL! ⚔️🏆**
