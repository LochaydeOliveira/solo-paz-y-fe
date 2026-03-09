<?php
// Carregar anúncios para o conteúdo principal com layout específico
require_once __DIR__ . '/AnunciosManager.php';

try {
    $anunciosManager = new AnunciosManager($pdo);
    $anunciosConteudo = $anunciosManager->getAnunciosPorLocalizacao('conteudo');
    
    if (!empty($anunciosConteudo)) {
        // Verificar se há anúncios com layout de grade
        $anunciosGrade = array_filter($anunciosConteudo, function($anuncio) {
            return ($anuncio['layout'] ?? 'carrossel') === 'grade';
        });
        
        $anunciosCarrossel = array_filter($anunciosConteudo, function($anuncio) {
            return ($anuncio['layout'] ?? 'carrossel') === 'carrossel';
        });
        
        // Exibir anúncios em grade (máximo 8)
        if (!empty($anunciosGrade)) {
            $anunciosGrade = array_slice($anunciosGrade, 0, 8); // Limitar a 8 anúncios
            echo '<div class="anuncios-grade-container">';
            echo '<div class="anuncios-grade">';
            foreach ($anunciosGrade as $anuncio) {
                echo '<div class="anuncio-card-grade">';
                echo '<div class="anuncio-patrocinado-badge">Anúncio</div>';
                if (!empty($anuncio['imagem'])) {
                    echo '<a href="' . htmlspecialchars($anuncio['link_compra']) . '" target="_blank">';
                    echo '<img src="' . htmlspecialchars($anuncio['imagem']) . '" alt="' . htmlspecialchars($anuncio['titulo']) . '" class="anuncio-imagem-grade">';
                    echo '</a>';
                }
                echo '<a href="' . htmlspecialchars($anuncio['link_compra']) . '" target="_blank" class="anuncio-titulo-grade">' . htmlspecialchars($anuncio['titulo']) . '</a>';
                echo '</div>';
            }
            echo '</div>';
            echo '</div>';
        }
        
        // Exibir anúncios em carrossel
        if (!empty($anunciosCarrossel)) {
            echo '<div class="anuncios-carrossel-container">';
            echo '<div class="anuncios-carrossel">';
            foreach ($anunciosCarrossel as $anuncio) {
                echo '<div class="anuncio-card-carrossel">';
                echo '<div class="anuncio-patrocinado-badge">Anúncio</div>';
                if (!empty($anuncio['imagem'])) {
                    echo '<a href="' . htmlspecialchars($anuncio['link_compra']) . '" target="_blank">';
                    echo '<img src="' . htmlspecialchars($anuncio['imagem']) . '" alt="' . htmlspecialchars($anuncio['titulo']) . '" class="anuncio-imagem-carrossel">';
                    echo '</a>';
                }
                echo '<a href="' . htmlspecialchars($anuncio['link_compra']) . '" target="_blank" class="anuncio-titulo-carrossel">' . htmlspecialchars($anuncio['titulo']) . '</a>';
                echo '</div>';
            }
            echo '</div>';
            echo '</div>';
        }
    }
} catch (Exception $e) {
    // Silenciar erros para não afetar o site
    error_log("Erro ao carregar anúncios do conteúdo: " . $e->getMessage());
}
?> 