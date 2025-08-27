/**
 * FIT BATTLE - JavaScript Principal
 * Sistema de Competição Fitness
 */

class FitBattleApp {
    constructor() {
        this.currentUser = null;
        this.isAuthenticated = false;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupNavigation();
        this.loadMockData();
        this.animateOnScroll();
    }

    setupEventListeners() {
        // Botões de autenticação
        document.getElementById('loginBtn')?.addEventListener('click', () => this.showModal('loginModal'));
        document.getElementById('registerBtn')?.addEventListener('click', () => this.showModal('registerModal'));
        
        // Botões de ação
        document.getElementById('startBattleBtn')?.addEventListener('click', () => this.startBattle());
        document.getElementById('learnMoreBtn')?.addEventListener('click', () => this.scrollToSection('features'));
        document.getElementById('joinNowBtn')?.addEventListener('click', () => this.showModal('registerModal'));
        
        // Botões de ranking
        document.getElementById('viewFullRankingBtn')?.addEventListener('click', () => this.viewFullRanking());
        
        // Botões de desafios
        document.getElementById('createChallengeBtn')?.addEventListener('click', () => this.createChallenge());
        document.getElementById('viewAllChallengesBtn')?.addEventListener('click', () => this.viewAllChallenges());
        
        // Fechar modais
        document.querySelectorAll('.close').forEach(closeBtn => {
            closeBtn.addEventListener('click', (e) => this.closeModal(e.target.closest('.modal')));
        });
        
        // Fechar modal ao clicar fora
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeModal(e.target);
            }
        });
        
        // Formulários
        document.getElementById('loginForm')?.addEventListener('submit', (e) => this.handleLogin(e));
        document.getElementById('registerForm')?.addEventListener('submit', (e) => this.handleRegister(e));
        
        // Tabs de ranking
        document.querySelectorAll('.tab-btn').forEach(tab => {
            tab.addEventListener('click', (e) => this.switchRankingTab(e.target));
        });
        
        // Categorias de exercícios
        document.querySelectorAll('.category-card').forEach(card => {
            card.addEventListener('click', (e) => this.selectCategory(e.currentTarget));
        });
    }

    setupNavigation() {
        // Navegação suave
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Navegação ativa
        window.addEventListener('scroll', () => {
            this.updateActiveNavigation();
        });
    }

    updateActiveNavigation() {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link');
        
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (window.pageYOffset >= sectionTop - 200) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    }

    showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }

    closeModal(modal) {
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    async handleLogin(e) {
        e.preventDefault();
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;

        try {
            // Simular login
            await this.simulateLogin(email, password);
            this.closeModal(document.getElementById('loginModal'));
            this.showNotification('🔑 Login realizado com sucesso!', 'success');
            this.updateUIAfterAuth();
        } catch (error) {
            this.showNotification('❌ Erro no login: ' + error.message, 'error');
        }
    }

    async handleRegister(e) {
        e.preventDefault();
        const username = document.getElementById('registerUsername').value;
        const email = document.getElementById('registerEmail').value;
        const password = document.getElementById('registerPassword').value;
        const fullName = document.getElementById('registerFullName').value;

        try {
            // Simular registro
            await this.simulateRegister({ username, email, password, fullName });
            this.closeModal(document.getElementById('registerModal'));
            this.showNotification('🚀 Cadastro realizado com sucesso!', 'success');
            this.updateUIAfterAuth();
        } catch (error) {
            this.showNotification('❌ Erro no cadastro: ' + error.message, 'error');
        }
    }

    async simulateLogin(email, password) {
        // Simular delay de API
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        if (email === 'admin@fitbattle.com' && password === 'password') {
            this.currentUser = {
                id: 1,
                username: 'admin',
                email: email,
                fullName: 'Administrador',
                totalPoints: 2500,
                currentLevel: 25,
                currentStreak: 7
            };
            this.isAuthenticated = true;
            localStorage.setItem('fitBattleUser', JSON.stringify(this.currentUser));
        } else {
            throw new Error('Credenciais inválidas');
        }
    }

    async simulateRegister(userData) {
        // Simular delay de API
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        this.currentUser = {
            id: Date.now(),
            username: userData.username,
            email: userData.email,
            fullName: userData.fullName,
            totalPoints: 0,
            currentLevel: 1,
            currentStreak: 0
        };
        this.isAuthenticated = true;
        localStorage.setItem('fitBattleUser', JSON.stringify(this.currentUser));
    }

    updateUIAfterAuth() {
        if (this.isAuthenticated && this.currentUser) {
            // Atualizar botões de autenticação
            const authContainer = document.querySelector('.nav-auth');
            if (authContainer) {
                authContainer.innerHTML = `
                    <span class="user-welcome">👋 Olá, ${this.currentUser.username}!</span>
                    <button class="btn btn-outline" onclick="app.logout()">🚪 Sair</button>
                `;
            }
            
            // Atualizar estatísticas do usuário
            this.updateUserStats();
        }
    }

    updateUserStats() {
        if (this.currentUser) {
            // Atualizar estatísticas na página
            const statCards = document.querySelectorAll('.stat-card');
            if (statCards.length >= 3) {
                statCards[0].querySelector('.stat-value').textContent = this.currentUser.totalPoints + '+';
                statCards[1].querySelector('.stat-value').textContent = this.currentUser.currentLevel;
                statCards[2].querySelector('.stat-value').textContent = this.currentUser.currentStreak;
            }
        }
    }

    logout() {
        this.currentUser = null;
        this.isAuthenticated = false;
        localStorage.removeItem('fitBattleUser');
        location.reload();
    }

    startBattle() {
        if (this.isAuthenticated) {
            this.showNotification('⚔️ Batalha iniciada! Prepare-se para dominar o ranking!', 'success');
            this.scrollToSection('ranking');
        } else {
            this.showModal('registerModal');
        }
    }

    scrollToSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }

    switchRankingTab(clickedTab) {
        // Remover classe ativa de todas as tabs
        document.querySelectorAll('.tab-btn').forEach(tab => tab.classList.remove('active'));
        
        // Adicionar classe ativa na tab clicada
        clickedTab.classList.add('active');
        
        // Carregar dados do ranking baseado na tab
        const tabType = clickedTab.dataset.tab;
        this.loadRankingData(tabType);
    }

    loadRankingData(tabType) {
        const rankingList = document.getElementById('rankingList');
        if (!rankingList) return;

        // Simular dados de ranking
        const mockRankings = {
            global: [
                { position: 1, username: 'FitnessKing', points: 15420, level: 154, streak: 45 },
                { position: 2, username: 'IronWoman', points: 12850, level: 128, streak: 32 },
                { position: 3, username: 'SpeedRunner', points: 11200, level: 112, streak: 28 },
                { position: 4, username: 'YogaMaster', points: 9850, level: 98, streak: 67 },
                { position: 5, username: 'CrossFitPro', points: 8750, level: 87, streak: 23 }
            ],
            running: [
                { position: 1, username: 'SpeedRunner', points: 11200, level: 112, streak: 28 },
                { position: 2, username: 'MarathonMan', points: 8900, level: 89, streak: 15 },
                { position: 3, username: 'TrailBlazer', points: 7650, level: 76, streak: 42 }
            ],
            gym: [
                { position: 1, username: 'IronWoman', points: 12850, level: 128, streak: 32 },
                { position: 2, username: 'MuscleBuilder', points: 10200, level: 102, streak: 18 },
                { position: 3, username: 'PowerLifter', points: 8900, level: 89, streak: 25 }
            ],
            yoga: [
                { position: 1, username: 'YogaMaster', points: 9850, level: 98, streak: 67 },
                { position: 2, username: 'ZenSeeker', points: 7200, level: 72, streak: 89 },
                { position: 3, username: 'MindfulOne', points: 6100, level: 61, streak: 45 }
            ]
        };

        const rankingData = mockRankings[tabType] || mockRankings.global;
        this.renderRanking(rankingList, rankingData);
    }

    renderRanking(container, rankingData) {
        container.innerHTML = rankingData.map(user => `
            <div class="ranking-item">
                <div class="ranking-position">#${user.position}</div>
                <div class="ranking-user">
                    <div class="user-avatar">👤</div>
                    <div class="user-info">
                        <div class="username">${user.username}</div>
                        <div class="user-stats">
                            <span class="level">Nível ${user.level}</span>
                            <span class="streak">🔥 ${user.streak} dias</span>
                        </div>
                    </div>
                </div>
                <div class="ranking-points">${user.points.toLocaleString()} pts</div>
            </div>
        `).join('');
    }

    selectCategory(card) {
        const category = card.dataset.category;
        this.showNotification(`💪 Categoria selecionada: ${card.querySelector('h3').textContent}`, 'info');
        
        // Adicionar efeito visual
        card.style.transform = 'scale(1.05)';
        setTimeout(() => {
            card.style.transform = 'scale(1)';
        }, 200);
    }

    viewFullRanking() {
        this.showNotification('📊 Redirecionando para o ranking completo...', 'info');
        // Aqui você pode redirecionar para uma página de ranking completa
    }

    createChallenge() {
        if (this.isAuthenticated) {
            this.showNotification('⚔️ Redirecionando para criação de desafio...', 'info');
        } else {
            this.showModal('registerModal');
        }
    }

    viewAllChallenges() {
        this.showNotification('📋 Redirecionando para todos os desafios...', 'info');
    }

    loadMockData() {
        // Carregar dados iniciais
        this.loadRankingData('global');
        this.loadChallengesData();
        
        // Verificar se há usuário logado
        const savedUser = localStorage.getItem('fitBattleUser');
        if (savedUser) {
            this.currentUser = JSON.parse(savedUser);
            this.isAuthenticated = true;
            this.updateUIAfterAuth();
        }
    }

    loadChallengesData() {
        const challengesGrid = document.getElementById('challengesGrid');
        if (!challengesGrid) return;

        const mockChallenges = [
            {
                title: '🏃‍♂️ Desafio da Semana',
                description: 'Corra 21km em 7 dias',
                participants: 45,
                prize: '500 pontos',
                timeLeft: '3 dias'
            },
            {
                title: '💪 Academia Intensa',
                description: 'Complete 100 séries em 30 dias',
                participants: 23,
                prize: '300 pontos',
                timeLeft: '15 dias'
            },
            {
                title: '🧘‍♀️ Yoga Challenge',
                description: '30 dias de yoga consecutivos',
                participants: 67,
                prize: '400 pontos',
                timeLeft: '8 dias'
            }
        ];

        challengesGrid.innerHTML = mockChallenges.map(challenge => `
            <div class="challenge-card">
                <div class="challenge-header">
                    <h3>${challenge.title}</h3>
                    <span class="challenge-prize">🏆 ${challenge.prize}</span>
                </div>
                <p class="challenge-description">${challenge.description}</p>
                <div class="challenge-footer">
                    <span class="participants">👥 ${challenge.participants} participantes</span>
                    <span class="time-left">⏰ ${challenge.timeLeft}</span>
                </div>
                <button class="btn btn-primary btn-small">⚔️ Participar</button>
            </div>
        `).join('');
    }

    animateOnScroll() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-up');
                }
            });
        }, observerOptions);

        // Observar elementos para animação
        document.querySelectorAll('.feature-card, .step, .category-card, .challenge-card').forEach(el => {
            observer.observe(el);
        });
    }

    showNotification(message, type = 'info') {
        // Criar notificação
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;

        // Adicionar estilos
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 3000;
            transform: translateX(100%);
            transition: transform 0.3s ease-out;
            max-width: 400px;
        `;

        // Adicionar ao DOM
        document.body.appendChild(notification);

        // Animar entrada
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Configurar fechamento
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        });

        // Auto-remover após 5 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }
}

// Inicializar aplicação quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.app = new FitBattleApp();
});

// Adicionar estilos para notificações
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    .notification-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0;
        line-height: 1;
    }
    
    .notification-close:hover {
        opacity: 0.8;
    }
    
    .ranking-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        background: white;
        border-radius: 12px;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    
    .ranking-item:hover {
        transform: translateY(-2px);
    }
    
    .ranking-position {
        font-size: 1.5rem;
        font-weight: 900;
        color: #667eea;
        min-width: 60px;
    }
    
    .ranking-user {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
    }
    
    .user-avatar {
        font-size: 2rem;
    }
    
    .username {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 0.25rem;
    }
    
    .user-stats {
        display: flex;
        gap: 1rem;
        font-size: 0.9rem;
        color: #6c757d;
    }
    
    .ranking-points {
        font-weight: 700;
        font-size: 1.2rem;
        color: #28a745;
    }
    
    .challenge-card {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    
    .challenge-card:hover {
        transform: translateY(-3px);
    }
    
    .challenge-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .challenge-header h3 {
        font-size: 1.2rem;
        font-weight: 700;
        color: #333;
    }
    
    .challenge-prize {
        background: linear-gradient(135deg, #ffc107, #fd7e14);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .challenge-description {
        color: #6c757d;
        margin-bottom: 1rem;
        line-height: 1.5;
    }
    
    .challenge-footer {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        font-size: 0.9rem;
        color: #6c757d;
    }
    
    .btn-small {
        padding: 8px 16px;
        font-size: 14px;
        min-height: 36px;
    }
    
    .user-welcome {
        color: #667eea;
        font-weight: 600;
        margin-right: 1rem;
    }
`;
document.head.appendChild(notificationStyles);
