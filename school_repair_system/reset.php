<?php
require_once 'db.php';
$hash = password_hash('1234', PASSWORD_BCRYPT);
$pdo->prepare("UPDATE users SET password = ?")->execute([$hash]);
echo "<h2>✅ รีเซ็ตรหัสผ่านทุกบัญชีเป็น 1234 เรียบร้อยแล้ว!</h2>";
echo "<a href='login.php'>คลิกที่นี่เพื่อไปหน้าล็อกอิน</a>";
?>