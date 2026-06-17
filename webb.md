# Education Center Class Planning & Teacher Assignment System
---
## 1. Mục tiêu hệ thống

Xây dựng hệ thống quản lí trung tâm đào tạo production-ready.

Hệ thống phải hỗ trợ:

- quản lí khóa học
- quản lí lớp học
- quản lí giáo viên
- quản lí học viên
- quản lí lịch học
- quản lí phòng học
- quản lí kế hoạch mở lớp
- phân công giáo viên
- quản lí ca học
- quản lí đăng kí học
- quản lí học phí
- quản lí điểm danh
- quản lí lịch dạy
- quản lí xung đột lịch
- quản lí quyền truy cập RBAC
- thống kê và báo cáo

Hệ thống phải:

- dễ mở rộng
- dễ maintain
- dễ debug
- hỗ trợ scale lớn
- chuẩn enterprise architecture
- hỗ trợ realtime scheduling validation

---

## 2. Kiến trúc tổng thể

```txt
Admin Portal
    ↓
REST API Gateway
    ↓
Application Services
    ├─ Authentication Service
    ├─ Course Management Service
    ├─ Class Planning Service
    ├─ Teacher Assignment Service
    ├─ Student Enrollment Service
    ├─ Scheduling Service
    ├─ Attendance Service
    ├─ Tuition Service
    └─ Reporting Service
    ↓
Database Layer
    ├─ PostgreSQL
    ├─ Redis Cache
    └─ File Storage
## 3. Công nghệ bắt buộc

Stack:

Java Spring Boot hoặc ASP.NET Core hoặc Node.js NestJS
PostgreSQL
Redis
JWT Authentication
Docker
Swagger/OpenAPI
Prisma hoặc Entity Framework hoặc Hibernate
ReactJS/VueJS frontend
TailwindCSS

Hệ thống phải:

phân tầng rõ ràng
repository pattern
service layer
DTO validation
RBAC middleware
transaction handling
audit logging
##4. Database Schema

Hệ thống phải thiết kế tối thiểu 12 bảng.

Danh sách bảng bắt buộc
1. users
id
full_name
email
password_hash
phone
status
created_at
updated_at
2. roles
id
role_name
description
3. user_roles
user_id
role_id
4. teachers
id
user_id
teacher_code
specialization
hire_date
status
5. students
id
user_id
student_code
date_of_birth
parent_phone
status
6. courses
id
course_code
course_name
description
duration_weeks
tuition_fee
status
7. classrooms
id
room_name
capacity
location
status
8. semesters
id
semester_name
start_date
end_date
status
9. class_plans
id
course_id
semester_id
planned_class_count
target_student_count
status
created_by
10. classes
id
class_code
course_id
teacher_id
classroom_id
semester_id
max_students
start_date
end_date
status
11. schedules
id
class_id
day_of_week
start_time
end_time
schedule_type
12. enrollments
id
student_id
class_id
enrollment_date
payment_status
status
13. attendance
id
class_id
student_id
attendance_date
attendance_status
note
14. teacher_assignments
id
teacher_id
class_id
assigned_by
assigned_at
assignment_status
15. payrolls
id
teacher_id
month
teaching_hours
salary_amount
payment_status
16. tuition_payments
id
student_id
amount
payment_date
payment_method
payment_status
17. notifications
id
title
content
receiver_id
is_read
created_at
18. audit_logs
id
user_id
action
entity_name
entity_id
created_at
##5. Quy tắc business logic
5.1 Teacher assignment

Hệ thống phải kiểm tra:

giáo viên có bị trùng lịch không
giáo viên có đúng chuyên môn không
giáo viên có vượt số giờ dạy tối đa không

Ví dụ:

Teacher A:
- Đã có lớp thứ 2 từ 18:00–20:00

Không cho assign thêm lớp:
- thứ 2 từ 19:00–21:00
##6. Scheduling engine

Scheduling service phải hỗ trợ:

detect conflict
validate classroom capacity
validate teacher availability
validate semester timeline
##7. RBAC

Roles:

SUPER_ADMIN
CENTER_MANAGER
ACADEMIC_STAFF
TEACHER
ACCOUNTANT
STUDENT

Permission examples:

CENTER_MANAGER:
- create class
- assign teacher
- approve schedules

TEACHER:
- view teaching schedule
- mark attendance

ACCOUNTANT:
- manage tuition
- payroll management
##8. API Requirements

RESTful APIs:

POST   /auth/login
POST   /courses
GET    /courses
POST   /classes
POST   /teacher-assignments
GET    /teachers/{id}/schedule
POST   /enrollments
POST   /attendance
GET    /reports/revenue

Tất cả APIs phải:

validate DTO
return standard response format
support pagination
support filtering
support sorting
##9. Reporting & Analytics

Hệ thống phải hỗ trợ:

doanh thu theo tháng
số lượng học viên
tỉ lệ lấp đầy lớp học
số giờ dạy giáo viên
thống kê điểm danh
lớp học hoạt động tốt nhất
10. Notification System

Notification phải hỗ trợ:

email
in-app notification

Events:

- class opened
- teacher assigned
- tuition overdue
- class cancelled
11. Kiến trúc thư mục
src/
  controllers/
  services/
  repositories/
  entities/
  dto/
  middlewares/
  guards/
  validators/
  schedulers/
  utils/
  configs/
##12. Service boundaries
auth_service
course_service
class_service
teacher_service
schedule_service
attendance_service
tuition_service
report_service
notification_service
##13. Security Requirements

Hệ thống phải:

hash password bằng bcrypt
JWT authentication
refresh token
RBAC authorization
rate limiting
audit logging
SQL injection protection
XSS protection
##14. Definition of done

System đạt khi:

login hoạt động
CRUD đầy đủ
teacher assignment hoạt động
detect conflict lịch học
enrollment hoạt động
attendance hoạt động
tuition management hoạt động
reporting hoạt động
RBAC hoạt động
audit logs hoạt động
##15. Implementation roadmap

Phase 1

authentication
RBAC
database schema

Phase 2

course management
class management
teacher management

Phase 3

scheduling engine
teacher assignment
conflict detection

Phase 4

attendance
tuition
notifications

Phase 5

analytics
reports
system optimization
##16. Quy tắc cuối cùng

Luôn đảm bảo:

clean architecture
enterprise standard
scalable design
modular services
strong validation
transaction safety
high maintainability



Đề tài: Xây một web application hoàn chỉnh có cả frontend lẫn backend thật, dùng PHP + MySQL/MariaDB + HTML/CSS/JS. Trọng tâm là xử lý backend & business logic, KHÔNG phải giao diện đẹp. Đây là điểm cực kỳ quan trọng — đẹp mà backend yếu thì điểm vẫn bị giới hạn.
Quy mô nhóm & bảng dữ liệu:
- Làm ít nhất 3 bảng liên quan logic, thuộc một nghiệp vụ có nghĩa.
-  Tối thiểu 9 bảng kết nối thành một hệ thống tích hợp (không phải 3 hệ thống rời rạc).
Mỗi bộ bảng phải có đầy đủ CRUD + business rules (chống trùng, xử lý trạng thái, phân quyền, duyệt, kiểm tra số lượng, ràng buộc lịch, tính điểm...).
- Kỹ thuật bắt buộc:
+ Database tự thiết kế + có ERD thể hiện quan hệ.
+ Tối thiểu 3 vai trò người dùng  Admin, Giáo viên, Học viên, có tài khoản
+ Validation cả phía giao diện lẫn server. 
+ Điểm cộng: tách MVC, dùng PDO/MySQLi, AJAX CRUD, phân quyền theo role, export, thông báo.

* Admin: Vai trò quản lí học viên, giáo viên, thu tiền
-  Khóa học: cố định thêm lớp học, 
+  ID ||Mã khóa học|| Tên khóa học||Số buổi học	||Học phí ||	Trạng thái	||Mô tả Sửa || Xóa

Số buổi học mặc định là 2 buổi -> 1 tuần -> tự động set sửa số ngày luôn ví dụ set 20 buổi học -> 10 tuần ->  1/7/2026 + 10 tuần là kết thúc -> 18/11/2026 (gần hoàn thiện)

- quản lý lớp học -> Dựa trên khóa học để mở lớp và kèm theo thời gian, học kì,.1/7/2026 + 10 tuần là kết thúc -> 18/11/2026 (gần hoàn thiện) 

- Học viên: Thanh toán và thu tiền
+ Thanh toán xong thì cho vào lớp học + lịch học 
+ Sẽ có tài khoản email : password để học sinh check portal
+ Không thanh toán sẽ không cho vào lớp học
+ 1 học sinh có thể đăng ký nhiều lớp, không trùng lịch.
+ Doanh thu sẽ nhảy lên dashboard 
+ 1 khóa học sẽ cố định 2 ngày chính trong tuần ví dụ học sinh đăng ký thứ hai và thứ năm (Lớp python)
- Giáo viên thì điền thông tin info nhưng quan trọng nhất (email: password) để đăng nhập

- Lịch học: Học viên sau thi thanh toán sẽ được phân vào lớp học kèm với giáo viên:
ví dụ: 
Nguyễn Văn A -
PY501	Lập trình Python	 1/7/2026 -> 18/11/2026 -> Lịch sẽ mở ngay đúng ngay mốc thời gian 1/7/2026 và tuần 1: thứ hai thứ năm, tuần 2 thứ hai - Giáo viên nào được phân công đi dạy (Không được trùng lịch giáo viên với học sinh khác), thứ năm (Không được trùng lịch giáo viên với học sinh khác - tương tự)

- Mục nữa thống kê học sinh và giáo viên: Điểm số, chất lượng học ( cái này a có thể tự lên idea)

+ Trong 10 tuần sẽ có mục học bù và lịch thi admin sẽ quyết định. Đồng thời từ tài khoản admin update lên hệ thống và thông báo lịch cho giáo viên và học sinh
+....
* Giáo viên: 
- Sẽ có portal dạy 
- Điểm danh trên hệ thống là có học sinh với giáo viên đi học đầy đủ -> Thông báo qua admin để mục đích chấm công
- Có mục xin phép nghỉ từ thông báo giáo viên -> Sẽ ra thông hệ thống của admdin 
- Có mục xin phép học bù do lí do gì sẽ thông báo qua admin để admin duyệt
- Mục chấm bài tập -> học sinh sẽ up file pdf bài tập hoặc giữa kì hoặc cuối kì -> giáo viên có thể cho điểm lên hệ thống và mục nhận xét
- Tổng kết điểm học sinh, đánh giá năng lực giỏi, khá, trung bình , kém, đánh giá (có học lại hay không)


* Học sinh:
- Check được lịch đi học, đã điểm danh bởi giáo viên hay chưa
- Có mục file up pdf để up bài tập hoặc các kì thi
- Có cột nhân xét và đánh giá sau khóa học
- Sau khi kết thúc khóa học sẽ có một khảo sát về gia sư
- Cột so sánh điểm so với các bạn khác.

Tôi muốn tối ưu tạo ra 3 thư mục code các chức năng của sinh viên student, giáo viên teacher, và admin quản trị 

 student thư mục này xây dựng các chức năng của học sinh
 giáo viên teacher chức năng của giáo viên 
 admin quản trị  các chức năng của quản trị crud các tính năng

 tôi muốn file view là file php và chia layout tối ưu giao diện clear dễ sử dụng dễ đọc hiểu source code 