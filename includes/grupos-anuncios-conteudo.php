<?php
// Carregar grupos de anúncios para o conteúdo principal
require_once __DIR__ . '/GruposAnunciosManager.php';

try {
    $gruposManager = new GruposAnunciosManager($pdo);
    
    // Determinar se estamos na página inicial ou em um post específico
    // Verificar se estamos na página inicial de forma mais robusta
    $current_url = $_SERVER['REQUEST_URI'];
    $isHomePage = (
        $current_url === '/' || 
        $current_url === '/index.php' || 
        preg_match('/^\/\d+$/', $current_url) || // Páginas numeradas como /1, /2, etc.
        (basename($_SERVER['PHP_SELF']) === 'index.php' && !isset($_GET['slug']))
    );
    $postId = isset($post['id']) ? $post['id'] : null;
    
    // Debug temporário (remover depois)
    if (isset($_GET['debug_anuncios'])) {
        error_log("DEBUG ANÚNCIOS - isHomePage: " . ($isHomePage ? 'SIM' : 'NÃO') . ", postId: " . ($postId ?? 'NULL') . ", URL: " . $current_url);
    }
    
    $gruposConteudo = $gruposManager->getGruposPorLocalizacao('conteudo', $postId, $isHomePage);
    
    if (!empty($gruposConteudo)) {
        foreach ($gruposConteudo as $grupo) {
            $anuncios = $gruposManager->getAnunciosDoGrupo($grupo['id']);
            
            if (empty($anuncios)) continue;
            
            // Limitar a 8 anúncios para grade
            if ($grupo['layout'] === 'grade') {
                $anuncios = array_slice($anuncios, 0, 8);
            }
            
            if ($grupo['layout'] === 'grade') {
                // Layout de Grade (máximo 8 anúncios)
                echo '<div class="grupo-anuncios-grade" data-grupo-id="' . $grupo['id'] . '">';
                echo '<div class="anuncios-grade-grid">';
                foreach ($anuncios as $anuncio) {
                    echo '<div class="anuncio-card-grade">';
                    echo '<div class="anuncio-patrocinado-badge">Patrocinado</div>';
                    
                    // Badge de marca
                    if (!empty($anuncio['marca'])) {
                        echo '<div class="marca-badge">';
                        if ($anuncio['marca'] === 'shopee') {
                            echo '<span class="brand-badge badge-shopee"><i class="fas fa-shopping-cart"></i> Shopee</span>';
                        } elseif ($anuncio['marca'] === 'amazon') {
                            echo '<span class="brand-badge badge-amazon"><i class="fab fa-amazon"></i> Amazon</span>';
                        }
                        echo '</div>';
                    }
                    if (!empty($anuncio['imagem'])) {
                        echo '<a href="' . htmlspecialchars($anuncio['link_compra']) . '" target="_blank" class="anuncio-imagem-link" onclick="registrarCliqueAnuncio(' . $anuncio['id'] . ', \'imagem\')">';
                        echo '<img src="' . htmlspecialchars($anuncio['imagem']) . '" alt="' . htmlspecialchars($anuncio['titulo']) . '" class="anuncio-imagem-grade" loading="lazy" decoding="async">';
                        echo '</a>';
                    }
                    echo '<a href="' . htmlspecialchars($anuncio['link_compra']) . '" target="_blank" class="anuncio-titulo-grade" onclick="registrarCliqueAnuncio(' . $anuncio['id'] . ', \'titulo\')">' . htmlspecialchars($anuncio['titulo']) . '</a>';
                    echo '</div>';
                }
                echo '</div>';
                echo '</div>';
                
            } else {
                // Layout de Carrossel (ilimitado)
                echo '<div class="grupo-anuncios-carrossel" data-grupo-id="' . $grupo['id'] . '">';
                echo '<div class="anuncios-carrossel-wrapper">';
                echo '<div class="anuncios-carrossel">';
                foreach ($anuncios as $anuncio) {
                    echo '<div class="anuncio-card-carrossel">';
                    echo '<div class="anuncio-patrocinado-badge">Patrocinado</div>';
                    
                    // Badge de marca
                    if (!empty($anuncio['marca'])) {
                        echo '<div class="marca-badge">';
                        if ($anuncio['marca'] === 'shopee') {
                            echo '<span class="brand-badge badge-shopee"><i class="fas fa-shopping-cart"></i> Shopee</span>';
                        } elseif ($anuncio['marca'] === 'amazon') {
                            echo '<span class="brand-badge badge-amazon"><i class="fab fa-amazon"></i> Amazon</span>';
                        }
                        echo '</div>';
                    }
                    if (!empty($anuncio['imagem'])) {
                        echo '<a href="' . htmlspecialchars($anuncio['link_compra']) . '" target="_blank" class="anuncio-imagem-link" onclick="registrarCliqueAnuncio(' . $anuncio['id'] . ', \'imagem\')">';
                        echo '<img src="' . htmlspecialchars($anuncio['imagem']) . '" alt="' . htmlspecialchars($anuncio['titulo']) . '" class="anuncio-imagem-carrossel" loading="lazy" decoding="async">';
                        echo '</a>';
                    }
                    echo '<a href="' . htmlspecialchars($anuncio['link_compra']) . '" target="_blank" class="anuncio-titulo-carrossel" onclick="registrarCliqueAnuncio(' . $anuncio['id'] . ', \'titulo\')">' . htmlspecialchars($anuncio['titulo']) . '</a>';
                    echo '</div>';
                }
                echo '</div>';
                echo '<button class="carrossel-btn carrossel-btn-prev" onclick="scrollCarrossel(' . $grupo['id'] . ', \'left\')">‹</button>';
                echo '<button class="carrossel-btn carrossel-btn-next" onclick="scrollCarrossel(' . $grupo['id'] . ', \'right\')">›</button>';
                echo '</div>';
                echo '</div>';
            }
        }
    }
} catch (Exception $e) {
    // Silenciar erros para não afetar o site
    error_log("Erro ao carregar grupos de anúncios: " . $e->getMessage());
}
?> 