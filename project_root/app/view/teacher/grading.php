<div class="card">
    <form method="GET">
        <div class="form-group">
            <label>Lọc theo lớp</label>
            <select name="class_id" onchange="this.form.submit()">
                <option value="">Tất cả</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($classId??0)===(int)$c['id']?'selected':'' ?>><?= htmlspecialchars($c['class_code']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<div class="card">
    <h2>Chấm bài nộp (PDF)</h2>
    <?php if (empty($submissions)): ?><p class="empty">Chưa có bài nộp</p><?php else: ?>
    <table>
        <thead><tr><th>HV</th><th>Lớp</th><th>Loại</th><th>File</th><th>Điểm</th><th>Chấm</th></tr></thead>
        <tbody>
        <?php foreach ($submissions as $sub): ?>
            <tr>
                <td><?= htmlspecialchars($sub['full_name']) ?></td>
                <td><?= htmlspecialchars($sub['class_code']) ?></td>
                <td><?= htmlspecialchars($sub['type']) ?></td>
                <td><a href="<?= htmlspecialchars(dirname($baseUrl) . '/' . $sub['file_path']) ?>" target="_blank">Xem PDF</a></td>
                <td><?= $sub['score'] !== null ? $sub['score'].'/10' : '-' ?></td>
                <td>
                    <form method="POST" action="<?= $url('teacher/grading/score') ?>" style="display:flex;gap:4px;flex-wrap:wrap">
                        <input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
                        <input type="hidden" name="class_id" value="<?= $sub['class_id'] ?>">
                        <input type="number" name="score" min="0" max="10" step="0.5" value="<?= $sub['score'] ?? '' ?>" style="width:60px" required>
                        <input name="comment" placeholder="Nhận xét" value="<?= htmlspecialchars($sub['comment'] ?? '') ?>" style="width:120px">
                        <button class="btn btn-sm btn-primary">Lưu</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php if ($classId): ?>
<div class="card">
    <h2>Tổng kết & Đánh giá năng lực</h2>
    <?php if (empty($evaluations)): ?>
        <p class="empty mb-0">Không có học viên được phân công trong lớp này.</p>
    <?php else: ?>
    <table>
        <thead><tr><th>HV</th><th>ĐTB</th><th>Xếp loại</th><th>Học lại?</th><th>Nhận xét</th></tr></thead>
        <tbody>
        <?php foreach ($evaluations as $ev):
            $hasGrades = (int)($ev['graded_count'] ?? 0) > 0;
        ?>
            <tr>
                <td>
                    <?= htmlspecialchars($ev['full_name']) ?>
                    <br><small class="text-muted"><?= htmlspecialchars($ev['student_code']) ?></small>
                    <?php if (!$hasGrades): ?>
                        <br><span class="badge bg-secondary">Chưa nộp / chưa chấm</span>
                    <?php endif; ?>
                </td>
                <td><?= $hasGrades ? number_format((float)$ev['avg_score'], 1) : '—' ?></td>
                <td><?= $hasGrades && !empty($ev['level']) ? $p2->levelLabel($ev['level']) : '—' ?></td>
                <td><?= $hasGrades ? ($ev['retake_needed'] ? 'Có' : 'Không') : '—' ?></td>
                <td>
                    <form method="POST" action="<?= $url('teacher/grading/evaluate') ?>">
                        <input type="hidden" name="student_id" value="<?= (int)$ev['student_id'] ?>">
                        <input type="hidden" name="class_id" value="<?= (int)$classId ?>">
                        <input name="teacher_comment" value="<?= htmlspecialchars($ev['teacher_comment'] ?? '') ?>" style="width:150px" placeholder="Nhận xét GV">
                        <label><input type="checkbox" name="retake_needed" <?= !empty($ev['retake_needed']) ? 'checked' : '' ?>> Học lại</label>
                        <button class="btn btn-sm btn-secondary">Lưu</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="form-hint">Hiển thị mọi HV được phân công. ĐTB/xếp loại tự động khi đã chấm bài (≥8 Giỏi, ≥6.5 Khá, ≥5 TB, &lt;5 Kém). Có thể nhận xét / đánh dấu học lại ngay cả khi chưa nộp bài.</p>
    <?php endif; ?>
</div>
<?php endif; ?>
