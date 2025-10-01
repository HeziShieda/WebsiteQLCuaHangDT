<?php
session_start();
include '../../includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../index.php");
    exit();
}

// Lấy thông tin từ session
$cart_items = $_SESSION['checkout_info']['cart_items'] ?? [];
$amount = $_SESSION['checkout_info']['total_price'] ?? 0;
$method = $_SESSION['checkout_info']['payment_method'] ?? 'Unknown';

// Insert đơn hàng
if (!empty($cart_items) && $amount > 0) {
    $order_sql = "INSERT INTO orders (user_id, order_date, total_price, status, payment_method)
                  VALUES (?, NOW(), ?, 'Paid', ?)";
    $stmt = $conn->prepare($order_sql);
    $stmt->bind_param("ids", $user_id, $amount, $method);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    foreach ($cart_items as $item) {
        $detail_sql = "INSERT INTO orderdetails (order_id, product_id, quantity, price)
                       VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($detail_sql);
        $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
        $stmt->execute();

        // Xóa sản phẩm khỏi cart
        $conn->query("DELETE FROM cart WHERE user_id = $user_id AND product_id = ".$item['product_id']);
    }

    // Xóa session tạm
    unset($_SESSION['checkout_info']);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh toán thành công</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta http-equiv="refresh" content="3;url=../../index.php">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-sm text-center">
        <div class="card-header bg-success text-white">
            <h3>Thanh toán thành công!</h3>
        </div>
        <div class="card-body">
            <p>Phương thức: <strong><?= htmlspecialchars($method) ?></strong></p>
            <p>Tổng tiền: <strong><?= number_format($amount,0,',','.') ?> đ</strong></p>
            <p>Bạn sẽ được chuyển về trang chủ trong <strong>3 giây</strong>...</p>
            <a href="../../index.php" class="btn btn-primary mt-2">Về trang chủ ngay</a>
        </div>
    </div>
</div>
</body>
</html>
