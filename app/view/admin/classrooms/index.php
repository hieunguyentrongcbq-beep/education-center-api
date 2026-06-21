<div id="classrooms-ajax-panel" class="card">
    <p class="form-hint">Phòng học dùng khi mở lớp. Chỉ phòng <strong>ACTIVE</strong> hiện trong dropdown form lớp. Thao tác bên dưới dùng AJAX — không tải lại trang.</p>

    <div id="classroom-ajax-alert" class="alert d-none py-2" role="alert"></div>

    <form id="classroom-ajax-form" class="mb-4">
        <input type="hidden" id="classroom-ajax-id" name="id" value="">
        <h2 class="h5 mb-3">Thêm / sửa phòng học</h2>
        <div class="form-row">
            <div class="form-group">
                <label>Tên phòng *</label>
                <input name="room_name" required maxlength="50" placeholder="VD: P101">
            </div>
            <div class="form-group">
                <label>Sức chứa *</label>
                <input type="number" name="capacity" min="1" max="500" required value="30">
            </div>
            <div class="form-group">
                <label>Trạng thái</label>
                <select name="status">
                    <option value="ACTIVE">Đang sử dụng</option>
                    <option value="INACTIVE">Ngưng sử dụng</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>Vị trí / Tòa nhà</label>
            <input name="location" maxlength="200" placeholder="VD: Tầng 1 — Tòa A">
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary">Lưu phòng</button>
            <button type="button" id="classroom-ajax-cancel" class="btn btn-light d-none">Hủy sửa</button>
            <a href="<?= $url('admin/classrooms/create') ?>" class="btn btn-outline-secondary btn-sm align-self-center">Form POST (dự phòng)</a>
        </div>
    </form>

    <div class="table-responsive-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên phòng</th>
                    <th>Sức chứa</th>
                    <th>Vị trí</th>
                    <th>Lớp đang dùng</th>
                    <th>TT</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="classroom-ajax-tbody">
                <tr><td colspan="7" class="text-muted">Đang tải…</td></tr>
            </tbody>
        </table>
    </div>
</div>
