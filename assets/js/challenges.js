/**
 * FIT BATTLE - Sistema de Desafios
 * Gerenciamento de Desafios e Competições
 */

class ChallengeManager {
    constructor() {
        this.challenges = [];
        this.userChallenges = [];
        this.currentUser = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadChallenges();
        this.checkUserChallenges();
    }

    setupEventListeners() {
        // Botões de ação
        document.getElementById('createChallengeBtn')?.addEventListener('click', () => this.showCreateChallengeModal());
        document.getElementById('viewAllChallengesBtn')?.addEventListener('click', () => this.viewAllChallenges());
        
        // Event listeners para desafios existentes
        this.setupChallengeEventListeners();
    }

    setupChallengeEventListeners() {
        // Delegar eventos para desafios dinâmicos
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('challenge-participate-btn')) {
                const challengeId = e.target.dataset.challengeId;
                this.participateInChallenge(challengeId);
            }
            
            if (e.target.classList.contains('challenge-view-btn')) {
                const challengeId = e.target.dataset.challengeId;
                this.viewChallengeDetails(challengeId);
            }
            
            if (e.target.classList.contains('challenge-leave-btn')) {
                const challengeId = e.target.dataset.challengeId;
                this.leaveChallenge(challengeId);
            }
        });
    }

    async loadChallenges() {
        try {
            // Simular carregamento de desafios
            const challenges = await this.fetchChallenges();
            this.challenges = challenges;
            
            // Renderizar desafios
            this.renderChallenges();
            
        } catch (error) {
            console.error('Erro ao carregar desafios:', error);
            this.showChallengesError(error.message);
        }
    }

    async fetchChallenges() {
        // Simular delay de API
        await new Promise(resolve => setTimeout(resolve, 1000));

        return [
            {
                id: 1,
                title: '🏃‍♂️ Desafio da Semana',
                description: 'Corra 21km em 7 dias consecutivos. Este é o desafio definitivo para corredores que querem provar sua resistência!',
                category: 'running',
                goalType: 'distance',
                goalValue: 21,
                durationDays: 7,
                entryFeePoints: 50,
                prizePoolPoints: 500,
                maxParticipants: 100,
                currentParticipants: 45,
                status: 'active',
                startDate: new Date(Date.now() - 4 * 24 * 60 * 60 * 1000), // 4 dias atrás
                endDate: new Date(Date.now() + 3 * 24 * 60 * 60 * 1000), // 3 dias restantes
                creator: 'FitnessKing',
                participants: [
                    { username: 'SpeedRunner', progress: 18.5, position: 1 },
                    { username: 'MarathonMan', progress: 16.2, position: 2 },
                    { username: 'TrailBlazer', progress: 14.8, position: 3 }
                ],
                rules: [
                    'Corrida deve ser registrada diariamente',
                    'Mínimo de 3km por dia',
                    'GPS obrigatório para validação',
                    'Sem pausas de mais de 1 dia'
                ]
            },
            {
                id: 2,
                title: '💪 Academia Intensa',
                description: 'Complete 100 séries de exercícios de força em 30 dias. Transforme seu corpo e domine o ranking de musculação!',
                category: 'gym',
                goalType: 'sets',
                goalValue: 100,
                durationDays: 30,
                entryFeePoints: 100,
                prizePoolPoints: 1000,
                maxParticipants: 50,
                currentParticipants: 23,
                status: 'active',
                startDate: new Date(Date.now() - 15 * 24 * 60 * 60 * 1000), // 15 dias atrás
                endDate: new Date(Date.now() + 15 * 24 * 60 * 60 * 1000), // 15 dias restantes
                creator: 'IronWoman',
                participants: [
                    { username: 'PowerLifter', progress: 78, position: 1 },
                    { username: 'MuscleBuilder', progress: 65, position: 2 },
                    { username: 'CrossFitPro', progress: 58, position: 3 }
                ],
                rules: [
                    'Mínimo de 3 séries por dia',
                    'Exercícios de força apenas',
                    'Foto obrigatória do treino',
                    'Descanso máximo de 2 dias'
                ]
            },
            {
                id: 3,
                title: '🧘‍♀️ Yoga Challenge',
                description: '30 dias de yoga consecutivos. Desenvolva flexibilidade, força mental e encontre seu equilíbrio interior!',
                category: 'yoga',
                goalType: 'days',
                goalValue: 30,
                durationDays: 30,
                entryFeePoints: 75,
                prizePoolPoints: 750,
                maxParticipants: 200,
                currentParticipants: 67,
                status: 'active',
                startDate: new Date(Date.now() - 22 * 24 * 60 * 60 * 1000), // 22 dias atrás
                endDate: new Date(Date.now() + 8 * 24 * 60 * 60 * 1000), // 8 dias restantes
                creator: 'YogaMaster',
                participants: [
                    { username: 'ZenSeeker', progress: 30, position: 1 },
                    { username: 'MindfulOne', progress: 28, position: 2 },
                    { username: 'PeacefulSoul', progress: 26, position: 3 }
                ],
                rules: [
                    'Sessão mínima de 20 minutos',
                    'Yoga tradicional ou moderno',
                    'Registro diário obrigatório',
                    'Sem interrupções'
                ]
            },
            {
                id: 4,
                title: '⚡ HIIT Explosivo',
                description: 'Complete 50 sessões de HIIT em 60 dias. Queime calorias e melhore sua condição cardiovascular!',
                category: 'hiit',
                goalType: 'sessions',
                goalValue: 50,
                durationDays: 60,
                entryFeePoints: 150,
                prizePoolPoints: 1500,
                maxParticipants: 75,
                currentParticipants: 12,
                status: 'active',
                startDate: new Date(Date.now() - 10 * 24 * 60 * 60 * 1000), // 10 dias atrás
                endDate: new Date(Date.now() + 50 * 24 * 60 * 60 * 1000), // 50 dias restantes
                creator: 'CrossFitPro',
                participants: [
                    { username: 'HIITMaster', progress: 25, position: 1 },
                    { username: 'ExplosiveRunner', progress: 22, position: 2 },
                    { username: 'CardioKing', progress: 18, position: 3 }
                ],
                rules: [
                    'Sessão mínima de 15 minutos',
                    'Intensidade alta obrigatória',
                    'Intervalos de 30s/30s',
                    'Máximo 2 sessões por dia'
                ]
            }
        ];
    }

    renderChallenges() {
        const challengesGrid = document.getElementById('challengesGrid');
        if (!challengesGrid) return;

        if (this.challenges.length === 0) {
            challengesGrid.innerHTML = `
                <div class="empty-challenges">
                    <div class="empty-icon">⚔️</div>
                    <h3>Nenhum desafio ativo</h3>
                    <p>Seja o primeiro a criar um desafio épico!</p>
                    <button class="btn btn-primary" onclick="challengeManager.showCreateChallengeModal()">
                        ⚔️ Criar Primeiro Desafio
                    </button>
                </div>
            `;
            return;
        }

        challengesGrid.innerHTML = this.challenges.map(challenge => this.createChallengeCard(challenge)).join('');
    }

    createChallengeCard(challenge) {
        const timeLeft = this.calculateTimeLeft(challenge.endDate);
        const progressPercentage = this.calculateProgressPercentage(challenge);
        const isUserParticipating = this.isUserParticipating(challenge.id);
        
        return `
            <div class="challenge-card ${challenge.status}" data-challenge-id="${challenge.id}">
                <div class="challenge-header">
                    <div class="challenge-title-section">
                        <h3>${challenge.title}</h3>
                        <span class="challenge-category">${this.getCategoryIcon(challenge.category)} ${challenge.category}</span>
                    </div>
                    <div class="challenge-status-badge ${challenge.status}">
                        ${this.getStatusIcon(challenge.status)} ${challenge.status}
                    </div>
                </div>
                
                <p class="challenge-description">${challenge.description}</p>
                
                <div class="challenge-goal">
                    <div class="goal-info">
                        <span class="goal-label">Meta:</span>
                        <span class="goal-value">${challenge.goalValue} ${this.getGoalUnit(challenge.goalType)}</span>
                    </div>
                    <div class="goal-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${progressPercentage}%"></div>
                        </div>
                        <span class="progress-text">${progressPercentage}%</span>
                    </div>
                </div>
                
                <div class="challenge-details">
                    <div class="detail-item">
                        <span class="detail-icon">⏰</span>
                        <span class="detail-label">Duração:</span>
                        <span class="detail-value">${challenge.durationDays} dias</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-icon">👥</span>
                        <span class="detail-label">Participantes:</span>
                        <span class="detail-value">${challenge.currentParticipants}/${challenge.maxParticipants}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-icon">🏆</span>
                        <span class="detail-label">Prêmio:</span>
                        <span class="detail-value">${challenge.prizePoolPoints} pontos</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-icon">💰</span>
                        <span class="detail-label">Taxa:</span>
                        <span class="detail-value">${challenge.entryFeePoints} pontos</span>
                    </div>
                </div>
                
                <div class="challenge-time">
                    <span class="time-left ${timeLeft.urgent ? 'urgent' : ''}">
                        ⏰ ${timeLeft.text}
                    </span>
                </div>
                
                <div class="challenge-participants">
                    <h4>🏆 Top 3 Participantes</h4>
                    <div class="participants-list">
                        ${challenge.participants.slice(0, 3).map((participant, index) => `
                            <div class="participant-item">
                                <span class="participant-position">#${index + 1}</span>
                                <span class="participant-name">${participant.username}</span>
                                <span class="participant-progress">${participant.progress} ${this.getGoalUnit(challenge.goalType)}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                <div class="challenge-actions">
                    ${isUserParticipating ? `
                        <button class="btn btn-outline challenge-leave-btn" data-challenge-id="${challenge.id}">
                            🚪 Sair
                        </button>
                        <button class="btn btn-primary challenge-view-btn" data-challenge-id="${challenge.id}">
                            📊 Ver Progresso
                        </button>
                    ` : `
                        <button class="btn btn-primary challenge-participate-btn" data-challenge-id="${challenge.id}">
                            ⚔️ Participar
                        </button>
                        <button class="btn btn-outline challenge-view-btn" data-challenge-id="${challenge.id}">
                            👁️ Ver Detalhes
                        </button>
                    `}
                </div>
            </div>
        `;
    }

    getCategoryIcon(category) {
        const icons = {
            'running': '🏃‍♂️',
            'gym': '💪',
            'yoga': '🧘‍♀️',
            'hiit': '⚡',
            'cycling': '🚴‍♂️',
            'swimming': '🏊‍♂️'
        };
        return icons[category] || '🏃‍♂️';
    }

    getStatusIcon(status) {
        const icons = {
            'open': '🟢',
            'active': '🟡',
            'completed': '✅',
            'cancelled': '❌'
        };
        return icons[status] || '🟢';
    }

    getGoalUnit(goalType) {
        const units = {
            'distance': 'km',
            'time': 'minutos',
            'repetitions': 'reps',
            'sets': 'séries',
            'sessions': 'sessões',
            'days': 'dias'
        };
        return units[goalType] || 'unidades';
    }

    calculateTimeLeft(endDate) {
        const now = new Date();
        const end = new Date(endDate);
        const diff = end - now;
        
        if (diff <= 0) {
            return { text: 'Encerrado', urgent: false };
        }
        
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        
        if (days > 0) {
            return { text: `${days} dias restantes`, urgent: days <= 3 };
        } else {
            return { text: `${hours} horas restantes`, urgent: true };
        }
    }

    calculateProgressPercentage(challenge) {
        const now = new Date();
        const start = new Date(challenge.startDate);
        const end = new Date(challenge.endDate);
        
        if (now < start) return 0;
        if (now > end) return 100;
        
        const totalDuration = end - start;
        const elapsed = now - start;
        
        return Math.round((elapsed / totalDuration) * 100);
    }

    isUserParticipating(challengeId) {
        // Verificar se o usuário atual está participando
        return this.userChallenges.some(uc => uc.challengeId === challengeId);
    }

    async participateInChallenge(challengeId) {
        try {
            // Verificar se o usuário está logado
            if (!this.checkUserAuthentication()) {
                return;
            }

            const challenge = this.challenges.find(c => c.id == challengeId);
            if (!challenge) {
                throw new Error('Desafio não encontrado');
            }

            // Verificar se ainda há vagas
            if (challenge.currentParticipants >= challenge.maxParticipants) {
                this.showNotification('❌ Este desafio está lotado!', 'error');
                return;
            }

            // Verificar se o usuário tem pontos suficientes
            if (this.currentUser.totalPoints < challenge.entryFeePoints) {
                this.showNotification(`❌ Você precisa de ${challenge.entryFeePoints} pontos para participar!`, 'error');
                return;
            }

            // Confirmar participação
            if (confirm(`Deseja participar do desafio "${challenge.title}"?\n\nTaxa de entrada: ${challenge.entryFeePoints} pontos\nPrêmio: ${challenge.prizePoolPoints} pontos`)) {
                await this.joinChallenge(challengeId);
            }

        } catch (error) {
            console.error('Erro ao participar do desafio:', error);
            this.showNotification('❌ Erro ao participar do desafio: ' + error.message, 'error');
        }
    }

    async joinChallenge(challengeId) {
        try {
            // Simular chamada de API
            await new Promise(resolve => setTimeout(resolve, 1000));

            const challenge = this.challenges.find(c => c.id == challengeId);
            
            // Adicionar usuário aos participantes
            challenge.currentParticipants++;
            
            // Adicionar aos desafios do usuário
            this.userChallenges.push({
                challengeId: challengeId,
                joinedAt: new Date(),
                currentProgress: 0,
                isCompleted: false
            });

            // Deduzir pontos de entrada
            this.currentUser.totalPoints -= challenge.entryFeePoints;

            // Atualizar UI
            this.renderChallenges();
            this.showNotification(`🎉 Você entrou no desafio "${challenge.title}"!`, 'success');

        } catch (error) {
            throw new Error('Erro ao entrar no desafio');
        }
    }

    async leaveChallenge(challengeId) {
        try {
            if (confirm('Tem certeza que deseja sair deste desafio?')) {
                // Simular chamada de API
                await new Promise(resolve => setTimeout(resolve, 1000));

                const challenge = this.challenges.find(c => c.id == challengeId);
                
                // Remover usuário dos participantes
                challenge.currentParticipants--;
                
                // Remover dos desafios do usuário
                this.userChallenges = this.userChallenges.filter(uc => uc.challengeId != challengeId);

                // Atualizar UI
                this.renderChallenges();
                this.showNotification('👋 Você saiu do desafio!', 'info');
            }
        } catch (error) {
            console.error('Erro ao sair do desafio:', error);
            this.showNotification('❌ Erro ao sair do desafio', 'error');
        }
    }

    viewChallengeDetails(challengeId) {
        const challenge = this.challenges.find(c => c.id == challengeId);
        if (!challenge) return;

        this.showChallengeDetailsModal(challenge);
    }

    showChallengeDetailsModal(challenge) {
        // Criar modal de detalhes
        const modal = document.createElement('div');
        modal.className = 'modal challenge-details-modal';
        modal.innerHTML = `
            <div class="modal-content challenge-details-content">
                <div class="modal-header">
                    <h3>${challenge.title}</h3>
                    <span class="close">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="challenge-full-description">
                        <p>${challenge.description}</p>
                    </div>
                    
                    <div class="challenge-rules">
                        <h4>📋 Regras do Desafio</h4>
                        <ul>
                            ${challenge.rules.map(rule => `<li>${rule}</li>`).join('')}
                        </ul>
                    </div>
                    
                    <div class="challenge-participants-full">
                        <h4>👥 Todos os Participantes (${challenge.currentParticipants})</h4>
                        <div class="participants-full-list">
                            ${challenge.participants.map((participant, index) => `
                                <div class="participant-full-item">
                                    <span class="participant-position">#${index + 1}</span>
                                    <span class="participant-name">${participant.username}</span>
                                    <span class="participant-progress">${participant.progress} ${this.getGoalUnit(challenge.goalType)}</span>
                                    <span class="participant-percentage">${Math.round((participant.progress / challenge.goalValue) * 100)}%</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    
                    <div class="challenge-timeline">
                        <h4>📅 Cronograma</h4>
                        <div class="timeline-item">
                            <span class="timeline-date">${challenge.startDate.toLocaleDateString()}</span>
                            <span class="timeline-event">Início do Desafio</span>
                        </div>
                        <div class="timeline-item">
                            <span class="timeline-date">${challenge.endDate.toLocaleDateString()}</span>
                            <span class="timeline-event">Fim do Desafio</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Adicionar ao DOM
        document.body.appendChild(modal);
        modal.style.display = 'block';

        // Configurar fechamento
        const closeBtn = modal.querySelector('.close');
        closeBtn.addEventListener('click', () => {
            modal.remove();
        });

        // Fechar ao clicar fora
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }

    showCreateChallengeModal() {
        if (!this.checkUserAuthentication()) {
            return;
        }

        // Criar modal de criação de desafio
        const modal = document.createElement('div');
        modal.className = 'modal create-challenge-modal';
        modal.innerHTML = `
            <div class="modal-content create-challenge-content">
                <div class="modal-header">
                    <h3>⚔️ Criar Novo Desafio</h3>
                    <span class="close">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="createChallengeForm">
                        <div class="form-group">
                            <label for="challengeTitle">Título do Desafio</label>
                            <input type="text" id="challengeTitle" required placeholder="Ex: Desafio da Semana">
                        </div>
                        
                        <div class="form-group">
                            <label for="challengeDescription">Descrição</label>
                            <textarea id="challengeDescription" required placeholder="Descreva seu desafio épico..."></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="challengeCategory">Categoria</label>
                                <select id="challengeCategory" required>
                                    <option value="">Selecione...</option>
                                    <option value="running">🏃‍♂️ Corrida</option>
                                    <option value="gym">💪 Academia</option>
                                    <option value="yoga">🧘‍♀️ Yoga</option>
                                    <option value="hiit">⚡ HIIT</option>
                                    <option value="cycling">🚴‍♂️ Ciclismo</option>
                                    <option value="swimming">🏊‍♂️ Natação</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="challengeGoalType">Tipo de Meta</label>
                                <select id="challengeGoalType" required>
                                    <option value="">Selecione...</option>
                                    <option value="distance">Distância (km)</option>
                                    <option value="time">Tempo (minutos)</option>
                                    <option value="repetitions">Repetições</option>
                                    <option value="sets">Séries</option>
                                    <option value="sessions">Sessões</option>
                                    <option value="days">Dias</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="challengeGoalValue">Valor da Meta</label>
                                <input type="number" id="challengeGoalValue" required min="1" placeholder="Ex: 21">
                            </div>
                            
                            <div class="form-group">
                                <label for="challengeDuration">Duração (dias)</label>
                                <input type="number" id="challengeDuration" required min="1" max="365" placeholder="Ex: 7">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="challengeEntryFee">Taxa de Entrada (pontos)</label>
                                <input type="number" id="challengeEntryFee" required min="0" placeholder="Ex: 50">
                            </div>
                            
                            <div class="form-group">
                                <label for="challengeMaxParticipants">Máximo de Participantes</label>
                                <input type="number" id="challengeMaxParticipants" required min="2" max="1000" placeholder="Ex: 100">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-outline" onclick="this.closest('.modal').remove()">
                                ❌ Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                ⚔️ Criar Desafio
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        // Adicionar ao DOM
        document.body.appendChild(modal);
        modal.style.display = 'block';

        // Configurar formulário
        const form = modal.querySelector('#createChallengeForm');
        form.addEventListener('submit', (e) => this.handleCreateChallenge(e));

        // Configurar fechamento
        const closeBtn = modal.querySelector('.close');
        closeBtn.addEventListener('click', () => {
            modal.remove();
        });

        // Fechar ao clicar fora
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }

    async handleCreateChallenge(e) {
        e.preventDefault();
        
        try {
            const formData = {
                title: document.getElementById('challengeTitle').value,
                description: document.getElementById('challengeDescription').value,
                category: document.getElementById('challengeCategory').value,
                goalType: document.getElementById('challengeGoalType').value,
                goalValue: parseInt(document.getElementById('challengeGoalValue').value),
                durationDays: parseInt(document.getElementById('challengeDuration').value),
                entryFeePoints: parseInt(document.getElementById('challengeEntryFee').value),
                maxParticipants: parseInt(document.getElementById('challengeMaxParticipants').value)
            };

            // Validar dados
            if (!this.validateChallengeData(formData)) {
                return;
            }

            // Criar desafio
            await this.createChallenge(formData);

            // Fechar modal
            document.querySelector('.create-challenge-modal').remove();

        } catch (error) {
            console.error('Erro ao criar desafio:', error);
            this.showNotification('❌ Erro ao criar desafio: ' + error.message, 'error');
        }
    }

    validateChallengeData(data) {
        if (data.title.length < 5) {
            this.showNotification('❌ Título deve ter pelo menos 5 caracteres', 'error');
            return false;
        }

        if (data.description.length < 20) {
            this.showNotification('❌ Descrição deve ter pelo menos 20 caracteres', 'error');
            return false;
        }

        if (data.goalValue <= 0) {
            this.showNotification('❌ Valor da meta deve ser maior que 0', 'error');
            return false;
        }

        if (data.durationDays < 1 || data.durationDays > 365) {
            this.showNotification('❌ Duração deve ser entre 1 e 365 dias', 'error');
            return false;
        }

        if (data.entryFeePoints < 0) {
            this.showNotification('❌ Taxa de entrada não pode ser negativa', 'error');
            return false;
        }

        if (data.maxParticipants < 2) {
            this.showNotification('❌ Deve haver pelo menos 2 participantes', 'error');
            return false;
        }

        return true;
    }

    async createChallenge(data) {
        try {
            // Simular chamada de API
            await new Promise(resolve => setTimeout(resolve, 1500));

            // Criar novo desafio
            const newChallenge = {
                id: Date.now(),
                ...data,
                currentParticipants: 1, // Criador automaticamente participa
                status: 'active',
                startDate: new Date(),
                endDate: new Date(Date.now() + data.durationDays * 24 * 60 * 60 * 1000),
                creator: this.currentUser.username,
                participants: [
                    { username: this.currentUser.username, progress: 0, position: 1 }
                ],
                rules: [
                    'Respeite as regras do desafio',
                    'Registre suas atividades diariamente',
                    'Seja honesto com seus resultados',
                    'Mantenha o espírito esportivo'
                ]
            };

            // Adicionar à lista
            this.challenges.unshift(newChallenge);

            // Adicionar aos desafios do usuário
            this.userChallenges.push({
                challengeId: newChallenge.id,
                joinedAt: new Date(),
                currentProgress: 0,
                isCompleted: false
            });

            // Atualizar UI
            this.renderChallenges();
            this.showNotification('🎉 Desafio criado com sucesso!', 'success');

        } catch (error) {
            throw new Error('Erro ao criar desafio');
        }
    }

    checkUserAuthentication() {
        // Verificar se o usuário está logado
        if (!this.currentUser) {
            this.showNotification('🔑 Você precisa estar logado para criar desafios!', 'error');
            return false;
        }
        return true;
    }

    checkUserChallenges() {
        // Verificar desafios do usuário atual
        // Isso seria carregado da API
        this.userChallenges = [];
    }

    viewAllChallenges() {
        this.showNotification('📋 Redirecionando para todos os desafios...', 'info');
        // Aqui você pode redirecionar para uma página de desafios completa
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

    getChallengesByCategory(category) {
        return this.challenges.filter(c => c.category === category);
    }

    getActiveChallenges() {
        return this.challenges.filter(c => c.status === 'active');
    }

    getUserChallenges() {
        return this.userChallenges;
    }
}

// Inicializar gerenciador de desafios
let challengeManager;
document.addEventListener('DOMContentLoaded', () => {
    challengeManager = new ChallengeManager();
});

// Adicionar estilos para os desafios
const challengeStyles = document.createElement('style');
challengeStyles.textContent = `
    .empty-challenges {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
        grid-column: 1 / -1;
    }
    
    .empty-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }
    
    .challenge-card {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: transform 0.2s;
        border: 1px solid #e9ecef;
        position: relative;
        overflow: hidden;
    }
    
    .challenge-card:hover {
        transform: translateY(-3px);
    }
    
    .challenge-card.active {
        border-left: 4px solid #28a745;
    }
    
    .challenge-card.completed {
        border-left: 4px solid #6c757d;
        opacity: 0.8;
    }
    
    .challenge-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    
    .challenge-title-section h3 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 0.5rem;
    }
    
    .challenge-category {
        font-size: 0.9rem;
        color: #6c757d;
        background: #f8f9fa;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
    }
    
    .challenge-status-badge {
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        text-transform: capitalize;
    }
    
    .challenge-status-badge.active {
        background: #fff3cd;
        color: #856404;
    }
    
    .challenge-status-badge.completed {
        background: #d4edda;
        color: #155724;
    }
    
    .challenge-description {
        color: #6c757d;
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }
    
    .challenge-goal {
        margin-bottom: 1.5rem;
    }
    
    .goal-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    
    .goal-label {
        font-weight: 600;
        color: #333;
    }
    
    .goal-value {
        font-weight: 700;
        color: #667eea;
    }
    
    .goal-progress {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .progress-bar {
        flex: 1;
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        transition: width 0.3s ease;
    }
    
    .progress-text {
        font-size: 0.9rem;
        font-weight: 600;
        color: #667eea;
        min-width: 40px;
    }
    
    .challenge-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .detail-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
    }
    
    .detail-icon {
        font-size: 1rem;
    }
    
    .detail-label {
        color: #6c757d;
    }
    
    .detail-value {
        font-weight: 600;
        color: #333;
    }
    
    .challenge-time {
        margin-bottom: 1.5rem;
        text-align: center;
    }
    
    .time-left {
        background: #f8f9fa;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        color: #6c757d;
    }
    
    .time-left.urgent {
        background: #f8d7da;
        color: #721c24;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    .challenge-participants {
        margin-bottom: 1.5rem;
    }
    
    .challenge-participants h4 {
        font-size: 1rem;
        margin-bottom: 0.75rem;
        color: #333;
    }
    
    .participants-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .participant-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem;
        background: #f8f9fa;
        border-radius: 8px;
        font-size: 0.9rem;
    }
    
    .participant-position {
        font-weight: 700;
        color: #667eea;
        min-width: 30px;
    }
    
    .participant-name {
        flex: 1;
        margin: 0 1rem;
        color: #333;
    }
    
    .participant-progress {
        font-weight: 600;
        color: #28a745;
    }
    
    .challenge-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
    }
    
    .challenge-details-modal .modal-content {
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .challenge-full-description {
        margin-bottom: 2rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .challenge-rules {
        margin-bottom: 2rem;
    }
    
    .challenge-rules h4 {
        margin-bottom: 1rem;
        color: #333;
    }
    
    .challenge-rules ul {
        list-style: none;
        padding: 0;
    }
    
    .challenge-rules li {
        padding: 0.5rem 0;
        border-bottom: 1px solid #e9ecef;
        position: relative;
        padding-left: 1.5rem;
    }
    
    .challenge-rules li::before {
        content: "✅";
        position: absolute;
        left: 0;
    }
    
    .challenge-participants-full {
        margin-bottom: 2rem;
    }
    
    .challenge-participants-full h4 {
        margin-bottom: 1rem;
        color: #333;
    }
    
    .participants-full-list {
        max-height: 300px;
        overflow-y: auto;
    }
    
    .participant-full-item {
        display: grid;
        grid-template-columns: 60px 1fr 120px 80px;
        gap: 1rem;
        padding: 0.75rem;
        border-bottom: 1px solid #e9ecef;
        align-items: center;
    }
    
    .participant-percentage {
        text-align: center;
        font-weight: 600;
        color: #667eea;
    }
    
    .challenge-timeline {
        margin-bottom: 1rem;
    }
    
    .challenge-timeline h4 {
        margin-bottom: 1rem;
        color: #333;
    }
    
    .timeline-item {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 0.5rem;
    }
    
    .timeline-date {
        font-weight: 600;
        color: #667eea;
    }
    
    .timeline-event {
        color: #333;
    }
    
    .create-challenge-modal .modal-content {
        max-width: 600px;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }
    
    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
        padding-top: 1rem;
        border-top: 1px solid #e9ecef;
    }
    
    @media (max-width: 768px) {
        .challenge-details {
            grid-template-columns: 1fr;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .challenge-actions {
            flex-direction: column;
        }
        
        .participant-full-item {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }
    }
`;
document.head.appendChild(challengeStyles);
