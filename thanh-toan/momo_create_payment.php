<?php
session_start();
include(__DIR__ . '/../includes/db_connect.php');

if (!isset($_GET['order_id'])) {
    die("Thiếu order_id");
}

$order_id = (int)$_GET['order_id'];

// LẤY ORDER
$sql = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Không tìm thấy đơn");
}

if ($order['status'] !== 'pending' && $order['status'] !== 'Chờ thanh toán') {
    die("Đơn này không thể thanh toán tiếp");
}

// Load MoMo Payment Config
$momoConfig = require_once(__DIR__ . '/momo_config.php');

// Tạo parameters
$orderId = $order['order_code'] . '_' . time();
$requestId = time() . '';
$amount = (int)$order['total_amount'];
$orderInfo = 'Thanh toan don hang ' . $order['order_code'];
$extraData = '';

// Tính signature
$rawData = 'partnerCode=' . $momoConfig['PartnerCode'] .
           '&accessKey=' . $momoConfig['AccessKey'] .
           '&requestId=' . $requestId .
           '&amount=' . $amount .
           '&orderId=' . $orderId .
           '&orderInfo=' . $orderInfo .
           '&returnUrl=' . $momoConfig['ReturnUrl'] .
           '&notifyUrl=' . $momoConfig['NotifyUrl'] .
           '&extraData=' . $extraData;

$signature = hash_hmac('sha256', $rawData, $momoConfig['SecretKey']);

// Dữ liệu gửi tới MoMo
$requestData = array(
    'accessKey' => $momoConfig['AccessKey'],
    'partnerCode' => $momoConfig['PartnerCode'],
    'requestType' => $momoConfig['RequestType'],
    'notifyUrl' => $momoConfig['NotifyUrl'],
    'returnUrl' => $momoConfig['ReturnUrl'],
    'orderId' => $orderId,
    'amount' => (string)$amount,
    'orderInfo' => $orderInfo,
    'requestId' => $requestId,
    'extraData' => $extraData,
    'signature' => $signature
);

// Gửi POST request tới MoMo API
$ch = curl_init();
curl_setopt_array($ch, array(
    CURLOPT_URL => $momoConfig['MomoApiUrl'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($requestData),
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json; charset=UTF-8'
    ),
    CURLOPT_TIMEOUT => 30
));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    die("Lỗi kết nối: " . $curlError);
}

$responseData = json_decode($response, true);

if (!$responseData || !isset($responseData['payUrl'])) {
    echo '<pre>';
    echo "HTTP Code: " . $httpCode . "\n";
    echo "Response: " . $response . "\n";
    echo '</pre>';
    die("Lỗi từ MoMo: Không nhận được payUrl");
}

// Chuyển hướng tới trang thanh toán MoMo
header('Location: ' . $responseData['payUrl']);
exit;
?>
