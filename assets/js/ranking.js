/**
 * Sistema de Ranking
 * Vida Equilibrada
 */

class RankingManager {
    constructor() {
        this.rankings = {
            semana: [],
            mes: [],
            ano: []
        };
        this.currentPeriod = 'semana';
        this.init();
    }

    init() {
        this.loadRankings();
        this.bindEvents();
    }

    async loadRankings() {
        try {
            const response = await fetch(API_CONFIG.buildUrl(API_CONFIG.ENDPOINTS.RANKING), {
                headers: API_CONFIG.getDefaultHeaders()
            });
            
            if (response.ok) {
                const data = await response.json();
                this.rankings = data;
                this.renderRanking(this.currentPeriod);
            }
        } catch (error) {
            console.error('Erro ao carregar ranking:', error);
        }
    }

    renderRanking(period = 'semana') {
        const container = document.getElementById('ranking-container');
        if (!container) return;

        const ranking = this.rankings[period] || [];
        
        container.innerHTML = `
            <div class="ranking-header">
                <h2>🏆 Ranking ${this.getPeriodLabel(period)}</h2>
                <div class="period-selector">
                    <button class="btn ${period === 'semana' ? 'btn-primary' : 'btn-outline'}" 
                            onclick="rankingManager.changePeriod('semana')">Semana</button>
                    <button class="btn ${period === 'mes' ? 'btn-primary' : 'btn-outline'}" 
                            onclick="rankingManager.changePeriod('mes')">Mês</button>
                    <button class="btn ${period === 'ano' ? 'btn-primary' : 'btn-outline'}" 
                            onclick="rankingManager.changePeriod('ano')">Ano</button>
                </div>
            </div>
            <div class="ranking-list">
                ${this.generateRankingHTML(ranking)}
            </div>
        `;
    }

    generateRankingHTML(ranking) {
        if (ranking.length === 0) {
            return '<div class="no-ranking">Nenhum ranking disponível ainda.</div>';
        }

        return ranking.map((user, index) => {
            const position = index + 1;
            const medal = this.getMedal(position);
            const isCurrentUser = user.id === this.getCurrentUserId();
            
            return `
                <div class="ranking-item ${isCurrentUser ? 'current-user' : ''}" data-position="${position}">
                    <div class="ranking-position">
                        ${medal} ${position}º
                    </div>
                    <div class="ranking-user">
                        <div class="user-avatar">
                            ${user.nome.charAt(0).toUpperCase()}
                        </div>
                        <div class="user-info">
                            <div class="user-name">${user.nome}</div>
                            <div class="user-stats">
                                <span class="user-points">${user.pontos} pts</span>
                                <span class="user-streak">🔥 ${user.streak_atual}</span>
                                <span class="user-badges">🏅 ${user.total_badges}</span>
                            </div>
                        </div>
                    </div>
                    <div class="ranking-score">
                        <div class="score-main">${user.pontos}</div>
                        <div class="score-label">pontos</div>
                    </div>
                </div>
            `;
        }).join('');
    }

    getMedal(position) {
        switch (position) {
            case 1: return '🥇';
            case 2: return '🥈';
            case 3: return '🥉';
            default: return '🏅';
        }
    }

    getPeriodLabel(period) {
        const labels = {
            'semana': 'da Semana',
            'mes': 'do Mês',
            'ano': 'do Ano'
        };
        return labels[period] || '';
    }

    getCurrentUserId() {
        const user = localStorage.getItem('currentUser');
        return user ? JSON.parse(user).id : null;
    }

    changePeriod(period) {
        this.currentPeriod = period;
        this.renderRanking(period);
    }

    async refreshRanking() {
        await this.loadRankings();
        this.renderRanking(this.currentPeriod);
    }

    bindEvents() {
        // Adicionar event listeners para atualizações automáticas
        setInterval(() => {
            this.refreshRanking();
        }, 300000); // Atualizar a cada 5 minutos
    }

    showUserDetails(userId) {
        // Implementar modal com detalhes do usuário
        console.log('Mostrar detalhes do usuário:', userId);
    }

    exportRanking() {
        // Implementar exportação do ranking
        console.log('Exportar ranking');
    }
}

// Variável global para o manager
let rankingManager;

// Inicializar quando a configuração da API estiver pronta
window.addEventListener('APIConfigReady', function(event) {
    console.log('APIConfigReady recebido, inicializando RankingManager...');
    if (typeof window.API_CONFIG !== 'undefined') {
        rankingManager = new RankingManager();
        window.rankingManager = rankingManager;
    } else {
        console.error('API_CONFIG ainda não está definido após evento APIConfigReady');
    }
});

// Fallback: se o evento não for disparado, tentar inicializar após um delay
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        if (typeof window.API_CONFIG !== 'undefined' && !rankingManager) {
            console.log('API_CONFIG encontrado via fallback, inicializando RankingManager...');
            rankingManager = new RankingManager();
            window.rankingManager = rankingManager;
        }
    }, 2000);
});
