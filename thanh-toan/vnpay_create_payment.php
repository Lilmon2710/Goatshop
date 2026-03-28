<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

include(__DIR__ . '/../includes/db_connect.php');

// CONFIG
$vnp_TmnCode = "4LNKRNI0";
$vnp_HashSecret = "3PUHD3439H612D9ERCLUWXCEGL6GTIU3";
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
$vnp_Returnurl = "http://localhost/Goatshop/thanh-toan/vnpay_return.php";

// CHECK order_id
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

if (!$order)
    die("Không tìm thấy đơn");

// CHỈ CHO THANH TOÁN KHI PENDING
if ($order['status'] !== 'pending' && $order['status'] !== 'Chờ thanh toán') {
    die("Đơn này không thể thanh toán");
}

// DATA
$vnp_TxnRef = $order['order_code'];
$vnp_OrderInfo = "Thanh toan don hang " . $order['order_code'];
$vnp_Amount = $order['total_amount'] * 100;

// IP
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
if ($ip == '::1')
    $ip = '127.0.0.1';

// PARAM
$inputData = [
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_Amount" => $vnp_Amount,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_ExpireDate" => date('YmdHis', strtotime('+15 minutes')),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $ip,
    "vnp_Locale" => "vn",
    "vnp_OrderInfo" => $vnp_OrderInfo,
    "vnp_OrderType" => "billpayment",
    "vnp_ReturnUrl" => $vnp_Returnurl,
    "vnp_TxnRef" => $vnp_TxnRef
];

ksort($inputData);

// BUILD HASH
$hashdata = "";
$query = "";
$i = 0;
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    }
    else {
        $hashdata .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
}

$query = rtrim($query, '&');
$vnp_SecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

$paymentUrl = $vnp_Url . '?' . $query . '&vnp_SecureHash=' . $vnp_SecureHash;

header('Location: ' . $paymentUrl);
exit; 