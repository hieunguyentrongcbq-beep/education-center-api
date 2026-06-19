<?php
$typeLabels = ['LEAVE' => 'Xin nghỉ', 'MAKEUP' => 'Học bù'];
$statusLabels = ['PENDING' => 'Chờ duyệt', 'APPROVED' => 'Đã duyệt', 'REJECTED' => 'Đã từ chối'];
$statusBadges = ['PENDING' => 'bg-warning text-dark', 'APPROVED' => 'bg-success', 'REJECTED' => 'bg-danger'];
?>
<div class="card">
    <h2>Gửi yêu cầu</h2>
    <p class="form-hint mb-3">Gửi Admin duyệt. Yêu cầu <strong>học bù</strong> sau khi duyệt sẽ tự thêm buổi MAKEUP vào lịch học của bạn.</p>

    <?php if (empty($classes)): ?>
        <p class="empty mb-0">Bạn chưa có lớp đã thanh toán để gửi yêu cầu.</p>
    <?php else: ?>
    <form method="POST" action="<?= $url('student/leave/submit') ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Loại</label>
                <select name="request_type">
                    <option value="LEAVE">Xin nghỉ</option>
                    <option value="MAKEUP">Xin học bù</option>
                </select>
            </div>
            <div class="form-group">
                <label>Lớp *</label>
                <select name="class_id" required>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= (int)$c['id'] ?>">
                            <?= htmlspecialchars($c['class_code'] . ' — ' . ($c['course_name'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Ngày *</label>
                <input type="date" name="request_date" required value="<?= date('Y-m-d') ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Lý do *</label>
            <textarea name="reason" rows="3" required placeholder="Mô tả lý do nghỉ hoặc xin học bù..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Gửi Admin duyệt</button>
    </form>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Yêu cầu của tôi</h2>
    <?php if (empty($requests)): ?>
        <p class="empty mb-0">Chưa có yêu cầu nào.</p>
    <?php else: ?>
    <div class="table-responsive-wrap">
        <table>
            <thead>
                <tr>
                    <th>Loại</th>
                    <th>Lớp</th>
                    <th>Ngày</th>
                    <th>Trạng thái</th>
                    <th>Lý do</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($requests as $r):
                $st = $r['status'] ?? 'PENDING';
                $tp = $r['request_type'] ?? 'LEAVE';
            ?>
                <tr>
                    <td><?= htmlspecialchars($typeLabels[$tp] ?? $tp) ?></td>
                    <td><?= htmlspecialchars($r['class_code'] ?? '-') ?></td>
                    <td><?= date('d/m/Y', strtotime($r['request_date'])) ?></td>
                    <td>
                        <span class="badge <?= $statusBadges[$st] ?? 'bg-secondary' ?>">
                            <?= htmlspecialchars($statusLabels[$st] ?? $st) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($r['reason'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
