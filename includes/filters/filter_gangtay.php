<div class="filter-box">
  <h3>Thương hiệu</h3>
  <ul>
    <?php
$brands = ['ZOCKER', 'GKVN', 'WIKA'];
foreach ($brands as $b) {
  echo "<li><a href='index.php?category=gangtay&brand=$b'>$b</a></li>";
}
?>
  </ul>
</div>

<div class="filter-box">
  <h3>Mức giá</h3>
  <ul>
    <li><a href="index.php?category=gangtay&price=under300">Dưới 300K</a></li>
    <li><a href="index.php?category=gangtay&price=300-400">300K - 400K</a></li>
    <li><a href="index.php?category=gangtay&price=400-500">400K - 500K</a></li>
    <li><a href="index.php?category=gangtay&price=500-600">500K - 600K</a></li>
    <li><a href="index.php?category=gangtay&price=600-700">600K - 700K</a></li>
    <li><a href="index.php?category=gangtay&price=700-900">700K - 900K</a></li>
    <li><a href="index.php?category=gangtay&price=900-1000">900K - 1000K</a></li>
    <li><a href="index.php?category=gangtay&price=over1000">Trên 1000K</a></li>
  </ul>
</div>