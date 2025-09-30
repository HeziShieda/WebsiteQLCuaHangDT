<?php
require '../includes/db.php';
?>

<button class="btn btn-success mb-3" onclick="toggleCategoryForm()">Thêm danh mục</button>

<!-- Form thêm/sửa danh mục -->
<div id="categoryFormContainer" style="display: none;">
    <div class="card p-3">
        <h4 id="formTitle">Thêm danh mục</h4>
        <form id="categoryForm">
            <input type="hidden" id="category_id" name="category_id">
            <div class="mb-3">
                <label>Tên danh mục</label>
                <input type="text" id="category_name" name="category_name" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Lưu</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('categoryFormContainer').style.display = 'none'">Hủy</button>
        </form>
    </div>
</div>

<!-- Thanh Tìm Kiếm -->
<div class="mb-3">
    <form id="searchCategoryForm" class="d-flex">
        <input type="text" name="search" class="form-control me-2" placeholder="Tìm danh mục (ID, tên)">
        <button type="submit" class="btn btn-primary">Tìm</button>
    </form>
</div>

<!-- Danh sách danh mục -->
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên danh mục</th>
            <th>Hành động</th>
        </tr>
    </thead>
    <tbody id="categoryList">
        <!-- Dữ liệu sẽ load bằng AJAX -->
    </tbody>
</table>

<script>
// Hiện/ẩn form thêm & sửa
function toggleCategoryForm(id = '', name = '') {
    document.getElementById('categoryFormContainer').style.display = 'block';
    document.getElementById('category_id').value = id;
    document.getElementById('category_name').value = name;
    document.getElementById('formTitle').innerText = id ? 'Sửa danh mục' : 'Thêm danh mục';
}

// Load danh mục từ database (có hỗ trợ tìm kiếm)
function loadCategories(search = '') {
    let url = 'load_categories.php';
    if (search !== '') {
        url += '?search=' + encodeURIComponent(search);
    }

    fetch(url)
        .then(response => response.text())
        .then(data => {
            document.getElementById('categoryList').innerHTML = data;
        });
}

// Xử lý form thêm/sửa danh mục bằng AJAX
document.getElementById('categoryForm').onsubmit = function(event) {
    event.preventDefault();
    let formData = new FormData(this);

    fetch('save_category.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);

            // Ẩn form tự động
            document.getElementById('categoryFormContainer').style.display = 'none';

            // Xóa giá trị trong form
            document.getElementById('category_id').value = '';
            document.getElementById('category_name').value = '';

            // Cập nhật danh sách danh mục
            if (data.category) {
                let row = document.getElementById('category-row-' + data.category.id);
                if (row) {
                    // Update tên nếu sửa
                    row.querySelector('.category-name').innerText = data.category.name;
                } else {
                    // Nếu thêm mới, reload danh sách
                    loadCategories();
                }
            } else {
                loadCategories();
            }
        } else {
            alert('Lỗi: ' + data.message);
        }
    });
};


// Xóa danh mục bằng AJAX
function deleteCategory(id) {
    if (confirm('Xác nhận xóa danh mục này?')) {
        fetch('delete_category.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    let row = document.getElementById('category-row-' + id);
                    if (row) row.remove(); // xóa trực tiếp dòng
                } else {
                    alert('Lỗi: ' + data.message);
                }
            });
    }
}

// Xử lý tìm kiếm
document.getElementById('searchCategoryForm').onsubmit = function(e) {
    e.preventDefault();
    let search = this.search.value.trim();
    loadCategories(search);
};

// Khởi tạo khi load trang qua Ajax
function initCategoryPage() {
    loadCategories(); // load ngay khi trang vừa được chèn vào dashboard
}

// Gọi ngay nếu file chạy trực tiếp
initCategoryPage();

</script>

