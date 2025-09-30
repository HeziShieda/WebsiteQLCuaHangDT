<?php
include '../includes/db.php';
session_start();

// ================== XỬ LÝ CẬP NHẬT ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];
    $payment_status = $_POST['payment_status'];

    $sql_update = "UPDATE orders SET status=?, payment_status=? WHERE id=?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ssi", $status, $payment_status, $order_id);

    if ($stmt->execute()) {
        // Trả về JSON thông tin đã update
        echo json_encode([
            'success' => true,
            'status' => $status,
            'payment_status' => $payment_status
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

// ================== LẤY DANH SÁCH ĐƠN HÀNG ==================
$sql = "SELECT o.id, u.email, o.order_date, o.total_price, 
               o.status, o.payment_method, o.payment_status
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.order_date DESC";
$result = $conn->query($sql);
?>

<div class="container mt-4">
    <h2>Quản lý đơn hàng</h2>
    <table class="table table-bordered table-striped mt-3" id="orders-table">
        <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Email khách</th>
                <th>Ngày đặt</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Thanh toán</th>
                <th>Phương thức</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr id="order-row-<?= $row['id'] ?>">
                <td>#<?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= $row['order_date'] ?></td>
                <td><?= number_format($row['total_price'], 0, ',', '.') ?> đ</td>
                <td>
                    <select id="status-<?= $row['id'] ?>" class="form-select form-select-sm">
                        <option value="Pending" <?= $row['status']=="Pending"?"selected":"" ?>>Pending</option>
                        <option value="Processing" <?= $row['status']=="Processing"?"selected":"" ?>>Processing</option>
                        <option value="Completed" <?= $row['status']=="Completed"?"selected":"" ?>>Completed</option>
                        <option value="Canceled" <?= $row['status']=="Canceled"?"selected":"" ?>>Cancelled</option>
                    </select>
                </td>
                <td>
                    <select id="payment-<?= $row['id'] ?>" class="form-select form-select-sm">
                        <option value="Unpaid" <?= $row['payment_status']=="Unpaid"?"selected":"" ?>>Chưa thanh toán</option>
                        <option value="Paid" <?= $row['payment_status']=="Paid"?"selected":"" ?>>Đã thanh toán</option>
                    </select>
                </td>
                <td><?= htmlspecialchars($row['payment_method']) ?></td>
                <td>
                    <button type="button" class="btn btn-sm btn-primary" onclick="updateOrder(<?= $row['id'] ?>)">Lưu</button>
                    <button type="button" class="btn btn-sm btn-info" onclick="toggleDetails(<?= $row['id'] ?>)">Xem chi tiết</button>
                </td>
            </tr>

            <!-- HÀNG ẨN: chi tiết sản phẩm -->
            <tr id="details-<?= $row['id'] ?>" style="display: none;">
                <td colspan="8">
                    <?php
                    $order_id = $row['id'];
                    $sql_details = "SELECT od.product_id, p.name, od.quantity, od.price 
                                    FROM orderdetails od
                                    JOIN products p ON od.product_id = p.id
                                    WHERE od.order_id = ?";
                    $stmt = $conn->prepare($sql_details);
                    $stmt->bind_param("i", $order_id);
                    $stmt->execute();
                    $details_result = $stmt->get_result();
                    ?>
                    <h6>Chi tiết đơn hàng #<?= $order_id ?>:</h6>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>ID SP</th>
                                <th>Tên sản phẩm</th>
                                <th>Số lượng</th>
                                <th>Giá</th>
                                <th>Tổng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($d = $details_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $d['product_id'] ?></td>
                                <td><?= htmlspecialchars($d['name']) ?></td>
                                <td><?= $d['quantity'] ?></td>
                                <td><?= number_format($d['price'], 0, ',', '.') ?> đ</td>
                                <td><?= number_format($d['price'] * $d['quantity'], 0, ',', '.') ?> đ</td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
function toggleDetails(orderId) {
    const row = document.getElementById("details-" + orderId);
    row.style.display = (row.style.display === "none") ? "table-row" : "none";
}

function updateOrder(orderId) {
    const status = $('#status-' + orderId).val();
    const payment_status = $('#payment-' + orderId).val();

    $.post('manage_order.php', {
        update_order: 1,
        order_id: orderId,
        status: status,
        payment_status: payment_status
    }, function(response){
        try {
            const data = JSON.parse(response);
            if(data.success){
                alert('Cập nhật đơn hàng thành công!');
                // Cập nhật select trong dòng mà không reload
                $('#status-' + orderId).val(data.status);
                $('#payment-' + orderId).val(data.payment_status);
            } else {
                alert('Có lỗi xảy ra khi cập nhật đơn hàng!');
            }
        } catch(e){
            alert('Có lỗi xảy ra khi xử lý dữ liệu từ server!');
        }
    });
}
</script>
