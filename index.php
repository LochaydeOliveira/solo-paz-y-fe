<?php

    ob_start();

    require_once 'includes/db.php';
    require_once 'config/config.php';

    $request_uri = strtok($_SERVER["REQUEST_URI"], '?');
    preg_match('/\/(\d+)$/', $request_uri, $matches);
    $page_from_url = !empty($matches[1]) ? (int)$matches[1] : 0;
    $page_from_get = isset($_GET['page']) ? (int)($_GET['page'][0] ?? $_GET['page']) : 0;

    $page = max(1, $page_from_url, $page_from_get);
    $offset = ($page - 1) * POSTS_PER_PAGE;

    include 'includes/header.php';

?>

<div class="row">

    <div class="col-lg-8">

        <?php
        try {
            $limit = POSTS_PER_PAGE;

            $sql = "
                SELECT p.*, c.nome as categoria_nome, c.slug as categoria_slug, t_grouped.tags_data
                FROM posts p 
                JOIN categorias c ON p.categoria_id = c.id 
                LEFT JOIN (
                    SELECT pt.post_id, GROUP_CONCAT(DISTINCT CONCAT(t.id, ':', t.nome, ':', t.slug) ORDER BY t.nome ASC SEPARATOR ',') as tags_data
                    FROM post_tags pt
                    JOIN tags t ON pt.tag_id = t.id
                    GROUP BY pt.post_id
                ) as t_grouped ON p.id = t_grouped.post_id
                WHERE p.publicado = 1
                ORDER BY p.data_publicacao DESC 
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($posts as $key => $post_item) {
                $posts[$key]['tags'] = [];
                if (!empty($post_item['tags_data'])) {
                    $tags_array = explode(',', $post_item['tags_data']);
                    foreach ($tags_array as $tag_data) {
                        list($id, $nome, $tag_slug) = explode(':', $tag_data);
                        $posts[$key]['tags'][] = [
                            'id' => $id,
                            'nome' => $nome,
                            'slug' => $tag_slug
                        ];
                    }
                }
                unset($posts[$key]['tags_data']);
            }

            $count_stmt = $pdo->prepare("SELECT COUNT(id) as total FROM posts WHERE publicado = 1");
            $count_stmt->execute();
            $total_posts = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
            $total_pages = ceil($total_posts / POSTS_PER_PAGE);

            if (empty($posts)) {
                echo '<div class="alert alert-info">Nenhum post encontrado.</div>';
            } else {
                $post_count = 0;
                foreach ($posts as $post): 
                    $post_count++;
                ?>
                    <article class="blog-post mb-4" data-aos="fade-up">
                        <?php if (!empty($post['imagem_destacada'])): ?>
                            <div class="post-image mb-3">
                                <a href="<?php echo BLOG_URL; ?>/post/<?php echo htmlspecialchars($post['slug']); ?>">
                                    <img src="<?php echo BLOG_URL; ?>/uploads/images/<?php echo htmlspecialchars($post['imagem_destacada']); ?>" 
                                         class="img-fluid" 
                                         alt="<?php echo htmlspecialchars($post['titulo']); ?>">
                                </a>
                            </div>
                        <?php endif; ?>

                        <h2 class="display-6 fw-bold mb-3">
                            <a href="<?php echo BLOG_URL; ?>/post/<?php echo htmlspecialchars($post['slug']); ?>" class="text-decoration-none text-dark">
                                <?php echo htmlspecialchars($post['titulo']); ?>
                            </a>
                        </h2>

                        
                        <div class="post-meta mb-2">
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($post['data_publicacao'])); ?>
                                <i class="fas fa-folder ms-2"></i> 
                                <a href="<?php echo BLOG_URL; ?>/categoria/<?php echo htmlspecialchars($post['categoria_slug']); ?>" class="text-muted">
                                    <?php echo htmlspecialchars($post['categoria_nome']); ?>
                                </a>
                            </small>
                        </div>
              
                        <?php if (!empty($post['tags'])): ?>
                            <div class="post-tags mb-3">
                                <?php foreach ($post['tags'] as $tag): ?>
                                    <span class="badge bg-info text-dark me-1">
                                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($tag['nome']); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="post-excerpt mb-3">
                            <?php echo htmlspecialchars($post['resumo']); ?>
                        </div>
             
                        <a href="<?php echo BLOG_URL; ?>/post/<?php echo htmlspecialchars($post['slug']); ?>" class="lead">
                            Ler mais
                        </a>
                    </article>
                    
                <?php endforeach;

                if ($total_pages > 1):
                ?>
                <nav aria-label="Navegação de posts" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">Anterior</a>
                        </li>
                        <?php else: ?>
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Anterior</a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Próximo</a>
                        </li>
                        <?php else: ?>
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Próximo</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php
            }
        } catch (Exception $e) {
            echo '<div class="alert alert-danger">Erro ao carregar posts: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>

    <div class="col-lg-4">
        <?php include 'includes/sidebar.php'; ?>
    </div>

</div>

<?php 
    include 'includes/footer.php';
    ob_end_flush();
?>
