<?php
ob_start();
session_start();

require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/GruposAnunciosManager.php';
require_once '../includes/AnunciosManager.php';

$page_title = 'Grupos de Anúncios';

// Verificar login - usar a mesma verificação do header
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$gruposManager = new GruposAnunciosManager($pdo);
$anunciosManager = new AnunciosManager($pdo);

// Ações individuais (excluir via modal antigo)
if (isset($_POST['excluir_grupo'])) {
    $grupoId = (int)$_POST['grupo_id'];
    try {
        // Remover associações e depois o grupo
        $stmt = $pdo->prepare('DELETE FROM grupos_anuncios_items WHERE grupo_id = ?');
        $stmt->execute([$grupoId]);
        $stmt = $pdo->prepare('DELETE FROM grupos_anuncios_posts WHERE grupo_id = ?');
        $stmt->execute([$grupoId]);
        $stmt = $pdo->prepare('DELETE FROM grupos_anuncios WHERE id = ?');
        $ok = $stmt->execute([$grupoId]);
        if ($ok) {
            $mensagem = 'Grupo excluído com sucesso!';
            $tipo_mensagem = 'success';
        } else {
            $mensagem = 'Erro ao excluir grupo.';
            $tipo_mensagem = 'danger';
        }
    } catch (Exception $e) {
        $mensagem = 'Erro: ' . $e->getMessage();
        $tipo_mensagem = 'danger';
    }
}

// Ações em massa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $bulkAction = $_POST['bulk_action'];
    $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? array_filter($_POST['ids'], 'is_numeric') : [];
    if (!empty($ids)) {
        try {
            if ($bulkAction === 'ativar' || $bulkAction === 'desativar') {
                $novo = $bulkAction === 'ativar' ? 1 : 0;
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $pdo->prepare("UPDATE grupos_anuncios SET ativo = ? WHERE id IN ($placeholders)");
                $stmt->execute(array_merge([$novo], $ids));
                $mensagem = $bulkAction === 'ativar' ? 'Grupos ativados com sucesso.' : 'Grupos desativados com sucesso.';
                $tipo_mensagem = 'success';
            } elseif ($bulkAction === 'excluir') {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                // Remover associações
                $stmt = $pdo->prepare("DELETE FROM grupos_anuncios_items WHERE grupo_id IN ($placeholders)");
                $stmt->execute($ids);
                $stmt = $pdo->prepare("DELETE FROM grupos_anuncios_posts WHERE grupo_id IN ($placeholders)");
                $stmt->execute($ids);
                // Remover grupos
                $stmt = $pdo->prepare("DELETE FROM grupos_anuncios WHERE id IN ($placeholders)");
                $stmt->execute($ids);
                $mensagem = 'Grupos excluídos com sucesso.';
                $tipo_mensagem = 'success';
            }
        } catch (Exception $e) {
            $mensagem = 'Erro: ' . $e->getMessage();
            $tipo_mensagem = 'danger';
        }
    } else {
        $mensagem = 'Selecione pelo menos um grupo.';
        $tipo_mensagem = 'danger';
    }
}

// Filtros (GET)
$q = trim($_GET['q'] ?? '');
$localizacaoFiltro = $_GET['localizacao'] ?? '';
$layoutFiltro = $_GET['layout'] ?? '';
$statusFiltro = $_GET['status'] ?? '';
$temAnuncios = $_GET['tem_anuncios'] ?? '';
$temPosts = $_GET['tem_posts'] ?? '';

// Paginação
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Buscar grupos (com filtros e agregados)
try {
    $sqlBase = "SELECT g.*, 
                   COUNT(DISTINCT gi.anuncio_id) AS total_anuncios,
                   COUNT(DISTINCT gap.post_id) AS total_posts
            FROM grupos_anuncios g
            LEFT JOIN grupos_anuncios_items gi ON g.id = gi.grupo_id
            LEFT JOIN grupos_anuncios_posts gap ON g.id = gap.grupo_id";

    $conditions = [];
    $params = [];

    if ($q !== '') {
        $conditions[] = '(g.nome LIKE ?)';
        $params[] = '%' . $q . '%';
    }
    if ($localizacaoFiltro !== '') {
        $conditions[] = 'g.localizacao = ?';
        $params[] = $localizacaoFiltro;
    }
    if ($layoutFiltro !== '') {
        $conditions[] = 'g.layout = ?';
        $params[] = $layoutFiltro;
    }
    if ($statusFiltro === '1' || $statusFiltro === '0') {
        $conditions[] = 'g.ativo = ?';
        $params[] = (int)$statusFiltro;
    }

    $sql = $sqlBase;
    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' GROUP BY g.id';

    if ($temAnuncios === 'sim') {
        $sql .= ' HAVING total_anuncios > 0';
    } elseif ($temAnuncios === 'nao') {
        $sql .= ' HAVING total_anuncios = 0';
    }
    // encadear HAVING para total_posts
    if ($temPosts === 'sim') {
        $sql .= ($temAnuncios ? ' AND' : ' HAVING') . ' total_posts > 0';
    } elseif ($temPosts === 'nao') {
        $sql .= ($temAnuncios ? ' AND' : ' HAVING') . ' total_posts = 0';
    }

    // Contagem total para paginação
    $sqlCount = 'SELECT COUNT(*) AS total FROM (' . $sql . ') t';
    $stmt = $pdo->prepare($sqlCount);
    $stmt->execute($params);
    $totalRows = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    $totalPages = max(1, (int)ceil($totalRows / $perPage));
    if ($page > $totalPages) { $page = $totalPages; $offset = ($page - 1) * $perPage; }

    $fromRow = $totalRows > 0 ? ($offset + 1) : 0;
    $toRow = min($offset + $perPage, $totalRows);

    // Lista paginada
    $sql .= ' ORDER BY g.criado_em DESC LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $grupos = [];
    $mensagem = 'Erro ao carregar grupos: ' . $e->getMessage();
    $tipo_mensagem = 'danger';
}

// Buscar informações de posts específicos para cada grupo
foreach ($grupos as &$grupo) {
    if ($grupo['posts_especificos']) {
        $postsDoGrupo = $gruposManager->getPostsDoGrupo($grupo['id']);
        $grupo['posts_info'] = count($postsDoGrupo) . ' post(s) específico(s)';
        $grupo['posts_list'] = array_slice(array_column($postsDoGrupo, 'titulo'), 0, 3); // Primeiros 3 títulos
    } else {
        $grupo['posts_info'] = 'Todos os posts';
        $grupo['posts_list'] = [];
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Grupos de Anúncios</h1>
    <div>
        <a href="novo-grupo-anuncios.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Novo Grupo
        </a>
    </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <input type="text" class="form-control" name="q" placeholder="Nome do grupo" value="<?php echo htmlspecialchars($q); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Localização</label>
                    <select class="form-select" name="localizacao">
                        <option value="">Todas</option>
                        <option value="conteudo" <?php echo $localizacaoFiltro==='conteudo'?'selected':''; ?>>Conteúdo</option>
                        <option value="sidebar" <?php echo $localizacaoFiltro==='sidebar'?'selected':''; ?>>Sidebar</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Layout</label>
                    <select class="form-select" name="layout">
                        <option value="">Todos</option>
                        <option value="carrossel" <?php echo $layoutFiltro==='carrossel'?'selected':''; ?>>Carrossel</option>
                        <option value="grade" <?php echo $layoutFiltro==='grade'?'selected':''; ?>>Grade</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Todos</option>
                        <option value="1" <?php echo $statusFiltro==='1'?'selected':''; ?>>Ativo</option>
                        <option value="0" <?php echo $statusFiltro==='0'?'selected':''; ?>>Inativo</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Com anúncios</label>
                    <select class="form-select" name="tem_anuncios">
                        <option value="">Todos</option>
                        <option value="sim" <?php echo $temAnuncios==='sim'?'selected':''; ?>>Sim</option>
                        <option value="nao" <?php echo $temAnuncios==='nao'?'selected':''; ?>>Não</option>
                    </select>
                </div>
                <div class="col-md-2 mt-2">
                    <label class="form-label">Com posts</label>
                    <select class="form-select" name="tem_posts">
                        <option value="">Todos</option>
                        <option value="sim" <?php echo $temPosts==='sim'?'selected':''; ?>>Sim</option>
                        <option value="nao" <?php echo $temPosts==='nao'?'selected':''; ?>>Não</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2 mt-2">
                    <button class="btn btn-outline-secondary w-50" type="submit"><i class="fas fa-search"></i></button>
                    <a href="grupos-anuncios.php" class="btn btn-outline-dark w-50"><i class="fas fa-eraser"></i></a>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($mensagem)): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensagem; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?php if (empty($grupos)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-ad fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhum grupo de anúncios encontrado</h5>
                    <p class="text-muted">Crie seu primeiro grupo para começar a exibir anúncios organizados.</p>
                    <a href="novo-grupo-anuncios.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Criar Primeiro Grupo
                    </a>
                </div>
            <?php else: ?>
                <form method="post" id="bulkForm">
                <div class="d-flex align-items-center mb-2 gap-2">
                    <select name="bulk_action" class="form-select form-select-sm" style="max-width: 220px;">
                        <option value="">Ação em massa</option>
                        <option value="ativar">Ativar selecionados</option>
                        <option value="desativar">Desativar selecionados</option>
                        <option value="excluir">Excluir selecionados</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-primary" onclick="return confirmarBulk();">Aplicar</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width:32px"><input type="checkbox" id="checkAll"></th>
                                <th>Nome</th>
                                <th>Localização</th>
                                <th>Layout</th>
                                <th>Anúncios</th>
                                <th>Posts</th>
                                <th>Status</th>
                                <th>Criado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grupos as $grupo): ?>
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="<?php echo (int)$grupo['id']; ?>" class="row-check"></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($grupo['nome']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $grupo['localizacao'] === 'sidebar' ? 'info' : 'primary'; ?>">
                                            <?php echo ucfirst($grupo['localizacao']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $grupo['layout'] === 'carrossel' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($grupo['layout']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo $grupo['total_anuncios']; ?> anúncio(s)
                                        </span>
                                        <?php if (!empty($grupo['marca'])): ?>
                                            <?php if ($grupo['marca'] === 'amazon'): ?>
                                                <span class="badge badge-amazon ms-1"><i class="fab fa-amazon"></i></span>
                                            <?php elseif ($grupo['marca'] === 'shopee'): ?>
                                                <span class="badge badge-shopee ms-1"><i class="fas fa-shopping-cart"></i></span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo (int)$grupo['total_posts']>0 ? 'warning' : 'secondary'; ?>">
                                            <?php echo (int)$grupo['total_posts']; ?> post(s)
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($grupo['ativo']): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($grupo['criado_em'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="editar-grupo-anuncios.php?id=<?php echo $grupo['id']; ?>" 
                                               class="btn btn-outline-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="confirmarExclusao(<?php echo $grupo['id']; ?>, '<?php echo htmlspecialchars($grupo['nome']); ?>')" 
                                                    title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                </form>
            <?php endif; ?>
        </div>
    </div>


<div class="modal fade" id="modalConfirmacao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o grupo "<span id="nomeGrupo"></span>"?</p>
                <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="grupo_id" id="grupoId">
                    <button type="submit" name="excluir_grupo" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarExclusao(grupoId, nomeGrupo) {
    document.getElementById('grupoId').value = grupoId;
    document.getElementById('nomeGrupo').textContent = nomeGrupo;
    new bootstrap.Modal(document.getElementById('modalConfirmacao')).show();
}
document.getElementById('checkAll').addEventListener('change', function(){
  document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
});
function confirmarBulk(){
  const action = document.querySelector('select[name="bulk_action"]').value;
  if(!action){ alert('Escolha uma ação em massa.'); return false; }
  const checked = document.querySelectorAll('.row-check:checked').length;
  if(checked===0){ alert('Selecione pelo menos um item.'); return false; }
  if(action==='excluir'){
    return confirm('Excluir os grupos selecionados? Anúncios não serão excluídos.');
  }
  return true;
}
</script>

<?php include 'includes/footer.php'; ?> 
<?php if (isset($totalPages) && $totalPages > 1): ?>
<nav aria-label="Paginacao" class="mt-3 d-flex justify-content-between align-items-center">
  <div class="small text-muted">
    <?php echo $fromRow; ?>–<?php echo $toRow; ?> de <?php echo $totalRows; ?>
  </div>
  <ul class="pagination mb-0">
    <?php 
      $baseQuery = $_GET; 
      $baseQuery['page'] = 1; 
      $firstUrl = 'grupos-anuncios.php?' . http_build_query($baseQuery);
      $baseQuery['page'] = max(1, $page-1);
      $prevUrl = 'grupos-anuncios.php?' . http_build_query($baseQuery);
      $baseQuery['page'] = min($totalPages, $page+1);
      $nextUrl = 'grupos-anuncios.php?' . http_build_query($baseQuery);
      $baseQuery['page'] = $totalPages;
      $lastUrl = 'grupos-anuncios.php?' . http_build_query($baseQuery);
    ?>
    <li class="page-item <?php echo $page<=1?'disabled':''; ?>"><a class="page-link" href="<?php echo $firstUrl; ?>">«</a></li>
    <li class="page-item <?php echo $page<=1?'disabled':''; ?>"><a class="page-link" href="<?php echo $prevUrl; ?>">‹</a></li>
    <li class="page-item disabled"><span class="page-link"><?php echo $page; ?>/<?php echo $totalPages; ?></span></li>
    <li class="page-item <?php echo $page>=$totalPages?'disabled':''; ?>"><a class="page-link" href="<?php echo $nextUrl; ?>">›</a></li>
    <li class="page-item <?php echo $page>=$totalPages?'disabled':''; ?>"><a class="page-link" href="<?php echo $lastUrl; ?>">»</a></li>
  </ul>
</nav>
<?php endif; ?>
