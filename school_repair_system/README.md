# 🔧 ระบบแจ้งซ่อมภายในโรงเรียน (School Repair Request System)

ระบบบริหารจัดการแจ้งซ่อมภายในโรงเรียน พัฒนาด้วย PHP 8, MySQL/phpMyAdmin, Bootstrap 5 และ SweetAlert2

---

## 📂 โครงสร้างไฟล์ในโปรเจกต์
- `database.sql` : สคริปต์สร้างฐานข้อมูล และตาราง users, repair_requests, repair_images, comments
- `db.php` : ไฟล์เชื่อมต่อฐานข้อมูล PDO
- `auth.php` : ระบบตรวจสอบสิทธิ์เข้าใช้งานและ Middleware
- `header.php` / `footer.php` : เลย์เอาต์หลักของระบบ
- `login.php` : หน้าเข้าสู่ระบบ
- `logout.php` : หน้าออกจากระบบ
- `dashboard.php` : หน้าแสดงสถิติและรายการแจ้งซ่อมทั้งหมด
- `add_repair.php` : ฟอร์มส่งใบแจ้งซ่อมพร้อมอัปโหลดรูปภาพ
- `repair_detail.php` : หน้ารายละเอียด, มอบหมายช่าง, อัปเดตผลการซ่อม, เพิ่มความคิดเห็น, สั่งพิมพ์ PDF
- `export_csv.php` : สคริปต์ส่งออกรายงานเป็นไฟล์ CSV (UTF-8 BOM)
- `uploads/repairs/` : โฟลเดอร์เก็บไฟล์รูปภาพประกอบ

---

## 🛠️ ขั้นตอนการติดตั้งบน XAMPP

1. **ก๊อปปี้โฟลเดอร์โปรเจกต์**
   นำโฟลเดอร์ `school_repair_system` ไปวางไว้ที่ `C:\xampp\htdocs\`

2. **นำเข้าฐานข้อมูลผ่าน phpMyAdmin**
   - เปิด Apache และ MySQL บน XAMPP Control Panel
   - ไปที่ `http://localhost/phpmyadmin`
   - สร้างฐานข้อมูลใหม่ชื่อ `school_repair` (Collation: `utf8mb4_unicode_ci`)
   - เลือกฐานข้อมูล `school_repair` แล้วกดเมนู **Import** หรือ **SQL**
   - นำไฟล์ `database.sql` มา Execute เพื่อสร้างตารางและข้อมูลเริ่มต้น

3. **เปิดใช้งานระบบผ่าน Web Browser**
   - เข้าผ่าน URL: `http://localhost/school_repair_system/login.php`

---

## 🔐 ข้อมูลผู้ใช้งานเริ่มต้น (ทดสอบระบบ)

| Username | Password | Role | ชื่อ-นามสกุล |
| :--- | :--- | :--- | :--- |
| `admin` | `1234` | **Admin** | ผู้ดูแลระบบ |
| `teacher1` | `1234` | **Teacher** | ครูสมชาย ขยันเรียน |
| `tech1` | `1234` | **Technician** | ช่างวิเชียร การช่าง |

---
