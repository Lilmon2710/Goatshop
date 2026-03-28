<?php
// 1. KẾT NỐI CSDL (SỬA LẠI THÔNG TIN CỦA BẠN)
$conn = new mysqli("localhost", "root", "", "goatshop"); 
$conn->set_charset("utf8mb4");
if ($conn->connect_error) { die("Kết nối thất bại: " . $conn->connect_error); }

// 2. LẤY ID ĐƠN HÀNG TỪ URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID đơn hàng không hợp lệ.");
}
$order_id = (int)$_GET['id'];

// 3. LẤY THÔNG TIN CHUNG CỦA ĐƠN HÀNG
$sql_order = "SELECT * FROM orders WHERE id = $order_id";
$order_result = $conn->query($sql_order);
if ($order_result->num_rows == 0) {
    die("Không tìm thấy đơn hàng.");
}
$order = $order_result->fetch_assoc();

// 4. LẤY DANH SÁCH SẢN PHẨM
$sql_details = "SELECT * FROM order_details WHERE order_id = $order_id";
$details_result = $conn->query($sql_details);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi Tiết Đơn Hàng <?= htmlspecialchars($order['order_code']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1, h2 { text-align: center; color: #333; }
        h2 { border-bottom: 2px solid #f4f4f4; padding-bottom: 10px; }
        .customer-info { margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 20px; }
        .customer-info p { margin: 5px 0; font-size: 1.1em; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px 15px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        .total-row { font-weight: bold; font-size: 1.2em; }
        .total-row td { text-align: right; }
        .payment-info { padding-top: 20px; font-size: 1.1em; }
        .back-link { display: inline-block; margin-top: 20px; color: #ffd000ff; text-decoration: none; font-weight: bold; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Chi Tiết Đơn Hàng: <?= htmlspecialchars($order['order_code']) ?></h1>

        <div class="customer-info">
            <h2>Thông tin khách hàng</h2>
            <p><strong>Tên:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
            <p><strong>SĐT:</strong> <?= htmlspecialchars($order['phone']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
            
<p><strong>Địa chỉ:</strong> 
    <?= htmlspecialchars($order['address']) ?>, 
    <?= htmlspecialchars($order['ward_code']) ?>, 
    <?= htmlspecialchars($order['district_code']) ?>, 
    <?= htmlspecialchars($order['province_code']) ?>
</p>
            
            <p><strong>Ghi chú:</strong> <?= htmlspecialchars($order['note']) ?></p>
        </div>

        <h2>Danh sách sản phẩm</h2>
        <table>
            <thead>
                <tr>
                    <th>Tên Sản Phẩm</th>
                    <th>Giá</th>
                    <th>Số Lượng</th>
                    <th>Thành Tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($details_result->num_rows > 0) {
                    while($item = $details_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($item['product_name']) . "</td>";
                        echo "<td>" . number_format($item['price'], 0, ',', '.') . "đ</td>";
                        echo "<td>" . $item['quantity'] . "</td>";
                        echo "<td>" . number_format($item['price'] * $item['quantity'], 0, ',', '.') . "đ</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align: center;'>Không tìm thấy chi tiết sản phẩm.</td></tr>";
                }
                ?>
                <tr class="total-row">
                    <td colspan="3">Tổng cộng (chưa gồm ship):</td>
                    <td><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</td>
                </tr>
            </tbody>
        </table>
        
        <div class="payment-info">
            <h2>Thông tin thanh toán</h2>
            <p><strong>Phương thức:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
            <p><strong>Trạng thái hiện tại:</strong> <span class="status-<?= strtolower(str_replace(' ', '-', $order['status'])) ?>"><?= htmlspecialchars($order['status']) ?></span></p>

            <form action="capnhat_trangthai.php" method="POST" style="margin-top: 15px;">
                <input type="hidden" name="order_id" value="<?= $order_id ?>"> 
                <label for="new_status"><strong>Đổi trạng thái thành:</strong></label>
                <select name="new_status" id="new_status" required style="padding: 5px; margin: 0 10px;">
                    <option value="Chờ thanh toán" <?= ($order['status'] == 'Chờ thanh toán') ? 'selected' : '' ?>>Chờ thanh toán</option>
                    <option value="Đang xử lý" <?= ($order['status'] == 'Đang xử lý') ? 'selected' : '' ?>>Đang xử lý</option>
                    <option value="Đang giao hàng" <?= ($order['status'] == 'Đang giao hàng') ? 'selected' : '' ?>>Đang giao hàng</option>
                    <option value="Đã hoàn thành" <?= ($order['status'] == 'Đã hoàn thành') ? 'selected' : '' ?>>Đã hoàn thành</option>
                    <option value="Đã hủy" <?= ($order['status'] == 'Đã hủy') ? 'selected' : '' ?>>Đã hủy</option>
                </select>
                <button type="submit" style="padding: 5px 10px; cursor: pointer;">Cập Nhật</button>
            </form>
            </div>
        
        <br>
        <a href="orders.php" class="back-link">Quay lại danh sách</a> 
    </div>

    </body>
    <body>
    <?php if (isset($_GET['updated']) && $_GET['updated'] == 'true'): ?>
        <div style="background: #d4edda; color: #155724; padding: 10px; text-align: center; margin-bottom: 15px;">
            <strong>Cập nhật trạng thái thành công!</strong>
        </div>
    <?php endif; ?>
    <div class="container">
<?php
$conn->close();
?>