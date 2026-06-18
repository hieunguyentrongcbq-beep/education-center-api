(function () {
    'use strict';

    var root = document.getElementById('classrooms-ajax-panel');
    if (!root || !window.PortalApi) return;

    var form = document.getElementById('classroom-ajax-form');
    var tbody = document.getElementById('classroom-ajax-tbody');
    var alertBox = document.getElementById('classroom-ajax-alert');
    var editId = document.getElementById('classroom-ajax-id');
    var btnCancel = document.getElementById('classroom-ajax-cancel');

    function showAlert(msg, type) {
        if (!alertBox) return;
        alertBox.className = 'alert alert-' + (type || 'info') + ' py-2';
        alertBox.textContent = msg;
        alertBox.classList.remove('d-none');
    }

    function hideAlert() {
        if (alertBox) alertBox.classList.add('d-none');
    }

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function renderRows(items) {
        if (!tbody) return;
        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-muted">Chưa có phòng học.</td></tr>';
            return;
        }
        tbody.innerHTML = items.map(function (rm) {
            var used = parseInt(rm.class_count, 10) || 0;
            var status = rm.status || 'ACTIVE';
            var badge = status === 'ACTIVE' ? 'bg-success' : 'bg-secondary';
            return '<tr data-id="' + rm.id + '"' +
                ' data-room="' + esc(rm.room_name) + '"' +
                ' data-capacity="' + esc(rm.capacity) + '"' +
                ' data-location="' + esc(rm.location || '') + '"' +
                ' data-status="' + esc(status) + '"' +
                ' data-used="' + used + '">' +
                '<td>' + esc(rm.id) + '</td>' +
                '<td class="fw-semibold">' + esc(rm.room_name) + '</td>' +
                '<td>' + esc(rm.capacity) + ' chỗ</td>' +
                '<td>' + esc(rm.location || '—') + '</td>' +
                '<td>' + used + '</td>' +
                '<td><span class="badge ' + badge + '">' + esc(status) + '</span></td>' +
                '<td class="actions d-flex flex-wrap gap-1">' +
                '<button type="button" class="btn btn-sm btn-outline-primary js-edit"><i class="bi bi-pencil"></i> Sửa</button>' +
                '<button type="button" class="btn btn-sm btn-danger js-delete"' +
                (used > 0 ? ' disabled title="Phòng đang được lớp sử dụng"' : '') + '>Xóa</button>' +
                '</td></tr>';
        }).join('');
    }

    async function loadList() {
        hideAlert();
        var res = await PortalApi.get('/api/portal/classrooms');
        renderRows(res.data || []);
    }

    function resetForm() {
        form.reset();
        form.capacity.value = '30';
        form.status.value = 'ACTIVE';
        if (editId) editId.value = '';
        if (btnCancel) btnCancel.classList.add('d-none');
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        hideAlert();
        var payload = {
            room_name: form.room_name.value.trim(),
            capacity: form.capacity.value,
            location: form.location.value.trim(),
            status: form.status.value
        };
        try {
            if (editId && editId.value) {
                await PortalApi.put('/api/portal/classrooms/' + editId.value, payload);
                showAlert('Đã cập nhật phòng học.', 'success');
            } else {
                await PortalApi.post('/api/portal/classrooms', payload);
                showAlert('Đã tạo phòng học.', 'success');
            }
            resetForm();
            await loadList();
        } catch (err) {
            showAlert(err.message || 'Lỗi lưu phòng học', 'danger');
        }
    });

    if (btnCancel) {
        btnCancel.addEventListener('click', function () {
            resetForm();
            hideAlert();
        });
    }

    tbody.addEventListener('click', async function (e) {
        var row = e.target.closest('tr[data-id]');
        if (!row) return;
        var id = row.getAttribute('data-id');

        if (e.target.closest('.js-edit')) {
            form.room_name.value = row.getAttribute('data-room') || '';
            form.capacity.value = row.getAttribute('data-capacity') || '30';
            form.location.value = row.getAttribute('data-location') || '';
            form.status.value = row.getAttribute('data-status') || 'ACTIVE';
            if (editId) editId.value = id;
            if (btnCancel) btnCancel.classList.remove('d-none');
            form.room_name.focus();
            return;
        }

        if (e.target.closest('.js-delete')) {
            if (!confirm('Xóa phòng này?')) return;
            hideAlert();
            try {
                await PortalApi.delete('/api/portal/classrooms/' + id);
                showAlert('Đã xóa phòng học.', 'success');
                if (editId && editId.value === id) resetForm();
                await loadList();
            } catch (err) {
                showAlert(err.message || 'Không thể xóa', 'danger');
            }
        }
    });

    loadList().catch(function (err) {
        showAlert(err.message || 'Không tải được danh sách', 'danger');
    });
})();
