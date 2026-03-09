<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/db.php'; // Aqui deve ter a conexão PDO na variável $pdo

ob_start();

if (!isset($_GET['slug'])) {
    header('Location: ' . BLOG_URL);
    exit;
}

$categoria_slug = $_GET['slug'];

// Buscar categoria pelo slug
$stmt = $pdo->prepare("SELECT * FROM categorias WHERE slug = ?");
$stmt->execute([$categoria_slug]);
$categoria = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$categoria) {
    header('Location: ' . BLOG_URL);
    exit;
}

// Buscar posts da categoria
$stmt = $pdo->prepare("
    SELECT p.*, u.nome as autor_nome, c.nome as categoria_nome 
    FROM posts p 
    LEFT JOIN usuarios u ON p.autor_id = u.id 
    LEFT JOIN categorias c ON p.categoria_id = c.id 
    WHERE p.categoria_id = ? AND p.publicado = 1 
    ORDER BY p.data_publicacao DESC
");
$stmt->execute([$categoria['id']]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($posts)) {
    header('Location: ' . BLOG_URL);
    exit;
}

$og_title = "Categoria: " . $categoria['nome'] . " - " . BLOG_TITLE;
$meta_description = "Posts sobre " . $categoria['nome'] . " no " . BLOG_TITLE;
$og_url = BLOG_URL . "/categoria/" . $categoria_slug;
$og_type = "website";

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">

        <div class="col-lg-8">
            <h1 style="font-size: 25px;" class="mb-4">Categoria: <?php echo htmlspecialchars($categoria['nome']); ?></h1>
            
            <div class="row">
                <?php foreach ($posts as $post): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <?php if (!empty($post['imagem_destacada'])): ?>
                                <img src="<?php echo BLOG_URL . '/uploads/images/' . htmlspecialchars($post['imagem_destacada']); ?>" 
                                    class="card-img-top" 
                                    alt="<?php echo htmlspecialchars($post['titulo']); ?>">
                            <?php endif; ?>
                            
                            <div class="card-body no-pad">
                                <h5 class="card-title">
                                    <a href="<?php echo BLOG_URL . '/post/' . htmlspecialchars($post['slug']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($post['titulo']); ?>
                                    </a>
                                </h5>
                                
                                <p class="card-text">
                                    <?php echo htmlspecialchars(mb_strimwidth($post['resumo'], 0, 150, '...')); ?>
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt"></i> 
                                        <?php echo date('d/m/Y', strtotime($post['data_publicacao'])); ?>
                                    </small>
                                    <a href="<?php echo BLOG_URL . '/post/' . htmlspecialchars($post['slug']); ?>" class="lead">
                                        Ler mais
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>        

        <div class="col-lg-4">
            <?php include 'includes/sidebar.php'; ?>
        </div>

        <ins class="adsbygoogle"
            style="display:block"
            data-ad-client="ca-pub-8313157699231074"
            data-ad-slot="6450653464"
            data-ad-format="auto"
            data-full-width-responsive="true">
        </ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>
</div>

<?php

include 'includes/footer.php';

ob_end_flush();
?>
