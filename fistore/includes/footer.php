<?php if (empty($hideNav)): ?>
<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-about">
      <h3>FI<span>STORE</span></h3>
      <p>Pusat buku terlengkap dan terpercaya. Kami siap mengantar ketenangan dan ilmu pengetahuan baru langsung ke depan rumahmu.</p>
    </div>
    <div class="footer-col">
      <h4>Tentang Kami</h4>
      <ul>
        <li><a href="about.php">About Us</a></li>
        <li><a href="books.php">Semua Buku</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Bantuan</h4>
      <ul>
        <li><a href="#">Privacy Policy</a></li>
        <li><a href="#">Terms of Service</a></li>
        <li><a href="#">Cookie Preferences</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Kontak</h4>
      <ul class="footer-contact">
        <li>Jl. Pelajar Indonesia No. 25, Kebayoran Baru, Jakarta Selatan, 12150</li>
        <li>WhatsApp / HP: +62 812-3456-7890</li>
        <li>Email: support@fistore-example.com</li>
        <li>Jam Operasional: Senin - Sabtu, 08.00 - 20.00 WIB</li>
      </ul>
    </div>
  </div>

  <div class="footer-social container">
    <a href="#" aria-label="Facebook">FB</a>
    <a href="#" aria-label="Twitter">TW</a>
    <a href="#" aria-label="YouTube">YT</a>
  </div>

  <div class="footer-bottom">
    &copy; <?= date('Y') ?> FISTORE. All Rights Reserved. &middot; <a href="admin/login.php" style="color:#b98ea0;">Admin</a>
  </div>
</footer>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var toggle = document.querySelector('.user-toggle');
  var dropdown = document.querySelector('.user-dropdown');
  if (toggle && dropdown) {
    toggle.addEventListener('click', function (e) {
      e.preventDefault();
      dropdown.classList.toggle('open');
    });
    document.addEventListener('click', function (e) {
      if (!toggle.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.remove('open');
      }
    });
  }
});
</script>
</body>
</html>
