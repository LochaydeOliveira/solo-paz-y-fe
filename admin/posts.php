<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/config.php';
require_once '../includes/db.php';
require_once 'includes/auth.php';

// Parâmetros de busca e filtros
$busca = $_GET['busca'] ?? '';
$categoria_id = $_GET['categoria_id'] ?? '';
$status = $_GET['status'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';
$ordenacao = $_GET['ordenacao'] ?? 'criado_em DESC';

// Construir a consulta SQL com filtros
$sql = "SELECT p.*, c.nome as categoria_nome 
                         FROM posts p 
                         LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE 1=1";
$params = [];

// Filtro de busca por título
if (!empty($busca)) {
    $sql .= " AND (p.titulo LIKE ? OR p.conteudo LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

// Filtro por categoria
if (!empty($categoria_id)) {
    $sql .= " AND p.categoria_id = ?";
    $params[] = $categoria_id;
}

// Filtro por status
if ($status !== '') {
    $sql .= " AND p.publicado = ?";
    $params[] = $status;
}

// Filtro por data de início
if (!empty($data_inicio)) {
    $sql .= " AND DATE(p.criado_em) >= ?";
    $params[] = $data_inicio;
}

// Filtro por data de fim
if (!empty($data_fim)) {
    $sql .= " AND DATE(p.criado_em) <= ?";
    $params[] = $data_fim;
}

// Ordenação
$sql .= " ORDER BY $ordenacao";

// Buscar posts com filtros
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erro ao buscar posts: " . $e->getMessage());
}

// Buscar categorias para o filtro
try {
    $stmt = $pdo->prepare("SELECT id, nome FROM categorias ORDER BY nome");
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categorias = [];
}

$page_title = 'Posts';
include 'includes/header.php';
?>


<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Posts</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="novo-post.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Novo Post
        </a>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Operação realizada com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
            
            <!-- Sistema de Busca e Filtros -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-search"></i> Buscar e Filtrar Posts</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <!-- Busca por texto -->
                        <div class="col-md-4">
                            <label for="busca" class="form-label">Buscar por título ou conteúdo</label>
                            <input type="text" class="form-control" id="busca" name="busca" 
                                   value="<?php echo htmlspecialchars($busca); ?>" 
                                   placeholder="Digite para buscar...">
                        </div>
                        
                        <!-- Filtro por categoria -->
                        <div class="col-md-2">
                            <label for="categoria_id" class="form-label">Categoria</label>
                            <select class="form-select" id="categoria_id" name="categoria_id">
                                <option value="">Todas as categorias</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                            <?php echo $categoria_id == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Filtro por status -->
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos os status</option>
                                <option value="1" <?php echo $status === '1' ? 'selected' : ''; ?>>Publicado</option>
                                <option value="0" <?php echo $status === '0' ? 'selected' : ''; ?>>Rascunho</option>
                            </select>
                        </div>
                        
                        <!-- Filtro por data de início -->
                        <div class="col-md-2">
                            <label for="data_inicio" class="form-label">Data início</label>
                            <input type="date" class="form-control" id="data_inicio" name="data_inicio" 
                                   value="<?php echo htmlspecialchars($data_inicio); ?>">
                        </div>
                        
                        <!-- Filtro por data de fim -->
                        <div class="col-md-2">
                            <label for="data_fim" class="form-label">Data fim</label>
                            <input type="date" class="form-control" id="data_fim" name="data_fim" 
                                   value="<?php echo htmlspecialchars($data_fim); ?>">
                        </div>
                        
                        <!-- Ordenação -->
                        <div class="col-md-3">
                            <label for="ordenacao" class="form-label">Ordenar por</label>
                            <select class="form-select" id="ordenacao" name="ordenacao">
                                <option value="criado_em DESC" <?php echo $ordenacao === 'criado_em DESC' ? 'selected' : ''; ?>>Mais recentes</option>
                                <option value="criado_em ASC" <?php echo $ordenacao === 'criado_em ASC' ? 'selected' : ''; ?>>Mais antigos</option>
                                <option value="titulo ASC" <?php echo $ordenacao === 'titulo ASC' ? 'selected' : ''; ?>>Título A-Z</option>
                                <option value="titulo DESC" <?php echo $ordenacao === 'titulo DESC' ? 'selected' : ''; ?>>Título Z-A</option>
                                <option value="visualizacoes DESC" <?php echo $ordenacao === 'visualizacoes DESC' ? 'selected' : ''; ?>>Mais visualizados</option>
                                <option value="visualizacoes ASC" <?php echo $ordenacao === 'visualizacoes ASC' ? 'selected' : ''; ?>>Menos visualizados</option>
                            </select>
                        </div>
                        
                        <!-- Botões de ação -->
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <a href="posts.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpar Filtros
                                </a>
                                <span class="badge bg-info align-self-center">
                                    <?php echo count($posts); ?> post(s) encontrado(s)
                                </span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Categoria</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Visualizações</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($posts)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-search fa-2x mb-3"></i>
                                    <p>Nenhum post encontrado com os filtros aplicados.</p>
                                    <a href="posts.php" class="btn btn-outline-primary btn-sm">Limpar filtros</a>
                                </td>
                            </tr>
                        <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo BLOG_URL . '/post/' . $post['slug']; ?>" target="_blank">
                                        <?php echo htmlspecialchars($post['titulo']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($post['categoria_nome'] ?? 'Sem categoria'); ?></td>
                                <td>
                                    <?php if ($post['publicado']): ?>
                                        <span class="badge bg-success">Publicado</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Rascunho</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($post['criado_em'])); ?></td>
                                <td><?php echo number_format($post['visualizacoes'], 0, ',', '.'); ?></td>
                                <td>
                                    <a href="editar-post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="excluir-post.php?id=<?php echo $post['id']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Tem certeza que deseja excluir este post?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>


<?php include 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const alertElement = document.querySelector('.alert.alert-success');
        if (alertElement) {
            setTimeout(() => {
                // Adiciona a classe 'show' e 'fade' para transição e depois remove a classe 'show'
                // para iniciar o fade out, se a mensagem não for dismissível manualmente
                if (alertElement.classList.contains('show')) {
                    alertElement.classList.remove('show');
                } else {
                    // Caso já esteja sem a classe 'show' por alguma razão, apenas remova
                    alertElement.remove();
                }
                // Remove o alerta do DOM após a transição
                alertElement.addEventListener('transitionend', () => alertElement.remove());
            }, 3000); // 3 segundos
        }
    });
</script> 
