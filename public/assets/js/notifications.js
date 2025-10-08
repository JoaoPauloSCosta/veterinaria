/**
 * Gerencia o dropdown de notificações no header.
 * - Busca e renderiza notificações não lidas.
 * - Marca como lida (unitária e todas).
 * - Formata mensagem e aplica escaping.
 */
// Base da aplicação para construir URLs absolutas de forma robusta
const NOTIF_BASE = (function () {
    try {
        if (typeof window.APP_URL === 'string' && window.APP_URL) {
            return window.APP_URL.replace(/\/$/, '');
        }
        var cssLink = document.querySelector('link[rel=stylesheet][href*="/assets/css/"]');
        if (cssLink && cssLink.href) {
            return cssLink.href.split('/assets/')[0];
        }
        var notifScript = document.querySelector('script[src*="/assets/js/notifications.js"]');
        if (notifScript && notifScript.src) {
            return notifScript.src.split('/assets/')[0];
        }
    } catch (e) {}
    return '';
})();
class NotificationManager {
    /**
     * Construtor: inicializa o gerenciador.
     * @returns {void}
     */
    constructor() {
        this.init();
    }

    /**
     * Inicializa ciclo de atualização e eventos.
     * @returns {void}
     */
    init() {
        this.loadNotifications();
        this.bindEvents();
        
        // Atualizar notificações a cada 30 segundos
        setInterval(() => {
            this.loadNotifications();
        }, 30000);
    }

    /**
     * Registra eventos do dropdown e botões.
     * @returns {void}
     */
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

    /**
     * Carrega notificações não lidas do backend.
     * @returns {Promise<void>}
     */
    async loadNotifications() {
        try {
            // Construir URL usando base detectada (corrige porta e subdiretório)
            const url = NOTIF_BASE ? NOTIF_BASE + '/notifications/unread' : '/notifications/unread';
            const response = await fetch(url, {
                credentials: 'same-origin' // Incluir cookies de sessão
            });
            
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

    /**
     * Atualiza o badge de quantidade de notificações.
     * @param {number} count
     * @returns {void}
     */
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

    /**
     * Renderiza a lista no dropdown com layout compacto.
     * @param {Array<{id:number,title:string,message:string,created_at:string}>} notifications
     * @returns {void}
     */
    renderNotifications(notifications) {
        const container = document.getElementById('notifications-list');
        if (!container) return;

        if (notifications.length === 0) {
            container.innerHTML = '<li class="dropdown-item text-center text-muted">Nenhuma notificação</li>';
            return;
        }

        container.innerHTML = notifications.map(notification => {
            const escapedMsg = this.escapeHtml(notification.message);
            const formattedMsg = this.formatMessage(escapedMsg);
            return `
            <li class="dropdown-item notification-item" data-id="${notification.id}">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-bold">${this.escapeHtml(notification.title)}</h6>
                        <p class="mb-1 small text-muted">${formattedMsg}</p>
                        <small class="text-muted">${this.formatDate(notification.created_at)}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary mark-read-btn" data-id="${notification.id}">
                        <i class="fa-solid fa-check"></i>
                    </button>
                </div>
            </li>
        `}).join('');

        // Adicionar eventos aos botões de marcar como lida
        container.querySelectorAll('.mark-read-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const id = btn.getAttribute('data-id');
                this.markAsRead(id);
            });
        });
    }

    /**
     * Exibe erro amigável no dropdown.
     * @param {string} message
     * @returns {void}
     */
    renderError(message) {
        const container = document.getElementById('notifications-list');
        if (!container) return;
        
        container.innerHTML = `<li class="dropdown-item text-center text-danger">${message}</li>`;
    }

    /**
     * Marca uma notificação como lida e recarrega.
     * @param {string|number} notificationId
     * @returns {Promise<void>}
     */
    async markAsRead(notificationId) {
        try {
            // Construir URL usando base detectada
            const url = NOTIF_BASE ? NOTIF_BASE + '/notifications/mark-read' : '/notifications/mark-read';
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin', // Incluir cookies de sessão
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

    /**
     * Marca todas as notificações como lidas.
     * @returns {Promise<void>}
     */
    async markAllAsRead() {
        try {
            // Construir URL usando base detectada
            const url = NOTIF_BASE ? NOTIF_BASE + '/notifications/mark-all-read' : '/notifications/mark-all-read';
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin' // Incluir cookies de sessão
            });

            const data = await response.json();
            if (data.success) {
                this.loadNotifications();
            }
        } catch (error) {
            console.error('Erro ao marcar todas as notificações como lidas:', error);
        }
    }

    /**
     * Escapa texto para evitar injeção de HTML.
     * @param {string} text
     * @returns {string}
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Converte data em texto relativo (Agora, Xm atrás, etc.).
     * @param {string} dateString
     * @returns {string}
     */
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

    /**
     * Quebra a mensagem em linhas (após PET e antes da DATA).
     * Ex.: "Nova consulta agendada para PET (cliente: X) em DATA".
     * @param {string} text
     * @returns {string}
     */
    formatMessage(text) {
        let t = text;
        // Quebra após nome do pet (antes do cliente)
        t = t.replace(/\s*\(cliente:/i, '<br>(cliente:');
        // Quebra antes da data
        t = t.replace(/\s+em\s+/i, '<br>em ');
        return t;
    }
}

// Inicializar quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', () => {
    new NotificationManager();
});