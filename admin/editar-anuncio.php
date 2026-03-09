<?php
require_once '../config/config.php';
require_once '../includes/db.php';
require_once 'includes/auth.php';

// Verificar se o usuário está logado
check_login();

// Conexão via $pdo (definido em ../includes/db.php)

// Verificar se o ID foi fornecido
$anuncio_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$anuncio_id) {
    header('Location: anuncios.php');
    exit;
}

// Buscar produto
try {
    $stmt = $pdo->prepare("SELECT * FROM anuncios WHERE id = ?");
    $stmt->execute([$anuncio_id]);
    $anuncio = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $anuncio = false;
}
if (!$anuncio) {
    header('Location: anuncios.php');
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $link_compra = trim($_POST['link_compra'] ?? '');
    $marca = $_POST['marca'] ?? '';
    $ativo = isset($_POST['ativo']);
    
    // Processar upload da imagem (se houver)
    $imagem_path = $anuncio['imagem']; // Manter imagem atual
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/images/';
        
        // Criar diretório se não existir
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $filename = 'anuncio_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_path)) {
                $imagem_path = '/uploads/images/' . $filename;
            } else {
                $mensagem = 'Erro ao fazer upload da imagem.';
                $tipo_mensagem = 'danger';
            }
        } else {
            $mensagem = 'Formato de imagem não suportado. Use JPG, PNG, GIF ou WebP.';
            $tipo_mensagem = 'danger';
        }
    }
    
    // Validar campos obrigatórios
    if (empty($titulo) || empty($link_compra)) {
        $mensagem = 'Nome do produto e link são obrigatórios.';
        $tipo_mensagem = 'danger';
    } elseif (empty($mensagem)) {
        try {
            $sql = "UPDATE anuncios SET titulo = ?, imagem = ?, link_compra = ?, marca = ?, ativo = ?, atualizado_em = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $ok = $stmt->execute([$titulo, $imagem_path, $link_compra, $marca, $ativo ? 1 : 0, $anuncio_id]);
            if ($ok) {
                header('Location: anuncios.php?success=1');
                exit;
            } else {
                $mensagem = 'Erro ao atualizar produto. Tente novamente.';
                $tipo_mensagem = 'danger';
            }
        } catch (Exception $e) {
            $mensagem = 'Erro: ' . $e->getMessage();
            $tipo_mensagem = 'danger';
        }
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Editar Produto</h1>
                <a href="anuncios.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
            
            <?php if (!empty($mensagem)): ?>
                <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($mensagem); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informações do Produto</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="titulo" class="form-label">Nome do Produto *</label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" 
                                           value="<?php echo htmlspecialchars($anuncio['titulo']); ?>" 
                                           required>
                                    <div class="form-text">Nome completo do produto</div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="marca" class="form-label">Marca</label>
                                    <select class="form-select" id="marca" name="marca">
                                        <option value="">Nenhuma</option>
                                        <option value="amazon" <?php echo $anuncio['marca'] === 'amazon' ? 'selected' : ''; ?>>Amazon</option>
                                        <option value="shopee" <?php echo $anuncio['marca'] === 'shopee' ? 'selected' : ''; ?>>Shopee</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="link_compra" class="form-label">Link do Produto *</label>
                            <input type="url" class="form-control" id="link_compra" name="link_compra" 
                                   value="<?php echo htmlspecialchars($anuncio['link_compra']); ?>" 
                                   required>
                            <div class="form-text">Link direto para compra do produto</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="imagem" class="form-label">Imagem do Produto</label>
                            <input type="file" class="form-control" id="imagem" name="imagem" 
                                   accept="image/*">
                            <div class="form-text">Selecione uma nova imagem ou deixe em branco para manter a atual</div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="ativo" name="ativo" 
                                       <?php echo $anuncio['ativo'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="ativo">
                                    Produto Ativo
                                </label>
                                <div class="form-text">Produtos inativos não aparecem em nenhum grupo</div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="anuncios.php" class="btn btn-secondary">← Voltar</a>
                            <button type="submit" class="btn btn-primary">Atualizar Produto</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Preview do Produto -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Preview do Produto</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <?php if (!empty($anuncio['imagem'])): ?>
                                        <img src="<?php echo htmlspecialchars($anuncio['imagem']); ?>" 
                                             alt="<?php echo htmlspecialchars($anuncio['titulo']); ?>"
                                             class="img-fluid rounded mb-3" style="max-height: 200px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light rounded mb-3 d-flex align-items-center justify-content-center" 
                                             style="height: 200px;">
                                            <i class="fas fa-image fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <h6><?php echo htmlspecialchars($anuncio['titulo']); ?></h6>
                                    
                                    <?php if (!empty($anuncio['marca'])): ?>
                                        <?php if ($anuncio['marca'] === 'amazon'): ?>
                                            <span class="badge badge-amazon mb-2">
                                                <i class="fab fa-amazon"></i> Amazon
                                            </span>
                                        <?php elseif ($anuncio['marca'] === 'shopee'): ?>
                                            <span class="badge badge-shopee mb-2">
                                                <i class="fas fa-shopping-cart"></i> Shopee
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <a href="<?php echo htmlspecialchars($anuncio['link_compra']); ?>" 
                                       target="_blank" class="btn btn-primary btn-sm">
                                        <i class="fas fa-external-link-alt"></i> Ver Produto
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Informações do Produto</h6>
                                <ul class="mb-0">
                                    <li><strong>ID:</strong> #<?php echo $anuncio['id']; ?></li>
                                    <li><strong>Status:</strong> 
                                        <?php if ($anuncio['ativo']): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inativo</span>
                                        <?php endif; ?>
                                    </li>
                                    <li><strong>Criado em:</strong> <?php echo date('d/m/Y H:i', strtotime($anuncio['criado_em'])); ?></li>
                                    <li><strong>Última atualização:</strong> <?php echo date('d/m/Y H:i', strtotime($anuncio['atualizado_em'])); ?></li>
                                </ul>
                            </div>
                            
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle"></i> Lembrete</h6>
                                <p class="mb-0">
                                    Para configurar onde este produto será exibido no site, 
                                    vá para <a href="grupos-anuncios.php" class="alert-link">Grupos de Anúncios</a> 
                                    e crie ou edite um grupo que inclua este produto.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 
