<?php
session_start();
include '../includes/db.php';

// Kiểm tra người dùng đã đăng nhập chưa
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$session_id = session_id();
$cart_items = [];

// Nếu đã đăng nhập, lấy giỏ hàng từ database
if ($user_id) {
    $sql = "SELECT p.id, p.name, p.price, c.quantity 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
}
} else {
    // Nếu chưa đăng nhập, lấy giỏ hàng từ session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $sql = "SELECT id, name, price FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($product = $result->fetch_assoc()) {
            $product['quantity'] = $quantity;
            $cart_items[] = $product;
        }
    }
}

$totalPrice = 0;
foreach ($cart_items as $item) {
    $totalPrice += $item['price'] * $item['quantity'];
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Giỏ hàng của bạn</h2>
        <?php if (empty($cart_items)): ?>
            <p>Giỏ hàng trống.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Tổng</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= number_format($item['price'], 0, ',', '.') ?> đ</td>
                            <td><form action="../process_cart.php" method="POST" class="d-inline">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="form-control d-inline w-50">
                                <button type="submit" class="btn btn-warning btn-sm">Cập nhật</button>
                            </form></td>
                            <td><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?> đ</td>
                            <td>
                            <form action="../process_cart.php" method="POST">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Xóa khỏi giỏ hàng</button>
                            </form>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <div class="d-flex justify-content-between">
        <a href="/BanDienThoai/index.php" class="btn btn-primary">Tiếp tục mua sắm</a>
        <h4>Tổng cộng: <strong><?= number_format($totalPrice, 0, ',', '.') ?> đ</strong></h4>
        <?php if (!empty($cart_items)): ?>
        <a href="checkout.php" class="btn btn-success">Thanh toán</a>
        <?php endif; ?>
        </div>
    </div>

</body>
</html>
