<?php
ob_start();
session_start();

require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/GruposAnunciosManager.php';
require_once '../includes/AnunciosManager.php';

$page_title = 'Editar Grupo de Anúncios';

// Verificar login - usar a mesma verificação do header
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$gruposManager = new GruposAnunciosManager($pdo);
$anunciosManager = new AnunciosManager($pdo);

// Verificar se foi passado um ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: grupos-anuncios.php');
    exit;
}

$grupoId = (int)$_GET['id'];

// Buscar dados do grupo
$grupo = $gruposManager->getGrupo($grupoId);
if (!$grupo) {
    header('Location: grupos-anuncios.php');
    exit;
}

// Buscar anúncios do grupo
$anunciosDoGrupo = $gruposManager->getAnunciosDoGrupo($grupoId);
$anunciosIds = array_column($anunciosDoGrupo, 'anuncio_id');

// Buscar posts do grupo
$postsDoGrupo = $gruposManager->getPostsDoGrupo($grupoId);
$postsIds = array_column($postsDoGrupo, 'id');

// Buscar todos os posts disponíveis
$todosPosts = $gruposManager->getAllPosts();

// Buscar todos os anúncios para seleção
$todosAnuncios = $anunciosManager->getAllAnunciosComStats();

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $localizacao = $_POST['localizacao'];
    $layout = $_POST['layout'] ?? 'carrossel';
    $anuncios = $_POST['anuncios'] ?? [];
    $posts = $_POST['posts'] ?? [];
    
    // Regras novas: sempre por posts específicos (ambas localizações)
    if (empty($nome)) {
        $erro = 'Nome do grupo é obrigatório.';
    } elseif (empty($anuncios)) {
        $erro = 'Selecione pelo menos um anúncio.';
    } elseif (empty($posts)) {
        $erro = 'Selecione pelo menos um post.';
    } else {
        $dados = [
            'nome' => $nome,
            'localizacao' => $localizacao,
            'layout' => $layout,
            'anuncios' => $anuncios,
            'posts' => $posts,
            'ativo' => isset($_POST['ativo'])
        ];
        
        if ($gruposManager->atualizarGrupo($grupoId, $dados)) {
            // Atualizar posts (sempre específicos)
            if ($gruposManager->atualizarConfiguracoesPosts($grupoId, 1, 0, $posts)) {
                $sucesso = 'Grupo atualizado com sucesso!';
                $grupo = $gruposManager->getGrupo($grupoId);
                $anunciosDoGrupo = $gruposManager->getAnunciosDoGrupo($grupoId);
                $anunciosIds = array_column($anunciosDoGrupo, 'anuncio_id');
                $postsDoGrupo = $gruposManager->getPostsDoGrupo($grupoId);
                $postsIds = array_column($postsDoGrupo, 'id');
            } else {
                $erro = 'Erro ao atualizar configurações de posts.';
            }
        } else {
            $erro = 'Erro ao atualizar grupo.';
        }
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Editar Grupo de Anúncios</h1>
    <a href="grupos-anuncios.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

    <?php if (isset($erro)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $erro; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($sucesso)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $sucesso; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informações do Grupo</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nome" class="form-label">Nome do Grupo *</label>
                                    <input type="text" class="form-control" id="nome" name="nome" 
                                           value="<?php echo htmlspecialchars($grupo['nome']); ?>" required>
                                    <div class="form-text">Ex: "Anúncios da Página Inicial", "Promoções Especiais"</div>
                                </div>
                                <div id="postsAlert" class="alert alert-danger mt-2" style="display:none;">
                                    Para grupos na Sidebar, selecione pelo menos um post.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="localizacao" class="form-label">Localização *</label>
                                    <select class="form-select" id="localizacao" name="localizacao" required>
                                        <option value="">Selecione...</option>
                                        <option value="sidebar" <?php echo $grupo['localizacao'] === 'sidebar' ? 'selected' : ''; ?>>Sidebar</option>
                                        <option value="conteudo" <?php echo $grupo['localizacao'] === 'conteudo' ? 'selected' : ''; ?>>Conteúdo Principal</option>
                                    </select>
                                    <div class="form-text">Onde o grupo será exibido no site</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="layout" class="form-label">Layout *</label>
                                    <select class="form-select" id="layout" name="layout" required>
                                        <option value="carrossel" <?php echo $grupo['layout'] === 'carrossel' ? 'selected' : ''; ?>>Carrossel</option>
                                        <option value="grade" <?php echo $grupo['layout'] === 'grade' ? 'selected' : ''; ?>>Grade</option>
                                    </select>
                                    <div class="form-text">Se "Sidebar" for selecionado, o layout será automaticamente "Grade" e ficará desabilitado.</div>
                                </div>
                            </div>
                            
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="ativo" name="ativo" 
                                               <?php echo $grupo['ativo'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="ativo">
                                            Grupo ativo
                                        </label>
                                    </div>
                                    <div class="form-text">Grupos inativos não são exibidos no site</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Selecionar Anúncios *</label>
                            <div class="form-text mb-2">Selecione os anúncios que farão parte deste grupo</div>
                            <?php if (empty($todosAnuncios)): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Nenhum anúncio encontrado.
                                    <a href="novo-anuncio.php" class="alert-link">Crie um anúncio primeiro</a>.
                                </div>
                            <?php else: ?>
                                <div class="row g-2 align-items-end mb-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Buscar</label>
                                        <input type="text" class="form-control" id="f_anuncio_q" placeholder="Nome ou link" oninput="filtrarLinhas('anuncio')">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Marca</label>
                                        <select class="form-select" id="f_anuncio_marca" onchange="filtrarLinhas('anuncio')">
                                            <option value="">Todas</option>
                                            <option value="amazon">Amazon</option>
                                            <option value="shopee">Shopee</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" id="f_anuncio_status" onchange="filtrarLinhas('anuncio')">
                                            <option value="">Todos</option>
                                            <option value="1">Ativo</option>
                                            <option value="0">Inativo</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="selecionarTodos('anuncio')">Selecionar todos</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="limparSelecao('anuncio')">Limpar</button>
                                    </div>
                                </div>
                                <div class="table-responsive" style="max-height: 420px; overflow: auto; border: 1px solid #ddd; border-radius: 4px;">
                                    <table class="table table-sm align-middle mb-0" id="tbl_anuncios">
                                        <thead>
                                            <tr>
                                                <th style="width:32px"><input type="checkbox" id="checkAllAnuncios" onclick="toggleAll('anuncio', this)"></th>
                                                <th style="width:52px"></th>
                                                <th>Produto</th>
                                                <th>Marca</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($todosAnuncios as $anuncio): ?>
                                            <tr data-kind="anuncio" data-id="<?php echo (int)$anuncio['id']; ?>" data-title="<?php echo strtolower(htmlspecialchars($anuncio['titulo'])); ?>" data-marca="<?php echo htmlspecialchars($anuncio['marca'] ?? ''); ?>" data-status="<?php echo (int)($anuncio['ativo'] ?? 1); ?>">
                                                <td><input type="checkbox" class="row-check-anuncio" name="anuncios[]" value="<?php echo $anuncio['id']; ?>" <?php echo in_array($anuncio['id'], $anunciosIds) ? 'checked' : ''; ?>></td>
                                                <td>
                                                    <?php if (!empty($anuncio['imagem'])): ?>
                                                        <img src="<?php echo htmlspecialchars($anuncio['imagem']); ?>" alt="" style="width:42px;height:42px;object-fit:cover;border-radius:4px;">
                                                    <?php endif; ?>
                                                </td>
                                                <td><strong><?php echo htmlspecialchars($anuncio['titulo']); ?></strong></td>
                                                <td>
                                                    <?php if (!empty($anuncio['marca'])): ?>
                                                        <?php if ($anuncio['marca'] === 'amazon'): ?>
                                                            <span class="badge badge-amazon"><i class="fab fa-amazon"></i> Amazon</span>
                                                        <?php elseif ($anuncio['marca'] === 'shopee'): ?>
                                                            <span class="badge badge-shopee"><i class="fas fa-shopping-cart"></i> Shopee</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary"><?php echo htmlspecialchars(ucfirst($anuncio['marca'])); ?></span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo (int)($anuncio['ativo'] ?? 1) ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-secondary">Inativo</span>'; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="small text-muted" id="anuncioPageInfo"></div>
                                    <div class="d-flex align-items-center gap-2">
                                        <select id="anuncioPageSize" class="form-select form-select-sm" style="width:auto" onchange="changePageSize('anuncio', this.value)">
                                            <option value="10">10</option>
                                            <option value="20" selected>20</option>
                                            <option value="50">50</option>
                                        </select>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-secondary" id="anuncioPrevBtn" onclick="changePage('anuncio', -1)">‹</button>
                                            <button type="button" class="btn btn-outline-secondary" id="anuncioNextBtn" onclick="changePage('anuncio', 1)">›</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Seleção de Posts (obrigatório) -->
                        <div class="mb-3">
                            <label class="form-label">Selecionar Posts *</label>
                            <div class="form-text mb-2">Selecione os posts onde este grupo será exibido</div>
                            <?php if (empty($todosPosts)): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Nenhum post encontrado.
                                </div>
                            <?php else: ?>
                                <div class="row g-2 align-items-end mb-2">
                                    <div class="col-8">
                                        <label class="form-label">Buscar</label>
                                        <input type="text" class="form-control" id="f_post_q" placeholder="Título do post" oninput="filtrarLinhas('post')">
                                    </div>
                                    <div class="col-4 d-flex gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="selecionarTodos('post')">Selecionar todos</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="limparSelecao('post')">Limpar</button>
                                    </div>
                                </div>
                                <div class="table-responsive" style="max-height: 360px; overflow: auto; border: 1px solid #ddd; border-radius: 4px;">
                                    <table class="table table-sm align-middle mb-0" id="tbl_posts">
                                        <thead>
                                            <tr>
                                                <th style="width:32px"><input type="checkbox" id="checkAllPosts" onclick="toggleAll('post', this)"></th>
                                                <th>Título</th>
                                                <th>Data</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($todosPosts as $post): ?>
                                            <tr data-kind="post" data-id="<?php echo (int)$post['id']; ?>" data-title="<?php echo strtolower(htmlspecialchars($post['titulo'])); ?>">
                                                <td><input type="checkbox" class="row-check-post" name="posts[]" value="<?php echo $post['id']; ?>" <?php echo in_array($post['id'], $postsIds) ? 'checked' : ''; ?>></td>
                                                <td><?php echo htmlspecialchars($post['titulo']); ?></td>
                                                <td><small class="text-muted"><?php echo !empty($post['data_publicacao']) ? date('d/m/Y', strtotime($post['data_publicacao'])) : ''; ?></small></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="small text-muted" id="postPageInfo"></div>
                                    <div class="d-flex align-items-center gap-2">
                                        <select id="postPageSize" class="form-select form-select-sm" style="width:auto" onchange="changePageSize('post', this.value)">
                                            <option value="10">10</option>
                                            <option value="20" selected>20</option>
                                            <option value="50">50</option>
                                        </select>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-secondary" id="postPrevBtn" onclick="changePage('post', -1)">‹</button>
                                            <button type="button" class="btn btn-outline-secondary" id="postNextBtn" onclick="changePage('post', 1)">›</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="grupos-anuncios.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Atualizar Grupo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informações do Grupo</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>ID:</strong> <?php echo $grupo['id']; ?>
                    </div>
                    <div class="mb-3">
                        <strong>Criado em:</strong><br>
                        <?php echo date('d/m/Y H:i', strtotime($grupo['criado_em'])); ?>
                    </div>
                    <?php if ($grupo['atualizado_em']): ?>
                        <div class="mb-3">
                            <strong>Última atualização:</strong><br>
                            <?php echo date('d/m/Y H:i', strtotime($grupo['atualizado_em'])); ?>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <strong>Anúncios no grupo:</strong><br>
                        <span class="badge bg-primary"><?php echo count($anunciosIds); ?> anúncio(s)</span>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Anúncios Selecionados</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($anunciosDoGrupo)): ?>
                        <p class="text-muted">Nenhum anúncio selecionado</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($anunciosDoGrupo as $anuncio): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($anuncio['titulo']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo ucfirst($anuncio['localizacao']); ?></small>
                                    </div>
                                    <span class="badge bg-secondary"><?php echo $anuncio['total_cliques'] ?? 0; ?> cliques</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const localizacaoSelect = document.getElementById('localizacao');
    const layoutSelect = document.getElementById('layout');
    function aplicarRegraSidebar() {
        const isSidebar = localizacaoSelect.value === 'sidebar';
        if (isSidebar) {
            layoutSelect.value = 'grade';
            layoutSelect.disabled = true;
        } else {
            layoutSelect.disabled = false;
        }
    }
    localizacaoSelect.addEventListener('change', aplicarRegraSidebar);
    aplicarRegraSidebar();
});
</script>

<script>
// Paginação e filtros client-side (reuso do novo-grupo)
let paginacao = {
  anuncio: { page: 1, size: 20 },
  post: { page: 1, size: 20 }
};
function applyPagination(tipo){
  const state = paginacao[tipo];
  const rows = Array.from(document.querySelectorAll(`#tbl_${tipo==='anuncio'?'anuncios':'posts'} tbody tr`))
    .filter(tr => tr.dataset.filtered !== '0');
  const total = rows.length;
  const start = (state.page - 1) * state.size;
  const end = start + state.size;
  let shown = 0;
  rows.forEach((tr, idx) => {
    const show = idx >= start && idx < end;
    tr.style.display = show ? '' : 'none';
    if (show) shown++;
  });
  const infoId = tipo==='anuncio' ? 'anuncioPageInfo' : 'postPageInfo';
  const prevBtn = document.getElementById(tipo==='anuncio' ? 'anuncioPrevBtn' : 'postPrevBtn');
  const nextBtn = document.getElementById(tipo==='anuncio' ? 'anuncioNextBtn' : 'postNextBtn');
  const from = total ? (start + 1) : 0;
  const to = Math.min(end, total);
  const totalPages = Math.max(1, Math.ceil(total / state.size));
  document.getElementById(infoId).textContent = `${from}–${to} de ${total} (pág. ${state.page}/${totalPages})`;
  prevBtn.disabled = state.page <= 1;
  nextBtn.disabled = state.page >= totalPages;
  // Logs de debug
  const ids = rows.slice(start, end).map(tr => tr.getAttribute('data-id'));
  console.log(`[${tipo}] applyPagination total=${total} page=${state.page} size=${state.size} range=${from}-${to} ids=`, ids);
}
function changePageSize(tipo, size){
  paginacao[tipo].size = parseInt(size, 10) || 20;
  paginacao[tipo].page = 1;
  applyPagination(tipo);
}
function changePage(tipo, delta){
  const tableId = `#tbl_${tipo==='anuncio'?'anuncios':'posts'}`;
  const rows = Array.from(document.querySelectorAll(`${tableId} tbody tr`)).filter(tr => tr.dataset.filtered !== '0');
  const total = rows.length;
  const totalPages = Math.max(1, Math.ceil(total / (paginacao[tipo].size || 20)));
  let newPage = (paginacao[tipo].page || 1) + delta;
  if (newPage < 1) newPage = 1;
  if (newPage > totalPages) newPage = totalPages;
  paginacao[tipo].page = newPage;
  console.log(`[${tipo}] changePage -> newPage=${newPage}, totalPages=${totalPages}, total=${total}`);
  applyPagination(tipo);
}
function filtrarLinhas(tipo){
  if(tipo==='anuncio'){
    const q = (document.getElementById('f_anuncio_q')?.value||'').toLowerCase();
    const marca = document.getElementById('f_anuncio_marca')?.value||'';
    const status = document.getElementById('f_anuncio_status')?.value||'';
    document.querySelectorAll('#tbl_anuncios tbody tr').forEach(tr=>{
      const t = tr.getAttribute('data-title');
      const m = tr.getAttribute('data-marca');
      const s = tr.getAttribute('data-status');
      let show = true;
      if(q && !t.includes(q)) show=false;
      if(marca && m !== marca) show=false;
      if(status && s !== status) show=false;
      tr.dataset.filtered = show ? '1' : '0';
    });
  } else if (tipo==='post'){
    const q = (document.getElementById('f_post_q')?.value||'').toLowerCase();
    document.querySelectorAll('#tbl_posts tbody tr').forEach(tr=>{
      const t = tr.getAttribute('data-title');
      tr.dataset.filtered = (q && !t.includes(q)) ? '0' : '1';
    });
  }
  // Reset para primeira página após filtro
  paginacao[tipo].page = 1;
  applyPagination(tipo);
}
function selecionarTodos(tipo){
  const selector = tipo==='anuncio' ? '.row-check-anuncio' : '.row-check-post';
  document.querySelectorAll(selector).forEach(cb=>{ if(cb.closest('tr').style.display !== 'none'){ cb.checked = true; } });
}
function limparSelecao(tipo){
  const selector = tipo==='anuncio' ? '.row-check-anuncio' : '.row-check-post';
  document.querySelectorAll(selector).forEach(cb=> cb.checked = false);
}
function toggleAll(tipo, el){
  const selector = tipo==='anuncio' ? '.row-check-anuncio' : '.row-check-post';
  document.querySelectorAll(selector).forEach(cb=>{ if(cb.closest('tr').style.display !== 'none'){ cb.checked = el.checked; } });
}

document.addEventListener('DOMContentLoaded', function(){
  // Inicializa flag de filtro para todas as linhas
  document.querySelectorAll('#tbl_anuncios tbody tr').forEach(tr=>{ tr.dataset.filtered = '1'; });
  document.querySelectorAll('#tbl_posts tbody tr').forEach(tr=>{ tr.dataset.filtered = '1'; });
  applyPagination('anuncio');
  applyPagination('post');
  // Bloquear submit se sidebar sem posts selecionados
  const form = document.querySelector('form');
  form.addEventListener('submit', function(e){
    const loc = document.getElementById('localizacao').value;
    if (loc === 'sidebar') {
      const anyChecked = document.querySelectorAll('#tbl_posts tbody input.row-check-post:checked').length > 0;
      if (!anyChecked) {
        e.preventDefault();
        const alertEl = document.getElementById('postsAlert');
        if (alertEl) { alertEl.style.display = ''; alertEl.scrollIntoView({behavior:'smooth', block:'center'}); }
      }
    }
  });
});
</script>

<?php include 'includes/footer.php'; ?> 