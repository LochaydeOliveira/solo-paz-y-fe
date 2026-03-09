<?php
$debug = isset($_GET['debug_sidebar']) && $_GET['debug_sidebar'] == '1';

$postId = null;
if (isset($post) && isset($post['id'])) {
    $postId = (int)$post['id'];
}

if ($postId === null) {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (preg_match('#/post/([a-z0-9\-]+)#i', $uri, $m)) {
        $slug = $m[1];
        try {
            $stmt = $pdo->prepare("SELECT id FROM posts WHERE slug = ? AND publicado = 1 LIMIT 1");
            $stmt->execute([$slug]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) { $postId = (int)$row['id']; }
        } catch (Exception $e) {
            if ($debug) error_log('SIDEBAR DEBUG: Erro ao obter postId por slug: ' . $e->getMessage());
        }
    }
}

if ($postId === null) {
    return;
}

require_once __DIR__ . '/GruposAnunciosManager.php';

$listaAnunciosSidebar = [];

try {
    $gruposManager = new GruposAnunciosManager($pdo);
    $gruposSidebar = $gruposManager->getGruposPorLocalizacao('sidebar', $postId, false);

    $anunciosRender = [];
    if (!empty($gruposSidebar)) {
        foreach ($gruposSidebar as $grupo) {
            $anuncios = $gruposManager->getAnunciosDoGrupo($grupo['id']);
            foreach ($anuncios as $anuncio) {
                $anunciosRender[$anuncio['id']] = $anuncio;
            }
        }
    } else {
        $sql = "SELECT a.* FROM anuncios a
                INNER JOIN anuncios_posts ap ON ap.anuncio_id = a.id
                WHERE a.localizacao = 'sidebar' AND a.ativo = 1 AND ap.post_id = ?
                ORDER BY a.criado_em DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$postId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $anuncio) {
            $anunciosRender[$anuncio['id']] = $anuncio;
        }
    }

    $listaAnunciosSidebar = array_values($anunciosRender);
    shuffle($listaAnunciosSidebar); 

} catch (Exception $e) {
    error_log('Erro ao carregar anúncios da sidebar: ' . $e->getMessage());
}

if (!function_exists('renderizarAnuncioIndividual')) {
    function renderizarAnuncioIndividual($anuncio) {
        if (!$anuncio) return;
        ?>
        <div class="card mb-4 anuncio-item" data-aos="fade-left">
            <div class="anuncio-card-sidebar">
                <div class="anuncio-patrocinado-badge-sidebar">Patrocinado</div>

                <?php if (!empty($anuncio['imagem'])): ?>
                    <a href="<?php echo htmlspecialchars($anuncio['link_compra']); ?>" target="_blank" onclick="registrarCliqueAnuncio(<?php echo (int)$anuncio['id']; ?>, 'imagem')">
                        <img src="<?php echo htmlspecialchars($anuncio['imagem']); ?>" alt="<?php echo htmlspecialchars($anuncio['titulo']); ?>" class="anuncio-imagem-sidebar" loading="lazy">
                    </a>
                <?php endif; ?>

                <a href="<?php echo htmlspecialchars($anuncio['link_compra']); ?>" target="_blank" class="anuncio-titulo-sidebar" style="font-size: 15px!important;padding: 0 0.9rem 0 0!important;font-weight: 500!important;" onclick="registrarCliqueAnuncio(<?php echo (int)$anuncio['id']; ?>, 'titulo')">
                    <?php echo htmlspecialchars($anuncio['titulo']); ?>
                </a>
                <?php if (!empty($anuncio['marca'])): ?>
                    <div class="marca-badge">
                        <?php if ($anuncio['marca'] === 'shopee'): ?>
                            <span class="brand-badge badge-shopee"><i class="fas fa-shopping-cart"></i> Shopee</span>
                        <?php elseif ($anuncio['marca'] === 'amazon'): ?>
                            <span class="brand-badge badge-amazon"><i class="fab fa-amazon"></i> Amazon</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}