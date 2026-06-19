// Tự động nhận diện API URL
// Nếu frontend chạy trên port 8081 → API ở port 8080
// Nếu chạy cùng server (XAMPP htdocs) → tự build từ path
(function() {
    const origin = window.location.origin;
    const port = window.location.port;
    if (port === '8081' || port === '3000') {
        window._API_URL = origin.replace(':' + port, ':8080');
    } else {
        const currentPath = window.location.pathname;
        const idx = currentPath.indexOf('/frontend');
        if (idx !== -1) {
            window._API_URL = origin + currentPath.substring(0, idx) + '/public';
        } else {
            window._API_URL = origin + '/public';
        }
    }
})();
const API_URL = window._API_URL;

function getToken() {
    return localStorage.getItem('token');
}

function handleLogout() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = 'index.html';
}

async function fetchAPI(endpoint, options = {}) {
    const token = getToken();
    
    if (!token && !endpoint.includes('/auth/login')) {
        handleLogout();
        return;
    }

    const defaultHeaders = {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
    };

    const config = {
        ...options,
        headers: {
            ...defaultHeaders,
            ...options.headers
        }
    };

    try {
        const response = await fetch(`${API_URL}${endpoint}`, config);
        
        if (response.status === 401 || response.status === 403) {
            handleLogout();
            throw new Error('Unauthorized');
        }

        const data = await response.json();
        return { ok: response.ok, status: response.status, data };
    } catch (error) {
        console.error('API Error:', error);
        return { ok: false, status: 500, error: error.message };
    }
}

function confirmDelete(label, onConfirm) {
    const overlay = document.createElement('div');
    overlay.className = 'confirm-overlay';
    overlay.innerHTML = `
        <div class="confirm-box">
            <div class="confirm-icon">🗑️</div>
            <div class="confirm-title">Xác nhận xóa</div>
            <div class="confirm-msg">Bạn có chắc muốn xóa <strong>${label}</strong>?<br>Hành động này không thể hoàn tác.</div>
            <div class="confirm-actions">
                <button class="confirm-cancel">Hủy</button>
                <button class="confirm-ok">Xóa</button>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);
    overlay.querySelector('.confirm-cancel').onclick = () => overlay.remove();
    overlay.querySelector('.confirm-ok').onclick = () => { overlay.remove(); onConfirm(); };
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.remove(); });
}

async function deleteRecord(endpoint, label, reloadFn) {
    confirmDelete(label, async () => {
        const res = await fetchAPI(endpoint, { method: 'DELETE' });
        if (res?.ok) { showToast('Đã xóa ' + label, 'success'); reloadFn(); }
        else showToast('Lỗi xóa: ' + (res?.data?.error || 'Không thể xóa'), 'error');
    });
}

function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const icons = { success: '✓', error: '✕', warning: '⚠', info: 'ℹ' };

    const el = document.createElement('div');
    el.className = `toast-item toast-${type}`;
    el.innerHTML = `
        <span class="toast-icon">${icons[type] || icons.info}</span>
        <span class="toast-body">${message}</span>
        <button class="toast-close" aria-label="Đóng">×</button>
        <div class="toast-progress"></div>
    `;
    container.appendChild(el);

    requestAnimationFrame(() => requestAnimationFrame(() => el.classList.add('show')));

    function dismiss() {
        el.classList.remove('show');
        el.addEventListener('transitionend', () => el.remove(), { once: true });
    }

    const timer = setTimeout(dismiss, 4000);
    el.querySelector('.toast-close').addEventListener('click', () => {
        clearTimeout(timer);
        dismiss();
    });
}
