console.log('Config.js carregando...');

// Configuração da API
const API_CONFIG = {
    // URL base da API
    BASE_URL: '/gamify-crud-api/api',
    
    // Endpoints
    ENDPOINTS: {
        // Autenticação
        AUTH: '/auth',
        LOGIN: '/auth/login',
        REGISTER: '/auth/register',
        
        // Usuários
        USERS: '/usuarios',
        PROFILE: '/usuarios/profile',
        
        // Hábitos
        HABITS: '/habitos',
        HABITS_COMPLETE: (id) => `/habitos/${id}/complete`,
        
        // Badges
        BADGES: '/badges',
        BADGES_CONQUERED: '/badges/conquistadas',
        BADGES_CHECK: '/badges/check',
        
        // Ranking
        RANKING: '/ranking',
        
        // Estatísticas
        STATS: '/estatisticas'
    },
    
    // Headers padrão
    getDefaultHeaders() {
        const token = localStorage.getItem('token');
        return {
            'Content-Type': 'application/json',
            'Authorization': token ? `Bearer ${token}` : ''
        };
    },
    
    // Função para construir URL completa
    buildUrl(endpoint) {
        return this.BASE_URL + endpoint;
    }
};

// Exportar para uso global
window.API_CONFIG = API_CONFIG;

console.log('API_CONFIG definido:', API_CONFIG);
console.log('Verificando window.API_CONFIG:', window.API_CONFIG);

// Disparar evento customizado quando config estiver pronto
window.dispatchEvent(new CustomEvent('APIConfigReady', { detail: API_CONFIG }));
