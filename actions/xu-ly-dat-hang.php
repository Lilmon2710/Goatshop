<?php
session_start();

// 1. KẾT NỐI CSDL (Sửa lại thông tin của bạn)
$conn = new mysqli("localhost", "root", "", "goatshop");
$conn->set_charset("utf8mb4");
if ($conn->connect_error) { 
    die("Kết nối thất bại: " . $conn->connect_error); 
}

// 2. KIỂM TRA GIỎ HÀNG
if (empty($_SESSION['cart'])) {
    die("Giỏ hàng của bạn đang trống.");
}

// 3. LẤY DỮ LIỆU TỪ FORM (ĐÃ SỬA LẠI CHO ĐÚNG)
$fullname = $_POST['fullname'];
$phone = $_POST['phone'];
$email = $_POST['email'] ?? ''; 
$address = $_POST['address'];


$province = $_POST['province_name']; // Lấy TÊN tỉnh
$district = $_POST['district_name']; // Lấy TÊN huyện
$ward = $_POST['ward_name'];       // Lấy TÊN xã
// ===================================
 
$note = $_POST['note'] ?? '';
$payment_method = $_POST['payment_method'];
$shipping_method = $_POST['shipping_method'] ?? 'Không xác định';
$user_id = $_SESSION['user_id'] ?? NULL; 

// 4. TÍNH TOÁN TỔNG TIỀN
$cart = $_SESSION['cart'];
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}
$shipping_cost = 0; 
$final_total = $total + $shipping_cost;

// 5. QUYẾT ĐỊNH TRẠNG THÁI
$order_status = ($payment_method == 'cod') ? "processing" : "pending";

// 6. TẠO MÃ ĐƠN HÀNG
$order_code = "BECK" . time();

// 7. BẮT ĐẦU GIAO DỊCH (TRANSACTION) - Chống tranh chấp dữ liệu (Race Condition)
$conn->begin_transaction();

try {
    // 8. LƯU ĐƠN HÀNG VÀO CSDL
    $current_user_id = $_SESSION['user']['id'] ?? null;
    $stmt = $conn->prepare("INSERT INTO orders 
        (order_code, user_id, customer_name, phone, email, address, province_code, district_code, ward_code, note, total_amount, shipping_method, payment_method, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissssssssssss", 
        $order_code, $current_user_id, $fullname, $phone, $email, $address, 
        $province, $district, $ward,
        $note, $final_total, $shipping_method, $payment_method, $order_status
    );
    $stmt->execute();
    $order_id = $stmt->insert_id; 
    $stmt->close();

    // 9. LƯU CHI TIẾT ĐƠN HÀNG VÀ TRỪ KHO
    $stmt_detail = $conn->prepare("
        INSERT INTO order_details 
        (order_id, product_id, product_name, product_size, price, quantity, total_price) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt_stock = $conn->prepare("
        UPDATE product_stock 
        SET quantity = quantity - ? 
        WHERE product_id = ? AND size = ? AND quantity >= ?
    ");

    foreach ($_SESSION['cart'] as $product_key => $item) {
        $total_price = $item['price'] * $item['quantity'];
        $parts = explode("_", $product_key);
        $pid = (int)$parts[0];
        $size = $item['size'];
        $qty = (int)$item['quantity'];

        // A. Lưu chi tiết đơn hàng
        $stmt_detail->bind_param("iissiii", 
            $order_id, $pid, $item['name'], $size, $item['price'], $qty, $total_price
        );
        $stmt_detail->execute();

        // B. Trừ kho hàng (Dùng điều kiện quantity >= $qty để chống tranh chấp)
        $stmt_stock->bind_param("iisi", $qty, $pid, $size, $qty);
        $stmt_stock->execute();

        // C. Kiểm tra xem có dòng nào bị ảnh hưởng không (nếu không có nghĩa là hết hàng)
        if ($stmt_stock->affected_rows === 0) {
            throw new Exception("Sản phẩm '" . $item['name'] . "' (Size $size) vừa hết hàng hoặc không đủ số lượng. Vui lòng kiểm tra lại giỏ hàng.");
        }
    }

    $stmt_detail->close();
    $stmt_stock->close();

    // 10. HOÀN TẤT GIAO DỊCH
    $conn->commit();

} catch (Exception $e) {
    // Nếu có lỗi (hết hàng, lỗi SQL,...), hoàn tác toàn bộ thay đổi
    $conn->rollback();
    die("<script>alert('" . $e->getMessage() . "'); window.location.href='../cart.php';</script>");
}

$conn->close();

// 9. XÓA GIỎ HÀNG
if ($payment_method == 'cod') {
    unset($_SESSION['cart']);
}

// Lưu session cho thanh toán online
$_SESSION['order_id'] = $order_id;
$_SESSION['order_code'] = $order_code;
$_SESSION['amount'] = $final_total;

// COD
if ($payment_method == 'cod') {
    unset($_SESSION['cart']);
    header("Location: ../dat-hang-thanh-cong.php?order_code=" . $order_code);
    exit;
}

// VNPAY
if ($payment_method == 'vnpay') {
    header("Location: ../thanh-toan/vnpay_create_payment.php?order_id=" . $order_id);
    exit;
}

// MOMO
if ($payment_method == 'momo') {
    header("Location: ../thanh-toan/momo_create_payment.php?order_id=" . $order_id);
    exit;
}

// fallback (tránh lỗi)
header("Location: dat-hang-thanh-cong.php?order_code=" . $order_code);
exit;