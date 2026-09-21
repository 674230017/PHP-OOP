<?php
require_once 'db.php';
require_once 'auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];
            
            setFlashMessage('success', 'สำเร็จ!', 'เข้าสู่ระบบเรียบร้อยแล้ว');
            header("Location: dashboard.php");
            exit;
        } else {
            $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
        }
    } else {
        $error = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    }
}

include 'header.php';
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0"><i class="fa-solid fa-wrench me-2"></i>เข้าสู่ระบบแจ้งซ่อมโรงเรียน</h4>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label class="form-label">ชื่อผู้ใช้งาน (Username)</label>
                        <input type="text" name="username" class="form-control" placeholder="กรอก Username" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รหัสผ่าน (Password)</label>
                        <input type="password" name="password" class="form-control" placeholder="กรอก Password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2"><i class="fa-solid fa-right-to-bracket me-1"></i> เข้าสู่ระบบ</button>
                </form>

                <hr class="my-4">
                <div class="bg-light p-3 rounded">
                    <h6><i class="fa-solid fa-info-circle me-1"></i> บัญชีทดสอบสำหรับเข้าใช้งาน:</h6>
                    <ul class="mb-0 small ps-3">
                        <li><strong>Admin:</strong> admin / 1234</li>
                        <li><strong>Teacher:</strong> teacher1 / 1234</li>
                        <li><strong>Technician:</strong> tech1 / 1234</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
