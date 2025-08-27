/**
 * FIT BATTLE - Autenticação
 * Sistema de Login e Registro
 */

class AuthManager {
    constructor() {
        this.token = localStorage.getItem('fitBattleToken');
        this.user = JSON.parse(localStorage.getItem('fitBattleUser') || 'null');
        this.init();
    }

    init() {
        this.setupAuthForms();
        this.checkAuthStatus();
    }

    setupAuthForms() {
        // Formulário de login
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }

        // Formulário de registro
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', (e) => this.handleRegister(e));
        }

        // Validação em tempo real
        this.setupRealTimeValidation();
    }

    setupRealTimeValidation() {
        // Validação de email
        const emailInputs = document.querySelectorAll('input[type="email"]');
        emailInputs.forEach(input => {
            input.addEventListener('blur', () => this.validateEmail(input));
            input.addEventListener('input', () => this.clearValidation(input));
        });

        // Validação de senha
        const passwordInputs = document.querySelectorAll('input[type="password"]');
        passwordInputs.forEach(input => {
            input.addEventListener('blur', () => this.validatePassword(input));
            input.addEventListener('input', () => this.clearValidation(input));
        });

        // Validação de username
        const usernameInputs = document.querySelectorAll('input[name*="username"], input[id*="username"]');
        usernameInputs.forEach(input => {
            input.addEventListener('blur', () => this.validateUsername(input));
            input.addEventListener('input', () => this.clearValidation(input));
        });
    }

    validateEmail(input) {
        const email = input.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (!email) {
            this.showFieldError(input, 'Email é obrigatório');
            return false;
        }
        
        if (!emailRegex.test(email)) {
            this.showFieldError(input, 'Email inválido');
            return false;
        }
        
        this.showFieldSuccess(input);
        return true;
    }

    validatePassword(input) {
        const password = input.value;
        
        if (!password) {
            this.showFieldError(input, 'Senha é obrigatória');
            return false;
        }
        
        if (password.length < 6) {
            this.showFieldError(input, 'Senha deve ter pelo menos 6 caracteres');
            return false;
        }
        
        this.showFieldSuccess(input);
        return true;
    }

    validateUsername(input) {
        const username = input.value.trim();
        
        if (!username) {
            this.showFieldError(input, 'Nome de usuário é obrigatório');
            return false;
        }
        
        if (username.length < 3) {
            this.showFieldError(input, 'Nome de usuário deve ter pelo menos 3 caracteres');
            return false;
        }
        
        if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            this.showFieldError(input, 'Nome de usuário deve conter apenas letras, números e underscore');
            return false;
        }
        
        this.showFieldSuccess(input);
        return true;
    }

    showFieldError(input, message) {
        this.clearFieldStatus(input);
        input.classList.add('error');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        errorDiv.style.cssText = `
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        `;
        
        input.parentNode.appendChild(errorDiv);
    }

    showFieldSuccess(input) {
        this.clearFieldStatus(input);
        input.classList.add('success');
        
        const successDiv = document.createElement('div');
        successDiv.className = 'field-success';
        successDiv.innerHTML = '✅';
        successDiv.style.cssText = `
            color: #28a745;
            font-size: 1rem;
            margin-top: 0.25rem;
            display: block;
        `;
        
        input.parentNode.appendChild(successDiv);
    }

    clearFieldStatus(input) {
        input.classList.remove('error', 'success');
        const existingError = input.parentNode.querySelector('.field-error');
        const existingSuccess = input.parentNode.querySelector('.field-success');
        
        if (existingError) existingError.remove();
        if (existingSuccess) existingSuccess.remove();
    }

    clearValidation(input) {
        this.clearFieldStatus(input);
    }

    async handleLogin(e) {
        e.preventDefault();
        
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;
        
        // Validar campos
        if (!this.validateEmail(document.getElementById('loginEmail')) ||
            !this.validatePassword(document.getElementById('loginPassword'))) {
            return;
        }
        
        try {
            this.showLoadingState('loginForm');
            await this.performLogin(email, password);
        } catch (error) {
            this.showAuthError(error.message);
        }
    }

    async handleRegister(e) {
        e.preventDefault();
        
        const username = document.getElementById('registerUsername').value;
        const email = document.getElementById('registerEmail').value;
        const password = document.getElementById('registerPassword').value;
        const fullName = document.getElementById('registerFullName').value;
        
        // Validar todos os campos
        if (!this.validateUsername(document.getElementById('registerUsername')) ||
            !this.validateEmail(document.getElementById('registerEmail')) ||
            !this.validatePassword(document.getElementById('registerPassword')) ||
            !fullName.trim()) {
            return;
        }
        
        try {
            this.showLoadingState('registerForm');
            await this.performRegister({ username, email, password, fullName });
        } catch (error) {
            this.showAuthError(error.message);
        }
    }

    async performLogin(email, password) {
        try {
            // Simular chamada de API
            const response = await this.simulateApiCall('/api/auth/login', {
                email,
                password
            });
            
            if (response.success) {
                this.setAuthData(response.token, response.user);
                this.showAuthSuccess('Login realizado com sucesso! 🎉');
                this.redirectAfterAuth();
            } else {
                throw new Error(response.message || 'Erro no login');
            }
        } catch (error) {
            throw new Error('Credenciais inválidas. Tente novamente.');
        }
    }

    async performRegister(userData) {
        try {
            // Simular chamada de API
            const response = await this.simulateApiCall('/api/auth/register', userData);
            
            if (response.success) {
                this.setAuthData(response.token, response.user);
                this.showAuthSuccess('Cadastro realizado com sucesso! 🚀');
                this.redirectAfterAuth();
            } else {
                throw new Error(response.message || 'Erro no cadastro');
            }
        } catch (error) {
            throw new Error('Erro ao criar conta. Tente novamente.');
        }
    }

    async simulateApiCall(endpoint, data) {
        // Simular delay de rede
        await new Promise(resolve => setTimeout(resolve, 1500));
        
        // Simular validações
        if (endpoint.includes('login')) {
            if (data.email === 'admin@fitbattle.com' && data.password === 'password') {
                return {
                    success: true,
                    token: 'mock_jwt_token_' + Date.now(),
                    user: {
                        id: 1,
                        username: 'admin',
                        email: data.email,
                        fullName: 'Administrador',
                        totalPoints: 2500,
                        currentLevel: 25,
                        currentStreak: 7,
                        profileImage: null
                    }
                };
            } else {
                return {
                    success: false,
                    message: 'Email ou senha incorretos'
                };
            }
        } else if (endpoint.includes('register')) {
            // Simular verificação de username/email único
            if (data.username === 'admin') {
                return {
                    success: false,
                    message: 'Nome de usuário já existe'
                };
            }
            
            return {
                success: true,
                token: 'mock_jwt_token_' + Date.now(),
                user: {
                    id: Date.now(),
                    username: data.username,
                    email: data.email,
                    fullName: data.fullName,
                    totalPoints: 0,
                    currentLevel: 1,
                    currentStreak: 0,
                    profileImage: null
                }
            };
        }
        
        throw new Error('Endpoint não encontrado');
    }

    setAuthData(token, user) {
        this.token = token;
        this.user = user;
        
        localStorage.setItem('fitBattleToken', token);
        localStorage.setItem('fitBattleUser', JSON.stringify(user));
        
        // Disparar evento de autenticação
        window.dispatchEvent(new CustomEvent('auth:login', { detail: { user } }));
    }

    clearAuthData() {
        this.token = null;
        this.user = null;
        
        localStorage.removeItem('fitBattleToken');
        localStorage.removeItem('fitBattleUser');
        
        // Disparar evento de logout
        window.dispatchEvent(new CustomEvent('auth:logout'));
    }

    checkAuthStatus() {
        if (this.token && this.user) {
            this.updateUIForAuthenticatedUser();
        } else {
            this.updateUIForUnauthenticatedUser();
        }
    }

    updateUIForAuthenticatedUser() {
        // Atualizar botões de autenticação
        const authContainer = document.querySelector('.nav-auth');
        if (authContainer) {
            authContainer.innerHTML = `
                <div class="user-menu">
                    <button class="btn btn-outline user-profile-btn">
                        <span class="user-avatar">👤</span>
                        <span class="username">${this.user.username}</span>
                        <span class="dropdown-arrow">▼</span>
                    </button>
                    <div class="user-dropdown">
                        <a href="#profile" class="dropdown-item">👤 Meu Perfil</a>
                        <a href="#dashboard" class="dropdown-item">📊 Dashboard</a>
                        <a href="#settings" class="dropdown-item">⚙️ Configurações</a>
                        <div class="dropdown-divider"></div>
                        <button class="dropdown-item logout-btn" onclick="authManager.logout()">🚪 Sair</button>
                    </div>
                </div>
            `;
            
            // Configurar dropdown do usuário
            this.setupUserDropdown();
        }
        
        // Atualizar estatísticas
        this.updateUserStats();
        
        // Fechar modais de autenticação
        this.closeAuthModals();
    }

    updateUIForUnauthenticatedUser() {
        const authContainer = document.querySelector('.nav-auth');
        if (authContainer) {
            authContainer.innerHTML = `
                <button class="btn btn-outline" id="loginBtn">🔑 Login</button>
                <button class="btn btn-primary" id="registerBtn">🚀 Cadastrar</button>
            `;
            
            // Reconfigurar event listeners
            document.getElementById('loginBtn')?.addEventListener('click', () => this.showModal('loginModal'));
            document.getElementById('registerBtn')?.addEventListener('click', () => this.showModal('registerModal'));
        }
    }

    setupUserDropdown() {
        const userProfileBtn = document.querySelector('.user-profile-btn');
        const userDropdown = document.querySelector('.user-dropdown');
        
        if (userProfileBtn && userDropdown) {
            userProfileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdown.classList.toggle('show');
            });
            
            // Fechar dropdown ao clicar fora
            document.addEventListener('click', () => {
                userDropdown.classList.remove('show');
            });
        }
    }

    updateUserStats() {
        if (this.user) {
            // Atualizar estatísticas na página
            const statCards = document.querySelectorAll('.stat-card');
            if (statCards.length >= 3) {
                statCards[0].querySelector('.stat-value').textContent = this.user.totalPoints + '+';
                statCards[1].querySelector('.stat-value').textContent = this.user.currentLevel;
                statCards[2].querySelector('.stat-value').textContent = this.user.currentStreak;
            }
        }
    }

    closeAuthModals() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.style.display = 'none';
        });
        document.body.style.overflow = 'auto';
    }

    showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Limpar formulários
            this.clearForms(modalId);
        }
    }

    clearForms(modalId) {
        const form = document.getElementById(modalId.replace('Modal', 'Form'));
        if (form) {
            form.reset();
            // Limpar validações
            form.querySelectorAll('input').forEach(input => {
                this.clearFieldStatus(input);
            });
        }
    }

    showLoadingState(formId) {
        const form = document.getElementById(formId);
        if (form) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner"></span> Carregando...';
            }
        }
    }

    hideLoadingState(formId) {
        const form = document.getElementById(formId);
        if (form) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = false;
                if (formId === 'loginForm') {
                    submitBtn.innerHTML = '🔑 Entrar';
                } else {
                    submitBtn.innerHTML = '🚀 Cadastrar';
                }
            }
        }
    }

    showAuthSuccess(message) {
        this.showNotification(message, 'success');
        this.hideLoadingState('loginForm');
        this.hideLoadingState('registerForm');
    }

    showAuthError(message) {
        this.showNotification(message, 'error');
        this.hideLoadingState('loginForm');
        this.hideLoadingState('registerForm');
    }

    showNotification(message, type = 'info') {
        // Usar o sistema de notificação da aplicação principal
        if (window.app && window.app.showNotification) {
            window.app.showNotification(message, type);
        } else {
            // Fallback para notificação simples
            alert(message);
        }
    }

    redirectAfterAuth() {
        // Redirecionar para dashboard ou página principal
        setTimeout(() => {
            window.location.hash = '#home';
            location.reload();
        }, 1000);
    }

    logout() {
        this.clearAuthData();
        this.updateUIForUnauthenticatedUser();
        this.showNotification('👋 Logout realizado com sucesso!', 'success');
        
        // Redirecionar para página inicial
        setTimeout(() => {
            window.location.hash = '#home';
            location.reload();
        }, 1000);
    }

    isAuthenticated() {
        return !!(this.token && this.user);
    }

    getCurrentUser() {
        return this.user;
    }

    getToken() {
        return this.token;
    }
}

// Inicializar gerenciador de autenticação
let authManager;
document.addEventListener('DOMContentLoaded', () => {
    authManager = new AuthManager();
});

// Adicionar estilos para o dropdown do usuário
const userMenuStyles = document.createElement('style');
userMenuStyles.textContent = `
    .user-menu {
        position: relative;
    }
    
    .user-profile-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        position: relative;
    }
    
    .user-avatar {
        font-size: 1.2rem;
    }
    
    .username {
        font-weight: 600;
        color: #667eea;
    }
    
    .dropdown-arrow {
        font-size: 0.8rem;
        transition: transform 0.2s;
    }
    
    .user-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        min-width: 200px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.2s;
        z-index: 1000;
        margin-top: 0.5rem;
    }
    
    .user-dropdown.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .dropdown-item {
        display: block;
        padding: 0.75rem 1rem;
        color: #333;
        text-decoration: none;
        transition: background-color 0.2s;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        cursor: pointer;
        font-size: 0.9rem;
    }
    
    .dropdown-item:hover {
        background-color: #f8f9fa;
    }
    
    .dropdown-divider {
        height: 1px;
        background-color: #e9ecef;
        margin: 0.5rem 0;
    }
    
    .logout-btn {
        color: #dc3545;
    }
    
    .logout-btn:hover {
        background-color: #f8d7da;
    }
    
    .spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 1s ease-in-out infinite;
        margin-right: 0.5rem;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    .field-error,
    .field-success {
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    input.error {
        border-color: #dc3545;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    }
    
    input.success {
        border-color: #28a745;
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
    }
`;
document.head.appendChild(userMenuStyles);
