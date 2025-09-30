<?php
session_start();
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'];  // Hành động (add, remove, update)
    $product_id = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    // Kiểm tra người dùng đã đăng nhập hay chưa
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Thêm sản phẩm vào database
        if ($action === "add") {
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) 
                                    VALUES (?, ?, ?) 
                                    ON DUPLICATE KEY UPDATE quantity = quantity + ?");
            $stmt->bind_param("iiii", $user_id, $product_id, $quantity, $quantity);
            $stmt->execute();
        }
        // Xóa sản phẩm khỏi database
        elseif ($action === "remove") {
            $user_id = $_SESSION['user_id'];
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
        }
        // Cập nhật số lượng sản phẩm
        elseif ($action === "update") {
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("iii", $quantity, $user_id, $product_id);
            $stmt->execute();
        }
    } else {
        // Xử lý giỏ hàng bằng SESSION nếu chưa đăng nhập
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if ($action === "add") {
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
        }
        elseif ($action === "remove") {
            unset($_SESSION['cart'][$product_id]);
        }
        elseif ($action === "update") {
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id] = $quantity;
            } else {
                unset($_SESSION['cart'][$product_id]);
            }
        }
    }
    header("Location: " . $_SERVER["HTTP_REFERER"]);
}
?>
