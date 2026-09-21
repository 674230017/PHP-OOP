<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบแจ้งซ่อมภายในโรงเรียน</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Sarabun', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .badge-status {
            font-size: 0.85rem;
            padding: 0.4em 0.8em;
            border-radius: 20px;
        }
        @media print {
            .no-print { display: none !important; }
            body { background-color: #fff; }
            .card { box-shadow: none; border: 1px solid #ddd; }
        }
    </style>
</head>
<body>
<?php if (isset($_SESSION['user_id'])): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 no-print shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fa-solid fa-wrench me-2"></i>ระบบแจ้งซ่อมโรงเรียน
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="fa-solid fa-gauge me-1"></i> Dashboard</a>
                </li>
                <?php if ($_SESSION['role'] === 'teacher' || $_SESSION['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="add_repair.php"><i class="fa-solid fa-plus-circle me-1"></i> แจ้งซ่อมใหม่</a>
                </li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center text-white">
                <span class="me-3">
                    <i class="fa-solid fa-user-circle me-1"></i> 
                    <?= htmlspecialchars($_SESSION['fullname']) ?> 
                    <span class="badge bg-light text-dark ms-1"><?= strtoupper($_SESSION['role']) ?></span>
                </span>
                <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-right-from-bracket"></i> ออกจากระบบ</a>
            </div>
        </div>
    </div>
</nav>
<?php endif; ?>
<div class="container pb-5">
