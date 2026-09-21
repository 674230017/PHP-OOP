-- ฐานข้อมูลระบบแจ้งซ่อมภายในโรงเรียน
CREATE DATABASE IF NOT EXISTS school_repair CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE school_repair;

DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS repair_images;
DROP TABLE IF EXISTS repair_requests;
DROP TABLE IF EXISTS users;

-- 1. ตารางผู้ใช้งานระบบ
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    role ENUM('admin', 'teacher', 'technician') NOT NULL,
    email VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. ตารางใบแจ้งซ่อม
CREATE TABLE repair_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_no VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    building VARCHAR(100) NOT NULL,
    room VARCHAR(50) NOT NULL,
    problem_type ENUM('electrical', 'furniture', 'computer', 'aircon', 'other') NOT NULL,
    description TEXT NOT NULL,
    urgency ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('pending', 'accepted', 'in_progress', 'completed') DEFAULT 'pending',
    assigned_to INT NULL,
    due_date DATE NULL,
    completion_date DATE NULL,
    cost DECIMAL(10, 2) NULL,
    result TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_repair_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_repair_assignee FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. ตารางรูปภาพประกอบ
CREATE TABLE repair_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    repair_id INT NOT NULL,
    image_type ENUM('before', 'after') NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_image_repair FOREIGN KEY (repair_id) REFERENCES repair_requests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. ตารางความคิดเห็น
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    repair_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comment_repair FOREIGN KEY (repair_id) REFERENCES repair_requests(id) ON DELETE CASCADE,
    CONSTRAINT fk_comment_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ข้อมูลตัวอย่างสำหรับทดสอบ (รหัสผ่านคือ 1234 สำหรับทุกบัญชี)
INSERT INTO users (username, password, fullname, role, email) VALUES
('admin', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1z5.V9m.X3YvYn4d34V5y1A11K5K2.', 'ผู้ดูแลระบบ (Admin)', 'admin', 'admin@school.ac.th'),
('teacher1', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1z5.V9m.X3YvYn4d34V5y1A11K5K2.', 'ครูสมชาย ขยันเรียน (Teacher)', 'teacher', 'somchai@school.ac.th'),
('tech1', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1z5.V9m.X3YvYn4d34V5y1A11K5K2.', 'ช่างวิเชียร การช่าง (Technician)', 'technician', 'wichian@school.ac.th');

-- ข้อมูลใบแจ้งซ่อมตัวอย่าง
INSERT INTO repair_requests (request_no, user_id, building, room, problem_type, description, urgency, status, assigned_to, due_date) VALUES
('RP-202401-001', 2, 'อาคารเรียน 3', 'ห้อง 302', 'aircon', 'เครื่องปรับอากาศไม่เย็น มีเสียงดังผิดปกติ', 'high', 'accepted', 3, '2024-01-25'),
('RP-202401-002', 2, 'อาคารอำนวยการ', 'ห้องประชุมใหญ่', 'electrical', 'หลอดไฟ LED ช่วงกลางห้องดับ 2 หลอด', 'medium', 'pending', NULL, NULL);
