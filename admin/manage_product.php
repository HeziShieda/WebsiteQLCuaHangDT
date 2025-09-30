<?php
require '../includes/db.php';
session_start();

// Lấy danh mục
$categories = [];
$res = $conn->query("SELECT id, name FROM categories");
while($row = $res->fetch_assoc()) $categories[] = $row;

// Lấy sản phẩm (hỗ trợ search)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT p.*, c.name AS category_name, u.username AS created_by_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.created_by = u.id";
if($search !== ''){
    $s = $conn->real_escape_string($search);
    $sql .= " WHERE p.name LIKE '%$s%' OR c.name LIKE '%$s%'";
}
$products = $conn->query($sql);
?>

<h2>Quản lý Sản phẩm</h2>

<!-- Tìm kiếm -->
<form id="searchProductForm" class="mb-3 d-flex" style="max-width:400px;">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control me-2" placeholder="Tìm sản phẩm...">
    <button type="submit" class="btn btn-primary">Tìm</button>
</form>

<button class="btn btn-success mb-3" onclick="toggleProductForm()">Thêm sản phẩm</button>
<!-- Thêm sản phẩm -->
<div id="addProductForm" style="display:none;">
    <div class="card p-3 mb-3">
        <h4 id="formTitle">Thêm sản phẩm</h4>
        <form id="productForm" enctype="multipart/form-data">
            <input type="hidden" name="id" id="product_id_add">
            <div class="mb-3"><label>Tên</label><input type="text" name="name" class="form-control" required></div>
            <div class="mb-3"><label>Hình ảnh</label><input type="file" name="image" class="form-control"></div>
            <div class="mb-3"><label>Giá</label><input type="number" name="price" class="form-control" required></div>
            <div class="mb-3"><label>Giá KM</label><input type="number" name="discount_price" class="form-control"></div>
            <div class="mb-3"><label>Tồn kho</label><input type="number" name="stock_quantity" class="form-control" required></div>
            <div class="mb-3">
                <label>Danh mục</label>
                <select name="category_id" class="form-control" required>
                    <?php foreach($categories as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Trạng thái</label>
                <select name="status" class="form-control">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <div class="mb-3"><label>Mô tả</label><textarea name="description" class="form-control"></textarea></div>
            <div class="mb-3"><label>Thông số</label><textarea name="specifications" class="form-control"></textarea></div>
            <button type="submit" class="btn btn-primary">Lưu</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('addProductForm').style.display='none';">Hủy</button>
        </form>
    </div>
</div>

<!-- Sửa sản phẩm -->
<div id="editProductDiv" style="display:none;">
    <div class="card p-3 mb-3">
        <h4>Sửa sản phẩm</h4>
        <form id="editProductForm" enctype="multipart/form-data">
            <input type="hidden" name="id" id="product_id_edit">
            <div class="mb-3"><label>Tên</label><input type="text" name="name" class="form-control" required></div>
            <div class="mb-3"><label>Hình ảnh</label><input type="file" name="image" class="form-control"></div>
            <div class="mb-3"><label>Giá</label><input type="number" name="price" class="form-control" required></div>
            <div class="mb-3"><label>Giá KM</label><input type="number" name="discount_price" class="form-control"></div>
            <div class="mb-3"><label>Tồn kho</label><input type="number" name="stock_quantity" class="form-control" required></div>
            <div class="mb-3">
                <label>Danh mục</label>
                <select name="category_id" class="form-control" required>
                    <?php foreach($categories as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Trạng thái</label>
                <select name="status" class="form-control">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <div class="mb-3"><label>Mô tả</label><textarea name="description" class="form-control"></textarea></div>
            <div class="mb-3"><label>Thông số</label><textarea name="specifications" class="form-control"></textarea></div>
            <button type="submit" class="btn btn-primary">Lưu</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('editProductDiv').style.display='none';">Hủy</button>
        </form>
    </div>
</div>



<!-- Danh sách sản phẩm -->
<table class="table table-bordered table-striped text-center align-middle">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Tên</th>
            <th>Hình ảnh</th>
            <th>Giá</th>
            <th>Giá KM</th>
            <th>Tồn kho</th>
            <th>Danh mục</th>
            <th>Trạng thái</th>
            <th>Mô tả</th>
            <th>Thông số</th>
            <th>Hành động</th>
        </tr>
    </thead>
    <tbody id="productList">
        <?php while($p = $products->fetch_assoc()): ?>
            <tr id="product-row-<?= $p['id'] ?>">
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td>
                    <?php if($p['image']): ?>
                        <img src="../<?= htmlspecialchars($p['image']) ?>" width="50" height="50">
                    <?php else: ?>Không<?php endif; ?>
                </td>
                <td><?= number_format($p['price']) ?> VND</td>
                <td><?= $p['discount_price']>0?number_format($p['discount_price']).' VND':'Không' ?></td>
                <td><?= $p['stock_quantity'] ?></td>
                <td><?= htmlspecialchars($p['category_name']) ?></td>
                <td><?= htmlspecialchars($p['created_by_name']) ?></td>
                <td><?= htmlspecialchars($p['status']) ?></td>
                <td><?= substr(htmlspecialchars($p['description']),0,50) ?>...</td>
                <td><?= substr(htmlspecialchars($p['specifications']),0,50) ?>...</td>
                <td>
                    <button class="btn btn-sm btn-primary"
                        onclick="toggleEditProductForm(
                            '<?= $p['id'] ?>',
                            '<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>',
                            '<?= $p['price'] ?>',
                            '<?= $p['discount_price'] ?>',
                            '<?= $p['stock_quantity'] ?>',
                            '<?= $p['category_id'] ?>',
                            '<?= $p['status'] ?>',
                            '<?= htmlspecialchars($p['description'], ENT_QUOTES) ?>',
                            '<?= htmlspecialchars($p['specifications'], ENT_QUOTES) ?>'
                        )">
                        Sửa
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?= $p['id'] ?>)">Xóa</button>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script>
// Hiện/ẩn form thêm
function toggleProductForm(id='',name='',price='',discount='',stock='',category='',status='',desc='',spec=''){
    document.getElementById('addProductForm').style.display='block';
    document.getElementById('product_id_add').value=id;
    let f = document.getElementById('productForm');
    f.name.value=name||'';
    f.price.value=price||'';
    f.discount_price.value=discount||'';
    f.stock_quantity.value=stock||'';
    f.category_id.value=category||'';
    f.status.value=status||'Active';
    f.description.value=desc||'';
    f.specifications.value=spec||'';
    document.getElementById('formTitle').innerText = id?'Sửa sản phẩm':'Thêm sản phẩm';
}

function toggleEditProductForm(id='', name='', price='', discount='', stock='', category='', status='', desc='', spec=''){
    document.getElementById('editProductDiv').style.display = 'block';
    let s = document.getElementById('editProductForm');
    s.querySelector('#product_id_edit').value = id;
    s.querySelector('[name="name"]').value = name;
    s.querySelector('[name="price"]').value = price;
    s.querySelector('[name="discount_price"]').value = discount;
    s.querySelector('[name="stock_quantity"]').value = stock;
    s.querySelector('[name="category_id"]').value = category;
    s.querySelector('[name="status"]').value = status;
    s.querySelector('[name="description"]').value = desc;
    s.querySelector('[name="specifications"]').value = spec;
}



// Load sản phẩm
function loadProducts(search=''){
    let url = 'load_products.php';
    if(search) url+='?search='+encodeURIComponent(search);
    fetch(url)
        .then(res=>res.text())
        .then(html=>document.getElementById('productList').innerHTML=html);
}

// Xử lý submit form sửa
document.getElementById('editProductForm').onsubmit = function(e){
    e.preventDefault();
    let formData = new FormData(this);
    fetch('save_product.php',{
        method:'POST',
        body: formData
    }).then(res=>res.json())
    .then(data=>{
        alert(data.message);
        if(data.success){
            // Ẩn container, không phải form
            document.getElementById('editProductDiv').style.display = 'none';
            this.reset(); // reset form hiện tại
            loadProducts();
        }
    }).catch(err=>{
        console.error(err);
        alert('Lỗi khi cập nhật sản phẩm');
    });
};

// Xử lý submit form thêm
document.getElementById('productForm').onsubmit = function(e){
    e.preventDefault();
    let formData = new FormData(this);
    fetch('save_product.php',{
        method:'POST',
        body: formData
    }).then(res=>res.json())
    .then(data=>{
        alert(data.message);
        if(data.success){
            // Ẩn container
            document.getElementById('addProductForm').style.display = 'none';
            this.reset();
            loadProducts();
        }
    }).catch(err=>{
        console.error(err);
        alert('Lỗi khi thêm sản phẩm');
    });
};


// Xóa sản phẩm
function deleteProduct(id){
    if(confirm('Xác nhận xóa sản phẩm này?')){
        fetch('delete_product.php?id='+id)
        .then(res=>res.json())
        .then(data=>{
            alert(data.message);
            if(data.success){
                let row = document.getElementById('product-row-'+id);
                if(row) row.remove();
            }
        });
        loadProducts();
    }
}

// Tìm kiếm
document.getElementById('searchProductForm').onsubmit = function(e){
    e.preventDefault();
    loadProducts(this.search.value.trim());
}

// Init
function initProductPage(){ loadProducts(); }
initProductPage();
</script>
