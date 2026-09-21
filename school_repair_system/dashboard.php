<?php
require_once 'db.php';
require_once 'auth.php';
checkAuth();

// ดึงข้อมูลสถิติ
$sqlStats = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted_count,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_count,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count
            FROM repair_requests";

if ($_SESSION['role'] === 'teacher') {
    $sqlStats .= " WHERE user_id = " . intval($_SESSION['user_id']);
} elseif ($_SESSION['role'] === 'technician') {
    $sqlStats .= " WHERE assigned_to = " . intval($_SESSION['user_id']) . " OR status = 'pending'";
}

$stmtStats = $pdo->query($sqlStats);
$stats = $stmtStats->fetch();

// ดึงรายการแจ้งซ่อม
$sqlList = "SELECT r.*, u.fullname as reporter_name, t.fullname as tech_name 
            FROM repair_requests r 
            LEFT JOIN users u ON r.user_id = u.id 
            LEFT JOIN users t ON r.assigned_to = t.id";

if ($_SESSION['role'] === 'teacher') {
    $sqlList .= " WHERE r.user_id = " . intval($_SESSION['user_id']);
} elseif ($_SESSION['role'] === 'technician') {
    $sqlList .= " WHERE r.assigned_to = " . intval($_SESSION['user_id']) . " OR r.status = 'pending'";
}

$sqlList .= " ORDER BY r.id DESC";
$stmtList = $pdo->query($sqlList);
$repairs = $stmtList->fetchAll();

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fa-solid fa-gauge me-2"></i>Dashboard สรุปสถิติงานซ่อม</h2>
    <div>
        <a href="export_csv.php" class="btn btn-success me-2"><i class="fa-solid fa-file-csv me-1"></i> ส่งออก CSV</a>
        <?php if ($_SESSION['role'] === 'teacher' || $_SESSION['role'] === 'admin'): ?>
            <a href="add_repair.php" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> แจ้งซ่อมใหม่</a>
        <?php endif; ?>
    </div>
</div>

<!-- Cards สถิติ -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">งานทั้งหมด</h6>
                    <h2 class="mb-0 font-weight-bold"><?= $stats['total'] ?></h2>
                </div>
                <i class="fa-solid fa-list-check fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">รอดำเนินการ</h6>
                    <h2 class="mb-0"><?= $stats['pending_count'] ?></h2>
                </div>
                <i class="fa-solid fa-clock fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">กำลังดำเนินการ</h6>
                    <h2 class="mb-0"><?= $stats['in_progress_count'] + $stats['accepted_count'] ?></h2>
                </div>
                <i class="fa-solid fa-screwdriver-wrench fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">เสร็จเรียบร้อย</h6>
                    <h2 class="mb-0"><?= $stats['completed_count'] ?></h2>
                </div>
                <i class="fa-solid fa-circle-check fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<!-- ตารางรายการแจ้งซ่อม -->
<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="fa-solid fa-list me-2"></i>รายการแจ้งซ่อม</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>รหัสรายการ</th>
                        <th>ผู้แจ้ง</th>
                        <th>อาคาร / ห้อง</th>
                        <th>ประเภท</th>
                        <th>ความเร่งด่วน</th>
                        <th>สถานะ</th>
                        <th>ช่างผู้รับผิดชอบ</th>
                        <th>วันที่แจ้ง</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($repairs)): ?>
                        <tr><td colspan="9" class="text-center py-4 text-muted">ไม่พบข้อมูลใบแจ้งซ่อม</td></tr>
                    <?php else: ?>
                        <?php foreach ($repairs as $row): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['request_no']) ?></strong></td>
                                <td><?= htmlspecialchars($row['reporter_name']) ?></td>
                                <td><?= htmlspecialchars($row['building']) ?> (<?= htmlspecialchars($row['room']) ?>)</td>
                                <td>
                                    <?php
                                    $types = [
                                        'electrical' => '⚡ ไฟฟ้า',
                                        'furniture' => '🪑 ครุภัณฑ์',
                                        'computer' => '🖥️ คอมพิวเตอร์',
                                        'aircon' => '❄️ เครื่องปรับอากาศ',
                                        'other' => '🔧 อื่นๆ'
                                    ];
                                    echo $types[$row['problem_type']] ?? $row['problem_type'];
                                    ?>
                                </td>
                                <td>
                                    <?php if ($row['urgency'] === 'high'): ?>
                                        <span class="badge bg-danger">ด่วนมาก</span>
                                    <?php elseif ($row['urgency'] === 'medium'): ?>
                                        <span class="badge bg-warning text-dark">ปานกลาง</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">ปกติ</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusMap = [
                                        'pending' => ['bg-warning text-dark', 'รอรับเรื่อง'],
                                        'accepted' => ['bg-info text-white', 'รับเรื่องแล้ว'],
                                        'in_progress' => ['bg-primary', 'กำลังดำเนินการ'],
                                        'completed' => ['bg-success', 'ซ่อมเสร็จสิ้น']
                                    ];
                                    $st = $statusMap[$row['status']];
                                    ?>
                                    <span class="badge badge-status <?= $st[0] ?>"><?= $st[1] ?></span>
                                </td>
                                <td><?= htmlspecialchars($row['tech_name'] ?? 'ยังไม่มอบหมาย') ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                                <td class="text-center">
                                    <a href="repair_detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-eye"></i> ดูรายละเอียด
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
