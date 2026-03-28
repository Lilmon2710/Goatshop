<?php
session_start(); 
// Bạn có thể include file header.php của mình ở đây
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán Đơn Hàng</title>
    <link rel="stylesheet" href="assets/css/checkout.css"> 
</head>
<body>

    <div class="checkout-container">

        <div class="checkout-column shipping-info">
            <h3 class="column-title">Thông tin nhận hàng</h3>
            
            <form id="checkout-form" action="actions/xu-ly-dat-hang.php" method="POST">
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email (tùy chọn)">
                </div>
                <div class="form-group">
                    <input type="text" name="fullname" placeholder="Họ và tên" required>
                </div>
                <div class="form-group">
                    <input type="tel" name="phone" placeholder="Số điện thoại" required>
                </div>
                <div class="form-group">
                    <input type="text" name="address" placeholder="Địa chỉ (Số nhà, đường)" required>
                </div>
                
                <div class="form-group">
                    <select name="province" id="province" required>
                        <option value="">-- Tỉnh thành --</option>
                    </select>
                    <input type="hidden" name="province_name" id="province_name">
                </div>
                <div class="form-group">
                    <select name="district" id="district" required>
                        <option value="">-- Quận huyện --</option>
                    </select>
                    <input type="hidden" name="district_name" id="district_name">
                </div>
                <div class="form-group">
                     <select name="ward" id="ward" required>
                        <option value="">-- Phường xã --</option>
                    </select>
                    <input type="hidden" name="ward_name" id="ward_name">
                </div>
                
                <div class="form-group">
                    <textarea name="note" placeholder="Ghi chú (tùy chọn)"></textarea>
                </div>
            </form>
        </div>

        <div class="checkout-column payment-policy">
            <div class="shipping-section">
                <h3 class="column-title">Chính sách</h3>
                <div id="shipping-options-container">
                    <div class="shipping-method">
                        <label><span>Vui lòng chọn tỉnh/thành để xem tùy chọn</span></label>
                    </div>
                </div>
            </div>

            <div class="payment-section">
    <h3 class="column-title">Thanh toán</h3>

    <div class="payment-method">
        <input type="radio" name="payment_method" id="payment-cod" value="cod" checked form="checkout-form">
        <label for="payment-cod">Thanh toán khi giao hàng (COD)</label>
    </div>

    <div class="payment-method">
        <input type="radio" name="payment_method" id="payment-vnpay" value="vnpay" form="checkout-form">
        <label for="payment-vnpay">
            Thanh toán qua VNPAY
            <small>(ATM / QR / Internet Banking)</small>
        </label>
    </div>

    <div class="payment-method">
        <input type="radio" name="payment_method" id="payment-momo" value="momo" form="checkout-form">
        <label for="payment-momo">
            Ví điện tử MoMo
        </label>
    </div>
</div>
        </div>

        <div class="checkout-column order-summary">
            <?php
            $total = 0;
            $totalQuantity = 0;
            if (!empty($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $item) {
                    $total += $item['price'] * $item['quantity'];
                    $totalQuantity += $item['quantity'];
                }
            }
            ?>
            <h3 class="column-title" id="cart-count-title">Đơn hàng (<?= $totalQuantity ?> sản phẩm)</h3>
            <div id="product-list">
                <?php if (empty($_SESSION['cart'])): ?>
                    <p>Giỏ hàng của bạn đang trống.</p>
                <?php else: ?>
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="product-item">
                            <img src="assets/uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                            <div class="product-info">
                                <p><?= htmlspecialchars($item['name']) ?></p>
                                <span>SL: <?= $item['quantity'] ?></span>
                            </div>
                            <div class="product-price"><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>đ</div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="price-details">
                <div class="price-row">
                    <span>Tạm tính</span>
                    <span id="subtotal-amount" data-value="<?= $total ?>"><?= number_format($total, 0, ',', '.') ?>đ</span>
                </div>
                <div class="price-row">
                    <span>Phí vận chuyển</span>
                    <span id="shipping-fee">-</span>
                </div>
            </div>
            <div class="price-total">
                <span>Tổng cộng</span>
                <span class="total-amount" id="total-amount" data-value="<?= $total ?>"><?= number_format($total, 0, ',', '.') ?>đ</span>
            </div>
            <div class="action-buttons">
                <a href="cart.php">&lt; Quay về giỏ hàng</a>
                <button type="submit" form="checkout-form" class="btn-submit">ĐẶT HÀNG</button> 
            </div>
        </div>
    </div>

<script>
    // 1. Khai báo API
    const host = "https://provinces.open-api.vn/api/";

    // 2. Lấy các phần tử HTML chính
    const provinceEl = document.getElementById("province");
    const districtEl = document.getElementById("district");
    const wardEl = document.getElementById("ward");
    const shippingContainer = document.getElementById("shipping-options-container");
    
    // Lấy các phần tử giá
    const subtotalAmountEl = document.getElementById("subtotal-amount");
    const shippingFeeEl = document.getElementById("shipping-fee");
    const totalAmountEl = document.getElementById("total-amount");
    
    // (MỚI) Lấy 3 trường ẩn
    const provinceNameEl = document.getElementById("province_name");
    const districtNameEl = document.getElementById("district_name");
    const wardNameEl = document.getElementById("ward_name");

    // Hàm định dạng tiền tệ
    function formatCurrency(number) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(number);
    }

    // Hàm cập nhật Phí vận chuyển và Tổng cộng
    function updateTotal(event) {
        const subtotal = parseFloat(subtotalAmountEl.dataset.value) || 0;
        let shippingCost = 0;
        const selectedShipping = event.target;
        
        if (selectedShipping && selectedShipping.dataset.cost) {
            shippingCost = parseFloat(selectedShipping.dataset.cost) || 0;
        }
        const total = subtotal + shippingCost;
        shippingFeeEl.textContent = (shippingCost === 0) ? "Miễn phí" : formatCurrency(shippingCost);
        totalAmountEl.textContent = formatCurrency(total);
        totalAmountEl.dataset.value = total;
    }

    // Hàm render <select>
    function renderOptions(selectElement, data) {
        selectElement.innerHTML = `<option value="">-- ${selectElement.id === 'province' ? 'Tỉnh thành' : (selectElement.id === 'district' ? 'Quận huyện' : 'Phường xã')} --</option>`;
        for (const item of data) {
            selectElement.innerHTML += `<option value="${item.code}">${item.name}</option>`;
        }
    }

    // Hàm tải Tỉnh/Thành
    async function loadProvinces() {
        try {
            const response = await fetch(host + "?depth=1");
            const provinces = await response.json();
            renderOptions(provinceEl, provinces);
        } catch (error) { console.error("Lỗi tải tỉnh thành:", error); }
    }

    // 5. Bắt sự kiện khi chọn Tỉnh/Thành 
    provinceEl.addEventListener("change", async () => {
        const provinceCode = provinceEl.value;
        const selectedProvinceName = provinceEl.options[provinceEl.selectedIndex].text;
        
        // Gán TÊN vào trường ẩn
        provinceNameEl.value = (provinceCode === "") ? "" : selectedProvinceName; 
        districtNameEl.value = ""; 
        wardNameEl.value = ""; 

        //  Reset phí vận chuyển
        shippingFeeEl.textContent = "-";
        const subtotal = parseFloat(subtotalAmountEl.dataset.value) || 0;
        totalAmountEl.textContent = formatCurrency(subtotal);
        totalAmountEl.dataset.value = subtotal;

        // Cập nhật tùy chọn vận chuyển
        let shippingHTML = "";
        if (selectedProvinceName === "Thành phố Hà Nội") {
             shippingHTML = `
              <div class="shipping-method">
                    <input type="radio" name="shipping_method" id="ship-hoatoc" value="hoatoc" form="checkout-form" data-cost="0">
                    <label for="ship-hoatoc">
                        <span>[TRONG NGÀY] Shop sẽ check phí ship hỏa tốc và alo báo KH</span>
                        <strong>Miễn phí</strong>
                    </label>
                </div>
                <div class="shipping-method">
                    <input type="radio" name="shipping_method" id="ship-nhanh" value="nhanh" form="checkout-form" data-cost="0">
                    <label for="ship-nhanh">
                        <span>[1-2 NGÀY] Tặng combo quà - Đổi trả hàng tới khi hài lòng</span>
                        <strong>Miễn phí</strong>
                    </label>
                </div>
                <div class="shipping-method">
                    <input type="radio" name="shipping_method" id="ship-laytructiep" value="laytructiep" form="checkout-form" data-cost="0">
                    <label for="ship-laytructiep">
                        <span>Tự lấy hàng tại beck shop 639 Kim Ngưu trong 24h</span>
                        <strong>Miễn phí</strong>
                    </label>
                </div>
            `;
        } else if (provinceCode) {
             shippingHTML = `
                <div class="shipping-method">
                    <input type="radio" name="shipping_method" id="ship-nhanh" value="nhanh" form="checkout-form" data-cost="0">
                    <label for="ship-nhanh">
                        <span>	Tặng BH trọn đời + tất +túi + băng cuốn + đổi size tận nhà + trả hàng không cần lý do</span>
                        <strong>Miễn phí</strong>
                    </label>
                </div>
                <div class="shipping-method">
                    <input type="radio" name="shipping_method" id="ship-nhanh" value="nhanh" form="checkout-form" data-cost="0">
                    <label for="ship-nhanh">
                        <span>Tặng khâu thêm đế miễn phí + 3 tháng bảo hành (thanh toán ngay)</span>
                        <strong>Miễn phí</strong>
                    </label>
                </div>
            `; 
        } else {
             shippingHTML = `<div class="shipping-method"><label><span>Vui lòng chọn tỉnh/thành</span></label></div>`;
        }
        shippingContainer.innerHTML = shippingHTML;
        
        // Reset cả Huyện và Xã
        renderOptions(districtEl, []);
        renderOptions(wardEl, []);

        // Tải quận/huyện
        if (provinceCode) {
            try {
                const response = await fetch(host + `p/${provinceCode}?depth=2`);
                const data = await response.json();
                renderOptions(districtEl, data.districts);
            } catch (error) { console.error("Lỗi tải quận huyện:", error); }
        }
    });

    // 6. Bắt sự kiện khi chọn Quận/Huyện (CODE ĐÚNG)
    districtEl.addEventListener("change", async () => {
        const districtCode = districtEl.value;
        const selectedDistrictName = districtEl.options[districtEl.selectedIndex].text;

        districtNameEl.value = (districtCode === "") ? "" : selectedDistrictName;
        wardNameEl.value = ""; 
        
        // Reset Xã
        renderOptions(wardEl, []);
        
        if (districtCode) {
            try {
                const response = await fetch(host + `d/${districtCode}?depth=2`);
                const data = await response.json();
                renderOptions(wardEl, data.wards);
            } catch (error) { console.error("Lỗi tải phường xã:", error); }
        }
    });
    
    // 7. Bắt sự kiện khi chọn Phường/Xã
    wardEl.addEventListener("change", () => {
        const wardCode = wardEl.value;
        const selectedWardName = wardEl.options[wardEl.selectedIndex].text;
        
        // Gán TÊN vào trường ẩn
        wardNameEl.value = (wardCode === "") ? "" : selectedWardName;
    });

    // 8. Tải tỉnh thành khi trang vừa mở
    document.addEventListener("DOMContentLoaded", () => {
        loadProvinces();
    });

    // 9. Bắt sự kiện khi bấm chọn VẬN CHUYỂN
    const paymentPolicyColumn = document.querySelector(".payment-policy");
    paymentPolicyColumn.addEventListener("change", (event) => {
        if (event.target.name === "shipping_method") {
            updateTotal(event);
        }
    });
</script>

</body>
</html>