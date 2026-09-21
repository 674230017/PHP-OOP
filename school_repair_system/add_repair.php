<?php
require_once 'db.php';
require_once 'auth.php';
checkAuth(['teacher', 'admin']);

function generateRequestNo(PDO $pdo): string {
    $prefix = 'RP-' . date('Ym') . '-';
    $stmt = $pdo->prepare("SELECT request_no FROM repair_requests WHERE request_no LIKE :prefix ORDER BY id DESC LIMIT 1");
    $stmt->execute([':prefix' => $prefix . '%']);
    $lastRequest = $stmt->fetchColumn();

    if ($lastRequest) {
        $lastNum = (int) substr($lastRequest, -3);
        $nextNum = $lastNum + 1;
    } else {
        $nextNum = 1;
    }
    return $prefix . str_pad((string)$nextNum, 3, '0', STR_PAD_LEFT);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $building = trim($_POST['building'] ?? '');
    $room = trim($_POST['room'] ?? '');
    $problem_type = $_POST['problem_type'] ?? '';
    $urgency = $_POST['urgency'] ?? 'medium';
    $description = trim($_POST['description'] ?? '');

    if (!empty($building) && !empty($room) && !empty($problem_type) && !empty($description)) {
        $pdo->beginTransaction();
        try {
            $requestNo = generateRequestNo($pdo);
            $stmt = $pdo->prepare("INSERT INTO repair_requests (request_no, user_id, building, room, problem_type, urgency, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$requestNo, $_SESSION['user_id'], $building, $room, $problem_type, $urgency, $description]);
            $repairId = $pdo->lastInsertId();

            // จัดการอัปโหลดรูปภาพก่อนซ่อม
            if (isset($_FILES['before_image']) && $_FILES['before_image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['before_image'];
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                $mime = mime_content_type($file['tmp_name']);

                if (in_array($mime, $allowedTypes)) {
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $newFilename = uniqid('before_', true) . '.' . $ext;
                    $targetDir = 'uploads/repairs/';
                    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
                    $targetPath = $targetDir . $newFilename;

                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $stmtImg = $pdo->prepare("INSERT INTO repair_images (repair_id, image_type, image_path) VALUES (?, 'before', ?)");
                        $stmtImg->execute([$repairId, $targetPath]);
                    }
                }
            }

            $pdo->commit();
            setFlashMessage('success', 'บันทึกสำเร็จ!', 'ส่งใบแจ้งซ่อมรหัส ' . $requestNo . ' เรียบร้อยแล้ว');
            header("Location: dashboard.php");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage();
        }
    } else {
        $error = 'กรุณากรอกข้อมูลที่มีเครื่องหมาย * ให้ครบถ้วน';
    }
}

include 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white py-3">
                <h4 class="mb-0"><i class="fa-solid fa-pen-to-square me-2"></i>แบบฟอร์มแจ้งซ่อมใหม่</h4>
            </div>
            <div class="card-body p-4">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">อาคารที่พบปัญหา <span class="text-danger">*</span></label>
                            <input type="text" name="building" class="form-control" placeholder="เช่น อาคารเรียน 3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ห้อง / สถานที่ <span class="text-danger">*</span></label>
                            <input type="text" name="room" class="form-control" placeholder="เช่น ห้อง 302, ห้องน้ำชั้น 1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ประเภทปัญหา <span class="text-danger">*</span></label>
                            <select name="problem_type" class="form-select" required>
                                <option value="">-- เลือกประเภทปัญหา --</option>
                                <option value="electrical">⚡ ระบบไฟฟ้า / แสงสว่าง</option>
                                <option value="furniture">🪑 ครุภัณฑ์ / โต๊ะ เก้าอี้</option>
                                <option value="computer">🖥️ คอมพิวเตอร์ / เครือข่าย</option>
                                <option value="aircon">❄️ เครื่องปรับอากาศ</option>
                                <option value="other">🔧 อื่นๆ</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ระดับความเร่งด่วน <span class="text-danger">*</span></label>
                            <select name="urgency" class="form-select" required>
                                <option value="low">ปกติ</option>
                                <option value="medium" selected>ปานกลาง</option>
                                <option value="high">ด่วนมาก</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">รายละเอียดปัญหา <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="4" placeholder="อธิบายรายละเอียดของปัญหาที่เกิดขึ้นอย่างชัดเจน..." required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">แนบรูปภาพประกอบก่อนซ่อม (ถ้ามี)</label>
                            <input type="file" name="before_image" class="form-control" accept="image/*">
                            <small class="text-muted">รองรับไฟล์ JPG, PNG, WEBP ขนาดไม่เกิน 5 MB</small>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <a href="dashboard.php" class="btn btn-secondary me-2"><i class="fa-solid fa-arrow-left"></i> ยกเลิก</a>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> ส่งใบแจ้งซ่อม</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
