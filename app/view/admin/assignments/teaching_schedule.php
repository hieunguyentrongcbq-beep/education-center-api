<?php
$weekDayLabels = $weekDayLabels ?? [];
$weekDayOrder = $weekDayOrder ?? [1, 2, 3, 4, 5, 6, 0];
$classStatusLabels = [
    'UPCOMING' => 'Sắp khai giảng',
    'ONGOING'  => 'Đang học',
    'COMPLETED'=> 'Đã kết thúc',
    'CANCELLED'=> 'Đã hủy',
];
$totalTeachers = count(array_unique(array_column($rows, 'teacher_id')));
$totalClasses = count(array_unique(array_column($rows, 'class_id')));
?>
<div class="page-actions actions d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <a href="<?= $url('admin/assignments') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-clipboard-check me-1"></i> Phân công GV
    </a>
    <span class="text-muted small"><?= count($rows) ?> dòng lịch dạy · <?= $totalTeachers ?> GV · <?= $totalClasses ?> lớp</span>
</div>

<div class="card">
    <h2><i class="bi bi-calendar3 me-2 text-primary"></i>Lịch dạy giáo viên</h2>
    <p class="form-hint">Danh sách giáo viên đang được phân công dạy (CONFIRMED) các lớp UPCOMING / ONGOING — theo thứ và khung giờ.</p>

    <form method="GET" action="<?= $url('admin/teaching-schedule') ?>" class="form-row" style="margin-bottom:20px">
        <div class="form-group">
            <label>Giáo viên</label>
            <select name="teacher_id" onchange="this.form.submit()">
                <option value="">— Tất cả —</option>
                <?php foreach ($teachers as $t): ?>
                    <option value="<?= (int)$t['id'] ?>" <?= ($teacherId ?? 0) === (int)$t['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['teacher_code'] . ' — ' . $t['full_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Lớp học</label>
            <select name="class_id" onchange="this.form.submit()">
                <option value="">— Tất cả —</option>
                <?php foreach ($classes as $cl): ?>
                    <option value="<?= (int)$cl['id'] ?>" <?= ($classId ?? 0) === (int)$cl['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cl['class_code'] . ' — ' . ($cl['course_name'] ?? '')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Thứ dạy</label>
            <select name="day_of_week" onchange="this.form.submit()">
                <option value="">— Tất cả —</option>
                <?php foreach ($weekDayOrder as $dow): ?>
                    <option value="<?= $dow ?>" <?= ($dayOfWeek ?? null) === $dow ? 'selected' : '' ?>>
                        <?= htmlspecialchars($weekDayLabels[$dow] ?? ('Thứ ' . $dow)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="align-self:flex-end">
            <a href="<?= $url('admin/teaching-schedule') ?>" class="btn btn-light">Xóa lọc</a>
        </div>
    </form>

    <?php if (empty($rows)): ?>
        <p class="empty mb-0">Không có lịch dạy phù hợp bộ lọc. Thử <a href="<?= $url('admin/assignments') ?>">phân công giáo viên</a> hoặc xác nhận thanh toán để tự phân công.</p>
    <?php else: ?>
    <div class="table-responsive-wrap">
        <table class="teaching-schedule-table">
            <thead>
                <tr>
                    <th>Mã GV</th>
                    <th>Họ tên giáo viên</th>
                    <th>Lớp</th>
                    <th>Khóa học</th>
                    <th>Thứ dạy</th>
                    <th>Giờ dạy</th>
                    <th>Học viên</th>
                    <th>Phòng</th>
                    <th>Thời gian lớp</th>
                    <th>TT lớp</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r):
                $st = $r['class_status'] ?? '';
                $badgeClass = match ($st) {
                    'ONGOING' => 'bg-success',
                    'UPCOMING' => 'bg-info text-dark',
                    default => 'bg-secondary',
                };
            ?>
                <tr>
                    <td><span class="fw-semibold text-primary"><?= htmlspecialchars($r['teacher_code']) ?></span></td>
                    <td><?= htmlspecialchars($r['teacher_name']) ?></td>
                    <td>
                        <a href="<?= $url('admin/assignments?class_id=' . (int)$r['class_id']) ?>" class="fw-semibold">
                            <?= htmlspecialchars($r['class_code']) ?>
                        </a>
                    </td>
                    <td>
                        <span><?= htmlspecialchars($r['course_name']) ?></span>
                        <br><small class="text-muted"><?= htmlspecialchars($r['course_code']) ?></small>
                    </td>
                    <td>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            <?= htmlspecialchars($r['day_label']) ?>
                        </span>
                    </td>
                    <td class="text-nowrap fw-semibold">
                        <?= substr($r['start_time'], 0, 5) ?> – <?= substr($r['end_time'], 0, 5) ?>
                    </td>
                    <td><?= htmlspecialchars($r['student_label']) ?></td>
                    <td><?= htmlspecialchars($r['room_name'] ?? '—') ?></td>
                    <td class="text-nowrap small">
                        <?php if (!empty($r['start_date']) && !empty($r['end_date'])): ?>
                            <?= date('d/m/Y', strtotime($r['start_date'])) ?>
                            → <?= date('d/m/Y', strtotime($r['end_date'])) ?>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td>
                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($classStatusLabels[$st] ?? $st) ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
