# Tài liệu Thuyết trình Dự án Goatshop (E-commerce)

Tài liệu này tóm tắt cách hoạt động và xử lý kỹ thuật của các tính năng cốt lõi: **Giỏ hàng**, **Quản lý Kho** và **Thanh toán Online**.

---

## 1. Hệ thống Giỏ hàng (Shopping Cart)
Hệ thống giỏ hàng được xây dựng dựa trên **Session** của PHP, giúp lưu trữ tạm thời dữ liệu mà không cần người dùng phải đăng nhập ngay lập tức.

*   **Lưu trữ**: Sử dụng biến siêu toàn cục `$_SESSION['cart']`. Mỗi sản phẩm trong giỏ được định danh bằng một khóa kết hợp giữa `product_id` và `size` (ví dụ: `10_42`).
*   **Chức năng chính**:
    *   **Thêm vào giỏ**: Kiểm tra nếu sản phẩm cùng size đã tồn tại thì tăng số lượng (`quantity`), nếu chưa có thì thêm mới.
    *   **Cập nhật số lượng**: Cho phép người dùng tăng/giảm số lượng trực tiếp trong trang giỏ hàng.
    *   **Xóa sản phẩm**: Loại bỏ từng món hàng hoặc xóa toàn bộ giỏ sau khi đặt hàng thành công.
*   **Xử lý Logic**: Các file trong thư mục `actions/` (như `add_to_cart.php`, `update_cart.php`) đảm nhận việc nhận dữ liệu từ Frontend qua phương thức POST và cập nhật Session.

---

## 2. Quản lý Kho hàng (Inventory Management)
Điểm đặc biệt của hệ thống là quản lý kho hàng chi tiết theo **Kích cỡ (Size)**.

*   **Cấu trúc bảng**: Bảng `product_stock` lưu trữ số lượng tồn kho cho từng cặp `product_id` và `size`.
*   **Xử lý trừ kho tự động**:
    *   Khi người dùng nhấn "Đặt hàng", hệ thống sẽ lặp qua các sản phẩm trong giỏ hàng.
    *   Sử dụng truy vấn SQL: `UPDATE product_stock SET quantity = quantity - ? WHERE product_id = ? AND size = ?`.
    *   Việc trừ kho được thực hiện ngay khi đơn hàng được tạo để đảm bảo tính chính xác của dữ liệu.
*   **Giao diện Admin**: 
    *   Admin có thể quản lý tồn kho cho từng sản phẩm tại `admin/stock_manager.php`.
    *   Hệ thống hỗ trợ các loại size mặc định (Giày, Áo, Găng tay) và cho phép thêm size tùy chỉnh.

---

## 3. Phương thức Thanh toán Online (Online Payment)
Hệ thống tích hợp đa dạng các phương thức thanh toán để tăng trải nghiệm người dùng.

*   **Các phương thức hỗ trợ**:
    *   **COD (Thanh toán khi nhận hàng)**: Đơn hàng được xác nhận ngay với trạng thái `processing`.
    *   **VNPAY & MoMo**: Tích hợp thông qua API của các cổng thanh toán.
*   **Quy trình xử lý (Workflow)**:
    1.  Khởi tạo đơn hàng trong Database với trạng thái `pending` (Chờ thanh toán).
    2.  Hệ thống tạo URL thanh toán và chuyển hướng người dùng sang cổng thanh toán (VNPAY/MoMo).
    3.  Sau khi người dùng trả tiền, cổng thanh toán sẽ phản hồi về hệ thống để cập nhật trạng thái đơn hàng.
*   **Tính năng linh hoạt**: Cho phép người dùng chuyển đổi từ thanh toán online sang COD nếu gặp sự cố trong quá trình giao dịch (thông qua `switch-to-cod.php`).

---

## 4. Câu hỏi kỹ thuật thường gặp (Deep Dive)

Nếu ban giám khảo hoặc người xem hỏi sâu hơn, dưới đây là các câu trả lời kỹ thuật chuẩn:

### **Q: Tại sao lại dùng Session cho giỏ hàng mà không dùng Cookie hay Database?**
*   **A**: Dùng **Session** giúp dữ liệu giỏ hàng được bảo mật phía máy chủ (Server-side), tránh việc người dùng can thiệp sửa giá hoặc số lượng dễ dàng như Cookie. Nó cũng nhanh hơn việc truy vấn Database liên tục mỗi khi người dùng nhấn "Thêm vào giỏ". Khi họ đặt hàng thành công, dữ liệu mới được lưu vĩnh viễn vào Database.

### **Q: Làm sao để đảm bảo không bị "trừ kho âm" nếu 2 người cùng mua 1 lúc?**
*   **A**: Hệ thống sử dụng kết hợp **Database Transaction (Giao dịch)** và **Atomic Update** để xử lý bài toán này:
    1.  **Giao dịch (Transaction)**: Sử dụng các lệnh `begin_transaction()`, `commit()`, và `rollback()`. Khi có người đặt hàng, hệ thống sẽ khóa các dòng dữ liệu liên quan. Nếu một sản phẩm trong giỏ bị hết hàng, toàn bộ quá trình tạo đơn sẽ bị hủy (rollback) để đảm bảo tính toàn vẹn.
    2.  **Atomic Update với điều kiện**: Câu lệnh SQL trừ kho có thêm điều kiện: `UPDATE product_stock SET quantity = quantity - ? WHERE ... AND quantity >= ?`. Điều này đảm bảo Database chỉ thực hiện trừ kho nếu số lượng hiện tại đủ đáp ứng, ngăn chặn hoàn toàn việc kho bị âm kể cả khi có hàng ngàn yêu cầu cùng lúc.

### **Q: Làm sao để biết người dùng đã thanh toán online thật hay chưa?**
*   **A**: Chúng ta không chỉ dựa vào việc người dùng quay lại trang Web. Hệ thống sử dụng **CheckSum (mã băm bảo mật)**. Khi yêu cầu thanh toán được gửi đi hoặc nhận về, cả Goatshop và VNPAY/MoMo đều sử dụng một "Secret Key" để mã hóa dữ liệu. Nếu mã băm không khớp, giao dịch sẽ bị coi là giả mạo và không được ghi nhận.

### **Q: Tại sao lại tách `product_id` và `size` bằng dấu gạch dưới (VD: `10_42`) trong giỏ hàng?**
*   **A**: Đây là kỹ thuật tạo **Unique Key** cho giỏ hàng. Nó giúp phân biệt cùng một đôi giày nhưng khác kích cỡ. Nếu không tách như vậy, người dùng mua đôi giày số 40 và số 42 sẽ bị gộp chung làm một, gây sai sót trong việc quản lý kho và đóng gói.

