<?php
$isEdit = !empty($student);
$s = $student ?? [];
$statusVal = $old('status', $s['status'] ?? 'ACTIVE');
?>
<div class="card">
    <form method="POST" action="<?= $url($isEdit ? 'admin/students/'.$s['id'].'/update' : 'admin/students/store') ?>">
        <div class="form-group">
            <label>Họ tên *</label>
            <input name="full_name" value="<?= htmlspecialchars($old('full_name', $s['full_name'] ?? '')) ?>" required minlength="2" maxlength="200">
        </div>
        <?php if (!$isEdit): ?>
        <div class="form-row">
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($old('email')) ?>" required maxlength="255">
            </div>
            <div class="form-group">
                <label>Mã học viên *</label>
                <input name="student_code" value="<?= htmlspecialchars($old('student_code')) ?>" required maxlength="30" pattern="[A-Za-z0-9_-]{2,30}" title="2–30 ký tự: chữ, số, gạch ngang">
            </div>
        </div>
        <div class="form-group">
            <label>Mật khẩu</label>
            <input type="password" name="password" minlength="6" placeholder="Để trống = hocvien123">
            <div class="form-hint">Tối thiểu 6 ký tự nếu nhập</div>
        </div>
        <?php else: ?>
        <div class="form-group">
            <label>Email / Mã HV</label>
            <input value="<?= htmlspecialchars(($s['email'] ?? '').' / '.($s['student_code'] ?? '')) ?>" readonly class="bg-light">
            <div class="form-hint">Email và mã học viên không đổi sau khi tạo</div>
        </div>
        <?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label>Điện thoại</label>
                <input name="phone" value="<?= htmlspecialchars($old('phone', $s['phone'] ?? '')) ?>" placeholder="0912345678" maxlength="20">
            </div>
            <div class="form-group">
                <label>SĐT phụ huynh</label>
                <input name="parent_phone" value="<?= htmlspecialchars($old('parent_phone', $s['parent_phone'] ?? '')) ?>" placeholder="0987654321" maxlength="20">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Ngày sinh</label>
                <input type="date" name="date_of_birth" value="<?= htmlspecialchars($old('date_of_birth', $s['date_of_birth'] ?? '')) ?>" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group">
                <label>Trạng thái</label>
                <select name="status">
                    <?php foreach (['ACTIVE', 'INACTIVE', 'GRADUATED'] as $st): ?>
                        <option value="<?= $st ?>" <?= $statusVal === $st ? 'selected' : '' ?>><?= $st ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($isEdit): ?>
                <div class="form-hint">INACTIVE bị chặn nếu HV đang học lớp đã thanh toán</div>
                <?php endif; ?>
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Cập nhật' : 'Tạo mới' ?></button>
        <a href="<?= $url('admin/students') ?>" class="btn btn-secondary">Hủy</a>
    </form>
</div>
