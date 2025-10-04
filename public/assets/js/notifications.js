class NotificationManager {
    constructor() {
        this.init();
    }

    init() {
        this.loadNotifications();
        this.bindEvents();
        
        // Atualizar notificações a cada 30 segundos
        setInterval(() => {
            this.loadNotifications();
        }, 30000);
    }

    bindEvents() {
        // Marcar todas como lidas
        document.getElementById('mark-all-read')?.addEventListener('click', () => {
            this.markAllAsRead();
        });

        // Carregar notificações quando abrir o dropdown
        document.getElementById('notificationsDropdown')?.addEventListener('click', () => {
            this.loadNotifications();
        });
    }

    async loadNotifications() {
        try {
            // Usar URL relativa baseada na localização atual
            const baseUrl = window.location.origin + window.location.pathname.split('/').slice(0, -1).join('/');
            const response = await fetch(baseUrl + '/notifications/unread');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            // Verificar se a resposta é JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // Se não é JSON, provavelmente foi redirecionado para login
                this.renderError('Sessão expirada. Faça login novamente.');
                return;
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.updateBadge(data.count);
                this.renderNotifications(data.notifications);
            } else {
                this.renderError('Erro ao carregar notificações');
            }
        } catch (error) {
            console.error('Erro ao carregar notificações:', error);
            this.renderError('Erro de conexão');
        }
    }

    updateBadge(count) {
        const badge = document.getElementById('notification-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }
        }
    }

    renderNotifications(notifications) {
        const container = document.getElementById('notifications-list');
        if (!container) return;

        if (notifications.length === 0) {
            container.innerHTML = '<li class="dropdown-item text-center text-muted">Nenhuma notificação</li>';
            return;
        }

        container.innerHTML = notifications.map(notification => `
            <li class="dropdown-item notification-item" data-id="${notification.id}">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-bold">${this.escapeHtml(notification.title)}</h6>
                        <p class="mb-1 small text-muted">${this.escapeHtml(notification.message)}</p>
                        <small class="text-muted">${this.formatDate(notification.created_at)}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary mark-read-btn" data-id="${notification.id}">
                        <i class="fa-solid fa-check"></i>
                    </button>
                </div>
            </li>
        `).join('');

        // Adicionar eventos aos botões de marcar como lida
        container.querySelectorAll('.mark-read-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const id = btn.getAttribute('data-id');
                this.markAsRead(id);
            });
        });
    }

    renderError(message) {
        const container = document.getElementById('notifications-list');
        if (!container) return;
        
        container.innerHTML = `<li class="dropdown-item text-center text-danger">${message}</li>`;
    }

    async markAsRead(notificationId) {
        try {
            // Usar URL relativa baseada na localização atual
            const baseUrl = window.location.origin + window.location.pathname.split('/').slice(0, -1).join('/');
            const response = await fetch(baseUrl + '/notifications/mark-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: notificationId })
            });

            const data = await response.json();
            if (data.success) {
                this.loadNotifications();
            }
        } catch (error) {
            console.error('Erro ao marcar notificação como lida:', error);
        }
    }

    async markAllAsRead() {
        try {
            // Usar URL relativa baseada na localização atual
            const baseUrl = window.location.origin + window.location.pathname.split('/').slice(0, -1).join('/');
            const response = await fetch(baseUrl + '/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();
            if (data.success) {
                this.loadNotifications();
            }
        } catch (error) {
            console.error('Erro ao marcar todas as notificações como lidas:', error);
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMins / 60);
        const diffDays = Math.floor(diffHours / 24);

        if (diffMins < 1) return 'Agora';
        if (diffMins < 60) return `${diffMins}m atrás`;
        if (diffHours < 24) return `${diffHours}h atrás`;
        if (diffDays < 7) return `${diffDays}d atrás`;
        
        return date.toLocaleDateString('pt-BR');
    }
}

// Inicializar quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    new NotificationManager();
});