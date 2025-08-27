/**
 * Sistema de Badges e Conquistas
 * Vida Equilibrada
 */

class BadgesManager {
    constructor() {
        this.badges = [];
        this.userBadges = [];
        this.currentUser = null;
        this.init();
    }

    init() {
        this.loadCurrentUser();
        this.loadBadges();
        this.loadUserBadges();
        this.bindEvents();
    }

    loadCurrentUser() {
        const user = localStorage.getItem('currentUser');
        if (user) {
            this.currentUser = JSON.parse(user);
        }
    }

    async loadBadges() {
        try {
            const response = await fetch(API_CONFIG.buildUrl(API_CONFIG.ENDPOINTS.BADGES), {
                headers: API_CONFIG.getDefaultHeaders()
            });
            
            if (response.ok) {
                this.badges = await response.json();
                this.renderBadges();
            }
        } catch (error) {
            console.error('Erro ao carregar badges:', error);
        }
    }

    async loadUserBadges() {
        try {
            const response = await fetch(API_CONFIG.buildUrl(API_CONFIG.ENDPOINTS.BADGES_CONQUERED), {
                headers: API_CONFIG.getDefaultHeaders()
            });
            
            if (response.ok) {
                this.userBadges = await response.json();
                this.renderUserBadges();
            }
        } catch (error) {
            console.error('Erro ao carregar badges do usuário:', error);
        }
    }

    renderBadges() {
        const container = document.getElementById('badges-container');
        if (!container) return;

        container.innerHTML = `
            <div class="badges-grid">
                ${this.badges.map(badge => `
                    <div class="badge-card ${this.isBadgeUnlocked(badge.id) ? 'unlocked' : 'locked'}" 
                         data-id="${badge.id}">
                        <div class="badge-icon">${badge.icone}</div>
                        <div class="badge-info">
                            <h3 class="badge-name">${badge.nome}</h3>
                            <p class="badge-description">${badge.descricao}</p>
                            <div class="badge-requirements">
                                <span class="badge-type ${badge.tipo}">${this.getTypeLabel(badge.tipo)}</span>
                                <span class="badge-requirement">${this.getRequirementText(badge)}</span>
                            </div>
                            <div class="badge-reward">
                                <span class="reward-points">+${badge.pontos_bonus} pts</span>
                            </div>
                        </div>
                        <div class="badge-status">
                            ${this.isBadgeUnlocked(badge.id) ? '✅' : '🔒'}
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    renderUserBadges() {
        const container = document.getElementById('user-badges-container');
        if (!container) return;

        if (this.userBadges.length === 0) {
            container.innerHTML = '<div class="no-badges">Nenhuma conquista ainda. Continue se esforçando!</div>';
            return;
        }

        container.innerHTML = `
            <div class="user-badges-grid">
                ${this.userBadges.map(userBadge => {
                    const badge = this.badges.find(b => b.id === userBadge.badge_id);
                    if (!badge) return '';
                    
                    return `
                        <div class="user-badge-card unlocked" data-id="${userBadge.id}">
                            <div class="badge-icon">${badge.icone}</div>
                            <div class="badge-info">
                                <h3 class="badge-name">${badge.nome}</h3>
                                <p class="badge-description">${badge.descricao}</p>
                                <div class="badge-conquest-date">
                                    Conquistado em: ${this.formatDate(userBadge.data_conquista)}
                                </div>
                                <div class="badge-reward">
                                    <span class="reward-points">+${userBadge.pontos_ganhos} pts</span>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
    }

    isBadgeUnlocked(badgeId) {
        return this.userBadges.some(ub => ub.badge_id === badgeId);
    }

    getTypeLabel(type) {
        const labels = {
            'streak': '🔥 Streak',
            'quantidade': '📊 Quantidade',
            'especial': '⭐ Especial',
            'tempo': '⏰ Tempo'
        };
        return labels[type] || type;
    }

    getRequirementText(badge) {
        switch (badge.tipo) {
            case 'streak':
                return `Mantenha um streak de ${badge.requisito_valor} dias`;
            case 'quantidade':
                return `Complete ${badge.requisito_valor} hábitos`;
            case 'especial':
                return 'Condição especial';
            case 'tempo':
                return `Complete em ${badge.requisito_valor} minutos`;
            default:
                return `Requisito: ${badge.requisito_valor}`;
        }
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR');
    }

    async checkForNewBadges() {
        try {
            const response = await fetch(API_CONFIG.buildUrl(API_CONFIG.ENDPOINTS.BADGES_CHECK), {
                method: 'POST',
                headers: API_CONFIG.getDefaultHeaders()
            });
            
            if (response.ok) {
                const newBadges = await response.json();
                if (newBadges.length > 0) {
                    this.showNewBadgesNotification(newBadges);
                    this.loadUserBadges(); // Recarregar badges do usuário
                }
            }
        } catch (error) {
            console.error('Erro ao verificar novas badges:', error);
        }
    }

    showNewBadgesNotification(newBadges) {
        // Implementar notificação de novas badges
        newBadges.forEach(badge => {
            this.showNotification(`🏆 Nova conquista: ${badge.nome}!`, 'success');
        });
    }

    showNotification(message, type = 'info') {
        // Implementar sistema de notificações
        console.log(`${type.toUpperCase()}: ${message}`);
    }

    bindEvents() {
        // Verificar novas badges periodicamente
        setInterval(() => {
            this.checkForNewBadges();
        }, 60000); // Verificar a cada minuto
    }

    getBadgeProgress(badgeId) {
        // Implementar cálculo de progresso para badges
        return 0; // Placeholder
    }

    exportBadges() {
        // Implementar exportação das badges
        console.log('Exportar badges');
    }
}

// Variável global para o manager
let badgesManager;

// Inicializar quando a configuração da API estiver pronta
window.addEventListener('APIConfigReady', function(event) {
    console.log('APIConfigReady recebido, inicializando BadgesManager...');
    if (typeof window.API_CONFIG !== 'undefined') {
        badgesManager = new BadgesManager();
        window.badgesManager = badgesManager;
    } else {
        console.error('API_CONFIG ainda não está definido após evento APIConfigReady');
    }
});

// Fallback: se o evento não for disparado, tentar inicializar após um delay
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        if (typeof window.API_CONFIG !== 'undefined' && !badgesManager) {
            console.log('API_CONFIG encontrado via fallback, inicializando BadgesManager...');
            badgesManager = new BadgesManager();
            window.badgesManager = badgesManager;
        }
    }, 2000);
});
