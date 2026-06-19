<?php

$statusOptions = [

    '' => 'Tất cả trạng thái',

    'PENDING' => 'Chờ duyệt',

    'APPROVED' => 'Đã duyệt',

    'REJECTED' => 'Đã từ chối',

];

$typeOptions = [

    '' => 'Tất cả loại',

    'LEAVE' => 'Xin nghỉ',

    'MAKEUP' => 'Học bù',

];

$currentStatus = $filterStatus ?? '';

$currentType = $filterType ?? '';

?>

<div class="card">

    <h2>Duyệt yêu cầu xin nghỉ / học bù</h2>

    <p class="form-hint mb-3">Duyệt <strong>MAKEUP</strong> → hệ thống tự tạo buổi học bù trên lịch và thông báo HV/GV.</p>



    <form method="GET" action="<?= $url('admin/leave-requests') ?>" class="mb-4">

        <div class="form-row align-items-end">

            <div class="form-group">

                <label>Trạng thái</label>

                <select name="status" onchange="this.form.submit()">

                    <?php foreach ($statusOptions as $val => $label): ?>

                        <option value="<?= htmlspecialchars($val) ?>" <?= $currentStatus === $val ? 'selected' : '' ?>>

                            <?= htmlspecialchars($label) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="form-group">

                <label>Loại yêu cầu</label>

                <select name="type" onchange="this.form.submit()">

                    <?php foreach ($typeOptions as $val => $label): ?>

                        <option value="<?= htmlspecialchars($val) ?>" <?= $currentType === $val ? 'selected' : '' ?>>

                            <?= htmlspecialchars($label) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="form-group">

                <a href="<?= $url('admin/leave-requests') ?>" class="btn btn-light btn-sm">Xóa bộ lọc</a>

            </div>

        </div>

    </form>



    <?php if (empty($requests)): ?>

        <p class="empty mb-0">Không có yêu cầu nào<?= $currentStatus || $currentType ? ' phù hợp bộ lọc' : '' ?>.</p>

    <?php else: ?>

    <table>

        <thead><tr><th>Loại</th><th>Người gửi</th><th>Lớp</th><th>Ngày</th><th>Lý do</th><th>TT</th><th></th></tr></thead>

        <tbody>

        <?php
        $statusBadges = [
            'PENDING' => 'bg-warning text-dark',
            'APPROVED' => 'bg-success',
            'REJECTED' => 'bg-danger',
        ];
        foreach ($requests as $r):
            $st = $r['status'] ?? '';
            $statusBadge = $statusBadges[$st] ?? 'bg-secondary';
        ?>

            <tr>

                <td>

                    <?php if (($r['request_type'] ?? 'LEAVE') === 'MAKEUP'): ?>

                        <span class="badge bg-success">Học bù</span>

                    <?php else: ?>

                        <span class="badge bg-secondary">Nghỉ</span>

                    <?php endif; ?>

                </td>

                <td><?= htmlspecialchars($r['requester_name'] ?? '') ?> (<?= $r['requester_type'] ?>)</td>

                <td><?= htmlspecialchars($r['class_code'] ?? '-') ?></td>

                <td><?= date('d/m/Y', strtotime($r['request_date'])) ?></td>

                <td><?= htmlspecialchars($r['reason']) ?></td>

                <td><span class="badge <?= $statusBadge ?>"><?= htmlspecialchars($st) ?></span></td>

                <td>

                    <?php if ($st === 'PENDING'): ?>

                    <form method="POST" action="<?= $url('admin/leave-requests/review') ?>" style="display:inline">

                        <input type="hidden" name="id" value="<?= $r['id'] ?>">

                        <input type="hidden" name="filter_status" value="<?= htmlspecialchars($currentStatus) ?>">

                        <input type="hidden" name="filter_type" value="<?= htmlspecialchars($currentType) ?>">

                        <button name="action" value="approve" class="btn btn-sm btn-primary">Duyệt</button>

                        <button name="action" value="reject" class="btn btn-sm btn-danger">Từ chối</button>

                    </form>

                    <?php endif; ?>

                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

    <?php endif; ?>

</div>


