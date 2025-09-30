<?php 
include 'includes/header.php'; 
include 'includes/db.php'; // Kết nối database

// Lấy từ khóa tìm kiếm từ thanh tìm kiếm trong header
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Xây dựng điều kiện truy vấn sản phẩm
$whereClauses = ["status = 'Active'"];
if ($search) {
    $whereClauses[] = "name LIKE '%" . $conn->real_escape_string($search) . "%'";
}
if ($category_id) {
    $whereClauses[] = "category_id = " . $category_id;
}
$whereQuery = $whereClauses ? "WHERE " . implode(" AND ", $whereClauses) : "";

// Lấy danh mục từ database
$categories = $conn->query("SELECT * FROM categories");

// Lấy danh sách sản phẩm từ database theo điều kiện
$products = $conn->query("SELECT * FROM products $whereQuery");
?>

<div class="container-custom mt-3">
    <div class="row">
        <!-- Sidebar danh mục -->
        <div class="col-md-2 bg-light p-3">
            <h5>Danh mục sản phẩm</h5>
            <button class="btn btn-secondary w-100 mb-2" onclick="window.location.href='index.php'">Tất cả</button>
            <?php while ($category = $categories->fetch_assoc()): ?>
                <button class="btn btn-secondary w-100 mb-2" 
                    onclick="window.location.href='index.php?category=<?= $category['id'] ?>'">
                    <?= htmlspecialchars($category['name']) ?>
                </button>
            <?php endwhile; ?>
        </div>

        <!-- Nội dung chính -->
        <div class="col-md-10">
            <h4 class="text-center">Danh sách sản phẩm</h4>
            <div class="row row-cols-2 row-cols-md-5 g-3">
                <?php while ($product = $products->fetch_assoc()): ?>
                    <div class="col">
                        <div class="product-card p-2 text-center">
                            <div class="product-image">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?= htmlspecialchars($product['image']) ?>" class="img-fluid" alt="Sản phẩm">
                                <?php else: ?>
                                    <p>Không có ảnh</p>
                                <?php endif; ?>
                            </div>

                            <h6 class="mt-2"><?= htmlspecialchars($product['name']) ?></h6>
                            <p class="text-danger"><?= number_format($product['price'], 0, ',', '.') ?> đ</p>
                            <a class="btn btn-sm btn-info" href="/BanDienThoai/pages/product.php?id=<?= $product['id'] ?>">Chi tiết</a>
                            <form action="/BanDienThoai/process_cart.php" method="POST">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-sm btn-success">Thêm vào giỏ hàng</button>
                            </form>

                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'includes/footer.php'; ?>
