<?php
session_start();
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAUNA MART</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container-custom {
            width: 70%;
            margin: 0 auto;
        }
        .product-card {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            background: #f8f9fa;
            margin-bottom: 15px;
        }
        .product-image {
            height: 150px;
            background:transparent;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: contain; /* Đảm bảo toàn bộ ảnh hiển thị trong khung */
        }
        .navbar-brand img {
            max-width: 200;
            max-height: 100;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark p-2">
        <div class="container-fluid">
            <a class="navbar-brand" href="/BanDienThoai/index.php"><img src="/BanDienThoai/includes/Logo.png" alt="FaunaMart" alt="FaunaMart" style="height: 50px; width: auto;"></a>
            <form class="d-flex" action="index.php" method="GET">
            <input class="form-control  flex-grow-1 mx-2" type="search" name="search" placeholder="Tìm kiếm..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button class="btn btn-outline-light ms-2" type="submit">Tìm</button>
        </form>
            <div>
            <?php if (isset($_SESSION['username'])): ?>
                <?php if ($_SESSION['roles'] === 'Admin'):?>
                <a class="btn btn-light" href="/BanDienThoai/admin/dashboard.php">Dashboard</a>
                <?php endif; ?>
                <span class="text-white me-2">Xin chào, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a class="btn btn-warning" href="/BanDienThoai/logout.php">Đăng xuất</a>
                <a class="btn btn-warning" href="/BanDienThoai/pages/profile.php">Hồ sơ</a>
                <a class="btn btn-light" href="/BanDienThoai/pages/order.php">Đơn hàng</a>
            <?php else: ?>
                <a class="btn btn-outline-light" href="/BanDienThoai/login.php?tab=login">Đăng Nhập</a>
                <a class="btn btn-outline-light" href="/BanDienThoai/login.php?tab=register">Đăng Ký</a>
            <?php endif; ?>
            <a class="btn btn-light" href="/BanDienThoai/pages/cart.php">Giỏ Hàng</a>
            </div>
        </div>
    </nav>
