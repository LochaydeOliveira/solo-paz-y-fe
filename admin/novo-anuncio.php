<?php
require_once '../config/config.php';
require_once '../includes/db.php';
require_once 'includes/auth.php';

// Verificar se o usuário está logado
check_login();

// Conexão via $pdo (definido em ../includes/db.php)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $link_compra = trim($_POST['link_compra']);
    $marca = $_POST['marca'];
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
    // Processar upload da imagem
    $imagem_path = '';
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
                $erro = "Erro ao fazer upload da imagem.";
            }
        } else {
            $erro = "Formato de imagem não suportado. Use JPG, PNG, GIF ou WebP.";
        }
    }
    
    // Validações básicas
    if (empty($titulo) || empty($link_compra)) {
        $erro = "Nome do produto e link são obrigatórios.";
    } elseif (empty($erro)) {
        try {
            $sql = "INSERT INTO anuncios (titulo, imagem, link_compra, marca, ativo, criado_em) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $ok = $stmt->execute([$titulo, $imagem_path, $link_compra, $marca, $ativo]);
            if ($ok) {
                $sucesso = "Produto cadastrado com sucesso!";
                $_POST = array();
            } else {
                $erro = "Erro ao cadastrar produto.";
            }
        } catch (Exception $e) {
            $erro = "Erro: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="h3 mb-4">Cadastrar Novo Produto</h1>
            
            <?php if (isset($erro)): ?>
                <div class="alert alert-danger"><?php echo $erro; ?></div>
            <?php endif; ?>
            
            <?php if (isset($sucesso)): ?>
                <div class="alert alert-success"><?php echo $sucesso; ?></div>
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
                                           value="<?php echo isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : ''; ?>" 
                                           required>
                                    <div class="form-text">Nome completo do produto</div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="marca" class="form-label">Marca</label>
                                    <select class="form-select" id="marca" name="marca">
                                        <option value="">Nenhuma</option>
                                        <option value="amazon" <?php echo (isset($_POST['marca']) && $_POST['marca'] === 'amazon') ? 'selected' : ''; ?>>Amazon</option>
                                        <option value="shopee" <?php echo (isset($_POST['marca']) && $_POST['marca'] === 'shopee') ? 'selected' : ''; ?>>Shopee</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="link_compra" class="form-label">Link do Produto *</label>
                            <input type="url" class="form-control" id="link_compra" name="link_compra" 
                                   value="<?php echo isset($_POST['link_compra']) ? htmlspecialchars($_POST['link_compra']) : ''; ?>" 
                                   required>
                            <div class="form-text">Link direto para compra do produto</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="imagem" class="form-label">Imagem do Produto</label>
                            <input type="file" class="form-control" id="imagem" name="imagem" 
                                   accept="image/*">
                            <div class="form-text">Selecione uma imagem do produto (JPG, PNG, GIF, WebP)</div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="ativo" name="ativo" 
                                       <?php echo (isset($_POST['ativo']) && $_POST['ativo']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="ativo">
                                    Produto Ativo
                                </label>
                                <div class="form-text">Produtos inativos não aparecem em nenhum grupo</div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="anuncios.php" class="btn btn-secondary">← Voltar</a>
                            <button type="submit" class="btn btn-primary">Cadastrar Produto</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Como Funciona</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>📋 Página Anúncios</h6>
                            <ul class="mb-0">
                                <li>Cadastre produtos no catálogo</li>
                                <li>Configure informações básicas</li>
                                <li>Defina marca (Amazon/Shopee)</li>
                                <li>Ative/desative produtos</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>🎯 Próximo Passo</h6>
                            <ul class="mb-0">
                                <li>Vá para "Grupos de Anúncios"</li>
                                <li>Selecione produtos do catálogo</li>
                                <li>Configure onde e como exibir</li>
                                <li>Defina posts específicos</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 
