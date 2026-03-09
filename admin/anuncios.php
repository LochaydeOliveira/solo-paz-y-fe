<?php
require_once '../config/config.php';
require_once '../includes/db.php';
require_once 'includes/auth.php';

check_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $bulkAction = $_POST['bulk_action'];
    $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? array_filter($_POST['ids'], 'is_numeric') : [];

    if (empty($ids)) {
        $erro = 'Selecione pelo menos um produto para a ação em massa.';
    } else {
        try {
            if ($bulkAction === 'ativar' || $bulkAction === 'desativar') {
                $novoStatus = $bulkAction === 'ativar' ? 1 : 0;
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $pdo->prepare("UPDATE anuncios SET ativo = ? WHERE id IN ($placeholders)");
                $ok = $stmt->execute(array_merge([$novoStatus], $ids));
                if ($ok) {
                    $sucesso = $bulkAction === 'ativar' ? 'Produtos ativados com sucesso.' : 'Produtos desativados com sucesso.';
                } else {
                    $erro = 'Não foi possível aplicar a ação em massa.';
                }
            } elseif ($bulkAction === 'excluir') {
                $excluidos = 0;
                $bloqueados = 0;
                foreach ($ids as $id) {
                    $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM grupos_anuncios_items WHERE anuncio_id = ?');
                    $stmt->execute([$id]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($row && (int)$row['total'] > 0) {
                        $bloqueados++;
                        continue;
                    }
                    $stmt = $pdo->prepare('DELETE FROM anuncios WHERE id = ?');
                    if ($stmt->execute([$id])) {
                        $excluidos++;
                    }
                }
                if ($excluidos > 0) {
                    $sucesso = "$excluidos produto(s) excluído(s) com sucesso.";
                }
                if ($bloqueados > 0) {
                    $msg = "$bloqueados produto(s) não excluído(s) por estarem associados a grupos.";
                    $erro = isset($erro) ? ($erro . ' ' . $msg) : $msg;
                }
                if ($excluidos === 0 && $bloqueados === 0) {
                    $erro = 'Nenhuma exclusão foi realizada.';
                }
            }
        } catch (Exception $e) {
            $erro = 'Erro: ' . $e->getMessage();
        }
    }
}


if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM grupos_anuncios_items WHERE anuncio_id = ?");
        $stmt->execute([$id]);
        $em_grupo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($em_grupo && (int)$em_grupo['total'] > 0) {
            $erro = "Não é possível excluir este produto pois ele está associado a um grupo. Remova a associação primeiro.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM anuncios WHERE id = ?");
            $ok = $stmt->execute([$id]);
            if ($ok) {
                $sucesso = "Produto excluído com sucesso!";
            } else {
                $erro = "Erro ao excluir produto.";
            }
        }
    } catch (Exception $e) {
        $erro = "Erro: " . $e->getMessage();
    }
}

$q = trim($_GET['q'] ?? '');
$marcaFiltro = $_GET['marca'] ?? '';
$statusFiltro = $_GET['status'] ?? '';
$emGrupoFiltro = $_GET['em_grupo'] ?? '';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

try {
    $sqlBase = "SELECT a.*, 
           COUNT(gi.grupo_id) as total_grupos,
           GROUP_CONCAT(g.nome SEPARATOR ', ') as grupos_associados
    FROM anuncios a
    LEFT JOIN grupos_anuncios_items gi ON a.id = gi.anuncio_id
    LEFT JOIN grupos_anuncios g ON gi.grupo_id = g.id";

    $conditions = [];
    $params = [];

    if ($q !== '') {
        $conditions[] = '(a.titulo LIKE ? OR a.link_compra LIKE ?)';
        $params[] = '%' . $q . '%';
        $params[] = '%' . $q . '%';
    }
    if ($marcaFiltro !== '') {
        $conditions[] = 'a.marca = ?';
        $params[] = $marcaFiltro;
    }
    if ($statusFiltro === '1' || $statusFiltro === '0') {
        $conditions[] = 'a.ativo = ?';
        $params[] = (int)$statusFiltro;
    }

    $sql = $sqlBase;
    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' GROUP BY a.id';

    if ($emGrupoFiltro === 'sim') {
        $sql .= ' HAVING total_grupos > 0';
    } elseif ($emGrupoFiltro === 'nao') {
        $sql .= ' HAVING total_grupos = 0';
    }

    $sqlCount = 'SELECT COUNT(*) AS total FROM (' . $sql . ') t';
    $stmt = $pdo->prepare($sqlCount);
    $stmt->execute($params);
    $totalRows = (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    $totalPages = max(1, (int)ceil($totalRows / $perPage));
    if ($page > $totalPages) { $page = $totalPages; $offset = ($page - 1) * $perPage; }

    $fromRow = $totalRows > 0 ? ($offset + 1) : 0;
    $toRow = min($offset + $perPage, $totalRows);

    $sql .= ' ORDER BY a.criado_em DESC LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $anuncios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $anuncios = [];
    $erro = "Erro ao carregar produtos: " . $e->getMessage();
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Catálogo de Produtos</h1>
                <a href="novo-anuncio.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Produto
                </a>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <form method="get" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Buscar</label>
                            <input type="text" class="form-control" name="q" placeholder="Nome ou link" value="<?php echo htmlspecialchars($q); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Marca</label>
                            <select class="form-select" name="marca">
                                <option value="">Todas</option>
                                <option value="amazon" <?php echo $marcaFiltro==='amazon'?'selected':''; ?>>Amazon</option>
                                <option value="shopee" <?php echo $marcaFiltro==='shopee'?'selected':''; ?>>Shopee</option>
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
                            <label class="form-label">Em grupo?</label>
                            <select class="form-select" name="em_grupo">
                                <option value="">Todos</option>
                                <option value="sim" <?php echo $emGrupoFiltro==='sim'?'selected':''; ?>>Sim</option>
                                <option value="nao" <?php echo $emGrupoFiltro==='nao'?'selected':''; ?>>Não</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button class="btn btn-outline-secondary w-50" type="submit"><i class="fas fa-search"></i></button>
                            <a href="anuncios.php" class="btn btn-outline-dark w-50"><i class="fas fa-eraser"></i></a>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if (isset($erro)): ?>
                <div class="alert alert-danger"><?php echo $erro; ?></div>
            <?php endif; ?>
            
            <?php if (isset($sucesso)): ?>
                <div class="alert alert-success"><?php echo $sucesso; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Produtos Disponíveis</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($anuncios)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <h5>Nenhum produto encontrado</h5>
                            <p class="text-muted">Cadastre seu primeiro produto para começar.</p>
                            <a href="novo-anuncio.php" class="btn btn-primary">Cadastrar Primeiro Produto</a>
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
                                        <th>ID</th>
                                        <th>Produto</th>
                                        <th>Marca</th>
                                        <th>Status</th>
                                        <th>Grupos</th>
                                        <th>Criado em</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($anuncios as $anuncio): ?>
                                        <tr>
                                            <td><input type="checkbox" name="ids[]" value="<?php echo (int)$anuncio['id']; ?>" class="row-check"></td>
                                            <td>
                                                <span class="badge bg-secondary">#<?php echo $anuncio['id']; ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($anuncio['imagem'])): ?>
                                                    <img src="<?php echo htmlspecialchars($anuncio['imagem']); ?>" 
                                                         alt="<?php echo htmlspecialchars($anuncio['titulo']); ?>"
                                                             class="me-3" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($anuncio['titulo']); ?></strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <a href="<?php echo htmlspecialchars($anuncio['link_compra']); ?>" 
                                                               target="_blank" class="text-decoration-none">
                                                                <i class="fas fa-external-link-alt"></i> Ver produto
                                                            </a>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($anuncio['marca'])): ?>
                                                    <?php if ($anuncio['marca'] === 'amazon'): ?>
                                                        <span class="badge badge-amazon">
                                                            <i class="fab fa-amazon"></i> Amazon
                                                </span>
                                                    <?php elseif ($anuncio['marca'] === 'shopee'): ?>
                                                        <span class="badge badge-shopee">
                                                            <i class="fas fa-shopping-cart"></i> Shopee
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?php echo ucfirst($anuncio['marca']); ?></span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($anuncio['ativo']): ?>
                                                    <span class="badge bg-success">Ativo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inativo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($anuncio['total_grupos'] > 0): ?>
                                                    <span class="badge bg-info"><?php echo $anuncio['total_grupos']; ?> grupo(s)</span>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($anuncio['grupos_associados']); ?></small>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">Sem grupo</span>
                                                    <br>
                                                    <small class="text-muted">Não aparecerá no site</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('d/m/Y H:i', strtotime($anuncio['criado_em'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="editar-anuncio.php?id=<?php echo $anuncio['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                    <a href="anuncios.php?delete=<?php echo $anuncio['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirm('Tem certeza que deseja excluir este produto?')"
                                                       title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Como Funciona o Sistema</h5>
                </div>
                        <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center mb-3">
                                <i class="fas fa-box fa-2x text-primary mb-2"></i>
                                <h6>1. Catálogo de Produtos</h6>
                                <p class="text-muted small">Cadastre produtos com informações básicas (nome, imagem, link, marca)</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center mb-3">
                                <i class="fas fa-layer-group fa-2x text-success mb-2"></i>
                                <h6>2. Grupos de Anúncios</h6>
                                <p class="text-muted small">Crie grupos e selecione produtos do catálogo para exibir</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center mb-3">
                                <i class="fas fa-eye fa-2x text-info mb-2"></i>
                                <h6>3. Exibição no Site</h6>
                                <p class="text-muted small">Configure onde e como os produtos aparecerão no site</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                </div>
                </div>
            </div>

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
      $firstUrl = 'anuncios.php?' . http_build_query($baseQuery);
      $baseQuery['page'] = max(1, $page-1);
      $prevUrl = 'anuncios.php?' . http_build_query($baseQuery);
      $baseQuery['page'] = min($totalPages, $page+1);
      $nextUrl = 'anuncios.php?' . http_build_query($baseQuery);
      $baseQuery['page'] = $totalPages;
      $lastUrl = 'anuncios.php?' . http_build_query($baseQuery);
    ?>
    <li class="page-item <?php echo $page<=1?'disabled':''; ?>"><a class="page-link" href="<?php echo $firstUrl; ?>">«</a></li>
    <li class="page-item <?php echo $page<=1?'disabled':''; ?>"><a class="page-link" href="<?php echo $prevUrl; ?>">‹</a></li>
    <li class="page-item disabled"><span class="page-link"><?php echo $page; ?>/<?php echo $totalPages; ?></span></li>
    <li class="page-item <?php echo $page>=$totalPages?'disabled':''; ?>"><a class="page-link" href="<?php echo $nextUrl; ?>">›</a></li>
    <li class="page-item <?php echo $page>=$totalPages?'disabled':''; ?>"><a class="page-link" href="<?php echo $lastUrl; ?>">»</a></li>
  </ul>
</nav>
<?php endif; ?>

<script>
document.getElementById('checkAll').addEventListener('change', function(){
  document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
});
function confirmarBulk(){
  const action = document.querySelector('select[name="bulk_action"]').value;
  if(!action){ alert('Escolha uma ação em massa.'); return false; }
  const checked = document.querySelectorAll('.row-check:checked').length;
  if(checked===0){ alert('Selecione pelo menos um item.'); return false; }
  if(action==='excluir'){
    return confirm('Excluir os itens selecionados? Itens em grupos não serão excluídos.');
  }
  return true;
}
</script>
