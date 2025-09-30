<?php
session_start();
include '../includes/db.php';

// Lấy dữ liệu từ form
$order_id = intval($_POST['order_id']);
$status = $_POST['status'];

// Cập nhật trạng thái đơn hàng
$sql = "UPDATE orders SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $order_id);

if ($stmt->execute()) {
    echo "Cập nhật trạng thái thành công!";
} else {
    echo "Lỗi khi cập nhật trạng thái.";
}

// Quay lại trang quản lý đơn hàng
header("Location: dashboard.php");
exit();
?>
