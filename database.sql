-- Education Center Class Planning & Teacher Assignment System
-- Database Schema (PostgreSQL)

CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    role_name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT
);

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_roles (
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    role_id INT REFERENCES roles(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, role_id)
);

CREATE TABLE teachers (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    teacher_code VARCHAR(50) UNIQUE NOT NULL,
    specialization VARCHAR(255),
    hire_date DATE,
    teacher_type VARCHAR(20) DEFAULT 'FULL_TIME',
    standard_hours INT DEFAULT 40,
    status VARCHAR(20) DEFAULT 'ACTIVE'
);

CREATE TABLE students (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    student_code VARCHAR(50) UNIQUE NOT NULL,
    date_of_birth DATE,
    parent_phone VARCHAR(20),
    status VARCHAR(20) DEFAULT 'ACTIVE'
);

CREATE TABLE courses (
    id SERIAL PRIMARY KEY,
    course_code VARCHAR(50) UNIQUE NOT NULL,
    course_name VARCHAR(255) NOT NULL,
    description TEXT,
    duration_weeks INT NOT NULL,
    tuition_fee DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE'
);

CREATE TABLE classrooms (
    id SERIAL PRIMARY KEY,
    room_name VARCHAR(50) UNIQUE NOT NULL,
    capacity INT NOT NULL,
    location VARCHAR(255),
    status VARCHAR(20) DEFAULT 'ACTIVE'
);

CREATE TABLE semesters (
    id SERIAL PRIMARY KEY,
    semester_name VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'PLANNED'
);

CREATE TABLE class_plans (
    id SERIAL PRIMARY KEY,
    course_id INT REFERENCES courses(id),
    semester_id INT REFERENCES semesters(id),
    planned_class_count INT NOT NULL,
    target_student_count INT NOT NULL,
    status VARCHAR(20) DEFAULT 'DRAFT',
    created_by INT REFERENCES users(id)
);

CREATE TABLE classes (
    id SERIAL PRIMARY KEY,
    class_code VARCHAR(50) UNIQUE NOT NULL,
    course_id INT REFERENCES courses(id),
    teacher_id INT REFERENCES teachers(id),
    classroom_id INT REFERENCES classrooms(id),
    semester_id INT REFERENCES semesters(id),
    max_students INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'UPCOMING'
);

CREATE TABLE schedules (
    id SERIAL PRIMARY KEY,
    class_id INT REFERENCES classes(id) ON DELETE CASCADE,
    day_of_week INT NOT NULL, -- 0: Sunday, 1: Monday, ... 6: Saturday
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    schedule_type VARCHAR(50) DEFAULT 'REGULAR'
);

CREATE TABLE enrollments (
    id SERIAL PRIMARY KEY,
    student_id INT REFERENCES students(id),
    class_id INT REFERENCES classes(id),
    enrollment_date DATE DEFAULT CURRENT_DATE,
    payment_status VARCHAR(20) DEFAULT 'UNPAID',
    status VARCHAR(20) DEFAULT 'ACTIVE'
);

CREATE TABLE attendance (
    id SERIAL PRIMARY KEY,
    class_id INT REFERENCES classes(id),
    student_id INT REFERENCES students(id),
    attendance_date DATE NOT NULL,
    attendance_status VARCHAR(20) NOT NULL, -- PRESENT, ABSENT, EXCUSED
    note TEXT
);

CREATE TABLE teacher_assignments (
    id SERIAL PRIMARY KEY,
    teacher_id INT REFERENCES teachers(id),
    class_id INT REFERENCES classes(id),
    assigned_by INT REFERENCES users(id),
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    scenario_name VARCHAR(100) DEFAULT 'FINAL',
    assignment_status VARCHAR(20) DEFAULT 'CONFIRMED'
);

CREATE TABLE payrolls (
    id SERIAL PRIMARY KEY,
    teacher_id INT REFERENCES teachers(id),
    month VARCHAR(7) NOT NULL, -- YYYY-MM
    teaching_hours DECIMAL(5, 2) NOT NULL,
    salary_amount DECIMAL(12, 2) NOT NULL,
    payment_status VARCHAR(20) DEFAULT 'UNPAID'
);

CREATE TABLE tuition_payments (
    id SERIAL PRIMARY KEY,
    student_id INT REFERENCES students(id),
    amount DECIMAL(10, 2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(50),
    payment_status VARCHAR(20) DEFAULT 'COMPLETED'
);

CREATE TABLE notifications (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    receiver_id INT REFERENCES users(id),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE audit_logs (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id),
    action VARCHAR(100) NOT NULL,
    entity_name VARCHAR(100) NOT NULL,
    entity_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Default Roles
INSERT INTO roles (role_name, description) VALUES 
('SUPER_ADMIN', 'Quản trị viên hệ thống'),
('CENTER_MANAGER', 'Quản lý trung tâm'),
('ACADEMIC_STAFF', 'Nhân viên giáo vụ'),
('TEACHER', 'Giáo viên'),
('ACCOUNTANT', 'Kế toán'),
('STUDENT', 'Học viên');
