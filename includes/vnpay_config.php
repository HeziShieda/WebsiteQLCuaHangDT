<?php
// vnpay_config.php
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html"; // URL test của VNPay
$vnp_Returnurl = "http://localhost/your_project/payment_return.php"; // URL trả về sau khi thanh toán
$vnp_TmnCode = "YOUR_TMNCODE"; // Mã website của mày (sandbox lấy trên cổng quản trị VNPay)
$vnp_HashSecret = "YOUR_HASHSECRET"; // Chuỗi bí mật dùng tạo checksum
