<h1 class="mb-1">Đăng nhập</h1>
<p class="auth-subtitle">Chào mừng trở lại EduCenter</p>

<form method="POST" action="<?= $url('login') ?>">
    <div class="form-group">
        <label class="form-label">Email</label>
        <div class="input-group">
            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($old('email')) ?>" placeholder="admin@edu.vn" required>
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">Mật khẩu</label>
        <div class="input-group">
            <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required minlength="6">
        </div>
    </div>
    <button type="submit" class="btn btn-primary w-100 py-2 mt-2">
        <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập
    </button>
</form>

<div class="mt-4 p-3 rounded-3 bg-light border">
    <div class="small text-muted fw-semibold mb-2"><i class="bi bi-info-circle me-1"></i> Tài khoản demo</div>
    <div class="small text-secondary">
        <div><strong>Admin:</strong> admin@edu.vn / admin123</div>
        <div><strong>GV:</strong> tuan.gv@edu.vn / teacher123</div>
        <div><strong>HV:</strong> an.hv@edu.vn / student123</div>
    </div>
</div>
