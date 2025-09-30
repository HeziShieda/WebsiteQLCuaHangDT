<?php
include '../includes/db.php';

// Truy vấn tổng doanh thu
$revenueQuery = $conn->query("SELECT SUM(total_price) AS revenue FROM orders WHERE status = 'Completed'");
$revenue = $revenueQuery->fetch_assoc()['revenue'] ?? 0;

// Truy vấn tổng số đơn hàng
$ordersQuery = $conn->query("SELECT COUNT(*) AS total_orders FROM orders");
$totalOrders = $ordersQuery->fetch_assoc()['total_orders'] ?? 0;

// Truy vấn số sản phẩm đã bán
$productsQuery = $conn->query("SELECT SUM(quantity) AS total_products FROM orderdetails");
$totalProducts = $productsQuery->fetch_assoc()['total_products'] ?? 0;

// Trả về JSON
echo json_encode([
    'revenue' => $revenue,
    'total_orders' => $totalOrders,
    'total_products' => $totalProducts
]);
?>
