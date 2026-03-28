<div class="filter-box">
  <h3>Chủng loại</h3>
  <ul>
    <?php
$types = ['Đinh AG', 'Đinh TF', 'Đinh FG'];
foreach ($types as $t) {
  $t_url = urlencode($t);
  echo "<li><a href='index.php?category=giay&type=$t_url'>$t</a></li>";
}
?>
  </ul>
</div>

<div class="filter-box">
  <h3>Thương hiệu</h3>
  <ul>
    <?php
$brands = ['ZOCKER', 'ADIDAS', 'NIKE', 'KAMITO', 'WIKA', 'MIZUNO', 'PUMA'];
foreach ($brands as $b) {
  echo "<li><a href='index.php?category=giay&brand=$b'>$b</a></li>";
}
?>
  </ul>
</div>

<div class="filter-box">
  <h3>Mức giá</h3>
  <ul>
    <li><a href="index.php?category=giay&price=under300">Dưới 300K</a></li>
    <li><a href="index.php?category=giay&price=300-400">300K - 400K</a></li>
    <li><a href="index.php?category=giay&price=400-500">400K - 500K</a></li>
    <li><a href="index.php?category=giay&price=500-600">500K - 600K</a></li>
    <li><a href="index.php?category=giay&price=600-700">600K - 700K</a></li>
    <li><a href="index.php?category=giay&price=700-900">700K - 900K</a></li>
    <li><a href="index.php?category=giay&price=900-1000">900K - 1000K</a></li>
    <li><a href="index.php?category=giay&price=over1000">Trên 1000K</a></li>
  </ul>
</div>
