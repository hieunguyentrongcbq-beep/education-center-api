/* ============================================================
   i18n.js — Hệ thống đa ngôn ngữ (Tiếng Việt / English)
   ============================================================ */

const TRANSLATIONS = {
    vi: {
        // --- Sidebar nav sections ---
        nav_overview:         'Tổng quan',
        nav_academic:         'Quản lý học vụ',
        nav_finance:          'Tài chính',
        nav_reports_section:  'Báo cáo',
        nav_system:           'Hệ thống',

        // --- Sidebar nav items ---
        nav_dashboard:        'Dashboard',
        nav_courses:          'Khóa học',
        nav_classes:          'Lớp học',
        nav_teachers:         'Giáo viên',
        nav_assignments:      'Phân công GV',
        nav_schedules:        'Lịch học',
        nav_enrollments:      'Đăng ký học',
        nav_attendance:       'Điểm danh',
        nav_tuitions:         'Học phí',
        nav_reports:          'Thống kê & Báo cáo',
        nav_notifications:    'Thông báo',

        // --- Page titles (breadcrumb) ---
        page_dashboard:       'Dashboard',
        page_courses:         'Quản lý Khóa học',
        page_classes:         'Quản lý Lớp học',
        page_teachers:        'Quản lý Giáo viên',
        page_assignments:     'Phân công Giáo viên',
        page_schedules:       'Quản lý Lịch học',
        page_enrollments:     'Quản lý Đăng ký học',
        page_attendance:      'Điểm danh',
        page_tuitions:        'Quản lý Học phí',
        page_reports:         'Thống kê & Báo cáo',
        page_notifications:   'Thông báo',

        // --- Dashboard stat labels ---
        stat_courses:         'Khóa học',
        stat_classes:         'Lớp học',
        stat_teachers:        'Giáo viên',
        stat_revenue:         'Doanh thu',
        chart_revenue_title:  '📈 Doanh thu theo tháng',
        chart_fillrate_title: '🏫 Tỉ lệ lấp đầy lớp học',
        recent_classes_title: '🏫 Danh sách lớp học gần đây',
        no_data_chart:        'Chưa có dữ liệu doanh thu',

        // --- Common buttons ---
        btn_add:              '+ Thêm',
        btn_add_course:       '+ Thêm khóa học',
        btn_add_class:        '+ Thêm lớp học',
        btn_add_teacher:      '+ Thêm giáo viên',
        btn_add_assignment:   '+ Phân công',
        btn_add_schedule:     '+ Tạo lịch',
        btn_add_enrollment:   '+ Đăng ký',
        btn_add_attendance:   '+ Ghi điểm danh',
        btn_pay_tuition:      '💳 Xử lý thanh toán',
        btn_view:             '🔍 Xem',
        btn_view_schedule:    '🔍 Xem lịch',
        btn_export_csv:       '⬇ Xuất CSV',
        btn_logout:           'Đăng xuất',

        // --- Filter labels ---
        filter_class_id:      'Lớp học (ID):',
        filter_class_id_s:    'Lớp (ID):',
        filter_date:          'Ngày:',
        filter_student_id:    'Học viên (ID):',
        filter_month:         'Tháng:',
        placeholder_class_id: 'Nhập ID lớp...',
        placeholder_student:  'ID học viên...',
        search_placeholder:   'Tìm kiếm...',

        // --- Page headings ---
        heading_courses:      '📚 Quản lý Khóa học',
        heading_classes:      '🏫 Quản lý Lớp học',
        heading_teachers:     '👨‍🏫 Quản lý Giáo viên',
        heading_assignments:  '📋 Phân công Giáo viên',
        heading_schedules:    '📅 Quản lý Lịch học',
        heading_enrollments:  '📝 Quản lý Đăng ký học',
        heading_attendance:   '✅ Điểm danh',
        heading_tuitions:     '💰 Quản lý Học phí',
        heading_reports:      '📈 Thống kê & Báo cáo',
        heading_notifications:'🔔 Thông báo',

        // --- Table columns ---
        col_id:               'ID',
        col_code:             'Mã',
        col_course_code:      'Mã khóa học',
        col_course_name:      'Tên khóa học',
        col_class_code:       'Mã lớp',
        col_teacher_code:     'Mã GV',
        col_teacher_name:     'Họ tên',
        col_teacher_incharge: 'GV phụ trách',
        col_classroom:        'Phòng học',
        col_max_students:     'Sĩ số tối đa',
        col_duration_weeks:   'Số tuần học',
        col_tuition_fee:      'Học phí',
        col_start_date:       'Bắt đầu',
        col_end_date:         'Kết thúc',
        col_status:           'Trạng thái',
        col_description:      'Mô tả',
        col_email:            'Email',
        col_phone:            'SĐT',
        col_specialization:   'Chuyên môn',
        col_hire_date:        'Ngày tuyển dụng',
        col_student_code:     'Mã HV',
        col_full_name:        'Họ tên',
        col_class_id:         'ID Lớp',
        col_enroll_date:      'Ngày đăng ký',
        col_payment_status:   'Thanh toán',
        col_att_date:         'Ngày',
        col_att_status:       'Trạng thái',
        col_note:             'Ghi chú',
        col_amount:           'Số tiền',
        col_payment_date:     'Ngày TT',
        col_payment_method:   'Hình thức',
        col_month:            'Tháng',
        col_revenue:          'Doanh thu',
        col_sessions:         'Số buổi',
        col_actual_hours:     'Giờ thực',
        col_workload:         'Mức độ',
        col_day_of_week:      'Ngày trong tuần',
        col_time_start:       'Giờ bắt đầu',
        col_time_end:         'Giờ kết thúc',
        col_sched_type:       'Loại lịch',
        col_assign_date:      'Ngày phân công',
        col_teacher_id:       'ID GV',

        // --- Days of week ---
        day_sun: 'Chủ nhật', day_mon: 'Thứ 2', day_tue: 'Thứ 3',
        day_wed: 'Thứ 4',   day_thu: 'Thứ 5', day_fri: 'Thứ 6', day_sat: 'Thứ 7',

        // --- Status badges ---
        status_active:    'Hoạt động',
        status_inactive:  'Ngưng',
        status_upcoming:  'Sắp mở',
        status_ongoing:   'Đang diễn ra',
        status_completed: 'Hoàn thành',
        status_paid:      'Đã TT',
        status_unpaid:    'Chưa TT',
        status_pending:   'Chờ xử lý',
        status_present:   'Có mặt',
        status_absent:    'Vắng',
        status_confirmed: 'Xác nhận',
        status_cancelled: 'Đã hủy',

        // --- Modal titles ---
        modal_add_course:      '➕ Thêm Khóa học',
        modal_add_class:       '➕ Thêm Lớp học',
        modal_add_teacher:     '➕ Thêm Giáo viên',
        modal_add_assignment:  '📋 Phân công Giáo viên',
        modal_add_schedule:    '📅 Tạo Lịch học',
        modal_add_enrollment:  '📝 Đăng ký Học',
        modal_add_attendance:  '✅ Ghi Điểm danh',
        modal_pay_tuition:     '💳 Xử lý Thanh toán',
        btn_modal_cancel:      'Hủy',
        btn_modal_confirm:     'Xác nhận',
        btn_save:              'Lưu thay đổi',
        modal_edit_course:     'Sửa khóa học',
        modal_edit_class:      'Sửa lớp học',
        modal_edit_teacher:    'Sửa giáo viên',
        modal_edit_enrollment:  'Sửa đăng ký học',
        modal_edit_assignment:  'Sửa phân công',
        lbl_scenario:           'Tên kịch bản',
        nav_students:           'Học viên',
        btn_add_student:        '+ Thêm học viên',
        lbl_full_name_req:      'Họ và tên *',
        lbl_email_req:          'Email *',
        lbl_phone:              'Số điện thoại',
        lbl_payment_status:    'Tình trạng thanh toán',

        // --- Modal form labels ---
        lbl_course_code:     'Mã khóa học *',
        lbl_course_name:     'Tên khóa học *',
        lbl_duration_weeks:  'Số tuần học *',
        lbl_tuition_fee_f:   'Học phí (VNĐ) *',
        lbl_description:     'Mô tả',
        lbl_status:          'Trạng thái',
        lbl_class_code:      'Mã lớp *',
        lbl_course_id:       'ID Khóa học *',
        lbl_teacher_id_opt:  'ID Giáo viên',
        lbl_classroom_id:    'ID Phòng học',
        lbl_semester_id:     'ID Học kỳ',
        lbl_max_students:    'Sĩ số tối đa *',
        lbl_start_date:      'Ngày bắt đầu *',
        lbl_end_date:        'Ngày kết thúc *',
        lbl_user_id:         'ID Người dùng *',
        lbl_teacher_code:    'Mã giáo viên *',
        lbl_specialization:  'Chuyên môn *',
        lbl_hire_date:       'Ngày tuyển dụng',
        lbl_teacher_id_req:  'ID Giáo viên *',
        lbl_class_id_req:    'ID Lớp học *',
        lbl_scenario:        'Tên kịch bản',
        lbl_day_of_week:     'Ngày trong tuần *',
        lbl_time_start:      'Giờ bắt đầu *',
        lbl_time_end:        'Giờ kết thúc *',
        lbl_sched_type:      'Loại lịch',
        lbl_student_id:      'ID Học viên *',
        lbl_class_id_f:      'ID Lớp học *',
        lbl_att_date:        'Ngày *',
        lbl_att_status:      'Trạng thái *',
        lbl_att_note:        'Ghi chú',
        lbl_payment_id:      'ID Khoản học phí *',
        lbl_payment_method:  'Hình thức thanh toán *',

        // --- Reports ---
        report_revenue_title:  '💰 Doanh thu theo tháng',
        report_hours_title:    '👨‍🏫 Giờ dạy giáo viên',
        report_students_title: '🎓 Thống kê học viên',
        total_students:        'Tổng',
        unit_students:         'học viên',

        // --- State messages ---
        loading:         'Đang tải...',
        loading_notif:   'Đang tải thông báo...',
        empty:           'Không có dữ liệu',
        error_load:      'Lỗi tải dữ liệu',
        error_no_perm:   'Không có quyền hoặc lỗi tải dữ liệu',
        no_notif:        'Không có thông báo',

        // --- Toast messages ---
        toast_course_ok:     '✅ Tạo khóa học thành công',
        toast_course_err:    '❌ Lỗi tạo khóa học',
        toast_class_ok:      '✅ Tạo lớp học thành công',
        toast_class_err:     '❌ Lỗi tạo lớp học',
        toast_teacher_ok:    '✅ Thêm giáo viên thành công',
        toast_teacher_err:   '❌ Lỗi thêm giáo viên',
        toast_assign_ok:     '✅ Phân công thành công',
        toast_assign_err:    '❌ Lỗi phân công',
        toast_sched_ok:      '✅ Tạo lịch học thành công',
        toast_sched_err:     '❌ Lỗi tạo lịch',
        toast_enroll_ok:     '✅ Đăng ký học thành công',
        toast_enroll_err:    '❌ Lỗi đăng ký',
        toast_att_ok:        '✅ Ghi điểm danh thành công',
        toast_att_err:       '❌ Lỗi ghi điểm danh',
        toast_pay_ok:        '✅ Thanh toán thành công',
        toast_pay_err:       '❌ Lỗi thanh toán',
        toast_required:      '⚠️ Vui lòng điền đầy đủ thông tin bắt buộc',
        toast_class_req:     '⚠️ Vui lòng nhập ID lớp học',
        toast_class_id_req:  '⚠️ Vui lòng nhập ID lớp',
        toast_student_req:   '⚠️ Vui lòng điền đầy đủ thông tin',
        toast_payment_req:   '⚠️ Vui lòng nhập ID khoản học phí',
        conflict_room:       'Xung đột phòng học',

        // --- Select options ---
        opt_active:      'Hoạt động',
        opt_inactive:    'Ngưng',
        opt_upcoming:    'Sắp mở',
        opt_ongoing:     'Đang diễn ra',
        opt_completed_s: 'Hoàn thành',
        opt_regular:     'Thường',
        opt_extra:       'Bổ sung',
        opt_makeup:      'Bù',
        opt_cash:        'Tiền mặt',
        opt_bank:        'Chuyển khoản',
        opt_card:        'Thẻ',
        opt_present:     'Có mặt',
        opt_absent:      'Vắng',

        // --- Workload ---
        workload_over:   'VƯỢT CHUẨN',
        workload_meet:   'ĐẠT CHUẨN',
        workload_under:  'THIẾU CHUẨN',
    },

    en: {
        // --- Sidebar nav sections ---
        nav_overview:         'Overview',
        nav_academic:         'Academic Management',
        nav_finance:          'Finance',
        nav_reports_section:  'Reports',
        nav_system:           'System',

        // --- Sidebar nav items ---
        nav_dashboard:        'Dashboard',
        nav_courses:          'Courses',
        nav_classes:          'Classes',
        nav_teachers:         'Teachers',
        nav_assignments:      'Teacher Assign.',
        nav_schedules:        'Schedules',
        nav_enrollments:      'Enrollments',
        nav_attendance:       'Attendance',
        nav_tuitions:         'Tuition',
        nav_reports:          'Stats & Reports',
        nav_notifications:    'Notifications',

        // --- Page titles ---
        page_dashboard:       'Dashboard',
        page_courses:         'Course Management',
        page_classes:         'Class Management',
        page_teachers:        'Teacher Management',
        page_assignments:     'Teacher Assignments',
        page_schedules:       'Schedule Management',
        page_enrollments:     'Enrollment Management',
        page_attendance:      'Attendance',
        page_tuitions:        'Tuition Management',
        page_reports:         'Stats & Reports',
        page_notifications:   'Notifications',

        // --- Dashboard stat labels ---
        stat_courses:         'Courses',
        stat_classes:         'Classes',
        stat_teachers:        'Teachers',
        stat_revenue:         'Revenue',
        chart_revenue_title:  '📈 Monthly Revenue',
        chart_fillrate_title: '🏫 Class Fill Rate',
        recent_classes_title: '🏫 Recent Classes',
        no_data_chart:        'No revenue data yet',

        // --- Common buttons ---
        btn_add:              '+ Add',
        btn_add_course:       '+ Add Course',
        btn_add_class:        '+ Add Class',
        btn_add_teacher:      '+ Add Teacher',
        btn_add_assignment:   '+ Assign',
        btn_add_schedule:     '+ Create Schedule',
        btn_add_enrollment:   '+ Enroll',
        btn_add_attendance:   '+ Record Attendance',
        btn_pay_tuition:      '💳 Process Payment',
        btn_view:             '🔍 View',
        btn_view_schedule:    '🔍 View Schedule',
        btn_export_csv:       '⬇ Export CSV',
        btn_logout:           'Logout',

        // --- Filter labels ---
        filter_class_id:      'Class (ID):',
        filter_class_id_s:    'Class (ID):',
        filter_date:          'Date:',
        filter_student_id:    'Student (ID):',
        filter_month:         'Month:',
        placeholder_class_id: 'Enter class ID...',
        placeholder_student:  'Student ID...',
        search_placeholder:   'Search...',

        // --- Page headings ---
        heading_courses:      '📚 Course Management',
        heading_classes:      '🏫 Class Management',
        heading_teachers:     '👨‍🏫 Teacher Management',
        heading_assignments:  '📋 Teacher Assignments',
        heading_schedules:    '📅 Schedule Management',
        heading_enrollments:  '📝 Enrollment Management',
        heading_attendance:   '✅ Attendance',
        heading_tuitions:     '💰 Tuition Management',
        heading_reports:      '📈 Stats & Reports',
        heading_notifications:'🔔 Notifications',

        // --- Table columns ---
        col_id:               'ID',
        col_code:             'Code',
        col_course_code:      'Course Code',
        col_course_name:      'Course Name',
        col_class_code:       'Class Code',
        col_teacher_code:     'Teacher Code',
        col_teacher_name:     'Full Name',
        col_teacher_incharge: 'Teacher',
        col_classroom:        'Classroom',
        col_max_students:     'Max Students',
        col_duration_weeks:   'Sessions',
        col_tuition_fee:      'Tuition Fee',
        col_start_date:       'Start Date',
        col_end_date:         'End Date',
        col_status:           'Status',
        col_description:      'Description',
        col_email:            'Email',
        col_phone:            'Phone',
        col_specialization:   'Specialization',
        col_hire_date:        'Hire Date',
        col_student_code:     'Student Code',
        col_full_name:        'Full Name',
        col_class_id:         'Class ID',
        col_enroll_date:      'Enroll Date',
        col_payment_status:   'Payment',
        col_att_date:         'Date',
        col_att_status:       'Status',
        col_note:             'Note',
        col_amount:           'Amount',
        col_payment_date:     'Payment Date',
        col_payment_method:   'Method',
        col_month:            'Month',
        col_revenue:          'Revenue',
        col_sessions:         'Sessions',
        col_actual_hours:     'Actual Hours',
        col_workload:         'Workload',
        col_day_of_week:      'Day of Week',
        col_time_start:       'Start Time',
        col_time_end:         'End Time',
        col_sched_type:       'Type',
        col_assign_date:      'Assigned At',
        col_teacher_id:       'Teacher ID',

        // --- Days of week ---
        day_sun: 'Sunday', day_mon: 'Monday', day_tue: 'Tuesday',
        day_wed: 'Wednesday', day_thu: 'Thursday', day_fri: 'Friday', day_sat: 'Saturday',

        // --- Status badges ---
        status_active:    'Active',
        status_inactive:  'Inactive',
        status_upcoming:  'Upcoming',
        status_ongoing:   'Ongoing',
        status_completed: 'Completed',
        status_paid:      'Paid',
        status_unpaid:    'Unpaid',
        status_pending:   'Pending',
        status_present:   'Present',
        status_absent:    'Absent',
        status_confirmed: 'Confirmed',
        status_cancelled: 'Cancelled',

        // --- Modal titles ---
        modal_add_course:      '➕ Add Course',
        modal_add_class:       '➕ Add Class',
        modal_add_teacher:     '➕ Add Teacher',
        modal_add_assignment:  '📋 Assign Teacher',
        modal_add_schedule:    '📅 Create Schedule',
        modal_add_enrollment:  '📝 Enroll Student',
        modal_add_attendance:  '✅ Record Attendance',
        modal_pay_tuition:     '💳 Process Payment',
        btn_modal_cancel:      'Cancel',
        btn_modal_confirm:     'Confirm',
        btn_save:              'Save changes',
        modal_edit_course:     'Edit course',
        modal_edit_class:      'Edit class',
        modal_edit_teacher:    'Edit teacher',
        modal_edit_enrollment: 'Edit enrollment',
        lbl_payment_status:    'Payment status',

        // --- Modal form labels ---
        lbl_course_code:     'Course Code *',
        lbl_course_name:     'Course Name *',
        lbl_duration_weeks:  'Number of Sessions *',
        lbl_tuition_fee_f:   'Tuition Fee (VND) *',
        lbl_description:     'Description',
        lbl_status:          'Status',
        lbl_class_code:      'Class Code *',
        lbl_course_id:       'Course ID *',
        lbl_teacher_id_opt:  'Teacher ID',
        lbl_classroom_id:    'Classroom ID',
        lbl_semester_id:     'Semester ID',
        lbl_max_students:    'Max Students *',
        lbl_start_date:      'Start Date *',
        lbl_end_date:        'End Date *',
        lbl_user_id:         'User ID *',
        lbl_teacher_code:    'Teacher Code *',
        lbl_specialization:  'Specialization *',
        lbl_hire_date:       'Hire Date',
        lbl_teacher_id_req:  'Teacher ID *',
        lbl_class_id_req:    'Class ID *',
        lbl_scenario:        'Scenario Name',
        lbl_day_of_week:     'Day of Week *',
        lbl_time_start:      'Start Time *',
        lbl_time_end:        'End Time *',
        lbl_sched_type:      'Schedule Type',
        lbl_student_id:      'Student ID *',
        lbl_class_id_f:      'Class ID *',
        lbl_att_date:        'Date *',
        lbl_att_status:      'Status *',
        lbl_att_note:        'Note',
        lbl_payment_id:      'Tuition Payment ID *',
        lbl_payment_method:  'Payment Method *',

        // --- Reports ---
        report_revenue_title:  '💰 Monthly Revenue',
        report_hours_title:    '👨‍🏫 Teacher Teaching Hours',
        report_students_title: '🎓 Student Statistics',
        total_students:        'Total',
        unit_students:         'students',

        // --- State messages ---
        loading:         'Loading...',
        loading_notif:   'Loading notifications...',
        empty:           'No data available',
        error_load:      'Failed to load data',
        error_no_perm:   'No permission or failed to load data',
        no_notif:        'No notifications',

        // --- Toast messages ---
        toast_course_ok:     '✅ Course created successfully',
        toast_course_err:    '❌ Failed to create course',
        toast_class_ok:      '✅ Class created successfully',
        toast_class_err:     '❌ Failed to create class',
        toast_teacher_ok:    '✅ Teacher added successfully',
        toast_teacher_err:   '❌ Failed to add teacher',
        toast_assign_ok:     '✅ Teacher assigned successfully',
        toast_assign_err:    '❌ Assignment failed',
        toast_sched_ok:      '✅ Schedule created successfully',
        toast_sched_err:     '❌ Failed to create schedule',
        toast_enroll_ok:     '✅ Enrollment successful',
        toast_enroll_err:    '❌ Enrollment failed',
        toast_att_ok:        '✅ Attendance recorded successfully',
        toast_att_err:       '❌ Failed to record attendance',
        toast_pay_ok:        '✅ Payment processed successfully',
        toast_pay_err:       '❌ Payment failed',
        toast_required:      '⚠️ Please fill in all required fields',
        toast_class_req:     '⚠️ Please enter a class ID',
        toast_class_id_req:  '⚠️ Please enter a class ID',
        toast_student_req:   '⚠️ Please fill in all required fields',
        toast_payment_req:   '⚠️ Please enter a tuition payment ID',
        conflict_room:       'Classroom conflict detected',

        // --- Select options ---
        opt_active:      'Active',
        opt_inactive:    'Inactive',
        opt_upcoming:    'Upcoming',
        opt_ongoing:     'Ongoing',
        opt_completed_s: 'Completed',
        opt_regular:     'Regular',
        opt_extra:       'Extra',
        opt_makeup:      'Make-up',
        opt_cash:        'Cash',
        opt_bank:        'Bank Transfer',
        opt_card:        'Card',
        opt_present:     'Present',
        opt_absent:      'Absent',

        // --- Workload ---
        workload_over:   'EXCEEDS',
        workload_meet:   'MEETS',
        workload_under:  'BELOW',
    }
};

// ============================================================
// I18N ENGINE
// ============================================================
let _currentLang = localStorage.getItem('lang') || 'vi';

function t(key) {
    return (TRANSLATIONS[_currentLang] && TRANSLATIONS[_currentLang][key])
        || (TRANSLATIONS['vi'][key])
        || key;
}

function getLang() { return _currentLang; }

function setLanguage(lang) {
    if (!TRANSLATIONS[lang]) return;
    _currentLang = lang;
    localStorage.setItem('lang', lang);
    applyI18n();
    updateLangBtn();
    // Re-render current active page
    const activePage = document.querySelector('.nav-item.active');
    if (activePage && activePage.dataset.page) {
        if (typeof loadPage === 'function') loadPage(activePage.dataset.page);
    }
}

function applyI18n() {
    // Update all elements with data-i18n attribute
    document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.getAttribute('data-i18n');
        const text = t(key);
        if (text) el.textContent = text;
    });

    // Update placeholder attributes
    document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
        const key = el.getAttribute('data-i18n-placeholder');
        const text = t(key);
        if (text) el.placeholder = text;
    });

    // Update modal buttons
    const cancelBtn = document.getElementById('modal-cancel');
    if (cancelBtn) cancelBtn.textContent = t('btn_modal_cancel');

    // Update page title in breadcrumb
    const pageTitle = document.getElementById('page-title');
    if (pageTitle) {
        const activePage = document.querySelector('.nav-item.active');
        if (activePage && activePage.dataset.page) {
            pageTitle.textContent = t('page_' + activePage.dataset.page) || pageTitle.textContent;
        }
    }
}

function updateLangBtn() {
    const flagEl = document.getElementById('lang-flag');
    const textEl = document.getElementById('lang-text');
    if (!flagEl || !textEl) return;
    if (_currentLang === 'vi') {
        flagEl.textContent = '🇻🇳';
        textEl.textContent = 'VI';
    } else {
        flagEl.textContent = '🇺🇸';
        textEl.textContent = 'EN';
    }
}

function toggleLanguage() {
    setLanguage(_currentLang === 'vi' ? 'en' : 'vi');
}

// Init on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    applyI18n();
    updateLangBtn();
    const btn = document.getElementById('lang-btn');
    if (btn) btn.addEventListener('click', toggleLanguage);
});
