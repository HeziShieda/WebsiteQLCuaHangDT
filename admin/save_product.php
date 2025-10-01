<?php
require '../includes/db.php';
session_start();

$response = ['success'=>false, 'message'=>'Lỗi không xác định'];

if($_SERVER['REQUEST_METHOD']==='POST'){
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $discount = floatval($_POST['discount_price']);
    $stock = intval($_POST['stock_quantity']);
    $category = intval($_POST['category_id']);
    $status = $_POST['status'] ?? 'Active';
    $description = $_POST['description'] ?? '';
    $spec = $_POST['specifications'] ?? '';

    if($name===''){
        $response['message'] = 'Tên sản phẩm không được để trống';
        echo json_encode($response); exit;
    }

    // Handle image upload
    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['tmp_name'] != '') {
        if ($_FILES['image']['error'] != UPLOAD_ERR_OK) {
            $response['message'] = 'Lỗi khi upload file';
            echo json_encode($response); exit;
        }

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg','jpeg','png','gif'];
        if (!in_array(strtolower($ext), $allowed)) {
            $response['message'] = 'Chỉ cho phép file ảnh (jpg, png, gif)';
            echo json_encode($response); exit;
        }

        $imagePath = 'uploads/' . uniqid() . '.' . $ext;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], '../' . $imagePath)) {
            $response['message'] = 'Lỗi upload ảnh';
            echo json_encode($response); exit;
        }
    } else {
        // Không upload ảnh -> dùng ảnh mặc định
        $imagePath = 'uploads/default.jpg';
    }



    if($id>0){
        // Update
        $sql = "UPDATE products SET name=?, price=?, discount_price=?, stock_quantity=?, category_id=?, status=?, description=?, specifications=?";
        $params = [$name,$price,$discount,$stock,$category,$status,$description,$spec];
        $types = "sdiissss";
        if($imagePath!==''){
            $sql.=", image=?";
            $types.="s";
            $params[]=$imagePath;
        }
        $sql.=" WHERE id=?";
        $types.="i";
        $params[]=$id;

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types,...$params);
        if($stmt->execute()){
            $response['success'] = true;
            $response['message'] = 'Cập nhật sản phẩm thành công';
        }else $response['message']='Lỗi khi cập nhật';
    }else{
        // Insert
        $stmt = $conn->prepare("INSERT INTO products (name, price, discount_price, stock_quantity, category_id, status, description, specifications, created_by, image) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $userId = $_SESSION['user_id'] ?? 0; // hoặc lấy user thực tế
        $stmt->bind_param("sdiissssis",$name,$price,$discount,$stock,$category,$status,$description,$spec,$userId,$imagePath);
        if($stmt->execute()){
            $response['success'] = true;
            $response['message'] = 'Thêm sản phẩm thành công';
        }else $response['message']='Lỗi khi thêm';
    }
}

echo json_encode($response);
