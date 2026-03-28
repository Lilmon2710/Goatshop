<?php
session_start();
include(__DIR__ . '/../includes/db_connect.php');

$vnp_HashSecret = "3PUHD3439H612D9ERCLUWXCEGL6GTIU3";

// LẤY DATA
$vnpData = $_GET;
$vnp_SecureHash = $vnpData['vnp_SecureHash'];
unset($vnpData['vnp_SecureHash']);

// SORT
ksort($vnpData);

// BUILD HASH
$hashData = "";
$i = 0;
foreach ($vnpData as $key => $value) {
    if ($i == 1) {
        $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
    }
    else {
        $hashData .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

// VERIFY
$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

// CHECK
if ($secureHash === $vnp_SecureHash) {

    $order_code = $vnpData['vnp_TxnRef'];
    $responseCode = $vnpData['vnp_ResponseCode'];

    // SUCCESS
    if ($responseCode == '00') {

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
    echo "<script>alert('Sai chữ ký giao dịch hợp lệ!'); window.location.href='../index.php';</script>";
    exit;
}