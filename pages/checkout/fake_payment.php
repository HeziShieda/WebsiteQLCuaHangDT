<?php
session_start();
include '../includes/db.php';

$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    header("Location: ../login.php");
    exit();
}

// Tạo order tạm (chưa thanh toán)
$order_id = rand(1000, 9999); // ID giả lập, hoặc insert vào DB nếu muốn

// Link khi quét QR → báo thanh toán thành công
$pay_url = "http://localhost/fake_payment_success.php?order_id=$order_id";

// Tạo QR code
include "../includes/phpqrcode/qrlib.php";

QRcode::png($pay_url, false, QR_ECLEVEL_L, 5);

?>
