<?php
require_once __DIR__ . '/config/Database.php';

try {
    $db = (new Database())->getConnection();
    $stmt = $db->query("SELECT * FROM users");
    $users = $stmt->fetchAll();

    echo "<h2>🔍 ตรวจสอบข้อมูลในตาราง users:</h2>";
    if (count($users) > 0) {
        echo "<table border='1' cellpadding='8' style='border-collapse:collapse;'>";
        echo "<tr style='background:#f0f0f0;'><th>ID</th><th>Username</th><th>Password</th><th>Role</th></tr>";
        foreach ($users as $u) {
            echo "<tr>";
            echo "<td>{$u['user_id']}</td>";
            echo "<td><b>{$u['username']}</b></td>";
            echo "<td>{$u['password']}</td>";
            echo "<td>{$u['role']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p style='color:green;'>✅ เชื่อมต่อและอ่านข้อมูลสำเร็จ!</p>";
    } else {
        echo "<p style='color:red;'>❌ ไม่พบข้อมูลในตาราง users (ตารางว่างเปล่า)</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ เกิดข้อผิดพลาด: " . $e->getMessage() . "</p>";
}
?>