<?php
require '../includes/db.php';

$response = ['success'=>false, 'message'=>'Lỗi không xác định'];

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Xóa danh mục thành công';
    } else {
        $response['message'] = 'Lỗi khi xóa';
    }
}

echo json_encode($response);
