<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("../includes/db_connect.php");

// ✅ Chặn người không phải admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php?page=login");
    exit;
}

include("../includes/header.php");
?>
<link rel="stylesheet" href="../assets/css/admin.css">

<div class="admin-container">
    <div class="admin-header-actions">
        <h1 class="admin-title" style="margin-bottom:0;">Quản Lý Sản Phẩm</h1>
        <a href="add_product.php" class="btn" style="background:var(--primary); color:#fff; border:none; padding:10px 20px; border-radius:50px; font-weight:600;">
           <i class="fa fa-plus"></i> Thêm sản phẩm mới
        </a>
    </div>

    <!-- ✅ Thanh lọc danh mục -->
    <div class="product-filters" style="text-align:left; margin-bottom:20px; display:flex; gap:10px;">
        <a href="products.php" class="<?= !isset($_GET['category']) ? 'active' : '' ?>">Tất cả</a>
        <a href="products.php?category=giay" class="<?= ($_GET['category'] ?? '') === 'giay' ? 'active' : '' ?>">Giày</a>
        <a href="products.php?category=gangtay" class="<?= ($_GET['category'] ?? '') === 'gangtay' ? 'active' : '' ?>">Găng tay</a>
        <a href="products.php?category=phukien" class="<?= ($_GET['category'] ?? '') === 'phukien' ? 'active' : '' ?>">Phụ kiện</a>
    </div>

    <style>
        .product-filters a {
            display: inline-block; padding: 8px 20px; background: #fff; border: 1px solid var(--border);
            border-radius: 50px; text-decoration: none; color: #555; font-weight: 600; font-size: 14px;
            transition: all 0.2s ease;
        }
        .product-filters a:hover { background: var(--secondary); color: #fff; border-color: var(--secondary); }
        .product-filters a.active { background: var(--primary); color: #fff; border-color: var(--primary); box-shadow: var(--shadow-sm); }
    </style>

    <?php
    // ✅ Thêm lọc danh mục vào truy vấn SQL
    $categoryFilter = "";
    if (isset($_GET['category']) && $_GET['category'] !== '') {
        $category = mysqli_real_escape_string($conn, $_GET['category']);
        $categoryFilter = "WHERE category = '$category'";
    }

    $sql = "SELECT * FROM products $categoryFilter ORDER BY id DESC";
    $result = mysqli_query($conn, $sql);
    ?>

    <div class="admin-table-wrapper">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Ảnh</th>
                <th>Tên sản phẩm</th>
                <th>Mã SKU</th>
                <th>Mức Giá</th>
                <th>Phân Loại</th>
                <th style="text-align:right;">Công Cụ</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $cat_badge = '';
                    if($row['category'] == 'giay') $cat_badge = 'badge-success';
                    elseif($row['category'] == 'gangtay') $cat_badge = 'badge-warning';
                    elseif($row['category'] == 'phukien') $cat_badge = 'badge-danger';
                    
                    echo '<tr>
                            <td style="width:80px;">
                                <img src="../assets/uploads/' . htmlspecialchars($row['image']) . '" alt="" style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:1px solid #eaedf2;">
                            </td>
                            <td><strong>' . htmlspecialchars($row['name']) . '</strong></td>
                            <td style="color:#888;">' . htmlspecialchars($row['code']) . '</td>
                            <td style="color:var(--primary); font-weight:700;">' . number_format($row['price'], 0, ',', '.') . '₫</td>
                            <td><span class="badge ' . $cat_badge . '">' . htmlspecialchars(strtoupper($row['category'])) . '</span></td>
                            <td style="text-align:right;">
                                <div class="action-group" style="display:flex; justify-content:flex-end; gap:5px; white-space:nowrap;">
                                    <a href="stock_manager.php?product_id=' . $row['id'] . '" class="action-link" style="color:var(--secondary);">Kho hàng</a>
                                    <a href="edit_product.php?id=' . $row['id'] . '" class="action-link">Sửa</a>
                                    <a href="delete_product.php?id=' . $row['id'] . '" class="action-link action-delete"
                                       onclick="return confirm(\'Bạn chắc chắn muốn xóa vĩnh viễn sản phẩm này?\')">Xóa</a>
                                </div>
                            </td>
                        </tr>';
                }
            } else {
                echo "<tr><td colspan='6' style='text-align: center; padding: 40px;'><br>Chưa có sản phẩm nào.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
