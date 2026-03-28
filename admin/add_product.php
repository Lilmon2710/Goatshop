<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("../includes/db_connect.php");

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php?page=login");
    exit;
}

// --- XỬ LÝ FORM ---
if (isset($_POST['add'])) {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $code = trim($_POST['code']);
    $image = $_FILES['image']['name'];
    
    // Lấy đúng giá trị từ form
    $category = trim($_POST['category']); // Danh mục (Chủng loại chính)
    $type = trim($_POST['type']);         // Loại phụ (Type)
    $type_id = (int)$_POST['type_id'];    // Loại size (Type_ID)
    $brand = trim($_POST['brand']);       // Thương hiệu (Brand)

    // Xử lý giá trị rỗng thành chuỗi trống '' (tránh lỗi NOT NULL trong DB)
    $brand_value = empty($brand) ? '' : $brand;
    $type_value = empty($type) ? '' : $type;       
    $category_value = empty($category) ? '' : $category; 

    // Xử lý upload ảnh
    $image_name = ''; // Khởi tạo biến lưu tên ảnh (đúng)
    if ($image) {
        $target_dir = "../assets/uploads/";
        $image_name = uniqid() . "_" . basename($image);
        $target_file = $target_dir . $image_name;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
             echo "<script>alert('❌ Lỗi khi tải ảnh lên!');</script>";
             $image_name = '';
        }
    }
    
    // SỬA DÒNG SQL VÀ BIND_PARAM CHO ĐÚNG:
    // Cột 'category' là Danh mục chính, cột 'type' là Loại phụ.
    $sql = "INSERT INTO products (name, price, category, type, code, image, type_id, brand)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"; 

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Lỗi prepare SQL: " . $conn->error);
    }
    
    // SỬA: Dùng $image_name thay vì $image
    // Bind param: name(s), price(d), category(s), type(s), code(s), image(s), type_id(i), brand(s)
    $stmt->bind_param("sdssssis",
        $name,
        $price,
        $category_value, // Danh mục (Chủng loại chính)
        $type_value,     // Loại phụ (Type)
        $code,
        $image_name,     // ĐÃ SỬA: Dùng tên file đã được đổi tên
        $type_id,
        $brand_value
    );

    if ($stmt->execute()) {
        echo "<script>alert('✅ Thêm sản phẩm thành công!'); window.location='products.php';</script>";
        exit;
    } else {
        echo "<script>alert('❌ Lỗi khi thêm sản phẩm: " . $stmt->error . "');</script>";
    }
    $stmt->close();
    $conn->close();
}

// --- include header ---
include("../includes/header.php");
?>

<link rel="stylesheet" href="../assets/css/header.css">
<link rel="stylesheet" href="../assets/css/products.css">
<link rel="stylesheet" href="../assets/css/category.css">

<main style="display:flex; justify-content:center; align-items:flex-start; padding:50px 0; background:#fafafa; min-height:calc(100vh - 150px);">
    <div style="background:white; width:600px; padding:40px 50px; border-radius:15px; box-shadow:0 4px 15px rgba(0,0,0,0.08);">

        <h2 style="text-align:center; font-size:22px; font-weight:700; margin-bottom:30px;">
            <i class="fa fa-plus-circle" style="color:#f5b400;"></i> Thêm sản phẩm mới
        </h2>

        <form action="" method="POST" enctype="multipart/form-data">
            <div style="margin-bottom:18px;">
                <label for="name" style="display:block; font-weight:600; margin-bottom:8px;">Tên sản phẩm:</label>
                <input type="text" id="name" name="name" required style="width:100%; padding:10px 12px; border:1px solid #ccc; border-radius:6px; font-size:15px;">
            </div>

            <div style="margin-bottom:18px;">
                <label for="price" style="display:block; font-weight:600; margin-bottom:8px;">Giá (VNĐ):</label>
                <input type="number" id="price" name="price" required style="width:100%; padding:10px 12px; border:1px solid #ccc; border-radius:6px; font-size:15px;">
            </div>

            <div style="margin-bottom:18px;">
                <label for="brand" style="display:block; font-weight:600; margin-bottom:8px;">Thương hiệu:</label>
                <select id="brand" name="brand" style="width:100%; padding:10px 12px; border:1px solid #ccc; border-radius:6px; font-size:15px;">
                    <option value="">-- Không có --</option>
                    <option value="ZOCKER">ZOCKER</option>
                    <option value="ADIDAS">ADIDAS</option>
                    <option value="NIKE">NIKE</option>
                    <option value="KAMITO">KAMITO</option>
                    <option value="WIKA">WIKA</option>
                    <option value="MIZUNO">MIZUNO</option>
                    <option value="PUMA">PUMA</option>
                    <option value="GKVN">GKVN</option>
                </select>
                <small>(Để trống nếu không có)</small>
            </div>

            <div style="margin-bottom:18px;">
                 <label for="category" style="display:block; font-weight:600; margin-bottom:8px;">Danh mục:</label>
                 <select id="category" name="category" required style="width:100%; padding:10px 12px; border:1px solid #ccc; border-radius:6px; font-size:15px;">
                     <option value="">-- Chọn danh mục --</option>
                     <option value="giay">Giày</option>
                     <option value="gangtay">Găng tay</option>
                     <option value="phukien">Phụ kiện</option>
                 </select>
            </div>

            <div style="margin-bottom:18px;">
                 <label for="type_id" style="display:block; font-weight:600; margin-bottom:8px;">Loại Size:</label>
                 <select id="type_id" name="type_id" required style="width:100%; padding:10px 12px; border:1px solid #ccc; border-radius:6px; font-size:15px;">
                     <option value="">-- Chọn loại size --</option>
                     <option value="1">Giày / Lót giày (39-45)</option>
                     <option value="2">Găng tay (7-10)</option>
                     <option value="3">Áo (S-XXL)</option>
                     <option value="4">Không có size</option>
                 </select>
            </div>

             <div style="margin-bottom:18px;">
                 <label for="type">Loại (Type phụ):</label>
                 <select id="type" name="type" style="width:100%; padding:10px 12px; border:1px solid #ccc; border-radius:6px; font-size:15px;">
                     <option value="">-- Không có --</option>
                     <option value="Áo">Áo</option>
                     <option value="Bóng">Bóng</option>
                     <option value="Dây giày">Dây giày</option>
                     <option value="Balo">Balo</option>
                     <option value="Túi rút">Túi rút</option>
                     <option value="Tất">Tất</option>
                     <option value="Băng keo">Băng keo</option>
                     <option value="Lót giày">Lót giày</option>
                     <option value="Xịt khử mùi">Xịt khử mùi</option>
                     <option value="Đinh AG">Đinh AG</option>
                     <option value="Đinh TF">Đinh TF</option>
                     <option value="Đinh FG">Đinh FG</option>
                 </select>
                 <small>(Ví dụ: Tất, Băng keo, Lót giày... Để trống nếu không có)</small>
            </div>

            <div style="margin-bottom:18px;">
                <label for="code" style="display:block; font-weight:600; margin-bottom:8px;">Mã sản phẩm:</label>
                <input type="text" id="code" name="code" required style="width:100%; padding:10px 12px; border:1px solid #ccc; border-radius:6px; font-size:15px;">
            </div>

            <div style="margin-bottom:25px;">
                <label for="image" style="display:block; font-weight:600; margin-bottom:8px;">Hình ảnh:</label>
                <input type="file" id="image" name="image" accept="image/*" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
            </div>

            <button type="submit" name="add" style="background:#f5b400; color:#000; border:none; padding:12px 25px; border-radius:8px; font-weight:600; cursor:pointer; transition:0.2s; width:100%; font-size:16px;">
                <i class="fa fa-save"></i> Lưu sản phẩm
            </button>

            <a href="products.php" style="display:block; text-align:center; margin-top:20px; color:#555; text-decoration:none; font-size:14px;">
                ← Quay lại danh sách
            </a>
        </form>
    </div>
</main>

<?php include("../includes/footer.php"); ?>