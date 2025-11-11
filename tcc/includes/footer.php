    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-main">
            <div class="footer-brand">
                <div class="footer-logo">
                    <i class="fas fa-fire"></i>
                    <span>BONFIRE GAMES</span>
                </div>
                <p class="footer-desc">
                    Plataforma para fangames criados pela comunidade. 
                    Descubra, compartilhe, jogue.
                </p>
            </div>

            <div class="footer-links">
                <div class="link-group">
                    <h4>Navegação</h4>
                    <a href="/index.php">Início</a>
                    <a href="/pages/fangames.php">Fangames</a>
                    <a href="/pages/forum.php">Comunidade</a>
                    <a href="/pages/search.php">Pesquisar</a>
                </div>

                <div class="link-group">
                    <h4>Ajuda</h4>
                    <a href="/pages/help.php">Suporte</a>
                    <a href="/pages/faq.php">FAQ</a>
                    <a href="/pages/contact.php">Contato</a>
                </div>

                <div class="link-group">
                    <h4>Legal</h4>
                    <a href="/pages/terms.php">Termos</a>
                    <a href="/pages/privacy.php">Privacidade</a>
                </div>
            </div>

            <div class="footer-social">
                <h4>Conecte-se</h4>
                <div class="social-icons">
                    <a href="#" class="social-btn" title="Discord">
                        <i class="fab fa-discord"></i>
                    </a>
                    <a href="#" class="social-btn" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-btn" title="GitHub">
                        <i class="fab fa-github"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-copyright">
                &copy; 2024 BONFIRE GAMES. Desenvolvido com ♥ pela comunidade.
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="/assets/js/main.js"></script>
    
    <!-- Scripts específicos por página -->
    <?php if($currentPage == 'fangames.php'): ?>
        <script src="/assets/js/fangames.js"></script>
    <?php elseif($currentPage == 'profile.php'): ?>
        <script src="/assets/js/profile.js"></script>
    <?php elseif($currentPage == 'search.php'): ?>
        <script src="/assets/js/search.js"></script>
    <?php elseif($currentPage == 'forum.php'): ?>
        <script src="/assets/js/forum.js"></script>
    <?php endif; ?>
</body>
</html>