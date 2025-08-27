/**
 * FIT BATTLE - Sistema de Ranking
 * Gerenciamento de Rankings e Competições
 */

class RankingManager {
    constructor() {
        this.currentTab = 'global';
        this.rankings = {};
        this.filters = {
            period: 'all',
            region: 'all',
            ageGroup: 'all'
        };
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadInitialRankings();
        this.setupRealTimeUpdates();
    }

    setupEventListeners() {
        // Tabs de ranking
        document.querySelectorAll('.tab-btn').forEach(tab => {
            tab.addEventListener('click', (e) => this.switchTab(e.target));
        });

        // Botões de ação removidos - agora são links diretos
        
        // Filtros (se existirem)
        this.setupFilters();
    }

    setupFilters() {
        // Filtros de período
        const periodFilters = document.querySelectorAll('[data-filter="period"]');
        periodFilters.forEach(filter => {
            filter.addEventListener('change', (e) => {
                this.filters.period = e.target.value;
                this.refreshRankings();
            });
        });

        // Filtros de região
        const regionFilters = document.querySelectorAll('[data-filter="region"]');
        regionFilters.forEach(filter => {
            filter.addEventListener('change', (e) => {
                this.filters.region = e.target.value;
                this.refreshRankings();
            });
        });
    }

    switchTab(clickedTab) {
        // Remover classe ativa de todas as tabs
        document.querySelectorAll('.tab-btn').forEach(tab => tab.classList.remove('active'));
        
        // Adicionar classe ativa na tab clicada
        clickedTab.classList.add('active');
        
        // Atualizar tab atual
        this.currentTab = clickedTab.dataset.tab;
        
        // Carregar dados do ranking
        this.loadRankingData(this.currentTab);
        
        // Atualizar URL
        this.updateURL();
    }

    loadInitialRankings() {
        // Carregar ranking global por padrão
        this.loadRankingData('global');
        
        // Pré-carregar outros rankings em background
        ['running', 'gym', 'yoga'].forEach(category => {
            setTimeout(() => this.loadRankingData(category), 1000);
        });
    }

    async loadRankingData(category) {
        try {
            // Verificar se já temos os dados em cache
            if (this.rankings[category] && !this.shouldRefreshRanking(category)) {
                this.renderRanking(category);
                return;
            }

            // Mostrar loading
            this.showRankingLoading(category);

            // Carregar dados
            const rankingData = await this.fetchRankingData(category);
            
            // Armazenar em cache
            this.rankings[category] = {
                data: rankingData,
                lastUpdated: Date.now(),
                category: category
            };

            // Renderizar
            this.renderRanking(category);

        } catch (error) {
            console.error('Erro ao carregar ranking:', error);
            this.showRankingError(category, error.message);
        }
    }

    async fetchRankingData(category) {
        // Simular chamada de API
        await new Promise(resolve => setTimeout(resolve, 800));

        const mockRankings = {
            global: [
                { position: 1, username: 'Renato Cariani', points: 15420, level: 154, streak: 45, region: 'SP', age: 28, profileImage: null },
                { position: 2, username: 'Aizan Bolt', points: 12850, level: 128, streak: 32, region: 'RJ', age: 31, profileImage: null },
                { position: 3, username: 'Pelé Fitness', points: 11200, level: 112, streak: 28, region: 'MG', age: 25, profileImage: null },
                { position: 4, username: 'Gisele Bündchen', points: 9850, level: 98, streak: 67, region: 'RS', age: 35, profileImage: null },
                { position: 5, username: 'Ronaldo Fenômeno', points: 8750, level: 87, streak: 23, region: 'BA', age: 29, profileImage: null },
                { position: 6, username: 'Eliud Kipchoge', points: 8200, level: 82, streak: 15, region: 'SC', age: 33, profileImage: null },
                { position: 7, username: 'Haile Gebrselassie', points: 7650, level: 76, streak: 42, region: 'PR', age: 27, profileImage: null },
                { position: 8, username: 'Ronnie Coleman', points: 7200, level: 72, streak: 19, region: 'GO', age: 30, profileImage: null },
                { position: 9, username: 'Madonna', points: 6800, level: 68, streak: 89, region: 'CE', age: 26, profileImage: null },
                { position: 10, username: 'Jennifer Aniston', points: 6400, level: 64, streak: 45, region: 'PE', age: 32, profileImage: null }
            ],
            running: [
                { position: 1, username: 'Aizan Bolt', points: 11200, level: 112, streak: 28, region: 'MG', age: 25, profileImage: null },
                { position: 2, username: 'Eliud Kipchoge', points: 8900, level: 89, streak: 15, region: 'SC', age: 33, profileImage: null },
                { position: 3, username: 'Haile Gebrselassie', points: 7650, level: 76, streak: 42, region: 'PR', age: 27, profileImage: null },
                { position: 4, username: 'Renato Cariani', points: 7200, level: 72, streak: 20, region: 'SP', age: 28, profileImage: null },
                { position: 5, username: 'Aizan Bolt', points: 6800, level: 68, streak: 18, region: 'RJ', age: 31, profileImage: null }
            ],
            gym: [
                { position: 1, username: 'Renato Cariani', points: 12850, level: 128, streak: 32, region: 'RJ', age: 31, profileImage: null },
                { position: 2, username: 'Ronnie Coleman', points: 10200, level: 102, streak: 25, region: 'GO', age: 30, profileImage: null },
                { position: 3, username: 'Ronaldo Fenômeno', points: 8750, level: 87, streak: 23, region: 'BA', age: 29, profileImage: null },
                { position: 4, username: 'Renato Cariani', points: 8200, level: 82, streak: 22, region: 'SP', age: 28, profileImage: null },
                { position: 5, username: 'Arnold Schwarzenegger', points: 7800, level: 78, streak: 16, region: 'MG', age: 26, profileImage: null }
            ],
            yoga: [
                { position: 1, username: 'Gisele Bündchen', points: 9850, level: 98, streak: 67, region: 'RS', age: 35, profileImage: null },
                { position: 2, username: 'Madonna', points: 7200, level: 72, streak: 89, region: 'CE', age: 26, profileImage: null },
                { position: 3, username: 'Jennifer Aniston', points: 6100, level: 61, streak: 45, region: 'PE', age: 32, profileImage: null },
                { position: 4, username: 'Gisele Bündchen', points: 5800, level: 58, streak: 38, region: 'DF', age: 29, profileImage: null },
                { position: 5, username: 'Madonna', points: 5400, level: 54, streak: 52, region: 'MT', age: 34, profileImage: null }
            ]
        };

        let rankingData = mockRankings[category] || mockRankings.global;

        // Aplicar filtros
        rankingData = this.applyFilters(rankingData);

        // Ordenar por pontos
        rankingData.sort((a, b) => b.points - a.points);

        // Recalcular posições
        rankingData.forEach((user, index) => {
            user.position = index + 1;
        });

        return rankingData;
    }

    applyFilters(rankingData) {
        let filteredData = [...rankingData];

        // Filtro de período
        if (this.filters.period !== 'all') {
            filteredData = this.filterByPeriod(filteredData, this.filters.period);
        }

        // Filtro de região
        if (this.filters.region !== 'all') {
            filteredData = filteredData.filter(user => user.region === this.filters.region);
        }

        // Filtro de faixa etária
        if (this.filters.ageGroup !== 'all') {
            filteredData = this.filterByAgeGroup(filteredData, this.filters.ageGroup);
        }

        return filteredData;
    }

    filterByPeriod(data, period) {
        // Simular filtro por período
        const now = Date.now();
        const periods = {
            'week': 7 * 24 * 60 * 60 * 1000,
            'month': 30 * 24 * 60 * 60 * 1000,
            'year': 365 * 24 * 60 * 60 * 1000
        };

        if (periods[period]) {
            // Simular dados filtrados por período
            return data.filter(user => Math.random() > 0.3); // Manter 70% dos usuários
        }

        return data;
    }

    filterByAgeGroup(data, ageGroup) {
        const ageRanges = {
            '18-25': user => user.age >= 18 && user.age <= 25,
            '26-35': user => user.age >= 26 && user.age <= 35,
            '36-45': user => user.age >= 36 && user.age <= 45,
            '46+': user => user.age >= 46
        };

        if (ageRanges[ageGroup]) {
            return data.filter(ageRanges[ageGroup]);
        }

        return data;
    }

    renderRanking(category) {
        const rankingList = document.getElementById('rankingList');
        if (!rankingList) return;

        const rankingData = this.rankings[category]?.data || [];
        
        if (rankingData.length === 0) {
            rankingList.innerHTML = `
                <div class="empty-ranking">
                    <div class="empty-icon">🏆</div>
                    <h3>Nenhum ranking disponível</h3>
                    <p>Seja o primeiro a aparecer neste ranking!</p>
                </div>
            `;
            return;
        }

        rankingList.innerHTML = rankingData.map(user => this.createRankingItem(user)).join('');
        
        // Adicionar event listeners aos itens
        this.setupRankingItemEvents();
    }

    createRankingItem(user) {
        const positionClass = this.getPositionClass(user.position);
        const regionFlag = this.getRegionFlag(user.region);
        
        return `
            <div class="ranking-item ${positionClass}" data-user-id="${user.id}" data-username="${user.username}">
                <div class="ranking-position">
                    <span class="position-number">#${user.position}</span>
                    ${this.getPositionIcon(user.position)}
                </div>
                
                <div class="ranking-user">
                    <div class="user-avatar">
                        ${user.profileImage ? `<img src="${user.profileImage}" alt="${user.username}">` : '👤'}
                    </div>
                    <div class="user-info">
                        <div class="username">${user.username}</div>
                        <div class="user-details">
                            <span class="region">${regionFlag} ${user.region}</span>
                            <span class="age">${user.age} anos</span>
                        </div>
                        <div class="user-stats">
                            <span class="level">Nível ${user.level}</span>
                            <span class="streak">🔥 ${user.streak} dias</span>
                        </div>
                    </div>
                </div>
                
                <div class="ranking-points">
                    <div class="points-value">${user.points.toLocaleString()}</div>
                    <div class="points-label">pontos</div>
                </div>
                
                <div class="ranking-actions">
                    <button class="btn btn-outline btn-small challenge-btn" onclick="rankingManager.challengeUser('${user.username}')">
                        ⚔️ Desafiar
                    </button>
                    <button class="btn btn-outline btn-small profile-btn" onclick="rankingManager.viewUserProfile('${user.username}')">
                        👤 Perfil
                    </button>
                </div>
            </div>
        `;
    }

    getPositionClass(position) {
        if (position === 1) return 'gold';
        if (position === 2) return 'silver';
        if (position === 3) return 'bronze';
        return '';
    }

    getPositionIcon(position) {
        if (position === 1) return '🥇';
        if (position === 2) return '🥈';
        if (position === 3) return '🥉';
        return '';
    }

    getRegionFlag(region) {
        const regionFlags = {
            'SP': '🇧🇷', 'RJ': '🇧🇷', 'MG': '🇧🇷', 'RS': '🇧🇷', 'BA': '🇧🇷',
            'SC': '🇧🇷', 'PR': '🇧🇷', 'GO': '🇧🇷', 'CE': '🇧🇷', 'PE': '🇧🇷'
        };
        return regionFlags[region] || '🌍';
    }

    setupRankingItemEvents() {
        // Hover effects
        document.querySelectorAll('.ranking-item').forEach(item => {
            item.addEventListener('mouseenter', () => {
                item.classList.add('hovered');
            });
            
            item.addEventListener('mouseleave', () => {
                item.classList.remove('hovered');
            });
        });

        // Click para expandir detalhes
        document.querySelectorAll('.ranking-item').forEach(item => {
            item.addEventListener('click', (e) => {
                if (!e.target.classList.contains('btn')) {
                    this.toggleRankingItemDetails(item);
                }
            });
        });
    }

    toggleRankingItemDetails(item) {
        const isExpanded = item.classList.contains('expanded');
        
        if (isExpanded) {
            item.classList.remove('expanded');
            this.removeExpandedDetails(item);
        } else {
            // Remover expansão de outros itens
            document.querySelectorAll('.ranking-item.expanded').forEach(expandedItem => {
                expandedItem.classList.remove('expanded');
                this.removeExpandedDetails(expandedItem);
            });
            
            item.classList.add('expanded');
            this.addExpandedDetails(item);
        }
    }

    addExpandedDetails(item) {
        const username = item.dataset.username;
        const detailsDiv = document.createElement('div');
        detailsDiv.className = 'ranking-item-details';
        detailsDiv.innerHTML = `
            <div class="details-content">
                <div class="detail-section">
                    <h4>📊 Estatísticas Detalhadas</h4>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-label">Total de Exercícios</span>
                            <span class="stat-value">${Math.floor(Math.random() * 500) + 100}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Calorias Queimadas</span>
                            <span class="stat-value">${Math.floor(Math.random() * 50000) + 10000}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Distância Total</span>
                            <span class="stat-value">${Math.floor(Math.random() * 1000) + 100} km</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Tempo Total</span>
                            <span class="stat-value">${Math.floor(Math.random() * 200) + 50}h</span>
                        </div>
                    </div>
                </div>
                <div class="detail-section">
                    <h4>🏆 Conquistas Recentes</h4>
                    <div class="badges-grid">
                        <span class="badge">🔥 Em Chamas</span>
                        <span class="badge">⚡ Velocista</span>
                        <span class="badge">💪 Musculoso</span>
                        <span class="badge">🌅 Madrugador</span>
                    </div>
                </div>
            </div>
        `;
        
        item.appendChild(detailsDiv);
        
        // Animar entrada
        setTimeout(() => {
            detailsDiv.style.opacity = '1';
            detailsDiv.style.transform = 'translateY(0)';
        }, 10);
    }

    removeExpandedDetails(item) {
        const detailsDiv = item.querySelector('.ranking-item-details');
        if (detailsDiv) {
            detailsDiv.style.opacity = '0';
            detailsDiv.style.transform = 'translateY(-10px)';
            setTimeout(() => detailsDiv.remove(), 200);
        }
    }

    showRankingLoading(category) {
        const rankingList = document.getElementById('rankingList');
        if (rankingList) {
            rankingList.innerHTML = `
                <div class="ranking-loading">
                    <div class="loading-spinner"></div>
                    <p>Carregando ranking ${category}...</p>
                </div>
            `;
        }
    }

    showRankingError(category, error) {
        const rankingList = document.getElementById('rankingList');
        if (rankingList) {
            rankingList.innerHTML = `
                <div class="ranking-error">
                    <div class="error-icon">❌</div>
                    <h3>Erro ao carregar ranking</h3>
                    <p>${error}</p>
                    <button class="btn btn-primary" onclick="rankingManager.retryLoading('${category}')">
                        🔄 Tentar Novamente
                    </button>
                </div>
            `;
        }
    }

    retryLoading(category) {
        this.loadRankingData(category);
    }

    refreshRankings() {
        // Limpar cache
        this.rankings = {};
        
        // Recarregar ranking atual
        this.loadRankingData(this.currentTab);
    }

    shouldRefreshRanking(category) {
        const ranking = this.rankings[category];
        if (!ranking) return true;
        
        // Atualizar a cada 5 minutos
        const fiveMinutes = 5 * 60 * 1000;
        return (Date.now() - ranking.lastUpdated) > fiveMinutes;
    }

    setupRealTimeUpdates() {
        // Simular atualizações em tempo real
        setInterval(() => {
            if (this.rankings[this.currentTab]) {
                this.simulateRealTimeUpdate();
            }
        }, 30000); // A cada 30 segundos
    }

    simulateRealTimeUpdate() {
        // Simular mudança de posição
        const rankingData = this.rankings[this.currentTab].data;
        if (rankingData.length > 0) {
            // Simular pequenas mudanças nos pontos
            rankingData.forEach(user => {
                if (Math.random() > 0.8) { // 20% de chance
                    user.points += Math.floor(Math.random() * 10) + 1;
                }
            });
            
            // Reordenar
            rankingData.sort((a, b) => b.points - a.points);
            rankingData.forEach((user, index) => {
                user.position = index + 1;
            });
            
            // Re-renderizar se houver mudanças significativas
            this.renderRanking(this.currentTab);
        }
    }

    challengeUser(username) {
        if (window.app && window.app.showNotification) {
            window.app.showNotification(`⚔️ Desafiando ${username} para uma batalha!`, 'info');
        }
        
        // Aqui você pode abrir um modal para criar o desafio
        console.log(`Desafiando usuário: ${username}`);
    }

    viewUserProfile(username) {
        if (window.app && window.app.showNotification) {
            window.app.showNotification(`👤 Visualizando perfil de ${username}`, 'info');
        }
        
        // Aqui você pode abrir um modal com o perfil do usuário
        console.log(`Visualizando perfil: ${username}`);
    }



    updateURL() {
        // Atualizar URL com a tab atual
        const url = new URL(window.location);
        url.hash = `#ranking-${this.currentTab}`;
        window.history.replaceState(null, null, url);
    }

    getCurrentRanking() {
        return this.rankings[this.currentTab]?.data || [];
    }

    getUserPosition(username) {
        const ranking = this.getCurrentRanking();
        const user = ranking.find(u => u.username === username);
        return user ? user.position : null;
    }
}

// Inicializar gerenciador de ranking
let rankingManager;
document.addEventListener('DOMContentLoaded', () => {
    rankingManager = new RankingManager();
});

// Adicionar estilos para o ranking
const rankingStyles = document.createElement('style');
rankingStyles.textContent = `
    .ranking-loading,
    .ranking-error,
    .empty-ranking {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
    }
    
    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .error-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
    
    .empty-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }
    
    .ranking-item {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .ranking-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .ranking-item.gold {
        background: linear-gradient(135deg, #ffd700, #ffed4e);
        border: 2px solid #ffc107;
    }
    
    .ranking-item.silver {
        background: linear-gradient(135deg, #c0c0c0, #e8e8e8);
        border: 2px solid #6c757d;
    }
    
    .ranking-item.bronze {
        background: linear-gradient(135deg, #cd7f32, #daa520);
        border: 2px solid #fd7e14;
    }
    
    .ranking-item.expanded {
        margin-bottom: 1rem;
    }
    
    .ranking-item-details {
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
        padding: 1.5rem;
        margin-top: 1rem;
        opacity: 0;
        transform: translateY(-10px);
        transition: all 0.2s ease;
    }
    
    .details-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
    
    .detail-section h4 {
        margin-bottom: 1rem;
        color: #333;
        font-size: 1.1rem;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem;
        background: white;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    .stat-label {
        font-size: 0.9rem;
        color: #6c757d;
    }
    
    .stat-value {
        font-weight: 700;
        color: #667eea;
    }
    
    .badges-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .badge {
        background: #667eea;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .ranking-actions {
        display: flex;
        gap: 0.5rem;
        flex-direction: column;
    }
    
    .btn-small {
        padding: 6px 12px;
        font-size: 12px;
        min-height: 32px;
    }
    
    .user-details {
        display: flex;
        gap: 1rem;
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }
    
    .region, .age {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .points-value {
        font-size: 1.5rem;
        font-weight: 900;
        color: #28a745;
    }
    
    .points-label {
        font-size: 0.8rem;
        color: #6c757d;
        text-align: center;
    }
    
    @media (max-width: 768px) {
        .details-content {
            grid-template-columns: 1fr;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .ranking-actions {
            flex-direction: row;
        }
    }
`;
document.head.appendChild(rankingStyles);
