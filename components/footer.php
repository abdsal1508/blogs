</div>
<?php if (!isset($hide_footer) || !$hide_footer): ?>
    <!-- Footer -->
    <footer class="text-black py-4 mt-5 <?php echo isset($footer_class) ? $footer_class : ''; ?> footer ">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>About Us</h5>
                    <p>Our blog management system provides a platform for authors to share their knowledge and for
                        readers to discover great content.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled ">
                        <li><a href="end_user.php" class="text-black text-decoration-none links">Home</a></li>
                        <li><a href="#" class="text-black text-decoration-none links">About</a></li>
                        <li><a href="#" class="text-black text-decoration-none links">Contact</a></li>
                        <?php if (!is_logged_in()): ?>
                            <li><a href="login.php" class="text-white">Login</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Connect With Us</h5>
                    <div class="d-flex gap-3 fs-4">
                        <a href="#" class="text-white"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?>     <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
<?php endif; ?>

<?php if (isset($extra_js)):
    echo $extra_js;
endif; ?>
</body>

</html>