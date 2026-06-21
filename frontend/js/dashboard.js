/* ============================================================
   EDUCENTER — dashboard.js  (i18n-ready)
   ============================================================ */

// ============================================================
// KHỞI ĐỘNG
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    if (!localStorage.getItem('token')) {
        window.location.href = 'index.html';
        return;
    }
    loadUserInfo();
    setupSidebar();
    setupNavigation();
    setupModal();
    setupButtonListeners();
    startClock();
    loadPage('dashboard');
});

function loadUserInfo() {
    try {
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        if (user.full_name) {
            document.getElementById('user-name').textContent = user.full_name;
            const initials = user.full_name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
            document.getElementById('user-avatar').textContent = initials;
        }
        const role = user.role || (Array.isArray(user.roles) ? user.roles[0] : user.roles) || '';
        if (role) document.getElementById('user-role').textContent = role;
    } catch (e) {}
}

// ============================================================
// SIDEBAR
// ============================================================
function setupSidebar() {
    const sidebar = document.getElementById('sidebar');
    document.getElementById('sidebar-toggle').addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('collapsed'));
    });
    document.getElementById('mobile-toggle').addEventListener('click', () => {
        sidebar.classList.toggle('mobile-open');
    });
    if (localStorage.getItem('sidebar_collapsed') === 'true') {
        sidebar.classList.add('collapsed');
    }
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 900
            && !sidebar.contains(e.target)
            && e.target !== document.getElementById('mobile-toggle')) {
            sidebar.classList.remove('mobile-open');
        }
    });
    document.getElementById('btn-logout').addEventListener('click', handleLogout);
}

// ============================================================
// ĐIỀU HƯỚNG
// ============================================================
function setupNavigation() {
    document.querySelectorAll('.nav-item[data-page]').forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            navigateTo(item.dataset.page);
            document.getElementById('sidebar').classList.remove('mobile-open');
        });
    });
}

function navigateTo(page) {
    document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
    const navEl = document.querySelector(`.nav-item[data-page="${page}"]`);
    if (navEl) navEl.classList.add('active');

    document.getElementById('page-title').textContent = t('page_' + page) || page;

    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    const pageEl = document.getElementById(`page-${page}`);
    if (pageEl) pageEl.classList.add('active');

    loadPage(page);
}

function loadPage(page) {
    const loaders = {
        dashboard:     loadDashboard,
        courses:       loadCourses,
        classes:       loadClasses,
        students:      loadStudents,
        teachers:      loadTeachers,
        schedules:     loadSchedulesPage,
        enrollments:   loadEnrollments,
        tuitions:      loadEnrollments,
        reports:       loadReports,
        notifications: loadNotifications,
    };
    if (loaders[page]) loaders[page]();
}

// ============================================================
// ĐỒNG HỒ
// ============================================================
function startClock() {
    function tick() {
        const now = new Date();
        const pad = n => String(n).padStart(2, '0');
        document.getElementById('time-display').textContent =
            `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
    }
    tick();
    setInterval(tick, 1000);
}

// ============================================================
// TIỆN ÍCH
// ============================================================
function fmtCurrency(val) {
    if (val === null || val === undefined || val === '') return '—';
    return Number(val).toLocaleString('vi-VN') + ' ₫';
}

function fmtDate(str) {
    if (!str) return '—';
    return new Date(str).toLocaleDateString(getLang() === 'en' ? 'en-GB' : 'vi-VN');
}

function mkBadge(text, cls) {
    return `<span class="badge badge-${cls}">${text}</span>`;
}

function statusBadge(status) {
    const map = {
        ACTIVE:     mkBadge(t('status_active'),    'active'),
        INACTIVE:   mkBadge(t('status_inactive'),  'inactive'),
        UPCOMING:   mkBadge(t('status_upcoming'),  'upcoming'),
        ONGOING:    mkBadge(t('status_ongoing'),   'ongoing'),
        COMPLETED:  mkBadge(t('status_completed'), 'completed'),
        PAID:       mkBadge(t('status_paid'),      'paid'),
        UNPAID:     mkBadge(t('status_unpaid'),    'unpaid'),
        PENDING:    mkBadge(t('status_pending'),   'pending'),
        PRESENT:    mkBadge(t('status_present'),   'present'),
        ABSENT:     mkBadge(t('status_absent'),    'absent'),
        CONFIRMED:  mkBadge(t('status_confirmed'), 'active'),
        CANCELLED:  mkBadge(t('status_cancelled'), 'inactive'),
    };
    return map[status] || mkBadge(status || '—', 'inactive');
}

function dayName(n) {
    const keys = ['day_sun','day_mon','day_tue','day_wed','day_thu','day_fri','day_sat'];
    return t(keys[n] || 'day_sun');
}

function makeTable(cols, rows, emptyMsg) {
    emptyMsg = emptyMsg || t('empty');
    if (!rows || rows.length === 0) {
        return `<div class="empty-state"><div class="empty-icon">📭</div><p>${emptyMsg}</p></div>`;
    }
    const thead = cols.map(c => `<th>${c.label}</th>`).join('');
    const tbody = rows.map(row => {
        const tds = cols.map(c => {
            if (c.key === '_act') {
                // render trả về <td>...</td> nguyên bản, không bọc thêm
                return c.render(row);
            }
            return `<td>${c.render ? c.render(row) : (row[c.key] ?? '—')}</td>`;
        }).join('');
        return `<tr>${tds}</tr>`;
    }).join('');
    return `<div class="table-wrap"><table class="data-table"><thead><tr>${thead}</tr></thead><tbody>${tbody}</tbody></table></div>`;
}

function setHtml(id, html) {
    const el = document.getElementById(id);
    if (el) el.innerHTML = html;
}
function loading(id)  { setHtml(id, `<div class="loading-placeholder">${t('loading')}</div>`); }
function errState(id, msgKey) { setHtml(id, `<div class="empty-state"><div class="empty-icon">⚠️</div><p>${t(msgKey || 'error_load')}</p></div>`); }

// ============================================================
// MODAL
// ============================================================
let _modalCb = null;

function setupModal() {
    document.getElementById('modal-close').addEventListener('click', closeModal);
    document.getElementById('modal-cancel').addEventListener('click', closeModal);
    document.getElementById('modal-overlay').addEventListener('click', e => {
        if (e.target === document.getElementById('modal-overlay')) closeModal();
    });
    document.getElementById('modal-submit').addEventListener('click', () => {
        if (_modalCb) _modalCb();
    });
}

function openModal(titleKey, bodyHTML, submitKey, callback, wide = false) {
    document.getElementById('modal-title').textContent = t(titleKey) || titleKey;
    document.getElementById('modal-body').innerHTML = bodyHTML;
    document.getElementById('modal-submit').textContent = t(submitKey) || submitKey || t('btn_modal_confirm');
    document.getElementById('modal-cancel').textContent = t('btn_modal_cancel');
    document.getElementById('modal-overlay').classList.add('open');
    const modal = document.getElementById('modal');
    if (wide) modal.classList.add('modal-wide');
    else modal.classList.remove('modal-wide');
    _modalCb = callback;
}

function closeModal() {
    document.getElementById('modal-overlay').classList.remove('open');
    document.getElementById('modal').classList.remove('modal-wide');
    _modalCb = null;
}

function field(labelKey, inputHTML) {
    return `<div class="form-field"><label>${t(labelKey) || labelKey}</label>${inputHTML}</div>`;
}

function val(id)  { return (document.getElementById(id)?.value || '').trim(); }
function ival(id) { return parseInt(document.getElementById(id)?.value) || null; }
function fval(id) { return parseFloat(document.getElementById(id)?.value) || null; }

function selectOpt(options) {
    return options.map(([v, k]) => `<option value="${v}">${t(k) || v}</option>`).join('');
}

// ============================================================
// BUTTON LISTENERS
// ============================================================
function setupButtonListeners() {
    on('btn-add-student',        () => openAddStudentModal());
    on('btn-add-enrollment',     () => openAddEnrollmentModal());
    on('btn-filter-enrollment',  () => loadEnrollments());
    on('btn-reset-enrollment',   () => {
        const p = document.getElementById('enroll-filter-pay');
        const s = document.getElementById('enroll-filter-status');
        if (p) p.value = '';
        if (s) s.value = '';
        loadEnrollments();
    });
    on('btn-load-schedule',      () => loadSchedules());
    // btn-add-exam removed – "+" is inline in each timetable cell
    on('btn-load-attendance',    () => loadAttendance());
    on('btn-add-attendance',     () => openAddAttendanceModal());
    on('btn-load-tuition',       () => loadTuitions(true));
    on('btn-pay-tuition',        () => openPayTuitionModal());
    on('btn-load-teacher-hours', () => loadTeacherHours());
    on('export-csv-btn',         exportRevenueCSV, true);

    const monthInput = document.getElementById('teacher-hours-month');
    if (monthInput && !monthInput.value) {
        const now = new Date();
        monthInput.value = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
    }
    const attDate = document.getElementById('att-date');
    if (attDate && !attDate.value) attDate.value = new Date().toISOString().split('T')[0];
}

function on(id, handler, preventDefault = false) {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('click', e => {
        if (preventDefault) e.preventDefault();
        handler(e);
    });
}

function exportRevenueCSV() {
    window.location.href = `${API_URL}/reports/revenue/export`;
}

// ============================================================
// DASHBOARD
// ============================================================
async function loadDashboard() {
    // Update static labels
    setHtml('stat-courses',  '—');
    setHtml('stat-classes',  '—');
    setHtml('stat-teachers', '—');
    setHtml('stat-revenue',  '—');

    // Update stat labels
    const statLabels = {
        'stat-courses':  'stat_courses',
        'stat-classes':  'stat_classes',
        'stat-teachers': 'stat_teachers',
        'stat-revenue':  'stat_revenue',
    };
    document.querySelectorAll('.stat-label').forEach((el, i) => {
        const keys = Object.values(statLabels);
        if (keys[i]) el.textContent = t(keys[i]);
    });

    const [coursesRes, classesRes, teachersRes, revenueRes, reportRes] = await Promise.all([
        fetchAPI('/courses?limit=1000'),
        fetchAPI('/classes?limit=1000'),
        fetchAPI('/teachers?limit=1000'),
        fetchAPI('/reports/revenue'),
        fetchAPI('/reports/students'),
    ]);

    if (coursesRes?.ok)  document.getElementById('stat-courses').textContent  = coursesRes.data.data?.length ?? '—';
    if (classesRes?.ok)  document.getElementById('stat-classes').textContent   = classesRes.data.data?.length ?? '—';
    if (teachersRes?.ok) document.getElementById('stat-teachers').textContent  = teachersRes.data.data?.length ?? '—';

    if (revenueRes?.ok && revenueRes.data.data) {
        const total = revenueRes.data.data.reduce((s, r) => s + parseFloat(r.total_revenue || 0), 0);
        document.getElementById('stat-revenue').textContent = fmtCurrency(total);
        drawRevenueChart([...revenueRes.data.data].reverse().slice(-8));
    }

    if (reportRes?.ok && reportRes.data.class_fill_rates) {
        renderFillRates(reportRes.data.class_fill_rates.slice(0, 8));
    } else {
        setHtml('fill-rate-list', `<div class="empty-state"><div class="empty-icon">📭</div><p>${t('empty')}</p></div>`);
    }

    if (classesRes?.ok && classesRes.data.data) {
        setHtml('recent-classes-table', makeTable([
            { key: 'class_code', label: t('col_class_code') },
            { key: 'course_name', label: t('col_course_name') },
            { key: 'max_students', label: t('col_max_students') },
            { key: 'start_date', label: t('col_start_date'), render: r => fmtDate(r.start_date) },
            { key: 'status', label: t('col_status'), render: r => statusBadge(r.status) },
        ], classesRes.data.data.slice(0, 5)));
    }
}

function renderFillRates(rates) {
    if (!rates || rates.length === 0) {
        setHtml('fill-rate-list', `<div class="empty-state"><div class="empty-icon">📭</div><p>${t('empty')}</p></div>`);
        return;
    }
    setHtml('fill-rate-list', rates.map(r => {
        const pct = r.max_students > 0 ? Math.min(100, Math.round((r.current_students / r.max_students) * 100)) : 0;
        return `
        <div class="fill-rate-item">
            <div class="fill-rate-name" title="${r.class_code}">${r.class_code}</div>
            <div class="fill-rate-bar-wrap"><div class="fill-rate-bar" style="width:${pct}%"></div></div>
            <div class="fill-rate-pct">${pct}%</div>
        </div>`;
    }).join(''));
}

function drawRevenueChart(data) {
    const canvas = document.getElementById('revenue-chart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const W = Math.max(canvas.parentElement.getBoundingClientRect().width || 400, 300);
    const H = 220;

    canvas.width  = W * dpr;
    canvas.height = H * dpr;
    canvas.style.width  = W + 'px';
    canvas.style.height = H + 'px';
    ctx.scale(dpr, dpr);
    ctx.clearRect(0, 0, W, H);

    if (!data || data.length === 0) {
        ctx.fillStyle = '#475569';
        ctx.font = '13px Inter, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(t('no_data_chart'), W / 2, H / 2);
        return;
    }

    const pad = { top: 36, right: 16, bottom: 48, left: 72 };
    const cW = W - pad.left - pad.right;
    const cH = H - pad.top - pad.bottom;
    const maxVal = Math.max(...data.map(d => parseFloat(d.total_revenue || 0)), 1);

    for (let i = 0; i <= 4; i++) {
        const y = pad.top + cH - (i / 4) * cH;
        const v = (maxVal * i / 4);
        ctx.strokeStyle = 'rgba(255,255,255,0.05)';
        ctx.lineWidth = 1;
        ctx.beginPath(); ctx.moveTo(pad.left, y); ctx.lineTo(pad.left + cW, y); ctx.stroke();
        ctx.fillStyle = '#475569';
        ctx.font = '10px Inter, sans-serif';
        ctx.textAlign = 'right';
        const label = v >= 1e9 ? (v/1e9).toFixed(1)+'T' : v >= 1e6 ? (v/1e6).toFixed(1)+'M' : v >= 1e3 ? (v/1e3).toFixed(0)+'K' : v.toFixed(0);
        ctx.fillText(label, pad.left - 8, y + 4);
    }

    const gap  = cW / data.length;
    const barW = Math.min(38, gap * 0.62);

    data.forEach((d, i) => {
        const v  = parseFloat(d.total_revenue || 0);
        const bH = (v / maxVal) * cH;
        const x  = pad.left + i * gap + (gap - barW) / 2;
        const y  = pad.top + cH - bH;
        const grad = ctx.createLinearGradient(0, y, 0, pad.top + cH);
        grad.addColorStop(0, 'rgba(99,102,241,0.95)');
        grad.addColorStop(1, 'rgba(99,102,241,0.18)');
        ctx.fillStyle = grad;
        const r = Math.min(4, bH / 2);
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.lineTo(x + barW - r, y);
        ctx.quadraticCurveTo(x + barW, y, x + barW, y + r);
        ctx.lineTo(x + barW, pad.top + cH);
        ctx.lineTo(x, pad.top + cH);
        ctx.lineTo(x, y + r);
        ctx.quadraticCurveTo(x, y, x + r, y);
        ctx.closePath();
        ctx.fill();
        // Label tháng (trục X)
        ctx.fillStyle = '#94a3b8';
        ctx.font = '9px Inter, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText((d.month || '').replace(/^\d{4}-/, ''), x + barW / 2, pad.top + cH + 16);

        // Label số tiền trên đỉnh cột
        if (v > 0) {
            const valLabel = v >= 1e9 ? (v/1e9).toFixed(1)+'T'
                           : v >= 1e6 ? (v/1e6).toFixed(1)+'M'
                           : v >= 1e3 ? (v/1e3).toFixed(0)+'K'
                           : v.toFixed(0);
            ctx.fillStyle = '#e2e8f0';
            ctx.font = `bold ${Math.min(11, barW > 30 ? 11 : 9)}px Inter, sans-serif`;
            ctx.textAlign = 'center';
            ctx.fillText(valLabel, x + barW / 2, y - 6);
        }
    });
}

// ============================================================
// COURSES
// ============================================================
async function loadCourses() {
    loading('courses-table');
    const res = await fetchAPI('/courses?limit=200');
    if (!res?.ok) { errState('courses-table'); return; }
    setHtml('courses-table', makeTable([
        { key: 'id',            label: t('col_id') },
        { key: 'course_code',   label: t('col_course_code') },
        { key: 'course_name',   label: t('col_course_name') },
        { key: 'duration_weeks',label: t('col_duration_weeks') },
        { key: 'tuition_fee',   label: t('col_tuition_fee'), render: r => fmtCurrency(r.tuition_fee) },
        { key: 'status',        label: t('col_status'), render: r => statusBadge(r.status) },
        { key: 'description',   label: t('col_description'), render: r => r.description ? r.description.slice(0,60)+(r.description.length>60?'…':'') : '—' },
        { key: '_act', label: '', render: r => `<td class="td-actions"><button class="btn-edit" onclick='openEditCourseModal(${JSON.stringify(r)})'>Sửa</button><button class="btn-delete" onclick='deleteRecord("/courses/${r.id}","khóa học ${r.course_code}",loadCourses)'>Xóa</button></td>` },
    ], res.data.data));
    document.getElementById('btn-add-course').textContent = t('btn_add_course');
    document.getElementById('btn-add-course').onclick = openAddCourseModal;
}

function openEditCourseModal(r) {
    openModal('modal_edit_course', `
        ${field('lbl_course_code', `<input type="text" value="${r.course_code}" disabled style="opacity:.5;cursor:not-allowed">`)}
        ${field('lbl_course_name', `<input id="fe-cn" type="text" value="${r.course_name || ''}">`)}
        ${field('lbl_duration_weeks', `<input id="fe-dw" type="number" min="1" value="${r.duration_weeks || ''}">`)}
        ${field('lbl_tuition_fee_f', `<input id="fe-tf" type="number" min="0" value="${r.tuition_fee || ''}">`)}
        ${field('lbl_description', `<textarea id="fe-desc">${r.description || ''}</textarea>`)}
        ${field('lbl_status', `<select id="fe-cs"><option value="ACTIVE" ${r.status==='ACTIVE'?'selected':''}>Hoạt động</option><option value="INACTIVE" ${r.status==='INACTIVE'?'selected':''}>Ngừng</option></select>`)}
    `, 'btn_save', async () => {
        const body = { course_name: val('fe-cn'), duration_weeks: ival('fe-dw'), tuition_fee: fval('fe-tf'), description: val('fe-desc'), status: val('fe-cs') };
        if (!body.course_name || !body.duration_weeks || !body.tuition_fee) { showToast(t('toast_required'), 'warning'); return; }
        const res = await fetchAPI(`/courses/${r.id}`, { method: 'PUT', body: JSON.stringify(body) });
        if (res?.ok) { showToast('Cập nhật khóa học thành công', 'success'); closeModal(); loadCourses(); }
        else showToast('Lỗi: ' + (res?.data?.error || ''), 'error');
    });
}

function openAddCourseModal() {
    openModal('modal_add_course', `
        ${field('lbl_course_code',    `<input id="f-cc" type="text" placeholder="${t('col_course_code')} (VD: JS101)">`)}
        ${field('lbl_course_name',    `<input id="f-cn" type="text" placeholder="${t('col_course_name')}">`)}
        ${field('lbl_duration_weeks', `<input id="f-dw" type="number" min="1" placeholder="VD: 12">`)}
        ${field('lbl_tuition_fee_f',  `<input id="f-tf" type="number" min="0" placeholder="VD: 3000000">`)}
        ${field('lbl_description',    `<textarea id="f-desc" placeholder="${t('lbl_description')}..."></textarea>`)}
        ${field('lbl_status',         `<select id="f-cs"><option value="ACTIVE">${t('opt_active')}</option><option value="INACTIVE">${t('opt_inactive')}</option></select>`)}
    `, 'btn_add_course', async () => {
        const p = { course_code: val('f-cc'), course_name: val('f-cn'), duration_weeks: ival('f-dw'), tuition_fee: fval('f-tf'), description: val('f-desc'), status: val('f-cs') };
        if (!p.course_code || !p.course_name || !p.duration_weeks || !p.tuition_fee) { showToast(t('toast_required'), 'warning'); return; }
        const res = await fetchAPI('/courses', { method: 'POST', body: JSON.stringify(p) });
        if (res?.ok) { showToast(t('toast_course_ok'), 'success'); closeModal(); loadCourses(); }
        else showToast(t('toast_course_err') + ': ' + (res?.data?.error || ''), 'error');
    });
}

// ============================================================
// CLASSES
// ============================================================
async function loadClasses() {
    loading('classes-table');
    const res = await fetchAPI('/classes?limit=200');
    if (!res?.ok) { errState('classes-table'); return; }
    const classRows = res.data.data || [];
    classRows.forEach((r, i) => { r._seq = i + 1; });
    setHtml('classes-table', makeTable([
        { key: '_seq',         label: t('col_id') },
        { key: 'class_code',   label: t('col_class_code') },
        { key: 'course_name',  label: t('col_course_name') },
        { key: 'room_name',    label: t('col_classroom'), render: r => r.room_name || '—' },
        { key: 'max_students', label: t('col_max_students') },
        { key: 'start_date',   label: t('col_start_date'), render: r => fmtDate(r.start_date) },
        { key: 'end_date',     label: t('col_end_date'),   render: r => fmtDate(r.end_date) },
        { key: 'status',       label: t('col_status'), render: r => statusBadge(r.status) },
        { key: '_act', label: '', render: r => `<td class="td-actions"><button class="btn-edit" onclick='openEditClassModal(${JSON.stringify(r)})'>Sửa</button><button class="btn-delete" onclick='deleteRecord("/classes/${r.id}","lớp ${r.class_code}",loadClasses)'>Xóa</button></td>` },
    ], classRows));
    document.getElementById('btn-add-class').textContent = t('btn_add_class');
    document.getElementById('btn-add-class').onclick = openAddClassModal;
}

// Auto-compute end_date: start + duration_weeks weeks (1 week = 1 học kỳ mini, 2 buổi/tuần)
function _autoEndDate(startStr, sessions) {
    if (!startStr || !sessions) return '';
    const start = new Date(startStr);
    if (isNaN(start)) return '';
    const weeks = parseInt(sessions); // duration_weeks = actual weeks
    const end = new Date(start);
    end.setDate(start.getDate() + weeks * 7);
    return end.toISOString().split('T')[0];
}

async function openEditClassModal(r) {
    openModal('modal_edit_class', `<div class="loading-placeholder" style="padding:24px 0">${t('loading')}</div>`, 'btn_save', null);
    const [teacherRes, roomRes] = await Promise.all([fetchAPI('/teachers?limit=200'), fetchAPI('/classrooms')]);
    const teachers   = teacherRes?.ok ? (teacherRes.data.data || []) : [];
    const classrooms = roomRes?.ok    ? (roomRes.data.data    || []) : [];

    const teacherOpts = `<option value="">-- Chưa phân công --</option>` +
        teachers.map(t => `<option value="${t.id}" ${t.id==r.teacher_id?'selected':''}>[${t.teacher_code}] ${t.full_name}</option>`).join('');
    const roomOpts = `<option value="">-- Không chọn --</option>` +
        classrooms.map(c => `<option value="${c.id}" ${c.id==r.classroom_id?'selected':''}>${c.room_name} (${c.capacity} chỗ)</option>`).join('');
    const statuses = ['UPCOMING','ONGOING','COMPLETED','CANCELLED'];
    const sessions = r.duration_weeks || 0;

    document.getElementById('modal-title').textContent = `Sửa lớp học: ${r.class_code}`;
    document.getElementById('modal-body').innerHTML = `
        ${field('lbl_teacher_id_opt', `<select id="fe-cti">${teacherOpts}</select>`)}
        ${field('lbl_classroom_id',   `<select id="fe-crmi">${roomOpts}</select>`)}
        ${field('lbl_max_students',   `<input id="fe-cms" type="number" min="1" value="${r.max_students || 30}">`)}
        ${field('lbl_start_date',     `<input id="fe-csd" type="date" value="${r.start_date?.split('T')[0] || ''}">`)}
        ${field('lbl_end_date',       `<input id="fe-ced" type="date" value="${r.end_date?.split('T')[0] || ''}">`)}
        ${sessions ? `<p style="font-size:12px;color:var(--text-faint);margin:-8px 0 8px">📌 Khóa học có <strong>${sessions} buổi</strong> → <strong>${Math.ceil(sessions/2)} tuần</strong>. Ngày kết thúc tự tính khi đổi ngày bắt đầu.</p>` : ''}
        ${field('lbl_status', `<select id="fe-cst">${statuses.map(s => `<option value="${s}" ${s===r.status?'selected':''}>${s}</option>`).join('')}</select>`)}
    `;
    // Auto-fill end_date when start_date changes
    document.getElementById('fe-csd').addEventListener('change', function() {
        const computed = _autoEndDate(this.value, sessions);
        if (computed) document.getElementById('fe-ced').value = computed;
    });
    _modalCb = async () => {
        const body = { teacher_id: ival('fe-cti') || null, classroom_id: ival('fe-crmi') || null, max_students: ival('fe-cms'), start_date: val('fe-csd'), end_date: val('fe-ced'), status: val('fe-cst') };
        if (!body.max_students || !body.start_date || !body.end_date) { showToast(t('toast_required'), 'warning'); return; }
        const res = await fetchAPI(`/classes/${r.id}`, { method: 'PUT', body: JSON.stringify(body) });
        if (res?.ok) { showToast('Cập nhật lớp học thành công', 'success'); closeModal(); loadClasses(); }
        else showToast('Lỗi: ' + (res?.data?.error || ''), 'error');
    };
}

async function openAddClassModal() {
    openModal('modal_add_class', `<div class="loading-placeholder" style="padding:32px 0">${t('loading')}</div>`, 'btn_add_class', null);

    const [courseRes, teacherRes, roomRes, semRes] = await Promise.all([
        fetchAPI('/courses?limit=200'),
        fetchAPI('/teachers?limit=200'),
        fetchAPI('/classrooms'),
        fetchAPI('/semesters'),
    ]);

    const courses   = courseRes?.ok  ? (courseRes.data.data   || []) : [];
    const teachers  = teacherRes?.ok ? (teacherRes.data.data  || []) : [];
    const classrooms = roomRes?.ok   ? (roomRes.data.data     || []) : [];
    const semesters  = semRes?.ok    ? (semRes.data.data      || []) : [];

    const courseOpts = courses.length
        ? courses.map(c => `<option value="${c.id}">[${c.course_code}] ${c.course_name}</option>`).join('')
        : `<option value="" disabled>Không có khóa học</option>`;

    const teacherOpts = `<option value="">-- Chưa phân công --</option>` + teachers.map(t => `<option value="${t.id}">[${t.teacher_code}] ${t.full_name}</option>`).join('');

    const roomOpts = classrooms.length
        ? classrooms.map(r => `<option value="${r.id}">${r.room_name} — ${r.location || ''} (${r.capacity} chỗ)</option>`).join('')
        : `<option value="" disabled>Không có phòng học</option>`;

    const semOpts = semesters.length
        ? semesters.map(s => `<option value="${s.id}">${s.semester_name} (${s.status})</option>`).join('')
        : `<option value="" disabled>Không có học kỳ</option>`;

    document.getElementById('modal-body').innerHTML = `
        ${field('lbl_class_code',     `<input id="f-clc" type="text" placeholder="VD: JS101-A">`)}
        ${field('lbl_course_id',      `<select id="f-coci"><option value="">-- Chọn khóa học --</option>${courseOpts}</select>`)}
        ${field('lbl_teacher_id_opt', `<select id="f-cti">${teacherOpts}</select>`)}
        ${field('lbl_classroom_id',   `<select id="f-crmi"><option value="">-- Chọn phòng học --</option>${roomOpts}</select>`)}
        ${field('lbl_semester_id',    `<select id="f-csi"><option value="">-- Chọn học kỳ --</option>${semOpts}</select>`)}
        ${field('lbl_max_students',   `<input id="f-cms" type="number" min="1" placeholder="VD: 30">`)}
        ${field('lbl_start_date',     `<input id="f-csd" type="date">`)}
        ${field('lbl_end_date',       `<input id="f-ced" type="date">`)}
        <p id="f-duration-hint" style="font-size:12px;color:var(--text-faint);margin:-8px 0 8px;display:none"></p>
        ${field('lbl_status',         `<select id="f-cst"><option value="UPCOMING">${t('opt_upcoming')}</option><option value="ONGOING">${t('opt_ongoing')}</option><option value="COMPLETED">${t('opt_completed_s')}</option></select>`)}
    `;

    // Auto-fill end_date from course sessions + start_date
    function _updateAddClassEndDate() {
        const courseId  = ival('f-coci');
        const startDate = val('f-csd');
        const course    = courses.find(c => c.id === courseId);
        const sessions  = course?.duration_weeks;
        const hint      = document.getElementById('f-duration-hint');
        if (sessions && startDate) {
            const computed = _autoEndDate(startDate, sessions);
            document.getElementById('f-ced').value = computed;
            if (hint) { hint.textContent = `📌 ${sessions} buổi → ${Math.ceil(sessions/2)} tuần → kết thúc ${computed.split('-').reverse().join('/')}`; hint.style.display = ''; }
        } else if (hint) hint.style.display = 'none';
    }
    document.getElementById('f-coci').addEventListener('change', _updateAddClassEndDate);
    document.getElementById('f-csd').addEventListener('change',  _updateAddClassEndDate);

    _modalCb = async () => {
        const p = { class_code: val('f-clc'), course_id: ival('f-coci'), teacher_id: ival('f-cti') || null, classroom_id: ival('f-crmi'), semester_id: ival('f-csi'), max_students: ival('f-cms'), start_date: val('f-csd'), end_date: val('f-ced'), status: val('f-cst') };
        if (!p.class_code || !p.course_id || !p.max_students || !p.start_date || !p.end_date) { showToast(t('toast_required'), 'warning'); return; }
        const res = await fetchAPI('/classes', { method: 'POST', body: JSON.stringify(p) });
        if (res?.ok) { showToast(t('toast_class_ok'), 'success'); closeModal(); loadClasses(); }
        else showToast(t('toast_class_err') + ': ' + (res?.data?.error || ''), 'error');
    };
}

// ============================================================
// STUDENTS
// ============================================================
async function loadStudents() {
    loading('students-table');
    const [studentsRes, enrollmentsRes] = await Promise.all([
        fetchAPI('/students?all=1&limit=500'),
        fetchAPI('/enrollments'),
    ]);
    if (!studentsRes?.ok) { errState('students-table'); return; }

    const rows       = Array.isArray(studentsRes.data.data) ? studentsRes.data.data : (Array.isArray(studentsRes.data) ? studentsRes.data : []);
    const allEnrolls = enrollmentsRes?.ok ? (enrollmentsRes.data.data || []) : [];

    // Nhóm enrollment theo student_id
    const enrollMap = {};
    allEnrolls.forEach(e => {
        if (!enrollMap[e.student_id]) enrollMap[e.student_id] = [];
        enrollMap[e.student_id].push(e);
    });

    // Fetch schedules per (student_id, class_id) pair — only this student's slots
    const schedMap = {}; // schedMap[`${studentId}_${classId}`] = [schedules]
    const uniquePairs = [...new Map(allEnrolls.filter(e=>e.class_id && e.student_id)
        .map(e => [`${e.student_id}_${e.class_id}`, e])).values()];
    if (uniquePairs.length > 0) {
        const schedResults = await Promise.all(
            uniquePairs.map(e => fetchAPI(`/schedules?class_id=${e.class_id}&student_id=${e.student_id}`))
        );
        schedResults.forEach((res, i) => {
            const list = res?.ok ? (res.data.data || []) : [];
            schedMap[`${uniquePairs[i].student_id}_${uniquePairs[i].class_id}`] = list;
        });
    }

    const dayFull = {1:'CN',2:'Thứ 2',3:'Thứ 3',4:'Thứ 4',5:'Thứ 5',6:'Thứ 6',7:'Thứ 7'};

    function renderScheduleBadge(classId, studentId) {
        const list = schedMap[`${studentId}_${classId}`] || [];
        if (!list.length) return '';
        // Chỉ hiện lịch REGULAR của đúng học viên này
        const regular = list.filter(s =>
            (!s.schedule_type || s.schedule_type === 'REGULAR') &&
            (String(s.student_id) === String(studentId))
        );
        if (!regular.length) return '';
        // Deduplicate by day+time key
        const seen = new Set();
        const unique = regular.filter(s => {
            const k = `${s.day_of_week}-${s.start_time}-${s.end_time}`;
            if (seen.has(k)) return false;
            seen.add(k); return true;
        });
        // Sort by day (2=Mon...7=Sat,1=Sun) and take max 2
        const dayOrder = [2,3,4,5,6,7,1];
        unique.sort((a,b) => dayOrder.indexOf(a.day_of_week) - dayOrder.indexOf(b.day_of_week));
        const top2  = unique.slice(0, 2);
        const parts = top2.map(s => `${dayFull[s.day_of_week]} ${s.start_time.slice(0,5)}–${s.end_time.slice(0,5)}`);
        const chips = parts.map(p => `<span class="sv-sched-chip">${p}</span>`).join('');
        return `<span class="sv-schedule-badge" title="${parts.join(' | ')}">${chips}</span>`;
    }

    if (rows.length === 0) {
        setHtml('students-table', `<div class="empty-state"><div class="empty-icon">📭</div><p>Chưa có học viên nào</p></div>`);
        return;
    }

    const html = rows.map((r, idx) => {
        const enrolls = enrollMap[r.id] || [];

        const enrollSection = enrolls.length > 0 ? `
            <div class="sv-enroll-section">
                <div class="sv-enroll-header">🏫 Lớp đăng ký (${enrolls.length})</div>
                <div class="sv-enroll-list">
                    ${enrolls.map(e => {
                        const isPaid = e.payment_status === 'PAID' || e.payment_status === 'COMPLETED';
                        const fee    = fmtCurrency(e.tuition_amount || 0);
                        const schedBadge = isPaid ? renderScheduleBadge(e.class_id, e.student_id) : '';
                        return `
                        <div class="sv-enroll-item ${isPaid ? 'sv-paid' : 'sv-unpaid'}">
                            <span class="sv-class-badge">${e.class_code || '—'}</span>
                            <span class="sv-class-name">${e.course_name || '—'}</span>
                            ${schedBadge}
                            <span class="sv-class-fee">${fee}</span>
                            <div class="sv-enroll-actions">
                            ${isPaid
                                ? `<span class="badge badge-paid">Đã thanh toán</span>
                                   <button class="btn-refund-inline" onclick='cancelPayment(${e.id},${e.tuition_id||0},loadStudents)'>Hủy TT</button>`
                                : `<button class="btn-pay-inline" onclick='quickPayTuition(${e.tuition_id||0},${e.id},${e.class_id||0},loadStudents,${e.student_id||0})'>Thanh toán</button>`
                            }
                            </div>
                        </div>`;
                    }).join('')}
                </div>
            </div>` : `
            <div class="sv-enroll-section sv-no-enroll">
                <span style="color:var(--text-faint);font-size:12px">Chưa đăng ký lớp nào</span>
            </div>`;

        return `
        <div class="sv-card">
            <div class="sv-main-row">
                <span class="id-chip">#${idx + 1}</span>
                <div class="sv-info">
                    <div class="sv-name-row">
                        <strong class="sv-code">${r.student_code}</strong>
                        <span class="sv-name">${r.full_name}</span>
                        ${statusBadge(r.status)}
                    </div>
                    <div class="sv-meta-row">
                        <span>✉ ${r.email || '—'}</span>
                        <span>📞 ${r.phone || '—'}</span>
                        <span>🎂 ${fmtDate(r.date_of_birth)}</span>
                        ${r.parent_phone ? `<span>👨‍👧 ${r.parent_phone}</span>` : ''}
                    </div>
                </div>
                <div class="sv-actions">
                    <button class="btn-edit" onclick="openStudentSchedule(${r.id})">Lịch</button>
                    <button class="btn-edit" onclick='openEditStudentModal(${JSON.stringify(r)})'>Sửa</button>
                    <button class="btn-delete" onclick='deleteStudentForce(${r.id},"${r.student_code}")'>Xóa</button>
                </div>
            </div>
            ${enrollSection}
        </div>`;
    }).join('');

    setHtml('students-table', `<div class="sv-list">${html}</div>`);
}

function deleteStudentForce(studentId, studentCode) {
    const overlay = document.createElement('div');
    overlay.className = 'confirm-overlay';
    overlay.innerHTML = `
        <div class="confirm-box">
            <div class="confirm-icon">🗑️</div>
            <div class="confirm-title">Xóa học viên ${studentCode}?</div>
            <div class="confirm-msg">
                Thao tác này sẽ xóa <strong>tất cả đăng ký lớp học</strong> và dữ liệu của học viên này.<br>
                <span style="color:#f87171;font-size:12px;margin-top:6px;display:block">Không thể hoàn tác!</span>
            </div>
            <div class="confirm-actions">
                <button class="confirm-cancel" id="cfm-cancel-del">Hủy</button>
                <button class="confirm-ok" id="cfm-ok-del">Xóa tất cả</button>
            </div>
        </div>`;
    document.body.appendChild(overlay);

    document.getElementById('cfm-cancel-del').onclick = () => overlay.remove();
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.remove(); });

    document.getElementById('cfm-ok-del').onclick = async () => {
        overlay.remove();
        const res = await fetchAPI(`/students/${studentId}`, { method: 'DELETE' });
        if (res?.ok) {
            showToast(`Đã xóa học viên ${studentCode} và toàn bộ dữ liệu liên quan`, 'success');
            loadStudents();
        } else {
            showToast('Lỗi: ' + (res?.data?.error || 'Không thể xóa'), 'error');
        }
    };
}

async function openAddStudentModal() {
    openModal('Thêm học viên mới', `<div class="loading-placeholder" style="padding:32px 0">Đang tải danh sách lớp...</div>`, 'Thêm học viên', null, true);

    const classRes = await fetchAPI('/classes?limit=200');
    const classes  = classRes?.ok ? (classRes.data.data || []) : [];

    const statuses = [['ACTIVE','Hoạt động'],['INACTIVE','Ngừng'],['GRADUATED','Đã tốt nghiệp']];

    const statusMap = { UPCOMING: 'Sắp mở', ONGOING: 'Đang học', COMPLETED: 'Đã kết thúc', CANCELLED: 'Đã hủy' };

    const classListHTML = classes.length
        ? classes.map(c => `
            <label class="class-checkbox-item" id="cb-wrap-${c.id}">
                <input type="checkbox" name="class_cb" value="${c.id}"
                    onchange="this.closest('.class-checkbox-item').classList.toggle('selected',this.checked)">
                <div class="class-cb-info">
                    <span class="class-cb-code">${c.class_code}</span>
                    <span class="class-cb-name">${c.course_name || '—'}</span>
                    <span class="class-cb-meta">${fmtDate(c.start_date)} → ${fmtDate(c.end_date)} · ${statusMap[c.status] || c.status}</span>
                </div>
            </label>`).join('')
        : `<p style="color:var(--text-faint);text-align:center;padding:20px 0">Không có lớp học nào</p>`;

    document.getElementById('modal-body').innerHTML = `
        ${field('Họ và tên *',   `<input id="fs-name"  type="text"     placeholder="Nguyễn Văn A">`)}
        ${field('Email *',       `<input id="fs-email" type="email"    placeholder="hocvien@edu.vn">`)}
        ${field('Mật khẩu',     `<input id="fs-pass"  type="password" placeholder="Mặc định: hocvien123">`)}
        ${field('Mã học viên *', `<input id="fs-code"  type="text"     placeholder="VD: HV004">`)}
        ${field('Số điện thoại', `<input id="fs-phone" type="text"     placeholder="090xxxxxxx">`)}
        ${field('Ngày sinh',     `<input id="fs-dob"   type="date">`)}
        ${field('SĐT phụ huynh',`<input id="fs-pp"    type="text"     placeholder="090xxxxxxx">`)}
        ${field('Trạng thái',   `<select id="fs-st">${statuses.map(([v,l]) => `<option value="${v}">${l}</option>`).join('')}</select>`)}
        <div class="section-divider">
            <div class="section-divider-title">🏫 Đăng ký lớp học</div>
            <div class="section-divider-hint">Chọn một hoặc nhiều lớp học viên muốn đăng ký. Học phí sẽ ở trạng thái <strong>Chưa thanh toán</strong> cho đến khi xác nhận.</div>
            <div class="class-checkbox-list">${classListHTML}</div>
        </div>
    `;

    _modalCb = async () => {
        const body = {
            full_name:     val('fs-name'),
            email:         val('fs-email'),
            phone:         val('fs-phone'),
            student_code:  val('fs-code'),
            password:      val('fs-pass') || 'hocvien123',
            date_of_birth: val('fs-dob'),
            parent_phone:  val('fs-pp'),
            status:        val('fs-st'),
        };
        if (!body.full_name || !body.email || !body.student_code) {
            showToast('Vui lòng điền đủ: Họ tên, Email, Mã học viên', 'warning'); return;
        }

        const checkedIds = [...document.querySelectorAll('input[name="class_cb"]:checked')]
            .map(el => parseInt(el.value));

        const studentRes = await fetchAPI('/students', { method: 'POST', body: JSON.stringify(body) });
        if (!studentRes?.ok) {
            showToast('Lỗi tạo học viên: ' + (studentRes?.data?.error || ''), 'error'); return;
        }

        const studentId = studentRes.data?.data?.id || studentRes.data?.id;

        if (checkedIds.length > 0 && studentId) {
            const enrollResults = await Promise.all(
                checkedIds.map(classId => fetchAPI('/enrollments', {
                    method: 'POST',
                    body: JSON.stringify({ student_id: studentId, class_id: classId, payment_status: 'UNPAID', status: 'ACTIVE' }),
                }))
            );
            const failed = enrollResults.filter(r => !r?.ok).length;
            if (failed > 0) {
                showToast(`Thêm học viên thành công. ${checkedIds.length - failed}/${checkedIds.length} lớp đã đăng ký (${failed} thất bại).`, 'warning');
            } else {
                showToast(`Thêm học viên thành công! Đã đăng ký ${checkedIds.length} lớp. Vào "Đăng ký & Học phí" để thanh toán.`, 'success');
            }
        } else {
            showToast('Thêm học viên thành công!', 'success');
        }
        closeModal();
        loadStudents();
    };
}

async function openEditStudentModal(r) {
    openModal(`Sửa học viên ${r.student_code}`, `<div class="loading-placeholder" style="padding:32px 0">Đang tải...</div>`, 'Lưu thay đổi', null, true);

    const [classRes, enrollRes] = await Promise.all([
        fetchAPI('/classes?limit=200'),
        fetchAPI('/enrollments'),
    ]);

    const allClasses   = classRes?.ok  ? (classRes.data.data   || []) : [];
    const allEnrolls   = enrollRes?.ok ? (enrollRes.data.data  || []) : [];
    const myEnrolls    = allEnrolls.filter(e => e.student_id == r.id);
    const enrolledIds  = new Set(myEnrolls.map(e => e.class_id));
    const enrollMap    = {};
    myEnrolls.forEach(e => { enrollMap[e.class_id] = e; });

    const statuses  = [['ACTIVE','Hoạt động'],['INACTIVE','Ngừng'],['GRADUATED','Đã tốt nghiệp']];
    const statusMap = { UPCOMING:'Sắp mở', ONGOING:'Đang học', COMPLETED:'Đã kết thúc', CANCELLED:'Đã hủy' };

    const classListHTML = allClasses.length ? allClasses.map(c => {
        const isEnrolled = enrolledIds.has(c.id);
        const enroll     = enrollMap[c.id];
        const isPaid     = enroll && (enroll.payment_status === 'PAID' || enroll.payment_status === 'COMPLETED');
        const paidTag    = isPaid ? `<span class="badge badge-paid" style="font-size:10px">Đã TT</span>` : '';
        return `
        <label class="class-checkbox-item ${isEnrolled ? 'selected' : ''}" id="ecb-wrap-${c.id}">
            <input type="checkbox" name="edit_class_cb" value="${c.id}" ${isEnrolled ? 'checked' : ''}
                data-enrolled="${isEnrolled ? '1' : '0'}"
                data-enroll-id="${enroll?.id || ''}"
                data-paid="${isPaid ? '1' : '0'}"
                onchange="document.getElementById('ecb-wrap-${c.id}').classList.toggle('selected',this.checked)">
            <div class="class-cb-info">
                <span class="class-cb-code">${c.class_code}</span>
                <span class="class-cb-name">${c.course_name || '—'}</span>
                ${paidTag}
                <span class="class-cb-meta">${fmtDate(c.start_date)} → ${fmtDate(c.end_date)} · ${statusMap[c.status] || c.status}</span>
            </div>
        </label>`;
    }).join('') : `<p style="color:var(--text-faint);text-align:center;padding:20px 0">Không có lớp học nào</p>`;

    document.getElementById('modal-body').innerHTML = `
        ${field('Họ và tên *',    `<input id="fe-sname"  type="text"  placeholder="Nguyễn Văn A"   value="${r.full_name || ''}">`)}
        ${field('Email',          `<input type="email" value="${r.email || ''}" disabled style="opacity:.5;cursor:not-allowed">`)}
        ${field('Mật khẩu',      `<input id="fe-spass"  type="password" placeholder="Để trống = giữ nguyên">`)}
        ${field('Mã học viên',   `<input type="text" value="${r.student_code}" disabled style="opacity:.5;cursor:not-allowed">`)}
        ${field('Số điện thoại', `<input id="fe-sphone" type="text"  placeholder="090xxxxxxx"      value="${r.phone || ''}">`)}
        ${field('Ngày sinh',     `<input id="fe-sdob"   type="date"  value="${r.date_of_birth ? r.date_of_birth.split('T')[0] : ''}">`)}
        ${field('SĐT phụ huynh',`<input id="fe-spp"    type="text"  placeholder="090xxxxxxx"      value="${r.parent_phone || ''}">`)}
        ${field('Trạng thái',   `<select id="fe-sst">${statuses.map(([v,l]) => `<option value="${v}" ${v===r.status?'selected':''}>${l}</option>`).join('')}</select>`)}
        <div class="section-divider">
            <div class="section-divider-title">🏫 Quản lý lớp học</div>
            <div class="section-divider-hint">
                Tick ✔ để đăng ký thêm lớp · Bỏ tick để hủy đăng ký.
                <span style="color:#f87171"> Lớp đã thanh toán không thể hủy.</span>
            </div>
            <div class="class-checkbox-list">${classListHTML}</div>
        </div>
    `;

    _modalCb = async () => {
        // 1. Cập nhật thông tin học viên
        const body = {
            full_name:     val('fe-sname'),
            phone:         val('fe-sphone'),
            date_of_birth: val('fe-sdob'),
            parent_phone:  val('fe-spp'),
            status:        val('fe-sst'),
        };
        const updateRes = await fetchAPI(`/students/${r.id}`, { method: 'PUT', body: JSON.stringify(body) });
        if (!updateRes?.ok) { showToast('Lỗi cập nhật: ' + (updateRes?.data?.error || ''), 'error'); return; }

        // 2. Xử lý thay đổi lớp học
        const checkboxes = [...document.querySelectorAll('input[name="edit_class_cb"]')];
        const toAdd    = [];
        const toRemove = [];

        checkboxes.forEach(cb => {
            const classId    = parseInt(cb.value);
            const wasEnrolled = cb.dataset.enrolled === '1';
            const isPaid      = cb.dataset.paid === '1';
            const enrollId    = cb.dataset.enrollId ? parseInt(cb.dataset.enrollId) : null;
            const isChecked   = cb.checked;

            if (!wasEnrolled && isChecked)  toAdd.push(classId);
            if (wasEnrolled && !isChecked && !isPaid && enrollId) toRemove.push(enrollId);
        });

        const adds    = await Promise.all(toAdd.map(classId =>
            fetchAPI('/enrollments', { method: 'POST', body: JSON.stringify({ student_id: r.id, class_id: classId, payment_status: 'UNPAID', status: 'ACTIVE' }) })
        ));
        const removes = await Promise.all(toRemove.map(enrollId =>
            fetchAPI(`/enrollments/${enrollId}`, { method: 'DELETE' })
        ));

        const addFail = adds.filter(x => !x?.ok).length;
        const remFail = removes.filter(x => !x?.ok).length;

        let msg = 'Cập nhật học viên thành công!';
        if (toAdd.length)    msg += ` Đã thêm ${toAdd.length - addFail} lớp.`;
        if (toRemove.length) msg += ` Đã hủy ${toRemove.length - remFail} lớp.`;
        if (addFail + remFail > 0) msg += ` (${addFail + remFail} thao tác thất bại)`;

        showToast(msg, addFail + remFail > 0 ? 'warning' : 'success');
        closeModal();
        loadStudents();
    };
}

// TEACHERS
// ============================================================
async function loadTeachers() {
    loading('teachers-table');
    const res = await fetchAPI('/teachers?limit=200');
    if (!res?.ok) { errState('teachers-table'); return; }
    setHtml('teachers-table', makeTable([
        { key: 'id',             label: t('col_id') },
        { key: 'teacher_code',   label: t('col_teacher_code') },
        { key: 'full_name',      label: t('col_teacher_name') },
        { key: 'email',          label: t('col_email'), render: r => r.email || '—' },
        { key: 'phone',          label: t('col_phone'), render: r => r.phone || '—' },
        { key: 'specialization', label: t('col_specialization') },
        { key: 'hire_date',      label: t('col_hire_date'), render: r => fmtDate(r.hire_date) },
        { key: 'status',         label: t('col_status'), render: r => statusBadge(r.status) },
        { key: '_act', label: '', render: r => `<td class="td-actions"><button class="btn-edit" onclick="openTeacherSchedule(${r.id})">Lịch</button><button class="btn-edit" onclick='openEditTeacherModal(${JSON.stringify(r)})'>Sửa</button><button class="btn-delete" onclick='deleteRecord("/teachers/${r.id}","giáo viên ${r.teacher_code}",loadTeachers)'>Xóa</button></td>` },
    ], res.data.data));
    document.getElementById('btn-add-teacher').textContent = t('btn_add_teacher');
    document.getElementById('btn-add-teacher').onclick = openAddTeacherModal;
}

function openEditTeacherModal(r) {
    const typeVal = r.teacher_type || 'FULL_TIME';
    openModal('modal_edit_teacher', `
        ${field('lbl_teacher_code', `<input type="text" value="${r.teacher_code}" disabled style="opacity:.5;cursor:not-allowed">`)}
        ${field('Họ và tên *', `<input id="fe-tname" type="text" value="${escHtml(r.full_name || '')}">`)}
        ${field('Email *', `<input id="fe-temail" type="email" value="${escHtml(r.email || '')}">`)}
        ${field('Số điện thoại', `<input id="fe-tphone" type="text" value="${escHtml(r.phone || '')}" placeholder="0912345678">`)}
        ${field('Mật khẩu mới', `<input id="fe-tpass" type="password" placeholder="Để trống nếu không đổi">`)}
        ${field('lbl_specialization', `<input id="fe-tsp" type="text" value="${escHtml(r.specialization || '')}">`)}
        ${field('lbl_hire_date', `<input id="fe-thd" type="date" value="${r.hire_date?.split('T')[0] || ''}">`)}
        ${field('Loại GV', `<select id="fe-ttype" onchange="window._syncTeacherHoursField()">
            <option value="FULL_TIME" ${typeVal==='FULL_TIME'?'selected':''}>Cơ hữu</option>
            <option value="VISITING" ${typeVal==='VISITING'?'selected':''}>Mời giảng</option>
        </select>`)}
        ${field('Giờ chuẩn/tháng', `<input id="fe-thours" type="number" min="1" max="200" step="0.5" value="${r.standard_hours ?? 40}">`)}
        ${field('lbl_status', `<select id="fe-tst"><option value="ACTIVE" ${r.status==='ACTIVE'?'selected':''}>Hoạt động</option><option value="INACTIVE" ${r.status==='INACTIVE'?'selected':''}>Ngừng</option></select>`)}
    `, 'btn_save', async () => {
        const body = {
            full_name: val('fe-tname'),
            email: val('fe-temail'),
            phone: val('fe-tphone'),
            password: val('fe-tpass'),
            specialization: val('fe-tsp'),
            hire_date: val('fe-thd') || null,
            teacher_type: val('fe-ttype'),
            standard_hours: val('fe-thours'),
            status: val('fe-tst'),
        };
        if (!body.full_name || !body.email || !body.specialization) { showToast(t('toast_required'), 'warning'); return; }
        const res = await fetchAPI(`/teachers/${r.id}`, { method: 'PUT', body: JSON.stringify(body) });
        if (res?.ok) { showToast('Cập nhật giáo viên thành công', 'success'); closeModal(); loadTeachers(); }
        else showToast('Lỗi: ' + (res?.data?.error || ''), 'error');
    });
    window._syncTeacherHoursField();
    document.getElementById('modal-title').textContent = `Sửa giáo viên: ${r.full_name}`;
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;');
}

window._syncTeacherHoursField = function () {
    const typeSel = document.getElementById('fe-ttype');
    const hours = document.getElementById('fe-thours');
    if (!typeSel || !hours) return;
    const visiting = typeSel.value === 'VISITING';
    hours.disabled = visiting;
    hours.closest('.form-field, .field-row, div')?.style && (hours.parentElement.style.opacity = visiting ? '0.5' : '1');
};

function openAddTeacherModal() {
    openModal('modal_add_teacher', `
        ${field('Họ và tên *',       `<input id="f-tname" type="text" placeholder="Nguyễn Văn A">`)}
        ${field('Email *',           `<input id="f-temail" type="email" placeholder="gv@edu.vn">`)}
        ${field('Số điện thoại',     `<input id="f-tphone" type="text" placeholder="090xxxxxxx">`)}
        ${field('Mật khẩu',         `<input id="f-tpass" type="password" placeholder="Mặc định: giaovien123">`)}
        ${field('lbl_teacher_code',  `<input id="f-tc" type="text" placeholder="VD: GV001">`)}
        ${field('lbl_specialization',`<input id="f-tsp" type="text" placeholder="${t('col_specialization')}">`)}
        ${field('lbl_hire_date',     `<input id="f-thd" type="date">`)}
        ${field('Loại GV', `<select id="f-ttype" onchange="window._syncAddTeacherHours()">
            <option value="FULL_TIME">Cơ hữu</option>
            <option value="VISITING">Mời giảng</option>
        </select>`)}
        ${field('Giờ chuẩn/tháng', `<input id="f-thours" type="number" min="1" max="200" step="0.5" value="40">`)}
        ${field('lbl_status',        `<select id="f-tst"><option value="ACTIVE">${t('opt_active')}</option><option value="INACTIVE">${t('opt_inactive')}</option></select>`)}
    `, 'btn_add_teacher', async () => {
        const p = {
            full_name:      val('f-tname'),
            email:          val('f-temail'),
            phone:          val('f-tphone'),
            password:       val('f-tpass') || 'giaovien123',
            teacher_code:   val('f-tc'),
            specialization: val('f-tsp'),
            hire_date:      val('f-thd') || undefined,
            teacher_type:   val('f-ttype'),
            standard_hours: val('f-thours'),
            status:         val('f-tst'),
        };
        if (!p.full_name || !p.email || !p.teacher_code || !p.specialization) {
            showToast('Vui lòng điền đủ: Họ tên, Email, Mã GV, Chuyên môn', 'warning'); return;
        }
        const res = await fetchAPI('/teachers', { method: 'POST', body: JSON.stringify(p) });
        if (res?.ok) { showToast(t('toast_teacher_ok'), 'success'); closeModal(); loadTeachers(); }
        else showToast(t('toast_teacher_err') + ': ' + (res?.data?.error || ''), 'error');
    });
    window._syncAddTeacherHours = function () {
        const typeSel = document.getElementById('f-ttype');
        const hours = document.getElementById('f-thours');
        if (!typeSel || !hours) return;
        hours.disabled = typeSel.value === 'VISITING';
    };
    window._syncAddTeacherHours();
}

// ============================================================
// ASSIGNMENTS
// ============================================================
async function loadAssignments() {
    loading('assignments-table');
    const res = await fetchAPI('/teacher-assignments');
    if (!res?.ok) { errState('assignments-table'); return; }
    setHtml('assignments-table', makeTable([
        { key: 'id',           label: 'ID PC' },
        { key: 'teacher_id',   label: 'ID GV',   render: r => `<span class="id-chip">#${r.teacher_id}</span>` },
        { key: 'teacher_code', label: 'Mã GV',    render: r => r.teacher_code || '—' },
        { key: 'full_name',    label: 'Tên GV',   render: r => r.full_name || '—' },
        { key: 'class_id',     label: 'ID Lớp',  render: r => `<span class="id-chip">#${r.class_id}</span>` },
        { key: 'class_code',   label: 'Mã lớp',  render: r => r.class_code || '—' },
        { key: 'course_name',  label: 'Khóa học', render: r => r.course_name ? `<span title="${r.course_code || ''}">${r.course_name}</span>` : '—' },
        { key: 'scenario_name',label: 'Kịch bản', render: r => r.scenario_name || 'FINAL' },
        { key: 'assigned_at',  label: t('col_assign_date'), render: r => fmtDate(r.assigned_at) },
        { key: 'assignment_status', label: t('col_status'), render: r => statusBadge(r.assignment_status) },
        { key: '_act', label: '', render: r => `<td class="td-actions">
            <button class="btn-edit" onclick='openEditAssignmentModal(${JSON.stringify(r)})'>Sửa</button>
            <button class="btn-delete" onclick='deleteRecord("/teacher-assignments/${r.id}","phân công #${r.id}",loadAssignments)'>Xóa</button>
        </td>` },
    ], res.data.data));
}

function openEditAssignmentModal(r) {
    const statuses = [['CONFIRMED','Đã xác nhận'],['PENDING','Chờ duyệt'],['CANCELLED','Đã hủy']];
    openModal('modal_edit_assignment', `
        <div class="assign-info-card">
            <div class="assign-info-row"><span class="assign-info-label">ID Phân công</span><span class="id-chip">#${r.id}</span></div>
            <div class="assign-info-row"><span class="assign-info-label">ID Giáo viên</span><span class="id-chip">#${r.teacher_id}</span> <strong>${r.teacher_code}</strong> — ${r.full_name || ''}</div>
            <div class="assign-info-row"><span class="assign-info-label">ID Lớp học</span><span class="id-chip">#${r.class_id}</span> <strong>${r.class_code}</strong></div>
            <div class="assign-info-row"><span class="assign-info-label">Khóa học</span>${r.course_name || '—'} <span style="color:var(--text-muted);font-size:12px">(${r.course_code || ''})</span></div>
        </div>
        ${field('lbl_scenario', `<input id="fe-asn" type="text" value="${r.scenario_name || 'FINAL'}">`)}
        ${field('lbl_status', `<select id="fe-ast">${statuses.map(([v,l]) => `<option value="${v}" ${v===r.assignment_status?'selected':''}>${l}</option>`).join('')}</select>`)}
    `, 'btn_save', async () => {
        const body = { scenario_name: val('fe-asn') || 'FINAL', assignment_status: val('fe-ast') };
        const res = await fetchAPI(`/teacher-assignments/${r.id}`, { method: 'PUT', body: JSON.stringify(body) });
        if (res?.ok) { showToast('Cập nhật phân công thành công', 'success'); closeModal(); loadAssignments(); }
        else showToast('Lỗi: ' + (res?.data?.error || ''), 'error');
    });
    document.getElementById('modal-title').textContent = `Sửa phân công #${r.id}`;
}

async function openAddAssignmentModal() {
    openModal('modal_add_assignment', `<div class="loading-placeholder" style="padding:32px 0">${t('loading')}</div>`, 'btn_add_assignment', null);

    const [teacherRes, classRes] = await Promise.all([
        fetchAPI('/teachers?limit=200'),
        fetchAPI('/classes?limit=200'),
    ]);

    const teachers = teacherRes?.ok ? (teacherRes.data.data || []) : [];
    const classes  = classRes?.ok  ? (classRes.data.data  || []) : [];

    const teacherOpts = teachers.length
        ? teachers.map(t => `<option value="${t.id}">ID #${t.id} · [${t.teacher_code}] ${t.full_name} — ${t.specialization}</option>`).join('')
        : `<option value="" disabled>Không có giáo viên</option>`;

    const classOpts = classes.length
        ? classes.map(c => `<option value="${c.id}">ID #${c.id} · [${c.class_code}] ${c.course_name || ''} (${c.status})</option>`).join('')
        : `<option value="" disabled>Không có lớp học</option>`;

    document.getElementById('modal-body').innerHTML = `
        ${field('lbl_teacher_id_req', `<select id="f-ati"><option value="">-- Chọn giáo viên --</option>${teacherOpts}</select>`)}
        ${field('lbl_class_id_req',   `<select id="f-aci"><option value="">-- Chọn lớp học --</option>${classOpts}</select>`)}
        ${field('lbl_scenario',       `<input id="f-asn" type="text" placeholder="VD: FINAL" value="FINAL">`)}
    `;

    _modalCb = async () => {
        const p = { teacher_id: ival('f-ati'), class_id: ival('f-aci'), scenario_name: val('f-asn') || 'FINAL' };
        if (!p.teacher_id || !p.class_id) { showToast(t('toast_required'), 'warning'); return; }
        const res = await fetchAPI('/teacher-assignments', { method: 'POST', body: JSON.stringify(p) });
        if (res?.ok) { showToast(t('toast_assign_ok'), 'success'); closeModal(); loadAssignments(); }
        else {
            const err  = res?.data?.error || 'Lỗi không xác định';
            const det  = res?.data?.details;
            const ERRORS = {
                'Teacher is already assigned to this class': 'Giáo viên đã được phân công vào lớp này rồi',
                'Teacher not found':                        'Không tìm thấy giáo viên',
                'Class not found':                          'Không tìm thấy lớp học',
                'Missing required fields':                  'Thiếu thông tin bắt buộc',
                'Teacher Schedule Conflict Detected':       'Trùng lịch dạy',
            };
            let msg = ERRORS[err] || err;
            if (det && det.length > 0) {
                const c = det[0];
                msg += ` — lớp "${c.conflict_class}" vào ${['CN','Thứ 2','Thứ 3','Thứ 4','Thứ 5','Thứ 6','Thứ 7'][c.day_of_week] || c.day_of_week} (${c.time})`;
            }
            showToast('Lỗi phân công: ' + msg, 'error');
        }
    };
}

// ============================================================
// SCHEDULES
// ============================================================
window.openTeacherSchedule = function(teacherId) {
    window._pendingTeacherScheduleId = String(teacherId);
    navigateTo('schedules');
};

window.openStudentSchedule = function(studentId) {
    window._pendingStudentScheduleId = String(studentId);
    navigateTo('schedules');
};

async function loadSchedulesPage() {
    const [studentRes, classRes, teacherRes] = await Promise.all([
        fetchAPI('/students?limit=500'),
        fetchAPI('/classes?limit=200'),
        fetchAPI('/teachers?limit=200'),
    ]);
    const students   = studentRes?.ok ? (studentRes.data.data || []) : [];
    const allClasses = classRes?.ok   ? (classRes.data.data   || []) : [];
    const teachers   = teacherRes?.ok   ? (teacherRes.data.data || []) : [];

    const stuSel = document.getElementById('sched-student-id');
    if (stuSel) {
        const prev = stuSel.value || window._pendingStudentScheduleId || '';
        stuSel.innerHTML = `<option value="">-- Chọn học viên --</option>` +
            students.map(s => `<option value="${s.id}">[${s.student_code}] ${s.full_name}</option>`).join('');
        if (prev) stuSel.value = prev;

        stuSel.onchange = async function() {
            const sid = this.value;
            const clsSel = document.getElementById('schedule-class-id');
            if (!clsSel) return;

            if (!sid) {
                clsSel.innerHTML = '<option value="">Tất cả lớp đã đăng ký</option>';
                return;
            }

            const er = await fetchAPI(`/enrollments?student_id=${sid}&limit=100`);
            const enrList = er?.ok ? (er.data.data || []) : [];
            const paidClasses = enrList.filter(e =>
                (e.payment_status === 'PAID' || e.payment_status === 'COMPLETED') && e.status === 'ACTIVE'
            );
            clsSel.innerHTML = `<option value="">Tất cả lớp đã đăng ký</option>` +
                paidClasses.map(e => {
                    const c = allClasses.find(x => String(x.id) === String(e.class_id));
                    const label = c ? `[${c.class_code}] ${c.course_name || ''}` : (e.class_code || `Lớp #${e.class_id}`);
                    return `<option value="${e.class_id}">${label}</option>`;
                }).join('');
            setHtml('schedules-table', '<p style="color:var(--text-muted);text-align:center;padding:24px 0">Chọn học viên rồi nhấn Xem lịch học</p>');
        };
        if (prev) stuSel.onchange();
    }

    const btnStudent = document.getElementById('btn-load-student-schedule');
    if (btnStudent) btnStudent.onclick = () => { _ttStudentWeekOffset = 0; loadStudentSchedule(); };

    const btnDetail = document.getElementById('btn-load-schedule-detail');
    if (btnDetail) btnDetail.onclick = () => { _ttLastClassId = null; _ttWeekOffset = 0; loadSchedules(); };

    const tSel = document.getElementById('sched-teacher-id');
    if (tSel) {
        const prevT = tSel.value || window._pendingTeacherScheduleId || '';
        tSel.innerHTML = `<option value="">-- Chọn giáo viên --</option>` +
            teachers.map(t => `<option value="${t.id}">[${t.teacher_code}] ${t.full_name}</option>`).join('');
        if (prevT) tSel.value = prevT;
    }

    const modeSel = document.getElementById('sched-view-mode');
    const studentPanel = document.getElementById('sched-student-panel');
    const teacherPanel = document.getElementById('sched-teacher-panel');
    const setSchedMode = (mode) => {
        if (studentPanel) studentPanel.style.display = mode === 'student' ? '' : 'none';
        if (teacherPanel) teacherPanel.style.display = mode === 'teacher' ? '' : 'none';
        if (mode === 'teacher') {
            setHtml('schedules-table', '<p style="color:var(--text-muted);text-align:center;padding:24px 0">Chọn giáo viên rồi nhấn Xem lịch dạy</p>');
        } else {
            setHtml('schedules-table', '<p style="color:var(--text-muted);text-align:center;padding:24px 0">Chọn học viên rồi nhấn Xem lịch học (chỉ lớp đã đăng ký & thanh toán)</p>');
        }
    };
    if (modeSel) {
        if (window._pendingTeacherScheduleId) {
            modeSel.value = 'teacher';
            window._pendingTeacherScheduleId = null;
        } else if (window._pendingStudentScheduleId) {
            modeSel.value = 'student';
            window._pendingStudentScheduleId = null;
        }
        setSchedMode(modeSel.value);
        modeSel.onchange = () => setSchedMode(modeSel.value);
    }

    const tBtn = document.getElementById('btn-load-teacher-schedule');
    if (tBtn) tBtn.onclick = () => { _ttTeacherWeekOffset = 0; loadTeacherSchedule(); };

    if (modeSel?.value === 'teacher' && tSel?.value) {
        loadTeacherSchedule();
    }
    if (modeSel?.value === 'student' && stuSel?.value) {
        loadStudentSchedule();
    }

    // "+" button is inline in each timetable cell (single-class mode only)
}

function _fmtDMY(d) { return `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()}`; }
function _fmtISO(d) { return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; }

// Generate all dates of a given dayOfWeek (1=Sun,2=Mon…7=Sat) within [start,end]
function _sessionDates(startStr, endStr, dayOfWeek) {
    // Convert our day (1=Sun,2=Mon…7=Sat) to JS getDay() (0=Sun,1=Mon…6=Sat)
    const jsDow = dayOfWeek === 1 ? 0 : dayOfWeek - 1;
    const end = new Date(endStr.split('T')[0]);
    const cur = new Date(startStr.split('T')[0]);
    while (cur.getDay() !== jsDow) cur.setDate(cur.getDate() + 1);
    const dates = [];
    while (cur <= end) { dates.push(new Date(cur)); cur.setDate(cur.getDate() + 7); }
    return dates;
}

// ── week offset for timetable navigation ──────────────────────
let _ttWeekOffset  = 0;
let _ttLastClassId = null; // tracks which class is currently shown
let _ttTeacherWeekOffset = 0;
let _ttTeacherId = null;
let _ttStudentWeekOffset = 0;
let _ttStudentId = null;

function _getWeekDates(offset) {
    const today = new Date();
    const dow   = today.getDay(); // 0=Sun
    const mon   = new Date(today);
    mon.setDate(today.getDate() - (dow === 0 ? 6 : dow - 1) + offset * 7);
    return Array.from({length:7}, (_, i) => { const d = new Date(mon); d.setDate(mon.getDate()+i); return d; });
}
function _fmtDM(d)  { return `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}`; }

// ── main schedule loader (timetable view) ──────────────────────
async function loadSchedules() {
    const studentId   = document.getElementById('sched-student-id')?.value;
    const classId     = document.getElementById('schedule-class-id')?.value;
    const isRosterMode = (studentId === 'all-students');

    if (!studentId || !classId) {
        setHtml('schedules-table','<p style="color:var(--text-muted);text-align:center;padding:24px 0">Chọn học viên và lớp học, sau đó nhấn Xem lịch</p>');
        return;
    }
    // Roster mode requires a specific class (not 'all')
    if (isRosterMode && classId === 'all') {
        setHtml('schedules-table','<p style="color:var(--text-muted);text-align:center;padding:24px 0">Vui lòng chọn một lớp cụ thể để xem danh sách học viên</p>');
        return;
    }
    loading('schedules-table');

    // ── Roster mode: show all students in one class ──────────────
    if (isRosterMode) {
        return loadRosterTimetable(classId);
    }

    // ── Fetch data ──────────────────────────────
    const isAllMode = (classId === 'all');
    const schedUrl  = isAllMode
        ? `/schedules?student_id=${studentId}`
        : `/schedules?class_id=${classId}&student_id=${studentId}`;
    const assignUrl = isAllMode
        ? `/teacher-assignments?student_id=${studentId}`
        : `/teacher-assignments?class_id=${classId}&student_id=${studentId}`;
    const attUrl    = isAllMode
        ? `/attendance?student_id=${studentId}`
        : `/attendance?class_id=${classId}&student_id=${studentId}`;

    const [schedRes, assignRes, attRes] = await Promise.all([
        fetchAPI(schedUrl),
        fetchAPI(assignUrl),
        fetchAPI(attUrl),
    ]);

    if (!schedRes?.ok) { errState('schedules-table'); return; }
    const allSchedRows = schedRes.data.data || [];
    const schedules    = allSchedRows.filter(s => s.schedule_type === 'REGULAR');
    const specials     = allSchedRows.filter(s => s.schedule_type !== 'REGULAR'); // EXAM + MAKEUP
    const assignments  = assignRes?.ok ? (assignRes.data.data || []) : [];
    const attList      = attRes?.ok    ? (attRes.data.data    || []) : [];

    const stuSel  = document.getElementById('sched-student-id');
    const stuName = (stuSel?.selectedOptions[0]?.text || '').replace(/^\[.*?\]\s*/, '');

    // class meta – use first schedule row for single-class mode, or derive for all
    const metaRow    = allSchedRows[0] || {};
    const classCode  = isAllMode ? 'TẤT CẢ' : (metaRow.class_code || '');
    const courseName = isAllMode ? 'Tất cả khóa học' : (metaRow.course_name || '');
    const startDate  = isAllMode ? null : metaRow.class_start_date;
    const endDate    = isAllMode ? null : metaRow.class_end_date;
    const numSess    = isAllMode ? 0    : (metaRow.num_sessions || 0);

    // Auto-jump to class start week when switching to a new class
    if (!isAllMode && startDate && _ttLastClassId !== classId) {
        _ttLastClassId = classId;
        const today      = new Date(); today.setHours(0,0,0,0);
        const classStart = new Date(startDate); classStart.setHours(0,0,0,0);
        const diffMs     = classStart - today;
        const diffWeeks  = Math.round(diffMs / (7 * 86400000));
        if (_ttWeekOffset !== diffWeeks) {
            _ttWeekOffset = diffWeeks;
            return loadSchedules(); // re-render with correct week
        }
    }

    // assignment map: class_id → assignment (or first for single mode)
    const assignMap = {};
    // Key by "classId_dayOfWeek" for per-day assignment
    // Separate per-day records from global (no day_of_week) records
    const globalAssigns = assignments.filter(a => !a.day_of_week);
    const dayAssigns    = assignments.filter(a =>  a.day_of_week);
    // Load global (fallback) first, then override with specific-day records
    globalAssigns.forEach(a => { assignMap[`${a.class_id}_all`] = a; });
    dayAssigns.forEach(a    => { assignMap[`${a.class_id}_${a.day_of_week}`] = a; });
    const defaultAssign = globalAssigns[0] || null;

    // attendance map: class_id + session_date → record (đồng bộ với lịch học)
    const attMap = {};
    attList.forEach(a => {
        const d = a.attendance_date?.split('T')[0];
        if (!d) return;
        attMap[`${a.class_id}_${d}`] = a;
        if (!isAllMode) attMap[d] = a;
    });

    const completed   = attList.filter(a => a.attendance_status === 'PRESENT' && a.tinh_luong == 1).length;
    const progressPct = numSess ? Math.round(completed / numSess * 100) : 0;

    if (!schedules.length && !specials.length) {
        setHtml('schedules-table','<p style="color:var(--text-muted);text-align:center;padding:32px 0">Chưa có lịch học. Lịch sẽ tạo tự động sau khi thanh toán.</p>');
        return;
    }

    // ── Timetable helpers ─────────────────────
    const weekDates = _getWeekDates(_ttWeekOffset);
    const weekStart = weekDates[0];
    const weekEnd   = weekDates[6];
    const dayOrder  = [1, 2, 3, 4, 5, 6, 0];
    const dayLabels = { 0: 'CN', 1: 'T2', 2: 'T3', 3: 'T4', 4: 'T5', 5: 'T6', 6: 'T7' };
    const dayIdx    = { 1: 0, 2: 1, 3: 2, 4: 3, 5: 4, 6: 5, 0: 6 };
    const _toDay    = s => s ? new Date(s.split('T')[0]) : null;

    const classStartDay = _toDay(startDate);
    const classEndDay   = _toDay(endDate);
    const weekBeforeClass = classStartDay && _fmtISO(weekEnd) < _fmtISO(classStartDay);
    const weekAfterClass  = classEndDay   && _fmtISO(weekStart) > _fmtISO(classEndDay);

    // schedByDay: dayOfWeek → [sched rows] (multiple classes may share a day)
    const schedByDay = {};
    schedules.forEach(s => {
        if (!schedByDay[s.day_of_week]) schedByDay[s.day_of_week] = [];
        schedByDay[s.day_of_week].push(s);
    });

    // specialsByDate: "YYYY-MM-DD" → [specials]  (keyed by exact date, not day-of-week)
    const specialsByDate = {};
    specials.forEach(s => {
        const key = s.specific_date ? s.specific_date.split('T')[0] : null;
        if (!key) return; // skip if no specific_date (old records without date)
        if (!specialsByDate[key]) specialsByDate[key] = [];
        specialsByDate[key].push(s);
    });

    // ── Build cell ───────────────────────────
    function buildCell(d) {
        const cellDate = weekDates[dayIdx[d]];
        const iso      = _fmtISO(cellDate);

        // In all-mode: only show rows whose class is active on this cellDate
        const allRows = schedByDay[d] || [];
        const rows = isAllMode
            ? allRows.filter(s => {
                const sd = s.class_start_date ? _toDay(s.class_start_date) : null;
                const ed = s.class_end_date   ? _toDay(s.class_end_date)   : null;
                return (!sd || cellDate >= sd) && (!ed || cellDate <= ed);
            })
            : allRows;

        const spRows = specialsByDate[iso] || [];

        const outOfRange = !isAllMode && (
            (classStartDay && cellDate < classStartDay) ||
            (classEndDay   && cellDate > classEndDay)
        );
        const inRange = !isAllMode && !outOfRange;

        // Empty cell with no content and not in range → blank
        if (!rows.length && !spRows.length && !inRange) {
            return `<td class="tt-cell tt-cell-empty"></td>`;
        }
        // Out of range, no specials → closed indicator
        if (outOfRange && !spRows.length) {
            return `<td class="tt-cell tt-cell-closed"></td>`;
        }

        let cellContent = '';

        // Regular schedule items
        rows.forEach(sched => {
            if (outOfRange) return;
            const cid        = sched.class_id;
            const att        = attMap[`${cid}_${iso}`] || (!isAllMode ? attMap[iso] : null);
            const svPres     = att ? att.attendance_status === 'PRESENT' : false;
            const gvPres     = att ? att.tinh_luong == 1 : false;
            const bothDone   = svPres && gvPres;
            // Per-day assignment takes priority over global (class-wide) fallback
            const assign = assignMap[`${sched.class_id}_${d}`] || assignMap[`${sched.class_id}_all`] || null;
            const gvName     = assign ? (assign.teacher_name || assign.teacher_code || '') : '';
            const assignId   = assign ? assign.id : 0;
            const assignHtml = assign
                ? `<div class="tt-gv-line">${gvName}
                       ${!isAllMode ? `<button class="tt-teacher-btn tt-teacher-edit" onclick="openAssignTeacherModal(${cid},${studentId},${assignId},'',${JSON.stringify([])},${d})">Sửa</button>
                       <button class="tt-teacher-btn tt-teacher-del"  onclick="deleteStudentTeacher(${assignId})">Xóa</button>` : ''}
                   </div>`
                : (!isAllMode ? `<button class="tt-add-teacher-btn" onclick="openAssignTeacherModal(${cid},${studentId},0,'',${JSON.stringify([])},${d})">+ Thêm GV</button>` : '<span style="color:var(--text-faint);font-size:10px">Chưa có GV</span>');

            const _bc = bothDone ? '#22c55e' : '#06b6d4';
            const _bg = bothDone ? 'rgba(34,197,94,0.09)' : 'rgba(6,182,212,0.09)';
            cellContent += `<div class="tt-item${bothDone?' tt-item-done':''}" style="border-left:3px solid ${_bc};background:${_bg}">
                <div class="tt-item-time">${sched.start_time.slice(0,5)} – ${sched.end_time.slice(0,5)}</div>
                <div class="tt-sv-name-line">${stuName}</div>
                <div class="tt-info-line"><span class="tt-lbl">Giáo viên dạy:</span> ${assignHtml}</div>
                ${isAllMode
                    ? `<div class="tt-item-course-tag">${sched.course_name || sched.class_code}</div>`
                    : `<div class="tt-att-row">
                    <span class="tt-lbl">Điểm danh:</span>
                    <label class="tt-att-check">
                        <input type="checkbox" ${gvPres?'checked':''} onchange="window._markAtt(${cid},${studentId},'${iso}',this.checked,${svPres?1:0})">
                        <span>Giáo viên</span>
                    </label>
                    <label class="tt-att-check">
                        <input type="checkbox" ${svPres?'checked':''} onchange="window._markAtt(${cid},${studentId},'${iso}',${gvPres?1:0},this.checked)">
                        <span>Học viên</span>
                    </label>
                    ${bothDone ? '<span class="tt-done-tick">✓</span>' : ''}
                </div>`}
            </div>`;
        });

        // Special items (EXAM, MAKEUP)
        spRows.forEach(sp => {
            const color = sp.schedule_type === 'EXAM' ? '#fde68a' : '#f59e0b';
            const label = sp.schedule_type === 'EXAM' ? (sp.exam_label || 'Lịch thi') : 'Học bù';
            const examBg = sp.schedule_type === 'EXAM' ? 'rgba(253,230,138,.13)' : 'rgba(0,0,0,.18)';
            cellContent += `<div class="tt-item tt-item-special" style="border-left:3px solid ${color};background:${examBg}">
                <div class="tt-item-time" style="color:${color}">${sp.start_time.slice(0,5)} – ${sp.end_time.slice(0,5)}</div>
                <div class="tt-special-label" style="color:${color}">${label}</div>
                ${sp.exam_supervisor ? `<div class="tt-info-line"><span class="tt-lbl">GV trông thi:</span> ${sp.exam_supervisor}</div>` : ''}
                ${isAllMode ? `<div class="tt-item-course-tag">${sp.course_name || sp.class_code}</div>` : ''}
                <button class="tt-del-btn" onclick='deleteRecord("/schedules/${sp.id}","${label}",loadSchedules)'>✕</button>
            </div>`;
        });

        // "+" add button: only in single-class mode and within class date range
        if (inRange) {
            cellContent += `<button class="tt-add-special-btn" onclick="openAddExamMakeupModal(${parseInt(classId)},'${iso}')" title="Thêm lịch thi / học bù">+</button>`;
        }

        return `<td class="tt-cell">${cellContent}</td>`;
    }

    const classRangeTxt = (classStartDay && classEndDay)
        ? `<span class="tt-class-range">${_fmtDMY(classStartDay)} – ${_fmtDMY(classEndDay)} · ${numSess} buổi</span>`
        : '';

    // Date picker value
    const pickerISO = `${weekStart.getFullYear()}-${String(weekStart.getMonth()+1).padStart(2,'0')}-${String(weekStart.getDate()).padStart(2,'0')}`;

    const html = `
    <div class="tt-nav-bar">
        <div class="tt-nav-left">
            <span class="tt-class-chip">${classCode}</span>
            <span class="tt-course-name">${courseName}</span>
            ${classRangeTxt}
        </div>
        <div class="tt-nav-center">
            <button class="tt-nav-btn" onclick="_ttWeekOffset--;loadSchedules()">&#8249; Trở về</button>
            <button class="tt-nav-btn tt-nav-today" onclick="_ttWeekOffset=0;loadSchedules()">Hiện tại</button>
            <input type="date" class="tt-date-picker" value="${pickerISO}"
                onchange="(function(v){
                    const d=new Date(v);
                    const today=new Date();
                    const diffDay=Math.round((d-today)/86400000);
                    _ttWeekOffset=Math.round(diffDay/7);
                    loadSchedules();
                })(this.value)">
            <button class="tt-nav-btn" onclick="_ttWeekOffset++;loadSchedules()">Tiếp &#8250;</button>
        </div>
        <div class="tt-nav-right">
            ${!isAllMode && numSess ? `<div class="tt-progress-mini">
                <span>${completed} buổi / ${numSess} tuần</span>
                <div class="tt-prog-bar"><div style="width:${progressPct}%"></div></div>
            </div>` : `<span class="tt-week-label">${_fmtDMY(weekStart)} – ${_fmtDMY(weekEnd)}</span>`}
        </div>
    </div>
    ${weekBeforeClass ? `<div class="tt-out-of-range">Tuần này trước ngày khai giảng (${_fmtDMY(classStartDay)})</div>` : ''}
    ${weekAfterClass  ? `<div class="tt-out-of-range">Lớp đã kết thúc vào ${_fmtDMY(classEndDay)}</div>` : ''}
    <div class="timetable-wrap">
        <table class="timetable">
            <thead>
                <tr>
                    <th class="tt-head-period">Ca học</th>
                    ${dayOrder.map(d => {
                        const dt = weekDates[dayIdx[d]];
                        return `<th class="tt-head-day"><div class="tt-head-day-name">${dayLabels[d]}</div><div class="tt-head-day-date">${_fmtDM(dt)}</div></th>`;
                    }).join('')}
                </tr>
            </thead>
            <tbody>
                <tr class="tt-period-row">
                    <td class="tt-period-label"><span class="tt-period-name">Lịch học</span></td>
                    ${dayOrder.map(d => buildCell(d)).join('')}
                </tr>
            </tbody>
        </table>
    </div>
    <div class="tt-legend">
        <span class="tt-legend-item"><span class="tt-legend-dot" style="background:#06b6d4"></span>Lịch học</span>
        <span class="tt-legend-item"><span class="tt-legend-dot" style="background:#fde68a"></span>Lịch thi</span>
        <span class="tt-legend-item"><span class="tt-legend-dot" style="background:#f59e0b"></span>Học bù</span>
        <span class="tt-legend-item"><span class="tt-legend-dot" style="background:#22c55e"></span>Hoàn thành</span>
    </div>`;

    setHtml('schedules-table', html);
}

// ── Student schedule (GET /students/{id}/schedule) ─────────────
async function loadStudentSchedule() {
    const studentId = document.getElementById('sched-student-id')?.value;
    if (!studentId) {
        setHtml('schedules-table', '<p style="color:var(--text-muted);text-align:center;padding:24px 0">Chọn học viên rồi nhấn Xem lịch học</p>');
        return;
    }

    loading('schedules-table');
    _ttStudentId = studentId;

    const classId = document.getElementById('schedule-class-id')?.value || '';
    const weekDates = _getWeekDates(_ttStudentWeekOffset);
    const weekStart = _fmtISO(weekDates[0]);
    const weekEnd   = weekDates[6];

    let path = `/students/${studentId}/schedule?week=${weekStart}`;
    if (classId) path += `&class_id=${classId}`;

    const res = await fetchAPI(path);
    if (!res?.ok) {
        errState('schedules-table');
        return;
    }

    const payload = res.data || {};
    const items   = payload.data || [];
    const student = payload.student || {};
    const dayOrder  = [1, 2, 3, 4, 5, 6, 0];
    const dayLabels = { 0: 'CN', 1: 'T2', 2: 'T3', 3: 'T4', 4: 'T5', 5: 'T6', 6: 'T7' };
    const dayIdx    = { 1: 0, 2: 1, 3: 2, 4: 3, 5: 4, 6: 5, 0: 6 };

    const byDate = {};
    items.forEach(item => {
        const key = item.session_date ? item.session_date.split('T')[0] : null;
        if (!key) return;
        if (!byDate[key]) byDate[key] = [];
        byDate[key].push(item);
    });

    const attLabels = { PRESENT: 'Có mặt', ABSENT: 'Vắng', LATE: 'Muộn', EXCUSED: 'Có phép' };
    function attBadgeHtml(item) {
        if (item.attendance_marked) {
            const st = (item.attendance_status || '').toLowerCase();
            const label = attLabels[item.attendance_status] || item.attendance_status;
            return `<div class="tt-att-badge tt-att-${st}">${label}</div>`;
        }
        return '<div class="tt-att-badge tt-att-none">Chưa ĐD</div>';
    }

    function buildStudentCell(dow) {
        const iso = _fmtISO(weekDates[dayIdx[dow]]);
        const rows = byDate[iso] || [];
        if (!rows.length) {
            return '<td class="tt-cell tt-cell-empty"></td>';
        }
        let cellContent = '';
        rows.forEach(item => {
            const type = item.schedule_type || 'REGULAR';
            const isSpecial = type !== 'REGULAR';
            const color = type === 'EXAM' ? '#fde68a' : (type === 'MAKEUP' ? '#f59e0b' : '#6366f1');
            const bg = type === 'EXAM' ? 'rgba(253,230,138,.13)' : (type === 'MAKEUP' ? 'rgba(245,158,11,.12)' : 'rgba(99,102,241,0.09)');
            const typeLabel = type === 'EXAM' ? (item.exam_label || 'Lịch thi') : (type === 'MAKEUP' ? 'Học bù' : 'Lịch học');
            const doneCls = item.attendance_marked && item.attendance_status === 'PRESENT' ? ' tt-item-done' : '';
            cellContent += `<div class="tt-item${isSpecial ? ' tt-item-special' : ''}${doneCls}" style="border-left:3px solid ${color};background:${bg}">
                <div class="tt-item-time">${(item.start_time || '').slice(0, 5)} – ${(item.end_time || '').slice(0, 5)}</div>
                <div class="tt-item-course-tag">${item.class_code || ''} · ${item.course_name || ''}</div>
                <div class="tt-sv-name-line">GV: ${item.teacher_name || item.teacher_code || '—'}</div>
                ${isSpecial ? `<div class="tt-special-label" style="color:${color}">${typeLabel}</div>` : ''}
                ${attBadgeHtml(item)}
            </div>`;
        });
        return `<td class="tt-cell">${cellContent}</td>`;
    }

    const filterNote = classId ? ' · 1 lớp' : ' · tất cả lớp đã ĐK';
    const html = `
    <div class="tt-nav-bar">
        <div class="tt-nav-left">
            <span class="tt-class-chip">${student.student_code || 'HV'}</span>
            <span class="tt-course-name">${student.full_name || ''}</span>
            <span class="tt-class-range">Lịch học theo tuần${filterNote}</span>
        </div>
        <div class="tt-nav-center">
            <button class="tt-nav-btn" onclick="_ttStudentWeekOffset--;loadStudentSchedule()">&#8249; Trở về</button>
            <button class="tt-nav-btn tt-nav-today" onclick="_ttStudentWeekOffset=0;loadStudentSchedule()">Hiện tại</button>
            <input type="date" class="tt-date-picker" value="${weekStart}"
                onchange="(function(v){
                    const d=new Date(v);
                    const today=new Date();
                    const diffDay=Math.round((d-today)/86400000);
                    _ttStudentWeekOffset=Math.round(diffDay/7);
                    loadStudentSchedule();
                })(this.value)">
            <button class="tt-nav-btn" onclick="_ttStudentWeekOffset++;loadStudentSchedule()">Tiếp &#8250;</button>
        </div>
        <div class="tt-nav-right">
            <span class="tt-week-label">${_fmtDMY(weekDates[0])} – ${_fmtDMY(weekEnd)}</span>
        </div>
    </div>
    ${!items.length ? '<div class="tt-out-of-range">Không có buổi học trong tuần này. (Cần đăng ký + thanh toán lớp trước)</div>' : ''}
    <div class="timetable-wrap">
        <table class="timetable">
            <thead>
                <tr>
                    <th class="tt-head-period">Ca học</th>
                    ${dayOrder.map(d => {
                        const dt = weekDates[dayIdx[d]];
                        return `<th class="tt-head-day"><div class="tt-head-day-name">${dayLabels[d]}</div><div class="tt-head-day-date">${_fmtDM(dt)}</div></th>`;
                    }).join('')}
                </tr>
            </thead>
            <tbody>
                <tr class="tt-period-row">
                    <td class="tt-period-label"><span class="tt-period-name">Lịch học</span></td>
                    ${dayOrder.map(d => buildStudentCell(d)).join('')}
                </tr>
            </tbody>
        </table>
    </div>
    <div class="tt-legend">
        <span class="tt-legend-item"><span class="tt-legend-dot" style="background:#6366f1"></span>Lịch học</span>
        <span class="tt-legend-item"><span class="tt-legend-dot" style="background:#fde68a"></span>Lịch thi</span>
        <span class="tt-legend-item"><span class="tt-legend-dot" style="background:#f59e0b"></span>Học bù</span>
    </div>`;

    setHtml('schedules-table', html);
}

// ── Teacher schedule (GET /teachers/{id}/schedule) ─────────────
async function loadTeacherSchedule() {
    const teacherId = document.getElementById('sched-teacher-id')?.value;
    if (!teacherId) {
        setHtml('schedules-table', '<p style="color:var(--text-muted);text-align:center;padding:24px 0">Chọn giáo viên rồi nhấn Xem lịch dạy</p>');
        return;
    }

    loading('schedules-table');
    _ttTeacherId = teacherId;

    const weekDates = _getWeekDates(_ttTeacherWeekOffset);
    const weekStart = _fmtISO(weekDates[0]);
    const weekEnd   = weekDates[6];

    const res = await fetchAPI(`/teachers/${teacherId}/schedule?week=${weekStart}`);
    if (!res?.ok) {
        errState('schedules-table');
        return;
    }

    const payload = res.data || {};
    const items   = payload.data || [];
    const teacher = payload.teacher || {};
    const dayOrder  = [1, 2, 3, 4, 5, 6, 0];
    const dayLabels = { 0: 'CN', 1: 'T2', 2: 'T3', 3: 'T4', 4: 'T5', 5: 'T6', 6: 'T7' };
    const dayIdx    = { 1: 0, 2: 1, 3: 2, 4: 3, 5: 4, 6: 5, 0: 6 };

    const byDate = {};
    items.forEach(item => {
        const key = item.session_date ? item.session_date.split('T')[0] : null;
        if (!key) return;
        if (!byDate[key]) byDate[key] = [];
        byDate[key].push(item);
    });

    function buildTeacherCell(dow) {
        const iso = _fmtISO(weekDates[dayIdx[dow]]);
        const rows = byDate[iso] || [];
        if (!rows.length) {
            return '<td class="tt-cell tt-cell-empty"></td>';
        }
        let cellContent = '';
        rows.forEach(item => {
            const type = item.schedule_type || 'REGULAR';
            const isSpecial = type !== 'REGULAR';
            const color = type === 'EXAM' ? '#fde68a' : (type === 'MAKEUP' ? '#f59e0b' : '#06b6d4');
            const bg = type === 'EXAM' ? 'rgba(253,230,138,.13)' : (type === 'MAKEUP' ? 'rgba(245,158,11,.12)' : 'rgba(6,182,212,0.09)');
            const typeLabel = type === 'EXAM' ? (item.exam_label || 'Lịch thi') : (type === 'MAKEUP' ? 'Học bù' : 'Lịch dạy');
            cellContent += `<div class="tt-item${isSpecial ? ' tt-item-special' : ''}" style="border-left:3px solid ${color};background:${bg}">
                <div class="tt-item-time">${(item.start_time || '').slice(0, 5)} – ${(item.end_time || '').slice(0, 5)}</div>
                <div class="tt-item-course-tag">${item.class_code || ''} · ${item.course_name || ''}</div>
                <div class="tt-sv-name-line">${item.student_name || item.student_code || '—'}</div>
                ${isSpecial ? `<div class="tt-special-label" style="color:${color}">${typeLabel}</div>` : ''}
            </div>`;
        });
        return `<td class="tt-cell">${cellContent}</td>`;
    }

    const pickerISO = weekStart;
    const html = `
    <div class="tt-nav-bar">
        <div class="tt-nav-left">
            <span class="tt-class-chip">${teacher.teacher_code || 'GV'}</span>
            <span class="tt-course-name">${teacher.full_name || ''}</span>
            <span class="tt-class-range">Lịch dạy theo tuần</span>
        </div>
        <div class="tt-nav-center">
            <button class="tt-nav-btn" onclick="_ttTeacherWeekOffset--;loadTeacherSchedule()">&#8249; Trở về</button>
            <button class="tt-nav-btn tt-nav-today" onclick="_ttTeacherWeekOffset=0;loadTeacherSchedule()">Hiện tại</button>
            <input type="date" class="tt-date-picker" value="${pickerISO}"
                onchange="(function(v){
                    const d=new Date(v);
                    const today=new Date();
                    const diffDay=Math.round((d-today)/86400000);
                    _ttTeacherWeekOffset=Math.round(diffDay/7);
                    loadTeacherSchedule();
                })(this.value)">
            <button class="tt-nav-btn" onclick="_ttTeacherWeekOffset++;loadTeacherSchedule()">Tiếp &#8250;</button>
        </div>
        <div class="tt-nav-right">
            <span class="tt-week-label">${_fmtDMY(weekDates[0])} – ${_fmtDMY(weekEnd)}</span>
        </div>
    </div>
    ${!items.length ? '<div class="tt-out-of-range">Không có buổi dạy trong tuần này.</div>' : ''}
    <div class="timetable-wrap">
        <table class="timetable">
            <thead>
                <tr>
                    <th class="tt-head-period">Ca dạy</th>
                    ${dayOrder.map(d => {
                        const dt = weekDates[dayIdx[d]];
                        return `<th class="tt-head-day"><div class="tt-head-day-name">${dayLabels[d]}</div><div class="tt-head-day-date">${_fmtDM(dt)}</div></th>`;
                    }).join('')}
                </tr>
            </thead>
            <tbody>
                <tr class="tt-period-row">
                    <td class="tt-period-label"><span class="tt-period-name">Lịch dạy</span></td>
                    ${dayOrder.map(d => buildTeacherCell(d)).join('')}
                </tr>
            </tbody>
        </table>
    </div>
    <div class="tt-legend">
        <span class="tt-legend-item"><span class="tt-legend-dot" style="background:#06b6d4"></span>Lịch dạy</span>
        <span class="tt-legend-item"><span class="tt-legend-dot" style="background:#fde68a"></span>Lịch thi</span>
        <span class="tt-legend-item"><span class="tt-legend-dot" style="background:#f59e0b"></span>Học bù</span>
    </div>`;

    setHtml('schedules-table', html);
}

window._markAtt = async function(classId, studentId, date, gvPresent, svPresent) {
    const body = {
        class_id: classId, student_id: studentId, attendance_date: date,
        attendance_status: svPresent ? 'PRESENT' : 'ABSENT',
        teacher_present: gvPresent ? 1 : 0,
    };
    const r = await fetchAPI('/attendance', { method: 'POST', body: JSON.stringify(body) });
    if (r?.ok) loadSchedules();
    else showToast('Lỗi điểm danh: ' + (r?.data?.error || ''), 'error');
};

// ── Roster timetable: all students in one class ──────────────────
async function loadRosterTimetable(classId) {
    const [schedRes, assignRes, enrollRes] = await Promise.all([
        fetchAPI(`/schedules?class_id=${classId}`),
        fetchAPI(`/teacher-assignments?class_id=${classId}`),
        fetchAPI(`/enrollments?class_id=${classId}&limit=200`),
    ]);

    if (!schedRes?.ok) { errState('schedules-table'); return; }
    const allRows   = schedRes.data.data  || [];
    const assigns   = assignRes?.ok ? (assignRes.data.data   || []) : [];
    const enrolls   = enrollRes?.ok ? (enrollRes.data.data   || []) : [];

    const schedules = allRows.filter(s => s.schedule_type === 'REGULAR');
    const specials  = allRows.filter(s => s.schedule_type !== 'REGULAR');
    const metaRow   = allRows[0] || {};

    const classCode  = metaRow.class_code  || '';
    const courseName = metaRow.course_name || '';
    const startDate  = metaRow.class_start_date;
    const endDate    = metaRow.class_end_date;
    const numSess    = metaRow.num_sessions || 0;

    // Auto-jump to class start week
    if (startDate && _ttLastClassId !== classId) {
        _ttLastClassId = classId;
        const today      = new Date(); today.setHours(0,0,0,0);
        const classStart = new Date(startDate); classStart.setHours(0,0,0,0);
        const diffWeeks  = Math.round((classStart - today) / (7 * 86400000));
        if (_ttWeekOffset !== diffWeeks) { _ttWeekOffset = diffWeeks; return loadRosterTimetable(classId); }
    }

    // Paid students set
    const paidStudentIds = new Set(
        enrolls.filter(e => e.payment_status === 'PAID' || e.payment_status === 'COMPLETED')
               .map(e => String(e.student_id))
    );

    // Teacher assignment map: "studentId_day" → assignment
    const assignMap = {};
    assigns.filter(a =>  a.day_of_week).forEach(a => { assignMap[`${a.student_id}_${a.day_of_week}`] = a; });
    assigns.filter(a => !a.day_of_week).forEach(a => {
        if (!assignMap[`${a.student_id}_all`]) assignMap[`${a.student_id}_all`] = a;
    });

    // Group schedules by day_of_week → list of student slots
    const schedByDay = {};
    schedules.forEach(s => {
        if (!schedByDay[s.day_of_week]) schedByDay[s.day_of_week] = [];
        schedByDay[s.day_of_week].push(s);
    });

    // Special events by exact date
    const specialsByDate = {};
    specials.forEach(s => {
        const key = s.specific_date ? s.specific_date.split('T')[0] : null;
        if (!key) return;
        if (!specialsByDate[key]) specialsByDate[key] = [];
        specialsByDate[key].push(s);
    });

    const _toDay = s => s ? new Date(s.split('T')[0]) : null;
    const classStartDay = _toDay(startDate);
    const classEndDay   = _toDay(endDate);

    const weekDates = _getWeekDates(_ttWeekOffset);
    const weekStart = weekDates[0];
    const weekEnd   = weekDates[6];
    const dayOrder  = [1, 2, 3, 4, 5, 6, 0];
    const dayLabels = { 0: 'CN', 1: 'T2', 2: 'T3', 3: 'T4', 4: 'T5', 5: 'T6', 6: 'T7' };
    const dayIdx    = { 1: 0, 2: 1, 3: 2, 4: 3, 5: 4, 6: 5, 0: 6 };

    const weekBeforeClass = classStartDay && _fmtISO(weekEnd) < _fmtISO(classStartDay);
    const weekAfterClass  = classEndDay   && _fmtISO(weekStart) > _fmtISO(classEndDay);

    const totalPaid = paidStudentIds.size;

    function buildRosterCell(d) {
        const cellDate = weekDates[dayIdx[d]];
        const iso      = _fmtISO(cellDate);
        const spRows   = specialsByDate[iso] || [];
        const rows     = schedByDay[d] || [];

        const outOfRange = (classStartDay && cellDate < classStartDay) ||
                           (classEndDay   && cellDate > classEndDay);
        const inRange = !outOfRange;

        if (!rows.length && !spRows.length && !inRange) return `<td class="tt-cell tt-cell-empty"></td>`;
        if (outOfRange && !spRows.length && !rows.length) return `<td class="tt-cell tt-cell-closed"></td>`;

        // Group rows by time slot
        const timeSlots = {};
        rows.forEach(s => {
            const key = `${s.start_time}|${s.end_time}`;
            if (!timeSlots[key]) timeSlots[key] = { start: s.start_time, end: s.end_time, students: [] };
            timeSlots[key].students.push(s);
        });

        let cellContent = '';

        // Render each time slot
        Object.values(timeSlots).forEach(slot => {
            const paidCount = slot.students.filter(s => paidStudentIds.has(String(s.student_id))).length;
            const totalCount = slot.students.length;
            const slotId = `slot_${d}_${slot.start.replace(':','')}_${Math.random().toString(36).slice(2,6)}`;

            // Only show students that exist (have sv_full_name) — skip orphaned schedules
                const validStudents = slot.students.filter(s => s.sv_full_name);
                if (!validStudents.length) return;

                // Pre-compute teacher IDs already assigned to any student in this slot on this day
                const slotAssignedTeacherIds = validStudents
                    .map(sv => (assignMap[`${sv.student_id}_${d}`] || assignMap[`${sv.student_id}_all`] || null))
                    .filter(a => a && a.teacher_id)
                    .map(a => a.teacher_id);

                const studentRows = validStudents.map(s => {
                const svName = s.sv_full_name;
                const isPaid = paidStudentIds.has(String(s.student_id));
                const assign = assignMap[`${s.student_id}_${d}`] || assignMap[`${s.student_id}_all`] || null;
                const gvName = assign ? (assign.teacher_name || assign.teacher_code || '') : '';
                const assignId = assign ? assign.id : 0;
                const cid = s.class_id;
                const sid = s.student_id;

                // Busy = teachers assigned to OTHER students in this slot (not this student's own teacher)
                const busyIds = slotAssignedTeacherIds.filter(tid =>
                    !assign || tid !== assign.teacher_id
                );

                const gvHtml = assign
                    ? `<span class="tt-roster-gv-name">${gvName}</span>
                       <button class="tt-teacher-btn tt-teacher-edit" onclick="openAssignTeacherModal(${cid},${sid},${assignId},'',${JSON.stringify(busyIds)},${d})">Sửa</button>
                       <button class="tt-teacher-btn tt-teacher-del"  onclick="deleteStudentTeacher(${assignId})">Xóa</button>`
                    : `<button class="tt-add-teacher-btn" onclick="openAssignTeacherModal(${cid},${sid},0,'',${JSON.stringify(busyIds)},${d})">+ Thêm GV</button>`;

                return `<div class="tt-roster-student ${isPaid?'tt-roster-paid':'tt-roster-unpaid'}">
                    <div class="tt-roster-sv-info">
                        <span class="tt-roster-sv-badge ${isPaid?'paid':'unpaid'}">${isPaid?'TT':'–'}</span>
                        <span class="tt-roster-sv-name">${svName}</span>
                    </div>
                    <div class="tt-roster-gv-row">
                        <span class="tt-roster-gv-label">GV:</span>
                        ${gvHtml}
                    </div>
                </div>`;
            }).join('');

            if (!studentRows) return; // skip empty slots

            cellContent += `<div class="tt-roster-slot">
                <div class="tt-roster-header">
                    <span class="tt-roster-time">${slot.start.slice(0,5)} – ${slot.end.slice(0,5)}</span>
                    <div class="tt-roster-badges">
                        <span class="tt-rc-total">👥 ${validStudents.length}</span>
                        <span class="tt-rc-paid">${paidCount} TT</span>
                    </div>
                </div>
                <div class="tt-roster-students">${studentRows}</div>
            </div>`;
        });

        // Special events (EXAM/MAKEUP)
        spRows.forEach(sp => {
            const color = sp.schedule_type === 'EXAM' ? '#fde68a' : '#f59e0b';
            const label = sp.schedule_type === 'EXAM' ? (sp.exam_label || 'Lịch thi') : 'Học bù';
            cellContent += `<div class="tt-item tt-item-special" style="border-left:3px solid ${color};background:rgba(253,230,138,.1)">
                <div class="tt-item-time" style="color:${color}">${sp.start_time.slice(0,5)} – ${sp.end_time.slice(0,5)}</div>
                <div class="tt-special-label" style="color:${color}">${label}</div>
                ${sp.exam_supervisor ? `<div class="tt-info-line"><span class="tt-lbl">GV trông thi:</span> ${sp.exam_supervisor}</div>` : ''}
                <button class="tt-del-btn" onclick='deleteRecord("/schedules/${sp.id}","${label}",loadSchedules)'>✕</button>
            </div>`;
        });

        // "+" add button within class range
        if (inRange) {
            cellContent += `<button class="tt-add-special-btn" onclick="openAddExamMakeupModal(${parseInt(classId)},'${iso}')" title="Thêm lịch thi / học bù">+</button>`;
        }

        if (!cellContent) return `<td class="tt-cell tt-cell-closed"></td>`;
        return `<td class="tt-cell">${cellContent}</td>`;
    }

    // ── Build HTML ─────────────────────────────────────────────
    const pctDone = 0; // no per-student attendance in roster mode
    const weekLabel = `${_fmtDMY(weekStart)} – ${_fmtDMY(weekEnd)}`;

    const html = `
    <div class="tt-nav-bar">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <span class="tt-class-chip">${classCode}</span>
            <span style="font-weight:600;font-size:14px">${courseName}</span>
            <span class="tt-class-range">${startDate && endDate ? `${_fmtDMY(classStartDay)} – ${_fmtDMY(classEndDay)} · ${numSess} buổi` : ''}</span>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <button class="btn-ghost" onclick="_ttWeekOffset--;loadRosterTimetable('${classId}')">‹ Trở về</button>
            <button class="btn-ghost btn-active" onclick="_ttWeekOffset=0;loadRosterTimetable('${classId}')">Hiện tại</button>
            <span class="tt-week-label">${weekLabel}</span>
            <input type="date" class="tt-date-picker" onchange="(function(v){if(!v)return;const d=new Date(v);const today=new Date();today.setHours(0,0,0,0);_ttWeekOffset=Math.round((d-today)/604800000);loadRosterTimetable('${classId}')})(this.value)">
            <button class="btn-ghost" onclick="_ttWeekOffset++;loadRosterTimetable('${classId}')">Tiếp ›</button>
            <span style="font-size:12px;color:var(--text-muted)">👥 ${totalPaid} HV đã TT / ${numSess} tuần</span>
        </div>
    </div>
    ${weekBeforeClass ? `<div class="tt-out-of-range">Tuần này trước ngày khai giảng (${_fmtDMY(classStartDay)})</div>` : ''}
    ${weekAfterClass  ? `<div class="tt-out-of-range">Lớp đã kết thúc vào ${_fmtDMY(classEndDay)}</div>` : ''}
    <div class="tt-wrap">
        <table class="tt-table">
            <thead><tr>
                <th class="tt-head-cat">CA HỌC</th>
                ${dayOrder.map(d => {
                    const dt = weekDates[dayIdx[d]];
                    return `<th class="tt-head-day"><div class="tt-head-day-name">${dayLabels[d]}</div><div class="tt-head-day-date">${_fmtDM(dt)}</div></th>`;
                }).join('')}
            </tr></thead>
            <tbody>
                <tr>
                    <td class="tt-head-cat">Lịch học</td>
                    ${dayOrder.map(d => buildRosterCell(d)).join('')}
                </tr>
            </tbody>
        </table>
    </div>
    <div class="tt-legend">
        <span class="tt-legend-item"><span class="tt-legend-dot" style="background:#06b6d4"></span>Lịch học</span>
        <span class="tt-legend-item"><span class="tt-legend-dot" style="background:#fde68a"></span>Lịch thi</span>
        <span class="tt-legend-item"><span class="tt-legend-dot" style="background:#f59e0b"></span>Học bù</span>
        <span class="tt-legend-item">✅ Đã thanh toán &nbsp; ⏳ Chưa thanh toán</span>
    </div>`;

    setHtml('schedules-table', html);
}

async function openAddExamMakeupModal(preClassId, preDate) {
    const classId   = preClassId || document.getElementById('schedule-class-id')?.value;
    const studentId = document.getElementById('sched-student-id')?.value;
    if (!classId || classId === 'all') { showToast('Vui lòng chọn một lớp cụ thể trước', 'warning'); return; }

    const teachersRes = await fetchAPI('/teachers?limit=100');
    const teachers    = teachersRes?.ok ? (teachersRes.data.data || []) : [];
    const tvOpts = teachers.map(t => `<option value="${t.full_name||t.teacher_code}">${t.teacher_code} — ${t.full_name||''}</option>`).join('');
    const dateVal = preDate || _fmtISO(new Date());

    openModal('Thêm Lịch thi / Học bù', `
        ${field('Loại', `<select id="em-type" onchange="(function(v){
            document.getElementById('em-exam-fields').style.display=(v==='EXAM')?'block':'none';
            document.getElementById('em-makeup-fields').style.display=(v==='MAKEUP')?'block':'none';
        })(this.value)">
            <option value="EXAM">Lịch thi</option>
            <option value="MAKEUP">Học bù</option>
        </select>`)}
        <div id="em-exam-fields">
            ${field('Kỳ thi', `<select id="em-label">
                <option value="Thi giữa kỳ">Thi giữa kỳ</option>
                <option value="Thi cuối kỳ">Thi cuối kỳ</option>
            </select>`)}
            ${field('Giáo viên trông thi', `<select id="em-supervisor">
                <option value="">-- Chọn GV --</option>${tvOpts}
            </select>`)}
        </div>
        <div id="em-makeup-fields" style="display:none">
            ${field('Lý do học bù', `<input id="em-reason" type="text" placeholder="Ví dụ: Bù buổi 03/06">`)}
        </div>
        ${field('Ngày', `<input id="em-date" type="date" value="${dateVal}">`)}
        ${field('Giờ bắt đầu', `<input id="em-st" type="time" value="08:00">`)}
        ${field('Giờ kết thúc', `<input id="em-et" type="time" value="10:00">`)}
    `, 'Thêm lịch', async () => {
        const type  = val('em-type');
        const dateStr = val('em-date');
        if (!dateStr) { showToast('Vui lòng chọn ngày', 'warning'); return; }
        const d   = new Date(dateStr);
        // JS getDay: 0=Sun,1=Mon...6=Sat → our dow: 1=Sun,2=Mon...7=Sat
        const dow = d.getDay() === 0 ? 1 : d.getDay() + 1;
        const supervisor = type === 'EXAM' ? (val('em-supervisor') || '') : '';
        const label      = type === 'EXAM' ? val('em-label') : (val('em-reason') || 'Học bù');

        const p = {
            class_id:        parseInt(classId),
            student_id:      studentId ? parseInt(studentId) : null,
            day_of_week:     dow,
            specific_date:   dateStr,   // exact date — prevents repeat every week
            start_time:      val('em-st'),
            end_time:        val('em-et'),
            schedule_type:   type,
            exam_label:      label,
            exam_supervisor: supervisor,
        };
        const r = await fetchAPI('/schedules', { method:'POST', body: JSON.stringify(p) });
        if (r?.ok) { showToast('Đã thêm lịch!', 'success'); closeModal(); loadSchedules(); }
        else showToast('Lỗi: ' + (r?.data?.error||''), 'error');
    });
}

// ── inline teacher assignment modal (1:1 per student) ─────────
// dayOfWeek: số thứ (2=T2...7=T7,1=CN) — phân công riêng cho từng buổi
async function openAssignTeacherModal(classId, studentId, existAssignId, studentName, busyTeacherIds, dayOfWeek) {
    const busy  = Array.isArray(busyTeacherIds) ? busyTeacherIds : [];
    const res   = await fetchAPI('/teachers?limit=100');
    const teachers = res?.ok ? (res.data.data || []) : [];
    const dayLabels = {1:'Chủ nhật',2:'Thứ 2',3:'Thứ 3',4:'Thứ 4',5:'Thứ 5',6:'Thứ 6',7:'Thứ 7'};
    const dayLabel  = dayOfWeek ? dayLabels[dayOfWeek] || '' : '';

    const opts = teachers.map(t => {
        const isBusy = busy.includes(String(t.id)) || busy.includes(t.id);
        if (isBusy) return `<option value="${t.id}" disabled style="color:#666">${t.teacher_code} — ${t.full_name || t.teacher_code} (Đang bận)</option>`;
        return `<option value="${t.id}">${t.teacher_code} — ${t.full_name || t.teacher_code}</option>`;
    }).join('');

    const dayNote = dayLabel ? `<p style="font-size:12px;color:#06b6d4;margin:0 0 10px">📅 Buổi: <strong>${dayLabel}</strong></p>` : '';
    const title   = existAssignId ? `Đổi GV${dayLabel?' — '+dayLabel:''}` : `Phân công GV${dayLabel?' — '+dayLabel:''}`;

    openModal(title, `
        ${dayNote}
        ${field('Chọn giáo viên', `<select id="gv-sel" style="width:100%;background:#1e2030;color:#e2e8f0;border:1px solid var(--border);border-radius:8px;padding:8px 12px;font-size:14px">
            <option value="">— Chọn giáo viên —</option>${opts}
        </select>`)}
    `, existAssignId ? 'Lưu thay đổi' : 'Phân công', async () => {
        const teacherId = document.getElementById('gv-sel')?.value;
        if (!teacherId) { showToast('Vui lòng chọn giáo viên', 'warning'); return; }
        // Always POST — backend deletes existing record for (student+class+day) then inserts fresh
        // This ensures per-day assignments never bleed into other days
        const body = JSON.stringify({ teacher_id: teacherId, class_id: classId, student_id: studentId, day_of_week: dayOfWeek || null });
        const r = await fetchAPI('/teacher-assignments', { method:'POST', body });
        if (r?.ok) { showToast('Phân công thành công!', 'success'); closeModal(); loadSchedules(); }
        else showToast('Lỗi: ' + (r?.data?.error || ''), 'error');
    });
}

async function deleteStudentTeacher(assignId) {
    if (!confirm('Xóa giáo viên của học viên này?')) return;
    const r = await fetchAPI(`/teacher-assignments/${assignId}`, { method:'DELETE' });
    if (r?.ok) { showToast('Đã xóa phân công', 'success'); loadSchedules(); }
    else showToast('Lỗi: ' + (r?.data?.error || ''), 'error');
}


// ============================================================
// ENROLLMENTS
// ============================================================
async function loadEnrollments() {
    loading('enrollments-table');
    const filterPay    = document.getElementById('enroll-filter-pay')?.value    || '';
    const filterStatus = document.getElementById('enroll-filter-status')?.value || '';
    const res = await fetchAPI('/enrollments');
    if (!res?.ok) { errState('enrollments-table'); return; }

    let rows = res.data.data || [];
    if (filterPay)    rows = rows.filter(r => r.payment_status === filterPay);
    if (filterStatus) rows = rows.filter(r => r.status === filterStatus);

    if (rows.length === 0) {
        setHtml('enrollments-table', '<p style="color:var(--text-muted);text-align:center;padding:32px 0">Không có dữ liệu phù hợp</p>');
        return;
    }

    setHtml('enrollments-table', makeTable([
        { key: 'id',             label: 'ID' },
        { key: 'student_code',   label: 'Mã HV' },
        { key: 'full_name',      label: 'Họ tên' },
        { key: 'class_code',     label: 'Lớp',     render: r => r.class_code || '—' },
        { key: 'course_name',    label: 'Khóa học', render: r => r.course_name || '—' },
        { key: 'enrollment_date',label: 'Ngày đăng ký', render: r => fmtDate(r.enrollment_date) },
        { key: 'status',         label: 'Trạng thái ĐK', render: r => statusBadge(r.status) },
        { key: 'tuition_amount', label: 'Học phí', render: r => fmtCurrency(r.tuition_amount || 0) },
        { key: 'payment_status', label: 'Thanh toán', render: r => statusBadge(r.payment_status || 'UNPAID') },
        { key: 'payment_date',   label: 'Ngày TT',    render: r => r.payment_date ? fmtDate(r.payment_date) : '—' },
        { key: 'payment_method', label: 'Hình thức',  render: r => r.payment_method || '—' },
        { key: '_act', label: '', render: r => {
            const isPaid = r.payment_status === 'PAID' || r.payment_status === 'COMPLETED';
            const payBtn = !isPaid
                ? `<button class="btn-pay" onclick='quickPayTuition(${r.tuition_id || 0},${r.id},${r.class_id || 0},loadEnrollments)'>Thanh toán</button>`
                : '';
            return `<td class="td-actions">
                ${payBtn}
                <button class="btn-edit" onclick='openEditEnrollmentModal(${JSON.stringify(r)})'>Sửa</button>
                <button class="btn-delete" onclick='deleteRecord("/enrollments/${r.id}","đăng ký #${r.id}",loadEnrollments)'>Xóa</button>
            </td>`;
        }},
    ], rows));
}

function cancelPayment(enrollmentId, tuitionId, reloadFn) {
    const overlay = document.createElement('div');
    overlay.className = 'confirm-overlay';
    overlay.innerHTML = `
        <div class="confirm-box">
            <div class="confirm-icon">↩️</div>
            <div class="confirm-title">Hủy thanh toán?</div>
            <div class="confirm-msg">Xác nhận hoàn tiền và chuyển trạng thái về <strong>Chưa thanh toán</strong>.<br>
                <span style="color:#f87171;font-size:12px">Lịch học liên quan sẽ không bị xóa.</span>
            </div>
            <div class="confirm-actions">
                <button class="confirm-cancel" id="cfm-ref-no">Hủy bỏ</button>
                <button class="confirm-ok" id="cfm-ref-ok" style="background:#f59e0b">Xác nhận hoàn tiền</button>
            </div>
        </div>`;
    document.body.appendChild(overlay);
    document.getElementById('cfm-ref-no').onclick = () => overlay.remove();
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.remove(); });
    document.getElementById('cfm-ref-ok').onclick = async () => {
        overlay.remove();
        const r1 = await fetchAPI(`/enrollments/${enrollmentId}`, {
            method: 'PUT',
            body: JSON.stringify({ payment_status: 'REFUNDED' }),
        });
        if (r1?.ok) { showToast('Đã hủy thanh toán, chuyển sang Hoàn tiền', 'warning'); reloadFn(); }
        else showToast('Lỗi: ' + (r1?.data?.error || ''), 'error');
    };
}

// Tạo picker lịch học: mỗi thứ có khung giờ riêng
function addHours(timeStr, h) {
    const [hh, mm] = timeStr.split(':').map(Number);
    const totalMin = hh * 60 + mm + h * 60;
    const nh = Math.floor(((totalMin % 1440) + 1440) % 1440 / 60);
    const nm = ((totalMin % 60) + 60) % 60;
    return `${String(nh).padStart(2,'0')}:${String(nm).padStart(2,'0')}`;
}

function buildDaySchedulePicker() {
    const days = [
        {v:2,l:'Thứ 2'},{v:3,l:'Thứ 3'},{v:4,l:'Thứ 4'},
        {v:5,l:'Thứ 5'},{v:6,l:'Thứ 6'},{v:7,l:'Thứ 7'},{v:1,l:'Chủ nhật'},
    ];
    return `
    <div class="dsp-wrap">
        <div class="dsp-pills" id="dsp-pills">
            ${days.map(d => `
            <button type="button" class="dsp-pill" id="dpill-${d.v}" data-day="${d.v}"
                onclick="dspToggle(${d.v},'${d.l}')">${d.l}</button>`).join('')}
        </div>
        <div class="dsp-selected" id="dsp-selected"></div>
        ${days.map(d => `<input type="hidden" name="sched_day" value="${d.v}" id="dchk-${d.v}" disabled>`).join('')}
    </div>`;
}

function dspToggle(dayVal, dayLabel) {
    const selectedWrap = document.getElementById('dsp-selected');
    const pill = document.getElementById(`dpill-${dayVal}`);
    const hidden = document.getElementById(`dchk-${dayVal}`);
    const existRow = document.getElementById(`drow-${dayVal}`);

    if (existRow) {
        // Deselect: remove row, reset pill
        existRow.remove();
        pill.classList.remove('active');
        hidden.disabled = true;
        return;
    }

    const currentSelected = selectedWrap.querySelectorAll('.dsp-time-row').length;
    if (currentSelected >= 2) {
        window.showToast && showToast('Chỉ được chọn tối đa 2 ngày học / tuần', 'warning');
        return;
    }

    // Add time row
    pill.classList.add('active');
    hidden.disabled = false;
    const defStart = '17:00';
    const defEnd   = '19:00';
    const row = document.createElement('div');
    row.className = 'dsp-time-row';
    row.id = `drow-${dayVal}`;
    row.innerHTML = `
        <span class="dsp-day-label">${dayLabel}</span>
        <input type="time" id="ds-${dayVal}" value="${defStart}" class="day-time-input"
            oninput="document.getElementById('de-${dayVal}').value=addHours(this.value,2)">
        <span class="day-time-sep">→</span>
        <input type="time" id="de-${dayVal}" value="${defEnd}" class="day-time-input"
            oninput="document.getElementById('ds-${dayVal}').value=addHours(this.value,-2)">
        <button type="button" class="dsp-remove" onclick="dspToggle(${dayVal},'${dayLabel}')">✕</button>
    `;
    selectedWrap.appendChild(row);
}

function getScheduleData() {
    return [1,2,3,4,5,6,7]
        .filter(d => {
            const row = document.getElementById(`drow-${d}`);
            return !!row;
        })
        .map(d => ({
            day:   d,
            start: document.getElementById(`ds-${d}`)?.value || '17:00',
            end:   document.getElementById(`de-${d}`)?.value || '19:00',
        }))
        .filter(item => item.start < item.end);
}

async function quickPayTuition(tuitionId, enrollmentId, classId, reloadFn, studentId) {
    const methods = [['CASH','Tiền mặt'],['BANK_TRANSFER','Chuyển khoản'],['CARD','Thẻ']];

    openModal('Thanh toán & Lịch học', `
        <div class="pay-confirmed-banner">💳 Xác nhận thanh toán học phí</div>
        ${field('Hình thức thanh toán', `<select id="qp-method">${methods.map(([v,l]) => `<option value="${v}">${l}</option>`).join('')}</select>`)}
        <div class="schedule-picker-section">
            <div class="schedule-picker-title">📅 Lịch học của học viên</div>
            <div class="schedule-picker-hint">
                Chọn đúng <strong>2 ngày</strong> học trong tuần — mỗi buổi cố định <strong>2 tiếng</strong>.
            </div>
            ${buildDaySchedulePicker()}
            <p style="font-size:11px;color:var(--text-faint);margin-top:10px">
                Sau khi xác nhận, admin sẽ phân công giáo viên phù hợp cho từng buổi.
            </p>
        </div>
    `, 'Xác nhận thanh toán', async () => {
        const method    = val('qp-method');
        const schedules = getScheduleData();

        if (schedules.length === 0) {
            showToast('Vui lòng chọn 2 ngày học trong tuần', 'warning'); return;
        }
        if (schedules.length < 2) {
            showToast('Cần chọn đủ 2 ngày học / tuần', 'warning'); return;
        }
        const invalidDay = schedules.find(s => s.start >= s.end);
        if (invalidDay) {
            showToast('Giờ kết thúc phải sau giờ bắt đầu', 'warning'); return;
        }

        const r1 = await fetchAPI(`/enrollments/${enrollmentId}`, {
            method: 'PUT',
            body: JSON.stringify({ payment_status: 'PAID', payment_method: method }),
        });

        if (tuitionId) {
            await fetchAPI('/tuitions/pay', {
                method: 'POST',
                body: JSON.stringify({ payment_id: tuitionId, payment_method: method }),
            });
        }

        if (r1?.ok) {
            if (classId && schedules.length > 0) {
                await Promise.all(schedules.map(s =>
                    fetchAPI('/schedules', {
                        method: 'POST',
                        body: JSON.stringify({
                            class_id:      classId,
                            student_id:    studentId || null,
                            day_of_week:   s.day,
                            start_time:    s.start,
                            end_time:      s.end,
                            schedule_type: 'REGULAR',
                        }),
                    })
                ));
                const dayNames = {1:'Chủ nhật',2:'Thứ 2',3:'Thứ 3',4:'Thứ 4',5:'Thứ 5',6:'Thứ 6',7:'Thứ 7'};
                const summary  = schedules.map(s => `${dayNames[s.day]} (${s.start}–${s.end})`).join(', ');
                showToast(`Thanh toán thành công! Lịch: ${summary}. Vui lòng phân công giáo viên.`, 'success');
            } else {
                showToast('Thanh toán thành công!', 'success');
            }
            closeModal();
            reloadFn();
        } else {
            showToast('Lỗi: ' + (r1?.data?.error || ''), 'error');
        }
    }, true);
}

async function openAddEnrollmentModal() {
    openModal('modal_add_enrollment', `<div class="loading-placeholder" style="padding:32px 0">${t('loading')}</div>`, 'btn_add_enrollment', null);

    const [studentRes, classRes] = await Promise.all([
        fetchAPI('/students?limit=500'),
        fetchAPI('/classes?limit=200'),
    ]);

    const students = studentRes?.ok ? (studentRes.data.data || []) : [];
    const classes  = classRes?.ok  ? (classRes.data.data  || []) : [];

    const studentOpts = students.length
        ? students.map(s => `<option value="${s.id}">[${s.student_code}] ${s.full_name}</option>`).join('')
        : `<option value="" disabled>Không có học viên</option>`;

    const classOpts = classes.length
        ? classes.map(c => `<option value="${c.id}">[${c.class_code}] ${c.course_name || ''} — ${c.status}</option>`).join('')
        : `<option value="" disabled>Không có lớp học</option>`;

    document.getElementById('modal-body').innerHTML = `
        ${field('lbl_student_id', `<select id="f-esi"><option value="">-- Chọn học viên --</option>${studentOpts}</select>`)}
        ${field('lbl_class_id_f', `<select id="f-eci"><option value="">-- Chọn lớp học --</option>${classOpts}</select>`)}
        ${field('lbl_payment_status', `<select id="f-eps">
            <option value="UNPAID">Chưa thanh toán</option>
            <option value="PAID">Đã thanh toán</option>
        </select>`)}
        ${field('lbl_status', `<select id="f-est">
            <option value="ACTIVE">Đang học</option>
            <option value="DROPPED">Đã nghỉ</option>
            <option value="COMPLETED">Hoàn thành</option>
        </select>`)}
    `;

    _modalCb = async () => {
        const p = {
            student_id:     ival('f-esi'),
            class_id:       ival('f-eci'),
            payment_status: val('f-eps'),
            status:         val('f-est'),
        };
        if (!p.student_id || !p.class_id) { showToast(t('toast_student_req'), 'warning'); return; }
        const res = await fetchAPI('/enrollments', { method: 'POST', body: JSON.stringify(p) });
        if (res?.ok) { showToast(t('toast_enroll_ok'), 'success'); closeModal(); loadEnrollments(); }
        else showToast(t('toast_enroll_err') + ': ' + (res?.data?.error || ''), 'error');
    };
}

async function openEditEnrollmentModal(r) {
    openModal('modal_edit_enrollment', `<div class="loading-placeholder" style="padding:32px 0">${t('loading')}</div>`, 'btn_save', null);
    document.getElementById('modal-title').textContent = `Sửa đăng ký #${r.id}`;

    const [studentRes, classRes] = await Promise.all([
        fetchAPI('/students?limit=500'),
        fetchAPI('/classes?limit=200'),
    ]);

    const students = studentRes?.ok ? (studentRes.data.data || []) : [];
    const classes  = classRes?.ok  ? (classRes.data.data  || []) : [];

    const studentOpts = students.map(s =>
        `<option value="${s.id}" ${s.id == r.student_id ? 'selected' : ''}>[${s.student_code}] ${s.full_name}</option>`
    ).join('');

    const classOpts = classes.map(c =>
        `<option value="${c.id}" ${c.id == r.class_id ? 'selected' : ''}>[${c.class_code}] ${c.course_name || ''} — ${c.status}</option>`
    ).join('');

    const payStatuses = [['UNPAID','Chưa thanh toán'],['PAID','Đã thanh toán'],['REFUNDED','Hoàn tiền']];
    const enrStatuses = [['ACTIVE','Đang học'],['DROPPED','Đã nghỉ'],['COMPLETED','Hoàn thành']];

    document.getElementById('modal-body').innerHTML = `
        ${field('lbl_student_id',     `<select id="fe-esi"><option value="">-- Chọn học viên --</option>${studentOpts}</select>`)}
        ${field('lbl_class_id_f',     `<select id="fe-eci"><option value="">-- Chọn lớp học --</option>${classOpts}</select>`)}
        ${field('lbl_payment_status', `<select id="fe-eps">${payStatuses.map(([v,l]) => `<option value="${v}" ${v===r.payment_status?'selected':''}>${l}</option>`).join('')}</select>`)}
        ${field('lbl_status',         `<select id="fe-est">${enrStatuses.map(([v,l]) => `<option value="${v}" ${v===r.status?'selected':''}>${l}</option>`).join('')}</select>`)}
    `;

    _modalCb = async () => {
        const body = {
            student_id:     ival('fe-esi') || null,
            class_id:       ival('fe-eci') || null,
            payment_status: val('fe-eps'),
            status:         val('fe-est'),
        };
        const res = await fetchAPI(`/enrollments/${r.id}`, { method: 'PUT', body: JSON.stringify(body) });
        if (res?.ok) { showToast('Cập nhật đăng ký thành công', 'success'); closeModal(); loadEnrollments(); }
        else showToast('Lỗi: ' + (res?.data?.error || ''), 'error');
    };
}

// ============================================================
// ATTENDANCE
// ============================================================
async function loadAttendance() {
    const classId = document.getElementById('att-class-id')?.value;
    if (!classId) { showToast(t('toast_class_id_req'), 'warning'); return; }
    const date = document.getElementById('att-date')?.value || new Date().toISOString().split('T')[0];
    loading('attendance-table');
    const res = await fetchAPI(`/attendance?class_id=${classId}&date=${date}`);
    if (!res?.ok) { errState('attendance-table'); return; }
    setHtml('attendance-table', makeTable([
        { key: 'id',                label: t('col_id') },
        { key: 'student_code',      label: t('col_student_code') },
        { key: 'full_name',         label: t('col_full_name') },
        { key: 'attendance_date',   label: t('col_att_date'), render: r => fmtDate(r.attendance_date) },
        { key: 'attendance_status', label: t('col_att_status'), render: r => statusBadge(r.attendance_status) },
        { key: 'note',              label: t('col_note'), render: r => r.note || '—' },
        { key: '_act', label: '', render: r => `<td class="td-actions"><button class="btn-delete" onclick='deleteRecord("/attendance/${r.id}","điểm danh #${r.id}",loadAttendance)'>Xóa</button></td>` },
    ], res.data.data));
}

function openAddAttendanceModal() {
    const today = new Date().toISOString().split('T')[0];
    openModal('modal_add_attendance', `
        ${field('lbl_class_id_f', `<input id="f-xci" type="number" min="1" placeholder="ID">`)}
        ${field('lbl_student_id', `<input id="f-xsi" type="number" min="1" placeholder="ID">`)}
        ${field('lbl_att_date',   `<input id="f-xd" type="date" value="${today}">`)}
        ${field('lbl_att_status', `<select id="f-xst"><option value="PRESENT">${t('opt_present')}</option><option value="ABSENT">${t('opt_absent')}</option></select>`)}
        ${field('lbl_att_note',   `<input id="f-xn" type="text" placeholder="${t('col_note')}">`)}
    `, 'btn_add_attendance', async () => {
        const p = { class_id: ival('f-xci'), student_id: ival('f-xsi'), attendance_date: val('f-xd'), attendance_status: val('f-xst'), note: val('f-xn') };
        if (!p.class_id || !p.student_id || !p.attendance_date) { showToast(t('toast_required'), 'warning'); return; }
        const res = await fetchAPI('/attendance', { method: 'POST', body: JSON.stringify(p) });
        if (res?.ok) { showToast(t('toast_att_ok'), 'success'); closeModal(); loadAttendance(); }
        else showToast(t('toast_att_err') + ': ' + (res?.data?.error || ''), 'error');
    });
}

// ============================================================
// TUITIONS
// ============================================================
async function loadTuitions(byStudent = false) {
    loading('tuitions-table');
    const sid = byStudent ? document.getElementById('tuition-student-id')?.value : '';
    const endpoint = sid ? `/tuitions?student_id=${sid}` : '/tuitions';
    const res = await fetchAPI(endpoint);
    if (!res?.ok) { errState('tuitions-table', 'error_no_perm'); return; }
    setHtml('tuitions-table', makeTable([
        { key: 'id',             label: t('col_id') },
        { key: 'student_code',   label: t('col_student_code') },
        { key: 'full_name',      label: t('col_full_name') },
        { key: 'amount',         label: t('col_amount'), render: r => fmtCurrency(r.amount) },
        { key: 'payment_date',   label: t('col_payment_date'), render: r => fmtDate(r.payment_date) },
        { key: 'payment_method', label: t('col_payment_method'), render: r => r.payment_method || '—' },
        { key: 'payment_status', label: t('col_payment_status'), render: r => statusBadge(r.payment_status) },
        { key: '_act', label: '', render: r => `<td class="td-actions">
            <button class="btn-delete" onclick='deleteRecord("/tuitions/${r.id}","học phí #${r.id}",loadTuitions)'>Xóa</button>
        </td>` },
    ], res.data.data));
}

function openPayTuitionModal() {
    openModal('modal_pay_tuition', `
        ${field('lbl_payment_id',     `<input id="f-pid" type="number" min="1" placeholder="ID">`)}
        ${field('lbl_payment_method', `<select id="f-pmt"><option value="CASH">${t('opt_cash')}</option><option value="BANK_TRANSFER">${t('opt_bank')}</option><option value="CARD">${t('opt_card')}</option></select>`)}
    `, 'btn_pay_tuition', async () => {
        const p = { payment_id: ival('f-pid'), payment_method: val('f-pmt') };
        if (!p.payment_id) { showToast(t('toast_payment_req'), 'warning'); return; }
        const res = await fetchAPI('/tuitions/pay', { method: 'POST', body: JSON.stringify(p) });
        if (res?.ok) { showToast(t('toast_pay_ok'), 'success'); closeModal(); loadTuitions(); }
        else showToast(t('toast_pay_err') + ': ' + (res?.data?.error || ''), 'error');
    });
}

// ============================================================
// REPORTS
// ============================================================
async function loadReports() {
    await Promise.all([loadRevenueReport(), loadStudentsReport()]);
    loadTeacherHours();
}

async function loadRevenueReport() {
    loading('revenue-report-table');
    const res = await fetchAPI('/reports/revenue');
    if (!res?.ok) { errState('revenue-report-table', 'error_no_perm'); return; }
    setHtml('revenue-report-table', makeTable([
        { key: 'month',         label: t('col_month') },
        { key: 'total_revenue', label: t('col_revenue'), render: r => fmtCurrency(r.total_revenue) },
    ], res.data.data));
}

async function loadTeacherHours() {
    loading('teacher-hours-table');
    const month = document.getElementById('teacher-hours-month')?.value || '';
    const res = await fetchAPI('/reports/teacher-hours' + (month ? `?month=${month}` : ''));
    if (!res?.ok) { errState('teacher-hours-table', 'error_no_perm'); return; }
    setHtml('teacher-hours-table', makeTable([
        { key: 'teacher_code',   label: t('col_teacher_code') },
        { key: 'full_name',      label: t('col_teacher_name') },
        { key: 'total_sessions', label: t('col_sessions') },
        { key: 'actual_hours',   label: t('col_actual_hours'), render: r => parseFloat(r.actual_hours||0).toFixed(1)+'h' },
        { key: 'workload_status',label: t('col_workload'), render: r => {
            const s = r.workload_status || '';
            if (s.includes('VƯỢT') || s.includes('EXCEED')) return mkBadge(t('workload_over'),  'active');
            if (s.includes('ĐẠT')  || s.includes('MEETS'))  return mkBadge(t('workload_meet'),  'ongoing');
            if (s.includes('THIẾU')|| s.includes('BELOW'))  return mkBadge(t('workload_under'), 'unpaid');
            return mkBadge(s||'—', 'inactive');
        }},
    ], res.data.data));
}

async function loadStudentsReport() {
    loading('students-report-table');
    const res = await fetchAPI('/reports/students');
    if (!res?.ok) { errState('students-report-table', 'error_no_perm'); return; }
    const statusRows = res.data.student_status_count || [];
    const total = statusRows.reduce((s, r) => s + parseInt(r.count || 0), 0);
    setHtml('students-report-table',
        `<p style="font-size:13px;color:var(--text-muted);margin-bottom:12px;">
            ${t('total_students')}: <strong style="color:var(--text);font-size:18px">${total}</strong> ${t('unit_students')}
        </p>` +
        makeTable([
            { key: 'status', label: t('col_status'), render: r => statusBadge(r.status) },
            { key: 'count',  label: t('col_sessions').replace('Số buổi','').trim() || '#' },
        ], statusRows)
    );
}

// ============================================================
// NOTIFICATIONS
// ============================================================
async function loadNotifications() {
    setHtml('notifications-list', `<div class="loading-placeholder">${t('loading_notif')}</div>`);
    const res = await fetchAPI('/notifications');
    if (!res?.ok) { errState('notifications-list', 'error_load'); return; }
    const notifs = res.data.data || [];

    const unread = notifs.filter(n => !n.is_read).length;
    const badgeEl = document.getElementById('notif-badge');
    if (badgeEl) { badgeEl.textContent = unread; badgeEl.style.display = unread > 0 ? 'inline-block' : 'none'; }

    if (notifs.length === 0) {
        setHtml('notifications-list', `<div class="empty-state"><div class="empty-icon">🔔</div><p>${t('no_notif')}</p></div>`);
        return;
    }
    setHtml('notifications-list', notifs.map(n => `
        <div class="notif-item ${n.is_read ? '' : 'unread'}" data-id="${n.id}" onclick="markNotifRead(${n.id}, this)">
            <div class="notif-icon">${n.is_read ? '🔔' : '🔴'}</div>
            <div class="notif-content">
                <div class="notif-title">${n.title || t('nav_notifications')}</div>
                <div class="notif-body">${n.content || ''}</div>
                <div class="notif-time">${fmtDate(n.created_at)}</div>
            </div>
        </div>
    `).join(''));
}

async function markNotifRead(notificationId, el) {
    if (!el.classList.contains('unread')) return;
    const res = await fetchAPI('/notifications/read', { method: 'POST', body: JSON.stringify({ notification_id: notificationId }) });
    if (res?.ok) {
        el.classList.remove('unread');
        el.querySelector('.notif-icon').textContent = '🔔';
        const badgeEl = document.getElementById('notif-badge');
        if (badgeEl) {
            const next = Math.max(0, (parseInt(badgeEl.textContent) || 0) - 1);
            badgeEl.textContent = next;
            badgeEl.style.display = next > 0 ? 'inline-block' : 'none';
        }
    }
}
