<?php
// Lấy thông tin từ URL
$order_code = htmlspecialchars($_GET['order_code'] ?? 'LỖI');
$is_switched = isset($_GET['switched']) && $_GET['switched'] == 'true';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt Hàng Thành Công | Goatshop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        :root {
            --success: #00c853;
        }
        * {
            box-sizing: border-box;
        }
        body {
            background: #f8fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            font-family: 'Outfit', sans-serif;
        }
        .success-card {
            background: white;
            padding: 50px 40px;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.05);
            text-align: center;
            max-width: 500px;
            width: 100%;
            animation: slideUp 0.6s cubic-bezier(0.23, 1, 0.32, 1);
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .icon-box {
            width: 100px;
            height: 100px;
            background: #e6fffa;
            color: var(--success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 50px;
            animation: scaleIn 0.5s 0.2s both cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }
        h1 {
            font-weight: 800;
            font-size: 28px;
            color: #1a202c;
            margin-bottom: 12px;
        }
        p {
            color: #718096;
            line-height: 1.6;
            margin-bottom: 24px;
            font-size: 16px;
        }
        .order-info {
            background: #f1f5f9;
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 30px;
        }
        .order-label {
            display: block;
            font-size: 14px;
            color: #64748b;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        .order-id {
            font-size: 24px;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: 1px;
        }
        .delivery-steps {
            text-align: left;
            margin-bottom: 35px;
            border-left: 2px solid #e2e8f0;
            padding-left: 20px;
            margin-left: 10px;
        }
        .step {
            position: relative;
            margin-bottom: 15px;
        }
        .step::before {
            content: '';
            position: absolute;
            left: -26px;
            top: 5px;
            width: 10px;
            height: 10px;
            background: var(--primary);
            border-radius: 50%;
            box-shadow: 0 0 0 4px #fff, 0 0 0 6px #ffe4de;
        }
        .step span {
            display: block;
            font-size: 14px;
            color: #4a5568;
        }
        .action-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: center;
        }
        .btn-home {
            width: 100%;
            max-width: 320px; /* Limit width for better appearance */
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .history-link {
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.2s;
        }
        .history-link:hover {
            color: var(--primary);
        }
        .switch-alert {
            background: #f0fff4;
            color: #2f855a;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            margin-bottom: 25px;
            font-size: 14px;
            border: 1px solid #c6f6d5;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="success-card">
        <?php if ($is_switched): ?>
            <div class="switch-alert">
                ✨ Đã đổi sang thanh toán (COD) thành công!
            </div>
        <?php endif; ?>

        <div class="icon-box">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>

        <h1>Đặt hàng thành công!</h1>
        <p>Cảm ơn bạn đã tin tưởng mua sắm tại Goatshop. Đơn hàng của bạn đang được xử lý.</p>

        <div class="order-info">
            <span class="order-label">Mã đơn hàng</span>
            <div class="order-id"><?= $order_code ?></div>
        </div>

        <div class="delivery-steps">
            <div class="step">
                <span>Dự kiến giao hàng: 2-3 ngày (Hà Nội)</span>
            </div>
            <div class="step">
                <span>Dự kiến giao hàng: 3-5 ngày (Tỉnh khác)</span>
            </div>
            <div class="step">
                <span>Shop sẽ liên hệ xác nhận đơn hàng sớm nhất</span>
            </div>
        </div>

        <div class="action-group">
            <a href="index.php" class="btn btn-home">TIẾP TỤC MUA SẮM</a>
            <a href="orders.php" class="history-link">Xem lịch sử đơn hàng</a>
        </div>
    </div>
</body>
</html>