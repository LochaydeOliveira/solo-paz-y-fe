<?php
require_once 'config/config.php';
require_once 'includes/db.php';

$page_title = 'Página não encontrada - Brasil Hilário';
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <h1 class="display-1 text-muted">404</h1>
            <h2 class="mb-4">Página não encontrada</h2>
            <p class="lead mb-4">A página que você está procurando não existe ou foi movida.</p>
            <div class="mb-4">
                <a href="<?php echo BLOG_URL; ?>" class="btn btn-primary me-2">
                    <i class="fas fa-home"></i> Voltar ao Início
                </a>
                <a href="<?php echo BLOG_URL; ?>/busca" class="btn btn-outline-secondary">
                    <i class="fas fa-search"></i> Buscar Conteúdo
                </a>
            </div>
            
            <div class="mt-5">
                <h3>Posts Recentes</h3>
                <div class="row">
                    <?php
                    try {
                        $stmt = $pdo->prepare("
                            SELECT id, titulo, slug, resumo, imagem_destacada, criado_em 
                            FROM posts 
                            WHERE publicado = 1 
                            ORDER BY criado_em DESC 
                            LIMIT 3
                        ");
                        $stmt->execute();
                        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($posts as $post):
                    ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <?php if (!empty($post['imagem_destacada'])): ?>
                                    <img src="<?php echo BLOG_URL; ?>/uploads/images/<?php echo htmlspecialchars($post['imagem_destacada']); ?>" 
                                         class="card-img-top" alt="<?php echo htmlspecialchars($post['titulo']); ?>"
                                         style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($post['titulo']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars(substr($post['resumo'], 0, 100)) . '...'; ?></p>
                                    <a href="<?php echo BLOG_URL; ?>/post/<?php echo $post['slug']; ?>" class="btn btn-sm btn-primary">
                                        Ler mais
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endforeach;
                    } catch (Exception $e) {
                        echo '<p class="text-muted">Não foi possível carregar posts recentes.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 