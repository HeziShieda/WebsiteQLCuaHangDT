<?php
session_start();
include '../includes/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Kiểm tra có order_id không
if (!isset($_GET['order_id'])) {
    echo "Không tìm thấy đơn hàng.";
    exit();
}

$order_id = intval($_GET['order_id']);

// Lấy thông tin đơn hàng
$sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    echo "Đơn hàng không tồn tại hoặc không thuộc về bạn.";
    exit();
}

$order = $order_result->fetch_assoc();

// Lấy danh sách sản phẩm trong đơn hàng
$sql = "SELECT p.name, od.quantity, od.price 
        FROM orderdetails od
        JOIN products p ON od.product_id = p.id
        WHERE od.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$products = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Chi tiết đơn hàng #<?= $order_id ?></h2>
        <p><strong>Ngày đặt:</strong> <?= $order['order_date'] ?></p>
        <p><strong>Trạng thái:</strong> <?= htmlspecialchars($order['status']) ?></p>
        <?php if ($order['status'] !== 'Canceled' && $order['status'] !== 'Completed'): ?>
            <form action="cancel_order.php" method="POST" class="mb-3">
                <input type="hidden" name="order_id" value="<?= $order_id ?>">
                <button type="submit" class="btn btn-danger">Hủy đơn hàng</button>
            </form>
        <?php elseif ($order['status'] === 'Completed'): ?>
            <p class="text-success"><strong>Đơn hàng đã giao thành công</strong></p>
        <?php else: ?>
            <p class="text-danger"><strong>Đơn hàng đã bị hủy</strong></p>
        <?php endif; ?>
        <p><strong>Tổng tiền:</strong> <?= number_format($order['total_price'], 0, ',', '.') ?> đ</p>

        <h4>Sản phẩm trong đơn hàng:</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Giá</th>
                    <th>Tổng</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $products->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= number_format($item['price'], 0, ',', '.') ?> đ</td>
                        <td><?= number_format($item['quantity'] * $item['price'], 0, ',', '.') ?> đ</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <a href="order.php" class="btn btn-primary">Quay lại danh sách đơn hàng</a>
    </div>
</body>
</html>
