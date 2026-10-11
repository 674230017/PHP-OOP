<?php
session_start();

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/RepairService.php';

$database = new Database();
$db = $database->getConnection();
$service = new RepairService($db);

$message = "";

// 1. จัดการการเข้าสู่ระบบ (Login)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    $user = $service->login($username, $password);
    if ($user) {
        $_SESSION['user'] = serialize($user);
    } else {
        $message = "❌ Username หรือ Password ไม่ถูกต้อง";
    }
}

// 2. จัดการการออกจากระบบ (Logout)
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['user']);
    session_destroy();
    header("Location: index.php");
    exit;
}

// ดึงข้อมูล User ปัจจุบันจาก Session
$currentUser = isset($_SESSION['user']) ? unserialize($_SESSION['user']) : null;

// 3. จัดการ Form Actions สำหรับผู้ใช้ที่ล็อกอินแล้ว
if ($currentUser && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create_request') {
        if ($service->createRequest($currentUser->id, $_POST['title'], $_POST['description'])) {
            $message = "✅ ส่งใบแจ้งซ่อมสำเร็จแล้ว";
        } else {
            $message = "⚠️ กรุณากรอกข้อมูลให้ครบถ้วน";
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'update_status' && $currentUser->isAdmin()) {
        if ($service->updateStatus((int)$_POST['request_id'], $_POST['status'])) {
            $message = "✅ อัปเดตสถานะการแจ้งซ่อมเรียบร้อย";
        } else {
            $message = "⚠️ เกิดข้อผิดพลาดในการอัปเดตสถานะ";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบแจ้งซ่อมออนไลน์ (PHP OOP)</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        h2, h3 { color: #333; margin-top: 0; }
        .alert { padding: 12px 15px; background: #e9ecef; border-left: 4px solid #007bff; margin-bottom: 20px; border-radius: 4px; font-weight: bold; }
        .btn { padding: 9px 16px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-danger { background-color: #dc3545; text-decoration: none; display: inline-block; padding: 6px 12px; color: white; border-radius: 4px; font-size: 14px; }
        input[type="text"], input[type="password"], textarea, select { width: 100%; padding: 10px; margin: 8px 0 16px; border: 1px solid #ccc; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #dee2e6; padding: 12px; text-align: left; }
        th { background-color: #007bff; color: white; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        .badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; }
        .status-PENDING { background: #fff3cd; color: #856404; }
        .status-IN_PROGRESS { background: #cce5ff; color: #004085; }
        .status-COMPLETED { background: #d4edda; color: #155724; }
        .status-CANCELLED { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<div class="container">
    <h2>🛠️ ระบบแจ้งซ่อมออนไลน์ (PHP OOP)</h2>

    <?php if ($message): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (!$currentUser): ?>
        <!-- ฟอร์มเข้าสู่ระบบ -->
        <h3>เข้าสู่ระบบ</h3>
        <form method="POST">
            <input type="hidden" name="action" value="login">
            <label>Username:</label>
            <input type="text" name="username" required placeholder="กรอกชื่อผู้ใช้">
            
            <label>Password:</label>
            <input type="password" name="password" required placeholder="กรอกรหัสผ่าน">
            
            <button type="submit" class="btn">Login</button>
        </form>
        <p><small style="color: #6c757d;">* บัญชีทดสอบระบบ: admin / 1234 (ผู้ดูแล) หรือ john / 1234 (ผู้ใช้ทั่วไป)</small></p>

    <?php else: ?>
        <!-- หน้าต่างการทำงานของผู้ใช้ที่ล็อกอินแล้ว -->
        <p>
            ผู้ใช้งาน: <strong><?= htmlspecialchars($currentUser->username) ?></strong> 
            (สิทธิ์: <span style="color:#007bff;"><?= htmlspecialchars($currentUser->role) ?></span>) 
            | <a href="?action=logout" class="btn-danger">ออกจากระบบ</a>
        </p>
        <hr>

        <?php if (!$currentUser->isAdmin()): ?>
            <!-- เมนูสำหรับ User: ส่งใบแจ้งซ่อม -->
            <h3>ส่งรายการแจ้งซ่อมใหม่</h3>
            <form method="POST">
                <input type="hidden" name="action" value="create_request">
                
                <label>หัวข้อปัญหา:</label>
                <input type="text" name="title" required placeholder="เช่น เครื่องปรับอากาศไม่เย็น">
                
                <label>รายละเอียดปัญหา:</label>
                <textarea name="description" rows="3" required placeholder="ระบุรายละเอียดปัญหาสั้นๆ..."></textarea>
                
                <button type="submit" class="btn">ส่งแจ้งซ่อม</button>
            </form>

            <h3 style="margin-top: 30px;">ประวัติการแจ้งซ่อมของฉัน</h3>
            <?php $requests = $service->getRequestsByUser($currentUser->id); ?>
        <?php else: ?>
            <!-- เมนูสำหรับ Admin: จัดการรายการแจ้งซ่อมทั้งหมด -->
            <h3>รายการแจ้งซ่อมทั้งหมดในระบบ</h3>
            <?php $requests = $service->getAllRequests(); ?>
        <?php endif; ?>

        <!-- ตารางแสดงผลรายการแจ้งซ่อม -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>หัวข้อ</th>
                    <th>รายละเอียด</th>
                    <th>ผู้แจ้ง</th>
                    <th>สถานะ</th>
                    <th>วันที่แจ้ง</th>
                    <?php if ($currentUser->isAdmin()): ?><th>จัดการสถานะ</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                    <tr>
                        <td><?= $req['request_id'] ?></td>
                        <td><?= htmlspecialchars($req['title']) ?></td>
                        <td><?= htmlspecialchars($req['description']) ?></td>
                        <td><?= htmlspecialchars($req['username']) ?></td>
                        <td>
                            <span class="badge status-<?= $req['status'] ?>">
                                <?= $req['status'] ?>
                            </span>
                        </td>
                        <td><?= date('Y-m-d H:i', strtotime($req['created_at'])) ?></td>
                        <?php if ($currentUser->isAdmin()): ?>
                            <td>
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="request_id" value="<?= $req['request_id'] ?>">
                                    <select name="status" onchange="this.form.submit()" style="margin:0; padding: 4px;">
                                        <option value="PENDING" <?= $req['status'] === 'PENDING' ? 'selected' : '' ?>>PENDING</option>
                                        <option value="IN_PROGRESS" <?= $req['status'] === 'IN_PROGRESS' ? 'selected' : '' ?>>IN_PROGRESS</option>
                                        <option value="COMPLETED" <?= $req['status'] === 'COMPLETED' ? 'selected' : '' ?>>COMPLETED</option>
                                        <option value="CANCELLED" <?= $req['status'] === 'CANCELLED' ? 'selected' : '' ?>>CANCELLED</option>
                                    </select>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="<?= $currentUser->isAdmin() ? 7 : 6 ?>" style="text-align:center; color: #888;">
                            ไม่พบรายการแจ้งซ่อมในระบบ
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>