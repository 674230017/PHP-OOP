<?php
require_once 'db.php';
require_once 'auth.php';
checkAuth();

$repairId = intval($_GET['id'] ?? 0);

if (!$repairId) {
    header("Location: dashboard.php");
    exit;
}

// 1. ดึงข้อมูลใบแจ้งซ่อม
$stmt = $pdo->prepare("SELECT r.*, u.fullname as reporter_name, u.email as reporter_email, t.fullname as tech_name 
                       FROM repair_requests r 
                       LEFT JOIN users u ON r.user_id = u.id 
                       LEFT JOIN users t ON r.assigned_to = t.id 
                       WHERE r.id = ?");
$stmt->execute([$repairId]);
$repair = $stmt->fetch();

if (!$repair) {
    die("ไม่พบรายการแจ้งซ่อมนี้");
}

// 2. ดึงรูปภาพประกอบ
$stmtImg = $pdo->prepare("SELECT * FROM repair_images WHERE repair_id = ?");
$stmtImg->execute([$repairId]);
$images = $stmtImg->fetchAll();

// 3. ดึงความคิดเห็น
$stmtCom = $pdo->prepare("SELECT c.*, u.fullname, u.role FROM comments c JOIN users u ON c.user_id = u.id WHERE c.repair_id = ? ORDER BY c.id ASC");
$stmtCom->execute([$repairId]);
$comments = $stmtCom->fetchAll();

// 4. ดึงรายการช่างสำหรับ Admin
$technicians = [];
if ($_SESSION['role'] === 'admin') {
    $stmtTech = $pdo->query("SELECT id, fullname FROM users WHERE role = 'technician'");
    $technicians = $stmtTech->fetchAll();
}

// --- การจัดการ Form Actions ---

// Action A: Admin มอบหมายงาน
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_assign']) && $_SESSION['role'] === 'admin') {
    $techId = intval($_POST['assigned_to']);
    $dueDate = $_POST['due_date'];

    $stmtUp = $pdo->prepare("UPDATE repair_requests SET assigned_to = ?, due_date = ?, status = 'accepted' WHERE id = ?");
    $stmtUp->execute([$techId, $dueDate, $repairId]);

    setFlashMessage('success', 'สำเร็จ!', 'มอบหมายช่างผู้รับผิดชอบเรียบร้อยแล้ว');
    header("Location: repair_detail.php?id=" . $repairId);
    exit;
}

// Action B: Technician เริ่มดำเนินการ / ซ่อมเสร็จ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_status']) && ($_SESSION['role'] === 'technician' || $_SESSION['role'] === 'admin')) {
    $newStatus = $_POST['status'];
    $cost = isset($_POST['cost']) && $_POST['cost'] !== '' ? floatval($_POST['cost']) : null;
    $result = trim($_POST['result'] ?? '');

    if ($newStatus === 'completed') {
        $completionDate = date('Y-m-d');
        $stmtUp = $pdo->prepare("UPDATE repair_requests SET status = ?, cost = ?, result = ?, completion_date = ? WHERE id = ?");
        $stmtUp->execute([$newStatus, $cost, $result, $completionDate, $repairId]);

        // จัดการรูปภาพหลังซ่อม
        if (isset($_FILES['after_image']) && $_FILES['after_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['after_image'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $targetPath = 'uploads/repairs/' . uniqid('after_', true) . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $stmtImg = $pdo->prepare("INSERT INTO repair_images (repair_id, image_type, image_path) VALUES (?, 'after', ?)");
                $stmtImg->execute([$repairId, $targetPath]);
            }
        }
    } else {
        $stmtUp = $pdo->prepare("UPDATE repair_requests SET status = ? WHERE id = ?");
        $stmtUp->execute([$newStatus, $repairId]);
    }

    setFlashMessage('success', 'สำเร็จ!', 'อัปเดตสถานะการซ่อมเรียบร้อยแล้ว');
    header("Location: repair_detail.php?id=" . $repairId);
    exit;
}

// Action C: เพิ่มความคิดเห็น
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_comment'])) {
    $commentText = trim($_POST['comment'] ?? '');
    if (!empty($commentText)) {
        $stmtAdd = $pdo->prepare("INSERT INTO comments (repair_id, user_id, comment) VALUES (?, ?, ?)");
        $stmtAdd->execute([$repairId, $_SESSION['user_id'], $commentText]);
        setFlashMessage('success', 'สำเร็จ', 'เพิ่มความคิดเห็นเรียบร้อย');
        header("Location: repair_detail.php?id=" . $repairId);
        exit;
    }
}

include 'header.php';
?>

<!-- Header Actions -->
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <a href="dashboard.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> กลับ Dashboard</a>
    <button onclick="window.print();" class="btn btn-success"><i class="fa-solid fa-print"></i> พิมพ์ใบแจ้งซ่อม (PDF)</button>
</div>

<!-- ใบแจ้งซ่อม Document -->
<div class="card p-4 mb-4">
    <div class="row border-bottom pb-3 mb-3">
        <div class="col-8">
            <h3 class="text-primary mb-1"><i class="fa-solid fa-file-invoice me-2"></i>ใบแจ้งซ่อมภายในโรงเรียน</h3>
            <p class="text-muted mb-0">รหัสอ้างอิง: <strong><?= htmlspecialchars($repair['request_no']) ?></strong></p>
        </div>
        <div class="col-4 text-end">
            <p class="mb-1"><strong>วันที่แจ้ง:</strong> <?= date('d/m/Y H:i', strtotime($repair['created_at'])) ?></p>
            <div>
                <?php
                $statusMap = [
                    'pending' => ['bg-warning text-dark', 'รอรับเรื่อง'],
                    'accepted' => ['bg-info text-white', 'รับเรื่องแล้ว'],
                    'in_progress' => ['bg-primary text-white', 'กำลังดำเนินการ'],
                    'completed' => ['bg-success text-white', 'ซ่อมเสร็จสิ้น']
                ];
                $st = $statusMap[$repair['status']];
                ?>
                <span class="badge fs-6 <?= $st[0] ?>"><?= $st[1] ?></span>
            </div>
        </div>
    </div>

    <!-- ข้อมูลรายละเอียด -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <p><strong>ผู้แจ้งซ่อม:</strong> <?= htmlspecialchars($repair['reporter_name']) ?></p>
            <p><strong>สถานที่:</strong> <?= htmlspecialchars($repair['building']) ?> (ห้อง <?= htmlspecialchars($repair['room']) ?>)</p>
            <p><strong>ประเภทปัญหา:</strong> <?= htmlspecialchars($repair['problem_type']) ?></p>
        </div>
        <div class="col-md-6">
            <p><strong>ความเร่งด่วน:</strong> <?= strtoupper($repair['urgency']) ?></p>
            <p><strong>ผู้รับผิดชอบซ่อม:</strong> <?= htmlspecialchars($repair['tech_name'] ?? 'ยังไม่ได้กำหนด') ?></p>
            <p><strong>กำหนดเสร็จ:</strong> <?= $repair['due_date'] ? date('d/m/Y', strtotime($repair['due_date'])) : '-' ?></p>
        </div>
        <div class="col-12 bg-light p-3 rounded">
            <h6><strong>รายละเอียดปัญหา:</strong></h6>
            <p class="mb-0"><?= nl2br(htmlspecialchars($repair['description'])) ?></p>
        </div>
        <?php if ($repair['status'] === 'completed'): ?>
            <div class="col-12 bg-success-subtle p-3 rounded border border-success">
                <h6 class="text-success"><strong>ผลการซ่อมแซม:</strong></h6>
                <p class="mb-1"><?= nl2br(htmlspecialchars($repair['result'] ?? 'ซ่อมแซมเสร็จสิ้นตามปกติ')) ?></p>
                <p class="mb-0"><strong>ค่าใช้จ่ายในการซ่อม:</strong> <?= number_format($repair['cost'] ?? 0, 2) ?> บาท | <strong>วันที่ซ่อมเสร็จ:</strong> <?= date('d/m/Y', strtotime($repair['completion_date'])) ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- รูปภาพประกอบ -->
    <div class="mb-4">
        <h5><i class="fa-solid fa-images me-2"></i>รูปภาพประกอบ</h5>
        <div class="row g-3">
            <?php if (empty($images)): ?>
                <p class="text-muted ps-3">ไม่มีรูปภาพประกอบ</p>
            <?php else: ?>
                <?php foreach ($images as $img): ?>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <img src="<?= htmlspecialchars($img['image_path']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                            <div class="card-footer text-center bg-light">
                                <span class="badge bg-secondary"><?= $img['image_type'] === 'before' ? 'ภาพก่อนซ่อม' : 'ภาพหลังซ่อม' ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ส่วนการจัดการตามบทบาท (No Print) -->
<div class="row g-4 no-print">
    <!-- Admin Assign Work -->
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-user-gear me-2"></i>[สำหรับ Admin] มอบหมายงาน</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action_assign" value="1">
                        <div class="mb-3">
                            <label class="form-label">เลือกช่างผู้รับผิดชอบ</label>
                            <select name="assigned_to" class="form-select" required>
                                <option value="">-- เลือกช่าง --</option>
                                <?php foreach ($technicians as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= $repair['assigned_to'] == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['fullname']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">กำหนดวันเสร็จ (Due Date)</label>
                            <input type="date" name="due_date" class="form-control" value="<?= $repair['due_date'] ?>" required>
                        </div>
                        <button type="submit" class="btn btn-dark w-100"><i class="fa-solid fa-check me-1"></i> ยืนยันมอบหมายงาน</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Technician Status Update -->
    <?php if ($_SESSION['role'] === 'technician' || $_SESSION['role'] === 'admin'): ?>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fa-solid fa-pen-ruler me-2"></i>[สำหรับช่าง/Admin] อัปเดตสถานะงานซ่อม</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action_status" value="1">
                        <div class="mb-3">
                            <label class="form-label">เปลี่ยนสถานะ</label>
                            <select name="status" class="form-select" id="statusSelect" required>
                                <option value="in_progress" <?= $repair['status'] === 'in_progress' ? 'selected' : '' ?>>กำลังดำเนินการซ่อม</option>
                                <option value="completed" <?= $repair['status'] === 'completed' ? 'selected' : '' ?>>ซ่อมแซมเสร็จสิ้น</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">บันทึกผลการซ่อม / รายละเอียดเพิ่มเติม</label>
                            <textarea name="result" class="form-control" rows="2" placeholder="ระบุสิ่งที่แก้ไข หรืออุปกรณ์ที่เปลี่ยน..."><?= htmlspecialchars($repair['result'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ค่าใช้จ่ายในการซ่อม (บาท)</label>
                            <input type="number" step="0.01" name="cost" class="form-control" value="<?= $repair['cost'] ?>" placeholder="0.00">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">แนบรูปภาพหลังซ่อมเสร็จ</label>
                            <input type="file" name="after_image" class="form-control" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-floppy-disk me-1"></i> บันทึกการอัปเดต</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Comment Thread -->
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fa-solid fa-comments me-2"></i>ความคิดเห็นและข้อความสอบถาม (<?= count($comments) ?>)</h5>
            </div>
            <div class="card-body">
                <div class="mb-4" style="max-height: 300px; overflow-y: auto;">
                    <?php if (empty($comments)): ?>
                        <p class="text-muted">ยังไม่มีความคิดเห็น</p>
                    <?php else: ?>
                        <?php foreach ($comments as $c): ?>
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <strong><?= htmlspecialchars($c['fullname']) ?> <span class="badge bg-secondary"><?= $c['role'] ?></span></strong>
                                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></small>
                                </div>
                                <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <form method="POST">
                    <input type="hidden" name="action_comment" value="1">
                    <div class="input-group">
                        <input type="text" name="comment" class="form-control" placeholder="พิมพ์ข้อความสอบถาม หรือความคิดเห็นเพิ่มเติม..." required>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i> ส่งข้อความ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
