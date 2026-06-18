<?php
$isEdit = !empty($classroom);
$rm = $classroom ?? [];
$statusVal = $old('status', $rm['status'] ?? 'ACTIVE');
?>
<div class="card">
    <h2><?= $isEdit ? 'Sửa phòng học' : 'Thêm phòng học' ?></h2>
    <form method="POST" action="<?= $url($isEdit ? 'admin/classrooms/' . (int)$rm['id'] . '/update' : 'admin/classrooms/store') ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Tên phòng *</label>
                <input name="room_name" value="<?= htmlspecialchars($old('room_name', $rm['room_name'] ?? '')) ?>"
                       required maxlength="50" placeholder="VD: P101">
            </div>
            <div class="form-group">
                <label>Sức chứa *</label>
                <input type="number" name="capacity" min="1" max="500" required
                       value="<?= htmlspecialchars($old('capacity', $rm['capacity'] ?? '30')) ?>">
            </div>
            <div class="form-group">
                <label>Trạng thái</label>
                <select name="status">
                    <option value="ACTIVE" <?= $statusVal === 'ACTIVE' ? 'selected' : '' ?>>Đang sử dụng</option>
                    <option value="INACTIVE" <?= $statusVal === 'INACTIVE' ? 'selected' : '' ?>>Ngưng sử dụng</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>Vị trí / Tòa nhà</label>
            <input name="location" maxlength="200" value="<?= htmlspecialchars($old('location', $rm['location'] ?? '')) ?>"
                   placeholder="VD: Tầng 1 — Tòa A">
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Lưu thay đổi' : 'Tạo phòng' ?></button>
            <a href="<?= $url('admin/classrooms') ?>" class="btn btn-light">Hủy</a>
        </div>
    </form>
</div>
