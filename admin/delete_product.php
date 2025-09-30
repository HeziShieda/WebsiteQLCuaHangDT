<?php
require '../includes/db.php';

$response = ['success'=>false,'message'=>''];

if(isset($_GET['id'])){
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i",$id);
    if($stmt->execute()){
        $response['success'] = true;
        $response['message'] = "Xóa sản phẩm thành công";
    } else {
        $response['message'] = "Lỗi khi xóa sản phẩm";
    }
}

echo json_encode($response);
