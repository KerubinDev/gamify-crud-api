/**
 * Autenticação - Sistema Vida Equilibrada
 * Gerencia login, registro e autenticação de usuários
 */

// Sobrescreve os handlers de formulário do app.js
document.addEventListener('DOMContentLoaded', function() {
    setupAuthHandlers();
});

/**
 * Configura os handlers de autenticação
 */
function setupAuthHandlers() {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    
    if (loginForm) {
        loginForm.removeEventListener('submit', handleLogin);
        loginForm.addEventListener('submit', handleLogin);
    }
    
    if (registerForm) {
        registerForm.removeEventListener('submit', handleRegister);
        registerForm.addEventListener('submit', handleRegister);
    }
}

/**
 * Handler do formulário de login
 */
async function handleLogin(e) {
    e.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    // Validação básica
    if (!email || !password) {
        showNotification('Por favor, preencha todos os campos', 'error');
        return;
    }
    
    try {
        // Mostra loading
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Entrando...';
        submitBtn.disabled = true;
        
        // Faz requisição de login
        const response = await apiRequest('/auth/login', {
            method: 'POST',
            body: JSON.stringify({
                email: email,
                password: password
            })
        });
        
        if (response.status === 'success') {
            // Salva token e dados do usuário
            localStorage.setItem('authToken', response.data.token);
            localStorage.setItem('userData', JSON.stringify(response.data.usuario));
            
            // Atualiza estado global
            currentUser = response.data.usuario;
            isAuthenticated = true;
            
            // Mostra interface autenticada
            showAuthenticatedUI();
            
            // Carrega dados iniciais
            loadDashboardData();
            
            showNotification('Login realizado com sucesso! Bem-vindo, ' + currentUser.nome, 'success');
        }
        
    } catch (error) {
        console.error('Erro no login:', error);
        showNotification(error.message || 'Erro ao fazer login', 'error');
    } finally {
        // Restaura botão
        const submitBtn = e.target.querySelector('button[type="submit"]');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
}

/**
 * Handler do formulário de registro
 */
async function handleRegister(e) {
    e.preventDefault();
    
    const name = document.getElementById('registerName').value;
    const email = document.getElementById('registerEmail').value;
    const password = document.getElementById('registerPassword').value;
    const passwordConfirm = document.getElementById('registerPasswordConfirm').value;
    
    // Validação básica
    if (!name || !email || !password || !passwordConfirm) {
        showNotification('Por favor, preencha todos os campos', 'error');
        return;
    }
    
    if (password !== passwordConfirm) {
        showNotification('As senhas não coincidem', 'error');
        return;
    }
    
    if (password.length < 6) {
        showNotification('A senha deve ter pelo menos 6 caracteres', 'error');
        return;
    }
    
    // Validação de email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showNotification('Por favor, insira um email válido', 'error');
        return;
    }
    
    try {
        // Mostra loading
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Cadastrando...';
        submitBtn.disabled = true;
        
        // Faz requisição de registro
        const response = await apiRequest('/auth/register', {
            method: 'POST',
            body: JSON.stringify({
                nome: name,
                email: email,
                password: password
            })
        });
        
        if (response.status === 'success') {
            showNotification('Cadastro realizado com sucesso! Faça login para continuar.', 'success');
            
            // Limpa formulário
            e.target.reset();
            
            // Muda para aba de login
            const loginTab = document.querySelector('.tab-btn[data-tab="login"]');
            if (loginTab) {
                loginTab.click();
            }
            
            // Preenche email no formulário de login
            const loginEmail = document.getElementById('loginEmail');
            if (loginEmail) {
                loginEmail.value = email;
            }
        }
        
    } catch (error) {
        console.error('Erro no registro:', error);
        showNotification(error.message || 'Erro ao fazer cadastro', 'error');
    } finally {
        // Restaura botão
        const submitBtn = e.target.querySelector('button[type="submit"]');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
}

/**
 * Verifica se o token é válido
 */
async function validateToken() {
    const token = localStorage.getItem('authToken');
    
    if (!token) {
        return false;
    }
    
    try {
        const response = await apiRequest('/auth/profile', {
            method: 'GET'
        });
        
        if (response.status === 'success') {
            // Atualiza dados do usuário
            currentUser = response.data;
            localStorage.setItem('userData', JSON.stringify(response.data));
            return true;
        }
        
        return false;
    } catch (error) {
        console.error('Erro ao validar token:', error);
        return false;
    }
}

/**
 * Atualiza dados do usuário
 */
async function updateUserData() {
    if (!isAuthenticated) return;
    
    try {
        const response = await apiRequest('/usuarios/' + currentUser.id, {
            method: 'GET'
        });
        
        if (response.status === 'success') {
            currentUser = response.data;
            localStorage.setItem('userData', JSON.stringify(response.data));
            updateUserInfo();
        }
    } catch (error) {
        console.error('Erro ao atualizar dados do usuário:', error);
    }
}

/**
 * Recupera senha
 */
async function recoverPassword(email) {
    try {
        const response = await apiRequest('/auth/recover', {
            method: 'POST',
            body: JSON.stringify({
                email: email
            })
        });
        
        if (response.status === 'success') {
            showNotification('Email de recuperação enviado!', 'success');
            return true;
        }
        
        return false;
    } catch (error) {
        console.error('Erro ao recuperar senha:', error);
        showNotification(error.message || 'Erro ao enviar email de recuperação', 'error');
        return false;
    }
}

/**
 * Redefine senha
 */
async function resetPassword(token, newPassword) {
    try {
        const response = await apiRequest('/auth/reset', {
            method: 'POST',
            body: JSON.stringify({
                token: token,
                password: newPassword
            })
        });
        
        if (response.status === 'success') {
            showNotification('Senha redefinida com sucesso!', 'success');
            return true;
        }
        
        return false;
    } catch (error) {
        console.error('Erro ao redefinir senha:', error);
        showNotification(error.message || 'Erro ao redefinir senha', 'error');
        return false;
    }
}

/**
 * Altera senha do usuário logado
 */
async function changePassword(currentPassword, newPassword) {
    if (!isAuthenticated) {
        showNotification('Você precisa estar logado para alterar a senha', 'error');
        return false;
    }
    
    try {
        const response = await apiRequest('/auth/change-password', {
            method: 'POST',
            body: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword
            })
        });
        
        if (response.status === 'success') {
            showNotification('Senha alterada com sucesso!', 'success');
            return true;
        }
        
        return false;
    } catch (error) {
        console.error('Erro ao alterar senha:', error);
        showNotification(error.message || 'Erro ao alterar senha', 'error');
        return false;
    }
}

/**
 * Atualiza perfil do usuário
 */
async function updateProfile(profileData) {
    if (!isAuthenticated) {
        showNotification('Você precisa estar logado para atualizar o perfil', 'error');
        return false;
    }
    
    try {
        const response = await apiRequest('/usuarios/' + currentUser.id, {
            method: 'PUT',
            body: JSON.stringify(profileData)
        });
        
        if (response.status === 'success') {
            // Atualiza dados do usuário
            currentUser = response.data;
            localStorage.setItem('userData', JSON.stringify(response.data));
            updateUserInfo();
            
            showNotification('Perfil atualizado com sucesso!', 'success');
            return true;
        }
        
        return false;
    } catch (error) {
        console.error('Erro ao atualizar perfil:', error);
        showNotification(error.message || 'Erro ao atualizar perfil', 'error');
        return false;
    }
}

/**
 * Função de logout (sobrescreve a do app.js)
 */
function logout() {
    // Remove dados de autenticação
    localStorage.removeItem('authToken');
    localStorage.removeItem('userData');
    
    // Reseta estado global
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
 * Verifica se o usuário tem permissão para uma ação
 */
function hasPermission(permission) {
    if (!isAuthenticated || !currentUser) {
        return false;
    }
    
    // Implementar sistema de permissões se necessário
    return true;
}

/**
 * Middleware de autenticação para requisições
 */
function requireAuth() {
    if (!isAuthenticated) {
        showNotification('Você precisa estar logado para acessar esta funcionalidade', 'error');
        showAuthModal();
        return false;
    }
    return true;
}

// Exporta funções para uso em outros módulos
window.auth = {
    handleLogin,
    handleRegister,
    validateToken,
    updateUserData,
    recoverPassword,
    resetPassword,
    changePassword,
    updateProfile,
    logout,
    hasPermission,
    requireAuth
};
