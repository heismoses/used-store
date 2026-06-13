/**
 * UsedStore Marketplace - Main JavaScript
 */

const SITE_URL = document.querySelector('link[href*="style.css"]')?.href.replace('/assets/css/style.css', '') || '';

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initMobileNav();
    initNotifications();
    initFlashAutoDismiss();
    pollUnreadCounts();
});

/* ---- Dark Mode ---- */
function initTheme() {
    const toggle = document.getElementById('themeToggle');
    const saved = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);
    updateThemeIcon(saved);

    toggle?.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
        updateThemeIcon(next);
    });
}

function updateThemeIcon(theme) {
    const icon = document.querySelector('#themeToggle i');
    if (icon) {
        icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
}

/* ---- Mobile Navigation ---- */
function initMobileNav() {
    const toggle = document.getElementById('navToggle');
    const menu = document.getElementById('navMenu');

    toggle?.addEventListener('click', () => {
        menu?.classList.toggle('active');
        toggle.classList.toggle('active');
    });

    document.addEventListener('click', (e) => {
        if (menu?.classList.contains('active') && !toggle?.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.remove('active');
            toggle?.classList.remove('active');
        }
    });
}

/* ---- Notifications ---- */
function initNotifications() {
    const btn = document.getElementById('notifBtn');
    const dropdown = document.getElementById('notifDropdown');

    btn?.addEventListener('click', async (e) => {
        e.stopPropagation();
        dropdown?.classList.toggle('show');

        if (dropdown?.classList.contains('show')) {
            await loadNotifications();
            fetch(`${SITE_URL}/api/mark-notifications-read.php`, { method: 'POST' });
            document.getElementById('notifBadge')?.remove();
        }
    });

    document.addEventListener('click', () => dropdown?.classList.remove('show'));
}

async function loadNotifications() {
    const dropdown = document.getElementById('notifDropdown');
    if (!dropdown) return;

    try {
        const res = await fetch(`${SITE_URL}/api/notifications.php`);
        const data = await res.json();

        if (data.length === 0) {
            dropdown.innerHTML = '<div class="notif-item"><p>No notifications</p></div>';
            return;
        }

        dropdown.innerHTML = data.map(n => `
            <div class="notif-item ${n.is_read == 0 ? 'unread' : ''}" ${n.link ? `onclick="window.location='${n.link}'"` : ''}>
                <h5>${escapeHtml(n.title)}</h5>
                <p>${escapeHtml(n.message)}</p>
                <small>${n.created_at}</small>
            </div>
        `).join('');
    } catch {
        dropdown.innerHTML = '<div class="notif-item"><p>Failed to load</p></div>';
    }
}

/* ---- Unread Counts Polling ---- */
function pollUnreadCounts() {
    if (!document.getElementById('notifBtn')) return;

    setInterval(async () => {
        try {
            const res = await fetch(`${SITE_URL}/api/unread-counts.php`);
            const data = await res.json();
            updateBadge('notifBadge', data.notifications);
        } catch { /* silent */ }
    }, 30000);
}

function updateBadge(id, count) {
    let badge = document.getElementById(id);
    if (count > 0) {
        if (!badge) {
            badge = document.createElement('span');
            badge.id = id;
            badge.className = 'badge';
            document.getElementById('notifBtn')?.appendChild(badge);
        }
        badge.textContent = count;
    } else {
        badge?.remove();
    }
}

/* ---- Flash Auto Dismiss ---- */
function initFlashAutoDismiss() {
    const alert = document.getElementById('flashAlert');
    if (alert) {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    }
}

/* ---- Password Toggle ---- */
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input?.parentElement.querySelector('.toggle-password i');
    if (input) {
        input.type = input.type === 'password' ? 'text' : 'password';
        if (icon) icon.className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    }
}

/* ---- Utility ---- */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type}`;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i> ${message}`;
    toast.style.cssText = 'position:fixed;top:70px;right:20px;z-index:9999;max-width:350px;animation:slideIn 0.3s ease';
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}
