<?php

$cl = $class ?? [];
$isEdit = !empty($cl['id']);

$courseId = (int)$old('course_id', $cl['course_id'] ?? 0);

$semesterId = (int)$old('semester_id', $cl['semester_id'] ?? 0);

$classroomId = (int)$old('classroom_id', $cl['classroom_id'] ?? 0);

$statusVal = $old('status', $cl['status'] ?? 'UPCOMING');

?>

<div class="card">

    <form method="POST" action="<?= $url($isEdit ? 'admin/classes/'.$cl['id'].'/update' : 'admin/classes/store') ?>">

        <div class="form-row">

            <div class="form-group">

                <label>Mã lớp *</label>

                <input name="class_code" value="<?= htmlspecialchars($old('class_code', $cl['class_code'] ?? '')) ?>" <?= $isEdit ? 'readonly' : 'required' ?> maxlength="40" pattern="[A-Za-z0-9_-]{2,40}" title="2–40 ký tự: chữ, số, gạch ngang">

            </div>

            <div class="form-group">

                <label>Khóa học *</label>

                <select name="course_id" required <?= $isEdit ? 'disabled' : '' ?>>

                    <option value="">-- Chọn --</option>

                    <?php foreach ($courses as $co): ?>

                        <option value="<?= $co['id'] ?>" <?= $courseId === (int)$co['id'] ? 'selected' : '' ?>>

                            <?= htmlspecialchars($co['course_code'].' - '.$co['course_name'].' ('.$co['total_sessions'].' buổi)') ?>

                        </option>

                    <?php endforeach; ?>

                </select>

                <?php if ($isEdit): ?>

                    <input type="hidden" name="course_id" value="<?= (int)($cl['course_id'] ?? 0) ?>">

                <?php endif; ?>

            </div>

        </div>

        <div class="form-row">

            <div class="form-group">

                <label>Học kỳ</label>

                <select name="semester_id">

                    <option value="">-- Chọn --</option>

                    <?php foreach ($semesters as $s): ?>

                        <option value="<?= $s['id'] ?>" <?= $semesterId === (int)$s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['semester_name']) ?></option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="form-group">

                <label>Phòng học</label>

                <select name="classroom_id">

                    <option value="">-- Chọn --</option>

                    <?php foreach ($classrooms as $rm): ?>

                        <option value="<?= $rm['id'] ?>" <?= $classroomId === (int)$rm['id'] ? 'selected' : '' ?>><?= htmlspecialchars($rm['room_name']) ?></option>

                    <?php endforeach; ?>

                </select>

            </div>

        </div>

        <div class="form-row">

            <div class="form-group">

                <label>Ngày bắt đầu *</label>

                <input type="date" name="start_date" value="<?= htmlspecialchars($old('start_date', $cl['start_date'] ?? '')) ?>" required>

                <?php if (!$isEdit): ?>

                <div class="form-hint">Ngày kết thúc tự tính từ số buổi khóa học (2 buổi/tuần)</div>

                <?php endif; ?>

            </div>

            <?php if ($isEdit): ?>

            <div class="form-group">

                <label>Ngày kết thúc *</label>

                <input type="date" name="end_date" value="<?= htmlspecialchars($old('end_date', $cl['end_date'] ?? '')) ?>" required>

            </div>

            <?php endif; ?>

            <div class="form-group">

                <label>Sĩ số tối đa *</label>

                <input type="number" name="max_students" min="1" max="500" value="<?= htmlspecialchars($old('max_students', $cl['max_students'] ?? 25)) ?>" required>

            </div>

        </div>

        <div class="form-group">

            <label>Trạng thái</label>

            <select name="status">

                <?php foreach (['UPCOMING', 'ONGOING', 'COMPLETED', 'CANCELLED'] as $st): ?>

                    <option value="<?= $st ?>" <?= $statusVal === $st ? 'selected' : '' ?>><?= $st ?></option>

                <?php endforeach; ?>

            </select>

        </div>

        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Cập nhật' : 'Tạo lớp' ?></button>

        <a href="<?= $url('admin/classes') ?>" class="btn btn-secondary">Hủy</a>

    </form>

</div>


