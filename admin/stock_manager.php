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

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

if ($product_id > 0) {
    // Lấy thông tin sản phẩm
    $sql = "SELECT * FROM products WHERE id = $product_id";
    $product = mysqli_query($conn, $sql)->fetch_assoc();
    if (!$product) die("Sản phẩm không tồn tại");

    // Xử lý cập nhật kho
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sizes = $_POST['sizes'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        
        foreach ($sizes as $index => $size) {
            $qty = (int)$quantities[$index];
            $size = mysqli_real_escape_string($conn, trim($size));
            if ($size !== "") {
                $stmt = $conn->prepare("INSERT INTO product_stock (product_id, size, quantity) 
                                        VALUES (?, ?, ?) 
                                        ON DUPLICATE KEY UPDATE quantity = ?");
                $stmt->bind_param("isii", $product_id, $size, $qty, $qty);
                $stmt->execute();
            }
        }
        $success_msg = "Cập nhật kho hàng thành công!";
    }

    // Lấy tồn kho hiện tại
    $stocks = [];
    $res = $conn->query("SELECT * FROM product_stock WHERE product_id = $product_id");
    while ($row_s = $res->fetch_assoc()) {
        $stocks[$row_s['size']] = $row_s['quantity'];
    }

    // Xác định các size mặc định theo type_id
    $default_sizes = [];
    switch ($product['type_id']) {
        case 1: $default_sizes = ['35','36','37','38','39','40','41','42','43','44','45']; break;
        case 2: $default_sizes = ['7','8','9','10']; break;
        case 3: $default_sizes = ['S','M','L','XL','XXL']; break;
        case 4: $default_sizes = ['Kích thước chung']; break; // Thêm cho sản phẩm không có size
        default: $default_sizes = [];
    }

    // Hợp nhất size mặc định và size đã có trong DB
    $all_sizes = array_unique(array_merge($default_sizes, array_keys($stocks)));
}

include("../includes/header.php");
?>
<link rel="stylesheet" href="../assets/css/admin.css">
<style>
    .stock-card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: var(--shadow-sm); max-width: 700px; margin: 20px auto; }
    .stock-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .stock-table th, .stock-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
    .stock-table th { background: #f8f9fa; font-weight: 600; }
    .size-input { width: 80px; padding: 6px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9; }
    .qty-input { width: 120px; padding: 6px; border: 1px solid #ddd; border-radius: 4px; }
    .btn-add-row { background: #e9ecef; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; margin-bottom: 15px; font-size: 13px; }
    .product-info-mini { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
    .product-info-mini img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
</style>

<div class="admin-container">
    <div class="admin-header-actions">
        <h1 class="admin-title">Quản Lý Kho Hàng</h1>
        <a href="products.php" class="btn" style="background:#6c757d; color:#fff;">Quay lại</a>
    </div>

    <?php if ($product_id > 0): ?>
        <div class="stock-card">
            <?php if (isset($success_msg)) echo "<p style='background:#d4edda; color:#155724; padding:10px; border-radius:4px; margin-bottom:15px;'>✅ $success_msg</p>"; ?>
            
            <div class="product-info-mini">
                <img src="../assets/uploads/<?= htmlspecialchars($product['image']) ?>" alt="">
                <div>
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p style="color:#888; margin:0;">SKU: <?= htmlspecialchars($product['code']) ?></p>
                    <p style="color:#888; margin:0;">Phân loại size: 
                        <strong>
                        <?php 
                            if($product['type_id'] == 1) echo "Giày / Lót giày (39-45)";
                            elseif($product['type_id'] == 2) echo "Găng tay (7-10)";
                            elseif($product['type_id'] == 3) echo "Áo (S-XXL)";
                            else echo "Không có size định sẵn";
                        ?>
                        </strong>
                    </p>
                </div>
            </div>

            <form method="POST">
                <table class="stock-table">
                    <thead>
                        <tr>
                            <th>Kích cỡ (Size)</th>
                            <th>Số lượng trong kho</th>
                        </tr>
                    </thead>
                    <tbody id="stock-rows-container">
                        <?php foreach ($all_sizes as $sz): ?>
                            <tr>
                                <td>
                                    <input type="text" name="sizes[]" value="<?= htmlspecialchars($sz) ?>" class="size-input" readonly>
                                </td>
                                <td>
                                    <input type="number" name="quantities[]" value="<?= $stocks[$sz] ?? 0 ?>" class="qty-input" min="0">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <button type="button" class="btn-add-row" onclick="addRow()">+ Thêm size tùy chỉnh</button>
                
                <div style="margin-top:20px; text-align:right;">
                    <button type="submit" class="btn" style="background:var(--primary); color:#fff; border:none; padding:12px 30px; border-radius:6px; font-weight:bold; cursor:pointer;">LƯU THAY ĐỔI KHO</button>
                </div>
            </form>
        </div>

        <script>
            function addRow() {
                const container = document.getElementById('stock-rows-container');
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><input type="text" name="sizes[]" placeholder="Size" class="size-input"></td>
                    <td><input type="number" name="quantities[]" value="0" class="qty-input" min="0"></td>
                `;
                container.appendChild(tr);
            }
        </script>

    <?php else: ?>
        <p>Vui lòng chọn sản phẩm từ danh sách để quản lý kho.</p>
    <?php endif; ?>
</div>

<?php include("../includes/footer.php"); ?>
