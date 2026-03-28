<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
    header("Location: index.php?page=login");
    exit;
}

include('includes/db_connect.php');
// AUTO HUỶ SAU 15 PHÚT
$conn->query("
    UPDATE orders 
    SET status = 'cancelled'
    WHERE status = 'pending' 
    AND created_at < NOW() - INTERVAL 15 MINUTE
");

$user_id = $_SESSION['user']['id'];

// Truy vấn đơn hàng của user
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$statusText = [
  'pending' => 'Chờ thanh toán',
  'processing' => 'Đang xử lý',
  'paid' => 'Đã thanh toán',
  'shipping' => 'Đang giao hàng',
  'completed' => 'Hoàn thành',
  'cancelled' => 'Đã huỷ',
  'failed' => 'Thanh toán thất bại'
];

$statusColor = [
  'pending' => 'bg-secondary',
  'processing' => 'bg-info text-dark',
  'paid' => 'bg-success',
  'shipping' => 'bg-warning text-dark',
  'completed' => 'bg-success',
  'cancelled' => 'bg-danger',
  'failed' => 'bg-danger'
];
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Lịch sử đơn hàng</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .order-card { border: 1px solid #ccc; border-radius: 8px; padding: 15px; margin-bottom: 15px; }
  </style>
</head>
<body>
  <?php include('includes/header.php'); ?>
        <link rel="stylesheet" href="assets/css/main.css">
        <link rel="stylesheet" href="assets/css/header.css">
        <link rel="stylesheet" href="assets/css/category.css">

  <div class="container mt-5">
    <h2>Lịch sử đơn hàng của bạn</h2>
    <hr>

   <?php if (!empty($orders)): ?>
    
    <?php foreach ($orders as $order): ?>
      <div class="order-card">

        <h5>Mã đơn: 
          <span class="text-primary"><?= htmlspecialchars($order['order_code']) ?></span>
        </h5>

        <p><strong>Ngày đặt:</strong> <?= htmlspecialchars($order['created_at']) ?></p>
        <p><strong>Tổng tiền:</strong> <?= number_format($order['total_amount'], 0, ',', '.') ?>đ</p>
        <p><strong>Phương thức thanh toán:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
        <p><strong>Vận chuyển:</strong> <?= htmlspecialchars($order['shipping_method']) ?></p>

        <!-- STATUS -->
        <p><strong>Trạng thái:</strong> 
          <span class="badge <?= $statusColor[$order['status']] ?? 'bg-secondary' ?>">
            <?= $statusText[$order['status']] ?? $order['status'] ?>
          </span>
        </p>

        <!-- ACTION -->
        <div class="mt-2 text-end">
          <?php if ($order['status'] == 'pending' || $order['status'] == 'failed'): ?>
            <a href="thanh-toan/vnpay_create_payment.php?order_id=<?= $order['id'] ?>" 
               class="btn btn-sm btn-success">
               🛒 Thanh toán ngay
            </a>
          <?php endif; ?>

          <?php if ($order['status'] == 'pending' || $order['status'] == 'processing'): ?>
            <a href="actions/cancel_order.php?id=<?= $order['id'] ?>" 
               class="btn btn-sm btn-outline-danger"
               onclick="return confirm('Bạn có chắc chắn muốn huỷ đơn hàng này?')">
               Huỷ đơn
            </a>
          <?php elseif ($order['status'] == 'paid'): ?>
            <span class="badge bg-success">✔ Đã thanh toán</span>


          <?php elseif ($order['status'] == 'shipping'): ?>
            <span class="text-warning">🚚 Đang giao</span>

          <?php elseif ($order['status'] == 'completed'): ?>
            <span class="text-success">🎉 Hoàn thành</span>

          <?php elseif ($order['status'] == 'cancelled'): ?>
            <span class="text-danger">❌ Đã huỷ</span>

          <?php endif; ?>
        </div>

      </div>
    <?php endforeach; ?>

  <?php else: ?>
    <div class="alert alert-warning">Không có đơn hàng nào 😢</div>
  <?php endif; ?>

</div>

</body>
</html>