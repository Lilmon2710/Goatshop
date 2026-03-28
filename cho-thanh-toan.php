<?php
$order_code = htmlspecialchars($_GET['order_code'] ?? 'LỖI');
$total = (int)($_GET['total'] ?? 0);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chờ Thanh Toán Đơn Hàng</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; box-sizing: border-box; }
        .container { max-width: 500px; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center; }
        h2 { margin-top: 0; }
        img { max-width: 250px; border: 1px solid #ddd; border-radius: 5px; }
        .payment-details { background: #f9f9f9; padding: 20px; border-radius: 5px; margin-top: 20px; }
        .payment-details p { margin: 10px 0; font-size: 1.1em; line-height: 1.5; }
        .payment-details .amount, .payment-details .content { font-size: 1.3em; font-weight: bold; color: #e53935; }
        .payment-details .content { word-break: break-all; }
        .footer-note { margin-top: 20px; }

        .home-link {
  text-decoration: none; 
  color: #f5a623;      
  font-weight: bold;   
}


.home-link:hover {
  color: #e49712; 
  text-decoration: underline; 
}

.switch-link {
    text-decoration: none; 
    color: #f5a623
}

.switch-link:hover {
  color: #e49712;
  text-decoration: underline; 
}
    </style>
</head>
<body>
    <div class="container">
        <h2>Cảm ơn bạn đã đặt hàng!</h2>
        <p>Vui lòng quét mã QR dưới đây để hoàn tất đơn hàng.</p>
        
        <img src="assets/images/myqr.jpg" alt="Mã QR thanh toán">

        <div class="payment-details">
            <p>Ngân hàng: TECHCOMBANK</p>
            <p>Số tài khoản: 271020046666</p>
            <p>Chủ tài khoản: NGO HOANG SON</p>
            
            <p>Số tiền:
                <strong class="amount"><?= number_format($total, 0, ',', '.') ?>đ</strong>
            </p>
            
            <p>Nội dung chuyển khoản (Bắt buộc):<br>
                <strong class="content"><?= $order_code ?></strong>
            </p>
        </div>
        
        <div class="footer-note">
            <small>Sau khi chuyển khoản, shop sẽ xác nhận và gửi hàng.</small><br><br>
            <a href="actions/switch-to-cod.php?order_code=<?= $order_code ?>" class="switch-link">
        Tôi muốn đổi sang thanh toán khi nhận hàng (COD)
    </a>
    <br><br>
            <a href="index.php" class="home-link">Quay về trang chủ</a>
        </div>
    </div>
</body>
</html>