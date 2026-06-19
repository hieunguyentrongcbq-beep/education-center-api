<?php
$typeLabels = [
    'ASSIGNMENT' => 'Bài tập',
    'MIDTERM' => 'Giữa kì',
    'FINAL' => 'Cuối kì',
];
?>
<div class="card">
    <h2>Nộp bài tập / Giữa kì / Cuối kì (PDF)</h2>
    <?php if (empty($classes)): ?>
        <p class="form-hint mb-0">Bạn cần ghi danh và thanh toán lớp trước khi nộp bài.</p>
    <?php else: ?>
    <form method="POST" action="<?= $url('student/submissions/upload') ?>" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group">
                <label>Lớp</label>
                <select name="class_id" required>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_code'].' - '.$c['course_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Loại bài</label>
                <select name="type">
                    <option value="ASSIGNMENT">Bài tập</option>
                    <option value="MIDTERM">Giữa kì</option>
                    <option value="FINAL">Cuối kì</option>
                </select>
            </div>
            <div class="form-group">
                <label>File PDF (max 5MB)</label>
                <input type="file" name="file" accept=".pdf,application/pdf" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Nộp bài</button>
    </form>
    <?php endif; ?>
</div>
<div class="card">
    <h2>Bài đã nộp</h2>
    <?php if (empty($submissions)): ?>
        <p class="empty mb-0">Chưa có bài nộp.</p>
    <?php else: ?>
    <div class="table-responsive-wrap">
        <table>
            <thead>
                <tr>
                    <th>Lớp</th>
                    <th>Loại</th>
                    <th>Ngày nộp</th>
                    <th>Điểm</th>
                    <th>Nhận xét</th>
                    <th>File</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($submissions as $s):
                $type = $s['type'] ?? 'ASSIGNMENT';
                $hasFile = !empty($s['file_path']);
            ?>
                <tr>
                    <td><?= htmlspecialchars($s['class_code']) ?></td>
                    <td><?= htmlspecialchars($typeLabels[$type] ?? $type) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($s['submitted_at'])) ?></td>
                    <td><?= $s['score'] !== null ? $s['score'].'/10' : 'Chờ chấm' ?></td>
                    <td><?= htmlspecialchars($s['comment'] ?? '-') ?></td>
                    <td>
                        <?php if ($hasFile): ?>
                            <a href="<?= $url('student/submissions/' . (int)$s['id'] . '/download') ?>"
                               class="btn btn-sm btn-outline-primary"
                               title="Tải lại file PDF đã nộp">
                                <i class="bi bi-download"></i> Tải PDF
                            </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
