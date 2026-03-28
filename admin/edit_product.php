<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("../includes/db_connect.php");

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php?page=login");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit;
}
$id = (int)$_GET['id'];

// Lấy sản phẩm an toàn
$stmt_get = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt_get->bind_param("i", $id);
$stmt_get->execute();
$result = $stmt_get->get_result();
$product = $result->fetch_assoc();
$stmt_get->close();

if (!$product) {
    if (isset($product['type']) && $product['type'] === '') {
        $product['type'] = NULL;
    }
    if (isset($product['brand']) && $product['brand'] === '') {
        $product['brand'] = NULL;
    }
    if (isset($product['category']) && $product['category'] === '') {
        $product['category'] = NULL;
    }
}
if (!$product) {
    echo "<p style='text-align:center; margin-top:50px;'>❌ Sản phẩm không tồn tại!</p>";
    exit;
}

// Khi nhấn nút cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);
    $price = (float)$_POST['price'];
    $category = trim($_POST['category']); 
    $type = trim($_POST['type']);        
    $type_id = isset($_POST['type_id']) ? (int)$_POST['type_id'] : NULL; 
    $brand = trim($_POST['brand']);


    // Xử lý giá trị rỗng thành chuỗi trống '' (tránh lỗi NOT NULL trong DB)
    $category_value = empty($category) ? '' : $category;
    $type_value     = empty($type) ? '' : $type;
    $brand_value    = empty($brand) ? '' : $brand;

    $image = $product['image']; 


    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../assets/uploads/";
        $imageName = uniqid() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $imageName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            if ($product['image'] && file_exists($targetDir . $product['image'])) {
                unlink($targetDir . $product['image']);
            }
            $image = $imageName;
        } else {
            echo "<script>alert('❌ Lỗi khi tải ảnh mới lên!');</script>";
        }
    }

    $updateSql = "UPDATE products
                  SET name = ?, code = ?, price = ?, category = ?, type = ?, type_id = ?, image = ?, brand = ?
                  WHERE id = ?";

    $stmt_update = $conn->prepare($updateSql);

    if ($stmt_update === false) {
        die("Lỗi prepare SQL: " . $conn->error);
    }

    $stmt_update->bind_param(
        "ssdssissi",
        $name,        
        $code,        
        $price,        
        $category_value,
        $type_value,    
        $type_id,       
        $image,         
        $brand_value,   
        $id             
    );

    if ($stmt_update->execute()) {
        echo "<script>alert('✅ Cập nhật sản phẩm thành công!'); window.location.href='products.php';</script>";
        exit;
    } else {
        echo "<p style='text-align:center; color:red;'>Lỗi khi cập nhật: " . $stmt_update->error . "</p>";
    }

    $stmt_update->close();
}

include("../includes/header.php");
?>
<link rel="stylesheet" href="../assets/css/header.css">
<link rel="stylesheet" href="../assets/css/category.css">
<link rel="stylesheet" href="../assets/css/admin.css">

<main style="padding: 40px; max-width:700px; margin:auto;">
    <h2 style="font-size:24px; font-weight:700; text-align:center; margin-bottom:30px;">
        ✏️ Sửa sản phẩm: <?= htmlspecialchars($product['name']) ?>
    </h2>

    <form method="POST" enctype="multipart/form-data"
          style="background:white; padding:30px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.05);">

        <label style="font-weight:600;">Tên sản phẩm:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required
               style="width:100%; padding:10px; margin:8px 0 16px; border:1px solid #ccc; border-radius:6px;">

        <label style="font-weight:600;">Mã sản phẩm:</label>
        <input type="text" name="code" value="<?= htmlspecialchars($product['code']) ?>" required
               style="width:100%; padding:10px; margin:8px 0 16px; border:1px solid #ccc; border-radius:6px;">

        <label style="font-weight:600;">Giá (VNĐ):</label>
        <input type="number" step="any" name="price" value="<?= htmlspecialchars($product['price']) ?>" required
               style="width:100%; padding:10px; margin:8px 0 16px; border:1px solid #ccc; border-radius:6px;">

        <label style="font-weight:600;">Thương hiệu:</label>
        <select id="brand" name="brand" style="width:100%; padding:10px; margin:8px 0 16px; border:1px solid #ccc; border-radius:6px;">
            <option value="" <?= is_null($product['brand']) ? 'selected' : '' ?>>-- Không có --</option>
            <option value="Zocker" <?= ($product['brand'] == 'Zocker') ? 'selected' : '' ?>>Zocker</option>
            <option value="GKVN" <?= ($product['brand'] == 'GKVN') ? 'selected' : '' ?>>GKVN</option>
            <option value="Wika" <?= ($product['brand'] == 'Wika') ? 'selected' : '' ?>>Wika</option>
            <option value="Adidas" <?= ($product['brand'] == 'Adidas') ? 'selected' : '' ?>>Adidas</option>
            <option value="Nike" <?= ($product['brand'] == 'Nike') ? 'selected' : '' ?>>Nike</option>
            <option value="Kamito" <?= ($product['brand'] == 'Kamito') ? 'selected' : '' ?>>Kamito</option>
            <option value="Mizuno" <?= ($product['brand'] == 'Mizuno') ? 'selected' : '' ?>>Mizuno</option>
            <option value="Puma" <?= ($product['brand'] == 'Puma') ? 'selected' : '' ?>>Puma</option>
        </select>
        
        <label style="font-weight:600;">Danh mục:</label> 
        <select name="category" required style="width:100%; padding:10px; margin:8px 0 16px; border:1px solid #ccc; border-radius:6px;">
            <option value="">-- Chọn danh mục --</option> 
            <option value="giay" <?= $product['category'] === 'giay' ? 'selected' : '' ?>>Giày</option>
            <option value="gangtay" <?= $product['category'] === 'gangtay' ? 'selected' : '' ?>>Găng tay</option>
            <option value="phukien" <?= $product['category'] === 'phukien' ? 'selected' : '' ?>>Phụ kiện</option> 
        </select>

        <label style="font-weight:600;">Loại Size:</label>
        <select name="type_id" required style="width:100%; padding:10px; margin:8px 0 16px; border:1px solid #ccc; border-radius:6px;">
            <option value="">-- Chọn loại size --</option>
            <option value="1" <?= $product['type_id'] == 1 ? 'selected' : '' ?>>Giày / Lót giày (39-45)</option>
            <option value="2" <?= $product['type_id'] == 2 ? 'selected' : '' ?>>Găng tay (7-10)</option>
            <option value="3" <?= $product['type_id'] == 3 ? 'selected' : '' ?>>Áo (S-XXL)</option>
            <option value="4" <?= $product['type_id'] == 4 ? 'selected' : '' ?>>Không có size</option>
        </select>
        
        <label style="font-weight:600;">Loại (Type phụ):</label> 
        <select id="type" name="type" style="width:100%; padding:10px; margin:8px 0 16px; border:1px solid #ccc; border-radius:6px;">
             <option value="" <?= is_null($product['type']) ? 'selected' : '' ?>>-- Không có --</option>
             <option value="Áo" <?= ($product['type'] == 'Áo') ? 'selected' : '' ?>>Áo</option>
             <option value="Bóng" <?= ($product['type'] == 'Bóng') ? 'selected' : '' ?>>Bóng</option>
             <option value="Dây giày" <?= ($product['type'] == 'Dây giày') ? 'selected' : '' ?>>Dây giày</option>
             <option value="Balo" <?= ($product['type'] == 'Balo') ? 'selected' : '' ?>>Balo</option>
             <option value="Túi rút" <?= ($product['type'] == 'Túi rút') ? 'selected' : '' ?>>Túi rút</option>
             <option value="Tất" <?= ($product['type'] == 'Tất') ? 'selected' : '' ?>>Tất</option>
             <option value="Băng keo" <?= ($product['type'] == 'Băng keo') ? 'selected' : '' ?>>Băng keo</option>
             <option value="Lót giày" <?= ($product['type'] == 'Lót giày') ? 'selected' : '' ?>>Lót giày</option>
             <option value="Xịt khử mùi" <?= ($product['type'] == 'Xịt khử mùi') ? 'selected' : '' ?>>Xịt khử mùi</option>
             <option value="Đinh AG" <?= ($product['type'] == 'Đinh AG') ? 'selected' : '' ?>>Đinh AG</option>
             <option value="Đinh TF" <?= ($product['type'] == 'Đinh TF') ? 'selected' : '' ?>>Đinh TF</option>
             <option value="Đinh FG" <?= ($product['type'] == 'Đinh FG') ? 'selected' : '' ?>>Đinh FG</option>
        </select>

        <label style="font-weight:600;">Ảnh sản phẩm hiện tại:</label><br>
        <?php if($product['image']): ?>
        <img src="../assets/uploads/<?= htmlspecialchars($product['image']) ?>"
             alt="Ảnh sản phẩm" style="width:100px; height:100px; object-fit:cover; border-radius:8px; margin-bottom:10px; border:1px solid #eee;">
        <?php else: ?>
        <p>Chưa có ảnh.</p>
        <?php endif; ?>
        <br>
        <label style="font-weight:600;">Chọn ảnh mới (để thay thế):</label>
        <input type="file" name="image" accept="image/*" style="margin-bottom:20px; display:block;">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px;">
            <a href="products.php"
               style="color:#555; text-decoration:none; font-weight:600;">← Quay lại danh sách</a>
            <button type="submit"
                    style="background:#f5b400; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer;">
                Lưu thay đổi
            </button>
        </div>
    </form>
</main>

<?php include("../includes/footer.php"); ?>