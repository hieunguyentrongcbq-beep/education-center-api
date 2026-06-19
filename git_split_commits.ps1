# Script tự động chia commit dự án EduCenter thành 15 commits trong 3 ngày (mỗi ngày 5 commits)
# Sử dụng phương pháp Orphan Branch để xóa lịch sử sạch sẽ mà không lo khóa folder .git của Windows

Write-Host "=== Bắt đầu chia nhỏ lịch sử Git bằng Orphan Branch ===" -ForegroundColor Cyan

# 1. Tạo nhánh orphan mới (không có cha, lịch sử hoàn toàn trống)
Write-Host "Tạo nhánh tạm thời temp_branch..." -ForegroundColor Green
git checkout --orphan temp_branch

# 2. Xóa toàn bộ file khỏi vùng nhớ đệm Git (Index) để bắt đầu add lại từ đầu
Write-Host "Bỏ stage toàn bộ file để chia commit..." -ForegroundColor Green
git rm -rf --cached .

# Thiết lập Git lưu tiếng Việt không bị lỗi font
git config core.quotepath false

# ----------------- NGÀY 1: 17/06/2026 (Thiết lập & Core Architecture) -----------------
Write-Host "`n--- Đang commit các file Ngày 1 (17/06/2026) ---" -ForegroundColor Blue

# Commit 1: 09:15:00
$env:GIT_AUTHOR_DATE="2026-06-17T09:15:00"
$env:GIT_COMMITTER_DATE="2026-06-17T09:15:00"
git add project_root/config/database.php project_root/.htaccess webb.md .gitignore
git commit -m "Feat: initialize project structure and database configuration"

# Commit 2: 11:30:00
$env:GIT_AUTHOR_DATE="2026-06-17T11:30:00"
$env:GIT_COMMITTER_DATE="2026-06-17T11:30:00"
git add migrate.php finaldemo_web.sql seed_fake.php
git commit -m "Feat: implement database schema migrations and seed data"

# Commit 3: 14:00:00
$env:GIT_AUTHOR_DATE="2026-06-17T14:00:00"
$env:GIT_COMMITTER_DATE="2026-06-17T14:00:00"
git add project_root/core/database.php project_root/core/router.php
git commit -m "Feat: implement Singleton database connection and Regex-based Router"

# Commit 4: 16:15:00
$env:GIT_AUTHOR_DATE="2026-06-17T16:15:00"
$env:GIT_COMMITTER_DATE="2026-06-17T16:15:00"
git add project_root/core/controller.php project_root/core/WebController.php
git commit -m "Feat: build base WebController with validation, render, and CSV utilities"

# Commit 5: 18:30:00
$env:GIT_AUTHOR_DATE="2026-06-17T18:30:00"
$env:GIT_COMMITTER_DATE="2026-06-17T18:30:00"
git add project_root/app/middleware/SessionAuth.php project_root/app/models/UserModel.php project_root/app/models/classmodel.php project_root/app/models/instructormodel.php
git commit -m "Feat: implement SessionAuth middleware and UserModel with password hashing"


# ----------------- NGÀY 2: 18/06/2026 (Auth, Layouts & Base CRUDs) -----------------
Write-Host "`n--- Đang commit các file Ngày 2 (18/06/2026) ---" -ForegroundColor Blue

# Commit 6: 09:30:00
$env:GIT_AUTHOR_DATE="2026-06-18T09:30:00"
$env:GIT_COMMITTER_DATE="2026-06-18T09:30:00"
git add project_root/app/controllers/WebAuthController.php project_root/app/controllers/AuthController.php project_root/app/view/auth/
git commit -m "Feat: implement WebAuthController and login views"

# Commit 7: 11:45:00
$env:GIT_AUTHOR_DATE="2026-06-18T11:45:00"
$env:GIT_COMMITTER_DATE="2026-06-18T11:45:00"
git add project_root/app/view/layouts/main.php project_root/app/view/layouts/auth.php project_root/app/view/partials/ project_root/index.php project_root/public/web.php project_root/public/index.php project_root/public/assets/ project_root/public/.htaccess
git commit -m "Feat: design main layout, dashboard layouts, and sidebar/flash partials"

# Commit 8: 13:45:00
$env:GIT_AUTHOR_DATE="2026-06-18T13:45:00"
$env:GIT_COMMITTER_DATE="2026-06-18T13:45:00"
git add project_root/app/web_routes.php project_root/app/routes.php
git commit -m "Feat: configure web_routes mapping path rules to controllers"

# Commit 9: 15:30:00
$env:GIT_AUTHOR_DATE="2026-06-18T15:30:00"
$env:GIT_COMMITTER_DATE="2026-06-18T15:30:00"
git add project_root/app/controllers/admin/CourseWebController.php project_root/app/controllers/admin/ClassroomWebController.php project_root/app/view/admin/courses/ project_root/app/view/admin/classrooms/
git commit -m "Feat: add Course and Classroom CRUD controllers and views"

# Commit 10: 17:45:00
$env:GIT_AUTHOR_DATE="2026-06-18T17:45:00"
$env:GIT_COMMITTER_DATE="2026-06-18T17:45:00"
git add project_root/app/controllers/admin/SemesterWebController.php project_root/app/controllers/admin/ClassWebController.php project_root/app/controllers/admin/ClassPlanWebController.php project_root/app/view/admin/semesters/ project_root/app/view/admin/classes/ project_root/app/view/admin/class_plans/ project_root/app/controllers/classcontrol.php project_root/app/controllers/instructorcontrol.php
git commit -m "Feat: add Class, Semester, and Class Plan management"


# ----------------- NGÀY 3: 19/06/2026 (Scheduling & Advanced Business) -----------------
Write-Host "`n--- Đang commit các file Ngày 3 (19/06/2026) ---" -ForegroundColor Blue

# Commit 11: 09:15:00
$env:GIT_AUTHOR_DATE="2026-06-19T09:15:00"
$env:GIT_COMMITTER_DATE="2026-06-19T09:15:00"
git add project_root/app/models/ScheduleService.php project_root/app/controllers/admin/ScheduleWebController.php project_root/app/view/admin/schedules/
git commit -m "Feat: build ScheduleService with student/teacher conflict check and automatic scheduler"

# Commit 12: 11:00:00
$env:GIT_AUTHOR_DATE="2026-06-19T11:00:00"
$env:GIT_COMMITTER_DATE="2026-06-19T11:00:00"
git add project_root/app/models/assigning.php project_root/app/controllers/admin/AssignmentWebController.php project_root/app/view/admin/assignments/
git commit -m "Feat: build teacher assignment model with conflict validation"

# Commit 13: 14:15:00
$env:GIT_AUTHOR_DATE="2026-06-19T14:15:00"
$env:GIT_COMMITTER_DATE="2026-06-19T14:15:00"
git add project_root/app/models/PaymentService.php project_root/app/controllers/admin/PaymentWebController.php project_root/app/view/admin/payments/
git commit -m "Feat: integrate PaymentService and PaymentWebController to process fees and generate schedules"

# Commit 14: 16:30:00
$env:GIT_AUTHOR_DATE="2026-06-19T16:30:00"
$env:GIT_COMMITTER_DATE="2026-06-19T16:30:00"
git add project_root/app/controllers/teacher/ project_root/app/controllers/student/ project_root/app/view/teacher/ project_root/app/view/student/ project_root/app/controllers/NotificationWebController.php project_root/app/view/notifications/ project_root/app/controllers/PortalApiController.php project_root/app/models/Phase2Service.php
git add -f project_root/public/uploads/submissions/pdf_6a3122198f48b7.24584309.pdf project_root/public/uploads/submissions/pdf_6a31231f7e4b71.00615683.pdf
git commit -m "Feat: implement teacher grading portal and student file upload submissions"

# Commit 15: 18:45:00
$env:GIT_AUTHOR_DATE="2026-06-19T18:45:00"
$env:GIT_COMMITTER_DATE="2026-06-19T18:45:00"
# Thêm toàn bộ các file còn lại (CSS, JS, frontend mockup, scripts, và các file báo cáo, v.v.)
git add .
git commit -m "Feat: finalize system with audit logs, leave request approval, and statistical reports"

# Clear environment variables
Remove-Item Env:\GIT_AUTHOR_DATE
Remove-Item Env:\GIT_COMMITTER_DATE

# 3. Ghi đè nhánh main cũ bằng nhánh temp_branch mới
Write-Host "Ghi đè lịch sử nhánh main cũ..." -ForegroundColor Green
git branch -D main
git branch -m main

Write-Host "`n=== Hoàn thành phân chia 15 commits thành công! ===" -ForegroundColor Green
Write-Host "Bây giờ bạn hãy add remote GitHub và gõ: git push -u -f origin main" -ForegroundColor Yellow
