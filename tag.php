<?php
require_once 'config/config.php';
require_once 'includes/db.php'; // Deve inicializar $pdo
require_once 'includes/functions.php';

// Verificar se o slug da tag foi fornecido
$tag_slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($tag_slug)) {
    header('Location: ' . BLOG_URL);
    exit;
}

// Buscar a tag
$stmt = $pdo->prepare("SELECT * FROM tags WHERE slug = ?");
$stmt->execute([$tag_slug]);
$tag = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tag) {
    header('Location: ' . BLOG_URL . '/404.php');
    exit;
}

// Configuração da paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$posts_per_page = POSTS_PER_PAGE;
$offset = ($page - 1) * $posts_per_page;

// Buscar total de posts da tag
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT p.id) as total
    FROM posts p
    JOIN post_tags pt ON p.id = pt.post_id
    WHERE pt.tag_id = :tag_id AND p.publicado = 1
");
$stmt->bindValue(':tag_id', $tag['id'], PDO::PARAM_INT);
$stmt->execute();
$total_posts = $stmt->fetchColumn();
$total_pages = ceil($total_posts / $posts_per_page);

// Buscar posts da tag
$stmt = $pdo->prepare("
    SELECT p.*, c.nome as categoria_nome, c.slug as categoria_slug,
        GROUP_CONCAT(DISTINCT CONCAT(t.id, ':', t.nome, ':', t.slug) SEPARATOR ',') as tags_data
    FROM posts p
    JOIN categorias c ON p.categoria_id = c.id
    JOIN post_tags pt ON p.id = pt.post_id
    LEFT JOIN post_tags pt2 ON p.id = pt2.post_id
    LEFT JOIN tags t ON pt2.tag_id = t.id
    WHERE pt.tag_id = :tag_id AND p.publicado = 1
    GROUP BY p.id
    ORDER BY p.criado_em DESC
    LIMIT :limit OFFSET :offset
");

// Bind dos parâmetros nomeados
$stmt->bindValue(':tag_id', $tag['id'], PDO::PARAM_INT);
$stmt->bindValue(':limit', $posts_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processar tags para cada post
foreach ($posts as &$post) {
    $post['tags'] = [];
    if (!empty($post['tags_data'])) {
        $tags_array = explode(',', $post['tags_data']);
        foreach ($tags_array as $tag_data) {
            list($id, $nome, $tag_slug) = explode(':', $tag_data);
            $post['tags'][] = [
                'id' => $id,
                'nome' => $nome,
                'slug' => $tag_slug
            ];
        }
    }
    unset($post['tags_data']);
}

// Incluir o cabeçalho
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8">
            <h1 class="mb-4">
                <i class="fas fa-tag"></i> Posts com a tag: <?php echo htmlspecialchars($tag['nome']); ?>
            </h1>

            <?php if (empty($posts)): ?>
                <div class="alert alert-info">
                    Nenhum post encontrado com esta tag.
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <article class="card mb-4">
                        <div class="card-body">
                            <h2 class="card-title">
                                <a href="<?php echo BLOG_PATH; ?>/post/<?php echo htmlspecialchars($post['slug']); ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($post['titulo']); ?>
                                </a>
                            </h2>
                            
                            <div class="post-meta mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($post['criado_em'])); ?>
                                    <i class="fas fa-folder ms-2"></i> 
                                    <a href="<?php echo BLOG_URL; ?>/categoria/<?php echo htmlspecialchars($post['categoria_slug']); ?>" class="text-muted">
                                        <?php echo htmlspecialchars($post['categoria_nome']); ?>
                                    </a>
                                </small>
                            </div>
                            
                            <?php if (!empty($post['tags'])): ?>
                                <div class="post-tags mb-3">
                                    <?php foreach ($post['tags'] as $post_tag): ?>
                                        <span class="badge bg-info text-dark me-1">
                                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($post_tag['nome']); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <p class="card-text"><?php echo htmlspecialchars($post['resumo']); ?></p>
                            
                            <a href="<?php echo BLOG_PATH; ?>/post/<?php echo htmlspecialchars($post['slug']); ?>" class="btn btn-primary">
                                Ler mais
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>

                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Navegação de páginas">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?slug=<?php echo urlencode($tag_slug); ?>&page=<?php echo $page - 1; ?>">
                                        Anterior
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?slug=<?php echo urlencode($tag_slug); ?>&page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?slug=<?php echo urlencode($tag_slug); ?>&page=<?php echo $page + 1; ?>">
                                        Próximo
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="col-lg-4">
            <?php include 'includes/sidebar.php'; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
