<div class="card">
    <h2>Khảo sát giáo viên (sau khi kết thúc khóa)</h2>
    <p class="form-hint">Chỉ hiện các lớp đã kết thúc hoặc sắp kết thúc.</p>
    <?php if (empty($classes)): ?>
        <p class="empty">Chưa có lớp nào đủ điều kiện khảo sát.</p>
    <?php else: ?>
    <?php foreach ($classes as $c): ?>
        <?php if ($c['responded']): ?>
            <p>✓ Đã khảo sát lớp <?= htmlspecialchars($c['class_code']) ?></p>
        <?php else: ?>
        <form method="POST" action="<?= $url('student/survey/submit') ?>" style="border:1px solid #e2e8f0;padding:16px;margin-bottom:12px;border-radius:8px">
            <input type="hidden" name="class_id" value="<?= $c['id'] ?>">
            <input type="hidden" name="teacher_id" value="<?= (int)$c['teacher_id'] ?>">
            <h3><?= htmlspecialchars($c['class_code'].' - '.$c['course_name']) ?></h3>
            <p>Giáo viên: <?= htmlspecialchars($c['teacher_name'] ?? 'Chưa phân công') ?></p>
            <div class="form-group">
                <label>Đánh giá (1-5 sao)</label>
                <select name="rating" required>
                    <?php for ($i=5;$i>=1;$i--): ?><option value="<?= $i ?>"><?= $i ?> sao</option><?php endfor; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Nhận xét</label>
                <textarea name="comment" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Gửi khảo sát</button>
        </form>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
