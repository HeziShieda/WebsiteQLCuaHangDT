<?php
session_start();
include '../includes/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    die("Bạn cần đăng nhập để thực hiện thao tác này.");
}

$user_id = $_SESSION['user_id'];
$order_id = $_POST['order_id'];

// Kiểm tra xem đơn hàng có thuộc về user không
$sql_check = "SELECT id FROM orders WHERE id = ? AND user_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $order_id, $user_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows === 0) {
    die("Không tìm thấy đơn hàng hoặc bạn không có quyền hủy đơn này.");
}

// Cập nhật trạng thái đơn hàng thành "canceled"
$sql_update = "UPDATE orders SET status = 'canceled' WHERE id = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("i", $order_id);

if ($stmt_update->execute()) {
    echo "Đơn hàng đã bị hủy thành công!";
} else {
    echo "Lỗi khi hủy đơn hàng.";
}

// Quay lại trang chi tiết đơn hàng
header("Location: order_details.php?order_id=" . $order_id);
exit();
?>
