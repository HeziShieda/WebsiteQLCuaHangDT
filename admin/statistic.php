<?php
require '../includes/db.php';

// Xử lý filter
$period = isset($_GET['period']) && in_array($_GET['period'], ['day','month']) ? $_GET['period'] : 'day';
$range = isset($_GET['range']) ? intval($_GET['range']) : 7; // số ngày hoặc tháng

// Thống kê tổng doanh thu
$revenue = $conn->query("SELECT SUM(total_price) AS revenue FROM orders WHERE status='Completed'")->fetch_assoc()['revenue'] ?? 0;

// Tổng số đơn hàng
$total_orders = $conn->query("SELECT COUNT(id) AS total_orders FROM orders")->fetch_assoc()['total_orders'] ?? 0;

// Tổng số sản phẩm tồn kho
$total_stock = $conn->query("SELECT SUM(stock_quantity) AS total_stock FROM products")->fetch_assoc()['total_stock'] ?? 0;

// Lấy dữ liệu doanh thu theo filter
$data_labels = [];
$data_values = [];

$stockProducts = $conn->query("SELECT name, stock_quantity FROM products ORDER BY stock_quantity ASC")->fetch_all(MYSQLI_ASSOC);

if($period === 'day'){
    $sql = "SELECT DATE(order_date) AS label, SUM(total_price) AS revenue 
            FROM orders 
            WHERE status='Completed' 
            AND order_date >= DATE_SUB(CURDATE(), INTERVAL $range-1 DAY)
            GROUP BY DATE(order_date)
            ORDER BY label ASC";
    $res = $conn->query($sql);
    while($row = $res->fetch_assoc()){
        $data_labels[] = $row['label'];
        $data_values[] = $row['revenue'];
    }
} else { // month
    $sql = "SELECT DATE_FORMAT(order_date,'%Y-%m') AS label, SUM(total_price) AS revenue 
            FROM orders 
            WHERE status='Completed' 
            AND order_date >= DATE_SUB(CURDATE(), INTERVAL $range-1 MONTH)
            GROUP BY DATE_FORMAT(order_date,'%Y-%m')
            ORDER BY label ASC";
    $res = $conn->query($sql);
    while($row = $res->fetch_assoc()){
        $data_labels[] = $row['label'];
        $data_values[] = $row['revenue'];
    }
}
?>

<div class="container-fluid">
    <h2>Thống kê</h2>

    <div class="row mt-3">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Tổng doanh thu</h5>
                    <p class="card-text"><?= number_format($revenue,0,',','.') ?> đ</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Tổng đơn hàng</h5>
                    <p class="card-text"><?= $total_orders ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Sản phẩm tồn kho</h5>
                    <p class="card-text"><?= $total_stock ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="row mb-3">
        <div class="col-md-4">
            <label>Chọn khoảng thời gian:</label>
            <select id="statRange" class="form-select">
                <?php if($period==='day'): ?>
                    <option value="7" <?= $range==7?'selected':'' ?>>7 ngày</option>
                    <option value="14" <?= $range==14?'selected':'' ?>>14 ngày</option>
                    <option value="30" <?= $range==30?'selected':'' ?>>30 ngày</option>
                <?php else: ?>
                    <option value="3" <?= $range==3?'selected':'' ?>>3 tháng</option>
                    <option value="6" <?= $range==6?'selected':'' ?>>6 tháng</option>
                    <option value="12" <?= $range==12?'selected':'' ?>>12 tháng</option>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label>Chọn loại thống kê:</label>
            <select id="statPeriod" class="form-select">
                <option value="day" <?= $period==='day'?'selected':'' ?>>Theo ngày</option>
                <option value="month" <?= $period==='month'?'selected':'' ?>>Theo tháng</option>
            </select>
        </div>
        <div class="col-md-4 mt-4">
            <button id="exportPDF" class="btn btn-danger mt-2">Xuất PDF</button>
        </div>
    </div>

    <!-- Bảng thống kê -->
    <table class="table table-bordered" id="statTable">
        <thead class="table-dark">
            <tr>
                <th><?= $period==='day'?'Ngày':'Tháng' ?></th>
                <th>Doanh thu (đ)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data_labels as $i => $label): ?>
            <tr>
                <td><?= $label ?></td>
                <td><?= number_format($data_values[$i],0,',','.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <canvas id="statChart" height="100"></canvas>
</div>


<h4>Sản phẩm tồn kho</h4>
<table class="table table-bordered" id="stockTable">
    <thead>
        <tr>
            <th>Sản phẩm</th>
            <th>Số lượng tồn</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($stockProducts as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= $p['stock_quantity'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>

<script>
$(document).ready(function(){
    let chart = null;
    function renderChart(labels, data){
        const ctx = document.getElementById('statChart').getContext('2d');
        if(chart) chart.destroy();
        const type = $('#statPeriod').val() === 'day' ? 'line' : 'bar';
        chart = new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh thu',
                    data: data,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: type==='bar' ? 'rgba(54, 162, 235, 0.7)' : 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2,
                    fill: type==='line',
                    tension: 0.3
                }]
            },
            options: {
                scales: { y: { beginAtZero:true } }
            }
        });
    }

    renderChart([<?= "'".implode("','",$data_labels)."'" ?>], [<?= implode(',',$data_values) ?>]);

    function loadStats(){
        const period = $('#statPeriod').val();
        const range = $('#statRange').val();
        $.get('statistic.php', {period: period, range: range}, function(html){
            $('#dashboard-content').html(html);
        });
    }

    $('#statPeriod, #statRange').change(function(){
        loadStats();
    });

    // Xuất PDF
    $('#exportPDF').click(function(){
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'pt', 'a4');

        html2canvas(document.querySelector('#dashboard-content')).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const imgWidth = 595; // a4 width in pt
            const imgHeight = canvas.height * imgWidth / canvas.width;
            doc.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);
            doc.save('statistic.pdf');
        });
    });
});
</script>
