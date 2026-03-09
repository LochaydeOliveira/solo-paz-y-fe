<?php
    session_start();
    require_once 'includes/db.php';
    require_once 'config/config.php';
    require_once 'config/admin_ips.php';
    require_once 'config/search.php';
    require_once 'vendor/autoload.php';

    // 1. Identificação de Admin (IP, Sessão e Cookie)
    $meu_ip_atual = '179.48.3.74'; 
    $is_admin = false;

    if (
        ($_SERVER['REMOTE_ADDR'] === $meu_ip_atual) || 
        isset($_SESSION['user_id']) || 
        isset($_COOKIE['admin_ignore'])
    ) {
        $is_admin = true;
        if (!defined('VISITANTE_INTERNO')) define('VISITANTE_INTERNO', true);
    }


    try {
        $slug = $_GET['slug'] ?? '';
        
        if (empty($slug)) {
            header('Location: ' . BLOG_URL);
            exit;
        }

        $font_configs = [];
        $stmt_fonts = $pdo->prepare("
            SELECT categoria, elemento, propriedade, valor 
            FROM configuracoes_visuais 
            WHERE categoria = 'fontes' AND ativo = 1
        ");
        $stmt_fonts->execute();
        while ($row = $stmt_fonts->fetch()) {
            $font_configs[$row['elemento']][$row['propriedade']] = $row['valor'];
        }
        
        $stmt = $pdo->prepare("
            SELECT p.*, c.nome as categoria_nome, c.slug as categoria_slug, u.nome as autor_nome
            FROM posts p 
            JOIN categorias c ON p.categoria_id = c.id 
            LEFT JOIN usuarios u ON p.autor_id = u.id 
            WHERE p.slug = ? AND p.publicado = 1
        ");
        $stmt->execute([$slug]);
        $post = $stmt->fetch();

        if (!$post) {
            header('HTTP/1.0 404 Not Found');
            include '404.php';
            exit;
        }

        $stmt_tags = $pdo->prepare("
            SELECT t.id, t.nome, t.slug 
            FROM post_tags pt 
            JOIN tags t ON pt.tag_id = t.id 
            WHERE pt.post_id = ?
            ORDER BY t.nome ASC
        ");
        $stmt_tags->execute([$post['id']]);
        $post['tags'] = $stmt_tags->fetchAll();


        $stmt_related = $pdo->prepare("
            SELECT p.id, p.titulo, p.slug, p.imagem_destacada, c.nome as categoria_nome
            FROM posts p 
            JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.categoria_id = ? AND p.id != ? AND p.publicado = 1
            ORDER BY p.data_publicacao DESC 
            LIMIT 4
        ");
        $stmt_related->execute([$post['categoria_id'], $post['id']]);
        $related_posts = $stmt_related->fetchAll();

        $stmt_latest = $pdo->prepare("
            SELECT p.id, p.titulo, p.slug, p.imagem_destacada, c.nome as categoria_nome
            FROM posts p 
            JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.publicado = 1 AND p.id != ?
            ORDER BY p.data_publicacao DESC 
            LIMIT 4
        ");
        $stmt_latest->execute([$post['id']]);
        $latest_posts = $stmt_latest->fetchAll();

        $og_title = htmlspecialchars($post['titulo']);
        $og_description = !empty($post['resumo']) ? htmlspecialchars($post['resumo']) : htmlspecialchars(generate_excerpt($post['conteudo'], 200));
        $og_url = BLOG_URL . '/post/' . htmlspecialchars($post['slug']);
        $og_image = !empty($post['imagem_destacada']) ? BLOG_URL . '/uploads/images/' . htmlspecialchars($post['imagem_destacada']) : BLOG_URL . '/assets/img/logo-brasil-hilario-quadrada-svg.svg';
        $meta_description = $og_description;
        $meta_keywords = implode(', ', array_column($post['tags'], 'nome')) . ', ' . META_KEYWORDS;

    } catch (PDOException $e) {
            die("Erro: " . $e->getMessage());
        }



    function buildPostsSectionHtml($title, $posts, $font_configs = []) {
        if (empty($posts)) {
            return '';
        }

        $section_type = '';
        if (stripos($title, 'Leia Também') !== false) {
            $section_type = 'leia_tambem';
        } elseif (stripos($title, 'Últimas do Portal') !== false) {
            $section_type = 'ultimas_portal';
        }

        $title_style = '';
        $text_style = '';
        $mobile_css = '';
        
        if ($section_type && isset($font_configs[$section_type])) {
            $config = $font_configs[$section_type];
            

            if (isset($config['fonte'])) {
                $title_style .= "font-family: {$config['fonte']} !important; ";
            }
            if (isset($config['peso_titulo'])) {
                $title_style .= "font-weight: {$config['peso_titulo']} !important; ";
            }
            if (isset($config['tamanho_titulo_desktop'])) {
                $title_style .= "font-size: {$config['tamanho_titulo_desktop']} !important; ";
            }
            
            // Estilo para o texto dos posts (desktop)
            if (isset($config['fonte'])) {
                $text_style .= "font-family: {$config['fonte']} !important; ";
            }
            if (isset($config['peso_texto'])) {
                $text_style .= "font-weight: {$config['peso_texto']} !important; ";
            }
            if (isset($config['tamanho_texto_desktop'])) {
                $text_style .= "font-size: {$config['tamanho_texto_desktop']} !important; ";
            }
            

            if (isset($config['tamanho_titulo_mobile']) || isset($config['tamanho_texto_mobile']) || isset($config['fonte']) || isset($config['peso_titulo']) || isset($config['peso_texto'])) {
                $mobile_css = '<style>';
                $mobile_css .= '@media (max-width: 768px) {';
                

                if (isset($config['fonte'])) {
                    $mobile_css .= "body .post-content .related-posts-block .related-posts-title { font-family: {$config['fonte']} !important; }";
                }
                if (isset($config['peso_titulo'])) {
                    $mobile_css .= "body .post-content .related-posts-block .related-posts-title { font-weight: {$config['peso_titulo']} !important; }";
                }
                if (isset($config['tamanho_titulo_mobile'])) {
                    $mobile_css .= "body .post-content .related-posts-block .related-posts-title { font-size: {$config['tamanho_titulo_mobile']} !important; }";
                }
                
                if (isset($config['fonte'])) {
                    $mobile_css .= "body .post-content .related-posts-block .related-post-title { font-family: {$config['fonte']} !important; }";
                }
                if (isset($config['peso_texto'])) {
                    $mobile_css .= "body .post-content .related-posts-block .related-post-title { font-weight: {$config['peso_texto']} !important; }";
                }
                if (isset($config['tamanho_texto_mobile'])) {
                    $mobile_css .= "body .post-content .related-posts-block .related-post-title { font-size: {$config['tamanho_texto_mobile']} !important; }";
                }
                
                $mobile_css .= '}';
                $mobile_css .= '</style>';
            }
        }


        static $section_counter = 0;
        $section_counter++;
        $section_id = 'related-section-' . $section_counter;

        $section_html = $mobile_css;
        $section_html .= '<div class="continue-reading my-3 text-center">'
                    . '<a href="#" class="continue-reading-link fw-semibold text-success" data-section="' . $section_id . '">Continuar Lendo...</a>'
                    . '</div>';
        $section_html .= '<section id="' . $section_id . '" class="related-posts-block my-5">';
        $section_html .= '<h4 class="related-posts-title" style="' . $title_style . '">' . htmlspecialchars($title) . '</h4>';
        $section_html .= '<div class="row align-items-start">';

        foreach ($posts as $p) {
            $post_url = BLOG_URL . '/post/' . htmlspecialchars($p['slug']);
            $image_path = !empty($p['imagem_destacada']) ? BLOG_URL . '/uploads/images/' . htmlspecialchars($p['imagem_destacada']) : BLOG_URL . '/assets/img/logo-brasil-hilario-para-og.png';

            $section_html .= '<div class="col-lg-3 col-md-6 mb-4">';
            $section_html .= '<a href="' . $post_url . '" class="related-post-link">';
            $section_html .= '<div class="card h-100 related-post-card">';
            $section_html .= '<img src="' . $image_path . '" class="related-post-img" alt="' . htmlspecialchars($p['titulo']) . '" loading="lazy" decoding="async">';
            $section_html .= '<div class="pad-01 d-flex flex-column ">';
            $section_html .= '<h6 class="card-title related-post-title mt-auto" style="' . $text_style . '">' . htmlspecialchars($p['titulo']) . '</h6>';
            $section_html .= '<div><span class="badge mb-2">' . htmlspecialchars($p['categoria_nome']) . '</span></div>';
            $section_html .= '</div>';
            $section_html .= '</div>';
            $section_html .= '</a>';
            $section_html .= '</div>';
        }

        $section_html .= '</div>';
        $section_html .= '</section>';

        return $section_html;
    }

    function applyContentTitleStyles($content, $font_configs) {
        $has_configs = false;
        
        if (isset($font_configs['titulo_conteudo']['fonte']) || 
            isset($font_configs['titulo_conteudo']['peso'])) {
            $has_configs = true;
        }
        
        if (isset($font_configs['titulo_conteudo_h1']['desktop']) || 
            isset($font_configs['titulo_conteudo_h1']['mobile']) ||
            isset($font_configs['titulo_conteudo_h2']['desktop']) || 
            isset($font_configs['titulo_conteudo_h2']['mobile']) ||
            isset($font_configs['titulo_conteudo_h3']['desktop']) || 
            isset($font_configs['titulo_conteudo_h3']['mobile'])) {
            $has_configs = true;
        }
        
        if (!$has_configs) {
            return $content;
        }
        
        $css = '<style>';
        $css .= '/* CSS gerado dinamicamente para títulos de conteúdo */';
        
        $css .= 'body .post-content h1, body .post-content h2, body .post-content h3, body .post-content h4, body .post-content h5, body .post-content h6 {';
        
        if (isset($font_configs['titulo_conteudo']['fonte'])) {
            $css .= "font-family: {$font_configs['titulo_conteudo']['fonte']} !important; ";
        }
        if (isset($font_configs['titulo_conteudo']['peso'])) {
            $css .= "font-weight: {$font_configs['titulo_conteudo']['peso']} !important; ";
        }
        
        $css .= '}';
        

        if (isset($font_configs['titulo_conteudo_h1']['desktop'])) {
            $css .= "body .post-content h1 { font-size: {$font_configs['titulo_conteudo_h1']['desktop']} !important; }";
        }
        if (isset($font_configs['titulo_conteudo_h2']['desktop'])) {
            $css .= "body .post-content h2 { font-size: {$font_configs['titulo_conteudo_h2']['desktop']} !important; }";
        }
        if (isset($font_configs['titulo_conteudo_h3']['desktop'])) {
            $css .= "body .post-content h3 { font-size: {$font_configs['titulo_conteudo_h3']['desktop']} !important; }";
        }
        

        $css .= '@media (max-width: 768px) {';
        $css .= '/* Estilos mobile para títulos de conteúdo */';
        
        if (isset($font_configs['titulo_conteudo']['fonte'])) {
            $css .= "body .post-content h1, body .post-content h2, body .post-content h3, body .post-content h4, body .post-content h5, body .post-content h6 { font-family: {$font_configs['titulo_conteudo']['fonte']} !important; }";
        }
        if (isset($font_configs['titulo_conteudo']['peso'])) {
            $css .= "body .post-content h1, body .post-content h2, body .post-content h3, body .post-content h4, body .post-content h5, body .post-content h6 { font-weight: {$font_configs['titulo_conteudo']['peso']} !important; }";
        }
        
        if (isset($font_configs['titulo_conteudo_h1']['mobile'])) {
            $css .= "body .post-content h1 { font-size: {$font_configs['titulo_conteudo_h1']['mobile']} !important; }";
        }
        if (isset($font_configs['titulo_conteudo_h2']['mobile'])) {
            $css .= "body .post-content h2 { font-size: {$font_configs['titulo_conteudo_h2']['mobile']} !important; }";
        }
        if (isset($font_configs['titulo_conteudo_h3']['mobile'])) {
            $css .= "body .post-content h3 { font-size: {$font_configs['titulo_conteudo_h3']['mobile']} !important; }";
        }
        
        $css .= '}';
        
        $css .= '</style>';
        $css .= '<!-- CSS para títulos de conteúdo aplicado dinamicamente -->';
        
        return $css . $content;
    }

    function injectSections($content, $sections, $font_configs = []) {

        if (stripos($content, '</p>') === false) {
            return $content;
        }

        $paragraphs = explode('</p>', $content);

        if (count($paragraphs) < 3) {
            return $content;
        }

        $all_injections = [];
        
        if (!empty($sections)) {
            usort($sections, function($a, $b) {
                return $a['point'] <=> $b['point'];
            });
            
            foreach ($sections as $section) {
                $all_injections[] = [
                    'type' => 'section',
                    'point' => $section['point'],
                    'html' => buildPostsSectionHtml($section['title'], $section['posts'], $font_configs)
                ];
            }
        }
        
        usort($all_injections, function($a, $b) {
            return $a['point'] <=> $b['point'];
        });

        $offset = 0;
        foreach ($all_injections as $injection) {
            $injection_point = $injection['point'] + $offset;
            
            if (count($paragraphs) >= $injection_point) {
                array_splice($paragraphs, $injection_point, 0, $injection['html']);
                $offset++;
            }
        }

        return implode('</p>', $paragraphs);
    }


    include 'includes/header.php';

?>

<style>
    #box-ads-inner-post {
        margin: 30px auto;
        max-width: 600px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        background-color: #ffffff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    #head-box-ads {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        background: #f9f9f9;
        border-bottom: 1px solid #eee;
    }

    #head-box-ads .h-ad {
        background: #007bff;
        color: white;
        font-size: 10px;
        font-weight: bold;
        padding: 2px 6px;
        border-radius: 3px;
        margin-right: 10px;
    }

    #head-box-ads .p-ad {
        color: #5f6368;
        font-size: 12px;
    }

    #content-box-ads {
        padding: 0 1.2rem;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    #inner-content {
        display: flex;
        align-items: center;
        gap: 3rem;
    }

    #box-img  {
        width: 350px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #box-description-ad {

    }

    #box-description-ad h3 {
        margin: 0 0 8px 0;
        font-size: 18px;
        color: #1a0dab;
        line-height: 1.2;
    }

    #box-description-ad p {
        margin: 0;
        font-size: 14px!important;
        color: #4d5156;
        line-height: 1.4!important;
    }

    #footer-box-ads {
        padding: 0 15px 15px;
    }

    #footer-box-ads a {
        display: block;
        text-align: center;
        background: #1a73e8;
        color: white;
        text-decoration: none;
        padding: 10px;
        border-radius: 4px;
        font-weight: 500;
        font-size: 14px;
    }

    #footer-box-ads a:hover {
        background: #2c86fb;
    }

    @media screen and (max-width: 768px) {
        #box-ads-inner-post {
            margin: 20px 10px;
            max-width: 100%;
        }

        #inner-content {
            flex-direction: column;
            gap: 1.5rem;
            align-items: flex-start;
        }

        #box-img {
            width: 100%;
            max-width: 100%;
        }
        
        #box-img img {
            width: 100%;
            height: auto;
            border-radius: 4px;
        }

        #box-description-ad {
            width: 100%;
            box-sizing: border-box;
        }

        #box-description-ad h3 {
            font-size: 16px;
        }

        #box-description-ad p {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        #footer-box-ads {
            padding: 15px;
        }
    }

</style>


<div class="container">
    <div class="row align-items-start">
        <div class="col-md-8">

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo BLOG_URL; ?>">Início</a></li>
                    <?php if (isset($post['categoria_nome'])): ?>
                        <li class="breadcrumb-item"><a href="<?php echo BLOG_URL; ?>/categoria/<?php echo htmlspecialchars($post['categoria_slug']); ?>">
                            <?php echo htmlspecialchars($post['categoria_nome']); ?>
                        </a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?php echo htmlspecialchars($post['titulo']); ?>
                    </li>
                </ol>
            </nav>

            <h1 class="mt-4 mb-3 title-posts"><?php echo htmlspecialchars($post['titulo']); ?></h1>



            <?php if (!empty($post['autor_id'])): ?>
                <p class="lead">
                    por <a href="<?php echo BLOG_URL; ?>/autor/<?php echo $post['autor_id']; ?>">
                        <?php echo htmlspecialchars($post['autor_nome']); ?>
                    </a>
                </p>
            <?php else: ?>
                <p class="lead">por <?php echo htmlspecialchars($post['autor_nome']); ?></p>
            <?php endif; ?>

            <?php if (!empty($post['tags'])): ?>
            <div class="post-tags mb-3">
                <i class="fas fa-tags"></i>
                <?php foreach ($post['tags'] as $tag): ?>
                <span class="badge bg-secondary text-decoration-none me-1">
                    <?php echo htmlspecialchars($tag['nome']); ?>
                </span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>


            <hr>

            <div class="post-meta mb-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="meta-info">
                        <span class="me-1"><i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($post['data_publicacao'])); ?></span>
                        <span class="me-1"><i class="far fa-folder"></i> <a href="<?php echo BLOG_URL; ?>/categoria/<?php echo htmlspecialchars($post['categoria_slug']); ?>"><?php echo htmlspecialchars($post['categoria_nome']); ?></a></span>
                        <!-- <span><i class="far fa-eye"></i> <?php echo number_format($post['visualizacoes']); ?> visualizações</span> -->
                    </div>

                    <div class="social-sharing-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(BLOG_URL . '/post/' . $post['slug']); ?>"
                        target="_blank" class="social-share-btn facebook-share" aria-label="Compartilhar no Facebook">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951"/>
                            </svg>
                        </a>
                        
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(BLOG_URL . '/post/' . $post['slug']); ?>&text=<?php echo urlencode($post['titulo']); ?>"
                        target="_blank" class="social-share-btn twitter-share" aria-label="Compartilhar no Twitter">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/>
                            </svg>
                        </a>
                        
                        <a href="https://wa.me/?text=<?php echo urlencode($post['titulo'] . ' ' . BLOG_URL . '/post/' . $post['slug']); ?>"
                        target="_blank" class="social-share-btn whatsapp-share" aria-label="Compartilhar no WhatsApp">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

<!-- BLOCO DE ANUNCIO NATIVO INTERNO -->           
<!--

    <div id="box-ads-inner-post">

        <div id="head-box-ads">
            <span class="h-ad">AD</span>
            <span class="p-ad">Oferta Relâmpago</span>
        </div>

        <div id="content-box-ads">
            <div id="inner-content">
                <div id="box-img">
                    <img src="https://www.brasilhilario.com.br/uploads/images/69ad9835e6d1c.webp" alt="Ads Oferta Relâpago" width="200" height="200">
                </div>
                <div id="box-description-ad">
                    <h3>Notebook Dell Latitude E5470</h3>
                    <p>Intel i5 de 6ª Geração / 16Gb RAM C/ HD 500Gb Pronto pra Uso E Mmochila Porta Notebook.</p>
                </div>
            </div>
        </div>

        <div id="footer-box-ads">
            <a href="https://s.shopee.com.br/2qPg7gJSV6" target="_blank">
                VER OFERTAS AGORA
            </a>
        </div>

    </div>
-->

            <div class="post-content">
                <?php 
                $content_to_display = '';
                if ($post['editor_type'] === 'markdown') {
                    $parsedown = new Parsedown();
                    $content_to_display = $parsedown->text($post['conteudo']);
                } else {
                    $content_to_display = $post['conteudo'];
                }
                
                $sections_to_inject = [];

                $first_injection_point = 5;
                $second_injection_point = 11;

                if (!empty($related_posts)) {
                    $sections_to_inject[] = [
                        'title' => 'Leia Também',
                        'posts' => $related_posts,
                        'point' => $first_injection_point
                    ];
                }

                if (!empty($latest_posts)) {
                    $sections_to_inject[] = [
                        'title' => 'Últimas do Portal',
                        'posts' => $latest_posts,
                        'point' => $second_injection_point
                    ];
                }

                $content_to_display = applyContentTitleStyles($content_to_display, $font_configs);

                echo injectSections($content_to_display, $sections_to_inject, $font_configs);
                ?>

            </div>

            <hr>

            <?php 
                include 'includes/grupos-anuncios-conteudo.php';
            ?>

            <div class="card my-4">
                <h5 class="card-header">Deixe um Comentário:</h5>
                <div class="card-body">
                    <div id="fb-root"></div>
                    <script async defer crossorigin="anonymous" 
                            src="https://connect.facebook.net/pt_BR/sdk.js#xfbml=1&version=v10.0&appId=YOUR_FACEBOOK_APP_ID&autoLogAppEvents=1">
                    </script>
                    <div class="fb-comments" 
                         data-href="<?php echo BLOG_URL . '/post/' . $post['slug']; ?>" 
                         data-width="100%" data-numposts="5">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <?php include 'includes/sidebar.php'; ?>
        </div>

    </div>

</div>

<?php include 'includes/footer.php'; ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function findNextParagraph(startEl) {
            let el = startEl.nextElementSibling;
            while (el) {
                if (el.tagName && el.tagName.toLowerCase() === 'p') return el;
                const p = el.querySelector && el.querySelector('p');
                if (p) return p;
                el = el.nextElementSibling;
            }

            return null;
        }

        document.querySelectorAll('.continue-reading-link').forEach(function(link){
            link.addEventListener('click', function(e){
                e.preventDefault();
                const sectionId = this.getAttribute('data-section');
                const sectionEl = document.getElementById(sectionId);
                if (!sectionEl) return;
                const nextP = findNextParagraph(sectionEl);
                const target = nextP || sectionEl;
                const y = target.getBoundingClientRect().top + window.pageYOffset - 80;
                window.scrollTo({ top: y, behavior: 'smooth' });
            });
        });
    });
</script>
<?php ob_end_flush(); ?> 