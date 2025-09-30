<?php
session_start();
include '../includes/db.php';

// Thống kê tổng doanh thu
$sql = "SELECT SUM(total_price) AS revenue FROM orders WHERE status = 'Completed'";
$revenue = $conn->query($sql)->fetch_assoc()['revenue'] ?? 0;

// Tổng số đơn hàng
$sql = "SELECT COUNT(id) AS total_orders FROM orders";
$total_orders = $conn->query($sql)->fetch_assoc()['total_orders'] ?? 0;

// Đơn hàng đang xử lý
$sql = "SELECT COUNT(id) AS pending_orders FROM orders WHERE status = 'Processing'";
$pending_orders = $conn->query($sql)->fetch_assoc()['pending_orders'] ?? 0;

// Đơn hàng bị hủy
$sql = "SELECT COUNT(id) AS canceled_orders FROM orders WHERE status = 'Canceled'";
$canceled_orders = $conn->query($sql)->fetch_assoc()['canceled_orders'] ?? 0;

// Doanh thu theo tháng
$sql = "SELECT MONTH(order_date) AS month, SUM(total_price) AS revenue 
        FROM orders WHERE status = 'Completed' 
        GROUP BY MONTH(order_date) ORDER BY month DESC LIMIT 6";
$monthly_revenue = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Top 5 sản phẩm bán chạy
$sql = "SELECT p.name, SUM(od.quantity) AS sold 
        FROM orderdetails od 
        JOIN products p ON od.product_id = p.id 
        GROUP BY od.product_id ORDER BY sold DESC LIMIT 5";
$top_products = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê - Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Thống kê quản lý</h2>
        <div class="row">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Tổng doanh thu</h5>
                        <p class="card-text"><?php echo number_format($revenue, 0, ',', '.'); ?> đ</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Tổng đơn hàng</h5>
                        <p class="card-text"><?php echo $total_orders; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Đơn hàng đang xử lý</h5>
                        <p class="card-text"><?php echo $pending_orders; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Đơn hàng đã hủy</h5>
                        <p class="card-text"><?php echo $canceled_orders; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <h4>Doanh thu theo tháng</h4>
        <canvas id="revenueChart"></canvas>

        <h4 class="mt-4">Top 5 sản phẩm bán chạy</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Đã bán</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo $product['sold']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    function loadChartData() {
        $.ajax({
            url: 'get_chart_data.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                updateCharts(data);
            }
        });
    }

    function updateCharts(data) {
        // Xóa biểu đồ cũ nếu có
        if (window.myChart) {
            window.myChart.destroy();
        }

        var ctx = document.getElementById('revenueChart').getContext('2d');
        var revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [<?php foreach ($monthly_revenue as $data) echo "'Tháng " . $data['month'] . "',"; ?>],
                datasets: [{
                    label: 'Doanh thu',
                    data: [<?php foreach ($monthly_revenue as $data) echo $data['revenue'] . ","; ?>],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2
                }]
            }
        });
    }

    // Load dữ liệu khi trang thống kê được hiển thị trên dashboard
    loadChartData();
});
</script>

</body>
</html>
