    </main>
    <footer class="site-footer">
        <div class="container footer-inner">
            <div class="footer-brand">
                <span class="logo-text">RedM<span class="logo-accent">Servers</span></span>
                <p>The community hub for listing and discovering RedM servers.</p>
            </div>
            <div class="footer-links">
                <div class="footer-col">
                    <h4>Navigate</h4>
                    <a href="<?= APP_URL ?>">Home</a>
                    <a href="<?= APP_URL ?>/?page=servers">Browse Servers</a>
                    <a href="<?= APP_URL ?>/?page=signup">List Your Server</a>
                </div>
                <div class="footer-col">
                    <h4>Account</h4>
                    <?php if (isLoggedIn()): ?>
                        <a href="<?= APP_URL ?>/?page=dashboard">Dashboard</a>
                        <a href="<?= APP_URL ?>/?page=tickets">Support</a>
                        <a href="<?= APP_URL ?>/logout.php">Logout</a>
                    <?php else: ?>
                        <a href="<?= APP_URL ?>/?page=login">Login</a>
                        <a href="<?= APP_URL ?>/?page=signup">Sign Up</a>
                    <?php endif; ?>
                </div>
                <div class="footer-col">
                    <h4>Resources</h4>
                    <a href="https://redm.net" target="_blank" rel="noopener">RedM.net</a>
                    <a href="https://docs.fivem.net" target="_blank" rel="noopener">RedM Docs</a>
                    <a href="https://forum.cfx.re" target="_blank" rel="noopener">Cfx.re Forum</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. Not affiliated with Rockstar Games or Cfx.re.</p>
                <div class="footer-legal">
                    <a href="<?= APP_URL ?>/?page=terms">Terms of Service</a>
                    <a href="<?= APP_URL ?>/?page=privacy">Privacy Policy</a>
                </div>
            </div>
        </div>
    </footer>
    <script src="<?= APP_URL ?>/assets/js/app.js"></script>
</body>
</html>
