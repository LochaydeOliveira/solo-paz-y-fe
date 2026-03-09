// Inicialização de componentes
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips do Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Inicializar popovers do Bootstrap
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Lazy loading de imagens
    if ('loading' in HTMLImageElement.prototype) {
        // Nativo suportado: nenhuma ação necessária
    } else {
        // Fallback para navegadores que não suportam lazy loading
        // Converte imagens com loading="lazy" para data-src + classe lazyload
        document.querySelectorAll('img[loading="lazy"]').forEach(img => {
            if (!img.dataset.src) {
                img.dataset.src = img.getAttribute('src');
            }
            img.classList.add('lazyload');
        });
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
        document.body.appendChild(script);
    }

    // Garantir lazy/async em imagens internas do conteúdo
    const content = document.querySelector('.post-content');
    if (content) {
        content.querySelectorAll('img').forEach(img => {
            if (!img.hasAttribute('loading')) img.setAttribute('loading', 'lazy');
            if (!img.hasAttribute('decoding')) img.setAttribute('decoding', 'async');
        });
    }

    // Smooth scroll para links internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    

    // Contador de visualizações (Função Reescrita)
    function incrementarVisualizacao(postId) {
        // Verifica se o cookie de admin existe antes de sequer tentar a chamada
        if (document.cookie.indexOf('admin_ignore=true') !== -1) {
            console.log('Acesso de admin detectado pelo navegador. Contagem cancelada.');
            return;
        }

        if (postId && postId > 0) {
            fetch('/api/incrementar-visualizacao.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ post_id: postId })
            })
            .catch(error => {
                console.log('Visualização não contabilizada');
            });
        }
    }

    // Verificar se está na página de post e disparar com atraso (PROTEÇÃO)
    const postId = document.querySelector('meta[name="post-id"]')?.content;
    if (postId && postId > 0) {
        // O atraso de 2 segundos evita que erros de 404 (como ícones faltando) 
        // disparem o contador em requisições fantasmas do navegador.
        setTimeout(function() {
            incrementarVisualizacao(postId);
        }, 2000); 
    }


    // Back to top button
    const backToTopButton = document.createElement('button');
    backToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTopButton.className = 'back-to-top';
    backToTopButton.style.display = 'none';
    document.body.appendChild(backToTopButton);

    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 100) {
            backToTopButton.style.display = 'flex';
        } else {
            backToTopButton.style.display = 'none';
        }
    });

    backToTopButton.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Estilização do botão back to top
    const style = document.createElement('style');
    style.textContent = `
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 33px;
            height: 33px;
            border-radius: 50%;
            background-color:rgb(56, 56, 56);
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
            z-index: 1000;
        }
        .back-to-top:hover {
            background-color:rgb(0, 0, 0);
        }
    `;
    document.head.appendChild(style);

    // Controle do menu dropdown no rodapé
    const footerTitles = document.querySelectorAll('.footer-title');
    
    footerTitles.forEach(title => {
        title.addEventListener('click', function(e) {
            if (window.innerWidth < 768) {
                e.preventDefault();
                const target = this.getAttribute('data-bs-target');
                const menu = document.querySelector(target);
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                
                // Fecha todos os outros menus
                footerTitles.forEach(otherTitle => {
                    if (otherTitle !== this) {
                        const otherTarget = otherTitle.getAttribute('data-bs-target');
                        const otherMenu = document.querySelector(otherTarget);
                        otherTitle.setAttribute('aria-expanded', 'false');
                        otherMenu.classList.remove('show');
                    }
                });
                
                // Toggle do menu atual
                this.setAttribute('aria-expanded', !isExpanded);
                menu.classList.toggle('show');
            }
        });
    });
}); 