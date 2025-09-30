<?php
require '../includes/db.php';

$search = isset($_GET['search']) ? "%".$_GET['search']."%" : "%";

$stmt = $conn->prepare("SELECT * FROM categories WHERE id LIKE ? OR name LIKE ? ORDER BY id DESC");
$stmt->bind_param("ss", $search, $search);
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()) {
    echo '<tr id="category-row-'.$row['id'].'">';
    echo '<td>'.$row['id'].'</td>';
    echo '<td class="category-name">'.htmlspecialchars($row['name']).'</td>';
    echo '<td>';
    echo '<button class="btn btn-sm btn-warning" onclick="toggleCategoryForm('.$row['id'].',\''.htmlspecialchars($row['name'],ENT_QUOTES).'\')">Sửa</button> ';
    echo '<button class="btn btn-sm btn-danger" onclick="deleteCategory('.$row['id'].')">Xóa</button>';
    echo '</td></tr>';
}
