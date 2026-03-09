<?php
// Iniciar buffer de saída
ob_start();

// Iniciar sessão antes de qualquer saída
session_start();

require_once 'config/config.php';
require_once 'config/database.php'; // Aqui sua conexão PDO deve estar disponível na variável $pdo
require_once 'config/search.php';
require_once 'includes/header.php';

// Verifica se existe um termo de busca
$search_term = isset($_GET['q']) ? clean_search_term($_GET['q']) : '';

if (empty($search_term)) {
    header('Location: ' . BLOG_URL);
    exit;
}

// Salva o termo no histórico
save_search_history($search_term);

// Página atual
$current_page = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$current_page = max(1, $current_page);

// Função para buscar posts
function search_posts($term, $page = 1) {
    global $pdo;

    try {
        if (!$pdo) {
            throw new Exception("Erro: Conexão com o banco de dados não está disponível");
        }

        $term_wildcard = '%' . $term . '%';
        $offset = ($page - 1) * SEARCH_RESULTS_PER_PAGE;
        $limit = SEARCH_RESULTS_PER_PAGE;

        // Contar total de resultados
        $count_sql = "SELECT COUNT(*) as total 
                      FROM posts p 
                      WHERE p.publicado = 1 
                        AND (p.titulo LIKE :term OR p.conteudo LIKE :term OR p.resumo LIKE :term)";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute([':term' => $term_wildcard]);
        $total_results = $count_stmt->fetchColumn();

        // Buscar resultados da página atual
        $sql = "SELECT p.*, c.nome as categoria_nome, u.nome as autor_nome 
                FROM posts p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                LEFT JOIN usuarios u ON p.autor_id = u.id 
                WHERE p.publicado = 1 
                  AND (p.titulo LIKE :term OR p.conteudo LIKE :term OR p.resumo LIKE :term)
                ORDER BY p.data_publicacao DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        // Bind dos parâmetros, PDO não permite bind direto em LIMIT e OFFSET, então bindValue com tipo INT é usado
        $stmt->bindValue(':term', $term_wildcard, PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'posts' => $posts,
            'total' => $total_results
        ];

    } catch (Exception $e) {
        error_log("Erro na busca: " . $e->getMessage());
        echo "<div class='alert alert-danger'>";
        echo "Erro na busca: " . htmlspecialchars($e->getMessage());
        echo "</div>";
        return ['posts' => [], 'total' => 0];
    }
}

$search_results = search_posts($search_term, $current_page);
$posts = $search_results['posts'];
$total_results = $search_results['total'];

// Busca sugestões
$suggestions = get_search_suggestions($search_term);
?>

<!-- PÁGINA DE RESULTADOS DE BUSCA - ÍCONES CORRIGIDOS -->
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-3">Resultados da busca para: "<?php echo htmlspecialchars($search_term); ?>"</h1>
            <p class="text-muted"><?php echo $total_results; ?> resultado(s) encontrado(s)</p>
            
            <?php if (!empty($suggestions)): ?>
                <div class="search-suggestions mb-3">
                    <h5>Sugestões relacionadas:</h5>
                    <div class="list-group">
                        <?php foreach ($suggestions as $suggestion): ?>
                            <a href="?q=<?php echo urlencode($suggestion); ?>" class="list-group-item list-group-item-action">
                                <?php echo htmlspecialchars($suggestion); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['search_history']) && !empty($_SESSION['search_history'])): ?>
                <div class="search-history mb-3">
                    <h5>Buscas recentes:</h5>
                    <div class="list-group">
                        <?php foreach ($_SESSION['search_history'] as $history_term): ?>
                            <a href="?q=<?php echo urlencode($history_term); ?>" class="list-group-item list-group-item-action">
                                <i class="fas fa-clock me-2"></i>
                                <?php echo htmlspecialchars($history_term); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (empty($posts)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Nenhum resultado encontrado para sua busca. Tente outros termos.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($posts as $post): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card search-result-card h-100 shadow-sm">
                        <?php if (!empty($post['imagem_destacada'])): ?>
                            <img src="<?php echo BLOG_URL . '/uploads/images/' . $post['imagem_destacada']; ?>" 
                                 class="card-img-top search-result-img" 
                                 alt="<?php echo htmlspecialchars($post['titulo']); ?>">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title search-result-title">
                                <a href="<?php echo BLOG_URL . '/post/' . $post['slug']; ?>" class="text-decoration-none text-dark">
                                    <?php echo highlight_search_term(htmlspecialchars($post['titulo']), $search_term); ?>
                                </a>
                            </h5>
                            <p class="card-text search-result-excerpt flex-grow-1">
                                <?php 
                                $excerpt = !empty($post['resumo']) ? $post['resumo'] : generate_excerpt($post['conteudo']);
                                echo highlight_search_term(htmlspecialchars($excerpt), $search_term); 
                                ?>
                            </p>
                            <div class="search-result-meta mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">
                                        <i class="far fa-calendar me-1"></i>
                                        <?php echo date('d/m/Y', strtotime($post['data_publicacao'])); ?>
                                    </small>
                                    <span class="badge bg-primary">
                                        <i class="fas fa-folder me-1"></i>
                                        <?php echo htmlspecialchars($post['categoria_nome']); ?>
                                    </span>
                                </div>
                                <?php if (!empty($post['autor_nome'])): ?>
                                    <small class="text-muted d-block">
                                        <i class="fas fa-user me-1"></i>
                                        Por: <?php echo htmlspecialchars($post['autor_nome']); ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($total_results > SEARCH_RESULTS_PER_PAGE): ?>
            <div class="mt-4">
                <?php echo generate_pagination($total_results, $current_page, SEARCH_RESULTS_PER_PAGE); ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
