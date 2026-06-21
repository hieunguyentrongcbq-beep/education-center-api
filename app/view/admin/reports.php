<div class="card">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h2 class="mb-0">Thống kê Giáo viên</h2>
        <a href="<?= $url('admin/reports/teachers/export') ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
    </div>
    <table>
        <thead><tr><th>Mã GV</th><th>Họ tên</th><th>Phân công</th><th>Buổi chấm công</th><th>ĐTB khảo sát</th></tr></thead>
        <tbody>
        <?php foreach ($teacherStats as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t['teacher_code']) ?></td>
                <td><?= htmlspecialchars($t['full_name']) ?></td>
                <td><?= (int)$t['assignments'] ?></td>
                <td><?= (int)$t['paid_sessions'] ?></td>
                <td><?= $t['avg_survey'] ? number_format((float)$t['avg_survey'], 1).'/5' : '-' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="card">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h2 class="mb-0">Thống kê Học viên</h2>
        <a href="<?= $url('admin/reports/students/export') ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
    </div>
    <table>
        <thead><tr><th>Mã HV</th><th>Họ tên</th><th>Số lớp</th><th>ĐTB điểm</th><th>Buổi có mặt</th></tr></thead>
        <tbody>
        <?php foreach ($studentStats as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['student_code']) ?></td>
                <td><?= htmlspecialchars($s['full_name']) ?></td>
                <td><?= (int)$s['classes_joined'] ?></td>
                <td><?= $s['avg_score'] ? number_format((float)$s['avg_score'], 1) : '-' ?></td>
                <td><?= (int)$s['present_count'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
