<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Xác định đường dẫn gốc (nếu file đang nằm trong /admin/)
$basePath = '';
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
    $basePath = '../';
}

// ✅ Tính tổng số sản phẩm trong giỏ hàng
$total_items = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total_items += $item['quantity'];
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Goat Shop</title>
<link rel="shortcut icon" href="<?= $basePath ?>favicon.ico?v=<?= time() ?>" type="image/x-icon">
<link rel="icon" href="<?= $basePath ?>favicon.ico?v=<?= time() ?>" type="image/x-icon">
<!-- ✅ Liên kết CSS & Icon -->
<link rel="stylesheet" href="<?= $basePath ?>assets/css/header.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= $basePath ?>assets/css/main.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

</head>
<body>

<header class="header">
  <a href="<?= $basePath ?>index.php" class="header-left" style="text-decoration: none; color: inherit;">
    <img src="<?= $basePath ?>assets/images/logo.png?v=<?= time() ?>" alt="Goat Shop" class="logo">
    <h1 class="shop-name">Goat Shop</h1>
  </a>


  <div class="header-center">
    <form action="index.php" method="get" class="search-form">
      <input type="text" 
             name="search" 
             placeholder="Tìm kiếm..." 
             value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
      <button type="submit" class="search-btn">
        <i class="fa fa-search"></i>
      </button>
    </form>

    <div class="header-contact">
      <span class="contact-label">Hotline hỗ trợ:</span>
      <span class="contact-phone">08.3266.8882</span>
    </div>

    <a href="<?= $basePath ?>cart.php" class="order-now-btn">
      <i class="fa-solid fa-bolt"></i> ĐẶT HÀNG NGAY
    </a>
  </div>

  <div class="header-right">
    <?php if (isset($_SESSION['user'])): ?>
      <div class="user-dropdown">
        <span class="user-name">
          Xin chào, <b><?= htmlspecialchars($_SESSION['user']['username']) ?></b> 
          <i class="fa fa-caret-down"></i>
        </span>
   <div class="dropdown-content">
        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
            <a href="<?= $basePath ?>admin/orders.php">Quản lý đơn hàng</a>
            <a href="<?= $basePath ?>admin/products.php">Quản lý sản phẩm</a>

        <?php endif; ?>
        <?php if ($_SESSION['user']['role'] === 'customer'): ?>
                        <a href="<?= $basePath ?>orders.php">Lịch sử Đơn hàng</a> 
                    <?php endif; ?>
      </div>
      </div> <!-- End user-dropdown -->

      <a href="<?= $basePath ?>logout.php" class="btn" style="background: transparent; color: #d40000; border: 1px solid #d40000; padding: 6px 15px; font-weight: 600;">
         Đăng xuất
      </a>
    <?php else: ?>
      <a href="<?= $basePath ?>index.php?page=login" class="btn">Đăng nhập</a>
    <?php endif; ?>

    <!-- 🛒 Giỏ hàng (hiển thị tổng số sản phẩm) -->
    <a href="<?= $basePath ?>cart.php" class="cart-link" style="font-size: 20px; color: #222; position: relative; margin-left: 10px;">
      <i class="fa fa-shopping-cart"></i>
      <span id="cart-count" style="position: absolute; top: -8px; right: -12px; background: #d40000; color: #fff; font-size: 12px; font-weight: bold; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center;"><?= $total_items ?></span>
    </a>
  </div>
</header>

 <nav class="category-bar" aria-label="Danh mục">

<a class="category" href="<?= $basePath ?>index.php">
  <i class="fa-solid fa-border-all"></i>
  <span>Tất cả</span>
</a>

<a class="category" href="<?= $basePath ?>index.php?category=giay">
  <img src="<?= $basePath ?>assets/images/shoe.svg" alt="Giày" />
  <span>Giày</span>
</a>

<a class="category" href="<?= $basePath ?>index.php?category=gangtay">
  <i class="fa-solid fa-hand-back-fist"></i>
  <span>Găng tay</span>
</a>

<a class="category" href="<?= $basePath ?>index.php?category=phukien">
  <i class="fa-solid fa-box-open"></i>
  <span>Phụ kiện</span>
</a>
</nav>

