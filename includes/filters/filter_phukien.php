<div class="filter-box">
  <h3>Chủng loại</h3>
  <ul>
    <?php
$types = ['ÁO', 'BÓNG', 'DÂY GIÀY', 'BALO', 'TÚI RÚT', 'TẤT', 'BĂNG KEO', 'LÓT GIÀY', 'XỊT KHỬ MÙI'];
foreach ($types as $t) {
  $t_url = urlencode($t);
  echo "<li><a href='index.php?category=phukien&type=$t_url'>$t</a></li>";
}
?>
  </ul>
</div>

<div class="filter-box">
  <h3>Mức giá</h3>
  <ul>
    <li><a href="index.php?category=phukien&price=under300">Dưới 300K</a></li>
    <li><a href="index.php?category=phukien&price=300-400">300K - 400K</a></li>
    <li><a href="index.php?category=phukien&price=400-500">400K - 500K</a></li>
    <li><a href="index.php?category=phukien&price=500-600">500K - 600K</a></li>
    <li><a href="index.php?category=phukien&price=600-700">600K - 700K</a></li>
    <li><a href="index.php?category=phukien&price=700-900">700K - 900K</a></li>
    <li><a href="index.php?category=phukien&price=900-1000">900K - 1000K</a></li>
    <li><a href="index.php?category=phukien&price=over1000">Trên 1000K</a></li>
  </ul>
</div>
