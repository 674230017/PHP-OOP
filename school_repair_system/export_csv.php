<?php
require_once 'db.php';
require_once 'auth.php';
checkAuth();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=school_repair_report_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

// UTF-8 BOM สำหรับอ่านภาษาไทยใน Excel
fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Header Column
fputcsv($output, ['รหัสใบแจ้งซ่อม', 'ผู้แจ้งซ่อม', 'อาคาร', 'ห้อง', 'ประเภทปัญหา', 'ความเร่งด่วน', 'สถานะ', 'ช่างผู้รับผิดชอบ', 'ค่าใช้จ่าย', 'วันที่แจ้ง']);

$sql = "SELECT r.request_no, u.fullname as reporter, r.building, r.room, r.problem_type, r.urgency, r.status, t.fullname as tech_name, r.cost, r.created_at 
        FROM repair_requests r 
        LEFT JOIN users u ON r.user_id = u.id 
        LEFT JOIN users t ON r.assigned_to = t.id 
        ORDER BY r.id DESC";

$stmt = $pdo->query($sql);

while ($row = $stmt->fetch()) {
    fputcsv($output, [
        $row['request_no'],
        $row['reporter'],
        $row['building'],
        $row['room'],
        $row['problem_type'],
        $row['urgency'],
        $row['status'],
        $row['tech_name'] ?? 'ยังไม่มอบหมาย',
        $row['cost'] ?? 0.00,
        $row['created_at']
    ]);
}

fclose($output);
exit;
?>
