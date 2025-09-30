<?php
include '../includes/db.php';
include '../includes/header.php';

// Kiểm tra xem có tham số product_id không
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Sản phẩm không hợp lệ.");
}
$product_id = $_GET['id'];

// Truy vấn thông tin sản phẩm
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
if (!$product) {
    die("Sản phẩm không tồn tại.");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-5">
                <img src="../<?= htmlspecialchars($product['image']) ?>" class="img-fluid" alt="Sản phẩm">
            </div>
            <div class="col-md-7">
                <h2><?= htmlspecialchars($product['name']) ?></h2>
                <h4 class="text-danger"><?= number_format($product['price'], 0, ',', '.') ?> đ</h4>
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                <h5>Thông số kỹ thuật</h5>
                <p><?= nl2br(htmlspecialchars($product['specifications'])) ?></p>
                <form action="../process_cart.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="number" name="quantity" value="1" min="1" class="form-control w-25 d-inline">
                    <button type="submit" class="btn btn-success">Thêm vào giỏ hàng</button>
                </form>
            </div>
        </div>
    </div>
    <?php
    include '../includes/footer.php';
    ?>
</body>
</html>
