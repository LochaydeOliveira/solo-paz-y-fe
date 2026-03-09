</main>
<?php
    try {
        $stmt = $pdo->prepare("SELECT id, nome, slug FROM categorias ORDER BY nome ASC");
        $stmt->execute();
        $footer_categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao buscar categorias para o rodapé: " . $e->getMessage());
        $footer_categorias = [];
    }
?>


    <footer class="bg-white py-5 mt-5">
        <div class="container pd-cst-ftr">
            <div class="row">
                <div class="col-md-3 mb-4">
                    <a class="footer-logo-link d-flex align-items-center mb-3" href="<?php echo BLOG_PATH; ?>">
                        <img src="<?php echo BLOG_PATH; ?>/assets/img/logo-brasil-hilario-quadrada-svg.svg" alt="<?php echo BLOG_TITLE; ?>" class="footer-logo-img me-2">
                    </a>
                    
                    <div class="social-links d-flex mg-sociais-footer">

                        <a href="https://www.facebook.com/profile.php?id=61577306277011" target="_blank" class="social-icon me-2" aria-label="Facebook">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16">
                            <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951"/>
                            </svg>
                        </a>
                        
                        
                        <a href="https://x.com/@brasilhilario" target="_blank" class="social-icon me-2" aria-label="Twitter"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-twitter-x" viewBox="0 0 16 16">
                            <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/>
                            </svg>
                        </a>
                        
                        <a href="https://www.instagram.com/brasilhilariooficial/" target="_blank" class="social-icon me-2" aria-label="Instagram">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-instagram" viewBox="0 0 16 16">
                            <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334"/>
                            </svg>
                        </a>

                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <button class="footer-title btn btn-link d-flex justify-content-between align-items-center w-100 p-0 text-decoration-none text-uppercase" type="button" data-bs-toggle="collapse" data-bs-target="#institucionalMenu" aria-expanded="false" aria-controls="institucionalMenu">
                        INSTITUCIONAL
                        <i class="fas fa-chevron-down d-md-none"></i>
                    </button>
                    <ul class="list-unstyled footer-links collapse d-md-block" id="institucionalMenu">
                        <li><a href="<?php echo BLOG_PATH; ?>/sobre">Sobre Nós</a></li>
                        <li><a href="<?php echo BLOG_PATH; ?>/contato">Fale Conosco</a></li>
                        <li><a href="<?php echo BLOG_PATH; ?>/privacidade">Política de Privacidade</a></li>
                        <li><a href="<?php echo BLOG_PATH; ?>/termos">Termos de Uso</a></li>
                        <li><a href="#" onclick="showCookieSettings(); return false;">Gerenciar Cookies</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <button class="footer-title btn btn-link d-flex justify-content-between align-items-center w-100 p-0 text-decoration-none text-uppercase" type="button" data-bs-toggle="collapse" data-bs-target="#categoriasMenu" aria-expanded="false" aria-controls="categoriasMenu">
                        CATEGORIAS
                        <i class="fas fa-chevron-down d-md-none"></i>
                    </button>
                    <ul class="list-unstyled footer-links collapse d-md-block" id="categoriasMenu">
                        <?php foreach ($footer_categorias as $categoria): ?>
                        <li><a href="<?php echo BLOG_URL; ?>/categoria/<?php echo htmlspecialchars($categoria['slug']); ?>"><?php echo htmlspecialchars($categoria['nome']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="col-md-3 mb-4">
                    <button class="footer-title btn btn-link d-flex justify-content-between align-items-center w-100 p-0 text-decoration-none text-uppercase" type="button" data-bs-toggle="collapse" data-bs-target="#usuariosMenu" aria-expanded="false" aria-controls="usuariosMenu">
                        USUÁRIOS
                        <i class="fas fa-chevron-down d-md-none"></i>
                    </button>
                    <ul class="list-unstyled footer-links collapse d-md-block" id="usuariosMenu">
                        <li><a href="<?php echo BLOG_PATH; ?>/admin/login.php">Entrar na Conta</a></li>
                        <li><a href="<?php echo BLOG_PATH; ?>/admin/login.php">Criar conta</a></li>
                    </ul>
                </div> 

            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-9 text-md-start text-center mb-3 mb-md-0">
                    <p class="mb-0 footer-copyright-text">&copy; <?php echo date('Y'); ?> <?php echo BLOG_TITLE; ?>. Todos os direitos reservados.</p>
                </div>
                <div class="col-md-3 text-md-end text-center">

                </div>
            </div>
        </div>
    </footer>

    <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
    </script> 
    <script src="<?php echo BLOG_PATH; ?>/assets/js/main.js"></script>
    <script>
        class CookieConsent {
            constructor() {
                this.cookieName = 'brasil_hilario_cookie_consent';
                this.cookieExpiry = 365; // dias
                this.banner = document.getElementById('cookie-banner');
                this.modal = new bootstrap.Modal(document.getElementById('cookieModal'));
                
                this.init();
            }

            init() {
                // Verificar se já existe consentimento
                if (!this.getCookieConsent()) {
                    this.showBanner();
                } else {
                    this.loadGoogleAnalytics();
                }

                this.bindEvents();
            }

            bindEvents() {
                // Botões do banner
                document.getElementById('accept-cookies').addEventListener('click', () => {
                    this.acceptAllCookies();
                });

                document.getElementById('reject-cookies').addEventListener('click', () => {
                    this.rejectAllCookies();
                });

                document.getElementById('customize-cookies').addEventListener('click', () => {
                    this.showCustomizeModal();
                });

                // Botão salvar do modal
                document.getElementById('save-cookie-preferences').addEventListener('click', () => {
                    this.saveCustomPreferences();
                });
            }

            showBanner() {
                if (this.banner) {
                    this.banner.style.display = 'block';
                }
            }

            hideBanner() {
                if (this.banner) {
                    this.banner.classList.add('hidden');
                }
            }

            acceptAllCookies() {
                const consent = {
                    essential: true,
                    analytics: true,
                    marketing: true,
                    preferences: true,
                    timestamp: new Date().toISOString()
                };
                
                this.setCookieConsent(consent);
                this.hideBanner();
                this.loadGoogleAnalytics();
                this.showSuccessMessage('Cookies aceitos com sucesso!');
            }

            rejectAllCookies() {
                const consent = {
                    essential: true,
                    analytics: false,
                    marketing: false,
                    preferences: false,
                    timestamp: new Date().toISOString()
                };
                
                this.setCookieConsent(consent);
                this.hideBanner();
                this.showSuccessMessage('Cookies não essenciais recusados.');
            }

            showCustomizeModal() {
                const consent = this.getCookieConsent();
                if (consent) {
                    // Preencher checkboxes com valores salvos
                    document.getElementById('analytics-cookies').checked = consent.analytics || false;
                    document.getElementById('marketing-cookies').checked = consent.marketing || false;
                    document.getElementById('preference-cookies').checked = consent.preferences || false;
                }
                
                this.modal.show();
            }

            saveCustomPreferences() {
                const consent = {
                    essential: true,
                    analytics: document.getElementById('analytics-cookies').checked,
                    marketing: document.getElementById('marketing-cookies').checked,
                    preferences: document.getElementById('preference-cookies').checked,
                    timestamp: new Date().toISOString()
                };
                
                this.setCookieConsent(consent);
                this.hideBanner();
                this.modal.hide();
                
                if (consent.analytics) {
                    this.loadGoogleAnalytics();
                }
                
                this.showSuccessMessage('Preferências de cookies salvas!');
            }

            getCookieConsent() {
                const cookie = this.getCookie(this.cookieName);
                if (cookie) {
                    try {
                        return JSON.parse(cookie);
                    } catch (e) {
                        return null;
                    }
                }
                return null;
            }

            setCookieConsent(consent) {
                const expiryDate = new Date();
                expiryDate.setDate(expiryDate.getDate() + this.cookieExpiry);
                
                const cookieValue = JSON.stringify(consent);
                document.cookie = `${this.cookieName}=${cookieValue}; expires=${expiryDate.toUTCString()}; path=/; SameSite=Lax`;
            }

            getCookie(name) {
                const nameEQ = name + "=";
                const ca = document.cookie.split(';');
                for (let i = 0; i < ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
                }
                return null;
            }

            loadGoogleAnalytics() {
                const consent = this.getCookieConsent();
                if (consent && consent.analytics) {
                    // Usar a função global definida no header
                    if (typeof loadGoogleAnalytics === 'function') {
                        loadGoogleAnalytics();
                    }
                }
            }

            showSuccessMessage(message) {
                // Criar toast de sucesso
                const toastContainer = document.createElement('div');
                toastContainer.className = 'position-fixed top-0 end-0 p-3';
                toastContainer.style.zIndex = '10000';
                
                toastContainer.innerHTML = `
                    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="fas fa-check-circle me-2"></i>${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(toastContainer);
                
                const toast = new bootstrap.Toast(toastContainer.querySelector('.toast'));
                toast.show();
                
                // Remover toast após ser fechado
                toastContainer.addEventListener('hidden.bs.toast', () => {
                    document.body.removeChild(toastContainer);
                });
            }
        }

        // Inicializar quando o DOM estiver carregado
        document.addEventListener('DOMContentLoaded', () => {
            new CookieConsent();
        });

        // Função global para mostrar configurações de cookies
        function showCookieSettings() {
            const modal = new bootstrap.Modal(document.getElementById('cookieModal'));
            const consent = getCookieConsent();
            
            if (consent) {
                // Preencher checkboxes com valores salvos
                document.getElementById('analytics-cookies').checked = consent.analytics || false;
                document.getElementById('marketing-cookies').checked = consent.marketing || false;
                document.getElementById('preference-cookies').checked = consent.preferences || false;
            }
            
            modal.show();
        }

        // Função global para verificar consentimento
        function getCookieConsent() {
            const nameEQ = 'brasil_hilario_cookie_consent' + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) {
                    try {
                        return JSON.parse(c.substring(nameEQ.length, c.length));
                    } catch (e) {
                        return null;
                    }
                }
            }
            return null;
        }
    </script>

</body>
</html>
