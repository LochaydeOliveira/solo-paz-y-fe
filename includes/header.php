<?php
    require_once 'config/config.php';
    require_once 'includes/db.php';

    $page_title = isset($og_title) ? $og_title : BLOG_TITLE;
    $page_description = isset($meta_description) ? $meta_description : BLOG_DESCRIPTION;
    $page_keywords = isset($meta_keywords) ? $meta_keywords : META_KEYWORDS;
    $page_url = isset($og_url) ? $og_url : BLOG_URL;
    $page_image = isset($og_image) ? $og_image : BLOG_URL . '/assets/img/logo-brasil-hilario-para-og.png';
    $page_og_type = isset($og_type) ? $og_type : 'website';

    $categories = [];
    try {
        $stmt = $pdo->prepare("SELECT id, nome, slug FROM categorias ORDER BY nome ASC");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao carregar categorias para a barra de navegação: " . $e->getMessage());
    }
?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <script>

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
        
        const existingConsent = getCookieConsent();
        if (existingConsent && existingConsent.analytics) {
            loadGoogleAnalytics();
        }
    </script>

    <title><?php echo $page_title; ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $page_description; ?>">
    <meta name="keywords" content="<?php echo $page_keywords; ?>">
    <meta name="google-adsense-account" content="ca-pub-8313157699231074">
    <meta name="author" content="Solo Paz y Fe">

    <meta property="og:type" content="<?php echo $page_og_type; ?>">
    <meta property="og:url" content="<?php echo $page_url; ?>">
    <meta property="og:title" content="<?php echo $page_title; ?>">
    <meta property="og:description" content="<?php echo $page_description; ?>">
    <meta property="og:image" content="<?php echo $page_image; ?>">
    <meta property="og:site_name" content="<?php echo BLOG_TITLE; ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="pt_BR">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo $page_url; ?>">
    <meta property="twitter:title" content="<?php echo $page_title; ?>">
    <meta property="twitter:description" content="<?php echo $page_description; ?>">
    <meta property="twitter:image" content="<?php echo $page_image; ?>">

    <?php if (isset($post) && isset($post['id'])): ?>
    <meta name="post-id" content="<?php echo $post['id']; ?>">
    <?php else: ?>
    <meta name="post-id" content="0">
    <?php endif; ?>


    <link rel="icon" type="image/png" href="<?php echo BLOG_URL; ?>/assets/img/icone-favi-brasil-hilario.png">
    <link rel="apple-touch-icon" href="<?php echo BLOG_URL; ?>/assets/img/icone-favi-brasil-hilario.png">
    <link rel="shortcut icon" href="<?php echo BLOG_URL; ?>/assets/img/icone-favi-brasil-hilario.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="<?php echo BLOG_URL; ?>/assets/css/style.css?v=05" rel="stylesheet">
    <link href="<?php echo BLOG_URL; ?>/assets/css/dynamic.css?v=1.01" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://unpkg.com">
    <link rel="preconnect" href="https://www.googletagmanager.com">
    <link rel="preconnect" href="https://pagead2.googlesyndication.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Merriweather:ital,opsz,wght@0,18..144,300..900;1,18..144,300..900&display=swap" rel="stylesheet">
    

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "<?php echo isset($is_post) && $is_post ? 'Article' : 'Blog'; ?>",
            "name": "<?php echo $page_title; ?>",
            "description": "<?php echo $page_description; ?>",
            "url": "<?php echo $page_url; ?>"
            <?php if (isset($is_post) && $is_post): ?>
            ,
            "mainEntityOfPage": {
                "@type": "WebPage",
                "@id": "<?php echo $page_url; ?>"
            },
            "headline": "<?php echo $page_title; ?>",
            "image": [
                "<?php echo $page_image; ?>"
            ],
            "datePublished": "<?php echo date('c', strtotime($post['data_publicacao'])); ?>",
            "dateModified": "<?php echo date('c', strtotime($post['data_atualizacao'] ?? $post['data_publicacao'])); ?>",
            "author": {
                "@type": "Person",
                "name": "<?php echo htmlspecialchars($post['autor_nome']); ?>"
            },
            "publisher": {
                "@type": "Organization",
                "name": "<?php echo BLOG_TITLE; ?>",
                "logo": {
                    "@type": "ImageObject",
                    "url": "<?php echo BLOG_URL; ?>/assets/img/logo-brasil-hilario-quadrada-svg.svg"
                }
            },
            "description": "<?php echo $page_description; ?>"
            <?php endif; ?>
        }
    </script>

    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/pt_BR/sdk.js#xfbml=1&version=v18.0"></script>

    <script src="<?php echo BLOG_URL; ?>/assets/js/anuncios.js" defer></script>
      
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const testIcon = document.createElement('i');
                testIcon.className = 'fa-solid fa-calendar';
                testIcon.style.position = 'absolute';
                testIcon.style.left = '-9999px';
                document.body.appendChild(testIcon);
                
                if (getComputedStyle(testIcon, ':before').content === 'none' || 
                    getComputedStyle(testIcon, ':before').content === 'normal') {
                    
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = 'https://use.fontawesome.com/releases/v6.5.1/css/all.css';
                    link.integrity = 'sha384-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==';
                    link.crossOrigin = 'anonymous';
                    link.referrerPolicy = 'no-referrer';
                    document.head.appendChild(link);
                }
                
                document.body.removeChild(testIcon);
            }, 1000);
        });
    </script>

    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8313157699231074" crossorigin="anonymous"></script>

</head>

<body>

    <div id="cookie-banner" class="cookie-banner" style="display: none;">
        <div class="cookie-content">
            <div class="cookie-text">
                <h5><i class="fa-solid fa-cookie-bite me-2"></i>Política de Cookies</h5>
                <p>Utilizamos cookies para melhorar sua experiência em nosso site, analisar o tráfego e personalizar conteúdo. Ao continuar navegando, você concorda com nossa <a href="<?php echo BLOG_URL; ?>/politica-de-privacidade" target="_blank">Política de Privacidade</a> e uso de cookies.</p>
            </div>
            <div class="cookie-buttons">
                <button id="accept-cookies" class="btn btn-success btn-sm">
                    <i class="fa-solid fa-check me-1"></i>Aceitar Todos
                </button>
                <button id="reject-cookies" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-times me-1"></i>Recusar
                </button>
                <button id="customize-cookies" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-gear me-1"></i>Personalizar
                </button>
            </div>
        </div>
    </div>


    <div class="modal fade" id="cookieModal" tabindex="-1" aria-labelledby="cookieModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cookieModalLabel">
                        <i class="fa-solid fa-gear me-2"></i>Configurações de Cookies
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-4">Escolha quais tipos de cookies você permite que utilizemos:</p>
                    
                    <div class="cookie-option mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="essential-cookies" checked disabled>
                            <label class="form-check-label" for="essential-cookies">
                                <strong>Cookies Essenciais</strong>
                            </label>
                        </div>
                        <small class="text-muted">Necessários para o funcionamento básico do site. Não podem ser desativados.</small>
                    </div>

                    <div class="cookie-option mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="analytics-cookies">
                            <label class="form-check-label" for="analytics-cookies">
                                <strong>Cookies de Análise</strong>
                            </label>
                        </div>
                        <small class="text-muted">Nos ajudam a entender como os visitantes interagem com o site, coletando e relatando informações anonimamente.</small>
                    </div>

                    <div class="cookie-option mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="marketing-cookies">
                            <label class="form-check-label" for="marketing-cookies">
                                <strong>Cookies de Marketing</strong>
                            </label>
                        </div>
                        <small class="text-muted">Usados para rastrear visitantes em sites para exibir anúncios relevantes e envolventes.</small>
                    </div>

                    <div class="cookie-option mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="preference-cookies">
                            <label class="form-check-label" for="preference-cookies">
                                <strong>Cookies de Preferências</strong>
                            </label>
                        </div>
                        <small class="text-muted">Permitem que o site lembre informações que mudam a forma como o site se comporta ou se parece.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="save-cookie-preferences">
                        <i class="fas fa-save me-1"></i>Salvar Preferências
                    </button>
                </div>
            </div>
        </div>
    </div>

    <header class="bg-light shadow-sm">
        <nav class="navbar navbar-expand-lg navbar-light ht-custom">
            <div class="container bg-nav-custom">
                <a class="navbar-brand d-flex align-items-center" href="<?php echo BLOG_URL; ?>">
                    <img src="<?php echo BLOG_URL; ?>/assets/img/logo-brasil-hilario-quadrada-svg.svg" alt="<?php echo BLOG_TITLE; ?>" class="logo-img me-2">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Alternar navegação">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BLOG_URL; ?>">Início</a>
                        </li>
                        <?php foreach (PAGES as $page_item): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $page_item['url']; ?>"><?php echo $page_item['title']; ?></a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <form class="d-flex mg-bt-search" action="<?php echo BLOG_URL; ?>/busca.php" method="GET">
                        <div class="input-group">
                            <input type="search" name="q" class="form-control" placeholder="Buscar no blog..." aria-label="Buscar" required>
                            <button class="btn btn-outline-success" type="submit" aria-label="Buscar">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </nav>
    </header>

    <style>
        .category-navbar { position: relative; }
        .category-navbar .container { position: relative; display: flex; align-items: center; gap: 8px; }
        .category-scroll-container { flex: 1 1 auto; overflow-x: auto; overflow-y: hidden; -ms-overflow-style: none; scrollbar-width: none; padding: 6px 32px; }
        .category-scroll-container::-webkit-scrollbar { display: none; }
        .category-navbar .nav { flex-wrap: nowrap; white-space: nowrap; }
        .category-navbar .nav .nav-item { flex: 0 0 auto; }
        .category-navbar .arrow { position: absolute; top: 50%; transform: translateY(-50%); background: #0b8103; color: #fff; border: 0; border-radius: 0; padding: 6px; width: auto; height: auto; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: none; z-index: 2; }
        .category-navbar .arrow.left { left: 4px; }
        .category-navbar .arrow.right { right: 4px; }
        @media (min-width: 992px) {
            .category-scroll-container { padding: 6px 0; }
            .category-navbar .arrow.left { left: -35px; }
            .category-navbar .arrow.right { right: -35px; }
        }
    </style>

    <nav class="category-navbar">
        <div class="container">
            <button class="arrow left" aria-label="Categorias anteriores">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" width="25" height="25" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
            </button>
            <div class="category-scroll-container">
                <ul class="nav">
                    <?php foreach ($categories as $category): ?>
                        <li class="nav-item">
                            <a class="category-nav-link" href="<?php echo BLOG_URL; ?>/categoria/<?php echo htmlspecialchars($category['slug']); ?>">
                                <?php echo htmlspecialchars($category['nome']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <button class="arrow right" aria-label="Próximas categorias">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"  width="25" height="25" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </button>
        </div>
    </nav>

    <main class="container mg-custom">

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const scrollContainer = document.querySelector('.category-scroll-container');
        const leftArrow = document.querySelector('.arrow.left');
        const rightArrow = document.querySelector('.arrow.right');

        function updateArrows() {

            const canScroll = scrollContainer.scrollWidth > scrollContainer.clientWidth + 2;
            if (!canScroll) {
                leftArrow.style.display = 'none';
                rightArrow.style.display = 'none';
                return;
            }

            leftArrow.style.display = (scrollContainer.scrollLeft > 2) ? '' : 'none';

            rightArrow.style.display = (scrollContainer.scrollLeft < scrollContainer.scrollWidth - scrollContainer.clientWidth - 2) ? '' : 'none';
        }


        scrollContainer.addEventListener('scroll', updateArrows);
        window.addEventListener('resize', updateArrows);

        if (document.fonts) {
            document.fonts.ready.then(updateArrows);
        }
        window.addEventListener('load', updateArrows);

        leftArrow.addEventListener('click', function() {
            scrollContainer.scrollBy({ left: -120, behavior: 'smooth' });
        });
        rightArrow.addEventListener('click', function() {
            scrollContainer.scrollBy({ left: 120, behavior: 'smooth' });
        });

        // Inicializa as setas
        updateArrows();
    });
</script>
