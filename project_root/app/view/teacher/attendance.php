<div class="card">
    <form method="GET" class="form-row" style="margin-bottom:16px">
        <div class="form-group">
            <label>Lớp</label>
            <select name="class_id" onchange="this.form.submit()">
                <option value="">-- Chọn lớp --</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $classId===(int)$c['id']?'selected':'' ?>><?= htmlspecialchars($c['class_code'].' - '.$c['course_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Ngày</label>
            <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" onchange="this.form.submit()">
        </div>
    </form>

    <?php
    $statusOptions = [
        'PRESENT' => ['label' => 'Có mặt',   'icon' => 'bi-check-circle-fill'],
        'ABSENT'  => ['label' => 'Vắng',     'icon' => 'bi-x-circle-fill'],
        'LATE'    => ['label' => 'Muộn',     'icon' => 'bi-clock-fill'],
        'EXCUSED' => ['label' => 'Có phép',  'icon' => 'bi-shield-check'],
    ];
    ?>
    <?php if ($classId && $students): ?>
    <form method="POST" action="<?= $url('teacher/attendance/mark') ?>" class="attendance-form">
        <input type="hidden" name="class_id" value="<?= $classId ?>">
        <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
        <label class="att-teacher-check">
            <input type="checkbox" name="teacher_present" value="1">
            <span><i class="bi bi-person-check"></i> GV có mặt (gửi thông báo chấm công cho Admin)</span>
        </label>
        <div class="table-responsive-wrap attendance-table-wrap">
        <table class="attendance-table">
            <thead><tr><th>Học viên</th><th>Trạng thái</th><th>Ghi chú</th></tr></thead>
            <tbody>
            <?php foreach ($students as $s):
                $m = $markedMap[$s['id']] ?? null;
                $st = $m['attendance_status'] ?? 'PRESENT';
            ?>
                <tr>
                    <td class="att-student-cell">
                        <span class="att-student-code"><?= htmlspecialchars($s['student_code']) ?></span>
                        <span class="att-student-name"><?= htmlspecialchars($s['full_name']) ?></span>
                    </td>
                    <td>
                        <div class="att-status-wrap">
                            <i class="bi att-status-icon <?= htmlspecialchars($statusOptions[$st]['icon'] ?? 'bi-circle-fill') ?>"></i>
                            <select name="status[<?= $s['id'] ?>]" class="att-status-select att-status-<?= strtolower($st) ?>" data-att-status>
                                <?php foreach ($statusOptions as $opt => $meta): ?>
                                    <option value="<?= $opt ?>" <?= $st === $opt ? 'selected' : '' ?>><?= $meta['label'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </td>
                    <td>
                        <input type="text" class="att-note-input" name="note[<?= $s['id'] ?>]" value="<?= htmlspecialchars($m['note'] ?? '') ?>" placeholder="Ghi chú (nếu có)">
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <button type="submit" class="btn btn-primary att-submit-btn"><i class="bi bi-save"></i> Lưu điểm danh</button>
    </form>
    <script>
    (function () {
        var icons = {
            PRESENT: 'bi-check-circle-fill',
            ABSENT: 'bi-x-circle-fill',
            LATE: 'bi-clock-fill',
            EXCUSED: 'bi-shield-check'
        };
        document.querySelectorAll('[data-att-status]').forEach(function (sel) {
            function sync() {
                var v = sel.value;
                sel.className = 'att-status-select att-status-' + v.toLowerCase();
                var icon = sel.closest('.att-status-wrap')?.querySelector('.att-status-icon');
                if (icon) {
                    icon.className = 'bi att-status-icon ' + (icons[v] || 'bi-circle-fill');
                }
            }
            sel.addEventListener('change', sync);
            sync();
        });
    })();
    </script>
    <?php elseif ($classId): ?>
        <p class="empty">Không có học viên trong lớp này.</p>
    <?php else: ?>
        <p class="empty">Chọn lớp để điểm danh.</p>
    <?php endif; ?>
</div>
