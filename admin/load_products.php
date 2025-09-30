<?php
require '../includes/db.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Query sản phẩm
$sql = "SELECT p.*, c.name as category_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id";

if ($search !== '') {
    $search_safe = $conn->real_escape_string($search);
    $sql .= " WHERE p.name LIKE '%$search_safe%' OR c.name LIKE '%$search_safe%'";
}

$sql .= " ORDER BY p.id DESC";

$result = $conn->query($sql);

// Sinh HTML
while ($row = $result->fetch_assoc()):
?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['name']) ?></td>
    <td>
        <?php if(!empty($row['image'])): ?>
            <img src="../<?= htmlspecialchars($row['image']) ?>" width="50" height="50">
        <?php else: ?>
            Không có ảnh
        <?php endif; ?>
    </td>
    <td><?= number_format($row['price'],0,',','.') ?> VND</td>
    <td><?= ($row['discount_price']>0)?number_format($row['discount_price'],0,',','.').' VND':'Không' ?></td>
    <td><?= $row['stock_quantity'] ?></td>
    <td><?= htmlspecialchars($row['category_name']) ?></td>
    <td><?= htmlspecialchars($row['status']) ?></td>
    <td><?= substr(htmlspecialchars($row['description']),0,50) ?>...</td>
    <td><?= substr(htmlspecialchars($row['specifications']),0,50) ?>...</td>
    <td>
        <button class="btn btn-sm btn-primary" 
            onclick="toggleEditProductForm(
                <?= $row['id'] ?>,
                '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>',
                <?= $row['price'] ?>,
                <?= $row['discount_price'] ?>,
                <?= $row['stock_quantity'] ?>,
                <?= $row['category_id'] ?>,
                '<?= $row['status'] ?>',
                '<?= htmlspecialchars($row['description'], ENT_QUOTES) ?>',
                '<?= htmlspecialchars($row['specifications'], ENT_QUOTES) ?>'
            )">Sửa</button>
        <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?= $row['id'] ?>)">Xóa</button>
    </td>
</tr>
<?php endwhile; ?>
