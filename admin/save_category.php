<?php
require '../includes/db.php';

$response = ['success'=>false, 'message'=>'Lỗi không xác định'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $name = trim($_POST['category_name']);

    if ($name === '') {
        $response['message'] = 'Tên danh mục không được để trống';
        echo json_encode($response); exit;
    }

    if ($id > 0) {
        // Update
        $stmt = $conn->prepare("UPDATE categories SET name=? WHERE id=?");
        $stmt->bind_param("si", $name, $id);
        if($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Cập nhật danh mục thành công';
            $response['category'] = ['id'=>$id, 'name'=>$name];
        } else {
            $response['message'] = 'Lỗi khi cập nhật';
        }
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if($stmt->execute()) {
            $newId = $stmt->insert_id;
            $response['success'] = true;
            $response['message'] = 'Thêm danh mục thành công';
            $response['category'] = ['id'=>$newId, 'name'=>$name];
        } else {
            $response['message'] = 'Lỗi khi thêm';
        }
    }
}

echo json_encode($response);
