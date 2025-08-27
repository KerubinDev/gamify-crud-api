/**
 * Sistema de Gerenciamento de Hábitos
 * Vida Equilibrada
 */

class HabitsManager {
    constructor() {
        this.habits = [];
        this.currentUser = null;
        this.init();
    }

    init() {
        this.loadCurrentUser();
        this.loadHabits();
        this.bindEvents();
    }

    loadCurrentUser() {
        const user = localStorage.getItem('currentUser');
        if (user) {
            this.currentUser = JSON.parse(user);
        }
    }

    async loadHabits() {
        try {
                    const response = await fetch(API_CONFIG.buildUrl(API_CONFIG.ENDPOINTS.HABITS), {
            headers: API_CONFIG.getDefaultHeaders()
        });
            
            if (response.ok) {
                this.habits = await response.json();
                this.renderHabits();
            }
        } catch (error) {
            console.error('Erro ao carregar hábitos:', error);
        }
    }

    renderHabits() {
        const container = document.getElementById('habits-container');
        if (!container) return;

        container.innerHTML = this.habits.map(habit => `
            <div class="habit-card" data-id="${habit.id}">
                <div class="habit-header">
                    <h3>${habit.titulo}</h3>
                    <span class="habit-category ${habit.categoria}">${habit.categoria}</span>
                </div>
                <p class="habit-description">${habit.descricao || 'Sem descrição'}</p>
                <div class="habit-info">
                    <span class="habit-points">${habit.pontos_base} pts</span>
                    <span class="habit-frequency">${habit.frequencia}</span>
                </div>
                <div class="habit-actions">
                    <button class="btn btn-success" onclick="habitsManager.completeHabit(${habit.id})">
                        ✅ Completar
                    </button>
                    <button class="btn btn-primary" onclick="habitsManager.editHabit(${habit.id})">
                        ✏️ Editar
                    </button>
                    <button class="btn btn-danger" onclick="habitsManager.deleteHabit(${habit.id})">
                        🗑️ Excluir
                    </button>
                </div>
            </div>
        `).join('');
    }

    async createHabit(habitData) {
        try {
                    const response = await fetch(API_CONFIG.buildUrl(API_CONFIG.ENDPOINTS.HABITS), {
            method: 'POST',
            headers: API_CONFIG.getDefaultHeaders(),
            body: JSON.stringify(habitData)
        });

            if (response.ok) {
                const newHabit = await response.json();
                this.habits.push(newHabit);
                this.renderHabits();
                this.showMessage('Hábito criado com sucesso!', 'success');
                return true;
            }
        } catch (error) {
            console.error('Erro ao criar hábito:', error);
            this.showMessage('Erro ao criar hábito', 'error');
        }
        return false;
    }

    async completeHabit(habitId) {
        try {
            const response = await fetch(`/api/habitos/${habitId}/complete`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            });

            if (response.ok) {
                const result = await response.json();
                this.showMessage(`Hábito completado! +${result.pontos_ganhos} pontos`, 'success');
                this.loadHabits(); // Recarregar para atualizar dados
            }
        } catch (error) {
            console.error('Erro ao completar hábito:', error);
            this.showMessage('Erro ao completar hábito', 'error');
        }
    }

    async editHabit(habitId) {
        const habit = this.habits.find(h => h.id === habitId);
        if (!habit) return;

        // Implementar modal de edição
        this.showEditModal(habit);
    }

    async deleteHabit(habitId) {
        if (!confirm('Tem certeza que deseja excluir este hábito?')) return;

        try {
            const response = await fetch(`/api/habitos/${habitId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            });

            if (response.ok) {
                this.habits = this.habits.filter(h => h.id !== habitId);
                this.renderHabits();
                this.showMessage('Hábito excluído com sucesso!', 'success');
            }
        } catch (error) {
            console.error('Erro ao excluir hábito:', error);
            this.showMessage('Erro ao excluir hábito', 'error');
        }
    }

    showEditModal(habit) {
        // Implementar modal de edição
        console.log('Editar hábito:', habit);
    }

    showMessage(message, type = 'info') {
        // Implementar sistema de mensagens
        console.log(`${type.toUpperCase()}: ${message}`);
    }

    bindEvents() {
        // Adicionar event listeners
        document.addEventListener('DOMContentLoaded', () => {
            const createHabitBtn = document.getElementById('create-habit-btn');
            if (createHabitBtn) {
                createHabitBtn.addEventListener('click', () => this.showCreateModal());
            }
        });
    }

    showCreateModal() {
        // Implementar modal de criação
        console.log('Mostrar modal de criação de hábito');
    }
}

// Variável global para o manager
let habitsManager;

// Inicializar quando a configuração da API estiver pronta
window.addEventListener('APIConfigReady', function(event) {
    console.log('APIConfigReady recebido, inicializando HabitsManager...');
    if (typeof window.API_CONFIG !== 'undefined') {
        habitsManager = new HabitsManager();
        window.habitsManager = habitsManager;
    } else {
        console.error('API_CONFIG ainda não está definido após evento APIConfigReady');
    }
});

// Fallback: se o evento não for disparado, tentar inicializar após um delay
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        if (typeof window.API_CONFIG !== 'undefined' && !habitsManager) {
            console.log('API_CONFIG encontrado via fallback, inicializando HabitsManager...');
            habitsManager = new HabitsManager();
            window.habitsManager = habitsManager;
        }
    }, 2000);
});
