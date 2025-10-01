<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy giỏ hàng từ DB
$cart_sql = "SELECT c.product_id, c.quantity, p.name, p.price, p.image 
             FROM cart c 
             JOIN products p ON c.product_id = p.id 
             WHERE c.user_id = ?";
$stmt = $conn->prepare($cart_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

if (empty($cart_items)) {
    echo "<script>alert('Giỏ hàng trống!'); window.location.href='../cart/cart.php';</script>";
    exit();
}

// Xử lý submit
$selected_items = [];
$total_price = 0;
$qr_code_url = "";
$payment_method = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $payment_method = $_POST['payment_method'];
    $selected_items_ids = $_POST['products'] ?? [];

    if (empty($selected_items_ids)) {
        $error_message = "Vui lòng chọn sản phẩm muốn thanh toán!";
    } else {
        foreach ($cart_items as $item) {
            if (in_array($item['product_id'], $selected_items_ids)) {
                $selected_items[] = $item;
                $total_price += $item['price'] * $item['quantity'];
            }
        }

        // Lưu session chỉ với các sản phẩm được chọn
        if (!empty($selected_items)) {
            $_SESSION['checkout_info'] = [
                'cart_items' => $selected_items,
                'total_price' => $total_price,
                'payment_method' => $payment_method
            ];
        }

        if ($payment_method == "COD") {
            // Insert đơn hàng COD
            $order_sql = "INSERT INTO orders (user_id, order_date, total_price, status, payment_method) 
                          VALUES (?, NOW(), ?, 'Pending', 'COD')";
            $stmt = $conn->prepare($order_sql);
            $stmt->bind_param("id", $user_id, $total_price);
            $stmt->execute();
            $order_id = $stmt->insert_id;

            foreach ($selected_items as $item) {
                $detail_sql = "INSERT INTO orderdetails (order_id, product_id, quantity, price) 
                               VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($detail_sql);
                $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $stmt->execute();
            }

            // Xóa sản phẩm đã thanh toán khỏi giỏ hàng
            foreach ($selected_items as $item) {
                $conn->query("DELETE FROM cart WHERE user_id = $user_id AND product_id = ".$item['product_id']);
            }

            $success_message = "Đặt hàng thành công! Mã đơn hàng: #$order_id";

        } else {
            // Fake QR code ảnh tĩnh
            $qr_code_url = "../uploads/QR/QR.png";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h2>Thanh toán</h2>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?= $success_message ?></div>
    <?php elseif (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>

    <form method="post" id="checkoutForm">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Chọn sản phẩm muốn thanh toán</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Chọn</th>
                            <th>Ảnh</th>
                            <th>Sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Giá</th>
                            <th>Tạm tính</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="product-checkbox" name="products[]" value="<?= $item['product_id'] ?>" data-price="<?= $item['price'] ?>" data-quantity="<?= $item['quantity'] ?>">
                            </td>
                            <td><img src="../<?= $item['image'] ?>" width="60"></td>
                            <td><?= $item['name'] ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= number_format($item['price'],0,',','.') ?> đ</td>
                            <td><?= number_format($item['price']*$item['quantity'],0,',','.') ?> đ</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <h5 class="text-end">Tổng cộng: <span id="totalPrice">0 đ</span></h5>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Phương thức thanh toán</h5>
            </div>
            <div class="card-body">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="payment_method" value="COD" checked>
                    <label class="form-check-label">Thanh toán khi nhận hàng (COD)</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="payment_method" value="Bank Transfer">
                    <label class="form-check-label">Chuyển khoản ngân hàng (hiển thị QR)</label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="payment_method" value="E-Wallet">
                    <label class="form-check-label">Ví điện tử (Momo, ZaloPay, ...)</label>
                </div>
                <button type="submit" class="btn btn-success w-100">Xác nhận thanh toán</button>
            </div>
        </div>
    </form>

    <?php if (!empty($qr_code_url)): ?>
        <div class="card shadow-sm mb-4 text-center">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Quét QR để thanh toán</h5>
            </div>
            <div class="card-body">
                <img src="<?= $qr_code_url ?>" alt="QR Code" class="img-fluid" width="200" height="200">
                <a href="../pages/checkout/fake_payment_success.php">
                <p class="mt-2" style="cursor:pointer; color:blue; text-decoration:underline;"
                   onclick="window.location.href='../pages/checkout/fake_payment_success.php'">
                    Thanh toán <?= number_format($total_price,0,',','.') ?> đ
                </p>
                </a>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
const checkboxes = document.querySelectorAll('.product-checkbox');
const totalPriceEl = document.getElementById('totalPrice');

function updateTotal() {
    let total = 0;
    checkboxes.forEach(cb => {
        if (cb.checked) {
            const price = parseFloat(cb.dataset.price);
            const qty = parseInt(cb.dataset.quantity);
            total += price * qty;
        }
    });
    totalPriceEl.textContent = total.toLocaleString('vi-VN') + ' đ';
}

checkboxes.forEach(cb => cb.addEventListener('change', updateTotal));
updateTotal();
</script>
</body>
</html>
