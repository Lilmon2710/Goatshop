    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    include("includes/db_connect.php");

    $category = $_GET['category'] ?? '';
    $page = $_GET['page'] ?? '';
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <title>Goat Shop</title>
        <link rel="stylesheet" href="assets/css/main.css">
        <link rel="stylesheet" href="assets/css/header.css">
        <link rel="stylesheet" href="assets/css/category.css">
        <link rel="stylesheet" href="assets/css/products.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    </head>
    <body>

    <?php include("includes/header.php"); ?>

    <main>
    <?php
    // Nếu có ?page=login thì chỉ hiển thị trang login
    if ($page == 'login') {
        include("login.php");
    }
    // Ngược lại hiển thị sản phẩm
    else {
    // Không ép category mặc định nữa để trang chủ là "Tất cả"

    echo '<div class="product-page">';
    if (!empty($category)) {
        echo '<div class="sidebar">';
        if ($category == 'giay') {
            include("includes/filters/filter_giay.php");
        } elseif ($category == 'gangtay') {
            include("includes/filters/filter_gangtay.php");
        } elseif ($category == 'phukien') {
            include("includes/filters/filter_phukien.php");
        }
        echo '</div>';
    }

    echo '<div class="product-grid">';

    // --- Xử lý tìm kiếm + lọc ---
    // Join với bảng product_stock để tính tổng số lượng tồn kho
    $sql = "SELECT p.*, COALESCE(SUM(ps.quantity), 0) AS total_qty 
            FROM products p 
            LEFT JOIN product_stock ps ON p.id = ps.product_id 
            WHERE 1=1";

    $search = $_GET['search'] ?? '';
    if (!empty($search)) {
        $search = mysqli_real_escape_string($conn, $search);
        $sql .= " AND (p.name LIKE '%$search%' OR p.code LIKE '%$search%')";
    } elseif (!empty($category)) {
        $sql .= " AND p.category='$category'";
    }

    if (isset($_GET['type'])) {
        $type = mysqli_real_escape_string($conn, $_GET['type']);
        $sql .= " AND type='$type'";
    }

    if (isset($_GET['brand'])) {
        $brand = mysqli_real_escape_string($conn, $_GET['brand']);
        $sql .= " AND brand='$brand'";
    }

    if (isset($_GET['price'])) {
        $price = $_GET['price'];
        switch ($price) {
            case 'under300': $sql .= " AND price < 300000"; break;
            case '300-400': $sql .= " AND price BETWEEN 300000 AND 400000"; break;
            case '400-500': $sql .= " AND price BETWEEN 400000 AND 500000"; break;
            case '500-600': $sql .= " AND price BETWEEN 500000 AND 600000"; break;
            case '600-700': $sql .= " AND price BETWEEN 600000 AND 700000"; break;
            case '700-900': $sql .= " AND price BETWEEN 700000 AND 900000"; break;
            case '900-1000': $sql .= " AND price BETWEEN 900000 AND 1000000"; break;
            case 'over1000': $sql .= " AND price > 1000000"; break;
        }
    }

    // Group by sản phẩm và Sắp xếp: Còn hàng lên đầu, hết hàng xuống cuối
    $sql .= " GROUP BY p.id ORDER BY (COALESCE(SUM(ps.quantity), 0) > 0) DESC, p.id DESC";

    // --- Hiển thị sản phẩm ---
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) == 0) {
        echo "<p class='no-products'>Không có sản phẩm nào phù hợp.</p>";
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $is_out_of_stock = ($row['total_qty'] <= 0);
        $out_of_stock_class = $is_out_of_stock ? 'is-out-of-stock' : '';
        
        echo '
        <div class="product-card ' . $out_of_stock_class . '">
            <a href="' . ($is_out_of_stock ? 'javascript:void(0)' : 'product_detail.php?id=' . $row['id']) . '">
                <img src="assets/uploads/' . $row['image'] . '" alt="' . $row['name'] . '">
                <div class="product-info">
                    <h3>' . $row['name'] . '</h3>
                    <p class="price">
                        ' . number_format($row['price'], 0, ',', '.') . 'đ
                        ' . ($is_out_of_stock ? '<span class="out-of-stock-text">(Hết hàng)</span>' : '') . '
                    </p>
                    <p class="code">Mã: ' . $row['code'] . '</p>
                </div>
            </a>
        </div>';
    }


    echo '</div>'; // end .product-grid
    echo '</div>'; // end .product-page

    }
    ?>
    </main>

    <?php include("includes/footer.php"); ?>

    </body>
    </html>
