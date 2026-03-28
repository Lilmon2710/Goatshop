<?php
session_start();
include(__DIR__ . '/../includes/db_connect.php');

// Load MoMo Payment Config
$momoConfig = require_once(__DIR__ . '/momo_config.php');
$partnerCode = $momoConfig['PartnerCode'];
$accessKey = $momoConfig['AccessKey'];
$secretKey = $momoConfig['SecretKey'];

if (isset($_GET['partnerCode'])) {
    $partnerCode = $_GET['partnerCode'];
    $orderId = $_GET['orderId'];
    $requestId = $_GET['requestId'];
    $amount = $_GET['amount'];
    $orderInfo = $_GET['orderInfo'];
    $orderType = $_GET['orderType'];
    $transId = $_GET['transId'];
    $resultCode = $_GET['resultCode'];
    $message = $_GET['message'];
    $payType = $_GET['payType'];
    $responseTime = $_GET['responseTime'];
    $extraData = $_GET['extraData'];
    $m2signature = $_GET['signature'];

    // Signature theo đúng format của MoMo (thứ tự này là chuẩn)
    $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&message=" . $message . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&orderType=" . $orderType . "&partnerCode=" . $partnerCode . "&payType=" . $payType . "&requestId=" . $requestId . "&responseTime=" . $responseTime . "&resultCode=" . $resultCode . "&transId=" . $transId;

    $signature = hash_hmac("sha256", $rawHash, $secretKey);

    if ($m2signature == $signature) {
        if ($resultCode == '0') {
            // Thanh toán thành công
            // Cắt phần order_code từ orderId (nếu nối thêm time() bằng dấu _)
            $parts = explode('_', $orderId);
            $order_code = $parts[0];

            $sql = "UPDATE orders SET status = 'paid' WHERE order_code = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $order_code);
            $stmt->execute();

            unset($_SESSION['cart']); // Xóa giỏ hàng sau khi thanh toán thành công
            header("Location: ../dat-hang-thanh-cong.php?order_code=" . $order_code);
            exit;
        }
        else {
            echo "<script>alert('Thanh toán thất bại hoặc đã bị hủy!'); window.location.href='../index.php';</script>";
            exit;
        }
    }
    else {
        echo "<script>alert('Sai chữ ký giao dịch (Invalid Signature)!'); window.location.href='../index.php';</script>";
        exit;
    }
}
else {
    echo "<script>alert('Không có dữ liệu trả về từ MoMo!'); window.location.href='../index.php';</script>";
    exit;
}
?>
