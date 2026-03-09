<?php
ob_start();
session_start();

require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/GruposAnunciosManager.php';
require_once '../includes/AnunciosManager.php';
require_once 'includes/auth.php';

// Verificar se o usuário está logado
check_login();

// Conexão via $pdo (definido em ../includes/db.php)

$page_title = 'Novo Grupo de Anúncios';

// Buscar anúncios disponíveis (para listagem com filtros)
try {
    $stmt = $pdo->prepare("SELECT id, titulo, marca, ativo, imagem FROM anuncios ORDER BY titulo ASC");
    $stmt->execute();
    $anuncios_disponiveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $anuncios_disponiveis = [];
}

// Buscar posts para seleção (para listagem com filtros)
try {
    $stmt = $pdo->prepare("SELECT id, titulo, slug, data_publicacao FROM posts WHERE publicado = 1 ORDER BY data_publicacao DESC");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $posts = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $localizacao = $_POST['localizacao'];
    $layout = $_POST['layout'] ?? 'carrossel';
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $anuncios_selecionados = $_POST['anuncios'] ?? [];
    $posts_selecionados = $_POST['posts'] ?? [];

    // Validações obrigatórias no novo modelo
    if (empty($nome)) {
        $erro = "Nome do grupo é obrigatório.";
    } elseif (empty($anuncios_selecionados)) {
        $erro = "Selecione pelo menos um anúncio para o grupo.";
    } elseif (empty($posts_selecionados)) {
        $erro = "Selecione pelo menos um post para exibir este grupo.";
    } else {
        try {
            $pdo->beginTransaction();

            // Ajustes de regra: sidebar força layout 'grade'; posts_especificos=1; aparecer_inicio=0; marca vazia
            if ($localizacao === 'sidebar') {
                $layout = 'grade';
            }

            $sql_grupo = "INSERT INTO grupos_anuncios (nome, localizacao, layout, marca, ativo, posts_especificos, aparecer_inicio, criado_em) 
                          VALUES (?, ?, ?, '', ?, 1, 0, NOW())";
            $stmt = $pdo->prepare($sql_grupo);
            $ok = $stmt->execute([$nome, $localizacao, $layout, $ativo]);

            if ($ok) {
                $grupo_id = (int)$pdo->lastInsertId();

                // Associar anúncios ao grupo (ordem pela posição no seletor)
                $stmtItem = $pdo->prepare("INSERT INTO grupos_anuncios_items (grupo_id, anuncio_id, ordem) VALUES (?, ?, ?)");
                foreach ($anuncios_selecionados as $ordem => $anuncio_id) {
                    $stmtItem->execute([$grupo_id, $anuncio_id, $ordem]);
                }

                // Associar posts obrigatórios
                $stmtPost = $pdo->prepare("INSERT INTO grupos_anuncios_posts (grupo_id, post_id) VALUES (?, ?)");
                foreach ($posts_selecionados as $post_id) {
                    $stmtPost->execute([$grupo_id, $post_id]);
                }

                $pdo->commit();
                $sucesso = "Grupo criado com sucesso!";
                $_POST = array();
            } else {
                $pdo->rollBack();
                $erro = "Erro ao criar grupo.";
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $erro = "Erro: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="h3 mb-4">Criar Novo Grupo de Anúncios</h1>
            
            <?php if (isset($erro)): ?>
                <div class="alert alert-danger"><?php echo $erro; ?></div>
            <?php endif; ?>
            
            <?php if (isset($sucesso)): ?>
                <div class="alert alert-success"><?php echo $sucesso; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="row">
                    <!-- Informações Básicas -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Informações do Grupo</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="nome" class="form-label">Nome do Grupo *</label>
                                    <input type="text" class="form-control" id="nome" name="nome" 
                                           value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>" 
                                           required>
                                    <div class="form-text">Nome para identificar este grupo</div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="localizacao" class="form-label">Localização *</label>
                                            <select class="form-select" id="localizacao" name="localizacao" required>
                                                <option value="">Selecione...</option>
                                                <option value="sidebar" <?php echo (isset($_POST['localizacao']) && $_POST['localizacao'] === 'sidebar') ? 'selected' : ''; ?>>Sidebar</option>
                                                <option value="conteudo" <?php echo (isset($_POST['localizacao']) && $_POST['localizacao'] === 'conteudo') ? 'selected' : ''; ?>>Conteúdo Principal</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="layout" class="form-label">Layout</label>
                                            <select class="form-select" id="layout" name="layout">
                                                <option value="carrossel" <?php echo (isset($_POST['layout']) && $_POST['layout'] === 'carrossel') ? 'selected' : ''; ?>>Carrossel</option>
                                                <option value="grade" <?php echo (isset($_POST['layout']) && $_POST['layout'] === 'grade') ? 'selected' : ''; ?>>Grade</option>
                                            </select>
                                            <div class="form-text">Para Sidebar o layout será automaticamente "Grade" empilhado.</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="ativo" name="ativo" 
                                               <?php echo (isset($_POST['ativo']) && $_POST['ativo']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="ativo">
                                            Grupo Ativo
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Seleção de Posts (sempre obrigatória) -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Posts Específicos *</h5>
                            </div>
                            <div class="card-body">
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
                                            <?php $selPosts = $_POST['posts'] ?? []; foreach ($posts as $post): ?>
                                            <tr data-kind="post" data-title="<?php echo strtolower(htmlspecialchars($post['titulo'])); ?>">
                                                <td><input type="checkbox" class="row-check-post" name="posts[]" value="<?php echo $post['id']; ?>" <?php echo in_array($post['id'], $selPosts) ? 'checked' : ''; ?>></td>
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
                                <div id="postsAlert" class="alert alert-danger mt-2" style="display:none;">
                                    Para grupos na Sidebar, selecione pelo menos um post.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Seleção de Anúncios -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Selecionar Anúncios *</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($anuncios_disponiveis)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Nenhum anúncio disponível!</strong>
                                <br>
                                Crie anúncios primeiro em <a href="anuncios.php" class="alert-link">Anúncios</a> antes de criar um grupo.
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
                                            <th style=\"width:32px\"><input type=\"checkbox\" id=\"checkAllAnuncios\" onclick=\"toggleAll('anuncio', this)\"></th>
                                            <th style=\"width:52px\"></th>
                                            <th>Produto</th>
                                            <th>Marca</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $selAnuncios = $_POST['anuncios'] ?? []; foreach ($anuncios_disponiveis as $anuncio): ?>
                                        <tr data-kind=\"anuncio\" data-title=\"<?php echo strtolower(htmlspecialchars($anuncio['titulo'])); ?>\" data-marca=\"<?php echo htmlspecialchars($anuncio['marca'] ?? ''); ?>\" data-status=\"<?php echo (int)$anuncio['ativo']; ?>\">
                                            <td><input type=\"checkbox\" class=\"row-check-anuncio\" name=\"anuncios[]\" value=\"<?php echo $anuncio['id']; ?>\" <?php echo in_array($anuncio['id'], $selAnuncios) ? 'checked' : ''; ?>></td>
                                            <td>
                                                <?php if (!empty($anuncio['imagem'])): ?>
                                                    <img src=\"<?php echo htmlspecialchars($anuncio['imagem']); ?>\" alt=\"\" style=\"width:42px;height:42px;object-fit:cover;border-radius:4px;\">
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
                                            <td><?php echo (int)$anuncio['ativo'] ? '<span class=\"badge bg-success\">Ativo</span>' : '<span class=\"badge bg-secondary\">Inativo</span>'; ?></td>
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
                </div>
                
                <!-- Seleções persistentes -->
                <div id="hiddenAnuncios"></div>
                <div id="hiddenPosts"></div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="grupos-anuncios.php" class="btn btn-secondary">← Voltar</a>
                    <button type="submit" id="submitBtn" class="btn btn-primary" <?php echo empty($anuncios_disponiveis) ? 'disabled' : ''; ?>>
                        Criar Grupo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const localizacaoSelect = document.getElementById('localizacao');
    const layoutSelect = document.getElementById('layout');
    
    // Função para atualizar configuração baseada na localização
    function aplicarRegraSidebar() {
        const isSidebar = localizacaoSelect.value === 'sidebar';
        layoutSelect.value = isSidebar ? 'grade' : layoutSelect.value;
        layoutSelect.disabled = isSidebar;
    }

    localizacaoSelect.addEventListener('change', aplicarRegraSidebar);
    aplicarRegraSidebar();
    initServerLists();
});
</script>
<script>
// ---- Server-side lists (AJAX) ----
const selectedAnuncios = new Set(<?php echo json_encode(array_map('intval', $_POST['anuncios'] ?? [])); ?>);
const selectedPosts = new Set(<?php echo json_encode(array_map('intval', $_POST['posts'] ?? [])); ?>);

function syncHiddenInputs() {
  const hA = document.getElementById('hiddenAnuncios');
  const hP = document.getElementById('hiddenPosts');
  hA.innerHTML = '';
  hP.innerHTML = '';
  selectedAnuncios.forEach(id => {
    const i = document.createElement('input');
    i.type = 'hidden'; i.name = 'anuncios[]'; i.value = id;
    hA.appendChild(i);
  });
  selectedPosts.forEach(id => {
    const i = document.createElement('input');
    i.type = 'hidden'; i.name = 'posts[]'; i.value = id;
    hP.appendChild(i);
  });
}

const estado = { anuncios: { page: 1, perPage: 20, total: 0 }, posts: { page: 1, perPage: 20, total: 0 } };

function initServerLists(){
  document.getElementById('anuncioPageSize').addEventListener('change', (e)=>{ estado.anuncios.perPage = parseInt(e.target.value)||20; estado.anuncios.page=1; loadAnuncios(); });
  document.getElementById('postPageSize').addEventListener('change', (e)=>{ estado.posts.perPage = parseInt(e.target.value)||20; estado.posts.page=1; loadPosts(); });
  document.getElementById('anuncioPrevBtn').addEventListener('click', ()=>{ if(estado.anuncios.page>1){ estado.anuncios.page--; loadAnuncios(); }});
  document.getElementById('anuncioNextBtn').addEventListener('click', ()=>{ const tp=Math.max(1, Math.ceil(estado.anuncios.total/estado.anuncios.perPage)); if(estado.anuncios.page<tp){ estado.anuncios.page++; loadAnuncios(); }});
  document.getElementById('postPrevBtn').addEventListener('click', ()=>{ if(estado.posts.page>1){ estado.posts.page--; loadPosts(); }});
  document.getElementById('postNextBtn').addEventListener('click', ()=>{ const tp=Math.max(1, Math.ceil(estado.posts.total/estado.posts.perPage)); if(estado.posts.page<tp){ estado.posts.page++; loadPosts(); }});
  document.getElementById('f_anuncio_q')?.addEventListener('input', debounce(()=>{ estado.anuncios.page=1; loadAnuncios(); },300));
  document.getElementById('f_anuncio_marca')?.addEventListener('change', ()=>{ estado.anuncios.page=1; loadAnuncios(); });
  document.getElementById('f_anuncio_status')?.addEventListener('change', ()=>{ estado.anuncios.page=1; loadAnuncios(); });
  document.getElementById('f_post_q')?.addEventListener('input', debounce(()=>{ estado.posts.page=1; loadPosts(); },300));
  loadAnuncios();
  loadPosts();
  syncHiddenInputs();

  // Impedir envio se sidebar sem posts
  const form = document.querySelector('form');
  form.addEventListener('submit', function(e){
    const loc = document.getElementById('localizacao').value;
    if (loc === 'sidebar' && selectedPosts.size === 0) {
      e.preventDefault();
      const alertEl = document.getElementById('postsAlert');
      alertEl.style.display = '';
      alertEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
}

function loadAnuncios(){
  const q = encodeURIComponent(document.getElementById('f_anuncio_q')?.value||'');
  const marca = encodeURIComponent(document.getElementById('f_anuncio_marca')?.value||'');
  const status = encodeURIComponent(document.getElementById('f_anuncio_status')?.value||'');
  fetch(`api/anuncios-list.php?q=${q}&marca=${marca}&status=${status}&page=${estado.anuncios.page}&per_page=${estado.anuncios.perPage}`)
    .then(r=>r.json()).then(json=>{
      estado.anuncios.total = json.total||0;
      renderAnuncios(json.data||[]);
      updatePager('anuncio', estado.anuncios.page, estado.anuncios.perPage, estado.anuncios.total);
    }).catch(()=>{});
}
function loadPosts(){
  const q = encodeURIComponent(document.getElementById('f_post_q')?.value||'');
  fetch(`api/posts-list.php?q=${q}&page=${estado.posts.page}&per_page=${estado.posts.perPage}`)
    .then(r=>r.json()).then(json=>{
      estado.posts.total = json.total||0;
      renderPosts(json.data||[]);
      updatePager('post', estado.posts.page, estado.posts.perPage, estado.posts.total);
    }).catch(()=>{});
}
function renderAnuncios(rows){
  const tbody = document.querySelector('#tbl_anuncios tbody');
  if (!tbody) return;
  tbody.innerHTML='';
  const chkAll = document.getElementById('checkAllAnuncios');
  if (chkAll) chkAll.checked = false;
  rows.forEach(row=>{
    const tr = document.createElement('tr');
    tr.setAttribute('data-kind','anuncio');
    tr.setAttribute('data-title', (row.titulo||'').toLowerCase());
    tr.setAttribute('data-marca', row.marca||'');
    tr.setAttribute('data-status', String(row.ativo?1:0));
    tr.innerHTML = `
      <td><input type="checkbox" class="row-check-anuncio"></td>
      <td>${row.imagem ? `<img src="${escapeHtml(row.imagem)}" style="width:42px;height:42px;object-fit:cover;border-radius:4px;">` : ''}</td>
      <td><strong>${escapeHtml(row.titulo||'')}</strong></td>
      <td>${row.marca ? escapeHtml(capFirst(row.marca)) : '<span class="text-muted">-</span>'}</td>
      <td>${row.ativo?'<span class="badge bg-success">Ativo</span>':'<span class="badge bg-secondary">Inativo</span>'}</td>
    `;
    const cb = tr.querySelector('input[type="checkbox"]');
    cb.checked = selectedAnuncios.has(Number(row.id));
    cb.addEventListener('change', ()=>{ toggleSelection('anuncio', Number(row.id), cb.checked); });
    tbody.appendChild(tr);
  });
}
function renderPosts(rows){
  const tbody = document.querySelector('#tbl_posts tbody');
  if (!tbody) return;
  tbody.innerHTML='';
  const chkAll = document.getElementById('checkAllPosts');
  if (chkAll) chkAll.checked = false;
  rows.forEach(row=>{
    const tr = document.createElement('tr');
    tr.setAttribute('data-kind','post');
    tr.setAttribute('data-title', (row.titulo||'').toLowerCase());
    tr.innerHTML = `
      <td><input type="checkbox" class="row-check-post"></td>
      <td>${escapeHtml(row.titulo||'')}</td>
      <td><small class="text-muted">${row.data_publicacao? formatDate(row.data_publicacao):''}</small></td>
    `;
    const cb = tr.querySelector('input[type="checkbox"]');
    cb.checked = selectedPosts.has(Number(row.id));
    cb.addEventListener('change', ()=>{ toggleSelection('post', Number(row.id), cb.checked); });
    tbody.appendChild(tr);
  });
}
function toggleSelection(tipo, id, checked){
  if (tipo==='anuncio') {
    if(checked) selectedAnuncios.add(id); else selectedAnuncios.delete(id);
  } else {
    if(checked) selectedPosts.add(id); else selectedPosts.delete(id);
  }
  syncHiddenInputs();
}
function updatePager(tipo, page, perPage, total){
  const infoId = tipo==='anuncio' ? 'anuncioPageInfo' : 'postPageInfo';
  const prevBtn = document.getElementById(tipo==='anuncio' ? 'anuncioPrevBtn' : 'postPrevBtn');
  const nextBtn = document.getElementById(tipo==='anuncio' ? 'anuncioNextBtn' : 'postNextBtn');
  const from = total ? ((page-1)*perPage + 1) : 0;
  const to = Math.min(page*perPage, total);
  const totalPages = Math.max(1, Math.ceil(total / perPage));
  document.getElementById(infoId).textContent = `${from}–${to} de ${total} (pág. ${page}/${totalPages})`;
  prevBtn.disabled = page <= 1;
  nextBtn.disabled = page >= totalPages;
}
function debounce(fn, ms){ let t; return function(){ clearTimeout(t); t = setTimeout(()=>fn.apply(this, arguments), ms); } }
function escapeHtml(s){ return String(s).replace(/[&<>"']/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[c])); }
function capFirst(s){ s=String(s); return s? s.charAt(0).toUpperCase()+s.slice(1): s; }
function formatDate(iso){ const d=new Date(iso.replace(' ','T')); const dd=('0'+d.getDate()).slice(-2); const mm=('0'+(d.getMonth()+1)).slice(-2); const yyyy=d.getFullYear(); return `${dd}/${mm}/${yyyy}`; }
</script>
<script>
function filtrarLinhas(tipo){
  if(tipo==='anuncio'){
    const q = (document.getElementById('f_anuncio_q').value||'').toLowerCase();
    const marca = document.getElementById('f_anuncio_marca').value;
    const status = document.getElementById('f_anuncio_status').value;
    document.querySelectorAll('#tbl_anuncios tbody tr').forEach(tr=>{
      const t = tr.getAttribute('data-title');
      const m = tr.getAttribute('data-marca');
      const s = tr.getAttribute('data-status');
      let show = true;
      if(q && !t.includes(q)) show=false;
      if(marca && m !== marca) show=false;
      if(status && s !== status) show=false;
      tr.style.display = show ? '' : 'none';
    });
  } else if (tipo==='post'){
    const q = (document.getElementById('f_post_q').value||'').toLowerCase();
    document.querySelectorAll('#tbl_posts tbody tr').forEach(tr=>{
      const t = tr.getAttribute('data-title');
      tr.style.display = (q && !t.includes(q)) ? 'none' : '';
    });
  }
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

// Paginação client-side para tabelas de seleção
let paginacao = {
  anuncio: { page: 1, size: 20 },
  post: { page: 1, size: 20 }
};

function applyPagination(tipo){
  const state = paginacao[tipo];
  const rows = Array.from(document.querySelectorAll(`#tbl_${tipo==='anuncio'?'anuncios':'posts'} tbody tr`))
    .filter(tr => tr.style.display !== 'none');
  const total = rows.length;
  const start = (state.page - 1) * state.size;
  const end = start + state.size;
  rows.forEach((tr, idx) => {
    tr.style.visibility = (idx >= start && idx < end) ? '' : 'hidden';
    tr.style.display = (idx >= start && idx < end) ? '' : 'none';
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
}

function changePageSize(tipo, size){
  paginacao[tipo].size = parseInt(size, 10) || 20;
  paginacao[tipo].page = 1;
  applyPagination(tipo);
}
function changePage(tipo, delta){
  paginacao[tipo].page += delta;
  if (paginacao[tipo].page < 1) paginacao[tipo].page = 1;
  applyPagination(tipo);
}

// Reaplicar paginação após filtro
['f_anuncio_q','f_anuncio_marca','f_anuncio_status','f_post_q'].forEach(id => {
  const el = document.getElementById(id);
  if (el) {
    el.addEventListener('input', ()=>{ paginacao.anuncio.page = 1; paginacao.post.page = 1; applyPagination('anuncio'); applyPagination('post'); });
    el.addEventListener('change', ()=>{ paginacao.anuncio.page = 1; paginacao.post.page = 1; applyPagination('anuncio'); applyPagination('post'); });
  }
});

document.addEventListener('DOMContentLoaded', function(){
  applyPagination('anuncio');
  applyPagination('post');
});
</script>

<?php include 'includes/footer.php'; ?> 
