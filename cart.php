<?php
session_start();
include("includes/header.php");
?>
<link rel="stylesheet" href="assets/css/header.css">
<link rel="stylesheet" href="assets/css/products.css">
<link rel="stylesheet" href="assets/css/category.css">
<link rel="stylesheet" href="assets/css/cart.css">

<div class="cart-container">
  <h2>Giỏ hàng</h2>

  <?php if (empty($_SESSION['cart'])): ?>
    <p class="empty-cart">Giỏ hàng của bạn đang trống.</p>
  <?php else: ?>
    <table class="cart-table">
      <thead>
        <tr>
          <th>Tên sản phẩm</th>
          <th>Giá</th>
          <th>Số lượng</th>
          <th>Tổng tiền</th>
          <th>Xóa</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $total = 0;
        foreach ($_SESSION['cart'] as $id => $item): 
          $subtotal = $item['price'] * $item['quantity'];
          $total += $subtotal;
        ?>
<tr data-id="<?= $id ?>">
  <td class="product-info">
    <img src="assets/uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
    <span><?= htmlspecialchars($item['name']) ?></span>
  </td>
  <td class="price"><?= number_format($item['price'], 0, ',', '.') ?>₫</td>
  <td>
    <input type="number" class="qty" value="<?= $item['quantity'] ?>" min="1">
  </td>
  <td class="subtotal"><?= number_format($subtotal, 0, ',', '.') ?>₫</td>
  <td><a href="actions/remove_from_cart.php?id=<?= $id ?>" class="remove-btn">🗑</a></td>
</tr>

        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="cart-actions">
      <a href="index.php" class="btn-continue">↩ Tiếp tục mua hàng</a>
      <div class="cart-summary">
        <p><strong>Tổng tiền:</strong> <span class="total"><?= number_format($total, 0, ',', '.') ?>₫</span></p>
        <a href="checkout.php" class="btn-checkout">✔ Chốt đơn</a>
      </div>
    </div>
  <?php endif; ?>
</div>
<script>
document.querySelectorAll(".qty").forEach(input => {
    input.addEventListener("input", function() {
        const tr = input.closest("tr");
        const id = tr.dataset.id;
        let qty = input.value.trim();

        if(qty === "") {
            tr.querySelector(".subtotal").textContent = "0₫";
            return;
        }

        qty = parseInt(qty);
        if(isNaN(qty) || qty < 1) qty = 1;
        input.value = qty;

        fetch("actions/update_cart.php", {
            method: "POST",
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}&quantity=${qty}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                input.value = data.current_qty;
            }
            tr.querySelector(".subtotal").textContent = data.subtotal.toLocaleString('vi-VN') + "₫";
            document.querySelector(".total").textContent = data.total.toLocaleString('vi-VN') + "₫";
        });
    });
});

</script>

<?php include("includes/footer.php"); ?>

