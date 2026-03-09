<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/config.php';
require_once '../includes/db.php';
require_once 'includes/auth.php';

$total_posts = 0;
$posts_publicados = 0;
$total_usuarios = 0;
$posts_recentes = [];
$error = null;

try {

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM posts");
    $total_posts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM posts WHERE publicado = 1");
    $posts_publicados = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $total_usuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $pdo->query("
        SELECT p.id, p.titulo, p.data_publicacao, p.publicado, p.visualizacoes, u.nome as autor 
        FROM posts p 
        LEFT JOIN usuarios u ON p.autor_id = u.id 
        ORDER BY p.data_publicacao DESC 
        LIMIT 5
    ");
    $posts_recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Erro ao obter estatísticas: " . $e->getMessage());
    $error = "Erro ao carregar estatísticas. Por favor, tente novamente mais tarde.";
}

$page_title = 'Dashboard';
include 'includes/header.php';
?>


<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
            
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total de Posts</h5>
                            <h2 class="card-text"><?php echo $total_posts; ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Posts Publicados</h5>
                            <h2 class="card-text"><?php echo $posts_publicados; ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total de Usuários</h5>
                            <h2 class="card-text"><?php echo $total_usuarios; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            
            
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Posts Recentes</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Título</th>
                                            <th>Autor</th>
                                            <th>Status</th>
                                            <th>Data</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($posts_recentes as $post): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($post['titulo'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($post['autor'] ?? 'Sem autor'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo ($post['publicado'] ?? 0) ? 'success' : 'warning'; ?>">
                                                    <?php echo ($post['publicado'] ?? 0) ? 'Publicado' : 'Rascunho'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($post['data_publicacao'] ?? 'now')); ?></td>
                                            <td>
                                                <a href="editar-post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

<?php include 'includes/footer.php'; ?> 
