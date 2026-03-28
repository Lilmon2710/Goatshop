<?php
$order_id = $_GET['order_id'] ?? '';
$amount = $_GET['amount'] ?? '';
$order_code = $_GET['order_code'] ?? '';

if (!$order_id) die("Thiếu thông tin đơn hàng!");

// Tạo signature đúng chuẩn để truyền về momo_return
$partnerCode = "MOMOBKUN20180529";
$accessKey = "klm05TvNCyandK7G";
$secretKey = "at67qH6mk8w5Y1nAwMoYK1801C7L1w2u";

$orderId = $order_code . "_" . time();
$requestId = time() . "";
$orderInfo = "Thanh toan don hang " . $order_code;
$orderType = "momo_wallet";
$transId = time() . random_int(1000, 9999);
$message = "Successful.";
$payType = "qr";
$responseTime = time() . "000";
$extraData = "";

// URL Thành công
$resultCode_Success = "0";
$rawHash_Success = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&message=" . $message . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&orderType=" . $orderType . "&partnerCode=" . $partnerCode . "&payType=" . $payType . "&requestId=" . $requestId . "&responseTime=" . $responseTime . "&resultCode=" . $resultCode_Success . "&transId=" . $transId;
$signature_Success = hash_hmac("sha256", $rawHash_Success, $secretKey);
$query_success = "partnerCode=$partnerCode&orderId=$orderId&requestId=$requestId&amount=$amount&orderInfo=".urlencode($orderInfo)."&orderType=$orderType&transId=$transId&resultCode=$resultCode_Success&message=$message&payType=$payType&responseTime=$responseTime&extraData=$extraData&signature=$signature_Success";
$url_success = "momo_return.php?" . $query_success;

// URL Hủy
$resultCode_Cancel = "1006";
$message_Cancel = "Giao dich bi huy.";
$rawHash_Cancel = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&message=" . $message_Cancel . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&orderType=" . $orderType . "&partnerCode=" . $partnerCode . "&payType=" . $payType . "&requestId=" . $requestId . "&responseTime=" . $responseTime . "&resultCode=" . $resultCode_Cancel . "&transId=" . $transId;
$signature_Cancel = hash_hmac("sha256", $rawHash_Cancel, $secretKey);
$query_cancel = "partnerCode=$partnerCode&orderId=$orderId&requestId=$requestId&amount=$amount&orderInfo=".urlencode($orderInfo)."&orderType=$orderType&transId=$transId&resultCode=$resultCode_Cancel&message=".urlencode($message_Cancel)."&payType=$payType&responseTime=$responseTime&extraData=$extraData&signature=$signature_Cancel";
$url_cancel = "momo_return.php?" . $query_cancel;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cổng thanh toán điện tử MoMo</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; background-color: #f6f6f6; margin: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .momo-box { background: #fff; width: 420px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden; font-size: 14px;}
        .header { background: #a50064; padding: 20px; color: #fff; text-align: center; }
        .content { padding: 30px; text-align: center; }
        .amount-title { color: #888; margin-bottom: 5px; font-weight: 500;}
        .amount { color: #a50064; font-size: 32px; font-weight: bold; margin-bottom: 25px; }
        .order-info { background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 25px; text-align: left; color: #555; border: 1px solid #eee; }
        .order-info span { float: right; font-weight: 600; color: #111; }
        .qr-wrapper { border: 2px solid #a50064; padding: 10px; border-radius: 10px; display: inline-block; margin-bottom: 15px; position: relative;}
        .qr-wrapper img { display: block; width: 200px; height: 200px; }
        .instruction { color: #444; line-height: 1.5; margin-bottom: 25px; }
        .instruction strong { color: #a50064; }
        
        .action-btns { display: flex; justify-content: space-between; gap: 10px; margin-bottom: 5px;}
        .btn { flex: 1; padding: 12px; text-align: center; text-decoration: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: 0.2s; outline: none;}
        .btn-cancel { background: #fff; color: #666; border: 1px solid #ddd; }
        .btn-cancel:hover { background: #f1f1f1; }
        .btn-success { background: #a50064; color: #fff; border: 1px solid #a50064;}
        .btn-success:hover { background: #8a0053; }
        
        .footer { padding: 15px; text-align: center; font-size: 13px; color: #888; border-top: 1px solid #eee; background: #fafafa; }
    </style>
</head>
<body>
    <div class="momo-box">
        <div class="header">
            <h2 style="margin: 0; font-size: 26px; letter-spacing: 1px;">momo</h2>
        </div>
        <div class="content">
            <div class="amount-title">Số tiền thanh toán</div>
            <div class="amount"><?= number_format($amount, 0, ',', '.') ?>đ</div>
            
            <div class="order-info">
                Mã đơn hàng: <span><?= htmlspecialchars($order_code) ?></span><br><br>
                Nhà cung cấp: <span>Goatshop.vn</span>
            </div>

            <div class="qr-wrapper">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=MoMo_<?= $order_code ?>_<?= $amount ?>" alt="MoMo QR">
            </div>
            
            <div class="instruction">
                Sử dụng <strong>App MoMo</strong> hoặc ứng dụng<br>Camera hỗ trợ QR code để quét mã.
            </div>

            <div class="action-btns">
                <a href="<?= $url_cancel ?>" class="btn btn-cancel">Quay về</a>
                <a href="<?= $url_success ?>" class="btn btn-success">Mở ứng dụng Momo</a>
            </div>
        </div>
        <div class="footer">
            Bảo mật thanh toán với công nghệ SSL
        </div>
    </div>
</body>
</html>
