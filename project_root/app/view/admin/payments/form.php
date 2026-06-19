<?php
$selStudent = (int)($prefillStudentId ?? 0);
$selClass = (int)($prefillClassId ?? 0);
$fromPending = $selStudent > 0 && $selClass > 0;
?>
<div class="card">
    <h2>Xác nhận thanh toán → Vào lớp + Lịch học</h2>
    <?php if ($fromPending): ?>
        <p class="form-hint mb-3"><i class="bi bi-info-circle me-1"></i>Duyệt từ danh sách chờ thanh toán — thông tin đã được điền sẵn.</p>
    <?php endif; ?>
    <form method="POST" action="<?= $url('admin/payments/confirm') ?>">
        <div class="form-group">
            <label>Học viên *</label>
            <select name="student_id" required>
                <option value="">-- Chọn học viên --</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $selStudent === (int)$s['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['student_code'].' - '.$s['full_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Lớp học *</label>
            <select name="class_id" required>
                <option value="">-- Chọn lớp --</option>
                <?php foreach ($classes as $cl): ?>
                    <option value="<?= $cl['id'] ?>" <?= $selClass === (int)$cl['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cl['class_code'].' - '.($cl['course_name']??'')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Phương thức thanh toán *</label>
            <select name="payment_method" required>
                <option value="CASH">Tiền mặt</option>
                <option value="BANK_TRANSFER">Chuyển khoản</option>
                <option value="CARD">Thẻ</option>
            </select>
        </div>
        <div class="form-group">
            <label>Phân công giáo viên</label>
            <select name="teacher_id">
                <option value="">-- Tự động: GV mặc định của lớp --</option>
                <?php foreach ($teachers as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['teacher_code'].' - '.$t['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="form-hint">Sau thanh toán, hệ thống phân công GV cho <strong>cả thứ chính và thứ phụ</strong> của khóa học (nếu lớp có GV mặc định hoặc bạn chọn GV). Kiểm tra trùng lịch trước khi lưu.</div>
        </div>
        <button type="submit" class="btn btn-primary">Xác nhận thanh toán</button>
        <a href="<?= $url('admin/payments') ?>" class="btn btn-secondary">Hủy</a>
    </form>
</div>
