<?php
session_start();
include '../includes/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = $success_message = "";

// Lấy thông tin người dùng
$sql = "SELECT email, phone, address FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Lấy giỏ hàng từ database
$cart_items = [];
$total_price = 0;

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
    $total_price += $row['price'] * $row['quantity'];
}

// Xử lý đặt hàng
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $payment_method = $_POST['payment_method'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    if (!$email || !$phone || !$address) {
        $error_message = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        // Lưu vào bảng orders
        $order_sql = "INSERT INTO orders (user_id, order_date, total_price, status, payment_method) 
              VALUES (?, NOW(), ?, 'Pending', ?)";
        $stmt = $conn->prepare($order_sql);
        $stmt->bind_param("ids", $user_id, $total_price, $payment_method);


        if ($stmt->execute()) {
            $order_id = $stmt->insert_id; // Lấy ID đơn hàng vừa tạo

            // Lưu chi tiết đơn hàng
            foreach ($cart_items as $item) {
                $detail_sql = "INSERT INTO orderdetails (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($detail_sql);
                $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
                $stmt->execute();
            }

            // Xóa giỏ hàng

            if ($user_id) {
                $conn->query("DELETE FROM cart WHERE user_id = $user_id");

                $success_message = "Đặt hàng thành công! Mã đơn hàng: #" . $order_id;
                $cart_items = [];
                $total_price = 0;
            } else {
                unset($_SESSION['cart']);
            }

            
        } else {
            $error_message = "Lỗi khi đặt hàng!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Thanh toán</h2>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
        <?php elseif ($error_message): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>

        <?php if (!empty($cart_items)): ?>
            <h4>Thông tin giỏ hàng:</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Tổng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= number_format($item['price'], 0, ',', '.') ?> đ</td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?> đ</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h4 class="text-end">Tổng cộng: <strong><?= number_format($total_price, 0, ',', '.') ?> đ</strong></h4>

            <h4 class="mt-4">Thông tin nhận hàng:</h4>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email:</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại:</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ:</label>
                        <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phương thức thanh toán:</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="COD">Thanh toán khi nhận hàng (COD)</option>
                            <option value="Bank Transfer">Chuyển khoản ngân hàng</option>
                            <option value="E-Wallet">Ví điện tử (Momo, ZaloPay,...)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Xác nhận đặt hàng</button>
                    <a href="cart.php" class="btn btn-secondary">Quay lại giỏ hàng</a>
                </form>
        <?php else: ?>
            <p>Giỏ hàng trống. <a href="../index.php">Tiếp tục mua sắm</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
