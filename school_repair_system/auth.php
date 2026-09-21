<?php
// auth.php - มิดเดิลแวร์สำหรับตรวจสอบการเข้าสู่ระบบและสิทธิ์การใช้งาน
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAuth(array $allowedRoles = []) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    if (!empty($allowedRoles) && !in_array($_SESSION['role'], $allowedRoles)) {
        http_response_code(403);
        die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
            <h1 style='color:#e74c3c;'>403 Forbidden</h1>
            <p>คุณไม่มีสิทธิ์เข้าถึงหน้านี้</p>
            <a href='dashboard.php' style='color:#3498db;'>กลับสู่หน้าหลัก Dashboard</a>
        </div>");
    }
}

function setFlashMessage($type, $title, $text) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'title' => $title,
        'text' => $text
    ];
}
?>
