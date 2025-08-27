/**
 * Sistema de Perfil do Usuário
 * Vida Equilibrada
 */

class ProfileManager {
    constructor() {
        this.currentUser = null;
        this.userStats = null;
        this.init();
    }

    init() {
        this.loadCurrentUser();
        this.loadUserStats();
        this.bindEvents();
    }

    loadCurrentUser() {
        const user = localStorage.getItem('currentUser');
        if (user) {
            this.currentUser = JSON.parse(user);
            this.renderProfile();
        }
    }

    async loadUserStats() {
        try {
            const response = await fetch(API_CONFIG.buildUrl(API_CONFIG.ENDPOINTS.STATS), {
                headers: API_CONFIG.getDefaultHeaders()
            });
            
            if (response.ok) {
                this.userStats = await response.json();
                this.renderStats();
            }
        } catch (error) {
            console.error('Erro ao carregar estatísticas:', error);
        }
    }

    renderProfile() {
        const container = document.getElementById('profile-container');
        if (!container || !this.currentUser) return;

        container.innerHTML = `
            <div class="profile-header">
                <div class="profile-avatar">
                    ${this.currentUser.nome.charAt(0).toUpperCase()}
                </div>
                <div class="profile-info">
                    <h1 class="profile-name">${this.currentUser.nome}</h1>
                    <p class="profile-email">${this.currentUser.email}</p>
                    <div class="profile-level">
                        <span class="level-label">Nível</span>
                        <span class="level-number">${this.calculateLevel(this.currentUser.pontos)}</span>
                    </div>
                </div>
            </div>
            
            <div class="profile-stats-overview">
                <div class="stat-card">
                    <div class="stat-icon">🏆</div>
                    <div class="stat-value">${this.currentUser.pontos}</div>
                    <div class="stat-label">Pontos Totais</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🔥</div>
                    <div class="stat-value">${this.currentUser.streak_atual}</div>
                    <div class="stat-label">Streak Atual</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🔥</div>
                    <div class="stat-value">${this.currentUser.streak_maximo}</div>
                    <div class="stat-label">Melhor Streak</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value">${this.currentUser.total_habitos}</div>
                    <div class="stat-label">Hábitos Completados</div>
                </div>
            </div>
            
            <div class="profile-actions">
                <button class="btn btn-primary" onclick="profileManager.editProfile()">
                    ✏️ Editar Perfil
                </button>
                <button class="btn btn-secondary" onclick="profileManager.changePassword()">
                    🔒 Alterar Senha
                </button>
                <button class="btn btn-outline" onclick="profileManager.exportData()">
                    📊 Exportar Dados
                </button>
            </div>
        `;
    }

    renderStats() {
        const container = document.getElementById('stats-container');
        if (!container || !this.userStats) return;

        if (this.userStats.length === 0) {
            container.innerHTML = '<div class="no-stats">Nenhuma estatística disponível ainda.</div>';
            return;
        }

        // Agrupar estatísticas por período
        const weeklyStats = this.userStats.filter(stat => stat.periodo === 'semana');
        const monthlyStats = this.userStats.filter(stat => stat.periodo === 'mes');

        container.innerHTML = `
            <div class="stats-section">
                <h3>📈 Estatísticas da Semana</h3>
                ${this.renderStatsChart(weeklyStats)}
            </div>
            
            <div class="stats-section">
                <h3>📊 Estatísticas do Mês</h3>
                ${this.renderStatsChart(monthlyStats)}
            </div>
            
            <div class="stats-section">
                <h3>🎯 Metas e Objetivos</h3>
                ${this.renderGoals()}
            </div>
        `;
    }

    renderStatsChart(stats) {
        if (stats.length === 0) {
            return '<div class="no-data">Nenhum dado disponível para este período.</div>';
        }

        return `
            <div class="stats-chart">
                ${stats.map(stat => `
                    <div class="stat-day">
                        <div class="stat-date">${this.formatDate(stat.data_registro)}</div>
                        <div class="stat-bar" style="height: ${this.calculateBarHeight(stat.habitos_completados)}px">
                            <span class="stat-tooltip">${stat.habitos_completados} hábitos</span>
                        </div>
                        <div class="stat-points">+${stat.pontos_ganhos}</div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    renderGoals() {
        const goals = [
            { name: 'Streak de 7 dias', current: this.currentUser?.streak_atual || 0, target: 7, icon: '🔥' },
            { name: '1000 pontos', current: this.currentUser?.pontos || 0, target: 1000, icon: '🏆' },
            { name: '10 hábitos por semana', current: this.currentUser?.total_habitos || 0, target: 10, icon: '✅' }
        ];

        return `
            <div class="goals-grid">
                ${goals.map(goal => {
                    const progress = Math.min((goal.current / goal.target) * 100, 100);
                    return `
                        <div class="goal-card">
                            <div class="goal-icon">${goal.icon}</div>
                            <div class="goal-info">
                                <h4 class="goal-name">${goal.name}</h4>
                                <div class="goal-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: ${progress}%"></div>
                                    </div>
                                    <span class="progress-text">${goal.current}/${goal.target}</span>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
    }

    calculateLevel(points) {
        return Math.floor(points / 100) + 1;
    }

    calculateBarHeight(habitsCompleted) {
        const maxHeight = 100;
        const maxHabits = 10;
        return Math.min((habitsCompleted / maxHabits) * maxHeight, maxHeight);
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
    }

    editProfile() {
        // Implementar modal de edição de perfil
        console.log('Editar perfil');
    }

    changePassword() {
        // Implementar modal de alteração de senha
        console.log('Alterar senha');
    }

    exportData() {
        // Implementar exportação de dados
        console.log('Exportar dados');
    }

    async updateProfile(profileData) {
        try {
            const response = await fetch('/api/usuarios/profile', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: JSON.stringify(profileData)
            });

            if (response.ok) {
                const updatedUser = await response.json();
                this.currentUser = updatedUser;
                localStorage.setItem('currentUser', JSON.stringify(updatedUser));
                this.renderProfile();
                this.showMessage('Perfil atualizado com sucesso!', 'success');
                return true;
            }
        } catch (error) {
            console.error('Erro ao atualizar perfil:', error);
            this.showMessage('Erro ao atualizar perfil', 'error');
        }
        return false;
    }

    showMessage(message, type = 'info') {
        // Implementar sistema de mensagens
        console.log(`${type.toUpperCase()}: ${message}`);
    }

    bindEvents() {
        // Adicionar event listeners
        document.addEventListener('DOMContentLoaded', () => {
            // Event listeners específicos do perfil
        });
    }
}

// Variável global para o manager
let profileManager;

// Inicializar quando a configuração da API estiver pronta
window.addEventListener('APIConfigReady', function(event) {
    console.log('APIConfigReady recebido, inicializando ProfileManager...');
    if (typeof window.API_CONFIG !== 'undefined') {
        profileManager = new ProfileManager();
        window.profileManager = profileManager;
    } else {
        console.error('API_CONFIG ainda não está definido após evento APIConfigReady');
    }
});

// Fallback: se o evento não for disparado, tentar inicializar após um delay
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        if (typeof window.API_CONFIG !== 'undefined' && !profileManager) {
            console.log('API_CONFIG encontrado via fallback, inicializando ProfileManager...');
            profileManager = new ProfileManager();
            window.profileManager = profileManager;
        }
    }, 2000);
});
