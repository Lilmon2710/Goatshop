<?php
/**
 * Cấu hình MoMo Payment Gateway
 * 
 * HƯỚNG DẪN CẬP NHẬT:
 * 1. Khi chuyển sang production, thay đổi MomoApiUrl từ test-payment sang payment.momo.vn
 * 2. Cập nhật SecretKey, AccessKey, PartnerCode từ tài khoản MoMo của bạn
 * 3. Cập nhật ReturnUrl và NotifyUrl khớp với domain thực tế của bạn
 * 4. NotifyUrl cần có khả năng xử lý webhook từ MoMo
 */

return array(
    // API Endpoint
    // TEST: https://test-payment.momo.vn/gw_payment/transactionProcessor
    // LIVE: https://payment.momo.vn/gw_payment/transactionProcessor
    'MomoApiUrl' => 'https://test-payment.momo.vn/gw_payment/transactionProcessor',
    
    // Credentials - Lấy từ Merchant Dashboard của MoMo
    'PartnerCode' => 'MOMO',
    'AccessKey' => 'F8BBA842ECF85',
    'SecretKey' => 'K951B6PE1waDMi640xX08PD3vg6EkVlz',
    
    // Request Type
    // 'captureMoMoWallet' - Thanh toán từ ví MoMo
    // 'payWithATM' - Thanh toán bằng thẻ ATM
    'RequestType' => 'captureMoMoWallet',
    
    // URLs - Cần thay đổi theo domain thực tế
    // ReturnUrl: Trang quay lại sau khi thanh toán (user sẽ được redirect tới đây)
    'ReturnUrl' => 'http://localhost:3000/booking/success',
    
    // NotifyUrl: Webhook endpoint để MoMo gửi thông báo (không cần user truy cập)
    'NotifyUrl' => 'https://76e2dc2e949b.ngrok-free.app/payment/callback'
);
?>
