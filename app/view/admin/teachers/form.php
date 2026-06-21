<?php

$isEdit = !empty($teacher);

$t = $teacher ?? [];

$statusVal = $old('status', $t['status'] ?? 'ACTIVE');
$typeVal = $old('teacher_type', $t['teacher_type'] ?? 'FULL_TIME');
$hoursVal = $old('standard_hours', $t['standard_hours'] ?? '40');

?>

<div class="card">

    <form method="POST" action="<?= $url($isEdit ? 'admin/teachers/'.$t['id'].'/update' : 'admin/teachers/store') ?>">

        <div class="form-group">

            <label>Họ tên *</label>

            <input name="full_name" value="<?= htmlspecialchars($old('full_name', $t['full_name'] ?? '')) ?>" required minlength="2" maxlength="200">

        </div>

        <div class="form-row">

            <div class="form-group">

                <label>Email *</label>

                <input type="email" name="email" value="<?= htmlspecialchars($old('email', $t['email'] ?? '')) ?>" required maxlength="255">

            </div>

            <?php if (!$isEdit): ?>

            <div class="form-group">

                <label>Mã giáo viên *</label>

                <input name="teacher_code" value="<?= htmlspecialchars($old('teacher_code')) ?>" required maxlength="30" pattern="[A-Za-z0-9_-]{2,30}" title="2–30 ký tự: chữ, số, gạch ngang">

            </div>

            <?php else: ?>

            <div class="form-group">

                <label>Mã giáo viên</label>

                <input value="<?= htmlspecialchars($t['teacher_code'] ?? '') ?>" readonly class="bg-light">

            </div>

            <?php endif; ?>

        </div>

        <div class="form-row">

            <div class="form-group">

                <label>Điện thoại</label>

                <input name="phone" value="<?= htmlspecialchars($old('phone', $t['phone'] ?? '')) ?>" placeholder="0912345678" maxlength="20">

            </div>

            <div class="form-group">

                <label><?= $isEdit ? 'Mật khẩu mới' : 'Mật khẩu' ?></label>

                <input type="password" name="password" minlength="6" placeholder="<?= $isEdit ? 'Để trống nếu không đổi' : 'Mặc định giaovien123' ?>">

                <?php if ($isEdit): ?>

                <div class="form-hint">Chỉ nhập khi muốn đổi mật khẩu đăng nhập</div>

                <?php endif; ?>

            </div>

        </div>

        <div class="form-row">

            <div class="form-group">

                <label>Chuyên môn *</label>

                <input name="specialization" value="<?= htmlspecialchars($old('specialization', $t['specialization'] ?? '')) ?>" required minlength="2" maxlength="200">

            </div>

            <div class="form-group">

                <label>Ngày vào</label>

                <input type="date" name="hire_date" value="<?= htmlspecialchars($old('hire_date', $t['hire_date'] ?? '')) ?>" max="<?= date('Y-m-d') ?>">

            </div>

        </div>

        <div class="form-row">

            <div class="form-group">

                <label>Loại giáo viên</label>

                <select name="teacher_type" id="teacher-type-select">

                    <option value="FULL_TIME" <?= $typeVal === 'FULL_TIME' ? 'selected' : '' ?>>Cơ hữu (FULL_TIME)</option>

                    <option value="VISITING" <?= $typeVal === 'VISITING' ? 'selected' : '' ?>>Mời giảng (VISITING)</option>

                </select>

            </div>

            <div class="form-group" id="standard-hours-group">

                <label>Giờ chuẩn / tháng</label>

                <input type="number" name="standard_hours" id="standard-hours-input" min="1" max="200" step="0.5"
                       value="<?= htmlspecialchars($hoursVal) ?>" placeholder="VD: 40">

                <div class="form-hint">Áp dụng GV cơ hữu — dùng cho báo cáo chấm công / lương</div>

            </div>

        </div>

        <div class="form-group">

            <label>Trạng thái</label>

            <select name="status">

                <option value="ACTIVE" <?= $statusVal === 'ACTIVE' ? 'selected' : '' ?>>ACTIVE</option>

                <option value="INACTIVE" <?= $statusVal === 'INACTIVE' ? 'selected' : '' ?>>INACTIVE</option>

            </select>

            <?php if ($isEdit): ?>

            <div class="form-hint">INACTIVE bị chặn nếu GV còn phân công hoặc đang gán vào lớp</div>

            <?php endif; ?>

        </div>

        <script>
        (function () {
            var typeSel = document.getElementById('teacher-type-select');
            var hoursGroup = document.getElementById('standard-hours-group');
            var hoursInput = document.getElementById('standard-hours-input');
            if (!typeSel || !hoursGroup) return;
            function syncHoursField() {
                var visiting = typeSel.value === 'VISITING';
                hoursGroup.style.display = visiting ? 'none' : '';
                if (hoursInput) hoursInput.required = !visiting;
            }
            typeSel.addEventListener('change', syncHoursField);
            syncHoursField();
        })();
        </script>

        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Cập nhật' : 'Tạo mới' ?></button>

        <a href="<?= $url('admin/teachers') ?>" class="btn btn-secondary">Hủy</a>

    </form>

</div>


