<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("includes/db_connect.php");

// ✅ Lấy id sản phẩm từ URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM products WHERE id = $id";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
    }
    else {
        echo "<p style='color:red; text-align:center;'>❌ Sản phẩm không tồn tại!</p>";
        exit;
    }
}
else {
    echo "<p style='color:red; text-align:center;'>⚠ Không tìm thấy sản phẩm!</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo $row['name']; ?> | Goat Shop</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/category.css">
    <link rel="stylesheet" href="assets/css/product_detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include("includes/header.php"); // Nếu là sản phẩm không có size (loại 4), lấy luôn số lượng của "Kích thước chung"
if ($row['type_id'] == 4) {
    $stock_res = mysqli_query($conn, "SELECT quantity FROM product_stock WHERE product_id = $id AND size = 'Kích thước chung' LIMIT 1");
    $srow = mysqli_fetch_assoc($stock_res);
    $current_stock = $srow['quantity'] ?? 0;
}
?>
<div class="product-detail">
    <div class="left">
        <img src="assets/uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
    </div>
    <div class="right">
        <h2><?= htmlspecialchars($row['name']) ?></h2>
        <p class="price"><?= number_format($row['price'], 0, ',', '.') ?>đ</p>
        <p class="masp">Mã sản phẩm: <b><?= htmlspecialchars($row['code']) ?></b></p>
        
        <?php if ($row['type_id'] == 4): ?>
            <div class="status">TRẠNG THÁI: <?= ($current_stock > 0) ? 'CÒN HÀNG ✅' : '<span class="text-danger">HẾT HÀNG ❌</span>' ?></div>
            <script>document.addEventListener("DOMContentLoaded", () => { document.getElementById('selectedSize').value = "Kích thước chung"; });</script>
        <?php else: ?>
            <div class="status">HÀNG CÓ SẴN ✅</div>
        <?php endif; ?>

        <?php
$size_stock = [];
$stock_res = mysqli_query($conn, "SELECT size, quantity FROM product_stock WHERE product_id = $id");
while ($srow = mysqli_fetch_assoc($stock_res)) {
    $size_stock[$srow['size']] = $srow['quantity'];
}

$size_options = [];
switch ($row['type_id']) {
    case 1:
        $size_options = ['35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45'];
        break;
    case 2:
        $size_options = ['7', '8', '9', '10'];
        break;
    case 3:
        $size_options = ['S', 'M', 'L', 'XL', 'XXL'];
        break;
    default:
        $size_options = [];
}

if (!empty($size_options)) {
    echo '<div class="size"><b>CHỌN SIZE:</b><br>';
    foreach ($size_options as $s) {
        $qty = $size_stock[$s] ?? 0;
        if ($qty > 0) {
            echo "<button type='button' class='size-btn' data-size='$s'>$s</button>";
        }
        else {
            echo "<button type='button' class='size-btn disabled' disabled title='Hết hàng'>$s</button>";
        }
    }
    echo '</div>';
}
?>
        <div class="qty">
            <b>CHỌN SỐ LƯỢNG:</b><br>
            <div class="qty-controls">
                <button type="button" onclick="giam()">-</button>
                <input type="text" id="soluong" value="1" readonly>
                <button type="button" onclick="tang()">+</button>
            </div>
        </div>

<div class="product-buttons" style="display:flex; gap:10px; margin-top:15px;">

    <form action="actions/add_to_cart.php" method="POST" id="cartForm">
        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
        <input type="hidden" id="selectedSize" name="size" value="">
        <input type="hidden" id="selectedQty" name="quantity" value="1">

        <button type="submit" class="btn-add-cart">
            🛒 THÊM VÀO GIỎ HÀNG
        </button>
<button type="submit" name="buy_now" value="1" class="btn-buy-now">
        MUA TẠI ĐÂY - FREE SHIP
    </button>

</form>
</div>
    </div>
    <div class="info-right">
        <div class="info-item">
            <img src="assets/icons/het-size.png" alt="">
            <div>
                <b>KHÔNG SỢ HẾT SIZE</b>
                <p>Do chẳng cần đợi nhân viên chốt đơn</p>
            </div>
        </div>
        <div class="info-item">
            <img src="assets/icons/giao-hang.png" alt="">
            <div>
                <b>GIAO HÀNG TOÀN QUỐC</b>
                <p>Gửi hàng đi luôn trong ngày</p>
            </div>
        </div>
        <div class="info-item">
            <img src="assets/icons/thanh-toan.png" alt="">
            <div>
                <b>THANH TOÁN LINH HOẠT</b>
                <p>Tiền mặt/CK/ví điện tử/thẻ</p>
            </div>
        </div>
        <div class="info-item">
            <img src="assets/icons/doi-size.png" alt="">
            <div>
                <b>ĐỔI SIZE THOẢI MÁI</b>
                <p>Đến khi anh em hài lòng</p>
            </div>
        </div>
        <div class="info-item">
            <img src="assets/icons/bao-hanh.png" alt="">
            <div>
                <b>BẢO HÀNH TRỌN ĐỜI</b>
                <p>Lại dễ dàng chỉ cần đọc SĐT</p>
            </div>
        </div>
        <div class="info-item">
            <img src="assets/icons/tri-an.png" alt="">
            <div>
                <b>LUÔN LUÔN TRI ÂN</b>
                <p>100% tích điểm, giảm giá lần sau</p>
            </div>
        </div>
    </div>    
</div>

<?php include("includes/footer.php"); ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const sizeButtons = document.querySelectorAll('.size-btn');
    const sizeInput = document.getElementById('selectedSize');
    const form = document.getElementById("cartForm");

    // Xử lý chọn size
    if (sizeButtons.length > 0) {
        sizeButtons.forEach(button => {
            button.addEventListener("click", () => {
                sizeButtons.forEach(b => b.classList.remove("active"));
                button.classList.add("active");
                sizeInput.value = button.dataset.size;
            });
        });
    }

    // Xử lý tăng giảm số lượng
    window.tang = function() {
        let sl = document.getElementById('soluong');
        sl.value = parseInt(sl.value) + 1;
        document.getElementById("selectedQty").value = sl.value;
    }

    window.giam = function() {
        let sl = document.getElementById('soluong');
        if (parseInt(sl.value) > 1) sl.value = parseInt(sl.value) - 1;
        document.getElementById("selectedQty").value = sl.value;
    }

    // Kiểm tra khi submit form
    form.addEventListener("submit", function(e) {
        const hasSizeOptions = sizeButtons.length > 0;

        if (hasSizeOptions && !sizeInput.value) {
            alert("⚠️ Vui lòng chọn size trước khi tiếp tục!");
            e.preventDefault();
        }
    });
});
</script>

</body>
</html>
