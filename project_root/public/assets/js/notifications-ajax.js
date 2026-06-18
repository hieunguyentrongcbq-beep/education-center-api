(function () {
    'use strict';

    if (!window.PortalApi) return;

    var list = document.querySelector('.notif-list');
    if (!list) return;

    function updateBellCount(count) {
        var badge = document.querySelector('.topbar-bell-badge');
        var bell = document.querySelector('.topbar-bell');
        if (bell) {
            if (count > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'topbar-bell-badge';
                    bell.appendChild(badge);
                }
                badge.textContent = count > 99 ? '99+' : String(count);
            } else if (badge) {
                badge.remove();
            }
        }
        var summary = document.querySelector('.notif-summary');
        if (summary) {
            summary.innerHTML = count > 0
                ? 'Bạn có <strong>' + count + '</strong> thông báo chưa đọc.'
                : 'Không có thông báo mới.';
        }
        var readAllBtn = document.querySelector('.js-mark-all-read');
        if (readAllBtn) {
            readAllBtn.style.display = count > 0 ? '' : 'none';
        }
    }

    list.addEventListener('click', async function (e) {
        var btn = e.target.closest('.js-mark-read');
        if (!btn) return;
        e.preventDefault();
        var id = btn.getAttribute('data-id');
        if (!id) return;
        btn.disabled = true;
        try {
            var res = await PortalApi.post('/api/portal/notifications/' + id + '/read');
            var item = btn.closest('.notif-item');
            if (item) {
                item.classList.remove('unread');
                var action = item.querySelector('.notif-item-action');
                if (action) action.remove();
            }
            updateBellCount(res.unread != null ? res.unread : 0);
        } catch (err) {
            btn.disabled = false;
            alert(err.message || 'Không thể đánh dấu đã đọc');
        }
    });

    var readAllBtn = document.querySelector('.js-mark-all-read');
    if (readAllBtn) {
        readAllBtn.addEventListener('click', async function (e) {
            e.preventDefault();
            readAllBtn.disabled = true;
            try {
                await PortalApi.post('/api/portal/notifications/read-all');
                document.querySelectorAll('.notif-item.unread').forEach(function (el) {
                    el.classList.remove('unread');
                    var action = el.querySelector('.notif-item-action');
                    if (action) action.remove();
                });
                updateBellCount(0);
            } catch (err) {
                alert(err.message || 'Lỗi');
            }
            readAllBtn.disabled = false;
        });
    }
})();
