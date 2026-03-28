<?php
// 1. KẾT NỐI CSDL (BẮT BUỘC - Sửa lại thông tin của bạn)
$conn = new mysqli("localhost", "root", "", "goatshop");
$conn->set_charset("utf8mb4");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
// 2. TRUY VẤN LẤY TẤT CẢ ĐƠN HÀNG
$sql = "SELECT id, order_code, customer_name, phone, total_amount, status, payment_method, created_at
        FROM orders
        ORDER BY created_at DESC";
$result = $conn->query($sql);
include("../includes/header.php"); 
?>
<link rel="stylesheet" href="../assets/css/admin.css">
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Đơn Hàng</title>
</head>
<body>

<div class="admin-container">
    <h1 class="admin-title">Quản Lý Đơn Hàng</h1>

    <form action="delete_order.php" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn tất cả các đơn hàng đã chọn không?');">
    <div class="admin-header-actions">
        <p style="color: #666; margin:0;">Tất cả lịch sử đơn đặt hàng của khách hàng được quản lý tại đây.</p>
        <button type="submit" name="bulk_delete" class="btn" style="background:#dc3545;" onclick="if(document.querySelectorAll('input[name=\'order_ids[]\']:checked').length === 0) { alert('Vui lòng chọn ít nhất một đơn hàng để xóa!'); return false; } return true;">
            <i class="fa fa-trash"></i> Xóa các mục đã chọn
        </button>
    </div>

    <div class="admin-table-wrapper">
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 40px; text-align: center;">
                    <input type="checkbox" id="selectAll" onclick="let checkboxes = document.querySelectorAll('input[name=\'order_ids[]\']'); checkboxes.forEach(cb => cb.checked = this.checked);">
                </th>
                <th>Mã Đơn Hàng</th>
                <th>Tên Khách Hàng</th>
                <th>Số Điện Thoại</th>
                <th>Tổng Tiền</th>
                <th>Trạng Thái</th>
                <th>PT Thanh Toán</th>
                <th>Ngày Đặt</th>
                <th>Hành Động</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // 3. HIỂN THỊ DỮ LIỆU
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    
                    $status_text = htmlspecialchars($row['status']);
                    $status_class = 'badge-warning';
                    if ($row['status'] == 'Chờ thanh toán') {
                        $status_class = 'badge-warning';
                    } else if ($row['status'] == 'Đang xử lý') {
                        $status_class = 'badge-warning';
                    } else if ($row['status'] == 'Đã hoàn thành') {
                        $status_class = 'badge-success';
                    } else if ($row['status'] == 'Đã hủy') {
                        $status_class = 'badge-danger';
                    }
                    
                    echo "<tr>";
                    echo "<td style='text-align: center;'><input type='checkbox' name='order_ids[]' value='" . $row['id'] . "'></td>";
                    echo "<td><strong>#" . htmlspecialchars($row['order_code']) . "</strong></td>";
                    echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                    echo "<td style='color:var(--primary); font-weight:700;'>" . number_format($row['total_amount'], 0, ',', '.') . "đ</td>";
                    echo "<td><span class='badge " . $status_class . "'>" . $status_text . "</span></td>";
                    echo "<td>" . htmlspecialchars($row['payment_method']) . "</td>";
                    echo "<td>" . date("d/m/Y H:i", strtotime($row['created_at'])) . "</td>";
                    
                    echo "<td>
                            <a href='order_detail.php?id=" . $row['id'] . "' class='action-link'>Xem</a>
                            <a href='delete_order.php?id=" . $row['id'] . "' class='action-link action-delete' onclick=\"return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn đơn hàng này không?');\">Xóa</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='9' style='text-align: center; padding: 40px;'><img src='../assets/images/empty_cart.png' style='width:100px; opacity:0.5; margin-bottom:10px;'><br>Chưa có đơn hàng nào.</td></tr>";
            }
            $conn->close();
            ?>
        </tbody>
    </table>
    </div>
    </form>
</div>

</body>
</html>