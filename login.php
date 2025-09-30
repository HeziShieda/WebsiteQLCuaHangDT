<?php
session_start();
include 'includes/db.php'; // Kết nối database

// Xử lý đăng ký
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $username = trim($_POST["registerUsername"]);
    $email = trim($_POST["registerEmail"]);
    $password = password_hash($_POST["registerPassword"], PASSWORD_DEFAULT);
    $role = "Customer"; // Mặc định user đăng ký là Customer

    // Kiểm tra email đã tồn tại chưa
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result->num_rows > 0) {
        $error = "Email đã tồn tại!";
    } else {
        // Thêm tài khoản vào database
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, 'Customer', NOW())");      
        if (!$stmt) {
            die("Lỗi SQL: " . $conn->error);
        }
        
        $stmt->bind_param("sss", $username, $email, $password);
        if ($stmt->execute()) {
            $_SESSION["user_id"] = $stmt->insert_id;
            $_SESSION["username"] = $username;
            $_SESSION["roles"] = $role;
            header("Location: index.php");
            exit;
        } else {
            $error = "Lỗi đăng ký!";
        }
    }
}

// Xử lý đăng nhập
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $email = trim($_POST["loginEmail"]);
    $password = trim($_POST["loginPassword"]);

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $username, $hashed_password, $roles);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $id;
            $_SESSION["username"] = $username;
            $_SESSION["roles"] = $roles;
            header("Location: index.php");
            exit;
        } else {
            $error = "Mật khẩu không đúng!";
        }
    } else {
        $error = "Email không tồn tại!";
    }
}
// Nếu có giỏ hàng trong SESSION, chuyển vào database
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $sql = "INSERT INTO cart (user_id, product_id, quantity) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE quantity = quantity + ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $user_id, $product_id, $quantity, $quantity);
        $stmt->execute();
    }
    // Xóa giỏ hàng trong SESSION sau khi chuyển
    unset($_SESSION['cart']);
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập & Đăng ký</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 400px; margin-top: 50px; }
    </style>
</head>
<body>
    <div class="container">
        <ul class="nav nav-tabs" id="authTabs">
            <li class="nav-item">
                <a class="nav-link <?= isset($_GET['tab']) && $_GET['tab'] == 'register' ? '' : 'active' ?>" data-bs-toggle="tab" href="#login">Đăng nhập</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= isset($_GET['tab']) && $_GET['tab'] == 'register' ? 'active' : '' ?>" data-bs-toggle="tab" href="#register">Đăng ký</a>
            </li>
        </ul>

        <div class="tab-content mt-3">
            <!-- Đăng nhập -->
            <div class="tab-pane fade <?= isset($_GET['tab']) && $_GET['tab'] == 'register' ? '' : 'show active' ?>" id="login">
                <h3 class="text-center">Đăng nhập</h3>
                <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="loginEmail" name="loginEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="loginPassword" name="loginPassword" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" name="login">Đăng nhập</button>
                </form>
            </div>
            
            <!-- Đăng ký -->
            <div class="tab-pane fade <?= isset($_GET['tab']) && $_GET['tab'] == 'register' ? 'show active' : '' ?>" id="register">
                <h3 class="text-center">Đăng ký</h3>
                <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="registerUsername" class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="registerUsername" name="registerUsername" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="registerEmail" name="registerEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerPassword" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="registerPassword" name="registerPassword" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100" name="register">Đăng ký</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>