/**
 * App Principal - Sistema Vida Equilibrada
 * Funcionalidades básicas da aplicação
 */

// Configurações da API
const API_BASE_URL = '/gamify-crud-api/api';
const API_ENDPOINTS = {
    auth: '/auth',
    usuarios: '/usuarios',
    habitos: '/habitos',
    conquistas: '/conquistas',
    ranking: '/ranking',
    estatisticas: '/estatisticas'
};

// Estado global da aplicação
let currentUser = null;
let isAuthenticated = false;

// Inicialização da aplicação
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Inicializa a aplicação
 */
function initializeApp() {
    setupNavigation();
    setupAuthTabs();
    setupModalHandlers();
    checkAuthentication();
    setupFormHandlers();
}

/**
 * Configura a navegação entre seções
 */
function setupNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class de todos os links
            navLinks.forEach(l => l.classList.remove('active'));
            
            // Adiciona active class ao link clicado
            this.classList.add('active');
            
            // Mostra a seção correspondente
            const sectionId = this.getAttribute('data-section');
            showSection(sectionId);
        });
    });
}

/**
 * Mostra uma seção específica
 */
function showSection(sectionId) {
    // Esconde todas as seções
    const sections = document.querySelectorAll('.section');
    sections.forEach(section => section.classList.remove('active'));
    
    // Mostra a seção selecionada
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
        targetSection.classList.add('active');
        
        // Carrega dados específicos da seção
        loadSectionData(sectionId);
    }
}

/**
 * Carrega dados específicos de cada seção
 */
function loadSectionData(sectionId) {
    if (!isAuthenticated) return;
    
    switch (sectionId) {
        case 'dashboard':
            loadDashboardData();
            break;
        case 'habitos':
            loadHabitsData();
            break;
        case 'ranking':
            loadRankingData();
            break;
        case 'conquistas':
            loadBadgesData();
            break;
        case 'perfil':
            loadProfileData();
            break;
    }
}

/**
 * Configura as abas de autenticação
 */
function setupAuthTabs() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const authForms = document.querySelectorAll('.auth-form');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            
            // Remove active class de todos os botões e formulários
            tabBtns.forEach(b => b.classList.remove('active'));
            authForms.forEach(f => f.classList.remove('active'));
            
            // Adiciona active class ao botão clicado
            this.classList.add('active');
            
            // Mostra o formulário correspondente
            const targetForm = document.getElementById(tabName + 'Form');
            if (targetForm) {
                targetForm.classList.add('active');
            }
        });
    });
}

/**
 * Configura handlers dos modais
 */
function setupModalHandlers() {
    // Fechar modais ao clicar fora
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target.id);
        }
    });
    
    // Fechar modais com ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.active');
            if (openModal) {
                closeModal(openModal.id);
            }
        }
    });
}

/**
 * Configura handlers dos formulários
 */
function setupFormHandlers() {
    // Formulário de login
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    // Formulário de registro
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }
    
    // Formulário de criação de hábito
    const createHabitForm = document.getElementById('createHabitForm');
    if (createHabitForm) {
        createHabitForm.addEventListener('submit', handleCreateHabit);
    }
    
    // Formulário de edição de perfil
    const editProfileForm = document.getElementById('editProfileForm');
    if (editProfileForm) {
        editProfileForm.addEventListener('submit', handleEditProfile);
    }
}

/**
 * Verifica se o usuário está autenticado
 */
function checkAuthentication() {
    const token = localStorage.getItem('authToken');
    const userData = localStorage.getItem('userData');
    
    if (token && userData) {
        try {
            currentUser = JSON.parse(userData);
            isAuthenticated = true;
            showAuthenticatedUI();
            loadDashboardData();
        } catch (error) {
            console.error('Erro ao carregar dados do usuário:', error);
            logout();
        }
    } else {
        showAuthModal();
    }
}

/**
 * Mostra a interface para usuários autenticados
 */
function showAuthenticatedUI() {
    // Esconde modal de autenticação
    const authModal = document.getElementById('authModal');
    if (authModal) {
        authModal.style.display = 'none';
    }
    
    // Mostra informações do usuário
    const userInfo = document.getElementById('userInfo');
    if (userInfo) {
        userInfo.style.display = 'flex';
    }
    
    // Atualiza dados do usuário no header
    updateUserInfo();
}

/**
 * Mostra o modal de autenticação
 */
function showAuthModal() {
    const authModal = document.getElementById('authModal');
    if (authModal) {
        authModal.style.display = 'flex';
        authModal.classList.add('active');
    }
}

/**
 * Atualiza informações do usuário no header
 */
function updateUserInfo() {
    if (!currentUser) return;
    
    const userName = document.getElementById('userName');
    const userLevel = document.getElementById('userLevel');
    
    if (userName) {
        userName.textContent = currentUser.nome;
    }
    
    if (userLevel) {
        const level = Math.floor(currentUser.pontos / 100) + 1;
        userLevel.textContent = `Nível ${level}`;
    }
}

/**
 * Função de logout
 */
function logout() {
    localStorage.removeItem('authToken');
    localStorage.removeItem('userData');
    currentUser = null;
    isAuthenticated = false;
    
    // Esconde informações do usuário
    const userInfo = document.getElementById('userInfo');
    if (userInfo) {
        userInfo.style.display = 'none';
    }
    
    // Mostra modal de autenticação
    showAuthModal();
    
    // Limpa dados das seções
    clearAllSections();
    
    showNotification('Logout realizado com sucesso!', 'success');
}

/**
 * Limpa dados de todas as seções
 */
function clearAllSections() {
    const sections = ['dashboard', 'habitos', 'ranking', 'conquistas', 'perfil'];
    
    sections.forEach(sectionId => {
        const section = document.getElementById(sectionId);
        if (section) {
            const loadingElements = section.querySelectorAll('.loading');
            loadingElements.forEach(el => {
                el.textContent = 'Faça login para ver os dados';
            });
        }
    });
}

/**
 * Funções de modal
 */
function showCreateHabitModal() {
    const modal = document.getElementById('createHabitModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('active');
    }
}

function showEditProfileModal() {
    const modal = document.getElementById('editProfileModal');
    if (modal && currentUser) {
        // Preenche formulário com dados atuais
        const editName = document.getElementById('editName');
        const editEmail = document.getElementById('editEmail');
        
        if (editName) editName.value = currentUser.nome;
        if (editEmail) editEmail.value = currentUser.email;
        
        modal.style.display = 'flex';
        modal.classList.add('active');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('active');
        
        // Limpa formulários
        const forms = modal.querySelectorAll('form');
        forms.forEach(form => form.reset());
    }
}

/**
 * Sistema de notificações
 */
function showNotification(message, type = 'info', duration = 5000) {
    const container = document.getElementById('notificationContainer');
    if (!container) return;
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    container.appendChild(notification);
    
    // Remove a notificação após o tempo especificado
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, duration);
}

/**
 * Funções de requisição HTTP
 */
async function apiRequest(endpoint, options = {}) {
    const token = localStorage.getItem('authToken');
    
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            ...(token && { 'Authorization': `Bearer ${token}` })
        }
    };
    
    const config = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...options.headers
        }
    };
    
    try {
        const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Erro na requisição');
        }
        
        return data;
    } catch (error) {
        console.error('Erro na API:', error);
        throw error;
    }
}

/**
 * Funções de carregamento de dados das seções
 */
function loadDashboardData() {
    if (!isAuthenticated) return;
    
    // Carrega estatísticas do usuário
    loadUserStats();
    
    // Carrega hábitos de hoje
    loadTodayHabits();
    
    // Carrega top ranking
    loadTopRanking();
    
    // Carrega conquistas recentes
    loadRecentBadges();
}

function loadUserStats() {
    // Atualiza cards de estatísticas
    const userPoints = document.getElementById('userPoints');
    const userStreak = document.getElementById('userStreak');
    const userLevel = document.getElementById('userLevel');
    const userBadges = document.getElementById('userBadges');
    const levelProgress = document.getElementById('levelProgress');
    const levelProgressText = document.getElementById('levelProgressText');
    
    if (currentUser) {
        if (userPoints) userPoints.textContent = currentUser.pontos || 0;
        if (userStreak) userStreak.textContent = currentUser.streak_atual || 0;
        
        const level = Math.floor((currentUser.pontos || 0) / 100) + 1;
        if (userLevel) userLevel.textContent = level;
        
        // Progresso do nível
        const progress = ((currentUser.pontos || 0) % 100);
        if (levelProgress) {
            levelProgress.style.width = `${progress}%`;
        }
        if (levelProgressText) {
            levelProgressText.textContent = `${progress} / 100 pontos para o próximo nível`;
        }
    }
}

function loadTodayHabits() {
    const todayHabits = document.getElementById('todayHabits');
    if (!todayHabits) return;
    
    todayHabits.innerHTML = '<p class="loading">Carregando hábitos...</p>';
    
    // Implementar carregamento de hábitos de hoje
    // Por enquanto, mostra mensagem de placeholder
    setTimeout(() => {
        todayHabits.innerHTML = '<p>Nenhum hábito para hoje</p>';
    }, 1000);
}

function loadTopRanking() {
    const topRanking = document.getElementById('topRanking');
    if (!topRanking) return;
    
    topRanking.innerHTML = '<p class="loading">Carregando ranking...</p>';
    
    // Implementar carregamento do top ranking
    // Por enquanto, mostra mensagem de placeholder
    setTimeout(() => {
        topRanking.innerHTML = '<p>Ranking em desenvolvimento</p>';
    }, 1000);
}

function loadRecentBadges() {
    const recentBadges = document.getElementById('recentBadges');
    if (!recentBadges) return;
    
    recentBadges.innerHTML = '<p class="loading">Carregando conquistas...</p>';
    
    // Implementar carregamento de conquistas recentes
    // Por enquanto, mostra mensagem de placeholder
    setTimeout(() => {
        recentBadges.innerHTML = '<p>Nenhuma conquista recente</p>';
    }, 1000);
}

function loadHabitsData() {
    const habitsList = document.getElementById('habitsList');
    if (!habitsList) return;
    
    habitsList.innerHTML = '<p class="loading">Carregando hábitos...</p>';
    
    // Implementar carregamento de hábitos
    // Por enquanto, mostra mensagem de placeholder
    setTimeout(() => {
        habitsList.innerHTML = '<p>Nenhum hábito encontrado</p>';
    }, 1000);
}

function loadRankingData() {
    const rankingList = document.getElementById('rankingList');
    if (!rankingList) return;
    
    rankingList.innerHTML = '<p class="loading">Carregando ranking...</p>';
    
    // Implementar carregamento do ranking
    // Por enquanto, mostra mensagem de placeholder
    setTimeout(() => {
        rankingList.innerHTML = '<p>Ranking em desenvolvimento</p>';
    }, 1000);
}

function loadBadgesData() {
    const badgesList = document.getElementById('badgesList');
    if (!badgesList) return;
    
    badgesList.innerHTML = '<p class="loading">Carregando conquistas...</p>';
    
    // Implementar carregamento de badges
    // Por enquanto, mostra mensagem de placeholder
    setTimeout(() => {
        badgesList.innerHTML = '<p>Conquistas em desenvolvimento</p>';
    }, 1000);
}

function loadProfileData() {
    const profileStats = document.getElementById('profileStats');
    const activityHistory = document.getElementById('activityHistory');
    
    if (profileStats) {
        profileStats.innerHTML = '<p class="loading">Carregando estatísticas...</p>';
    }
    
    if (activityHistory) {
        activityHistory.innerHTML = '<p class="loading">Carregando histórico...</p>';
    }
    
    // Implementar carregamento de dados do perfil
    // Por enquanto, mostra mensagem de placeholder
    setTimeout(() => {
        if (profileStats) {
            profileStats.innerHTML = '<p>Estatísticas em desenvolvimento</p>';
        }
        if (activityHistory) {
            activityHistory.innerHTML = '<p>Histórico em desenvolvimento</p>';
        }
    }, 1000);
}

/**
 * Handlers de formulários (serão implementados nos arquivos específicos)
 */
function handleLogin(e) {
    e.preventDefault();
    // Implementado em auth.js
}

function handleRegister(e) {
    e.preventDefault();
    // Implementado em auth.js
}

function handleCreateHabit(e) {
    e.preventDefault();
    // Implementado em habits.js
}

function handleEditProfile(e) {
    e.preventDefault();
    // Implementado em profile.js
}

// Exporta funções para uso em outros módulos
window.app = {
    API_BASE_URL,
    API_ENDPOINTS,
    currentUser,
    isAuthenticated,
    showNotification,
    apiRequest,
    showSection,
    loadSectionData,
    logout,
    closeModal
};
