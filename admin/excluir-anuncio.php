<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/AnunciosManager.php';
require_once 'includes/auth.php';

$anunciosManager = new AnunciosManager($pdo);

// Verificar se o ID foi fornecido
$anuncio_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$anuncio_id) {
    header('Location: anuncios.php');
    exit;
}

// Verificar se o anúncio existe
$anuncio = $anunciosManager->getAnuncio($anuncio_id);
if (!$anuncio) {
    header('Location: anuncios.php');
    exit;
}

// Processar exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sucesso = $anunciosManager->excluirAnuncio($anuncio_id);
    
    if ($sucesso) {
        header('Location: anuncios.php?success=1');
        exit;
    } else {
        $erro = 'Erro ao excluir anúncio. Tente novamente.';
    }
}

$page_title = 'Excluir Anúncio';
include 'includes/header.php';
?>


            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Excluir Anúncio</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="anuncios.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
            
            <?php if (isset($erro)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($erro); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle text-warning"></i> Confirmar Exclusão</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h6>Tem certeza que deseja excluir este anúncio?</h6>
                        <p class="mb-0">Esta ação não pode ser desfeita.</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <h6>Detalhes do Anúncio:</h6>
                            <ul class="list-unstyled">
                                <li><strong>Título:</strong> <?php echo htmlspecialchars($anuncio['titulo']); ?></li>
                                <li><strong>Localização:</strong> <?php echo ucfirst($anuncio['localizacao']); ?></li>
                                <li><strong>Status:</strong> <?php echo $anuncio['ativo'] ? 'Ativo' : 'Inativo'; ?></li>
                                <li><strong>Criado em:</strong> <?php echo date('d/m/Y H:i', strtotime($anuncio['criado_em'])); ?></li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <img src="<?php echo htmlspecialchars($anuncio['imagem']); ?>" 
                                 alt="<?php echo htmlspecialchars($anuncio['titulo']); ?>"
                                 class="img-fluid rounded" style="max-height: 150px;">
                        </div>
                    </div>
                    
                    <form method="POST" class="mt-4">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Confirmar Exclusão
                        </button>
                        <a href="anuncios.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </form>
                </div>
            </div>


<?php include 'includes/footer.php'; ?> 
